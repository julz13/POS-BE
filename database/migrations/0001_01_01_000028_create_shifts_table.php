<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('shift_number', 20)->unique();
            $table->foreignId('cashier_id')->constrained('users');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->decimal('cash_sales', 12, 2)->default(0);
            $table->decimal('change_given', 12, 2)->default(0);
            $table->decimal('cash_in_total', 12, 2)->default(0);
            $table->decimal('cash_out_total', 12, 2)->default(0);
            $table->decimal('expected_cash', 12, 2)->nullable();
            $table->decimal('counted_cash', 12, 2)->nullable();
            $table->decimal('variance', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();

            $table->index('shift_number');
            $table->index('cashier_id');
            $table->index('status');
            $table->index('opened_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
