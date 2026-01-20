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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();          // DISC10, LEBARAN25
            $table->string('name', 100);

            // jenis diskon
            $table->enum('discount_type', ['PERCENT', 'FIXED']);

            // nilai diskon (PERCENT = 10.00 artinya 10%)
            // FIXED = potongan nominal
            $table->decimal('discount_value', 10, 2);

            // scope promo
            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // optional: cegah promo dobel di periode sama
            $table->index(['store_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
