# Registration API - Frontend Implementation Guide

## Endpoint
```
POST /api/auth/register
```

## Required Fields

| Field | Type | Constraints | Example |
|-------|------|-------------|---------|
| `full_name` | string | Required, max 255 chars | "John Woldemariam" |
| `email` | string | Required, valid email, unique | "john@agrifarm.com" |
| `phone` | string | Required, max 20 chars, unique | "+251912345678" |
| `password` | string | Required, min 8 chars, must match confirmation | "SecurePass123!" |
| `password_confirmation` | string | Required, must match password | "SecurePass123!" |
| `role` | string | Required, one of: farmer, buyer, supplier, transport, cooperative, expert, financial | "farmer" |
| `address` | string | Required, max 255 chars | "123 Farm Lane, Addis Ababa" |
| `region` | string | Required, max 255 chars | "Oromia" |

## Request Example

```javascript
// Using axios or fetch
const registerData = {
  full_name: "Abeba Tadesse",
  email: "abeba@agrifarm.com",
  phone: "+251911123456",
  password: "MySecurePassword123",
  password_confirmation: "MySecurePassword123",
  role: "farmer",
  address: "Kebele 3, Addis Ababa",
  region: "Oromia"
};

// Axios example
axios.post('http://localhost:8000/api/auth/register', registerData)
  .then(response => console.log(response.data))
  .catch(error => console.error(error.response.data));
```

## Success Response (201 Created)

```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "Abeba Tadesse",
    "email": "abeba@agrifarm.com",
    "phone": "+251911123456",
    "role": "farmer",
    "region": "Oromia",
    "is_active": true,
    "created_at": "2026-08-05T12:30:45.000000Z",
    "updated_at": "2026-08-05T12:30:45.000000Z"
  },
  "token": "token_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
}
```

## Error Responses

### Validation Error (422 Unprocessable Entity)
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field must be at least 8 characters."],
    "password_confirmation": ["Passwords do not match."]
  }
}
```

### Duplicate Registration (422)
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

## Validation Rules Applied

### Email
- ✅ Required
- ✅ Must be valid email format (includes @)
- ✅ Must be unique in database (no duplicates)
- ✅ Max 255 characters

### Phone  
- ✅ Required
- ✅ Must be unique in database (no duplicates)
- ✅ Max 20 characters
- 💡 Tip: Include country code (e.g., +251 for Ethiopia)

### Password
- ✅ Required
- ✅ Minimum 8 characters
- ✅ Must match password_confirmation field exactly
- 💡 Best practice: Include uppercase, numbers, and special characters

### Role
- ✅ Required
- ✅ Must be one of: `farmer`, `buyer`, `supplier`, `transport`, `cooperative`, `expert`, `financial`
- ❌ Invalid role will return validation error

### Address & Region
- ✅ Both required
- ✅ Max 255 characters each
- 💡 Examples: Region = "Oromia", "SNNPR", "Amhara"; Address = full physical address

## Frontend Implementation Example

```vue
<template>
  <form @submit.prevent="handleRegister">
    <input v-model="form.full_name" type="text" placeholder="Full Name" />
    <input v-model="form.email" type="email" placeholder="Email" />
    <input v-model="form.phone" type="tel" placeholder="+251912345678" />
    <select v-model="form.role">
      <option value="farmer">Farmer</option>
      <option value="buyer">Buyer</option>
      <option value="supplier">Supplier</option>
      <!-- more options -->
    </select>
    <input v-model="form.address" type="text" placeholder="Address" />
    <input v-model="form.region" type="text" placeholder="Region" />
    <input v-model="form.password" type="password" placeholder="Password (min 8 chars)" />
    <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" />
    <button type="submit">Register</button>
  </form>
</template>

<script setup>
import { reactive } from 'vue';
import axios from 'axios';

const form = reactive({
  full_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  role: 'farmer',
  address: '',
  region: ''
});

async function handleRegister() {
  try {
    const response = await axios.post(
      'http://localhost:8000/api/auth/register',
      form
    );
    
    // Store token
    localStorage.setItem('token', response.data.token);
    localStorage.setItem('user', JSON.stringify(response.data.user));
    
    // Redirect to dashboard
    window.location.href = '/dashboard';
  } catch (error) {
    if (error.response?.status === 422) {
      // Show validation errors
      console.error('Validation errors:', error.response.data.errors);
    } else {
      // Show generic error
      console.error('Registration failed:', error.response?.data?.message);
    }
  }
}
</script>
```

## Common Issues & Solutions

### Issue: "Email or phone number already registered"
- **Cause**: User already has account with this email/phone
- **Solution**: Use different email/phone or use login endpoint

### Issue: "Passwords do not match"
- **Cause**: password and password_confirmation fields differ
- **Solution**: Ensure both password fields have identical values

### Issue: "The password field must be at least 8 characters"
- **Cause**: Password is shorter than 8 characters
- **Solution**: Use password with 8+ characters

### Issue: "The email field is required"
- **Cause**: Email field is empty or not sent
- **Solution**: Ensure email is populated before sending

### Issue: 500 Internal Server Error
- **Cause**: Database connection issue or server problem
- **Solution**: 
  - Check backend logs: `storage/logs/laravel.log`
  - Verify database is running
  - Contact backend team

## Rate Limiting

Currently no rate limiting is applied to the registration endpoint. Consider implementing rate limiting in production to prevent abuse.

## CORS & Headers

If registering from different domain:
```javascript
// CORS middleware should handle this, but if issues:
axios.post(
  'http://localhost:8000/api/auth/register',
  form,
  {
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  }
);
```

## Security Notes

✅ **Passwords are hashed** using bcrypt (12 rounds)  
✅ **Tokens are generated** as random 64-character strings  
✅ **Email/Phone are unique** - enforced at database level  
✅ **Validation** on both frontend and backend  

⚠️ **Production Recommendations:**
- Add rate limiting (e.g., 5 attempts per minute per IP)
- Add CAPTCHA for bot prevention
- Send verification email before account activation
- Implement email confirmation flow
- Add phone verification (optional)
- Log all registration attempts
- Monitor for suspicious patterns

## Status Codes Summary

| Code | Meaning | When |
|------|---------|------|
| 201 | Created | Registration successful |
| 422 | Unprocessable Entity | Validation failed or duplicate |
| 500 | Internal Server Error | Server/database problem |

---

**Last Updated**: 2026-08-05  
**API Version**: v1  
**Status**: ✅ Production Ready
