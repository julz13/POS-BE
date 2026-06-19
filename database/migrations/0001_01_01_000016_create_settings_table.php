<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name', 255)->default('PabiliPOS Store');
            $table->text('store_address')->nullable();
            $table->string('store_phone', 20)->nullable();
            $table->string('store_email', 255)->nullable();
            $table->string('store_tin', 20)->nullable();
            $table->char('currency', 3)->default('PHP');
            $table->string('timezone', 50)->default('Asia/Manila');
            $table->text('receipt_header')->nullable();
            $table->text('receipt_footer')->default('Thank you for shopping!');
            $table->boolean('tax_enabled')->default(false);
            $table->string('tax_name', 20)->default('VAT');
            $table->decimal('tax_rate', 5, 2)->default(12);
            $table->string('tax_type', 20)->default('exclusive');
            $table->decimal('max_cashier_discount_pct', 5, 2)->default(10);
            $table->decimal('max_manager_discount_pct', 5, 2)->default(30);
            $table->decimal('require_manager_approval_above_pct', 5, 2)->default(5);
            $table->boolean('round_to_two_decimals')->default(true);
            $table->boolean('allow_negative_stock')->default(false);
            $table->boolean('require_shift_for_sales')->default(true);
            $table->boolean('show_cashier_on_receipt')->default(true);
            $table->boolean('show_customer_on_receipt')->default(true);
            $table->boolean('show_tax_breakdown_on_receipt')->default(true);
            $table->boolean('show_discount_breakdown_on_receipt')->default(true);
            $table->decimal('loyalty_points_per_peso', 5, 3)->default(0.1);
            $table->decimal('loyalty_redeem_rate', 5, 3)->default(1.0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
