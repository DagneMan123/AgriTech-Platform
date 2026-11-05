<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\User;
use App\Models\Farmer;

$orders = Order::with(['farmer.user', 'items.product'])->orderBy('id', 'desc')->get();
echo "Total orders: " . $orders->count() . "\n";
foreach ($orders->take(15) as $o) {
    echo "ID: {$o->id} | Num: {$o->order_number} | Status: {$o->status} | PayStatus: {$o->payment_status} | Dest: {$o->delivery_address} | Farmer: " . ($o->farmer?->user?->name ?? 'none') . " | Phone: " . ($o->farmer?->user?->phone_number ?? 'none') . "\n";
}
