<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test 1: Real orders
$orders = App\Models\Order::with(['items.product'])->get();
foreach ($orders as $order) {
    if ($order->items->count() > 1) {
        echo "Order {$order->order_number} has {$order->items->count()} items:\n";
        foreach ($order->items as $it) {
            echo "  - Item: origin = '{$it->origin_location}', prod = '{$it->product?->origin_location}'\n";
        }
        echo "  => Order origin_location: '{$order->origin_location}'\n";
        break;
    }
}

// Test 2: Simulated model with multiple distinct origins
$mockOrder = new App\Models\Order();
$item1 = new App\Models\OrderItem(['origin_location' => 'Dessie']);
$item2 = new App\Models\OrderItem(['origin_location' => 'Adama']);
$item3 = new App\Models\OrderItem(['origin_location' => 'Dessie']); // duplicate to test deduplication
$mockOrder->setRelation('items', collect([$item1, $item2, $item3]));

echo "\nMock Order with [Dessie, Adama, Dessie]:\n";
echo "  Resolved origin: '{$mockOrder->origin_location}'\n";
