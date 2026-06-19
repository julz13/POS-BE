<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('default_pct', 5, 2)->default(0);
            $table->decimal('max_pct', 5, 2)->default(100);
            $table->boolean('is_custom_pct')->default(false);
            $table->boolean('requires_id')->default(false);
            $table->boolean('requires_reason')->default(false);
            $table->boolean('requires_override')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('color', 20)->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_types');
    }
};
