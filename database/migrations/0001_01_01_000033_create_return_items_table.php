<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->string('product_name', 255);
            $table->string('sku', 50)->nullable();
            $table->string('unit', 30);
            $table->integer('original_qty');
            $table->integer('return_qty');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_discount_pct', 5, 2)->default(0);
            $table->decimal('refund_amount', 12, 2);
            $table->timestamps();

            $table->index('return_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
