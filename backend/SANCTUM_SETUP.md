# Laravel Sanctum API Authentication Setup

## Overview
This document outlines the Sanctum configuration for your AgriTech Platform API. Sanctum provides a lightweight authentication system for SPAs (Single Page Applications) and mobile applications.

## What Has Been Configured

### 1. **User Model** (`app/Models/User.php`)
✅ Uses `HasApiTokens` trait from Laravel Sanctum
- Enables token creation via `$user->createToken()`
- Manages personal access tokens automatically

### 2. **Authentication Guard** (`config/auth.php`)
✅ Configured with:
- Default guard: `sanctum` (for API routes)
- Guard driver: `sanctum` (uses personal access tokens)
- Provider: `users` (Eloquent User model)

### 3. **Sanctum Configuration** (`config/sanctum.php`)
✅ Key settings:
- **Stateful Domains**: `localhost:3000, localhost:5173, localhost:8080, 127.0.0.1, etc.`
- **CORS Origins**: Configured to allow frontend domains
- **Token Storage**: Database (personal_access_tokens table)
- **Token Prefix**: Empty (configurable via .env)
- **Expiration**: No default expiration (configurable)

### 4. **API Routes** (`routes/api.php`)
✅ Protected routes use: `middleware(['auth:sanctum'])`
- All authenticated endpoints wrapped in this middleware
- Public endpoints (register, login, health checks) remain unprotected

## How It Works

### Authentication Flow

```
1. User Registration/Login
   ├─ POST /api/auth/register or /api/auth/login
   ├─ User credentials validated
   └─ Token generated: user->createToken('api-token', ['*'])

2. Token Storage
   ├─ Token stored in personal_access_tokens table
   ├─ Token associated with User (polymorphic)
   └─ Returned as plainTextToken to frontend

3. API Requests
   ├─ Frontend sends: Authorization: Bearer {token}
   ├─ Sanctum middleware validates token
   ├─ User authenticated automatically
   └─ $request->user() available in controllers

4. Logout
   ├─ POST /api/auth/logout
   ├─ Current token deleted: $request->user()->currentAccessToken()->delete()
   └─ User no longer authenticated
```

## Frontend Integration

### Setting Authorization Header

```typescript
// JavaScript/Vue/React
const token = localStorage.getItem('token');
const headers = {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json',
  'Accept': 'application/json'
};

fetch('/api/farmer/dashboard', {
  method: 'GET',
  headers: headers
});
```

### Example API Calls

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Response includes token:
# {
#   "token": "1|abcdefghijklmnopqrstuvwxyz...",
#   "token_type": "Bearer",
#   "user": {...}
# }

# Use token for protected endpoint
curl -X GET http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz..."

# Logout
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz..."
```

## Database Structure

### personal_access_tokens Table
```sql
CREATE TABLE personal_access_tokens (
  id BIGINT PRIMARY KEY,
  tokenable_type VARCHAR(255),     -- 'App\Models\User'
  tokenable_id BIGINT,              -- User ID
  name VARCHAR(255),                -- 'api-token'
  token VARCHAR(80) UNIQUE,         -- Hashed token
  abilities TEXT,                   -- JSON: ['*']
  last_used_at TIMESTAMP NULL,
  expires_at TIMESTAMP NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

## Protected Routes

All routes wrapped with `middleware(['auth:sanctum'])`:

### Authenticated Required Routes
- `/api/auth/logout` - POST
- `/api/auth/profile` - GET
- `/api/auth/profile/update` - POST
- `/api/auth/change-password` - POST
- `/api/auth/me` - GET
- `/api/admin/*` - All admin routes
- `/api/farmer/*` - All farmer routes
- `/api/buyer/*` - All buyer routes
- `/api/supplier/*` - All supplier routes
- `/api/transport/*` - All transport routes
- `/api/expert/*` - All expert routes
- `/api/financial/*` - All financial routes
- `/api/cooperative/*` - All cooperative routes
- `/api/notifications/*` - All notification routes

### Public Routes (No Authentication)
- `/api/health` - GET
- `/api/diagnostic/health` - GET
- `/api/test-auth` - GET
- `/api/test-db` - GET
- `/api/auth/register` - POST
- `/api/auth/login` - POST
- `/api/auth/forgot-password` - POST
- `/api/auth/reset-password` - POST
- `/api/marketplace/products` - GET
- `/api/marketplace/categories` - GET
- `/api/weather` - GET
- `/api/market-prices` - GET
- `/api/experts` - GET
- `/api/locations` - GET

## Role-Based Access Control

Routes also use `middleware(['role:farmer'])` for additional role checking:

```php
Route::middleware(['auth:sanctum', 'role:farmer'])->prefix('farmer')->group(function () {
    // Only users with role='farmer' can access
});
```

Available roles:
- `admin`
- `farmer`
- `buyer`
- `supplier`
- `transport`
- `expert`
- `financial`
- `cooperative`

## Error Handling

### Unauthenticated Request
```json
{
  "message": "Unauthenticated.",
  "errors": []
}
```
Status: 401 Unauthorized

### Unauthorized Role
```json
{
  "message": "User does not have access to this resource.",
  "errors": []
}
```
Status: 403 Forbidden

## Environment Variables

Add to `.env`:
```env
# Default auth guard (set to sanctum)
AUTH_GUARD=sanctum

# Token prefix (optional)
SANCTUM_TOKEN_PREFIX=

# Token expiration in minutes (0 = no expiration)
SANCTUM_TOKEN_EXPIRY_MINUTES=0

# Revoke previous tokens on new login
SANCTUM_REVOKE_PREVIOUS_TOKENS=false

# Frontend URL for CORS
FRONTEND_URL=http://localhost:5173

# CORS origins
SANCTUM_CORS_ORIGINS=http://localhost:3000,http://localhost:5173,http://localhost:8080
```

## Testing the Setup

### Test Endpoint
```bash
# Check if authentication is working
curl -X GET http://localhost:8000/api/test-auth \
  -H "Authorization: Bearer {your-token}"

# Response with valid token:
# {
#   "authenticated": true,
#   "user": {...},
#   "token_present": true,
#   "guard": "sanctum",
#   "timestamp": "2026-10-06T..."
# }
```

## Troubleshooting

### Issue: "Unauthenticated" on Valid Token

**Cause**: Token not being sent correctly

**Solution**:
1. Verify token format: `Authorization: Bearer {token}`
2. Check token exists in `personal_access_tokens` table
3. Verify `Content-Type: application/json` header
4. Check CORS configuration if from different domain

### Issue: CORS Errors

**Cause**: Frontend domain not in whitelist

**Solution**: Add domain to `config/sanctum.php` stateful domains or env variable `SANCTUM_CORS_ORIGINS`

### Issue: "User does not have access"

**Cause**: Role middleware rejecting request

**Solution**: 
1. Verify user role matches route requirement
2. Check `app/Http/Middleware/RoleMiddleware.php`
3. Update user role if necessary

## Security Best Practices

1. **Always use HTTPS** in production
2. **Set token expiration** via `SANCTUM_TOKEN_EXPIRY_MINUTES`
3. **Revoke old tokens** via `SANCTUM_REVOKE_PREVIOUS_TOKENS`
4. **Validate CORS origins** - only allow trusted frontend domains
5. **Use CSRF protection** if handling form data
6. **Rotate tokens regularly** by requiring re-login
7. **Hash tokens** - already done by Sanctum automatically

## Additional Resources

- [Laravel Sanctum Documentation](https://laravel.com/docs/11.x/sanctum)
- [Stateful SPA Authentication](https://laravel.com/docs/11.x/sanctum#spa-authentication)
- [Token Abilities](https://laravel.com/docs/11.x/sanctum#token-abilities)

## Quick Checklist

- ✅ User model has `HasApiTokens` trait
- ✅ Auth guard set to `sanctum`
- ✅ `personal_access_tokens` table exists
- ✅ Routes wrapped with `auth:sanctum` middleware
- ✅ CORS properly configured
- ✅ Role middleware in place
- ✅ Frontend sends `Authorization: Bearer {token}` header

Your Sanctum authentication is fully configured and ready to use!
