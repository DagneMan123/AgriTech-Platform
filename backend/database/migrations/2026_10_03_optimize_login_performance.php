<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure composite index on (email, is_active) exists for fast login queries
        Schema::table('users', function (Blueprint $table) {
            $indexName = 'users_email_is_active_index';
            
            // Check if index already exists in PostgreSQL
            $exists = collect(DB::select(
                "SELECT 1 FROM pg_indexes WHERE schemaname = 'public' AND indexname = ?", 
                [$indexName]
            ))->isNotEmpty();

            if (!$exists) {
                $table->index(['email', 'is_active'], $indexName);
            }
        });

        // 2. Ensure single index on is_active for future queries filtering by status
        Schema::table('users', function (Blueprint $table) {
            $indexName = 'users_is_active_index';

            $exists = collect(DB::select(
                "SELECT 1 FROM pg_indexes WHERE schemaname = 'public' AND indexname = ?", 
                [$indexName]
            ))->isNotEmpty();

            if (!$exists) {
                $table->index('is_active', $indexName);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes if they exist
            try {
                $table->dropIndex('users_email_is_active_index');
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }
            
            try {
                $table->dropIndex('users_is_active_index');
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }
        });
    }
};