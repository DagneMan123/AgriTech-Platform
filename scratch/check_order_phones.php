<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

$orders = Order::whereNotNull('delivery_phone')->take(10)->get();
foreach ($orders as $o) {
    echo "ID: {$o->id} | Delivery Phone: {$o->delivery_phone} | Recipient: {$o->recipient_name}\n";
}
