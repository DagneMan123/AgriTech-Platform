# Testing Login Functionality

## Prerequisites

1. Ensure backend is running on `http://localhost:8000`
2. Ensure PostgreSQL database is running with `agritech` database
3. User table must have at least one test user

## Quick Test - Using cURL

### 1. Create a Test User (if needed)

If you don't have a test user, create one with the registration endpoint first, or seed the database:

```bash
cd backend
php artisan db:seed --class=UserSeeder
```

### 2. Test Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "farmer@example.com",
    "password": "password123"
  }'
```

### 3. Expected Success Response

```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Farmer Name",
    "email": "farmer@example.com",
    "phone": "+251...",
    "role": "farmer"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "Bearer"
}
```

### 4. Use Token for Authenticated Requests

```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

## Testing from Frontend

### 1. Update Frontend API Config

File: `frontend/src/api/config.ts`

```typescript
const API_BASE_URL = 'http://localhost:8000/api';

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});
```

### 2. Test Login View

1. Open `http://localhost:5173/login` in browser
2. Enter test credentials
3. Check browser console for any errors
4. Token should be stored in localStorage or sessionStorage

### 3. Verify Token Storage

In browser DevTools (F12):
```javascript
// Check localStorage
localStorage.getItem('auth_token')

// Check sessionStorage
sessionStorage.getItem('auth_token')
```

## Debugging

### If Login Fails

Check logs at `backend/storage/logs/laravel.log`:

```bash
tail -f backend/storage/logs/laravel.log
```

Look for:
- "Login error:" messages
- "Token validation exception:" messages
- Database connection errors

### Common Issues

1. **Invalid credentials error**
   - Verify user exists in database
   - Check password hash matches (use bcrypt)

2. **Database connection error**
   - Verify `.env` has correct DB_* settings
   - Check PostgreSQL is running
   - Verify `agritech` database exists

3. **Token not working**
   - Verify token format (should be 160 characters - 2x SHA256 of 80 random chars)
   - Check `personal_access_tokens` table exists
   - Verify token is in Bearer format: `Bearer TOKEN_HERE`

4. **Authentication still fails after login**
   - Clear browser cache/cookies
   - Clear bootstrap cache: delete files in `backend/bootstrap/cache/`
   - Restart backend server

## API Endpoints

### Authentication

```
POST   /api/login              - Login user
POST   /api/register           - Register new user
POST   /api/logout             - Logout user (requires auth)
GET    /api/auth/me            - Get current user (requires auth)
POST   /api/auth/change-password - Change password (requires auth)
POST   /api/forgot-password    - Request password reset
POST   /api/reset-password     - Reset password with token
```

## Test Data

You can create test users using the register endpoint:

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Farmer",
    "email": "farmer@test.com",
    "phone": "+251912345678",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "farmer"
  }'
```

## Performance Notes

- Login should complete in < 100ms
- Token validation should be instant (< 10ms)
- No memory errors should occur
- Stack should remain stable

If performance issues persist:
- Check database indices on `personal_access_tokens` table
- Verify `users` table has indices on `email` and `is_active`
- Consider caching frequently accessed user data

## Next Steps

After successful login:

1. Test farmer endpoints: `/api/farmer/dashboard`
2. Test role-based access
3. Test token expiration
4. Test token revocation on logout
