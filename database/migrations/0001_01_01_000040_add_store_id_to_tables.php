<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add store_id to categories
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to product_barcodes
        Schema::table('product_barcodes', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to purchase_orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to goods_received_notes
        Schema::table('goods_received_notes', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to inventory_ledger
        Schema::table('inventory_ledger', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to stock_takes
        Schema::table('stock_takes', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to shifts
        Schema::table('shifts', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to vouchers
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to discount_types
        Schema::table('discount_types', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to quotes
        Schema::table('quotes', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to sales_orders
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });

        // Add store_id to audit_log
        Schema::table('audit_log', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('categories_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('products_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('product_barcodes', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('product_barcodes_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('suppliers_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('purchase_orders_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('goods_received_notes', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('goods_received_notes_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('inventory_ledger', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('inventory_ledger_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('stock_takes', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('stock_takes_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('transactions_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('shifts_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('vouchers_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('discount_types', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('discount_types_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('quotes_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('sales_orders_store_id_foreign');
            $table->dropColumn('store_id');
        });

        Schema::table('audit_log', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('audit_log_store_id_foreign');
            $table->dropColumn('store_id');
        });
    }
};
