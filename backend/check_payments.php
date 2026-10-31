<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$payments = App\Models\Payment::all();
echo "Total payments in DB: " . $payments->count() . "\n";
foreach ($payments as $p) {
    echo "ID: {$p->id} | User: {$p->user_id} | Order: {$p->order_id} | Amount: {$p->amount} | Status: {$p->status} | TxRef: {$p->transaction_id} | Created: {$p->created_at}\n";
}
