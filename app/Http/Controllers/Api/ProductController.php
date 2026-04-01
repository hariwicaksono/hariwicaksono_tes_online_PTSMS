<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%$search%");
        }

        // Sorting
        $sortBy = $request->get('sortBy', 'created_at');
        $sortDesc = $request->boolean('sortDesc', true);
        $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

        // Pagination
        $perPage = $request->get('itemsPerPage', 10);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $product = Product::create([
            'name'  => $request->name,
            'price' => $request->price,
        ]);

        return response()->json([
            'message' => 'Product created',
            'data'    => $product
        ], 201);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $product->update([
            'name'  => $request->name,
            'price' => $request->price,
        ]);

        return response()->json([
            'message' => 'Product updated'
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted'
        ]);
    }
}