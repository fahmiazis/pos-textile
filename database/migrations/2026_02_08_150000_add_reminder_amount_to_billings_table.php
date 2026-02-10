<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('billings', 'reminder_amount')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->decimal('reminder_amount', 15, 2)->default(0)->after('paid_amount');
            });
        }

        DB::table('billings')->update([
            'reminder_amount' => DB::raw('total_amount - paid_amount')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('billings', 'reminder_amount')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->dropColumn('reminder_amount');
            });
        }
    }
};
