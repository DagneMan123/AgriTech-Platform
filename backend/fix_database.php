<?php
/**
 * Quick database fix script
 * Run this from the backend directory: php fix_database.php
 */

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$connection = DB::connection()->getDriverName();
$pdo = DB::connection()->getPdo();

echo "═══════════════════════════════════════════════════\n";
echo "FIXING CROPS DATABASE ISSUES\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "Database: $connection\n";

try {
    if ($connection === 'pgsql') {
        echo "\n▶ PostgreSQL: Dropping incorrect farms foreign key...\n";
        $pdo->exec('ALTER TABLE IF EXISTS farms DROP CONSTRAINT IF EXISTS farms_farmer_id_foreign CASCADE');
        echo "   ✓ Done\n";
        
        echo "\n▶ PostgreSQL: Adding correct farms foreign key...\n";
        $pdo->exec('ALTER TABLE farms ADD CONSTRAINT farms_farmer_id_foreign FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE');
        echo "   ✓ Done\n";
        
        echo "\n▶ PostgreSQL: Dropping incorrect crops foreign key...\n";
        $pdo->exec('ALTER TABLE IF EXISTS crops DROP CONSTRAINT IF EXISTS crops_farm_id_foreign CASCADE');
        echo "   ✓ Done\n";
        
        echo "\n▶ PostgreSQL: Adding correct crops foreign key...\n";
        $pdo->exec('ALTER TABLE crops ADD CONSTRAINT crops_farm_id_foreign FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE');
        echo "   ✓ Done\n";
        
    } elseif ($connection === 'mysql') {
        echo "\n▶ MySQL: Dropping incorrect farms foreign key...\n";
        try {
            $pdo->exec('ALTER TABLE farms DROP FOREIGN KEY farms_farmer_id_foreign');
        } catch (Exception $e) {
            echo "   Note: Constraint might not exist\n";
        }
        echo "   ✓ Done\n";
        
        echo "\n▶ MySQL: Adding correct farms foreign key...\n";
        $pdo->exec('ALTER TABLE farms ADD CONSTRAINT farms_farmer_id_foreign FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE');
        echo "   ✓ Done\n";
        
        echo "\n▶ MySQL: Dropping incorrect crops foreign key...\n";
        try {
            $pdo->exec('ALTER TABLE crops DROP FOREIGN KEY crops_farm_id_foreign');
        } catch (Exception $e) {
            echo "   Note: Constraint might not exist\n";
        }
        echo "   ✓ Done\n";
        
        echo "\n▶ MySQL: Adding correct crops foreign key...\n";
        $pdo->exec('ALTER TABLE crops ADD CONSTRAINT crops_farm_id_foreign FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE');
        echo "   ✓ Done\n";
    }
    
    echo "\n═══════════════════════════════════════════════════\n";
    echo "✅ DATABASE FIX COMPLETED SUCCESSFULLY!\n";
    echo "═══════════════════════════════════════════════════\n\n";
    echo "You can now create farms and crops without errors.\n";
    echo "Clear caches: php artisan config:clear\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}
?>
