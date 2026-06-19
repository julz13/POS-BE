<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp')->useCurrent();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('user_name', 200);
            $table->string('user_role', 20)->nullable();
            $table->string('action', 50);
            $table->string('module', 50);
            $table->text('description');
            $table->string('reference_type', 30)->nullable();
            $table->bigInteger('reference_id')->nullable();
            $table->string('reference_number', 30)->nullable();
            $table->string('severity', 10)->default('info');
            $table->json('details')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index('timestamp');
            $table->index('user_id');
            $table->index('action');
            $table->index('module');
            $table->index('severity');
            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
