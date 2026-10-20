<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if personal_access_tokens table exists
        if (Schema::hasTable('personal_access_tokens')) {
            // Safely alter the token column length to 120 using native PostgreSQL statement
            try {
                DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN token TYPE VARCHAR(120);');
            } catch (\Exception $e) {
                // Ignore if it fails or is already updated
            }
            
            // Ensure expires_at column exists
            if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                Schema::table('personal_access_tokens', function (Blueprint $table) {
                    $table->timestamp('expires_at')->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a maintenance migration - reversing would be destructive
    }
};