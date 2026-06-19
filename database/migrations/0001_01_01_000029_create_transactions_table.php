<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 20)->unique();
            $table->timestamp('date')->useCurrent();
            $table->foreignId('cashier_id')->constrained('users');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('customer_name', 200)->nullable();
            $table->foreignId('shift_id')->nullable()->constrained('shifts');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('line_discounts_total', 12, 2)->default(0);
            $table->decimal('transaction_discount_pct', 5, 2)->default(0);
            $table->decimal('transaction_discount_amount', 12, 2)->default(0);
            $table->string('transaction_discount_type', 30)->nullable();
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('tax_type', 20)->default('exclusive');
            $table->decimal('total', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('change_given', 12, 2)->default(0);
            $table->string('status', 20)->default('completed');
            $table->string('source', 20)->nullable()->default('pos');
            $table->bigInteger('source_id')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users');
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index('receipt_number');
            $table->index('date');
            $table->index('cashier_id');
            $table->index('customer_id');
            $table->index('shift_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
