<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'so_number')) {
                $table->string('so_number', 20)
                    ->nullable()
                    ->after('id');
            }
        });

        DB::table('sales_orders')
            ->whereNull('so_number')
            ->orWhere('so_number', '')
            ->orderBy('id')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    DB::table('sales_orders')
                        ->where('id', $order->id)
                        ->update([
                            'so_number' => \App\Services\Common\DocumentNumberService::generate(
                                'sales_orders',
                                'so_number',
                                'SO'
                            ),
                        ]);
                }
            });

        if (! $this->indexExists('sales_orders', 'sales_orders_so_number_unique')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->unique('so_number', 'sales_orders_so_number_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('sales_orders', 'sales_orders_so_number_unique')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropUnique('sales_orders_so_number_unique');
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $result = DB::select(
            "SHOW INDEX FROM {$table} WHERE Key_name = ?",
            [$index]
        );

        return count($result) > 0;
    }
};
