<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\MenuController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('jwt.refresh');

Route::get('/settings/app', function () {
    $settings = \App\Models\Setting::pluck('value', 'key');
    return response()->json($settings);
});

Route::get('/pages/slug/{slug}', [PageController::class, 'showBySlug']);

// Protected Routes (require JWT token)
Route::middleware('auth:api')->group(function () {
    Route::get('laravel-version', function () {
        $laravel = app();
        echo "v" . $laravel::VERSION;
    });
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'index']);

    // User Management
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:user.view');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:user.create');
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:user.view');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:user.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('permission:user.delete');
    Route::patch('/users/{id}/status', [UserController::class, 'toggleStatus'])->middleware('permission:user.update');
    Route::put('/users/{id}/roles', [UserController::class, 'updateRoles'])->middleware('permission:user.update');

    Route::get('/profile', function () {
        $user = auth()->user();
        return response()->json([
            'user' => $user,
            'roles' => $user->roles->pluck('name'),
        ]);
    });
    Route::put('/profile', [UserController::class, 'updateProfile']);

    // Permission Management
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);

    // Role Management
    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:role.view');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:role.create');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->middleware('permission:role.view');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('permission:role.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:role.delete');
    Route::put('/roles/{id}/permissions', [RoleController::class, 'updatePermissions'])->middleware('permission:role.update');

    // Settings Management
    Route::get('/settings', [SettingsController::class, 'index'])->middleware('permission:setting.view');
    Route::post('/settings/{id}', [SettingsController::class, 'update'])->middleware('permission:setting.update');

    Route::get('/logs', [\App\Http\Controllers\Api\LogController::class, 'index'])->middleware('permission:log.view');
    Route::get('/logs/export', [\App\Http\Controllers\Api\LogController::class, 'export'])->middleware('permission:log.view');
    Route::get('/logs/export-pdf', [\App\Http\Controllers\Api\LogController::class, 'exportPdf'])->middleware('permission:log.view');
    Route::get('/logs/print', [\App\Http\Controllers\Api\LogController::class, 'print'])->middleware('permission:log.view');

    Route::get('/backups', [BackupController::class, 'index'])->middleware('permission:backup.view');
    Route::post('/backups', [BackupController::class, 'store'])->middleware('permission:backup.create');
    Route::get('/backups/download/{filename}', [BackupController::class, 'download'])->middleware('permission:backup.download');
    Route::delete('/backups/{id}', [BackupController::class, 'destroy'])->middleware('permission:backup.delete');
    Route::get('/backups/check-today', [BackupController::class, 'checkToday'])->middleware('permission:backup.view');

    Route::get('/pages', [PageController::class, 'index'])->middleware('permission:page.view');
    Route::post('/pages', [PageController::class, 'store'])->middleware('permission:page.create');
    Route::get('/pages/{id}', [PageController::class, 'show'])->middleware('permission:page.view');
    Route::put('/pages/{id}', [PageController::class, 'update'])->middleware('permission:page.update');
    Route::delete('/pages/{id}', [PageController::class, 'destroy'])->middleware('permission:page.delete');

    Route::get('/menus', [MenuController::class, 'index']);
    Route::get('/menus/all', [MenuController::class, 'all'])->middleware('permission:menu.view'); // menu untuk admin (semua)
    Route::post('/menus', [MenuController::class, 'store'])->middleware('permission:menu.create');
    Route::get('/menus/{id}', [MenuController::class, 'show'])->middleware('permission:menu.view');
    Route::put('/menus/{id}', [MenuController::class, 'update'])->middleware('permission:menu.update');
    Route::delete('/menus/{id}', [MenuController::class, 'destroy'])->middleware('permission:menu.delete');
    Route::post('/menus/reorder', [MenuController::class, 'reorder'])->middleware('permission:menu.update');
    Route::post('/menus/reorder-nested', [MenuController::class, 'reorderNested'])->middleware('permission:menu.update');

    // Product Management
    Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index'])->middleware('permission:product.view');
    Route::post('/product/create', [\App\Http\Controllers\Api\ProductController::class, 'store'])->middleware('permission:product.create');
    Route::get('/product/{id}', [\App\Http\Controllers\Api\ProductController::class, 'show'])->middleware('permission:product.view');
    Route::put('/product/update/{id}', [\App\Http\Controllers\Api\ProductController::class, 'update'])->middleware('permission:product.update');
    Route::delete('/product/delete/{id}', [\App\Http\Controllers\Api\ProductController::class, 'destroy'])->middleware('permission:product.delete');

    // Purchase Management
    Route::get('/purchases', [\App\Http\Controllers\Api\PurchaseController::class, 'index'])->middleware('permission:purchase.view');
    Route::post('/purchase/create', [\App\Http\Controllers\Api\PurchaseController::class, 'store'])->middleware('permission:purchase.create');
    Route::get('/purchases/{id}', [\App\Http\Controllers\Api\PurchaseController::class, 'show'])->middleware('permission:purchase.view');
    Route::put('/purchase/update/{id}', [\App\Http\Controllers\Api\PurchaseController::class, 'update'])->middleware('permission:purchase.update');
    Route::delete('/purchase/delete/{id}', [\App\Http\Controllers\Api\PurchaseController::class, 'destroy'])->middleware('permission:purchase.delete');

    Route::get('/report/purchases', [\App\Http\Controllers\Api\ReportPurchaseController::class, 'index']);
});
