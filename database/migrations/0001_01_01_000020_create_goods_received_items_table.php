<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_received_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('goods_received_notes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('po_item_id')->nullable()->constrained('purchase_order_items');
            $table->integer('received_qty');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->date('expiry_date')->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->timestamps();

            $table->index('grn_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_received_items');
    }
};
