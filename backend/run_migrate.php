<?php
/**
 * Simple script to run migrations without artisan CLI
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

try {
    $kernel->call('migrate', ['--force' => true]);
    echo "Migrations completed successfully\n";
} catch (\Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
    exit(1);
}
