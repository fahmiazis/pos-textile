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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_id')->constrained('inventories');

            $table->enum('type', [
                'in',        // GR / purchase
                'reserve',   // SO submit
                'release',   // cancel SO
                'out'        // paid / delivery
            ]);

            $table->decimal('qty', 12, 3);

            $table->string('reference_type', 50);
            $table->unsignedBigInteger('reference_id');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};