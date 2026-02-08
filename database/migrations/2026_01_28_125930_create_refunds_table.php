<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sales_order_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('billing_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);

            $table->enum('status', [
                'approved',
                'rejected',
                'cancelled',
            ])->default('approved');

            $table->text('reason')->nullable();

            $table->timestamps();

            $table->unique('billing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
