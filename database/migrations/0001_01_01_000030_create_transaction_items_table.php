<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->string('product_name', 255);
            $table->string('sku', 50);
            $table->string('category', 100)->nullable();
            $table->string('unit', 30);
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('original_unit_price', 12, 2)->nullable();
            $table->decimal('line_discount_pct', 5, 2)->default(0);
            $table->string('line_discount_type', 30)->nullable();
            $table->decimal('line_discount_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->string('tax_code', 20)->nullable();
            $table->boolean('price_overridden')->default(false);
            $table->foreignId('price_override_approved_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
