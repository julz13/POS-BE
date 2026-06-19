<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number', 20)->unique();
            $table->foreignId('po_id')->nullable()->constrained('purchase_orders');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->string('status', 20)->default('draft');
            $table->date('received_date');
            $table->string('invoice_number', 100)->nullable();
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->constrained('users');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index('grn_number');
            $table->index('po_id');
            $table->index('supplier_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_received_notes');
    }
};
