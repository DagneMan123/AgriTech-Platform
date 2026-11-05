<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$products = Product::with('farmer.user')->take(10)->get();
foreach ($products as $p) {
    echo "Product: {$p->name} | Farmer ID: {$p->farmer_id} | Farmer Name: " . ($p->farmer?->user?->name ?? 'none') . "\n";
}
