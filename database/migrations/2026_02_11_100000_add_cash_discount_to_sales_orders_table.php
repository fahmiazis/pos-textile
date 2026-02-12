<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('sales_orders', 'cash_discount')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->decimal('cash_discount', 15, 2)->default(0)->after('subtotal_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sales_orders', 'cash_discount')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropColumn('cash_discount');
            });
        }
    }
};
