<?php
namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchaseOrderController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('tanggal_order', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('tanggal_order', '<=', $request->date_to);
        }

        $purchaseOrders = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $purchaseOrders
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tanggal_order' => 'required|date',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah_dipesan' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Generate PO code
            $poCode = 'PO-' . date('Ymd') . '-' . str_pad(
                PurchaseOrder::whereDate('created_at', today())->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'kode_po' => $poCode,
                'supplier_id' => $request->supplier_id,
                'status' => 'draft',
                'tanggal_order' => $request->tanggal_order,
                'catatan' => $request->catatan,
                'user_id' => auth()->id()
            ]);

            // Create purchase order items
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'jumlah_dipesan' => $item['jumlah_dipesan'],
                    'harga_satuan' => $item['harga_satuan']
                ]);
            }

            // Calculate total
            $purchaseOrder->calculateTotal();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase order created successfully',
                'data' => $purchaseOrder->load(['supplier', 'items.product'])
            ], 201);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create purchase order: ' . $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'user', 'items.product.category'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $purchaseOrder
        ]);
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft purchase orders can be updated'
            ], 400);
        }

        $request->validate([
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'tanggal_order' => 'sometimes|date',
            'catatan' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.jumlah_dipesan' => 'required_with:items|integer|min:1',
            'items.*.harga_satuan' => 'required_with:items|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Update purchase order
            $purchaseOrder->update($request->only([
                'supplier_id', 'tanggal_order', 'catatan'
            ]));

            // Update items if provided
            if ($request->has('items')) {
                // Delete existing items
                $purchaseOrder->items()->delete();

                // Create new items
                foreach ($request->items as $item) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $item['product_id'],
                        'jumlah_dipesan' => $item['jumlah_dipesan'],
                        'harga_satuan' => $item['harga_satuan']
                    ]);
                }

                // Recalculate total
                $purchaseOrder->calculateTotal();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase order updated successfully',
                'data' => $purchaseOrder->load(['supplier', 'items.product'])
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update purchase order: ' . $e->getMessage()
            ], 400);
        }
    }

    public function approve($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft purchase orders can be approved'
            ], 400);
        }

        $purchaseOrder->update(['status' => 'dipesan']);

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase order approved successfully',
            'data' => $purchaseOrder
        ]);
    }

    public function receive(Request $request, $id)
    {
        $request->validate([
            'tanggal_terima' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:purchase_order_items,id',
            'items.*.jumlah_diterima' => 'required|integer|min:0'
        ]);

        $purchaseOrder = PurchaseOrder::with('items')->findOrFail($id);

        if ($purchaseOrder->status !== 'dipesan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only ordered purchase orders can be received'
            ], 400);
        }

        try {
            DB::beginTransaction();

            foreach ($request->items as $receivedItem) {
                $poItem = $purchaseOrder->items->find($receivedItem['item_id']);
                
                if (!$poItem) {
                    throw new Exception("Purchase order item not found: {$receivedItem['item_id']}");
                }

                if ($receivedItem['jumlah_diterima'] > ($poItem->jumlah_dipesan - $poItem->jumlah_diterima)) {
                    throw new Exception("Received quantity exceeds ordered quantity for item: {$poItem->product->nama_barang}");
                }

                // Update received quantity
                $poItem->jumlah_diterima += $receivedItem['jumlah_diterima'];
                $poItem->save();

                // Add stock to central warehouse if quantity received
                if ($receivedItem['jumlah_diterima'] > 0) {
                    $this->inventoryService->updateStock(
                        $poItem->product_id,
                        null, // Central warehouse
                        $receivedItem['jumlah_diterima'],
                        'masuk',
                        $purchaseOrder->kode_po,
                        auth()->id(),
                        "Penerimaan barang dari supplier: {$purchaseOrder->supplier->nama_supplier}"
                    );
                }
            }

            // Update PO status and received date
            $purchaseOrder->update([
                'status' => 'diterima',
                'tanggal_terima' => $request->tanggal_terima
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Goods received successfully',
                'data' => $purchaseOrder->load(['supplier', 'items.product'])
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to receive goods: ' . $e->getMessage()
            ], 400);
        }
    }

    public function cancel($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->status === 'diterima') {
            return response()->json([
                'status' => 'error',
                'message' => 'Received purchase orders cannot be cancelled'
            ], 400);
        }

        $purchaseOrder->update(['status' => 'dibatalkan']);

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase order cancelled successfully',
            'data' => $purchaseOrder
        ]);
    }
}