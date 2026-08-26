<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add index on last_login_at for faster queries and better timestamp performance
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }
            // Add index for last_login_at if it doesn't exist
            $table->index('last_login_at');
        });

        // Add composite index on email and is_active for faster login queries
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['last_login_at']);
            $table->dropIndex(['email', 'is_active']);
        });
    }
};
