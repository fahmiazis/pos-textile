<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sales_orders', 'subtotal_amount')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->decimal('subtotal_amount', 15, 2)->default(0)->after('total_qty');
            });
        }

        if (!Schema::hasColumn('sales_orders', 'tax_rate')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->decimal('tax_rate', 5, 4)->default(0.11)->after('subtotal_amount');
            });
        }

        if (!Schema::hasColumn('sales_orders', 'tax_amount')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
            });
        }

        DB::table('sales_orders')->update([
            'subtotal_amount' => DB::raw('total_amount'),
            'tax_rate' => 0.11,
            'tax_amount' => 0
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_orders', 'tax_amount')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropColumn('tax_amount');
            });
        }

        if (Schema::hasColumn('sales_orders', 'tax_rate')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropColumn('tax_rate');
            });
        }

        if (Schema::hasColumn('sales_orders', 'subtotal_amount')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropColumn('subtotal_amount');
            });
        }
    }
};
