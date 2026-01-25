<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permission (WAJIB)
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /**
         * ==============================
         * 1️⃣ MASTER DATA (CRUD)
         * ==============================
         */
        $masterModules = [
            'category',
            'store',
            'customer',
            'supplier',
            'product',
            'discount',
            'vehicle',
            'driver',
            'brand',
            'unit',
        ];

        $masterActions = ['view', 'create', 'update', 'delete'];

        foreach ($masterModules as $module) {
            foreach ($masterActions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        /**
         * ==============================
         * 2️⃣ SALES (TRANSACTION FLOW)
         * ==============================
         */
        $salesPermissions = [
            'sales_order.create',
            'sales_order.submit',
            'sales_order.cancel',

            'billing.create',
            'collection.create',
        ];

        foreach ($salesPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /**
         * ==============================
         * 3️⃣ INVENTORY (READ ONLY)
         * ==============================
         */
        $inventoryPermissions = [
            'inventory.view',
        ];

        foreach ($inventoryPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /**
         * ==============================
         * 4️⃣ ROLES
         * ==============================
         */
        $superAdmin = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $staff = Role::firstOrCreate([
            'name' => 'staff',
            'guard_name' => 'web',
        ]);

        $viewer = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'web',
        ]);

        /**
         * ==============================
         * 5️⃣ ASSIGN PERMISSIONS
         * ==============================
         */

        // SUPER ADMIN = ALL
        $superAdmin->syncPermissions(Permission::all());

        // ADMIN = semua master + sales + inventory
        $admin->syncPermissions(
            Permission::whereNotIn('name', [
                'customer.delete', // contoh pengecualian
            ])->get()
        );

        // STAFF = transaksi + master non destructive
        $staff->syncPermissions(
            Permission::whereIn(
                'name',
                collect($masterModules)->flatMap(fn($m) => [
                    "$m.view",
                    "$m.create",
                    "$m.update",
                ])
                    ->merge([
                        'sales_order.create',
                        'sales_order.submit',
                        'billing.create',
                        'collection.create',
                        'inventory.view',
                    ])
            )->get()
        );

        // VIEWER = view only
        $viewer->syncPermissions(
            Permission::whereIn(
                'name',
                collect($masterModules)->map(fn($m) => "$m.view")
                    ->merge(['inventory.view'])
            )->get()
        );
    }
}