<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // SALES ORDERS
        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'so_number')) {
                $table->string('so_number', 20)->unique()->after('id');
            }
        });

        // BILLINGS
        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'billing_number')) {
                $table->string('billing_number', 20)->unique()->after('id');
            }
        });

        // COLLECTIONS
        Schema::table('collections', function (Blueprint $table) {
            if (!Schema::hasColumn('collections', 'collection_number')) {
                $table->string('collection_number', 20)->unique()->after('id');
            }
        });

        // REFUNDS
        Schema::table('refunds', function (Blueprint $table) {
            if (!Schema::hasColumn('refunds', 'refund_number')) {
                $table->string('refund_number', 20)->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        // SALES ORDERS
        Schema::table('sales_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sales_orders', 'so_number')) {
                $this->dropUniqueIfExists($table, 'sales_orders_so_number_unique');
                $table->dropColumn('so_number');
            }
        });

        // BILLINGS
        Schema::table('billings', function (Blueprint $table) {
            if (Schema::hasColumn('billings', 'billing_number')) {
                $this->dropUniqueIfExists($table, 'billings_billing_number_unique');
                $table->dropColumn('billing_number');
            }
        });

        // COLLECTIONS
        Schema::table('collections', function (Blueprint $table) {
            if (Schema::hasColumn('collections', 'collection_number')) {
                $this->dropUniqueIfExists($table, 'collections_collection_number_unique');
                $table->dropColumn('collection_number');
            }
        });

        // REFUNDS
        Schema::table('refunds', function (Blueprint $table) {
            if (Schema::hasColumn('refunds', 'refund_number')) {
                $this->dropUniqueIfExists($table, 'refunds_refund_number_unique');
                $table->dropColumn('refund_number');
            }
        });
    }

    private function dropUniqueIfExists(Blueprint $table, string $indexName): void
    {
        try {
            $table->dropUnique($indexName);
        } catch (\Throwable $e) {
            // index tidak ada → skip
        }
    }
};
