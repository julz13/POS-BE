<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new columns after id
            $table->string('first_name', 100)->nullable()->after('id');
            $table->string('last_name', 100)->nullable()->after('first_name');

            // Make the legacy 'name' column optional since we use first_name/last_name
            $table->string('name')->nullable()->change();

            // Rename 'password' to 'password_hash'
            $table->renameColumn('password', 'password_hash');

            // pin stores a bcrypt hash so needs full varchar length
            $table->string('pin')->nullable()->after('email');
            $table->string('role', 20)->default('cashier')->after('password_hash');
            $table->string('status', 20)->default('active')->after('role');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->softDeletes();

            // Add indexes
            $table->index('email');
            $table->index('role');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['email']);
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            
            $table->string('name')->nullable(false)->change();
            $table->dropColumn(['first_name', 'last_name', 'pin', 'role', 'status', 'last_login_at']);
            $table->renameColumn('password_hash', 'password');
        });
    }
};
