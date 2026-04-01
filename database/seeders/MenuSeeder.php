<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $dashboard = DB::table('menus')->firstOrCreate(
                ['title' => 'Dashboard'],
                [
                    'icon' => 'mdi-view-dashboard',
                    'route' => '/dashboard',
                    'permission_key' => null,
                    'parent_id' => null,
                    'order' => 0,
                    'is_active' => 1,
                    'is_system' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $product = DB::table('menus')->firstOrCreate(
                ['title' => 'Product'],
                [
                    'icon' => 'mdi-package-variant-closed',
                    'route' => '/product',
                    'permission_key' => 'product.view',
                    'parent_id' => null,
                    'order' => 1,
                    'is_active' => 1,
                    'is_system' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $purchase = DB::table('menus')->firstOrCreate(
                ['title' => 'Purchase'],
                [
                    'icon' => 'mdi-cart',
                    'route' => '/purchases',
                    'permission_key' => 'purchase.view',
                    'parent_id' => null,
                    'order' => 2,
                    'is_active' => 1,
                    'is_system' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $report = DB::table('menus')->firstOrCreate(
                ['title' => 'Report'],
                [
                    'icon' => 'mdi-file-table',
                    'route' => '/report',
                    'permission_key' => null,
                    'parent_id' => null,
                    'order' => 3,
                    'is_active' => 1,
                    'is_system' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $users = DB::table('menus')->firstOrCreate(
                ['title' => 'Users'],
                [
                    'icon' => 'mdi-account-multiple',
                    'route' => null,
                    'permission_key' => 'user.view',
                    'parent_id' => null,
                    'order' => 4,
                    'is_active' => 1,
                    'is_system' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('menus')->firstOrCreate(
                ['title' => 'User List'],
                [
                    'icon' => 'mdi-account',
                    'route' => '/users',
                    'permission_key' => 'user.view',
                    'parent_id' => $users->id,
                    'order' => 0,
                    'is_active' => 1,
                    'is_system' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('menus')->firstOrCreate(
                ['title' => 'Roles'],
                [
                    'icon' => 'mdi-account-check',
                    'route' => '/roles',
                    'permission_key' => 'role.view',
                    'parent_id' => $users->id,
                    'order' => 1,
                    'is_active' => 1,
                    'is_system' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('menus')->firstOrCreate(
                ['title' => 'Permissions'],
                [
                    'icon' => 'mdi-account-details',
                    'route' => '/permissions',
                    'permission_key' => 'permission.view',
                    'parent_id' => $users->id,
                    'order' => 2,
                    'is_active' => 1,
                    'is_system' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $system = DB::table('menus')->firstOrCreate(
                ['title' => 'System'],
                [
                    'icon' => 'mdi-cog-box',
                    'route' => null,
                    'permission_key' => 'setting.view',
                    'parent_id' => null,
                    'order' => 5,
                    'is_active' => 1,
                    'is_system' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $systemMenus = [
                ['Settings', 'mdi-cog', '/settings', 'setting.update', 0],
                ['Menu', 'mdi-format-list-bulleted-square', '/menus', 'menu.view', 1],
                ['Log Activity', 'mdi-database-eye', '/logs', 'log.view', 2],
                ['Backup DB', 'mdi-database', '/backups', 'backup.view', 3],
            ];

            foreach ($systemMenus as $menu) {
                DB::table('menus')->firstOrCreate(
                    ['title' => $menu[0]],
                    [
                        'icon' => $menu[1],
                        'route' => $menu[2],
                        'permission_key' => $menu[3],
                        'parent_id' => $system->id,
                        'order' => $menu[4],
                        'is_active' => 1,
                        'is_system' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }
}