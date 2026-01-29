<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        /**
         * SALES ORDERS
         * SO26010001
         */
        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'so_number')) {
                $table->string('so_number', 20)
                    ->unique()
                    ->after('id');
            }
        });

        /**
         * BILLINGS
         * INV26010001
         */
        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'billing_number')) {
                $table->string('billing_number', 20)
                    ->unique()
                    ->after('id');
            }
        });

        /**
         * COLLECTIONS (Payments)
         * COL26010001
         */
        Schema::table('collections', function (Blueprint $table) {
            if (!Schema::hasColumn('collections', 'collection_number')) {
                $table->string('collection_number', 20)
                    ->unique()
                    ->after('id');
            }
        });

        /**
         * REFUNDS
         * RF26010001
         */
        Schema::table('refunds', function (Blueprint $table) {
            if (!Schema::hasColumn('refunds', 'refund_number')) {
                $table->string('refund_number', 20)
                    ->unique()
                    ->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sales_orders', 'so_number')) {
                $table->dropUnique(['so_number']);
                $table->dropColumn('so_number');
            }
        });

        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'billing_number')) {
                $table->dropUnique(['billing_number']);
                $table->dropColumn('billing_number');
            }
        });

        Schema::table('collections', function (Blueprint $table) {
            if (Schema::hasColumn('collections', 'collection_number')) {
                $table->dropUnique(['collection_number']);
                $table->dropColumn('collection_number');
            }
        });

        Schema::table('refunds', function (Blueprint $table) {
            if (Schema::hasColumn('refunds', 'refund_number')) {
                $table->dropUnique(['refund_number']);
                $table->dropColumn('refund_number');
            }
        });
    }
};
