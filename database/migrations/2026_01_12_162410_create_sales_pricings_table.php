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
        Schema::create('sales_pricings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            // nullable = harga global, kalau diisi = harga khusus store
            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();

            $table->enum('customer_type', ['RETAIL', 'GROSIR', 'PROJECT']);

            // harga jual per meter
            $table->decimal('price_per_meter', 15, 2);

            // minimal qty (meter)
            $table->decimal('min_qty', 12, 3)->default(0.000);

            $table->date('valid_from');
            $table->date('valid_to')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // optional tapi bagus: cegah duplikat aturan aktif
            $table->unique(
                ['product_id', 'store_id', 'customer_type', 'min_qty', 'valid_from'],
                'uniq_sales_pricing_rule'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_pricings');
    }
};
