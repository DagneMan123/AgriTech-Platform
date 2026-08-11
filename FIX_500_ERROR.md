# Fix 500 Internal Server Error

## Quick Fix (Try This First)

### Step 1: Clear Laravel Cache
```bash
cd backend

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:cache
```

### Step 2: Composer Autoload
```bash
composer dump-autoload -o
```

### Step 3: Verify Database Connection
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo()
```

---

## If Still Getting 500 Error

### Step 4: Check Error Details

**Enable Debug Mode** in `.env`:
```env
APP_DEBUG=true
APP_ENV=local
```

Then the error will show in response.

### Step 5: Check Laravel Log
```bash
# Watch logs in real time
tail -f storage/logs/laravel.log

# Or view last 100 lines
tail -100 storage/logs/laravel.log
```

---

## Common 500 Error Causes

### 1. Database Connection Failed
**Error in log**: "could not find driver"  
**Fix**:
```bash
# Verify PostgreSQL is running
# Check DB credentials in .env
# Test connection: php artisan tinker >>> DB::connection()->getPdo()
```

### 2. Missing Required Fields
**Error in log**: "Column not found" or "SQLSTATE"  
**Fix**: Run migrations
```bash
php artisan migrate
```

### 3. Composer Issues
**Error in log**: "Class not found"  
**Fix**:
```bash
composer dump-autoload -o
php artisan route:cache
```

### 4. Permission Issues
**Fix**:
```bash
# Windows: Usually not needed
# Linux/Mac: 
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 5. Memory Limit
**Error**: "Allowed memory size exceeded"  
**Fix**: In `php.ini`:
```ini
memory_limit = 512M
```

---

## Full Diagnostic Checklist

### ✅ Step 1: Verify Environment
```bash
# Check PHP version
php -v

# Check Laravel version
php artisan --version

# Check database
php artisan db:show
```

### ✅ Step 2: Clear Everything
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:cache
php artisan optimize:clear

composer dump-autoload -o
```

### ✅ Step 3: Run Migrations
```bash
# Check migration status
php artisan migrate:status

# Run pending migrations
php artisan migrate
```

### ✅ Step 4: Test API
```bash
# Test health endpoint
curl http://localhost:8000/api/auth/login -X POST -d '{"email":"test@test.com","password":"test"}' -H "Content-Type: application/json"
```

### ✅ Step 5: Check Logs
```bash
# Follow logs
tail -f storage/logs/laravel.log

# Search for errors
grep "ERROR" storage/logs/laravel.log | tail -20
```

---

## If You're Getting 500 on /api/auth/register

### The Backend Validation Issue
If you just made changes and getting 500:

**Problem**: RegisterRequest not found or import missing

**Fix**: Verify `app/Http/Controllers/Api/AuthController.php` line 5:
```php
use App\Http\Requests\Auth\RegisterRequest;
```

**Verify RegisterRequest exists**:
```bash
# Check file exists
ls -la app/Http/Requests/Auth/RegisterRequest.php

# If not found, it's deleted - need to restore
```

---

## Command to Fix Everything (One Go)

```bash
cd backend

# Clear all caches
php artisan cache:clear && \
php artisan config:clear && \
php artisan view:clear && \
php artisan route:cache && \
php artisan optimize:clear && \
composer dump-autoload -o && \
php artisan migrate

echo "✅ All fixes applied!"
```

---

## If Still Getting 500

### Option A: Add Debug to Frontend
In browser console, run:
```javascript
// After getting 500 error, check response
fetch('http://localhost:8000/api/auth/register', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({
    full_name: 'Test',
    email: 'test@test.com',
    phone: '+251912345678',
    password: 'TestPass123',
    password_confirmation: 'TestPass123',
    address: 'Test Address',
    region: 'Oromia',
    role: 'farmer'
  })
})
.then(r => r.json())
.then(d => console.log('Full Response:', d))
```

### Option B: Direct PHP Test
```bash
cd backend

php artisan tinker

# Test User model
>>> App\Models\User::all()->count()

# Test create user
>>> App\Models\User::create(['name' => 'Test', 'email' => 'test1@test.com', 'password' => Hash::make('test'), 'role' => 'farmer'])
```

---

## Check Backend Server Status

```bash
# Test if backend is running
curl -v http://localhost:8000/api/auth/register

# If not running, start it
php artisan serve

# If port 8000 in use
php artisan serve --port=8001
```

---

## PostgreSQL Connection Verification

```bash
# Test connection directly
psql -h 127.0.0.1 -U postgres -d agritech -c "SELECT 1"

# If fails, check:
# 1. PostgreSQL is running
# 2. Database 'agritech' exists
# 3. Username/password correct in .env
```

---

## Database Verification

```bash
# Check if database exists
psql -U postgres -l | grep agritech

# If not found, create it
createdb -U postgres agritech

# Check users table exists
psql -U postgres -d agritech -c "\dt users"

# If not found, run migrations
php artisan migrate
```

---

## Final Check - Complete Flow

1. **Backend running?** → `php artisan serve`
2. **Database connected?** → `php artisan db:show`
3. **Migrations done?** → `php artisan migrate:status`
4. **Cache cleared?** → `php artisan cache:clear`
5. **Composer updated?** → `composer dump-autoload`
6. **API working?** → `curl http://localhost:8000/api/auth/register`

---

## Tell Me The Error Details

Please run this and share output:
```bash
cd c:\Users\Hena\Desktop\AgriTech_Platform\backend

# Clear cache
php artisan cache:clear
php artisan config:clear

# Try request and show error
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d "{
    \"full_name\": \"Test\",
    \"email\": \"test@test.com\",
    \"phone\": \"+251912345678\",
    \"password\": \"TestPass123\",
    \"password_confirmation\": \"TestPass123\",
    \"address\": \"Test\",
    \"region\": \"Oromia\",
    \"role\": \"farmer\"
  }"
```

Check `storage/logs/laravel.log` for detailed error message.

---

**Status**: Follow these steps to resolve 500 error  
**Last Updated**: 2026-08-05
