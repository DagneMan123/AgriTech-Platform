<?php
/**
 * Run Migrations Script
 * This script ensures all pending migrations are executed
 * Run from command line: php run_migrations.php
 */

// Include the autoloader
require __DIR__ . '/vendor/autoload.php';

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';

// Get the Artisan instance
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Run migrations
$status = $kernel->call('migrate', ['--force' => true]);

if ($status === 0) {
    echo "✓ Migrations completed successfully!\n";
} else {
    echo "✗ Migration failed with status code: $status\n";
}

// Also ensure the crop_activities table exists
$app->make(\Illuminate\Database\ConnectionResolverInterface::class);
$schema = $app->make(\Illuminate\Database\Schema\Builder::class);

if (!$schema->hasTable('crop_activities')) {
    echo "Creating crop_activities table...\n";
    
    $schema->create('crop_activities', function ($table) {
        $table->id();
        $table->foreignId('crop_id')->constrained('crops')->onDelete('cascade');
        $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
        $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
        $table->enum('activity_type', [
            'planting',
            'watering',
            'fertilizing',
            'weeding',
            'pesticide',
            'pruning',
            'harvesting',
            'other'
        ]);
        $table->date('activity_date');
        $table->time('activity_time')->nullable();
        $table->text('description')->nullable();
        $table->decimal('quantity', 10, 2)->nullable();
        $table->string('unit', 50)->nullable();
        $table->decimal('cost', 12, 2)->nullable();
        $table->string('weather', 50)->nullable();
        $table->text('notes')->nullable();
        $table->softDeletes();
        $table->timestamps();

        // Indexes for better query performance
        $table->index(['crop_id', 'activity_date']);
        $table->index(['farm_id', 'activity_date']);
        $table->index(['farmer_id', 'activity_date']);
        $table->index('activity_type');
    });
    
    echo "✓ crop_activities table created successfully!\n";
} else {
    echo "✓ crop_activities table already exists\n";
}

exit($status);
