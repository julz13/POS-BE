<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_number', 20)->unique();
            $table->string('customer_name', 200)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('phone', 20)->nullable();
            $table->date('order_date');
            $table->date('expected_pickup')->nullable();
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('converted_transaction_id')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('so_number');
            $table->index('status');
            $table->index('customer_id');
            $table->index('order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
