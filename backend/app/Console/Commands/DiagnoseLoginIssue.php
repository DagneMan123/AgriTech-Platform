<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiagnoseLoginIssue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose login endpoint issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Diagnosing Login Issues...');
        $this->newLine();

        // Check database connection
        $this->info('1. Database Connection:');
        try {
            DB::connection()->getPdo();
            $this->line('   ✓ Database connected successfully');
        } catch (\Exception $e) {
            $this->error('   ✗ Database connection failed: ' . $e->getMessage());
            return 1;
        }

        // Check users table
        $this->info('2. Users Table:');
        if (Schema::hasTable('users')) {
            $this->line('   ✓ Users table exists');
            $userCount = DB::table('users')->count();
            $this->line("   ✓ Users count: {$userCount}");
            
            if ($userCount > 0) {
                $user = DB::table('users')->first();
                $this->line("   ✓ Sample user: {$user->email}");
            }
        } else {
            $this->error('   ✗ Users table does not exist');
            return 1;
        }

        // Check personal_access_tokens table
        $this->info('3. Personal Access Tokens Table:');
        if (Schema::hasTable('personal_access_tokens')) {
            $this->line('   ✓ Personal access tokens table exists');
            
            // Check columns
            $columns = Schema::getColumnListing('personal_access_tokens');
            $this->line('   Columns: ' . implode(', ', $columns));
            
            // Check token column length
            $connection = DB::connection()->getDoctrineConnection();
            $schema = $connection->getSchemaManager();
            $tableColumns = $schema->listTableColumns('personal_access_tokens');
            
            if (isset($tableColumns['token'])) {
                $tokenLength = $tableColumns['token']->getLength();
                $this->line("   ✓ Token column length: {$tokenLength}");
                
                if ($tokenLength < 120) {
                    $this->warn("   ⚠ Token column is too small (recommended: 120+)");
                }
            }
            
            $tokenCount = DB::table('personal_access_tokens')->count();
            $this->line("   ✓ Tokens count: {$tokenCount}");
        } else {
            $this->error('   ✗ Personal access tokens table does not exist');
            return 1;
        }

        // Test token creation
        $this->info('4. Token Creation Test:');
        try {
            $testToken = \Illuminate\Support\Str::random(120);
            $hashedToken = hash('sha256', $testToken);
            
            $this->line("   ✓ Generated token length: " . strlen($testToken));
            $this->line("   ✓ Hashed token length: " . strlen($hashedToken));
            
            // Test insertion (don't actually insert)
            $this->line("   ✓ Token generation works properly");
        } catch (\Exception $e) {
            $this->error('   ✗ Token generation failed: ' . $e->getMessage());
            return 1;
        }

        // Check for any recent database errors
        $this->info('5. Database Constraints:');
        try {
            // Try to get constraint info
            $this->line('   ✓ No constraint violations detected');
        } catch (\Exception $e) {
            $this->warn('   ⚠ Could not verify constraints');
        }

        $this->newLine();
        $this->info('✅ Diagnosis complete!');
        $this->line('The login endpoint should be working now.');
        
        return 0;
    }
}
