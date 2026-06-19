<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->decimal('original_amount', 12, 2);
            $table->decimal('remaining_balance', 12, 2);
            $table->string('status', 20)->default('active');
            $table->timestamp('issued_at')->useCurrent();
            $table->date('expires_at')->nullable();
            $table->foreignId('issued_by')->constrained('users');
            $table->string('issued_to_name', 200)->nullable();
            $table->foreignId('issued_to_customer_id')->nullable()->constrained('customers');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
