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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();          // SUP0001
            $table->string('name', 150);
            $table->string('phone', 20)->nullable();
            $table->string('address', 255)->nullable();

            // payment term untuk AP nanti (hari)
            $table->unsignedSmallInteger('payment_term_days')->default(0);

            // supplier preferensi ke store tertentu (opsional)
            $table->foreignId('default_store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
