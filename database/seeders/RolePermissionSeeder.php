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
         * 1️⃣ Definisi module & action
         */
        $modules = [
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

        $actions = ['view', 'create', 'update', 'delete'];

        /**
         * 2️⃣ Generate permissions
         */
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        /**
         * 3️⃣ Buat roles
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
         * 4️⃣ Assign permissions ke role
         */

        // Super Admin = ALL
        $superAdmin->syncPermissions(Permission::all());

        // Admin = ALL kecuali delete tertentu (contoh fleksibel)
        $admin->syncPermissions(
            Permission::whereNotIn('name', [
                'customer.delete',
            ])->get()
        );

        // Staff = view + create + update
        $staff->syncPermissions(
            Permission::whereIn('name', collect($modules)->flatMap(fn($m) => [
                "$m.view",
                "$m.create",
                "$m.update",
            ]))->get()
        );

        // Viewer = view only
        $viewer->syncPermissions(
            Permission::whereIn('name', collect($modules)->map(fn($m) => "$m.view"))->get()
        );
    }
}
