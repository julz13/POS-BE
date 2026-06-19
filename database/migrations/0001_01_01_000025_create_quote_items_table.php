<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->string('product_name', 255);
            $table->string('unit', 30);
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_discount_pct', 5, 2)->default(0);
            $table->decimal('line_discount_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index('quote_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
