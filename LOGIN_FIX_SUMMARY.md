# Login and Stack Overflow Fixes - Summary

## Issues Fixed

### 1. Sanctum Guard Not Defined Error
**Problem**: Auth guard `[sanctum]` was not defined in `config/auth.php`
**Solution**: Added the missing `sanctum` guard configuration to `config/auth.php`:
```php
'sanctum' => [
    'driver' => 'sanctum',
    'provider' => 'users',
],
```

### 2. Memory Exhaustion Error
**Problem**: `Allowed memory size of 134217728 bytes exhausted`
**Root Cause**: The User model was missing `protected $with = []` which prevented automatic eager loading of relationships

**Solutions Applied**:
- Set `PHP_MEMORY_LIMIT=512M` in `.env`
- Added `protected $with = []` to User model to disable eager loading
- Increased memory limit in `public/index.php` and `artisan`
- Updated `php.ini` (already had 512M)

### 3. Maximum Call Stack Size - Infinite Recursion Error
**Problem**: `Maximum call stack size of 67043328 bytes reached`
**Root Cause**: Multiple cascading issues causing infinite recursion:

#### Issue 3a: PersonalAccessToken Loading
- The `SanctumTokenGuard` was using `->with('tokenable')` which triggered relationship loading
- This caused the morphTo relationship to load User, which had morphMany relationships pointing back

#### Issue 3b: User Model Relationships
- Multiple models had circular morphMany/morphTo relationships
- Without `$with = []`, Laravel was eager loading these recursively

#### Issue 3c: HasApiTokens Trait
- The trait was calling `$this->tokens()->create()` which triggered relationship building

**Solutions Applied**:

1. **PersonalAccessToken Model** (`app/Models/PersonalAccessToken.php`):
   - Added `protected $with = []` to disable eager loading
   - Added `withoutEagerLoads()` to morphTo relationship
   - Created `getTokenableModel()` safe method to get User without recursion

2. **HasApiTokens Trait** (`app/Traits/HasApiTokens.php`):
   - Changed to create tokens directly without using `$this->tokens()`
   - Updated `currentAccessToken()` to query directly without relationship loading
   - Added `->without(['tokenable'])` to the tokens() relationship

3. **AuthController Login** (`app/Http/Controllers/Api/AuthController.php`):
   - Bypass `createToken()` trait method
   - Create tokens directly using `PersonalAccessToken::create()`
   - Select only necessary User fields to minimize data

4. **All Token Guards** Updated three guard implementations:
   - `app/Guards/SanctumTokenGuard.php`
   - `app/Guards/TokenGuard.php`
   - `app/Auth/TokenGuard.php`

   Changes:
   - Removed `->with('tokenable')` eager loading
   - Use `getTokenableModel()` safe method instead of morphTo relationship
   - Select specific fields only to prevent recursive loading

5. **Config Auth** (`config/auth.php`):
   - Added missing `sanctum` guard definition

6. **Cache Cleanup**:
   - Deleted bootstrap cache files (`packages.php`, `services.php`)

## Files Modified

1. `backend/config/auth.php` - Added sanctum guard
2. `backend/.env` - Added PHP_MEMORY_LIMIT
3. `backend/public/index.php` - Set memory limit at entry
4. `backend/artisan` - Set memory limit for CLI
5. `backend/app/Models/User.php` - Added $with = []
6. `backend/app/Models/PersonalAccessToken.php` - Safe loading + withoutEagerLoads()
7. `backend/app/Traits/HasApiTokens.php` - Direct token creation
8. `backend/app/Http/Controllers/Api/AuthController.php` - Bypass trait for token creation
9. `backend/app/Guards/SanctumTokenGuard.php` - Use safe method
10. `backend/app/Guards/TokenGuard.php` - Use safe method
11. `backend/app/Auth/TokenGuard.php` - Use safe method

## Key Prevention Strategies

1. **Disable Eager Loading**: Set `protected $with = []` on models with many relationships
2. **Use withoutEagerLoads()**: On relationships that could cause recursion
3. **Safe Field Selection**: Select only needed fields to prevent deep relationship loading
4. **Direct Database Queries**: For authentication, query directly instead of loading relationships
5. **Bypass Trait Methods**: Create tokens directly instead of through relationship methods

## Testing Login

To test the login flow:
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

Expected response:
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "test@example.com",
    "phone": "+251...",
    "role": "farmer"
  },
  "token": "...",
  "token_type": "Bearer"
}
```

## Additional Notes

- Memory limit can be increased further if needed in `php.ini`
- All three guards (Sanctum, TokenGuard in Guards, TokenGuard in Auth) have been updated for consistency
- The `getTokenableModel()` method in PersonalAccessToken provides a safe way to retrieve users
- Bootstrap cache is cleared and will be regenerated on next request
