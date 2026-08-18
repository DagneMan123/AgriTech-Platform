<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseConstraintFixer;

class FixDatabaseConstraints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:fix-constraints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix incorrect database foreign key constraints (farms.farmer_id should reference users.id, not farmers.id)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('FIXING DATABASE CONSTRAINTS');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        try {
            $this->info('▶ Checking and fixing farms foreign key constraint...');
            DatabaseConstraintFixer::fixFarmsConstraint();
            
            $this->info('✓ Foreign key constraint fixed successfully!');
            $this->newLine();
            $this->info('═══════════════════════════════════════════════════════');
            $this->info('✅ All constraints are now correct');
            $this->info('═══════════════════════════════════════════════════════');
            $this->newLine();
            
            $this->info('You can now create farms and crops without errors.');
            $this->info('Clear caches with: php artisan config:clear');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Error fixing constraints: ' . $e->getMessage());
            $this->newLine();
            $this->error('Please check your database connection and try again.');
            return Command::FAILURE;
        }
    }
}
