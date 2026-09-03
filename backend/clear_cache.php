<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

try {
    $kernel->call('cache:clear');
    echo "Cache cleared\n";
    $kernel->call('route:clear');
    echo "Route cache cleared\n";
    $kernel->call('config:clear');
    echo "Config cache cleared\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
