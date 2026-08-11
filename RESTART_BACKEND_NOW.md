# 🔴 BACKEND NOT RUNNING - RESTART NOW

## Error You're Seeing
```
ERR_CONNECTION_REFUSED on localhost:8000
```

This means the backend server is **not running** or **crashed**.

---

## What to Do NOW (1 minute)

### Step 1: Open Command Prompt
```
Press: Windows Key + R
Type: cmd
Press: Enter
```

### Step 2: Navigate to Backend
```bash
cd C:\Users\Hena\Desktop\AgriTech_Platform\backend
```

### Step 3: Clear Everything
```bash
php artisan cache:clear
php artisan config:cache
```

### Step 4: Delete Old Database (SQLite)
```bash
del database\database.sqlite
```

### Step 5: Create Fresh Database
```bash
touch database\database.sqlite
php artisan migrate
```

### Step 6: Start Backend
```bash
php artisan serve
```

**Wait for this message:**
```
Laravel development server started: http://127.0.0.1:8000
```

---

## Step 7: Test Backend is Running

Open a **NEW** command prompt and run:
```bash
curl http://localhost:8000/api/health
```

Should return:
```json
{"status":"ok","time":"..."}
```

If you see this ✅, backend is working!

---

## Step 8: Refresh Frontend and Try Login

1. Open browser: `http://localhost:5173`
2. Press: **Ctrl + Shift + R** (hard refresh)
3. Try login

---

## If Still Not Working

### Check 1: Is Backend Still Running?
Look at the command prompt where you ran `php artisan serve`
- Should show: `Laravel development server started`
- Should NOT show errors
- If it crashed, see "If Backend Crashes" section below

### Check 2: Check Port 8000
```bash
# Is something using port 8000?
netstat -ano | findstr :8000
```

If you see a process, that's your backend. Good!
If not, backend crashed.

### If Backend Crashes

Usually happens if database can't be created. Try this:

```bash
# In backend directory:
cd C:\Users\Hena\Desktop\AgriTech_Platform\backend

# Create fresh database file
type nul > database\database.sqlite

# Run migrations
php artisan migrate --force

# Start server
php artisan serve
```

---

## Complete Commands (Copy & Paste)

**Open Command Prompt and run:**

```bash
cd C:\Users\Hena\Desktop\AgriTech_Platform\backend
php artisan cache:clear
php artisan config:cache
del database\database.sqlite 2>nul
type nul > database\database.sqlite
php artisan migrate
php artisan serve
```

Then watch the output. Should say:
```
Laravel development server started: http://127.0.0.1:8000
```

---

## Verification Checklist

- [ ] Command Prompt shows "Laravel development server started"
- [ ] No errors in the terminal
- [ ] `curl http://localhost:8000/api/health` returns JSON
- [ ] Backend stays running (doesn't crash)
- [ ] Frontend can reach backend (try login)

All checked? Backend is fixed! ✅

---

## If All Else Fails

Try this nuclear option:

```bash
cd C:\Users\Hena\Desktop\AgriTech_Platform\backend

# Kill any old processes
taskkill /F /IM php.exe

# Wait 3 seconds
timeout /t 3

# Clean everything
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Recreate database
rmdir /s /q database 2>nul
mkdir database
type nul > database\database.sqlite

# Fresh install
php artisan migrate --force

# Start
php artisan serve
```

---

**Do this now. Backend needs to be running for frontend to connect.** ⏰
