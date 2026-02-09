<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $source = $request->query('source'); 
        $startDate = Carbon::now()->subDays(6); // 7 hari terakhir
        $endDate = Carbon::now();

        // 1. Statistik Dasar (Filtered by source)
        $query = Order::whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
        
        if ($source) {
            $query->where('source', $source);
        }

        $orders = $query->get();

        // 2. Data Grafik (Penjualan harian 7 hari terakhir, filtered by source)
        $graphQuery = Order::select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(grand_total) as total'))
            ->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($source) {
            $graphQuery->where('source', $source);
        }
            
        $salesGraph = $graphQuery->groupBy('date')
            ->orderBy('date')
            ->get();

        // 3. Data Stok Rendah (Varian yang stoknya < 5)
        $lowStockProducts = ProductVariant::with('product')
            ->where('stock', '<', 5)
            ->get();

        return response()->json([
            'todayOrders' => $orders->where('created_at', '>=', Carbon::now()->startOfDay())->count(),
            'todaySales' => $orders->where('created_at', '>=', Carbon::now()->startOfDay())->sum('grand_total'),
            'pendingOrders' => $orders->where('status', 'pending')->count(),
            'salesGraph' => $salesGraph,
            'lowStockProducts' => $lowStockProducts
        ]);
    }
}
