<?php

namespace App\Http\Controllers;

use App\Models\Ruko;
use Illuminate\Http\Request;

class RukoController extends Controller
{
    public function index(Request $request)
    {
        $query = Ruko::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('kota')) {
            $query->where('kota', 'LIKE', "%{$request->kota}%");
        }

        $rukos = $query->orderBy('nama_ruko')->get();

        return response()->json([
            'status' => 'success',
            'data' => $rukos
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_ruko' => 'required|string|unique:rukos,kode_ruko',
            'nama_ruko' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kota' => 'required|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20'
        ]);

        $ruko = Ruko::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Ruko created successfully',
            'data' => $ruko
        ], 201);
    }

    public function show($id)
    {
        $ruko = Ruko::with(['users', 'inventories.product'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $ruko
        ]);
    }

    public function update(Request $request, $id)
    {
        $ruko = Ruko::findOrFail($id);

        $request->validate([
            'kode_ruko' => 'sometimes|string|unique:rukos,kode_ruko,' . $id,
            'nama_ruko' => 'sometimes|string|max:255',
            'alamat' => 'sometimes|string',
            'kota' => 'sometimes|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20'
        ]);

        $ruko->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Ruko updated successfully',
            'data' => $ruko
        ]);
    }

    public function toggleStatus($id)
    {
        $ruko = Ruko::findOrFail($id);
        $ruko->update(['is_active' => !$ruko->is_active]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ruko status updated successfully',
            'data' => $ruko
        ]);
    }
}