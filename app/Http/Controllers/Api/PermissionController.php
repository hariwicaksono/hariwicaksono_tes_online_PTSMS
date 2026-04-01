<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        $perm = Permission::create(['name' => $request->name]);

        return response()->json(['message' => 'Permission created', 'permission' => $perm], 201);
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        // Cek apakah permission ini masih terhubung ke role manapun
        if ($permission->roles()->exists()) {
            return response()->json([
                'message' => 'The permission is currently in use by a Role and cannot be removed'
            ], 422);
        }

        // Jika tidak ada role yang terhubung, lanjutkan
        $permission->roles()->detach();
        $permission->delete();

        return response()->json(['message' => 'Permission deleted']);
    }
}
