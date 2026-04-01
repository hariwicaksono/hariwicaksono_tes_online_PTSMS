<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | Roles
            |--------------------------------------------------------------------------
            */
            $adminRoleId = DB::table('roles')->firstOrCreate(
                ['name' => 'admin'],
                ['created_at' => now(), 'updated_at' => now()]
            )->id;

            $userRoleId = DB::table('roles')->firstOrCreate(
                ['name' => 'user'],
                ['created_at' => now(), 'updated_at' => now()]
            )->id;

            /*
            |--------------------------------------------------------------------------
            | Permissions
            |--------------------------------------------------------------------------
            */
            $permissions = [
                'user.view','user.create','user.update','user.delete',
                'setting.view','setting.update',
                'role.view','role.create','role.update','role.delete',
                'permission.view','permission.create','permission.delete',
                'log.view',
                'backup.view','backup.create','backup.restore','backup.download','backup.delete',
                'page.view','page.create','page.update','page.delete',
                'menu.view','menu.create','menu.update','menu.delete',
                'product.view','product.create','product.update','product.delete',
                'purchase.view','purchase.create','purchase.update','purchase.delete',
            ];

            $permissionIds = [];

            foreach ($permissions as $perm) {
                $permissionIds[$perm] = DB::table('permissions')->firstOrCreate(
                    ['name' => $perm],
                    ['created_at' => now(), 'updated_at' => now()]
                )->id;
            }

            /*
            |--------------------------------------------------------------------------
            | Admin → ALL Permissions
            |--------------------------------------------------------------------------
            */
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->updateOrInsert(
                    [
                        'role_id' => $adminRoleId,
                        'permission_id' => $permissionId,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | User → VIEW Permissions Only
            |--------------------------------------------------------------------------
            */
            foreach ($permissionIds as $name => $permissionId) {
                if (str_ends_with($name, '.view')) {
                    DB::table('permission_role')->updateOrInsert(
                        [
                            'role_id' => $userRoleId,
                            'permission_id' => $permissionId,
                        ]
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Assign Role to Users
            |--------------------------------------------------------------------------
            */
            $admin = User::where('email', 'admin@test.com')->first();
            if ($admin) {
                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $admin->id],
                    ['role_id' => $adminRoleId]
                );
            }

            $user = User::where('email', 'user@test.com')->first();
            if ($user) {
                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $user->id],
                    ['role_id' => $userRoleId]
                );
            }
        });
    }
}