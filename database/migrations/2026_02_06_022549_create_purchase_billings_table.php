<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('purchase_billings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->string('billing_number')->unique();

            $table->date('billing_date');
            $table->decimal('total_amount', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('remaining_amount', 14, 2);

            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');

            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_billings');
    }
};
