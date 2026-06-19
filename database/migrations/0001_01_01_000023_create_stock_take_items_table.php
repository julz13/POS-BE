<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_take_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_take_id')->constrained('stock_takes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('system_qty');
            $table->integer('counted_qty')->nullable();
            $table->integer('variance')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('stock_take_id');
            $table->index('product_id');
            $table->unique(['stock_take_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_take_items');
    }
};
