<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$o = App\Models\Order::where('order_number', 'ORD-20261108-HvCkrqQl')->first();
echo "Order: {$o->order_number}\n";
echo "Farmer relation loaded? " . ($o->relationLoaded('farmer') ? 'YES' : 'NO') . "\n";
echo "Items relation loaded? " . ($o->relationLoaded('items') ? 'YES' : 'NO') . "\n";

// Access farmer directly via property:
$farmer = $o->farmer ?: ($o->items->first()?->product?->farmer);
echo "Farmer via property: " . ($farmer ? $farmer->farm_name : 'NULL') . "\n";
echo "Farmer woreda: " . ($farmer ? $farmer->woreda : 'NULL') . "\n";
echo "Farmer zone: " . ($farmer ? $farmer->zone : 'NULL') . "\n";
echo "Farmer region: " . ($farmer ? $farmer->region : 'NULL') . "\n";
echo "Product origin_location: " . ($o->items->first()?->product?->origin_location ?? 'NULL') . "\n";
