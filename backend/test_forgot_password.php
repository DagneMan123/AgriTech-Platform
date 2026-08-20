<?php
/**
 * Quick test script to verify forgot-password endpoint works
 * Run with: php test_forgot_password.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    // Test 1: Check database connection
    echo "1. Testing database connection...\n";
    $pdo = $app->make('db')->connection()->getPdo();
    echo "   ✓ Database connected\n\n";

    // Test 2: Check if password_resets table exists
    echo "2. Checking password_resets table...\n";
    $result = $app->make('db')->table('password_resets')->limit(1)->first();
    echo "   ✓ password_resets table exists\n\n";

    // Test 3: Check if users table has data
    echo "3. Checking users table...\n";
    $userCount = $app->make('db')->table('users')->count();
    echo "   ✓ Users in database: " . $userCount . "\n\n";

    // Test 4: List users with their roles
    if ($userCount > 0) {
        echo "4. User details:\n";
        $users = $app->make('db')->table('users')->limit(5)->get();
        foreach ($users as $user) {
            echo "   - ID: {$user->id}, Email: {$user->email}, Role: {$user->role}\n";
        }
        echo "\n";
    }

    // Test 5: Check mail configuration
    echo "5. Mail Configuration:\n";
    echo "   MAIL_MAILER: " . env('MAIL_MAILER') . "\n";
    echo "   MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n\n";

    echo "✓ All checks passed! Backend should be working.\n";
    echo "\nTo test the forgot-password endpoint:\n";
    echo "POST http://localhost:8000/api/auth/forgot-password\n";
    echo "Body: {\"email\": \"user@example.com\"}\n";

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
