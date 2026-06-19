<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 20)->unique();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->timestamp('date')->useCurrent();
            $table->string('payment_method', 30);
            $table->decimal('amount', 12, 2);
            $table->string('reference_number', 100)->nullable();
            $table->string('voucher_code', 20)->nullable();
            $table->boolean('is_electronic')->default(false);
            $table->foreignId('cashier_id')->nullable()->constrained('users');
            $table->string('status', 20)->default('completed');
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users');
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index('payment_number');
            $table->index('transaction_id');
            $table->index('payment_method');
            $table->index('date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
