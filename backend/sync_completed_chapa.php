<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$chapaPayments = App\Models\Payment::where('user_id', 3)
    ->whereIn('id', [15, 16])
    ->get();

foreach ($chapaPayments as $p) {
    $p->update([
        'status' => 'completed',
        'paid_at' => now(),
        'completed_at' => now(),
        'payment_details' => [
            'gateway' => 'Chapa',
            'channel' => 'Telebirr',
            'verified_at' => now()->toIso8601String()
        ]
    ]);
    echo "Payment {$p->id} ({$p->transaction_id}) marked completed.\n";
}

// Ensure user 3 has their active verified Telebirr method saved
$existingMethod = App\Models\PaymentMethod::where('user_id', 3)->first();
if (!$existingMethod) {
    App\Models\PaymentMethod::create([
        'user_id' => 3,
        'type' => 'mobile_money',
        'bank_name' => 'Telebirr (Chapa)',
        'account_number' => '0911000000',
        'account_holder' => 'Verified Buyer',
        'is_primary' => true,
        'is_active' => true
    ]);
    echo "Created verified Telebirr (Chapa) payment method for user 3.\n";
}
