<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /**
             * UNITS (UOM)
             */
            $meterId = DB::table('units')->insertGetId([
                'code' => 'MTR',
                'name' => 'Meter',
                'base_unit_id' => null,
                'multiplier' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $rollId = DB::table('units')->insertGetId([
                'code' => 'ROLL',
                'name' => 'Roll',
                'base_unit_id' => $meterId,
                'multiplier' => 50, // 1 roll = 50 meter
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /**
             * BRAND
             */
            $brandId = DB::table('brands')->insertGetId([
                'code' => 'TXT',
                'name' => 'Textile Local',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /**
             * CATEGORY
             */
            $categoryId = DB::table('categories')->insertGetId([
                'code' => 'KAIN',
                'name' => 'Kain',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /**
             * STORE
             */
            $storeId = DB::table('stores')->insertGetId([
                'code' => 'TOKO-01',
                'name' => 'Toko Textile Utama',
                'phone' => '08123456789',
                'address' => 'Pasar Textile',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /**
             * CUSTOMERS
             */
            DB::table('customers')->insert([
                [
                    'code' => 'CUST-001',
                    'name' => 'Konveksi Jaya',
                    'phone' => '0811111111',
                    'address' => 'Jakarta',
                    'customer_type' => 'GROSIR',
                    'default_store_id' => $storeId,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'CUST-002',
                    'name' => 'Retail Sinar',
                    'phone' => '0822222222',
                    'address' => 'Bandung',
                    'customer_type' => 'RETAIL',
                    'default_store_id' => $storeId,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);



            /**
             * PRODUCTS (KAIN)
             */
            $productKatunId = DB::table('products')->insertGetId([
                'sku' => 'KAIN-KATUN-01',
                'name' => 'Kain Katun Premium',
                'brand_id' => $brandId,
                'base_uom_id' => $meterId,
                'category_id' => $categoryId,
                'description' => 'Kain katun kualitas premium',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /**
             * SALES PRICING
             */
            DB::table('sales_pricings')->insert([
                'product_id' => $productKatunId,
                'store_id' => $storeId,
                'customer_type' => 'GROSIR',
                'price_per_meter' => 25000,
                'min_qty' => 10,
                'valid_from' => now()->toDateString(),
                'valid_to' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            /**
             * INVENTORY INITIAL
             */
            DB::table('inventories')->insert([
                'store_id' => $storeId,
                'product_id' => $productKatunId,
                'stock_on_hand' => 1000,   // 1000 meter
                'stock_reserved' => 0,
                'stock_available' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}