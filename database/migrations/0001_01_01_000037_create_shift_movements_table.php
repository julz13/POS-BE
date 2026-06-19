<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->string('type', 10);
            $table->decimal('amount', 12, 2);
            $table->string('reason', 255);
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index('shift_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_movements');
    }
};
