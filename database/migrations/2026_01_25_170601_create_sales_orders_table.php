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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();

            $table->string('so_number', 30)->unique();
            $table->foreignId('store_id')->constrained('stores');
            $table->foreignId('customer_id')->constrained('customers');

            $table->enum('status', [
                'draft',
                'submitted',
                'cancelled',
                'completed'
            ])->default('draft');

            $table->date('order_date');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->decimal('total_qty', 12, 3)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};