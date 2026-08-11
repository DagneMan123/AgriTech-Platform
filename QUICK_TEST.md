# Quick Testing Guide - Auth Endpoints

## Prerequisites
```bash
cd backend
php artisan migrate  # Run this first to add last_login_at column
```

## Test 1: Register New User

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "John Farmer",
    "email": "john@farm.com",
    "phone": "251912345678",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123",
    "address": "Kebele 5, Woreda 2",
    "region": "Addis Ababa",
    "role": "farmer"
  }'
```

**Expected Response (201):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Farmer",
    "email": "john@farm.com",
    "phone": "251912345678",
    "role": "farmer",
    "location": "Kebele 5, Woreda 2",
    "region": "Addis Ababa"
  },
  "token": "token_xxxxxxxxxxxxxxxxxxxxx"
}
```

---

## Test 2: Login with Registered User

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@farm.com",
    "password": "SecurePass123"
  }'
```

**Expected Response (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Farmer",
    "email": "john@farm.com",
    "phone": "251912345678",
    "role": "farmer",
    "location": "Kebele 5, Woreda 2",
    "region": "Addis Ababa",
    "is_active": true
  },
  "token": "token_xxxxxxxxxxxxxxxxxxxxx"
}
```

---

## Test 3: Invalid Login Attempt

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@farm.com",
    "password": "WrongPassword"
  }'
```

**Expected Response (422):**
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": [
      "The provided credentials are incorrect."
    ]
  }
}
```

---

## Test 4: Duplicate Email Registration

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Another User",
    "email": "john@farm.com",
    "phone": "251987654321",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123",
    "address": "Different Address",
    "region": "Oromia",
    "role": "buyer"
  }'
```

**Expected Response (422):**
```json
{
  "message": "Email address is already registered",
  "errors": {
    "registration": [
      "Email address is already registered"
    ]
  }
}
```

---

## Test 5: Duplicate Phone Registration

**Request:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Another User",
    "email": "different@farm.com",
    "phone": "251912345678",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123",
    "address": "Different Address",
    "region": "Oromia",
    "role": "buyer"
  }'
```

**Expected Response (422):**
```json
{
  "message": "Phone number is already registered",
  "errors": {
    "registration": [
      "Phone number is already registered"
    ]
  }
}
```

---

## Test 6: Validation Errors

**Request (Missing required fields):**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "John Farmer",
    "email": "invalid-email",
    "password": "short"
  }'
```

**Expected Response (422):**
```json
{
  "message": "The full_name field is required. (and 7 more errors)",
  "errors": {
    "email": ["Please enter a valid email address."],
    "phone": ["Phone number is required."],
    "password": ["Password must be at least 8 characters."],
    "password_confirmation": ["The password_confirmation field is required."],
    "address": ["Address is required."],
    "region": ["Region is required."],
    "role": ["Role is required."]
  }
}
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 500 error after fixes | Run `php artisan migrate` to add last_login_at column |
| 422 on registration | Check all required fields are included and valid |
| Can't login after registration | Verify email and password match exactly |
| Token not returned | Check if `createToken()` method exists in User model |
| CORS errors | Verify CorsMiddleware is properly configured |

---

## Frontend Integration

After successful login/registration, store the token and include it in all authenticated requests:

```javascript
// JavaScript/TypeScript Example
const token = response.data.token;
const headers = {
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${token}`
};

// Use these headers for all subsequent API calls
fetch('http://localhost:8000/api/auth/profile', {
  headers: headers
});
```

