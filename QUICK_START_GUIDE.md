# Quick Start - What To Do Now

## Step 1: Restart Your Backend Server

You MUST restart the backend for changes to take effect:

```bash
cd backend

# If running via php artisan serve, stop it (Ctrl+C) and restart:
php artisan serve

# OR if using another method, restart it
```

## Step 2: Clear All Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Step 3: Test the Health Endpoint

```bash
curl http://localhost:8000/api/health
```

Expected response:
```json
{"status":"ok","time":"2026-10-07T..."}
```

## Step 4: Create a Test User (if needed)

If you don't have a user, register one:

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Farmer",
    "email": "test@example.com",
    "phone": "+251912345678",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "farmer"
  }'
```

Expected response:
```json
{
  "message": "User registered successfully",
  "user": {...},
  "token": "eyJ0eX..."
}
```

## Step 5: Test Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
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
    "name": "Test Farmer",
    "email": "test@example.com",
    "phone": "+251912345678",
    "role": "farmer"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "Bearer"
}
```

**SAVE THE TOKEN** - you'll need it for the next step.

## Step 6: Test Authenticated Request

```bash
# Replace TOKEN with the token from step 5
curl -X GET http://localhost:8000/api/test-auth \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json"
```

Expected response:
```json
{
  "authenticated": true,
  "user": {
    "id": 1,
    "name": "Test Farmer",
    "email": "test@example.com",
    "phone": "+251912345678",
    "role": "farmer",
    "is_active": true,
    "location": null,
    "region": null
  },
  "token_present": true,
  "guard": "api",
  "timestamp": "2026-10-07T..."
}
```

## Step 7: Test Frontend Login

1. Go to `http://localhost:5173`
2. Click login
3. Enter credentials from Step 4
4. Click submit
5. Should redirect to dashboard

## If You Get 500 Errors

### Error 1: "Class 'App\Models\User' not found"

**Fix**: Run migrations
```bash
php artisan migrate
```

### Error 2: "Table 'personal_access_tokens' doesn't exist"

**Fix**: Create the table
```bash
php artisan migrate
```

### Error 3: "SQLSTATE HY000: General error"

**Fix**: Check database connection in `.env`
- Verify `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Verify PostgreSQL is running
- Test connection: `php artisan tinker` → `DB::connection()->getPdo()`

### Error 4: Still getting 500 errors

**Debug**:
```bash
# Check logs
tail -f backend/storage/logs/laravel.log

# Or enable raw errors:
# Edit .env: APP_DEBUG=true (should already be true)

# Check database:
php artisan tinker
>>> User::count()
>>> PersonalAccessToken::count()
```

## Expected File Changes Summary

✅ Changes made to these files:
- `backend/config/auth.php` - Added sanctum guard
- `backend/app/Models/User.php` - Added $with = []
- `backend/app/Models/PersonalAccessToken.php` - Removed morphTo, added getUser()
- `backend/app/Traits/HasApiTokens.php` - Safe token creation
- `backend/app/Guards/SanctumTokenGuard.php` - Uses getUser()
- `backend/app/Guards/TokenGuard.php` - Uses getUser()
- `backend/app/Auth/TokenGuard.php` - Uses getUser()
- `backend/app/Http/Middleware/ApiTokenGuard.php` - Uses getUser()
- `backend/app/Http/Controllers/Api/AuthController.php` - Safe login
- `backend/public/index.php` - Memory limit
- `backend/artisan` - Memory limit

## NO INFINITE RECURSION

The fixes permanently eliminate the infinite recursion problem by:

1. ❌ NOT using morphTo() in PersonalAccessToken
2. ❌ NOT using morphMany in HasApiTokens trait
3. ✅ Using direct database queries instead
4. ✅ Selecting only needed fields
5. ✅ Using withoutEagerLoads() everywhere

## Final Check

If everything works:
- ✅ Health endpoint returns 200
- ✅ Register endpoint returns 200
- ✅ Login endpoint returns 200 with token
- ✅ Test-auth endpoint returns 200 with user
- ✅ Frontend can login and see dashboard
- ✅ NO 500 errors
- ✅ NO stack overflow errors
- ✅ NO memory exhaustion errors

You're done! 🎉
