<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "ALL PAYMENT METHODS IN DATABASE:\n";
foreach (\App\Models\PaymentMethod::all() as $pm) {
    echo json_encode($pm->toArray()) . "\n";
}
