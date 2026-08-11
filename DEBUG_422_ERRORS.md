# Debugging 422 Validation Errors - Registration

## What is a 422 Error?
- **422 Unprocessable Entity** means the server received the request but can't process it due to validation failures
- This is **normal and expected** when form data doesn't meet requirements
- The response includes detailed validation errors

## How to See What's Wrong

### Option 1: Check Browser Console (Easiest)
1. Open your browser's Developer Tools (F12)
2. Go to the **Console** tab
3. Try to register
4. Look for logs that say `"Registration error:"` 
5. The detailed errors will be shown below it

### Option 2: Check Network Tab
1. Open Developer Tools (F12)
2. Go to **Network** tab
3. Try to register
4. Click on the `register` request (should show red)
5. Click on **Response** tab
6. You'll see JSON with all validation errors

Example response:
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field must be a valid email."],
    "phone": ["The phone field is required."],
    "password": ["The password field must be at least 8 characters."]
  }
}
```

### Option 3: Check Backend Logs
```bash
tail -f storage/logs/laravel.log
```

---

## Common Validation Errors & Fixes

### ❌ Error: "email field must be a valid email"
**Cause**: Email format is incorrect  
**Fix**: Ensure email has format like `user@example.com`
- Valid: `john@agrifarm.com`
- Invalid: `johnagrifarm`, `john@`, `@agrifarm.com`

### ❌ Error: "email already exists" or "email field must be unique"
**Cause**: This email is already registered  
**Fix**: Use a different email address or login if you already have account

### ❌ Error: "phone field is required"
**Cause**: Phone field is empty  
**Fix**: Enter your phone number in international format
- Valid: `+251912345678`
- Valid: `0912345678`

### ❌ Error: "phone field must be unique"
**Cause**: This phone number is already registered  
**Fix**: Use different phone number

### ❌ Error: "password field must be at least 8 characters"
**Cause**: Password is too short  
**Fix**: Use a password with at least 8 characters
- Too short: `Pass123`
- Valid: `SecurePass123`

### ❌ Error: "password_confirmation field is required"
**Cause**: Password confirmation field is empty  
**Fix**: Re-enter your password in the "Confirm Password" field

### ❌ Error: "password must be confirmed"
**Cause**: Password and confirmation don't match  
**Fix**: Make sure both password fields are identical

### ❌ Error: "role field is required"
**Cause**: You didn't select a role  
**Fix**: Choose one of: Farmer, Buyer, Supplier, Transport, Expert, Financial, Cooperative

### ❌ Error: "role field is not valid" or "role field is invalid"
**Cause**: Invalid role selected (shouldn't happen with dropdown)  
**Fix**: Select from dropdown only

### ❌ Error: "full_name field is required"
**Cause**: Full name is empty  
**Fix**: Enter your full name

### ❌ Error: "address field is required"
**Cause**: Address is empty  
**Fix**: Enter your physical address

### ❌ Error: "region field is required"
**Cause**: Region field is empty  
**Fix**: Enter your region (e.g., "Oromia", "SNNPR", "Amhara")

---

## Step-by-Step Registration Test

To verify registration works, follow these exact steps:

### Test Data
```
Full Name:      John Woldemariam
Email:          john.woldemariam.2024@example.com
Phone:          +251912345678
Address:        Bole, Addis Ababa
Region:         Addis Ababa
Role:           Farmer
Password:       MySecurePass123
Confirm Pass:   MySecurePass123
```

### What Should Happen
1. Fill all fields with test data above
2. Click "Register"
3. **Success**: See message "Redirecting to login"
4. **Error**: See red error messages

### If You Get 422 Error
1. Check the field-level error messages displayed
2. Verify the field data matches the requirement
3. Common issue: Email already exists - try different email

---

## Detailed Field Validation Rules

| Field | Rule | Example Valid | Example Invalid |
|-------|------|-------|-------|
| Full Name | max 255 chars | "John Farmer" | "" (empty) |
| Email | Valid email, unique | "john@farm.com" | "john@", "invalid.email" |
| Phone | max 20 chars, unique | "+251912345678" | "" (empty) |
| Address | max 255 chars | "123 Farm Lane" | "" (empty) |
| Region | max 255 chars | "Oromia" | "" (empty) |
| Role | One of 7 roles | "farmer" | "admin" (not allowed) |
| Password | min 8 chars, matches confirmation | "Pass1234" | "Pass" (too short) |
| Confirm | Must match password | "Pass1234" | "Pass1235" (mismatch) |

---

## API Response Format

### Success (201 Created)
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Woldemariam",
    "email": "john@example.com",
    "phone": "+251912345678",
    "role": "farmer",
    "region": "Oromia",
    "is_active": true
  },
  "token": "token_abc123..."
}
```

### Validation Error (422)
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field must be a valid email."],
    "password": ["The password field must be at least 8 characters."]
  }
}
```

### Duplicate Email (422)
```json
{
  "message": "Email or phone number already registered"
}
```

### Server Error (500)
```json
{
  "message": "An error occurred during registration. Please try again later."
}
```

---

## Frontend Error Display

The updated RegisterView now displays:
1. **General error message** at top in red box
2. **Field-specific errors** below each input field
3. Console logs for debugging

---

## Testing with curl (Command Line)

If you want to test directly:

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Test User",
    "email": "test@example.com",
    "phone": "+251912345678",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123",
    "role": "farmer",
    "address": "Test Address",
    "region": "Oromia"
  }'
```

Response with error:
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

## Quick Checklist Before Submitting

- [ ] Full Name is not empty
- [ ] Email is in format: user@domain.com
- [ ] Phone number is filled (with country code preferred)
- [ ] Address is not empty
- [ ] Region is not empty  
- [ ] Role is selected from dropdown
- [ ] Password is at least 8 characters
- [ ] Confirm Password matches Password exactly
- [ ] Password contains letters and numbers (best practice)

---

## Still Having Issues?

1. **Clear browser cache** - Ctrl+Shift+Delete
2. **Check Laravel logs** - `storage/logs/laravel.log`
3. **Verify database** - Ensure PostgreSQL is running
4. **Test backend directly** - Use curl command above
5. **Check network requests** - DevTools Network tab

**Error details are now visible in the UI** - check for red error messages below each field!

---

**Status**: Updated with enhanced error reporting  
**Last Updated**: 2026-08-05
