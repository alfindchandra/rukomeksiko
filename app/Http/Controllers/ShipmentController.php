<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ShipmentController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Shipment::with(['ruko', 'pengirim', 'penerima'])
            ->orderBy('created_at', 'desc');

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
            $query->whereDate('tanggal_pengiriman', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('tanggal_pengiriman', '<=', $request->date_to);
        }

        $shipments = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $shipments
        ]);
    }

    public function store(Request $request)
    {
        // Only admin pusat can create shipments
        if (!auth()->user()->isAdminPusat()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only central admin can create shipments'
            ], 403);
        }

        $request->validate([
            'ruko_id' => 'required|exists:rukos,id',
            'tanggal_pengiriman' => 'required|date',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah_dikirim' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Generate shipment code
            $shipmentCode = 'SHP-' . date('Ymd') . '-' . str_pad(
                Shipment::whereDate('created_at', today())->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            // Create shipment
            $shipment = Shipment::create([
                'kode_pengiriman' => $shipmentCode,
                'ruko_id' => $request->ruko_id,
                'status' => 'dalam_perjalanan',
                'tanggal_pengiriman' => $request->tanggal_pengiriman,
                'catatan' => $request->catatan,
                'pengirim_id' => auth()->id()
            ]);

            // Create shipment items and update stock
            foreach ($request->items as $item) {
                // Check if sufficient stock is available in central warehouse
                $centralInventory = \App\Models\Inventory::where('product_id', $item['product_id'])
                    ->whereNull('ruko_id')
                    ->first();

                if (!$centralInventory || $centralInventory->stok_tersedia < $item['jumlah_dikirim']) {
                    throw new Exception("Insufficient stock for product ID: {$item['product_id']}");
                }

                // Create shipment item
                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'product_id' => $item['product_id'],
                    'jumlah_dikirim' => $item['jumlah_dikirim']
                ]);

                // Reduce stock from central warehouse
                $this->inventoryService->updateStock(
                    $item['product_id'],
                    null, // Central warehouse
                    $item['jumlah_dikirim'],
                    'keluar',
                    $shipmentCode,
                    auth()->id(),
                    "Pengiriman ke ruko: {$shipment->ruko->nama_ruko}"
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Shipment created successfully',
                'data' => $shipment->load(['ruko', 'items.product'])
            ], 201);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create shipment: ' . $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        
        $query = Shipment::with(['ruko', 'pengirim', 'penerima', 'items.product.category']);

        // Filter based on user role
        if ($user->isAdminRuko()) {
            $query->where('ruko_id', $user->ruko_id);
        }

        $shipment = $query->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $shipment
        ]);
    }

    public function receive(Request $request, $id)
    {
        $user = auth()->user();
        $shipment = Shipment::with('items')->findOrFail($id);

        // Only admin ruko can receive shipments for their ruko
        if ($user->isAdminRuko() && $user->ruko_id !== $shipment->ruko_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only receive shipments for your ruko'
            ], 403);
        }

        if ($shipment->status !== 'dalam_perjalanan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only shipments in transit can be received'
            ], 400);
        }

        $request->validate([
            'tanggal_diterima' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:shipment_items,id',
            'items.*.jumlah_diterima' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->items as $receivedItem) {
                $shipmentItem = $shipment->items->find($receivedItem['item_id']);
                
                if (!$shipmentItem) {
                    throw new Exception("Shipment item not found: {$receivedItem['item_id']}");
                }

                if ($receivedItem['jumlah_diterima'] > $shipmentItem->jumlah_dikirim) {
                    throw new Exception("Received quantity exceeds shipped quantity for item: {$shipmentItem->product->nama_barang}");
                }

                // Update received quantity
                $shipmentItem->jumlah_diterima = $receivedItem['jumlah_diterima'];
                $shipmentItem->save();

                // Add stock to ruko if quantity received
                if ($receivedItem['jumlah_diterima'] > 0) {
                    $this->inventoryService->updateStock(
                        $shipmentItem->product_id,
                        $shipment->ruko_id,
                        $receivedItem['jumlah_diterima'],
                        'masuk',
                        $shipment->kode_pengiriman,
                        auth()->id(),
                        "Penerimaan dari gudang pusat"
                    );
                }

                // Handle damaged/lost items (difference between sent and received)
                $damagedQuantity = $shipmentItem->jumlah_dikirim - $receivedItem['jumlah_diterima'];
                if ($damagedQuantity > 0) {
                    // Log damaged/lost items for tracking
                    \App\Models\StockMovement::create([
                        'kode_transaksi' => $shipment->kode_pengiriman . '-LOST',
                        'product_id' => $shipmentItem->product_id,
                        'ruko_id' => null,
                        'jenis_transaksi' => 'keluar',
                        'jumlah' => $damagedQuantity,
                        'stok_sebelum' => 0,
                        'stok_sesudah' => 0,
                        'keterangan' => "Barang hilang/rusak dalam pengiriman: {$shipment->kode_pengiriman}",
                        'user_id' => auth()->id()
                    ]);
                }
            }

            // Update shipment status
            $shipment->update([
                'status' => 'selesai',
                'tanggal_diterima' => $request->tanggal_diterima,
                'penerima_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Shipment received successfully',
                'data' => $shipment->load(['ruko', 'pengirim', 'penerima', 'items.product'])
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to receive shipment: ' . $e->getMessage()
            ], 400);
        }
    }

    public function cancel($id)
    {
        // Only admin pusat can cancel shipments
        if (!auth()->user()->isAdminPusat()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only central admin can cancel shipments'
            ], 403);
        }

        $shipment = Shipment::with('items')->findOrFail($id);

        if ($shipment->status !== 'dalam_perjalanan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only shipments in transit can be cancelled'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Return stock to central warehouse
            foreach ($shipment->items as $item) {
                $this->inventoryService->updateStock(
                    $item->product_id,
                    null, // Central warehouse
                    $item->jumlah_dikirim,
                    'masuk',
                    $shipment->kode_pengiriman . '-CANCEL',
                    auth()->id(),
                    "Pembatalan pengiriman: {$shipment->kode_pengiriman}"
                );
            }

            $shipment->update(['status' => 'dibatalkan']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Shipment cancelled successfully',
                'data' => $shipment
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel shipment: ' . $e->getMessage()
            ], 400);
        }
    }
}