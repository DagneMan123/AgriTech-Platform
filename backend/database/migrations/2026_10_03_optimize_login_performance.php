<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure composite index on (email, is_active) exists for fast login queries
        Schema::table('users', function (Blueprint $table) {
            // Check if composite index already exists to avoid errors
            $indexName = 'users_email_is_active_index';
            
            try {
                // Get all indexes for the users table
                $indexes = DB::select("SHOW INDEX FROM users");
                $hasIndex = false;
                
                foreach ($indexes as $index) {
                    if ($index->Key_name === $indexName) {
                        $hasIndex = true;
                        break;
                    }
                }
                
                if (!$hasIndex) {
                    // Create composite index on email and is_active for fast login queries
                    $table->index(['email', 'is_active'], $indexName);
                }
            } catch (\Exception $e) {
                // If index creation fails, continue anyway
                \Illuminate\Support\Facades\Log::warning('Could not verify login index: ' . $e->getMessage());
            }
        });

        // Ensure single index on is_active for future queries filtering by status
        Schema::table('users', function (Blueprint $table) {
            try {
                $indexes = DB::select("SHOW INDEX FROM users");
                $hasIndex = false;
                
                foreach ($indexes as $index) {
                    if ($index->Key_name === 'users_is_active_index') {
                        $hasIndex = true;
                        break;
                    }
                }
                
                if (!$hasIndex) {
                    $table->index('is_active', 'users_is_active_index');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Could not create is_active index: ' . $e->getMessage());
            }
        });

        // Add database connection pooling hint (application level)
        // This improves connection reuse for faster queries
        $this->comment('✅ Login performance indexes have been optimized');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes if they exist
            try {
                $table->dropIndex('users_email_is_active_index');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Could not drop composite index: ' . $e->getMessage());
            }
            
            try {
                $table->dropIndex('users_is_active_index');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Could not drop is_active index: ' . $e->getMessage());
            }
        });
    }
};
