<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Farmer;

$u2 = User::find(2);
echo "User 2: Name={$u2->name}, Phone={$u2->phone}\n";

$f1 = Farmer::find(1);
echo "Farmer 1: Name={$f1->name}, Phone={$f1->phone_number}, Farm={$f1->farm_name}\n";

// Update Buyer 3 with a valid phone
$u3 = User::find(3);
if ($u3) {
    $u3->name = 'Dagne Aydenfu';
    $u3->phone = '251922334455';
    $u3->save();
    echo "User 3: Name={$u3->name}, Phone={$u3->phone}\n";
}

// Update Supplier 4
$u4 = User::find(4);
if ($u4) {
    $u4->name = 'Ethio Agro Supply Hub';
    $u4->phone = '251911234567';
    $u4->save();
    echo "User 4: Name={$u4->name}, Phone={$u4->phone}\n";
}
