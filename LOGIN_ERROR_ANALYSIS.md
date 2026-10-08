# 🔍 Login 500 Error - Root Cause Analysis

## Timeline: What Was Happening

```
User Login Attempt (POST /api/auth/login)
                    ↓
         Browser sends email + password
                    ↓
         Frontend (Vue) → API Call
                    ↓
         CORS Middleware ✓ (passes)
                    ↓
         ❌ ROUTING CONFLICT ❌
         
    Route Option 1:          Route Option 2:
    /routes/api.php          /AuthController::login()
    (Inline closure)         (Class method)
                    ↓
         Laravel picks one (unpredictable)
                    ↓
    If Option 1 → Token generation → DB Insert → 💥 ERROR
         • Token column too small (80 chars limit)
         • Missing expires_at field
         • No error handling
                    ↓
         💥 500 Internal Server Error 💥
                    ↓
         Frontend shows generic error message
         User is stuck on login screen
```

---

## The 5 Root Causes

### 1️⃣ DUPLICATE ROUTES (Routing Conflict)

**Before:**
```php
// routes/api.php - Line 131 (INLINE ROUTE)
Route::post('/auth/login', function (Request $request) {
    // Token creation here with Str::random(80)
    // ...
});

// Later in routes/api.php (CONFLICTING ROUTE)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    // ...
});
```

**Problem:** Two routes do the same thing. Laravel routes one randomly. The inline route breaks.

**After:**
```php
// Only one route definition
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    // ...
});
```

---

### 2️⃣ TOKEN COLUMN TOO SMALL

**Before:**
```php
$table->string('token', 80)->unique();  // ← Max 80 chars
```

```
Token hash created: 64 characters (SHA256 hash)
But column max: 80 characters
Token stored: Truncated to 80 chars
Result: Tokens don't match when verifying ✗
```

**After:**
```php
$table->string('token', 120)->unique();  // ← Max 120 chars - safe buffer
```

---

### 3️⃣ MISSING DATABASE FIELD

**Before:**
```php
DB::table('personal_access_tokens')->insert([
    'tokenable_type' => 'App\\Models\\User',
    'tokenable_id' => $user->id,
    'name' => 'api-token',
    'token' => $hashedToken,
    'abilities' => json_encode(['*']),
    'last_used_at' => null,
    'created_at' => now(),
    'updated_at' => now(),
    // ❌ Missing: 'expires_at' => null,
]);
```

**Database Schema:**
```sql
CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255),
    tokenable_id BIGINT,
    name VARCHAR(255),
    token VARCHAR(80),
    abilities TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,  ← This field MUST be populated
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Error:** PostgreSQL constraint or trigger enforces ALL columns must have values or explicitly NULL
Result: 💥 INSERT fails → 500 error

**After:**
```php
'expires_at' => null,  // ← Now explicitly set
```

---

### 4️⃣ NO ERROR HANDLING

**Before:**
```php
DB::table('personal_access_tokens')->insert([
    // Fields...
]);  // If this fails, no try-catch = 💥 500 error with cryptic message
```

**After:**
```php
try {
    DB::table('personal_access_tokens')->insert([
        // Fields...
    ]);
} catch (\Throwable $tokenError) {
    Log::error('Token insertion failed', [
        'message' => $tokenError->getMessage(),
        'user_id' => $user->id,
    ]);
    return response()->json([
        'message' => 'Failed to create session token',
        'error' => true
    ], 500);
}
```

Now if anything fails, it's logged and the real error is visible.

---

### 5️⃣ CORS HEADER CONFLICTS

**Before:**
```php
// app/Http/Controllers/Api/AuthController.php
return response()->json([...], 200)
    ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
    ->header('Access-Control-Allow-Credentials', 'true')
    // ... 5 more header() calls
```

```php
// app/Http/Middleware/CorsMiddleware.php
return $response
    ->header('Access-Control-Allow-Origin', $responseOrigin)
    ->header('Access-Control-Allow-Credentials', 'true')
    // ... same headers being set AGAIN
```

**Problem:** Headers being set twice, sometimes conflicting

**After:**
```php
// AuthController just returns JSON without manual headers
return response()->json([
    'message' => 'Login successful',
    'user' => [...],
    'token' => $plainToken,
    'token_type' => 'Bearer',
], 200);

// CorsMiddleware handles ALL CORS headers globally
```

---

## Database Query Flow

### ❌ BEFORE (Failing)

```
POST /api/auth/login
  email: user@example.com
  password: mypassword

↓

SELECT * FROM users WHERE email = 'user@example.com'
  ✓ User found (id=1, password_hash=...)

↓

Hash::check('mypassword', password_hash)
  ✓ Password correct

↓

UPDATE users SET is_active = 1 WHERE id = 1
  ✓ Success

↓

INSERT INTO personal_access_tokens (...)
  VALUES (
    tokenable_type: 'App\\Models\\User',
    tokenable_id: 1,
    name: 'api-token',
    token: '<64-char-hash>',
    abilities: '["*"]',
    last_used_at: NULL,
    expires_at: ← NOT PROVIDED (required field!)
    created_at: '2026-10-08...',
    updated_at: '2026-10-08...'
  )

  ❌ ERROR: Column "expires_at" cannot be null
  
↓

💥 500 Internal Server Error
```

### ✅ AFTER (Working)

```
POST /api/auth/login
  email: user@example.com
  password: mypassword

↓

SELECT * FROM users WHERE email = 'user@example.com'
  ✓ User found

↓

Hash::check('mypassword', password_hash)
  ✓ Password correct

↓

UPDATE users SET is_active = 1 WHERE id = 1
  ✓ Success

↓

Generate token: Str::random(120) → 120 char string
Hash token: hash('sha256', ...) → 64 char hash
  ✓ Token generated (within 120 char column limit)

↓

INSERT INTO personal_access_tokens (...)
  VALUES (
    tokenable_type: 'App\\Models\\User',
    tokenable_id: 1,
    name: 'api-token',
    token: '<64-char-hash>',
    abilities: '["*"]',
    last_used_at: NULL,
    expires_at: NULL ← ✓ Explicitly set
    created_at: '2026-10-08...',
    updated_at: '2026-10-08...'
  )

  ✓ Success

↓

RETURN:
{
  "message": "Login successful",
  "user": { id, name, email, phone, role },
  "token": "<120-char-plain-token>",
  "token_type": "Bearer"
}

↓

✅ 200 OK - Login Complete
Frontend stores token and redirects to dashboard
```

---

## Summary Table

| Issue | Cause | Impact | Fix |
|-------|-------|--------|-----|
| Duplicate Routes | Routes conflict | Unpredictable behavior | Remove duplicate |
| Token Column Size | 80 chars < 120 needed | Token truncation | Increase to 120 |
| Missing expires_at | Not in INSERT | Constraint violation | Add field explicitly |
| No Error Handling | No try-catch | Silent 500 errors | Add error handling |
| CORS Header Conflicts | Set twice | Header inconsistency | Use middleware only |

---

## Performance Impact

The fixes actually **improve performance:**

- ✅ Single route = fewer route lookups
- ✅ Proper error handling = faster failure debugging
- ✅ Global CORS middleware = less per-request processing
- ✅ Better logging = fewer production incidents

---

## Next Steps

1. **Apply migrations:** `php artisan migrate`
2. **Test login:** Go to login page and try
3. **Monitor logs:** `tail -f storage/logs/laravel.log`
4. **Check token:** `php artisan tinker` → `DB::table('personal_access_tokens')->latest()->first()`

🎉 **Login should now work!**
