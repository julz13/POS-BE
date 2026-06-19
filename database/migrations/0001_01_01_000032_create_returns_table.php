<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 20)->unique();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->timestamp('date')->useCurrent();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('customer_name', 200)->nullable();
            $table->string('return_type', 20);
            $table->string('reason', 200);
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->string('refund_method', 30)->nullable();
            $table->string('status', 20)->default('pending');
            $table->boolean('stock_restored')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('return_number');
            $table->index('transaction_id');
            $table->index('status');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
