<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $source = $request->query('source'); 
        $startDate = Carbon::now()->subDays(6); 
        $endDate = Carbon::now()->endOfDay();
        $today = Carbon::today();

        //Statistik Hari Ini (PENTING: Hanya hitung yang 'completed')
        $todayQuery = Order::whereDate('created_at', $today);
        if ($source) $todayQuery->where('source', $source);

        $todayOrders = (clone $todayQuery)->count();
        $todaySales = (clone $todayQuery)->sum('grand_total'); 

        //Pending Orders 
        $pendingQuery = Order::where('status', 'pending');
        if ($source) $pendingQuery->where('source', $source);
        $pendingOrders = $pendingQuery->count();

        // Data Grafik (Penjualan harian 7 hari terakhir, filtered by source)
        $graphQuery = Order::select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(grand_total) as total'))
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($source) {
            $graphQuery->where('source', $source);
        }
            
        $salesGraph = $graphQuery->groupBy('date')
            ->orderBy('date')
            ->get();

        // Data Stok Rendah (Varian yang stoknya < 5)
        $lowStockProducts = ProductVariant::with('product')
            ->where('stock', '<', 5)
            ->get();

        // Stok Terlaris
        $bestSellerProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.product_name',
                'product_variants.variant_name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->whereBetween('orders.created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);

        if ($source) {
            $bestSellerProducts->where('orders.source', $source);
        }

        $bestSellerProducts = $bestSellerProducts
            ->groupBy('order_items.product_variant_id', 'products.product_name', 'product_variants.variant_name')
            ->orderBy('total_sold', 'desc')
            ->limit(5) 
            ->get();

        return response()->json([
            'todayOrders' => $todayOrders, 
            'todaySales' => $todaySales,  
            'pendingOrders' => $pendingOrders,
            'salesGraph' => $salesGraph,
            'lowStockProducts' => $lowStockProducts,
            'bestSellerProducts' => $bestSellerProducts,
        ]);
    }

    public function downloadReport(Request $request)
    {
        $source = $request->query('source', 'online');
        $fileName = 'laporan-penjualan-' . $source . '-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new SalesReportExport($source), $fileName);
    }
}
