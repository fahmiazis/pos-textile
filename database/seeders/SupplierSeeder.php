<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'code' => 'SUP-001',
                'name' => 'PT Textile Nusantara',
                'phone' => '081200000001',
                'address' => 'Bandung',
                'payment_term_days' => 30,
                'default_store_id' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'code' => 'SUP-002',
                'name' => 'CV Kain Makmur',
                'phone' => '081200000002',
                'address' => 'Solo',
                'payment_term_days' => 14,
                'default_store_id' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'code' => 'SUP-003',
                'name' => 'PT Import Textile Asia',
                'phone' => '081200000003',
                'address' => 'Jakarta',
                'payment_term_days' => 45,
                'default_store_id' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
