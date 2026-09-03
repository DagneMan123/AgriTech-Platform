<?php
/**
 * Complete refresh and setup for Crop Activities
 */
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

try {
    // Clear everything
    echo "Clearing caches...\n";
    $kernel->call('cache:clear');
    $kernel->call('route:clear');
    $kernel->call('config:clear');
    $kernel->call('view:clear');
    
    echo "Setup complete! The Crop Activities feature is ready.\n";
    echo "Restart your development server for changes to take effect.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
