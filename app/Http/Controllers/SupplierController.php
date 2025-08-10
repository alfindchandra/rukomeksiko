<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_supplier', 'LIKE', "%{$search}%")
                  ->orWhere('kontak_person', 'LIKE', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('nama_supplier')->get();

        return response()->json([
            'status' => 'success',
            'data' => $suppliers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'kontak_person' => 'nullable|string|max:255'
        ]);

        $supplier = Supplier::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier created successfully',
            'data' => $supplier
        ], 201);
    }

    public function show($id)
    {
        $supplier = Supplier::with('purchaseOrders')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $supplier
        ]);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'nama_supplier' => 'sometimes|string|max:255',
            'alamat' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'kontak_person' => 'nullable|string|max:255'
        ]);

        $supplier->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier updated successfully',
            'data' => $supplier
        ]);
    }

    public function toggleStatus($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update(['is_active' => !$supplier->is_active]);

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier status updated successfully',
            'data' => $supplier
        ]);
    }
}
