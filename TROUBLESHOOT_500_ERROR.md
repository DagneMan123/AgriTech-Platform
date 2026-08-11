# Troubleshoot 500 Error - Complete Guide

## What is a 500 Error?
- **500 Internal Server Error** = Something went wrong on the backend
- **Not your fault** = Your frontend/request is fine, backend has an issue
- **Server logs have the answer** = Check Laravel logs to see actual error

---

## Step 1: Check if Backend is Running

### Windows CMD
```bash
netstat -ano | findstr :8000
```
- If nothing shows = Backend NOT running
- If shows "LISTENING" = Backend is running

### Start Backend (If Not Running)
```bash
cd c:\Users\Hena\Desktop\AgriTech_Platform\backend
php artisan serve
```

Expected output:
```
Starting Laravel development server: http://127.0.0.1:8000
```

---

## Step 2: Check Error in Laravel Log

### Find Latest Error
```bash
cd c:\Users\Hena\Desktop\AgriTech_Platform\backend

# View last 50 lines
type storage\logs\laravel.log | tail -50

# Or open in notepad
notepad storage\logs\laravel.log
```

Look for lines starting with:
- `[date] local.ERROR:`
- `[date] local.CRITICAL:`

The message after that is the actual error.

---

## Step 3: Common 500 Errors & Fixes

### ❌ Error: "Call to undefined method"
**Cause**: Missing method or wrong class name

**Fix**: Check for typos
```bash
# Example: If error mentions "register" method
grep -n "public function register" app/Http/Controllers/Api/AuthController.php
```

### ❌ Error: "Class not found"
**Cause**: Class can't be imported or autoloader issues

**Fix**: 
```bash
cd backend
composer dump-autoload -o
php artisan cache:clear
```

### ❌ Error: "SQLSTATE" or "Database error"
**Cause**: Database connection issue

**Fix**:
```bash
# Check database is running
# Check .env has correct credentials
# Test connection:
php artisan db:show

# If fails, fix .env:
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agritech
DB_USERNAME=postgres
DB_PASSWORD=YourPasswordHere
```

### ❌ Error: "Call to undefined function"
**Cause**: Function doesn't exist (wrong namespace or typo)

**Fix**: Check imports at top of file

### ❌ Error: "Trying to get property 'x' of non-object"
**Cause**: Null value accessed as object

**Fix**: Usually means database query returned nothing

### ❌ Error: "CSRF token mismatch"
**Cause**: CORS or token validation issue

**Fix**: Check CorsMiddleware is applied

---

## Step 4: Complete Reset Procedure

### Windows - Run this script:

**Option A: Automated Script**
```bash
cd c:\Users\Hena\Desktop\AgriTech_Platform\backend
RESET_AND_RUN.bat
```

**Option B: Manual commands**
```bash
cd c:\Users\Hena\Desktop\AgriTech_Platform\backend

# Clear everything
php artisan cache:clear
php artisan config:clear  
php artisan view:clear
php artisan route:cache
php artisan optimize:clear

# Update composer
composer dump-autoload -o

# Run migrations
php artisan migrate

# Start server
php artisan serve
```

---

## Step 5: Debug API Request

### Using curl (Windows PowerShell)
```powershell
$body = @{
    full_name = "Test User"
    email = "test@example.com"
    phone = "+251912345678"
    password = "TestPass123"
    password_confirmation = "TestPass123"
    address = "Test Address"
    region = "Oromia"
    role = "farmer"
} | ConvertTo-Json

$response = Invoke-WebRequest -Uri 'http://localhost:8000/api/auth/register' `
    -Method POST `
    -Headers @{'Content-Type'='application/json'} `
    -Body $body `
    -ErrorAction SilentlyContinue

$response.Content
```

### Using curl (Bash/Git Bash)
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Test User",
    "email": "test@example.com",
    "phone": "+251912345678",
    "password": "TestPass123",
    "password_confirmation": "TestPass123",
    "address": "Test Address",
    "region": "Oromia",
    "role": "farmer"
  }'
```

### Check Response
- If you see JSON → Backend is running
- If you see HTML error → Check laravel.log

---

## Step 6: Enable Debug Mode

**Edit `.env`**:
```env
APP_DEBUG=true
APP_ENV=local
```

Now errors show full stack trace in response (helpful for debugging).

**⚠️ WARNING**: Only for development! Disable for production.

---

## Step 7: Check File Permissions

On Windows, this rarely matters, but if migrating from Linux:

```bash
# Windows doesn't need this typically
# But if you have WSL issues:

icacls "storage" /grant:r "%username%:F" /t
icacls "bootstrap\cache" /grant:r "%username%:F" /t
```

---

## Step 8: Database Verification

### Check Database Exists
```bash
# From PostgreSQL terminal
\l

# Look for "agritech" database
```

### Check Tables Exist
```bash
php artisan tinker
>>> DB::table('users')->count()
>>> Schema::getColumnListing('users')
```

### Reset Database (Last Resort)
```bash
# WARNING: This deletes all data!
php artisan migrate:refresh
php artisan migrate:fresh --seed
```

---

## Step 9: Check PHP & Database

```bash
# Check PHP version (should be 8.0+)
php -v

# Check if PostgreSQL driver installed
php -m | findstr pdo_pgsql

# Check Laravel version
php artisan --version

# Check if composer installed
composer --version
```

---

## The Nuclear Option - Complete Fresh Start

```bash
cd c:\Users\Hena\Desktop\AgriTech_Platform\backend

# 1. Delete temp files
rmdir /s /q storage\bootstrap\cache
mkdir storage\bootstrap\cache

# 2. Clear everything
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 3. Reinstall composer
composer install

# 4. Regenerate key
php artisan key:generate

# 5. Run migrations
php artisan migrate:fresh

# 6. Start server
php artisan serve
```

---

## What To Tell Me If It Still Doesn't Work

Run this and share the output:

```bash
cd c:\Users\Hena\Desktop\AgriTech_Platform\backend

# 1. Show PHP version
echo === PHP Version ===
php -v

# 2. Show database status
echo === Database Status ===
php artisan db:show

# 3. Try a request and show error
echo === Test Register Request ===
curl -X POST http://localhost:8000/api/auth/register ^
  -H "Content-Type: application/json" ^
  -d "{"full_name":"Test","email":"test@test.com","phone":"+251912345678","password":"TestPass123","password_confirmation":"TestPass123","address":"Test","region":"Oromia","role":"farmer"}"

# 4. Show last error in log
echo === Last 30 Lines of Laravel Log ===
tail -30 storage\logs\laravel.log
```

---

## Quick Checklist

- [ ] Backend is running (`php artisan serve`)
- [ ] PostgreSQL is running
- [ ] Database `agritech` exists
- [ ] `.env` has correct database credentials
- [ ] Ran `php artisan migrate`
- [ ] Ran `composer dump-autoload`
- [ ] Ran `php artisan cache:clear`
- [ ] No syntax errors in PHP files
- [ ] All required files exist (RegisterRequest, AuthController, User model)
- [ ] Laravel.log shows actual error (if still failing)

---

## Most Common Fix

**99% of 500 errors fixed by:**
```bash
cd backend
php artisan cache:clear
php artisan config:clear
composer dump-autoload -o
php artisan serve
```

Then test again. If still failing, check the Laravel log!

---

**Status**: Complete troubleshooting guide  
**Last Updated**: 2026-08-05
