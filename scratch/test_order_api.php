<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Buyer\OrderController;
use Illuminate\Support\Facades\Auth;

$buyer = User::where('email', 'buyer@agritech.com')->first();
Auth::login($buyer);

$controller = new OrderController();
$request = Request::create('/api/buyer/orders', 'GET');
$response = $controller->index($request);
$data = json_decode($response->getContent(), true);

echo "Total orders returned: " . count($data['orders']) . "\n";
$statuses = [];
foreach ($data['orders'] as $o) {
    $st = $o['status'];
    $statuses[$st] = ($statuses[$st] ?? 0) + 1;
}
print_r($statuses);
