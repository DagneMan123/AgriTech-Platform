<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$buyer = App\Models\User::find(3);
Illuminate\Support\Facades\Auth::login($buyer);

$req = Illuminate\Http\Request::create('/api/buyer/orders', 'GET');
$ctrl = new App\Http\Controllers\Api\Buyer\OrderController();
$resp = $ctrl->index($req);

$data = json_decode($resp->getContent(), true);
$orders = $data['orders'] ?? $data['data']['data'] ?? [];

echo "Total returned orders: " . count($orders) . PHP_EOL;

$statuses = array_count_values(array_map(fn($o) => $o['status'], $orders));
echo "Returned statuses: " . json_encode($statuses) . PHP_EOL;

$sample = $orders[0] ?? null;
if ($sample) {
    echo "Sample order 0: ID=" . $sample['id'] . ", status=" . $sample['status'] . ", farmer=" . json_encode($sample['farmer'] ?? null) . PHP_EOL;
}
$order24 = null;
foreach ($orders as $o) {
    if ($o['id'] == 24) $order24 = $o;
}
if ($order24) {
    echo "Order 24: num=" . $order24['order_number'] . ", status=" . $order24['status'] . ", farmer=" . json_encode($order24['farmer'] ?? null) . PHP_EOL;
}
