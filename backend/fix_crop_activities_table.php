<?php
/**
 * Quick Fix Script for Crop Activities Foreign Key
 * 
 * This script fixes the foreign key constraint on the crop_activities table
 * to reference users instead of farmers.
 * 
 * Run from command line: php fix_crop_activities_table.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$db = $app->make(\Illuminate\Database\ConnectionResolverInterface::class);
$connection = $db->connection();

try {
    $driver = $connection->getDriverName();
    
    echo "Database driver: $driver\n";
    echo "Checking crop_activities table...\n";
    
    if ($driver === 'pgsql') {
        // PostgreSQL
        
        // Step 1: Drop the old table
        echo "Dropping existing crop_activities table...\n";
        $connection->statement('DROP TABLE IF EXISTS crop_activities CASCADE');
        
        // Step 2: Recreate with correct foreign key
        echo "Recreating crop_activities table with correct foreign key...\n";
        $connection->statement('
            CREATE TABLE crop_activities (
                id BIGSERIAL PRIMARY KEY,
                crop_id BIGINT NOT NULL REFERENCES crops(id) ON DELETE CASCADE,
                farm_id BIGINT NOT NULL REFERENCES farms(id) ON DELETE CASCADE,
                farmer_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                activity_type VARCHAR(50) NOT NULL,
                activity_date DATE NOT NULL,
                activity_time TIME,
                description TEXT,
                quantity NUMERIC(10,2),
                unit VARCHAR(50),
                cost NUMERIC(12,2),
                weather VARCHAR(50),
                notes TEXT,
                deleted_at TIMESTAMP,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )
        ');
        
        // Step 3: Create indexes
        echo "Creating indexes...\n";
        $connection->statement('CREATE INDEX crop_activities_crop_id_activity_date_idx ON crop_activities(crop_id, activity_date)');
        $connection->statement('CREATE INDEX crop_activities_farm_id_activity_date_idx ON crop_activities(farm_id, activity_date)');
        $connection->statement('CREATE INDEX crop_activities_farmer_id_activity_date_idx ON crop_activities(farmer_id, activity_date)');
        $connection->statement('CREATE INDEX crop_activities_activity_type_idx ON crop_activities(activity_type)');
        
        echo "✓ PostgreSQL: crop_activities table fixed successfully!\n";
        
    } elseif ($driver === 'mysql') {
        // MySQL
        
        // Step 1: Drop the old table
        echo "Dropping existing crop_activities table...\n";
        $connection->statement('DROP TABLE IF EXISTS crop_activities');
        
        // Step 2: Recreate with correct foreign key
        echo "Recreating crop_activities table with correct foreign key...\n";
        $connection->statement('
            CREATE TABLE crop_activities (
                id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                crop_id BIGINT UNSIGNED NOT NULL,
                farm_id BIGINT UNSIGNED NOT NULL,
                farmer_id BIGINT UNSIGNED NOT NULL,
                activity_type VARCHAR(50) NOT NULL,
                activity_date DATE NOT NULL,
                activity_time TIME,
                description LONGTEXT,
                quantity DECIMAL(10,2),
                unit VARCHAR(50),
                cost DECIMAL(12,2),
                weather VARCHAR(50),
                notes LONGTEXT,
                deleted_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
                FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
                FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE,
                
                INDEX idx_crop_activity_date (crop_id, activity_date),
                INDEX idx_farm_activity_date (farm_id, activity_date),
                INDEX idx_farmer_activity_date (farmer_id, activity_date),
                INDEX idx_activity_type (activity_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
        
        echo "✓ MySQL: crop_activities table fixed successfully!\n";
    } else {
        echo "✗ Unsupported database driver: $driver\n";
        exit(1);
    }
    
    echo "\n✓ All done! The crop_activities table is now correctly configured.\n";
    echo "✓ Foreign key now references users instead of farmers.\n";
    echo "\nYou can now save crop activities without errors.\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
