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
        // WAJIB: reset cache permission
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        | MASTER DATA (CRUD)
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

        /*
        | SALES
        */
        $salesPermissions = [
            'sales_order.view',
            'sales_order.create',
            'sales_order.update',
            'sales_order.submit',
            'sales_order.cancel',

            'billing.view',
            'billing.create',

            'collection.view',
            'collection.create',

            'refund.create',
        ];

        foreach ($salesPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        | PURCHASE
        */
        $purchasePermissions = [
            'purchase_order.view',
            'purchase_order.create',
            'purchase_order.update',
            'purchase_order.submit',
            'purchase_order.cancel',
            'purchase_order.receive',

            'purchase_billing.view',
            'purchase_billing.create',

            'purchase_payment.view',
            'purchase_payment.create',
        ];

        foreach ($purchasePermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        | INVENTORY
        */
        $inventoryPermissions = [
            'inventory.view',
            'inventory.manage',
        ];

        foreach ($inventoryPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        | ROLES
        */
        $superAdmin = Role::firstOrCreate([
            'name' => 'superadmin',
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

        /*
        | ASSIGN PERMISSIONS
        */

        // SUPERADMIN = ALL
        $superAdmin->syncPermissions(Permission::all());

        // ADMIN = almost all
        $admin->syncPermissions(
            Permission::whereNotIn('name', [
                'customer.delete',
            ])->get()
        );

        // STAFF
        $staff->syncPermissions(
            Permission::whereIn(
                'name',
                collect($masterModules)->flatMap(fn ($m) => [
                    "{$m}.view",
                    "{$m}.create",
                    "{$m}.update",
                ])->merge([
                    'sales_order.view',
                    'sales_order.create',
                    'sales_order.submit',

                    'purchase_order.view',
                    'purchase_order.create',
                    'purchase_order.submit',

                    'purchase_billing.view',
                    'purchase_billing.create',

                    'purchase_payment.view',
                    'purchase_payment.create',

                    'billing.view',
                    'billing.create',

                    'collection.view',
                    'collection.create',

                    'inventory.view',
                ])
            )->get()
        );

        // VIEWER = READ ONLY
        $viewer->syncPermissions(
            Permission::whereIn(
                'name',
                collect($masterModules)->map(fn ($m) => "{$m}.view")
                    ->merge([
                        'sales_order.view',
                        'purchase_order.view',
                        'purchase_billing.view',
                        'purchase_payment.view',
                        'billing.view',
                        'collection.view',
                        'inventory.view',
                    ])
            )->get()
        );
    }
}
