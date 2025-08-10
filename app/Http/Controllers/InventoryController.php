<?php
namespace App\Http\Controllers;

use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Determine location based on user role
        $rukoId = null;
        if ($user->isAdminRuko()) {
            $rukoId = $user->ruko_id;
        } elseif ($request->has('ruko_id')) {
            $rukoId = $request->ruko_id;
        }

        $inventory = $this->inventoryService->getInventorySummary($rukoId);

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    public function stockMovements(Request $request)
    {
        $user = auth()->user();
        
        $filters = $request->only([
            'product_id', 'transaction_type', 'date_from', 'date_to'
        ]);

        // Add ruko filter based on user role
        if ($user->isAdminRuko()) {
            $filters['ruko_id'] = $user->ruko_id;
        } elseif ($request->has('ruko_id')) {
            $filters['ruko_id'] = $request->ruko_id;
        }

        $movements = $this->inventoryService->getStockMovementHistory($filters);

        return response()->json([
            'status' => 'success',
            'data' => $movements
        ]);
    }

    public function lowStock(Request $request)
    {
        $user = auth()->user();
        
        $query = \App\Models\Inventory::with(['product.category', 'ruko'])
            ->whereRaw('stok_tersedia <= (SELECT stok_minimum FROM products WHERE products.id = inventories.product_id)')
            ->orderBy('stok_tersedia', 'asc');

        // Filter based on user role
        if ($user->isAdminRuko()) {
            $query->where('ruko_id', $user->ruko_id);
        } elseif ($request->has('ruko_id')) {
            $query->where('ruko_id', $request->ruko_id);
        }

        $lowStockItems = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $lowStockItems->map(function ($inventory) {
                return [
                    'product_id' => $inventory->product->id,
                    'product_code' => $inventory->product->kode_barang,
                    'product_name' => $inventory->product->nama_barang,
                    'category' => $inventory->product->category->nama_kategori,
                    'current_stock' => $inventory->stok_tersedia,
                    'minimum_stock' => $inventory->product->stok_minimum,
                    'location' => $inventory->ruko ? $inventory->ruko->nama_ruko : 'Gudang Pusat',
                    'status' => $inventory->stok_tersedia == 0 ? 'out_of_stock' : 'low_stock'
                ];
            })
        ]);
    }

    public function stockValue(Request $request)
    {
        $user = auth()->user();
        
        $query = \App\Models\Inventory::with(['product', 'ruko'])
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);
            });

        // Filter based on user role
        if ($user->isAdminRuko()) {
            $query->where('ruko_id', $user->ruko_id);
        } elseif ($request->has('ruko_id')) {
            $query->where('ruko_id', $request->ruko_id);
        }

        $inventories = $query->get();

        $summary = [
            'total_items' => $inventories->count(),
            'total_stock_value' => 0,
            'total_potential_revenue' => 0,
            'breakdown_by_category' => []
        ];

        $categoryBreakdown = [];

        foreach ($inventories as $inventory) {
            $stockValue = $inventory->stok_tersedia * $inventory->product->harga_beli;
            $potentialRevenue = $inventory->stok_tersedia * $inventory->product->harga_jual;
            
            $summary['total_stock_value'] += $stockValue;
            $summary['total_potential_revenue'] += $potentialRevenue;

            $categoryName = $inventory->product->category->nama_kategori;
            
            if (!isset($categoryBreakdown[$categoryName])) {
                $categoryBreakdown[$categoryName] = [
                    'category' => $categoryName,
                    'total_items' => 0,
                    'stock_value' => 0,
                    'potential_revenue' => 0
                ];
            }
            
            $categoryBreakdown[$categoryName]['total_items']++;
            $categoryBreakdown[$categoryName]['stock_value'] += $stockValue;
            $categoryBreakdown[$categoryName]['potential_revenue'] += $potentialRevenue;
        }

        $summary['breakdown_by_category'] = array_values($categoryBreakdown);

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    public function productStock($productId, Request $request)
    {
        $user = auth()->user();
        
        $query = \App\Models\Inventory::with(['ruko'])
            ->where('product_id', $productId);

        // Filter based on user role
        if ($user->isAdminRuko()) {
            $query->where('ruko_id', $user->ruko_id);
        }

        $inventories = $query->get();

        $product = \App\Models\Product::with('category')->findOrFail($productId);

        $stockLocations = $inventories->map(function ($inventory) {
            return [
                'location' => $inventory->ruko ? $inventory->ruko->nama_ruko : 'Gudang Pusat',
                'ruko_id' => $inventory->ruko_id,
                'stock_available' => $inventory->stok_tersedia,
                'stock_reserved' => $inventory->stok_reserved,
                'stock_status' => $inventory->status_stok
            ];
        });

        // Get recent stock movements for this product
        $recentMovements = \App\Models\StockMovement::with(['user', 'ruko'])
            ->where('product_id', $productId)
            ->when($user->isAdminRuko(), function ($q) use ($user) {
                return $q->where('ruko_id', $user->ruko_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => $product,
                'stock_locations' => $stockLocations,
                'total_stock' => $inventories->sum('stok_tersedia'),
                'recent_movements' => $recentMovements
            ]
        ]);
    }
}