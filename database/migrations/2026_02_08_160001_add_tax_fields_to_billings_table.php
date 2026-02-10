<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('billings', 'subtotal_amount')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->decimal('subtotal_amount', 15, 2)->default(0)->after('billing_date');
            });
        }

        if (!Schema::hasColumn('billings', 'tax_rate')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->decimal('tax_rate', 5, 4)->default(0.11)->after('subtotal_amount');
            });
        }

        if (!Schema::hasColumn('billings', 'tax_amount')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
            });
        }

        DB::table('billings')->update([
            'subtotal_amount' => DB::raw('total_amount'),
            'tax_rate' => 0.11,
            'tax_amount' => 0
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('billings', 'tax_amount')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->dropColumn('tax_amount');
            });
        }

        if (Schema::hasColumn('billings', 'tax_rate')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->dropColumn('tax_rate');
            });
        }

        if (Schema::hasColumn('billings', 'subtotal_amount')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->dropColumn('subtotal_amount');
            });
        }
    }
};
