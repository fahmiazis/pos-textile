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
        if (!Schema::hasColumn('sales_orders', 'tax_included')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->boolean('tax_included')->default(false)->after('cash_discount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sales_orders', 'tax_included')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropColumn('tax_included');
            });
        }
    }
};
