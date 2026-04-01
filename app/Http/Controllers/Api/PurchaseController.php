<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * List purchases
     */
    public function index(Request $request)
    {
        $query = Purchase::with('items');

        // 🔍 Search (by date atau total_price)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('date', 'like', "%$search%")
                    ->orWhere('total_price', 'like', "%$search%");
            });
        }

        // Sorting
        $sortBy   = $request->get('sortBy', 'created_at');
        $sortDesc = $request->boolean('sortDesc', true);
        $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

        // Pagination
        $perPage  = $request->get('itemsPerPage', 10);
        $purchases = $query->paginate($perPage);

        return response()->json($purchases);
    }

    /**
     * Store purchase + items
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            $total = 0;

            foreach ($request->items as $item) {
                $total += $item['qty'] * $item['price'];
            }

            $purchase = Purchase::create([
                'date' => $request->date,
                'total_price' => $total
            ]);

            foreach ($request->items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase berhasil disimpan',
                'data' => $purchase->load('items')
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show detail purchase
     */
    public function show($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $purchase
        ]);
    }

    /**
     * Update purchase
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            $purchase = Purchase::findOrFail($id);

            // hapus item lama
            $purchase->items()->delete();

            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['qty'] * $item['price'];
            }

            $purchase->update([
                'date' => $request->date,
                'total_price' => $total
            ]);

            foreach ($request->items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase berhasil diupdate',
                'data' => $purchase->load('items')
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete purchase
     */
    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase->delete(); // purchase_items ikut terhapus (cascade)

        return response()->json([
            'success' => true,
            'message' => 'Purchase berhasil dihapus'
        ]);
    }
}
