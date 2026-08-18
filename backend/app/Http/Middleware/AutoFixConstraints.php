<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoFixConstraints
{
    /**
     * Handle an incoming request.
     *
     * This middleware automatically fixes database constraints on the first request.
     * Specifically, it fixes the farms.farmer_id foreign key to point to users(id) instead of farmers(id).
     */
    public function handle(Request $request, Closure $next)
    {
        // Only run once per request cycle
        static $fixed = false;
        
        if (!$fixed && $this->shouldFix()) {
            try {
                $this->fixConstraints();
                $fixed = true;
            } catch (\Exception $e) {
                Log::warning('Could not auto-fix constraints: ' . $e->getMessage());
                // Don't block the request, just log the warning
            }
        }

        return $next($request);
    }

    private function shouldFix(): bool
    {
        // Only attempt to fix if we're accessing crop or farm routes
        $path = request()->path();
        return strpos($path, 'api/farmer') !== false;
    }

    private function fixConstraints(): void
    {
        $connection = DB::connection()->getDriverName();
        
        // PostgreSQL
        if ($connection === 'pgsql') {
            try {
                // Check if constraint exists and is wrong
                $constraint = DB::selectOne("
                    SELECT DISTINCT constraint_name
                    FROM information_schema.table_constraints
                    WHERE table_name = 'farms' 
                    AND constraint_type = 'FOREIGN KEY'
                    AND constraint_name = 'farms_farmer_id_foreign'
                    LIMIT 1
                ");

                if ($constraint) {
                    // Get the referenced table to check if it's wrong
                    $ref = DB::selectOne("
                        SELECT 
                            ccu.table_name AS foreign_table_name,
                            ccu.column_name AS foreign_column_name
                        FROM information_schema.constraint_column_usage AS ccu
                        WHERE ccu.constraint_name = 'farms_farmer_id_foreign'
                        LIMIT 1
                    ");

                    // If it references 'farmers' table (wrong), fix it
                    if ($ref && $ref->foreign_table_name === 'farmers') {
                        DB::statement('ALTER TABLE farms DROP CONSTRAINT farms_farmer_id_foreign CASCADE');
                        DB::statement('
                            ALTER TABLE farms
                            ADD CONSTRAINT farms_farmer_id_foreign
                            FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
                        ');
                        Log::info('Auto-fixed farms foreign key constraint (PostgreSQL)');
                    }
                }
            } catch (\Exception $e) {
                Log::debug('PostgreSQL constraint fix attempt: ' . $e->getMessage());
            }
        }
        // MySQL
        elseif ($connection === 'mysql') {
            try {
                // Get constraint info
                $constraints = DB::selectOne("
                    SELECT CONSTRAINT_NAME
                    FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
                    WHERE TABLE_NAME = 'farms'
                    AND CONSTRAINT_SCHEMA = DATABASE()
                    LIMIT 1
                ");

                if ($constraints) {
                    DB::statement('ALTER TABLE farms DROP FOREIGN KEY farms_farmer_id_foreign');
                    DB::statement('
                        ALTER TABLE farms
                        ADD CONSTRAINT farms_farmer_id_foreign
                        FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
                    ');
                    Log::info('Auto-fixed farms foreign key constraint (MySQL)');
                }
            } catch (\Exception $e) {
                Log::debug('MySQL constraint fix attempt: ' . $e->getMessage());
            }
        }
    }
}
