<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Optimize password_resets table for faster lookups
        Schema::table('password_resets', function (Blueprint $table) {
            // Add index on email for faster lookups during reset
            if (Schema::hasTable('password_resets')) {
                $table->index('email');
            }
        });

        // Add index on created_at for cleanup of expired tokens
        Schema::table('password_resets', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('password_resets', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });
    }
};
