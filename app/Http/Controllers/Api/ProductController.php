<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage; 

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse 
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $status = $request->query('status');
        $limit = $request->query('limit', 5); 

        $query = Product::with(['category', 'variants' => function($query){
            $query->where('is_active', true);
        }]);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($status) {
            $isActive = ($status === 'active');
            $query->where('is_active', $isActive);
        } else {
            $query->where('is_active', true);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate($limit);
        
        return response()->json([
            'success' => true,
            'data' => $products 
        ], 200);
    }

    
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'product_name' => 'required|string|max:100|unique:products,product_name', 
            'slug' => 'nullable|string|unique:products,slug',
            'description' => 'nullable|string|max:500',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.variant_name' => 'required|string',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.sku' => 'nullable|string|unique:product_variants,sku',
            'variants.*.detail_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $imagePath = null;
            if ($request->hasFile('main_image')) {
                $imagePath = $request->file('main_image')->store('products', 'public');
            }

             $product = Product::create([
                'category_id' => $request->category_id,
                'product_name' => $request->product_name,
                'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->product_name) . '-' . Str::lower(Str::random(5)),
                'description' => $request->description,
                'main_image' => $imagePath,
                'is_active' => true,
             ]);

             foreach ($request->variants as $variant) {
                $variantImagePath = null;
                if (isset($variant['detail_image']) && $variant['detail_image'] instanceof \Illuminate\Http\UploadedFile) {
                    $variantImagePath = $variant['detail_image']->store('products/variants', 'public');
                }
                
                $product->variants()->create([
                    'variant_name' => $variant['variant_name'],
                    'price' => $variant['price'],
                    'stock' => $variant['stock'],
                    'sku' => $variant['sku'],
                    'detail_image' => $variantImagePath, 
                    'is_active' => true,
                ]);
             }

             DB::commit();
             return response()->json([
                'success' => true,
                'message' => 'Product Created Successfully',
                'data' => $product->load('variants')
              ], 201);
              
        } catch (\Exception $e) {
            DB::rollBack();

            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to Save Product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::with('variants')->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product Not Found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ], 200);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product Not Found' 
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|required|exists:categories,id',
            'product_name' => [
                'sometimes', 'required', 'string', 'max:100', 
                Rule::unique('products', 'product_name')->ignore($product->id)
            ],
            'description' => 'sometimes|nullable|string|max:500',
            'main_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $validator->validated();

            if ($request->hasFile('main_image')) {
                if ($product->main_image && Storage::disk('public')->exists($product->main_image)) {
                    Storage::disk('public')->delete($product->main_image);
                }
                $data['main_image'] = $request->file('main_image')->store('products', 'public');
            }

            if ($request->has('product_name')) {
                if ($product->product_name !== $request->product_name) {
                    $data['slug'] = Str::slug($request->product_name) . '-' . Str::random(5);
                }
            }

            if ($request->has('variants')) {
                $variantIds = collect($request->variants)->pluck('id')->filter()->toArray();

                $product->variants()->whereNotIn('id', $variantIds)->delete();

                foreach ($request->variants as $v) {
                    $variantData = [
                        'variant_name' => $v['variant_name'],
                        'price' => $v['price'],
                        'stock' => $v['stock'],
                        'sku' => $v['sku'] ?? null,
                        'is_active' => $v['is_active'] ?? true,
                    ];

                    if (isset($v['detail_image']) && $v['detail_image'] instanceof \Illuminate\Http\UploadedFile) {
                        $variantData['detail_image'] = $v['detail_image']->store('products/variants', 'public');
                    }

                    $product->variants()->updateOrCreate(
                        ['id' => $v['id'] ?? null],
                        $variantData
                    );
                }
            }

            $product->update($data);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Product Updated Successfully',
                'data' => $product->load('variants')
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }  
    }

    public function destroy(string $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product Not Found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product Deleted Successfully'
        ], 200);
    }

    public function restore(string $id): JsonResponse
    {
        $product = Product::onlyTrashed()->find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product Not Found in Trash'
            ], 404);
        }

        $product->restore();

        return response()->json([
            'success' => true,
            'message' => 'Product Restored Successfully',
            'data' => $product
        ], 200);
    }
}