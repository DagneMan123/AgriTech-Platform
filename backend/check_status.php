<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== USER 3 PAYMENT METHODS ===\n";
foreach (\App\Models\PaymentMethod::where('user_id', 3)->get() as $pm) {
    echo json_encode($pm->toArray()) . "\n";
}

echo "=== USER 3 LATEST PAYMENTS ===\n";
foreach (\App\Models\Payment::where('user_id', 3)->latest()->take(5)->get() as $p) {
    echo json_encode($p->toArray()) . "\n";
}
