<?php

// app/Http/Controllers/Api/MenuController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    // GET /api/menus → Ambil menu sesuai permission user
    public function index(Request $request)
    {
        $user = $request->user();
        $permissionKeys = $user->getPermissionKeys(); // contoh: ['dashboard.view', 'pages.view']

        $menus = Menu::where('is_active', true)
            ->whereNull('parent_id')
            ->where(function ($query) use ($permissionKeys) {
                $query->whereNull('permission_key')
                    ->orWhereIn('permission_key', $permissionKeys);
            })
            ->with(['children' => function ($query) use ($permissionKeys) {
                $query->where('is_active', true)
                    ->where(function ($q) use ($permissionKeys) {
                        $q->whereNull('permission_key')
                            ->orWhereIn('permission_key', $permissionKeys);
                    })
                    ->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        return response()->json($menus);
    }

    // GET /api/menus/all → Ambil semua menu untuk admin/CRUD
    public function all(Request $request)
    {
        $user = $request->user();
        $permissionKeys = $user->getPermissionKeys(); // contoh: ['dashboard.view', 'pages.view']

        $menus = Menu::whereNull('parent_id')
            ->where(function ($query) use ($permissionKeys) {
                $query->whereNull('permission_key')
                    ->orWhereIn('permission_key', $permissionKeys);
            })
            ->with(['children' => function ($query) use ($permissionKeys) {
                $query->where(function ($q) use ($permissionKeys) {
                        $q->whereNull('permission_key')
                            ->orWhereIn('permission_key', $permissionKeys);
                    })
                    ->orderBy('order');
            }])
            ->orderBy('order')
            ->get();
        return response()->json($menus);
    }

    // POST /api/menus → Simpan menu baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'icon' => 'required|nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'permission_key' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $menu = Menu::create($validator->validated());

        return response()->json(['message' => 'Menu created', 'menu' => $menu]);
    }

    // GET /api/menus/{id} → Ambil detail menu
    public function show($id)
    {
        $menu = Menu::with('children')->findOrFail($id);
        return response()->json($menu);
    }

    // PUT /api/menus/{id} → Update menu
    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'icon' => 'required|nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'permission_key' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id|not_in:' . $menu->id,
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $menu->update($validator->validated());

        return response()->json(['message' => 'Menu updated', 'menu' => $menu]);
    }

    // DELETE /api/menus/{id} → Hapus menu
    public function destroy($id)
    {
        $check = Menu::where(['id' => $id, 'is_system' => 1])->first();
        if ($check) {
            return response()->json(['message' => 'Menu cannot be deleted'], 403);
        }
        
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return response()->json(['message' => 'Menu deleted']);
    }

    public function reorder(Request $request)
    {
        $orders = $request->input('orders');

        foreach ($orders as $item) {
            Menu::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Menu order updated']);
    }

    public function reorderNested(Request $request)
    {
        $orders = $request->input('orders');

        foreach ($orders as $item) {
            Menu::where('id', $item['id'])->update([
                'order' => $item['order'],
                'parent_id' => $item['parent_id']
            ]);
        }

        return response()->json(['message' => 'Menu reordered successfully']);
    }
}
