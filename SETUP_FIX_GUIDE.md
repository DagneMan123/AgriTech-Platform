# AgriTech Platform - Setup & Fix Guide

## Issues Fixed

### 1. ✅ CORS Configuration
- Added proper CORS middleware to handle preflight OPTIONS requests
- Configured allowed origins for local development
- Added necessary headers for credentials and requests

### 2. ✅ Backend Changes
- Updated `bootstrap/app.php` to include global CORS middleware

## Required Setup Steps

### Step 1: Start the Backend Server

Open a terminal in the backend folder and run:

```bash
cd backend
php artisan serve
```

The backend should run on: **http://localhost:8000**

### Step 2: Start the Vite Frontend Dev Server

Open a NEW terminal in the frontend folder and run:

```bash
cd frontend
npm install  # Only needed if node_modules is missing
npm run dev
```

The frontend should run on: **http://localhost:5173**

### Step 3: Access the Application

Open your browser and navigate to: **http://localhost:5173**

## What Each Fix Does

### CORS Middleware Fix
- **Problem**: Browser was blocking requests due to missing CORS headers
- **Solution**: Added global middleware to handle:
  - OPTIONS (preflight) requests
  - Proper `Access-Control-Allow-Origin` headers
  - Credentials support
  - Allowed methods and headers

### Why You're Getting Errors

**WebSocket Error**: Vite dev server isn't running
- Fix: Run `npm run dev` in the frontend folder

**CORS Error**: Backend wasn't sending proper CORS headers for cross-origin requests
- Fix: CORS middleware now added to bootstrap

**500 Error**: This typically happens when:
1. Database connection fails
2. User doesn't exist in database
3. Laravel tries to load Eloquent models with infinite recursion

## Testing the Fix

1. Make sure both servers are running
2. Go to http://localhost:5173
3. Try logging in with test credentials
4. Check browser console for any remaining errors

## If You Still Get Errors

### Check Backend Logs
```bash
cd backend
tail -f storage/logs/laravel.log
```

### Verify Database Connection
```bash
cd backend
php artisan tinker
```

Then test:
```php
DB::table('users')->count()
```

### Verify API Response
In your browser console:
```javascript
fetch('http://localhost:8000/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  credentials: 'include',
  body: JSON.stringify({
    email: 'admin@example.com',
    password: 'password'
  })
}).then(r => r.json()).then(console.log)
```

## Additional Notes

- Both servers must run simultaneously
- Keep browser developer tools open (F12) to monitor network requests
- The Vite proxy in `vite.config.ts` redirects `/api` calls to the backend
- All CORS requests include credentials for session/token support
