<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration FIXES the critical foreign key constraint issue:
     * The farms table has farmer_id that should reference users(id), not farmers(id)
     * 
     * Error: Key (farmer_id)=(3) is not present in table "farmers"
     * 
     * This is why POST /api/farmer/crops returns 500 error.
     */
    public function up(): void
    {
        $connection = DB::connection()->getDriverName();
        
        echo "\n▶ Fixing farms foreign key constraint...\n";
        
        try {
            if ($connection === 'pgsql') {
                // PostgreSQL: Drop incorrect constraint if it exists
                DB::statement("
                    ALTER TABLE IF EXISTS farms 
                    DROP CONSTRAINT IF EXISTS farms_farmer_id_foreign CASCADE
                ");
                
                // Add the correct constraint
                DB::statement("
                    ALTER TABLE farms 
                    ADD CONSTRAINT farms_farmer_id_foreign 
                    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
                ");
                
                echo "✓ PostgreSQL: Fixed farms_farmer_id_foreign constraint\n";
                
            } elseif ($connection === 'mysql') {
                // MySQL: Drop incorrect constraint if it exists
                try {
                    DB::statement("ALTER TABLE farms DROP FOREIGN KEY farms_farmer_id_foreign");
                } catch (\Exception $e) {
                    // Constraint might not exist
                }
                
                // Add the correct constraint
                DB::statement("
                    ALTER TABLE farms 
                    ADD CONSTRAINT farms_farmer_id_foreign 
                    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
                ");
                
                echo "✓ MySQL: Fixed farms_farmer_id_foreign constraint\n";
            }
            
            echo "✓ Foreign key constraint fixed successfully!\n";
            echo "✓ You can now create farms and crops without errors.\n\n";
            
        } catch (\Exception $e) {
            echo "✗ Error fixing constraint: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function down(): void
    {
        // Don't drop the constraint in rollback - we want to keep the fix
    }
};
