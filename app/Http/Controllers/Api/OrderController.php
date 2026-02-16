<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Packaging;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with([
                'shipping', 
                'packaging', 
                'orderItems.productVariant.product' 
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $orders
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $order = Order::with([
            'shipping', 
            'packaging', 
            'orderItems.productVariant.product',
            'user'
        ])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false, 
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $order
        ], 200);
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function showMyOrder($id)
    {
        $order = Order::with(['orderItems.productVariant.product'])
                    ->where('user_id', auth()->id())
                    ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function cancelOrder($id)
    {
        $order = Order::where('user_id', auth()->id())
                    ->where('id', $id)
                    ->where('status', 'pending') 
                    ->firstOrFail();

        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan.'
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [ 
            'order_id' => 'nullable|exists:orders,id',
            'customer_name' => 'nullable|string',
            'shipping_id' => 'nullable|exists:shippings,id',
            'package_id' => 'nullable|exists:packagings,id',
            'greeting_card_note' => 'nullable|string',
            'greeting_card_price' => 'nullable|numeric|min:0',
            'delivery_at' => 'nullable|date|after:now','shipping_address' => 'nullable|string',
            'payment_method' => 'required|string',
            'grand_total' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'amount_change' => 'nullable|numeric',
            'items' => 'required|array',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            if ($request->filled('order_id')) {
                $order = Order::findOrFail($request->order_id);
                foreach ($order->orderItems as $oldItem) {
                $oldItem->productVariant->increment('stock', $oldItem->quantity);
            }
                $order->orderItems()->delete(); 
            } else {
                $order = new Order();
                $order->order_number = 'UMA-' . strtoupper(uniqid());
                $order->user_id = auth()->id();
            }
            // Hitung Subtotal & Cek Stok Varian
            $subtotal = 0;
            foreach ($request->items as $item) {
                $variant = \App\Models\ProductVariant::find($item['product_variant_id']);
                
                if ($variant->stock < $item['quantity']) {
                    throw new \Exception("Stock for variant '{$variant->name}' is insufficient.");
                }

                $subtotal += $variant->price * $item['quantity'];

                $variant->decrement('stock', $item['quantity']);
            }

            // Hitung Biaya Tambahan
            $shippingCost = $request->shipping_id ? Shipping::find($request->shipping_id)->base_shipping_cost : 0;
            $packagingCost = $request->package_id ? Packaging::find($request->package_id)->base_packaging_cost : 0;
            $greetingCardPrice = $request->greeting_card_price ?? 0;
            
            $order->customer_name = $request->customer_name;
            $order->shipping_id = $request->shipping_id;
            $order->package_id = $request->package_id;
            $order->subtotal = $subtotal;
            $order->shipping_cost = $shippingCost;
            $order->packaging_cost = $packagingCost;
            $order->grand_total = $subtotal + $shippingCost + $packagingCost + $greetingCardPrice;
            $order->amount_paid = $request->amount_paid ?? 0;
            $order->amount_change = $request->amount_change ?? 0;
            $order->status = 'completed';
            $order->source = 'offline';
            $order->payment_method = $request->payment_method;
            $order->save();
           
            // Simpan Detail Item (order_items)
            foreach ($request->items as $item) {
                $variant = \App\Models\ProductVariant::find($item['product_variant_id']);
                
                $order->orderItems()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                    'price_at_buy'       => $variant->price, 
                    'subtotal'           => $variant->price * $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data'    => $order->load(['orderItems.productVariant.product', 'shipping', 'packaging']) 
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPendingOrders(): JsonResponse
    {
        $pendingOrders = Order::where('status', 'pending')
            ->with(['shipping', 'packaging', 'orderItems.productVariant.product'])
            ->orderBy('created_at', 'asc')
            ->get();

            $formattedOrders = $pendingOrders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer_name,
                    'shipping_id' => $order->shipping_id,
                    'package_id' => $order->package_id,
                    'grand_total' => $order->grand_total,
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'product_id' => $item->productVariant->product->id,
                            'variant_id' => $item->product_variant_id,
                            'name' => $item->productVariant->product->product_name . ' - ' . $item->productVariant->name,
                            'price' => $item->price_at_buy,
                            'quantity' => $item->quantity,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $formattedOrders
        ], 200);
    }

    public function holdOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'nullable|exists:orders,id',
            'customer_name' => 'nullable|string',
            'items' => 'required|array',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_id' => 'required|exists:shippings,id',
            'package_id' => 'required|exists:packagings,id',
            'grand_total' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            if ($request->filled('order_id')) {
                $order = Order::findOrFail($request->order_id);
                foreach($order->orderItems as $oldItem) {
                    $oldItem->productVariant->increment('stock', $oldItem->quantity);
                }
                $order->orderItems()->delete();
                $order->grand_total = $request->grand_total;
            } else {
            $order = new Order();
                $order->order_number = 'HOLD-' . time();
                $order->status = 'pending';
                $order->source = 'offline';
                $order->payment_method = 'cash';
            }

            $order->customer_name = $request->customer_name;
            $order->shipping_id = $request->shipping_id;
            $order->package_id = $request->package_id;
            $order->save();

            foreach ($request->items as $item) {
                $variant = ProductVariant::find($item['variant_id']);

                $order->orderItems()->create([
                    'product_variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price_at_buy' => $variant['price'],
                    'subtotal' => $variant->price * $item['quantity'],
                ]);
                
                $variant->decrement('stock', $item['quantity']);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Order held successfully',
                'data' => $order->load('orderItems.productVariant.product', 'shipping', 'packaging')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $order = Order::with('orderItems.productVariant')->find($id);
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'errors' => $validator->errors()
                ], 422);
        }

        $order = Order::with('orderItems.productVariant')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false, 
                'message' => 'Order not found'
                ], 404);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        DB::beginTransaction();
        try {
            $order->update(['status' => $newStatus]);

            // Logika Pengembalian Stok
            if ($oldStatus !== 'cancelled' && $newStatus === 'cancelled') {
                // Pesanan dibatalkan, kembalikan stok
                foreach ($order->orderItems as $item) {
                    $item->productVariant->increment('stock', $item->quantity);
                }
            } elseif ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                // Pesanan aktif kembali, kurangi stok
                foreach ($order->orderItems as $item) {
                    $item->productVariant->decrement('stock', $item->quantity);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true, 
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
