<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            $table->boolean('is_pkp')
                ->default(false)
                ->after('is_active');

            $table->string('nik')
                ->nullable()
                ->after('is_pkp');

            $table->string('sppkp')
                ->nullable()
                ->after('nik');

            $table->text('npwp_address')
                ->nullable()
                ->after('sppkp');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            $table->dropColumn([
                'is_pkp',
                'nik',
                'sppkp',
                'npwp_address',
            ]);
        });
    }
};
