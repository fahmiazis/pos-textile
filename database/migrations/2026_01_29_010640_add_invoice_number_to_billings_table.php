<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('billings', 'invoice_number')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->string('invoice_number', 30)
                    ->unique()
                    ->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('billings', 'invoice_number')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->dropUnique(['invoice_number']);
                $table->dropColumn('invoice_number');
            });
        }
    }
};
