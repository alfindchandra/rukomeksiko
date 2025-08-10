<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category']);

        // Apply filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'LIKE', "%{$search}%")
                  ->orWhere('kode_barang', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->paginate(20);

        return response()->json([
            'status' => 'error',
            'message' => 'Cannot delete product with existing stock'
        ], 400);
        

        $product->update(['is_active' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product deactivated successfully'
        ]);
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product status updated successfully',
            'data' => $product
        ]);
    }
}
