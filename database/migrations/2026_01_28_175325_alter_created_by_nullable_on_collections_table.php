<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            // hanya UBAH kolom, BUKAN add
            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')
                ->nullable(false)
                ->change();
        });
    }
};
