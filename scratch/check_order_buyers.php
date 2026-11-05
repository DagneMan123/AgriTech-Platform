<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\User;

$orders = Order::all();
echo "Buyer IDs of orders:\n";
foreach ($orders as $o) {
    echo "ID: {$o->id} | Buyer ID: {$o->buyer_id} | Status: {$o->status} | Created: {$o->created_at}\n";
}
