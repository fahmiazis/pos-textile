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
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_billing_id')
                ->constrained('purchase_billings');

            $table->foreignId('supplier_id')
                ->constrained('suppliers');

            $table->foreignId('store_id')
                ->constrained('stores');

            $table->date('payment_date');
            $table->decimal('amount', 14, 2);

            $table->string('payment_method')->nullable(); // cash / transfer
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
