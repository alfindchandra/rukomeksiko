<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Inventory;
use App\Models\Shipment;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard berdasarkan peran pengguna.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Asumsi method isAdminPusat() dan ruko_id sudah ada di model User
        if ($user->isAdminPusat()) {
            return $this->adminPusatDashboard();
        } else {
            // Pastikan user memiliki ruko_id, jika tidak berikan response error
            if (!$user->ruko_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak terasosiasi dengan ruko.'
                ], 403);
            }
            return $this->adminRukoDashboard($user->ruko_id);
        }
    }

    /**
     * Menyiapkan data dashboard untuk Admin Pusat.
     */
    private function adminPusatDashboard()
    {
        // Overview Penjualan
        $todaySales = Sale::whereDate('tanggal_penjualan', today())
            ->where('status', 'selesai')
            ->sum('total_amount');

        $monthSales = Sale::whereMonth('tanggal_penjualan', now()->month)
            ->whereYear('tanggal_penjualan', now()->year)
            ->where('status', 'selesai')
            ->sum('total_amount');

        // Overview Inventaris
        // Menggunakan join untuk query yang lebih efisien daripada subquery
        $lowStockCount = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->whereColumn('inventories.stok_tersedia', '<=', 'products.stok_minimum')
            ->count();

        // Menggunakan join dan DB::raw untuk menghitung nilai total stok secara efisien
        $totalStockValue = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->sum(DB::raw('inventories.stok_tersedia * products.harga_beli'));

        // Overview Operasional
        $pendingShipments = Shipment::where('status', 'dalam_perjalanan')->count();
        $pendingPO = PurchaseOrder::where('status', 'dipesan')->count();

        // Top performing rukos
        $topRukos = Sale::select('ruko_id', DB::raw('SUM(total_amount) as total_sales'))
            ->with('ruko:id,nama_ruko') // Eager loading dengan select spesifik untuk efisiensi
            ->whereMonth('tanggal_penjualan', now()->month)
            ->whereYear('tanggal_penjualan', now()->year)
            ->where('status', 'selesai')
            ->groupBy('ruko_id')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'sales' => [
                    'today' => $todaySales,
                    'this_month' => $monthSales
                ],
                'inventory' => [
                    'low_stock_items' => $lowStockCount,
                    'total_stock_value' => $totalStockValue
                ],
                'operations' => [
                    'pending_shipments' => $pendingShipments,
                    'pending_purchase_orders' => $pendingPO
                ],
                'top_rukos' => $topRukos
            ]
        ]);
    }

    /**
     * Menyiapkan data dashboard untuk Admin Ruko.
     */
    private function adminRukoDashboard($rukoId)
    {
        // Overview Penjualan untuk ruko ini
        $todaySales = Sale::where('ruko_id', $rukoId)
            ->whereDate('tanggal_penjualan', today())
            ->where('status', 'selesai')
            ->sum('total_amount');

        $monthSales = Sale::where('ruko_id', $rukoId)
            ->whereMonth('tanggal_penjualan', now()->month)
            ->whereYear('tanggal_penjualan', now()->year)
            ->where('status', 'selesai')
            ->sum('total_amount');

        // Overview Inventaris untuk ruko ini
        $lowStockCount = Inventory::where('ruko_id', $rukoId)
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->whereColumn('inventories.stok_tersedia', '<=', 'products.stok_minimum')
            ->count();
            
        $totalProducts = Inventory::where('ruko_id', $rukoId)->count();

        // Kiriman masuk
        $incomingShipments = Shipment::where('ruko_id', $rukoId)
            ->where('status', 'dalam_perjalanan')
            ->count();

        // Trend penjualan (7 hari terakhir)
        $salesTrend = Sale::where('ruko_id', $rukoId)
            ->whereDate('tanggal_penjualan', '>=', now()->subDays(7))
            ->where('status', 'selesai')
            ->selectRaw('DATE(tanggal_penjualan) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'sales' => [
                    'today' => $todaySales,
                    'this_month' => $monthSales,
                    'trend' => $salesTrend
                ],
                'inventory' => [
                    'total_products' => $totalProducts,
                    'low_stock_items' => $lowStockCount
                ],
                'operations' => [
                    'incoming_shipments' => $incomingShipments
                ]
            ]
        ]);
    }
}