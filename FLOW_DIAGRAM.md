# Authentication Flow - Before & After

## BEFORE (Broken)

```
User Registration Request
    ↓
RegisterRequest Validation ✓
    ↓
AuthController::register()
    ↓
User::create(['address' => value])  ❌ FIELD MISMATCH
    ↓
Error: Column 'address' does not exist
    ↓
No error handling
    ↓
422 Unprocessable Content (vague)
```

```
User Login Request
    ↓
Validation ✓
    ↓
AuthController::login()
    ↓
User::where('email', ...) ✓
    ↓
Hash::check() ✓
    ↓
ValidationException::withMessages() ❌ WRONG FORMAT
    ↓
user->update(['last_login_at' => now()]) ❌ COLUMN MISSING
    ↓
500 Internal Server Error
```

---

## AFTER (Fixed)

```
User Registration Request
    ↓
RegisterRequest Validation ✓
    ↓
AuthController::register()
    ├─ Try Block
    ├─ User::create([
    │    'location' => $validated['address']  ✓ CORRECT MAPPING
    │    ... other fields ...
    │ ]) ✓ SUCCESS
    │
    ├─ $user->createToken() ✓
    │
    └─ Return 201 with token
       {
         "message": "User registered successfully",
         "user": {...},
         "token": "token_xxx"
       }
    
    ├─ Catch QueryException
    │  └─ Check for duplicate email/phone
    │     Return 422 with specific message
    │
    └─ Catch General Exception
       └─ Return 500 with safe message
```

```
User Login Request
    ↓
Validation ✓
    ↓
AuthController::login() - Try Block
    ├─ User::where('email', ...) ✓
    ├─ Hash::check() ✓
    ├─ Check is_active ✓
    │
    ├─ user->update(['last_login_at' => now()])
    │  └─ Uses migration to ensure column exists ✓
    │
    ├─ $user->createToken() ✓
    │
    └─ Return 200 with token
       {
         "message": "Login successful",
         "user": {...},
         "token": "token_xxx"
       }
    
    ├─ If credentials wrong
    │  └─ Return 422 JSON (not ValidationException)
    │
    ├─ If account suspended
    │  └─ Return 403 JSON
    │
    ├─ Catch QueryException
    │  └─ Log error and return 500
    │
    └─ Catch General Exception
       └─ Log error and return 500
```

---

## Database Changes

### Before
```
users table:
┌─────────────┐
│ id          │
│ name        │
│ email       │
│ phone       │
│ password    │
│ role        │
│ location    │
│ region      │
│ is_active   │
│ created_at  │
│ updated_at  │
└─────────────┘
❌ NO: last_login_at
❌ NO: address field
```

### After
```
users table:
┌─────────────────┐
│ id              │
│ name            │
│ email           │
│ phone           │
│ password        │
│ role            │
│ location        │ ← mapped from 'address' in request
│ region          │
│ is_active       │
│ last_login_at   │ ← NEW COLUMN (migration applied)
│ created_at      │
│ updated_at      │
└─────────────────┘
✓ Column exists
✓ Proper mapping
```

---

## Code Flow Comparison

### Register Method

**BEFORE:**
```php
public function register(RegisterRequest $request)
{
    $validated = $request->validated();
    
    $user = User::create([
        'name' => $validated['full_name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'],
        'password' => Hash::make($validated['password']),
        'role' => $validated['role'],
        'location' => $validated['address'],
        'region' => $validated['region'],
        'is_active' => true,
    ]);  // ❌ No error handling
    
    $token = 'token_' . bin2hex(random_bytes(32));
    
    return response()->json([...], 201);
}
```

**AFTER:**
```php
public function register(RegisterRequest $request)
{
    try {  // ✓ Error handling
        $validated = $request->validated();
        
        $user = User::create([
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'location' => $validated['address'],  // ✓ Correct mapping
            'region' => $validated['region'] ?? null,
            'is_active' => true,
        ]);
        
        $token = $user->createToken('api-token')->plainTextToken;  // ✓ Proper token
        
        return response()->json([...], 201);
        
    } catch (QueryException $e) {  // ✓ Database errors
        // Handle duplicates, constraints, etc.
    } catch (\Exception $e) {  // ✓ Other errors
        // Safe error response
    }
}
```

### Login Method

**BEFORE:**
```php
public function login(Request $request)
{
    $request->validate([...]);
    
    $user = User::where('email', $request->email)->first();
    
    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([  // ❌ Throws exception
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
    
    if (!$user->is_active) {
        throw ValidationException::withMessages([...]);  // ❌ Throws exception
    }
    
    $user->update(['last_login_at' => now()]);  // ❌ May fail - column missing
    
    $token = 'token_' . bin2hex(random_bytes(32));
    
    return response()->json([...]);  // No error handling
}
```

**AFTER:**
```php
public function login(Request $request)
{
    try {  // ✓ Wrapped in try-catch
        $request->validate([...]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([...], 422);  // ✓ JSON response
        }
        
        if (!$user->is_active) {
            return response()->json([...], 403);  // ✓ Proper status
        }
        
        $user->update(['last_login_at' => now()]);  // ✓ Column exists
        
        $token = $user->createToken('api-token')->plainTextToken;  // ✓ Proper token
        
        return response()->json([...], 200);
        
    } catch (QueryException $e) {  // ✓ Database errors
        \Log::error('Login database error: ' . $e->getMessage());
        return response()->json([...], 500);
    } catch (\Exception $e) {  // ✓ Other errors
        \Log::error('Login error: ' . $e->getMessage());
        return response()->json([...], 500);
    }
}
```

---

## Summary of Improvements

| Aspect | Before | After |
|--------|--------|-------|
| **Error Handling** | None | Try-catch with logging |
| **Field Mapping** | Mismatch (address vs location) | Correct mapping |
| **Error Response** | 500 exception | 422/403/500 JSON |
| **Token Generation** | Random string | Proper Token object |
| **Database Columns** | Missing last_login_at | Added via migration |
| **Validation Messages** | Generic | Specific field errors |
| **User Feedback** | Vague errors | Clear, actionable messages |
| **Security** | Exposing stack traces | Safe error messages |
| **Debugging** | Impossible | Comprehensive logging |

---

## Request/Response Examples

### Successful Registration
```
REQUEST:
POST /api/auth/register
Content-Type: application/json

{
  "full_name": "John Farmer",
  "email": "john@farm.com",
  "phone": "251912345678",
  "password": "SecurePass123",
  "password_confirmation": "SecurePass123",
  "address": "Kebele 5",
  "region": "Addis Ababa",
  "role": "farmer"
}

RESPONSE: 201 Created
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Farmer",
    "email": "john@farm.com",
    "phone": "251912345678",
    "role": "farmer",
    "location": "Kebele 5",
    "region": "Addis Ababa"
  },
  "token": "token_a1b2c3d4e5f6..."
}
```

### Failed Login
```
REQUEST:
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@farm.com",
  "password": "WrongPassword"
}

RESPONSE: 422 Unprocessable Content (instead of 500)
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": [
      "The provided credentials are incorrect."
    ]
  }
}
```

