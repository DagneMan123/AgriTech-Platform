<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$methods = App\Models\PaymentMethod::where('user_id', 3)->get();
echo "Methods for user 3: " . $methods->count() . "\n";
foreach ($methods as $m) {
    echo "ID: {$m->id} | {$m->bank_name} | {$m->account_number}\n";
}

$user = App\Models\User::find(3);
$controller = app(App\Http\Controllers\Api\Buyer\PaymentMethodController::class);
$req = new Illuminate\Http\Request();
$req->setUserResolver(fn() => $user);
$res = $controller->index($req);
echo "Stats from index(): " . json_encode($res->getData()->stats) . "\n";

$transRes = $controller->transactions($req);
echo "Transactions count: " . count($transRes->getData()->data) . "\n";
