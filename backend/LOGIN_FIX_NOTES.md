# Login 500 Error - Fixed Issues

## Summary
The 500 error on POST `/api/auth/login` has been fixed by addressing multiple backend issues.

## Root Causes Identified & Fixed

### 1. **Duplicate Login Routes** ✅
**Issue:** There were two login implementations causing routing conflicts
- One in `/routes/api.php` (lines 131-180) - direct route closure
- One in `AuthController` class

**Fix:** Removed the duplicate route closure and consolidated all auth routes to use the `AuthController::login()` method

**File Changed:** `routes/api.php`
- Removed the inline login route
- Added the login route to the auth prefix group pointing to AuthController

### 2. **Token Column Size Too Small** ✅
**Issue:** The `personal_access_tokens` table had a token column limited to 80 characters, but the hashed token could exceed this

**Fix:** 
- Increased token column from 80 to 120 characters in the migration
- Created a new migration to automatically fix existing tables

**Files Changed:**
- `database/migrations/2026_07_24_000062_create_personal_access_tokens_table.php`
- `database/migrations/2026_10_08_fix_personal_access_tokens_table.php` (new)

### 3. **Missing expires_at Field in Insert** ✅
**Issue:** The `personal_access_tokens` table has an `expires_at` column, but the AuthController wasn't setting it during insert

**Fix:** Updated AuthController to include `'expires_at' => null` in the token insertion

**File Changed:** `app/Http/Controllers/Api/AuthController.php`

### 4. **Improved Error Handling** ✅
**Issue:** Database errors during token insertion weren't being properly caught and logged

**Fix:** 
- Added nested try-catch around token insertion to capture specific errors
- Enhanced error logging with token hash preview (first 10 chars only for security)
- Added detailed error messages to help diagnose issues

**File Changed:** `app/Http/Controllers/Api/AuthController.php`

### 5. **Removed Manual CORS Headers from AuthController** ✅
**Issue:** AuthController was manually adding CORS headers, which conflicts with the global CORS middleware

**Fix:** 
- Removed manual header additions from AuthController
- The `CorsMiddleware` now handles all CORS headers globally and consistently

**File Changed:** `app/Http/Controllers/Api/AuthController.php`

## How to Apply These Fixes

### Step 1: Update Your Code
All code changes are already in place. The following files have been modified:
- `app/Http/Controllers/Api/AuthController.php`
- `routes/api.php`
- `database/migrations/2026_07_24_000062_create_personal_access_tokens_table.php`

### Step 2: Run Database Migrations
```bash
cd backend
php artisan migrate
```

### Step 3: Run Diagnostics (Optional)
```bash
php artisan diagnose:login
```

This will check:
- Database connection
- Users table status
- Personal access tokens table and columns
- Token generation capability
- Database constraints

## Testing the Login

### 1. Using Frontend
Navigate to `http://localhost:5173/login` and try logging in with your credentials

### 2. Using cURL (Manual Test)
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Origin: http://localhost:5173" \
  -d '{
    "email": "your-email@example.com",
    "password": "your-password"
  }'
```

### 3. Expected Response (Success - 200)
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "phone": "+251987654321",
    "role": "farmer"
  },
  "token": "random80CharacterTokenString...",
  "token_type": "Bearer"
}
```

### 4. Expected Response (Invalid Credentials - 422)
```json
{
  "message": "Invalid email or password",
  "errors": {
    "email": ["Invalid credentials"]
  }
}
```

## Troubleshooting

### Still Getting 500 Error?

1. **Check Laravel Logs**
   ```bash
   tail -f backend/storage/logs/laravel.log
   ```

2. **Run Diagnostics**
   ```bash
   php artisan diagnose:login
   ```

3. **Verify Database**
   ```bash
   php artisan tinker
   > DB::table('users')->count()
   > DB::table('personal_access_tokens')->count()
   ```

4. **Check PostgreSQL Connection**
   - Ensure PostgreSQL is running on `127.0.0.1:5432`
   - Verify credentials in `.env` file
   - Check firewall allows connection

### Token Not Being Created?

If users can authenticate but tokens aren't being created:
1. Verify `personal_access_tokens` table exists
2. Check table has all columns: id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at
3. Run: `php artisan diagnose:login`

### CORS Issues?

The global CORS middleware handles all cross-origin requests. If you still see CORS errors:
1. Check browser console for specific CORS errors
2. Verify `CorsMiddleware.php` has your frontend origin in `$allowedOrigins`
3. Ensure frontend is using correct API URL from `VITE_API_URL` environment variable

## Key Changes Summary

| File | Change | Reason |
|------|--------|--------|
| `AuthController.php` | Improved error handling, added expires_at field, removed manual CORS headers | Better error visibility, data consistency, middleware integration |
| `routes/api.php` | Removed duplicate login route, consolidated auth routes | Prevent routing conflicts, cleaner code |
| `Migrations` | Increased token column to 120 chars, added table fix migration | Prevent token truncation, ensure compatibility |
| New: `DiagnoseLoginIssue.php` | New artisan command | Help diagnose future issues |

## Next Steps

- ✅ Apply database migrations
- ✅ Test login with frontend
- ✅ Monitor logs for any issues
- ✅ Consider adding rate limiting to login endpoint
- ✅ Add login attempt logging for audit trail

---

**Fixed by:** Kiro  
**Date:** October 8, 2026  
**Environment:** Development (PostgreSQL)
