<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Farmer;
use App\Models\Buyer;

echo "=== ALL USERS ===\n";
foreach (User::all() as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Role: {$u->role} | Phone: {$u->phone_number} | Phone2: " . ($u->phone ?? 'none') . "\n";
}

echo "=== ALL FARMERS ===\n";
foreach (Farmer::with('user')->get() as $f) {
    echo "Farmer ID: {$f->id} | User ID: {$f->user_id} | Farm Name: {$f->farm_name} | Location: {$f->farm_location} | User Name: {$f->user?->name} | User Phone: {$f->user?->phone_number}\n";
}

echo "=== ALL BUYERS ===\n";
foreach (Buyer::with('user')->get() as $b) {
    echo "Buyer ID: {$b->id} | User ID: {$b->user_id} | Company: {$b->company_name} | User Name: {$b->user?->name} | User Phone: {$b->user?->phone_number}\n";
}
