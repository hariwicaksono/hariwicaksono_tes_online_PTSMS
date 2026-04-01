<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        // Ambil semua role beserta permission-nya
        $roles = Role::with('permissions')->get();

        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        $role = Role::create([
            'name' => $request->name
        ]);

        return response()->json(['message' => 'Role created', 'role' => $role], 201);
    }

    public function show($id)
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id
        ]);

        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);

        return response()->json(['message' => 'Role updated', 'role' => $role]);
    }

    public function destroy($id)
    {
        if (in_array($id, [1, 2])) {
            return response()->json(['message' => 'The role cannot be deleted'], 403);
        }

        $role = Role::findOrFail($id);

        // Cek apakah role terhubung dengan user
        if ($role->users()->exists()) {
            return response()->json(['message' => 'The role is currently in use by the user and cannot be deleted'], 422);
        }
        
        $role->delete();

        return response()->json(['message' => 'Role deleted']);
    }

    // Assign permission ke role
    public function updatePermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::findOrFail($id);

        $permissionIds = Permission::whereIn('name', $request->permissions)->pluck('id');
        $role->permissions()->sync($permissionIds);

        return response()->json(['message' => 'Permissions updated']);
    }
}
//