# Login 500 Error - Root Cause and Fix

## Problem
The login endpoint was returning a 500 Internal Server Error. The issue was caused by an improper custom implementation of the `HasApiTokens` trait that was generating fake tokens without storing them in the database.

## Root Cause
The file `/backend/app/Traits/HasApiTokens.php` had a custom implementation that:
- Generated random tokens as strings (e.g., `token_abc123...`)
- Never actually stored them in the `personal_access_tokens` database table
- Returned a token object with only `plainTextToken` property

When a user tried to login:
1. Login endpoint would generate a fake token (not stored in DB)
2. Frontend would receive this token and send it with subsequent requests
3. Sanctum middleware would check `personal_access_tokens` table for the token
4. Token would not be found (because it was never stored)
5. Authentication would fail on subsequent requests

## Solution Applied

### 1. Fixed the HasApiTokens Trait
**File:** `/backend/app/Traits/HasApiTokens.php`

Changed from a broken custom implementation to properly using Laravel Sanctum's `HasApiTokens` trait:

```php
<?php

namespace App\Traits;

use Laravel\Sanctum\HasApiTokens as SanctumHasApiTokens;

/**
 * API token trait - uses Laravel Sanctum for proper token management
 */
trait HasApiTokens
{
    use SanctumHasApiTokens;
}
```

### 2. Created Sanctum Configuration
**File:** `/backend/config/sanctum.php` (NEW)

Created proper Sanctum configuration with:
- Stateful domains configured
- Token expiration settings
- Middleware configuration

### 3. Updated Auth Configuration
**File:** `/backend/config/auth.php`

Updated the default guard from 'web' to 'sanctum' and added Sanctum guard configuration:

```php
'defaults' => [
    'guard' => env('AUTH_GUARD', 'sanctum'),  // Changed from 'web' to 'sanctum'
    'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
],

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    
    'sanctum' => [  // NEW
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

## How It Works Now

1. **Login Request** → User sends email and password to `/api/auth/login`
2. **Token Generation** → Sanctum's proper `createToken()` method creates a token and stores it in `personal_access_tokens` table
3. **Response** → Frontend receives the token and stores it (in localStorage, sessionStorage, etc.)
4. **Authenticated Requests** → Frontend sends token in `Authorization: Bearer <token>` header
5. **Verification** → Sanctum middleware verifies token exists in database and user is authorized
6. **Success** → User can access protected routes

## Testing the Fix

1. Start the backend server
2. Try logging in via frontend
3. Check that you receive a token without errors
4. Verify you can access protected routes like `/api/auth/profile`

## Database Note

The `personal_access_tokens` table already exists in your migrations:
- File: `/backend/database/migrations/2026_07_24_000062_create_personal_access_tokens_table.php`

Make sure your database is migrated:
```bash
php artisan migrate
```

## Next Steps

If you still encounter issues:
1. Check that database migrations have been run
2. Verify PostgreSQL is running and accessible
3. Check Laravel logs: `storage/logs/laravel.log`
4. Ensure `.env` database credentials are correct
