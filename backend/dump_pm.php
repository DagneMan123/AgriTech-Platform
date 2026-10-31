<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PAYMENT METHODS ===\n";
foreach (App\Models\PaymentMethod::all() as $pm) {
    echo json_encode($pm->toArray()) . "\n";
}

echo "=== PAYMENTS ===\n";
foreach (App\Models\Payment::all() as $p) {
    echo json_encode($p->toArray()) . "\n";
}
