# Token-Based API Authentication Setup

## Overview
Your AgriTech Platform API uses **personal access tokens** stored in the `personal_access_tokens` table for authentication. This is a token-based authentication system compatible with Sanctum.

## Architecture

### Components

1. **User Model** (`app/Models/User.php`)
   - Uses `HasApiTokens` trait
   - Can create tokens: `$user->createToken('api-token', ['*'])`

2. **PersonalAccessToken Model** (`app/Models/PersonalAccessToken.php`)
   - Stores tokens in `personal_access_tokens` table
   - Morphs to User via `tokenable_type` and `tokenable_id`

3. **ApiTokenGuard Middleware** (`app/Http/Middleware/ApiTokenGuard.php`)
   - Custom middleware to validate bearer tokens
   - Authenticates users from tokens
   - Returns 401 if token invalid/missing

4. **Auth Config** (`config/auth.php`)
   - API guard uses `token` driver
   - Default guard: `api`
   - Provider: `users` (Eloquent)

## Authentication Flow

```
┌─────────────────────────────────────────────────────────┐
│                    LOGIN REQUEST                         │
│  POST /api/auth/login                                   │
│  Body: { email, password }                              │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│              VALIDATE CREDENTIALS                        │
│  AuthController validates email & password              │
│  Returns 422 if invalid                                 │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│              CREATE TOKEN                                │
│  $user->createToken('api-token', ['*'])                 │
│  Token stored in personal_access_tokens table           │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│         RETURN TOKEN TO FRONTEND                         │
│ {                                                        │
│   "message": "Login successful",                         │
│   "token": "1|abcdefghijklmnopqrstuvwxyz...",           │
│   "token_type": "Bearer",                               │
│   "user": { id, name, email, role, ... }               │
│ }                                                        │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│       FRONTEND STORES TOKEN                              │
│  localStorage.setItem('token', token)                   │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│    PROTECTED API REQUEST                                │
│  GET /api/farmer/dashboard                              │
│  Header: Authorization: Bearer {token}                  │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│     APITOKEN GUARD MIDDLEWARE                           │
│  1. Extract bearer token from header                    │
│  2. Find token in personal_access_tokens                │
│  3. Get user from tokenable_id                          │
│  4. Authenticate user in request                        │
│  5. Set user to auth('api')->user()                     │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│       ROUTE HANDLER EXECUTED                             │
│  $request->user() returns authenticated user            │
│  Role middleware checks user role                       │
│  Returns data if authorized                             │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│         RETURN DATA TO FRONTEND                          │
│  { "data": [...], "message": "..." }                    │
└─────────────────────────────────────────────────────────┘
```

## Database Structure

### personal_access_tokens Table
```sql
CREATE TABLE personal_access_tokens (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  tokenable_type VARCHAR(255) NOT NULL,      -- 'App\Models\User'
  tokenable_id BIGINT NOT NULL,              -- User ID
  name VARCHAR(255) NOT NULL,                -- 'api-token'
  token VARCHAR(80) NOT NULL UNIQUE,         -- Hashed token
  abilities LONGTEXT,                        -- JSON: ["*"]
  last_used_at TIMESTAMP NULL,
  expires_at TIMESTAMP NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  FOREIGN KEY (tokenable_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (tokenable_id, tokenable_type)
);
```

## API Endpoints

### Public (No Authentication Required)
```
POST   /api/auth/register         - User registration
POST   /api/auth/login            - User login
POST   /api/auth/forgot-password  - Request password reset
POST   /api/auth/reset-password   - Reset password
GET    /api/health                - Health check
GET    /api/test-db               - Database test
GET    /api/marketplace/products  - List products
GET    /api/weather               - Weather data
GET    /api/market-prices         - Market prices
GET    /api/experts               - List experts
```

### Protected (Requires Valid Token)
All routes in `/api/` protected by `middleware(['api.token'])`:

#### Authentication
```
POST   /api/auth/logout           - Logout & revoke token
GET    /api/auth/profile          - Get user profile
POST   /api/auth/profile/update   - Update profile
POST   /api/auth/change-password  - Change password
GET    /api/auth/me               - Get current user
```

#### Admin Routes
```
GET    /api/admin/dashboard       - Admin dashboard
GET    /api/admin/users           - List users
GET    /api/admin/reports         - System reports
...
```

#### Farmer Routes
```
GET    /api/farmer/dashboard           - Farmer dashboard
GET    /api/farmer/farms               - List farms
POST   /api/farmer/farms               - Create farm
GET    /api/farmer/crops               - List crops
POST   /api/farmer/crops               - Create crop
GET    /api/farmer/products            - List products
POST   /api/farmer/products            - Create product
GET    /api/farmer/orders              - List orders
...
```

#### Buyer Routes
```
GET    /api/buyer/dashboard       - Buyer dashboard
GET    /api/buyer/cart            - Get cart
POST   /api/buyer/cart            - Add to cart
GET    /api/buyer/orders          - List orders
POST   /api/buyer/orders          - Create order
...
```

*And similar routes for: Supplier, Transport, Expert, Financial, Cooperative*

## Frontend Usage

### 1. Login and Get Token
```typescript
// src/api/auth.ts
export async function login(email: string, password: string) {
  const response = await fetch('http://localhost:8000/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });

  if (!response.ok) {
    throw new Error('Login failed');
  }

  const data = await response.json();
  
  // Store token
  localStorage.setItem('token', data.token);
  localStorage.setItem('user', JSON.stringify(data.user));
  
  return data;
}
```

### 2. Setup Axios with Token
```typescript
// src/api/config.ts
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

// Add token to every request
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 - redirect to login
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

### 3. Make Protected Requests
```typescript
// Any component
import api from '@/api/config';

export async function getFarmerDashboard() {
  const response = await api.get('/farmer/dashboard');
  return response.data;
}

export async function updateProfile(data: any) {
  const response = await api.post('/auth/profile/update', data);
  return response.data;
}

export async function logout() {
  const response = await api.post('/auth/logout');
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  return response.data;
}
```

## Testing the API

### Using cURL
```bash
# 1. Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "farmer@example.com",
    "password": "password123"
  }'

# Response:
# {
#   "message": "Login successful",
#   "user": { "id": 1, "name": "John Farmer", "email": "farmer@example.com", "role": "farmer" },
#   "token": "1|abcdefghijklmnopqrstuvwxyz...",
#   "token_type": "Bearer"
# }

# 2. Use token for protected route
curl -X GET http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz..." \
  -H "Accept: application/json"

# 3. Test invalid token (should get 401)
curl -X GET http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer invalid-token" \
  -H "Accept: application/json"

# 4. Logout
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz..." \
  -H "Accept: application/json"
```

### Using Postman
1. **Create Login Request**
   - Method: POST
   - URL: `http://localhost:8000/api/auth/login`
   - Body (raw JSON):
     ```json
     {
       "email": "farmer@example.com",
       "password": "password123"
     }
     ```

2. **Set Environment Variable**
   - After login, copy the `token` value
   - Create environment variable: `{{token}}` = `1|abcdefgh...`

3. **Use Token in Protected Requests**
   - Method: GET
   - URL: `http://localhost:8000/api/auth/profile`
   - Headers:
     ```
     Authorization: Bearer {{token}}
     Accept: application/json
     ```

## Configuration

### Environment Variables (`.env`)
```env
# Laravel App
APP_URL=http://localhost:8000

# Frontend URL (for CORS)
FRONTEND_URL=http://localhost:5173

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agritech
DB_USERNAME=root
DB_PASSWORD=

# Auth
AUTH_GUARD=api
AUTH_MODEL=App\Models\User
```

### Sanctum Config (`config/sanctum.php`)
- Stateful domains: `localhost:3000, localhost:5173, 127.0.0.1`
- CORS origins: Includes all frontend URLs
- Token storage: Database
- Token expiration: None (set `SANCTUM_TOKEN_EXPIRY_MINUTES` if needed)

## Security Best Practices

1. **Always use HTTPS in production**
   ```env
   APP_URL=https://your-domain.com
   ```

2. **Set Token Expiration**
   ```env
   SANCTUM_TOKEN_EXPIRY_MINUTES=1440  # 24 hours
   ```

3. **Revoke Old Tokens on New Login**
   ```env
   SANCTUM_REVOKE_PREVIOUS_TOKENS=true
   ```

4. **Store Token Securely**
   ```typescript
   // Use httpOnly cookie (best)
   // Or localStorage with caution (XSS risk)
   ```

5. **Validate CORS Origins**
   - Only allow trusted frontend domains
   - Update `config/sanctum.php` accordingly

6. **Use HTTPS Headers**
   ```env
   APP_URL=https://api.agritech.com
   SESSION_SECURE_COOKIES=true
   ```

7. **Rate Limiting**
   - Already set: `throttle:60,1` (60 requests per minute)
   - Increase for specific high-traffic endpoints

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated.",
  "errors": []
}
```

### 403 Forbidden (Role Check Failed)
```json
{
  "message": "User does not have access to this resource.",
  "errors": []
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### 500 Server Error
```json
{
  "message": "An error occurred",
  "errors": []
}
```

## Troubleshooting

### "Unauthenticated" Error

**Cause 1**: Token not sent in header
```
❌ GET /api/profile
✅ GET /api/profile (with header: Authorization: Bearer {token})
```

**Cause 2**: Token not in database
- Check `personal_access_tokens` table
- Verify token hasn't expired

**Cause 3**: Token format incorrect
```
❌ Authorization: {token}
❌ Authorization: Token {token}
✅ Authorization: Bearer {token}
```

### CORS Errors

**Cause**: Frontend domain not in whitelist

**Solution**: Add domain to `config/sanctum.php`
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:5173')),
```

### Role Middleware Error

**Message**: "User does not have access to this resource"

**Cause**: User role doesn't match route requirement

**Solution**: Verify user role matches the middleware requirement
```
Route::middleware(['role:farmer'])->group(...) // User must have role='farmer'
```

## Monitoring

### Check Active Tokens
```bash
php artisan tinker
>>> App\Models\PersonalAccessToken::count()  // Total tokens
>>> App\Models\PersonalAccessToken::latest()->first()  // Latest token
>>> App\Models\PersonalAccessToken::where('tokenable_id', 1)->get()  // User's tokens
```

### Clear Expired Tokens
```bash
# Add this to your scheduler (app/Console/Kernel.php)
$schedule->call(function () {
    PersonalAccessToken::where('expires_at', '<', now())->delete();
})->hourly();
```

## Useful Links

- [Laravel Authentication](https://laravel.com/docs/11.x/authentication)
- [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum)
- [HTTP Authentication](https://developer.mozilla.org/en-US/docs/Web/HTTP/Authentication)

---

✅ **Your API is ready for production use!**
