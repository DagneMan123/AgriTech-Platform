<?php
require __DIR__ . '/../backend/vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Farmer;
use Illuminate\Support\Facades\DB;

echo "Updating database to remove all sample data...\n";

// Update Farmer User
$farmerUser = User::find(2);
if ($farmerUser) {
    $farmerUser->name = 'Bahir Dar Agro Farmers Cooperative';
    $farmerUser->phone = '251934318549';
    $farmerUser->region = 'Amhara';
    $farmerUser->zone = 'West Gojjam';
    $farmerUser->woreda = 'Bahir Dar';
    $farmerUser->save();
    echo "Updated User ID 2: Name='{$farmerUser->name}', Phone='{$farmerUser->phone}'\n";
}

// Update Farmer profile
$farmer = Farmer::find(1);
if ($farmer) {
    $farmer->farm_name = 'Lake Tana Agro Producers Union';
    $farmer->region = 'Amhara';
    $farmer->zone = 'West Gojjam';
    $farmer->woreda = 'Bahir Dar';
    $farmer->save();
    echo "Updated Farmer ID 1: Farm='{$farmer->farm_name}'\n";
}

// Update Sample Buyer User if needed
$buyerUser = User::find(3);
if ($buyerUser && $buyerUser->name === 'Sample Buyer') {
    $buyerUser->name = 'Dagne Aydenfu';
    $buyerUser->phone = '251934318549';
    $buyerUser->save();
    echo "Updated User ID 3: Name='{$buyerUser->name}'\n";
}

// Update Sample Supplier User if needed
$supplierUser = User::find(4);
if ($supplierUser && $supplierUser->name === 'Sample Supplier') {
    $supplierUser->name = 'Ethio Agro Supply Hub';
    $supplierUser->phone = '251911234567';
    $supplierUser->save();
    echo "Updated User ID 4: Name='{$supplierUser->name}'\n";
}

echo "Database sample data successfully cleaned up!\n";
