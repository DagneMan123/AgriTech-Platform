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
            // Check if token column exists and its length
            $connection = DB::connection()->getDoctrineConnection();
            $schema = $connection->getSchemaManager();
            $columns = $schema->listTableColumns('personal_access_tokens');
            
            // If token column is too small or doesn't exist, migrate data
            if (isset($columns['token'])) {
                $tokenColumn = $columns['token'];
                $currentLength = $tokenColumn->getLength();
                
                // If length is less than 120, we need to extend it
                if ($currentLength < 120) {
                    Schema::table('personal_access_tokens', function (Blueprint $table) {
                        $table->string('token', 120)->change();
                    });
                }
            }
            
            // Ensure expires_at column exists
            if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                Schema::table('personal_access_tokens', function (Blueprint $table) {
                    $table->timestamp('expires_at')->nullable()->after('last_used_at');
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
        // Keeping the changes
    }
};
