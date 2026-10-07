# ✅ Quick Fix for Login 500 Error

## What Was Wrong
- AuthController had duplicate class definitions (syntax error)
- CORS middleware wasn't registered properly
- Server hadn't been restarted to load new code

## What's Fixed Now ✓
- AuthController completely rewritten (clean, single class)
- CORS middleware registered with `prepend()` (runs first)
- Login uses ONLY raw database queries (no model loading)
- All CORS headers manually added to responses

## Instructions to Fix Login

### Step 1: Stop Backend Server
Press **CTRL+C** in the backend terminal where server is running.

### Step 2: Clear Cache
```bash
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Step 3: Create Test Users
```bash
php init.php
```
Output should show:
```
✓ Activated X users
✓ Created test user: test@example.com / password123
📋 Users in database:
  - Test Farmer (test@example.com) [farmer] - ✓ ACTIVE
```

### Step 4: Start Backend Server
```bash
php artisan serve
```
Wait for: `Server running on [http://127.0.0.1:8000]`

### Step 5: Test in Browser
Open http://localhost:5173 in browser and login with:
- **Email**: test@example.com  
- **Password**: password123

## If Still Getting 500 Error

### Check 1: Backend is running
```bash
# In new terminal
curl http://localhost:8000/api/health
# Should return: {"status":"ok","time":"..."}
```

### Check 2: CORS working
```bash
# In new terminal
curl -i http://localhost:8000/api/cors-test
# Should have header: Access-Control-Allow-Origin: http://localhost:5173
```

### Check 3: Test login directly
```bash
# In new terminal
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
# Should return token and user data
```

### Check 4: View Laravel logs
```bash
# In new terminal
tail -f backend/storage/logs/laravel.log
# Try login in browser and watch for errors
```

## Error Messages & Solutions

**"Invalid email or password"**
→ User doesn't exist or password is wrong
→ Run `php init.php` to create test user

**"Server error: SQLSTATE..."**
→ Database connection problem
→ Check `.env` DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD
→ Verify PostgreSQL is running

**"CORS policy error"**
→ CORS middleware issue (should be fixed)
→ Restart backend with `php artisan serve`
→ Clear browser cache (Ctrl+Shift+Delete)

## Files Changed

1. ✅ `backend/app/Http/Controllers/Api/AuthController.php` - Completely rewritten
2. ✅ `backend/bootstrap/app.php` - CORS middleware using prepend()
3. ✅ `backend/app/Http/Middleware/CorsMiddleware.php` - Already correct
4. ✅ `backend/routes/api.php` - Added /debug/login endpoint

## Next: After Login Works

1. Test protected endpoints: `GET /api/auth/profile`
2. Test logout: `POST /api/auth/logout`
3. Register new users (endpoint coming soon)
4. Test role-based dashboards
5. Check notifications

## Architecture

```
Login Flow (with NO model loading):
frontend login button
  ↓
POST /api/auth/login {email, password}
  ↓
CorsMiddleware (adds CORS headers)
  ↓
AuthController::login()
  ├─ Query DB directly: SELECT from users table
  ├─ Hash::check() password
  ├─ Auto-activate if inactive
  ├─ Generate random token + hash
  ├─ Insert into personal_access_tokens table
  └─ Return {user, token}
  ↓
Frontend stores token in localStorage
  ↓
Subsequent requests use: Authorization: Bearer {token}
```

## Database Schema Requirements

Make sure your database has:

```sql
-- Users table
CREATE TABLE users (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  phone VARCHAR(20),
  password VARCHAR(255),
  role VARCHAR(50),
  is_active BOOLEAN DEFAULT false,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

-- Personal access tokens table
CREATE TABLE personal_access_tokens (
  id BIGSERIAL PRIMARY KEY,
  tokenable_type VARCHAR(255),
  tokenable_id BIGINT,
  name VARCHAR(255),
  token VARCHAR(80) UNIQUE,
  abilities TEXT,
  last_used_at TIMESTAMP,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

If tables don't exist, run migrations:
```bash
php artisan migrate
```

---
**Need help?** Check the backend logs: `tail -f backend/storage/logs/laravel.log`
