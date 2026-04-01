<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'users' => [
                'title' => User::count(),
                'subtitle' => 'Users',
                'icon' => 'mdi-account',
                'color' => 'primary'
            ], 
            'roles' => [
                'title' => Role::count(),
                'subtitle' => 'Roles',
                'icon' => 'mdi-account-check',
                'color' => 'success'
            ], 
            'permissions' => [
                'title' => Permission::count(),
                'subtitle' => 'Permissions',
                'icon' => 'mdi-account-details',
                'color' => 'warning'
            ], 
            'logs' => [
                'title' => ActivityLog::count(),
                'subtitle' => 'Logs',
                'icon' => 'mdi-database-eye',
                'color' => 'error'
            ], 
        ]);
    }
}
