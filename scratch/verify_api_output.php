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

$firstOrder = $data['orders'][0] ?? null;
if ($firstOrder) {
    echo "Order Number: " . $firstOrder['order_number'] . "\n";
    echo "Farmer ID: " . ($firstOrder['farmer_id'] ?? 'none') . "\n";
    echo "Farmer Name: " . ($firstOrder['farmer']['name'] ?? 'none') . "\n";
    echo "Farmer Phone: " . ($firstOrder['farmer']['phone_number'] ?? 'none') . "\n";
    echo "Farmer Chat ID: " . ($firstOrder['farmer']['in_app_chat_id'] ?? 'none') . "\n";
    echo "Farmer User Name: " . ($firstOrder['farmer']['user']['name'] ?? 'none') . "\n";
    echo "Farmer User Phone: " . ($firstOrder['farmer']['user']['phone'] ?? 'none') . "\n";
}
