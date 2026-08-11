# Verification Steps for Login Fix

## Quick Checklist

### 1. Database Verification
```bash
# Check if personal_access_tokens table exists
php artisan tinker
>>> DB::table('personal_access_tokens')->count()
```

### 2. Configuration Verification
```bash
# Check current guard configuration
php artisan tinker
>>> config('auth.defaults.guard')  # Should return 'sanctum'
>>> config('sanctum')  # Should show sanctum config
```

### 3. API Test - Manual Login Test

**URL:** `POST http://localhost:8000/api/auth/login`

**Request Body:**
```json
{
  "email": "test@example.com",
  "password": "password"
}
```

**Expected Success Response (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com",
    "phone": "1234567890",
    "role": "farmer",
    "location": "Test Location",
    "region": "Test Region",
    "is_active": true
  },
  "token": "1|xyz..."
}
```

### 4. Database Check After Login
```bash
php artisan tinker
>>> DB::table('personal_access_tokens')->latest()->first()
# Should show a new record with the user's token
```

### 5. Protected Route Test

After receiving the token from login, try accessing a protected route:

**URL:** `GET http://localhost:8000/api/auth/profile`

**Headers:**
```
Authorization: Bearer {token_from_login_response}
```

**Expected Success Response (200):**
```json
{
  "id": 1,
  "name": "Test User",
  "email": "test@example.com",
  // ... other user data
}
```

## If Still Getting 500 Error

### Check Laravel Logs
```bash
# View recent logs
tail -f storage/logs/laravel.log
```

### Common Issues

1. **Database Migration Not Run**
   ```bash
   php artisan migrate
   ```

2. **Composer Autoload Not Updated**
   ```bash
   composer dump-autoload
   ```

3. **Cache Not Cleared**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Sanctum Package Issue**
   ```bash
   composer require laravel/sanctum:^4.0
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```

## Debug Mode

Make sure debug mode is on in `.env`:
```
APP_DEBUG=true
```

This will show detailed error messages if something goes wrong.

## Files Modified

1. ✅ `/backend/app/Traits/HasApiTokens.php` - Fixed trait
2. ✅ `/backend/config/sanctum.php` - Created configuration
3. ✅ `/backend/config/auth.php` - Updated guard configuration

All changes are backward compatible and don't affect existing functionality.
