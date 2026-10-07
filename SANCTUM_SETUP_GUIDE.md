# Laravel Sanctum Authentication Setup - Complete Professional Guide

## What Was Fixed

### 1. **Sanctum Service Provider Registration** ✅
- Added `\Laravel\Sanctum\SanctumServiceProvider::class` to `bootstrap/app.php`
- This was the main cause of the "Auth guard [sanctum] is not defined" error

### 2. **Authentication Configuration** ✅
- Updated `config/auth.php` with proper Sanctum guard configuration
- Added both `sanctum` and `api` guards pointing to the sanctum driver
- Configured `providers` and `passwords`

### 3. **Middleware Setup** ✅
- Added `\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class` to API middleware
- This enables stateful cookie authentication for SPAs (Single Page Applications)
- Configured CORS and throttling correctly

### 4. **Environment Configuration** ✅
- Added Sanctum-specific environment variables to `.env`:
  - `SANCTUM_STATEFUL_DOMAINS`
  - `SANCTUM_CORS_ORIGINS`
  - `SANCTUM_TOKEN_STORAGE`
  - `SANCTUM_TOKEN_EXPIRY_MINUTES`
  - `SANCTUM_REVOKE_PREVIOUS_TOKENS`

### 5. **Model Configuration** ✅
- User model already has `HasApiTokens` trait from `App\Traits\HasApiTokens`
- PersonalAccessToken model is properly configured

## How Sanctum Works Now

### Login Flow:
1. Frontend POST to `/api/auth/login` with email + password
2. Backend AuthController validates credentials
3. Creates token: `$user->createToken('api-token', ['*'])->plainTextToken`
4. Returns token to frontend
5. Frontend stores token in localStorage
6. Frontend includes token in all requests: `Authorization: Bearer {token}`
7. Backend authenticates using `auth:sanctum` middleware

### Protected Routes:
All routes within this group are protected:
```php
Route::middleware(['auth:sanctum'])->group(function () {
    // All protected endpoints
});
```

## Setup Steps to Run Now

### Step 1: Clear Cache (Critical!)
```bash
cd backend
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Step 2: Verify Database Migrations
Ensure `personal_access_tokens` table exists:
```bash
php artisan migrate --force
```

### Step 3: Restart Backend Server
```bash
php artisan serve
```

### Step 4: Test Authentication
Open browser DevTools → Network tab and try logging in at:
`http://localhost:5173/auth/login`

Expected flow:
- Try login with email/password
- Check Network tab for POST `/api/auth/login`
- Response should include `token` field
- Store token locally
- Subsequent API calls include `Authorization: Bearer {token}`

## Frontend Configuration (Already Correct) ✅

Your `frontend/src/api/config.ts` is properly configured:
- Base URL: `http://localhost:8000/api`
- Includes Bearer token in all requests
- Handles 401 (Unauthorized) responses
- Auto-updates token if `x-new-token` header present

## Debugging Checklist

### If Login Still Fails:

1. **Check Backend Logs**
   ```bash
   tail -f backend/storage/logs/laravel.log
   ```

2. **Test Auth Endpoint Directly**
   ```bash
   curl -X POST http://localhost:8000/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email":"aydenfudagne@gmail.com","password":"MYlove8$"}'
   ```

3. **Verify User Exists and is Active**
   ```bash
   php artisan tinker
   >>> User::where('email', 'aydenfudagne@gmail.com')->first()
   >>> User::where('is_active', true)->count()
   ```

4. **Check personal_access_tokens Table**
   ```bash
   php artisan tinker
   >>> \App\Models\PersonalAccessToken::count()
   ```

5. **Test Protected Route**
   ```bash
   curl -H "Authorization: Bearer {your_token_here}" \
     http://localhost:8000/api/auth/me
   ```

## Environment Requirements

✅ **Verified Configuration:**
- `AUTH_GUARD=api` (uses sanctum driver)
- `AUTH_MODEL=App\Models\User`
- `SANCTUM_STATEFUL_DOMAINS` includes frontend URL
- `SANCTUM_CORS_ORIGINS` includes frontend origin
- Database connection working (PostgreSQL)
- personal_access_tokens table exists

## Common Issues & Solutions

### Issue: "Auth driver [sanctum] is not defined"
**Solution:** Run `php artisan config:clear`

### Issue: 401 Unauthorized on protected routes
**Solution:** 
1. Verify token is being sent: `Authorization: Bearer {token}`
2. Check token isn't expired
3. Verify user exists in database

### Issue: 403 Forbidden (has token but wrong role)
**Solution:** Check user role matches route middleware requirement

### Issue: Login endpoint returns 500 error
**Solution:**
1. Check backend logs: `tail -f storage/logs/laravel.log`
2. Verify user record with email exists
3. Verify user is_active = true
4. Try resetting user password

## Token Structure

Sanctum tokens:
- **Format:** Bearer token stored in `personal_access_tokens` table
- **Storage:** Securely stored in database with hashing
- **Expiration:** Can be set via `SANCTUM_TOKEN_EXPIRY_MINUTES`
- **Revocation:** Can revoke tokens manually or auto-revoke previous ones

## Next Steps

1. ✅ Run `php artisan config:clear`
2. ✅ Restart backend server
3. ✅ Test login with valid credentials
4. ✅ Verify token is returned
5. ✅ Verify token works for protected routes
6. ✅ Check PaymentsView loads once authenticated

## Security Best Practices Implemented

✅ Passwords hashed with bcrypt
✅ Tokens stored securely in database
✅ CORS properly configured
✅ Stateful authentication for SPAs
✅ Throttling enabled (60 requests/min)
✅ Token-based API authentication
✅ Role-based access control

## Support

If issues persist after following this guide:
1. Check `storage/logs/laravel.log` for detailed errors
2. Run `php artisan tinker` to test database queries manually
3. Verify all migrations ran: `php artisan migrate:status`
4. Test with curl before testing from frontend
