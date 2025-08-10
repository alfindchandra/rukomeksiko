<?php
namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class SalesController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Sale::with(['ruko', 'user'])
            ->orderBy('tanggal_penjualan', 'desc');

        // Filter based on user role
        if ($user->isAdminRuko()) {
            $query->where('ruko_id', $user->ruko_id);
        }

        // Apply additional filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('ruko_id') && $user->isAdminPusat()) {
            $query->where('ruko_id', $request->ruko_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('tanggal_penjualan', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('tanggal_penjualan', '<=', $request->date_to);
        }

        $sales = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $sales
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Only admin ruko can create sales
        if (!$user->isAdminRuko()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only ruko admin can record sales'
            ], 403);
        }

        $request->validate([
            'tanggal_penjualan' => 'required|date',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_jual' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Generate sale code
            $saleCode = 'SAL-' . date('Ymd') . '-' . str_pad(
                Sale::whereDate('created_at', today())->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            // Create sale
            $sale = Sale::create([
                'kode_penjualan' => $saleCode,
                'ruko_id' => $user->ruko_id,
                'total_amount' => 0,
                'total_profit' => 0,
                'status' => 'selesai',
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'user_id' => auth()->id(),
                'catatan' => $request->catatan
            ]);

            // Create sale items and update stock
            foreach ($request->items as $item) {
                // Get product to retrieve cost price
                $product = \App\Models\Product::findOrFail($item['product_id']);

                // Check if sufficient stock is available in ruko
                $rukoInventory = \App\Models\Inventory::where('product_id', $item['product_id'])
                    ->where('ruko_id', $user->ruko_id)
                    ->first();

                if (!$rukoInventory || $rukoInventory->stok_tersedia < $item['jumlah']) {
                    throw new Exception("Insufficient stock for product: {$product->nama_barang}");
                }

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_jual' => $item['harga_jual'],
                    'harga_beli' => $product->harga_beli
                ]);

                // Reduce stock from ruko
                $this->inventoryService->updateStock(
                    $item['product_id'],
                    $user->ruko_id,
                    $item['jumlah'],
                    'penjualan',
                    $saleCode,
                    auth()->id(),
                    "Penjualan barang"
                );
            }

            // Calculate totals
            $sale->calculateTotals();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sale recorded successfully',
                'data' => $sale->load(['ruko', 'items.product'])
            ], 201);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record sale: ' . $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        
        $query = Sale::with(['ruko', 'user', 'items.product.category']);

        // Filter based on user role
        if ($user->isAdminRuko()) {
            $query->where('ruko_id', $user->ruko_id);
        }

        $sale = $query->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $sale
        ]);
    }

    public function cancel($id)
    {
        $user = auth()->user();
        $sale = Sale::with('items')->findOrFail($id);

        // Only admin ruko can cancel sales from their ruko
        if ($user->isAdminRuko() && $user->ruko_id !== $sale->ruko_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only cancel sales from your ruko'
            ], 403);
        }

        if ($sale->status === 'dibatalkan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Sale is already cancelled'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Return stock to ruko
            foreach ($sale->items as $item) {
                $this->inventoryService->updateStock(
                    $item->product_id,
                    $sale->ruko_id,
                    $item->jumlah,
                    'masuk',
                    $sale->kode_penjualan . '-CANCEL',
                    auth()->id(),
                    "Pembatalan penjualan: {$sale->kode_penjualan}"
                );
            }

            $sale->update(['status' => 'dibatalkan']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sale cancelled successfully',
                'data' => $sale
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel sale: ' . $e->getMessage()
            ], 400);
        }
    }

    public function report(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'ruko_id' => 'nullable|exists:rukos,id'
        ]);

        $query = Sale::with(['ruko', 'items.product'])
            ->where('status', 'selesai')
            ->whereDate('tanggal_penjualan', '>=', $request->date_from)
            ->whereDate('tanggal_penjualan', '<=', $request->date_to);

        // Filter based on user role
        if ($user->isAdminRuko()) {
            $query->where('ruko_id', $user->ruko_id);
        } elseif ($request->has('ruko_id')) {
            $query->where('ruko_id', $request->ruko_id);
        }

        $sales = $query->get();

        // Calculate summary
        $summary = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'total_profit' => $sales->sum('total_profit'),
            'period' => [
                'from' => $request->date_from,
                'to' => $request->date_to
            ]
        ];

        // Top selling products
        $topProducts = $sales->flatMap->items
            ->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->nama_barang,
                    'total_quantity' => $items->sum('jumlah'),
                    'total_revenue' => $items->sum('subtotal')
                ];
            })
            ->sortByDesc('total_quantity')
            ->take(10)
            ->values();

        // Sales by ruko (for admin pusat only)
        $salesByRuko = [];
        if ($user->isAdminPusat()) {
            $salesByRuko = $sales->groupBy('ruko_id')
                ->map(function ($rukoSales) {
                    $ruko = $rukoSales->first()->ruko;
                    return [
                        'ruko_id' => $ruko->id,
                        'ruko_name' => $ruko->nama_ruko,
                        'total_sales' => $rukoSales->count(),
                        'total_revenue' => $rukoSales->sum('total_amount'),
                        'total_profit' => $rukoSales->sum('total_profit')
                    ];
                })
                ->values();
        }

        // Daily sales trend
        $dailyTrend = $sales->groupBy(function ($sale) {
            return $sale->tanggal_penjualan->format('Y-m-d');
        })->map(function ($dailySales, $date) {
            return [
                'date' => $date,
                'total_sales' => $dailySales->count(),
                'total_revenue' => $dailySales->sum('total_amount'),
                'total_profit' => $dailySales->sum('total_profit')
            ];
        })->sortBy('date')->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => $summary,
                'top_products' => $topProducts,
                'sales_by_ruko' => $salesByRuko,
                'daily_trend' => $dailyTrend,
                'detailed_sales' => $sales
            ]
        ]);
    }
}