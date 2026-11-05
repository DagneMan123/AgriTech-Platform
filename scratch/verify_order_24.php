<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Buyer\OrderController;
use Illuminate\Support\Facades\Auth;

$buyer = User::where('email', 'buyer@agritech.com')->first();
Auth::login($buyer);

$controller = new OrderController();
$request = Request::create('/api/buyer/orders', 'GET');
$response = $controller->index($request);
$data = json_decode($response->getContent(), true);

foreach ($data['orders'] as $o) {
    if ($o['order_number'] === 'ORD-20261105-wlPq87dd') {
        echo "Found Order: " . $o['order_number'] . "\n";
        echo "Farmer Name: " . ($o['farmer']['name'] ?? 'none') . "\n";
        echo "Farmer Phone: " . ($o['farmer']['phone_number'] ?? 'none') . "\n";
        echo "Farmer Chat ID: " . ($o['farmer']['in_app_chat_id'] ?? 'none') . "\n";
        break;
    }
}
