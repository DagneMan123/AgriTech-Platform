# COMPLETE FIX FOR LOGIN AND 500 ERRORS

## Issues Found and Fixed

### 1. Stack Overflow / Infinite Recursion
**Root Cause**: PersonalAccessToken was using `morphTo()` relationship which created infinite loops during model serialization.

**Fix Applied**:
- Removed morphTo() method from PersonalAccessToken
- Created safe `getUser()` method that directly queries the database
- Updated all guards to use `getUser()` instead of morphTo

### 2. HasApiTokens Trait Issue
**Root Cause**: Calling `$this->tokens()->create()` was triggering morphMany relationship chain.

**Fix Applied**:
- Direct database creation in `createToken()` method
- Changed `tokens()` to return a safe query instead of morphMany relationship
- No relationship chains are triggered during token operations

### 3. PersonalAccessToken Model
**Root Cause**: Model was trying to use morphTo which caused recursion.

**Fix Applied**:
- Removed `tokenable()` method (was using morphTo)
- Added `getUser()` method for safe user retrieval
- All queries bypass relationships completely

### 4. All Guard Implementations
**Root Cause**: Guards were using relationship loading in various ways.

**Fix Applied**:
- SanctumTokenGuard: Uses `getUser()` directly
- TokenGuard (app/Guards): Uses `getUser()` directly  
- TokenGuard (app/Auth): Uses `getUser()` directly
- ApiTokenGuard middleware: Uses `getUser()` directly

## Files Modified

1. `app/Models/PersonalAccessToken.php` - Removed morphTo, added getUser()
2. `app/Traits/HasApiTokens.php` - Safe token creation
3. `app/Guards/SanctumTokenGuard.php` - Uses getUser()
4. `app/Guards/TokenGuard.php` - Uses getUser()
5. `app/Auth/TokenGuard.php` - Uses getUser()
6. `app/Http/Middleware/ApiTokenGuard.php` - Uses getUser()
7. `config/auth.php` - Added sanctum guard

## Testing the Fix

### 1. Check Health Endpoint
```bash
curl http://localhost:8000/api/health
# Should return: {"status":"ok","time":"..."}
```

### 2. Login Endpoint
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 3. Check Token Works
```bash
# Replace TOKEN with actual token from login response
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/test-auth
```

## What Was The Real Problem?

The infinite recursion was happening in this chain:

1. **Request comes in** → Middleware tries to authenticate
2. **Guard calls** `personalAccessToken->tokenable` (morphTo relationship)
3. **Morphto tries to load** User model
4. **User model loads** PersonalAccessToken relationships (morphMany defined in trait)
5. **PersonalAccessToken tries to load** tokenable again
6. **INFINITE LOOP** → Stack overflow

**Solution**: Never load relationships in authentication. Always query directly and select only needed fields.

## Key Prevention Rules

✅ DO:
- Query directly from PersonalAccessToken without loading relationships
- Use `getUser()` method for safe user retrieval
- Select only the fields you need
- Use `withoutEagerLoads()` on queries

❌ DON'T:
- Call `$token->tokenable` (morphTo relationship)
- Use `$this->tokens()` for token operations
- Load any relationships during authentication
- Use Laravel's default Sanctum guard (we're using our own)

## If 500 Errors Persist

1. **Check logs** (logs should now be fresh after deletion):
   ```bash
   tail -f backend/storage/logs/laravel.log
   ```

2. **Enable debug mode** (already enabled in .env):
   ```
   APP_DEBUG=true
   ```

3. **Check database**:
   ```bash
   # Verify tables exist
   php artisan tinker
   >>> DB::table('users')->count()
   >>> DB::table('personal_access_tokens')->count()
   ```

4. **Clear cache**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## Expected Results

- ✅ Login endpoint returns 200 with token
- ✅ No stack overflow errors
- ✅ No infinite recursion
- ✅ No memory exhaustion
- ✅ Token-based auth works correctly
- ✅ Frontend can authenticate and access protected endpoints

## Next Steps If Still Having Issues

1. Verify database connection works
2. Verify a test user exists in the database
3. Check that password is hashed with bcrypt
4. Verify the bearer token format in requests
5. Check CORS configuration allows requests
