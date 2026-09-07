<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseConstraintFixer
{
    private static bool $constraintFixed = false;

    /**
     * Fix the farms foreign key constraint.
     * This should only be called once during application startup.
     * Uses caching to prevent repeated expensive operations.
     */
    public static function fixFarmsConstraint(): void
    {
        // Skip if already fixed in this request cycle
        if (self::$constraintFixed) {
            return;
        }

        try {
            $connection = DB::connection()->getDriverName();
            
            if ($connection === 'pgsql') {
                static::fixPostgresConstraint();
            } elseif ($connection === 'mysql') {
                static::fixMysqlConstraint();
            }

            self::$constraintFixed = true;
        } catch (\Exception $e) {
            Log::debug('Error fixing constraints: ' . $e->getMessage());
        }
    }

    private static function fixPostgresConstraint(): void
    {
        try {
            // Disable foreign key constraints temporarily
            DB::statement('SET session_replication_role = replica');
            
            // Check and fix farms constraint
            $constraint = DB::selectOne("
                SELECT constraint_name
                FROM information_schema.table_constraints
                WHERE table_name = 'farms' 
                AND constraint_type = 'FOREIGN KEY'
            ");

            if ($constraint) {
                // Drop the incorrect constraint
                DB::statement('ALTER TABLE farms DROP CONSTRAINT IF EXISTS ' . $constraint->constraint_name . ' CASCADE');
            }

            // Add correct constraint
            DB::statement('
                ALTER TABLE IF EXISTS farms
                ADD CONSTRAINT farms_farmer_id_foreign
                FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
            ');

            // Re-enable foreign key constraints
            DB::statement('SET session_replication_role = default');
            
            Log::info('✓ Fixed PostgreSQL farms foreign key constraint');
        } catch (\Exception $e) {
            try {
                // Make sure we re-enable constraints even if there's an error
                DB::statement('SET session_replication_role = default');
            } catch (\Exception $ignored) {}
            
            throw $e;
        }
    }

    private static function fixMysqlConstraint(): void
    {
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Drop existing constraint
            try {
                DB::statement('ALTER TABLE farms DROP FOREIGN KEY farms_farmer_id_foreign');
            } catch (\Exception $e) {
                // Constraint might not exist, that's fine
            }

            // Add correct constraint
            DB::statement('
                ALTER TABLE farms
                ADD CONSTRAINT farms_farmer_id_foreign
                FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
            ');

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            Log::info('✓ Fixed MySQL farms foreign key constraint');
        } catch (\Exception $e) {
            try {
                // Make sure we re-enable constraints even if there's an error
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $ignored) {}
            
            throw $e;
        }
    }

    /**
     * Temporarily disable foreign key constraints for a callback
     */
    public static function withoutConstraints(callable $callback)
    {
        $connection = DB::connection()->getDriverName();
        
        try {
            if ($connection === 'pgsql') {
                DB::statement('SET session_replication_role = replica');
            } elseif ($connection === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }

            $result = $callback();

            if ($connection === 'pgsql') {
                DB::statement('SET session_replication_role = default');
            } elseif ($connection === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            return $result;
        } catch (\Exception $e) {
            // Re-enable constraints on error
            if ($connection === 'pgsql') {
                try {
                    DB::statement('SET session_replication_role = default');
                } catch (\Exception $ignored) {}
            } elseif ($connection === 'mysql') {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } catch (\Exception $ignored) {}
            }
            throw $e;
        }
    }
}
