<?php
namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Update stock and create movement record
     */
    public function updateStock(
        int $productId,
        ?int $rukoId,
        int $quantity,
        string $transactionType,
        string $transactionCode,
        int $userId,
        string $description = null
    ): bool {
        return DB::transaction(function () use (
            $productId, $rukoId, $quantity, $transactionType, 
            $transactionCode, $userId, $description
        ) {
            // Get or create inventory record
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $productId, 'ruko_id' => $rukoId],
                ['stok_tersedia' => 0, 'stok_reserved' => 0]
            );

            $stockBefore = $inventory->stok_tersedia;

            // Validate stock for outbound transactions
            if (in_array($transactionType, ['keluar', 'transfer', 'penjualan']) && $stockBefore < $quantity) {
                throw new Exception('Insufficient stock available');
            }

            // Update stock based on transaction type
            switch ($transactionType) {
                case 'masuk':
                    $inventory->stok_tersedia += $quantity;
                    break;
                case 'keluar':
                case 'transfer':
                case 'penjualan':
                    $inventory->stok_tersedia -= $quantity;
                    break;
                default:
                    throw new Exception('Invalid transaction type');
            }

            $stockAfter = $inventory->stok_tersedia;
            $inventory->save();

            // Create stock movement record
            StockMovement::create([
                'kode_transaksi' => $transactionCode,
                'product_id' => $productId,
                'ruko_id' => $rukoId,
                'jenis_transaksi' => $transactionType,
                'jumlah' => $quantity,
                'stok_sebelum' => $stockBefore,
                'stok_sesudah' => $stockAfter,
                'keterangan' => $description,
                'user_id' => $userId
            ]);

            return true;
        });
    }

    /**
     * Transfer stock from central warehouse to ruko
     */
    public function transferToRuko(
        int $productId,
        int $rukoId,
        int $quantity,
        string $shipmentCode,
        int $userId
    ): bool {
        return DB::transaction(function () use ($productId, $rukoId, $quantity, $shipmentCode, $userId) {
            // Reduce stock from central warehouse
            $this->updateStock(
                $productId,
                null, // Central warehouse
                $quantity,
                'transfer',
                $shipmentCode,
                $userId,
                "Transfer ke ruko ID: {$rukoId}"
            );

            // Add stock to ruko
            $this->updateStock(
                $productId,
                $rukoId,
                $quantity,
                'masuk',
                $shipmentCode,
                $userId,
                "Transfer dari gudang pusat"
            );

            return true;
        });
    }

    /**
     * Get inventory summary for a specific location
     */
    public function getInventorySummary(?int $rukoId = null): array
    {
        $query = Inventory::with(['product.category'])
            ->when($rukoId, function ($q) use ($rukoId) {
                return $q->where('ruko_id', $rukoId);
            }, function ($q) {
                return $q->whereNull('ruko_id'); // Central warehouse
            });

        $inventories = $query->get();

        $summary = [
            'total_products' => $inventories->count(),
            'total_stock_value' => 0,
            'low_stock_items' => 0,
            'out_of_stock_items' => 0,
            'items' => []
        ];

        foreach ($inventories as $inventory) {
            $stockValue = $inventory->stok_tersedia * $inventory->product->harga_beli;
            $summary['total_stock_value'] += $stockValue;

            if ($inventory->stok_tersedia == 0) {
                $summary['out_of_stock_items']++;
            } elseif ($inventory->stok_tersedia <= $inventory->product->stok_minimum) {
                $summary['low_stock_items']++;
            }

            $summary['items'][] = [
                'product_id' => $inventory->product->id,
                'product_code' => $inventory->product->kode_barang,
                'product_name' => $inventory->product->nama_barang,
                'category' => $inventory->product->category->nama_kategori,
                'stock_available' => $inventory->stok_tersedia,
                'stock_reserved' => $inventory->stok_reserved,
                'minimum_stock' => $inventory->product->stok_minimum,
                'stock_status' => $inventory->status_stok,
                'stock_value' => $stockValue,
                'unit' => $inventory->product->satuan
            ];
        }

        return $summary;
    }

    /**
     * Get stock movement history
     */
    public function getStockMovementHistory(array $filters = []): array
    {
        $query = StockMovement::with(['product', 'ruko', 'user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['ruko_id'])) {
            $query->where('ruko_id', $filters['ruko_id']);
        }

        if (isset($filters['transaction_type'])) {
            $query->where('jenis_transaksi', $filters['transaction_type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $movements = $query->paginate(50);

        return [
            'movements' => $movements->items(),
            'pagination' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total()
            ]
        ];
    }

    /**
     * Reserve stock for pending orders
     */
    public function reserveStock(int $productId, ?int $rukoId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $rukoId, $quantity) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('ruko_id', $rukoId)
                ->first();

            if (!$inventory || ($inventory->stok_tersedia - $inventory->stok_reserved) < $quantity) {
                throw new Exception('Insufficient stock available for reservation');
            }

            $inventory->stok_reserved += $quantity;
            $inventory->save();

            return true;
        });
    }

    /**
     * Release reserved stock
     */
    public function releaseReservedStock(int $productId, ?int $rukoId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $rukoId, $quantity) {
            $inventory = Inventory::where('product_id', $productId)
                ->where('ruko_id', $rukoId)
                ->first();

            if (!$inventory || $inventory->stok_reserved < $quantity) {
                throw new Exception('Invalid reserved stock amount');
            }

            $inventory->stok_reserved -= $quantity;
            $inventory->save();

            return true;
        });
    }
}