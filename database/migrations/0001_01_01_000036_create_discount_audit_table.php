<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_audit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions');
            $table->foreignId('discount_type_id')->nullable()->constrained('discount_types');
            $table->string('type_name', 100);
            $table->string('scope', 20);
            $table->foreignId('transaction_item_id')->nullable()->constrained('transaction_items');
            $table->string('product_name', 255)->nullable();
            $table->foreignId('applied_by')->constrained('users');
            $table->timestamp('applied_at')->useCurrent();
            $table->decimal('discount_pct', 5, 2);
            $table->decimal('original_amount', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('final_amount', 12, 2);
            $table->text('reason')->nullable();
            $table->string('id_number', 50)->nullable();
            $table->foreignId('overridden_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('applied_by');
            $table->index('applied_at');
            $table->index('discount_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_audit');
    }
};
