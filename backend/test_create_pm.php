<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $pm = \App\Models\PaymentMethod::create([
        'user_id' => 3,
        'type' => 'card',
        'bank_name' => 'Chapa Card (Visa / Mastercard)',
        'account_number' => '0911000000',
        'account_holder' => 'Sample Buyer',
        'is_primary' => true,
        'is_active' => true
    ]);
    echo "SUCCESS CREATING PM: " . json_encode($pm->toArray()) . "\n";
} catch (\Throwable $e) {
    echo "ERROR CREATING PM: " . $e->getMessage() . "\n";
}
