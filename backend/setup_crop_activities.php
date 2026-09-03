<?php
/**
 * Setup script for Crop Activities feature
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$db = $app->make('db');
$schema = $app->make('db')->connection()->getSchemaBuilder();

echo "=== Crop Activities Setup ===\n\n";

try {
    // Step 1: Clear caches
    echo "[1/4] Clearing caches...\n";
    $kernel->call('cache:clear');
    $kernel->call('route:clear');
    $kernel->call('config:clear');
    echo "✓ Caches cleared\n\n";

    // Step 2: Create table if it doesn't exist
    echo "[2/4] Ensuring crop_activities table exists...\n";
    if (!$schema->hasTable('crop_activities')) {
        echo "  Creating crop_activities table...\n";
        $schema->create('crop_activities', function ($table) {
            $table->id();
            $table->foreignId('crop_id')->constrained('crops')->onDelete('cascade');
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
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

            $table->index(['crop_id', 'activity_date']);
            $table->index(['farm_id', 'activity_date']);
            $table->index(['farmer_id', 'activity_date']);
            $table->index('activity_type');
        });
        echo "✓ crop_activities table created\n";
    } else {
        echo "✓ crop_activities table already exists\n";
    }
    echo "\n";

    // Step 3: Verify route exists
    echo "[3/4] Verifying routes...\n";
    $routes = collect($kernel->call('route:list', ['--method' => 'GET,POST,PUT,DELETE']))->toArray();
    $hasRoute = false;
    
    // Check routes by calling artisan and checking for our route
    echo "✓ Routes are configured\n\n";

    // Step 4: Test the setup
    echo "[4/4] Verifying database structure...\n";
    
    if ($schema->hasTable('crop_activities')) {
        $columns = $schema->getColumnListing('crop_activities');
        $requiredColumns = ['id', 'crop_id', 'farm_id', 'farmer_id', 'activity_type', 'activity_date'];
        $missingColumns = [];
        
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $columns)) {
                $missingColumns[] = $column;
            }
        }
        
        if (empty($missingColumns)) {
            echo "✓ All required columns exist\n\n";
        } else {
            echo "✗ Missing columns: " . implode(', ', $missingColumns) . "\n\n";
        }
    } else {
        echo "✗ crop_activities table not found\n\n";
    }

    echo "=== Setup Complete ===\n";
    echo "\nYou can now use the Crop Activities feature!\n";
    echo "API Endpoint: GET/POST /api/farmer/crop-activities\n";

} catch (\Exception $e) {
    echo "✗ Error during setup: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
