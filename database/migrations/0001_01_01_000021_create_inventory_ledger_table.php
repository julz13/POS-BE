<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->timestamp('date')->useCurrent();
            $table->string('type', 30);
            $table->string('reference_type', 30)->nullable();
            $table->bigInteger('reference_id')->nullable();
            $table->string('reference_number', 30)->nullable();
            $table->integer('qty_in')->default(0);
            $table->integer('qty_out')->default(0);
            $table->integer('balance_after');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('date');
            $table->index('type');
            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_ledger');
    }
};
