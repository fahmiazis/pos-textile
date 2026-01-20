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
        Schema::create('purchase_pricings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            // harga beli per meter (modal)
            $table->decimal('price_per_meter', 15, 2);

            // minimal qty pembelian (meter)
            $table->decimal('min_qty', 12, 3)->default(0.000);

            $table->date('valid_from');
            $table->date('valid_to')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // cegah aturan beli dobel
            $table->unique(
                ['product_id', 'supplier_id', 'min_qty', 'valid_from'],
                'uniq_purchase_pricing_rule'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_pricings');
    }
};
