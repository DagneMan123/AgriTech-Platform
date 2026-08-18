<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixFarmsForeignKey extends Command
{
    protected $signature = 'fix:farms-foreign-key';
    protected $description = 'Fix the farms table foreign key constraint';

    public function handle()
    {
        $this->info('Fixing farms table foreign key constraint...');
        
        $connection = DB::connection()->getDriverName();
        $this->line("Database connection: {$connection}");

        try {
            if ($connection === 'pgsql') {
                $this->fixPostgreSQL();
            } elseif ($connection === 'mysql') {
                $this->fixMySQL();
            } else {
                $this->error("Unsupported database connection: {$connection}");
                return 1;
            }

            $this->info('✅ Foreign key constraint fixed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error fixing constraint: ' . $e->getMessage());
            return 1;
        }
    }

    private function fixPostgreSQL()
    {
        $this->line('Fixing PostgreSQL constraint...');

        // Check if constraint exists
        $result = DB::selectOne("
            SELECT constraint_name 
            FROM information_schema.table_constraints 
            WHERE table_name = 'farms' 
            AND constraint_type = 'FOREIGN KEY'
        ");

        if ($result) {
            $this->line('Found existing foreign key constraint, dropping it...');
            DB::statement('ALTER TABLE farms DROP CONSTRAINT IF EXISTS farms_farmer_id_foreign CASCADE');
        }

        // Add correct constraint
        $this->line('Adding correct foreign key constraint...');
        DB::statement('
            ALTER TABLE farms 
            ADD CONSTRAINT farms_farmer_id_foreign 
            FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
        ');
    }

    private function fixMySQL()
    {
        $this->line('Fixing MySQL constraint...');

        // Check if constraint exists
        $result = DB::selectOne("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'farms' 
            AND COLUMN_NAME = 'farmer_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if ($result) {
            $this->line('Found existing foreign key constraint, dropping it...');
            try {
                DB::statement('ALTER TABLE farms DROP FOREIGN KEY farms_farmer_id_foreign');
            } catch (\Exception $e) {
                $this->line('Constraint might not exist: ' . $e->getMessage());
            }
        }

        // Add correct constraint
        $this->line('Adding correct foreign key constraint...');
        DB::statement('
            ALTER TABLE farms 
            ADD CONSTRAINT farms_farmer_id_foreign 
            FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
        ');
    }
}
