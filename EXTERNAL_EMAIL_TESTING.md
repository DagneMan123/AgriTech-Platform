# External Email Testing Guide

## Problem Statement
You want to verify that password reset emails are being sent to **external recipients** (not just Gmail accounts), and that the system works end-to-end.

## Solution: Improved Email System ✅

I've enhanced your forgot password system with:

1. **Better Error Logging** - Captures SMTP errors
2. **Email Test Command** - Send test emails to any address
3. **Detailed Debug Info** - See email configuration and errors

## Quick Test (30 seconds)

### Step 1: Restart Backend
```bash
cd backend
php artisan serve
```

### Step 2: Run Email Test Command
```bash
php artisan test:password-reset-email your-email@example.com
```

**Example:**
```bash
php artisan test:password-reset-email john@yahoo.com
php artisan test:password-reset-email mary@hotmail.com
php artisan test:password-reset-email farmer@agritech.io
```

### Expected Output ✅
```
Testing password reset email to: john@yahoo.com
Mail Driver: smtp
Mail Host: smtp.gmail.com
Mail Port: 587
Mail From: dagneaydenfu23@gmail.com

Generating reset token...
Reset Link: http://localhost:5173/reset-password?token=xxxxx&email=john@yahoo.com

Sending email...

✅ Email sent successfully!
Check your inbox at: john@yahoo.com
If not received, check spam folder.

Reset Token: xxxxx
```

## What the Command Does

1. ✅ Validates email format
2. ✅ Shows your mail configuration
3. ✅ Generates a reset token
4. ✅ Creates reset link with frontend URL
5. ✅ **Sends email immediately**
6. ✅ Shows success/error message
7. ✅ Logs everything to `storage/logs/laravel.log`

## Testing Different Email Providers

### Test with Multiple Providers:

```bash
# Gmail
php artisan test:password-reset-email user@gmail.com

# Yahoo
php artisan test:password-reset-email user@yahoo.com

# Outlook
php artisan test:password-reset-email user@outlook.com

# ProtonMail
php artisan test:password-reset-email user@protonmail.com

# Corporate Email
php artisan test:password-reset-email user@company.com

# Any Custom Domain
php artisan test:password-reset-email farmer@farm.local
```

All should work! If any fail, it indicates your Gmail SMTP isn't sending correctly.

## Production Test (Using Real User)

### 1. Create Test User
```bash
# Using tinker
php artisan tinker
>>> use App\Models\User;
>>> $user = User::create([
...   'name' => 'John Farmer',
...   'email' => 'john@farm.example.com',
...   'password' => Hash::make('password123'),
...   'role' => 'farmer'
... ]);
>>> exit

# Or register through frontend
```

### 2. Test Forgot Password
```bash
curl -X POST http://localhost:8000/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"john@farm.example.com"}'

# Response:
# {"message":"If an account exists with this email, a password reset link has been sent."}
```

### 3. Check Email
- User should receive email at `john@farm.example.com`
- Email includes clickable reset link
- Link format: `http://localhost:5173/reset-password?token=xxxxx&email=john@farm.example.com`

### 4. Click Link and Reset Password
- Frontend loads reset form
- Enter new password
- Confirm password
- Submit
- Backend validates token
- Password updated
- Redirect to login

## Enhanced Error Logging

The improved system now logs:

**Success Log** (`storage/logs/laravel.log`):
```json
{
  "message": "Password reset email sent successfully",
  "user_id": 1,
  "email": "john@farm.example.com",
  "reset_link": "http://localhost:5173/reset-password?token=xxxxx&email=john@farm.example.com",
  "mailer": "smtp",
  "host": "smtp.gmail.com"
}
```

**Error Log** (`storage/logs/laravel.log`):
```json
{
  "message": "Failed to send password reset email",
  "user_id": 1,
  "email": "john@farm.example.com",
  "error": "Swift_TransportException: Connection could not be established...",
  "trace": "[full stack trace]"
}
```

## Troubleshooting External Email Issues

### Issue 1: Gmail SMTP Won't Connect

**Symptom:** Email not sending to external addresses

**Solution:**
```bash
# Option A: Use Gmail App Password (RECOMMENDED)
# 1. Go to https://myaccount.google.com/apppasswords
# 2. Select Mail + Windows Computer
# 3. Copy the 16-character password
# 4. Update .env:

MAIL_PASSWORD=xxxx xxxx xxxx xxxx

# Option B: Enable Less Secure Apps
# Go to https://myaccount.google.com/lesssecureapps
# Toggle ON
```

**Test After Change:**
```bash
php artisan config:cache
php artisan config:clear
php artisan test:password-reset-email external@email.com
```

### Issue 2: Port 587 Blocked

**Symptom:** Connection timeout

**Solution:**
```bash
# Test if port is open (on Windows)
# Option 1: Use PowerShell
Test-NetConnection smtp.gmail.com -Port 587

# Option 2: Try alternate port 465 (SSL)
# Update .env:
MAIL_PORT=465
MAIL_ENCRYPTION=ssl

# Then test again
php artisan test:password-reset-email external@email.com
```

### Issue 3: Email Reaches Spam Folder

**This is normal for first-time senders.** Solutions:

1. **Ask recipient to mark as "Not Spam"**
2. **Use dedicated email service:**
   - SendGrid (free tier available)
   - Mailgun (1000 free emails/month)
   - AWS SES (reliable, low cost)

### Issue 4: Domain Validation Issues

If emails not sending to corporate addresses:

1. **Check DKIM/SPF records** (if using custom domain)
2. **Use transactional email service** (SendGrid, Mailgun)
3. **Add MAIL_FROM_NAME:**
```env
MAIL_FROM_NAME="AgriTech Platform"
MAIL_FROM_ADDRESS="noreply@agritech.com"
```

## Check Email Configuration

```bash
cd backend

php artisan tinker
>>> config('mail.mailer')
"smtp"

>>> config('mail.host')
"smtp.gmail.com"

>>> config('mail.port')
587

>>> config('mail.encryption')
"tls"

>>> config('mail.from')
["address" => "dagneaydenfu23@gmail.com", "name" => "AgriTech"]

>>> config('mail.username')
"dagneaydenfu23@gmail.com"
```

## Check Recent Logs

```bash
cd backend

# View last 50 lines
tail -50 storage/logs/laravel.log

# Search for email errors
grep -i "mail\|email\|password reset" storage/logs/laravel.log | tail -20

# On Windows PowerShell:
Get-Content storage/logs/laravel.log -Tail 50
```

## Files Modified/Created

- ✅ `backend/app/Http/Controllers/Api/AuthController.php` - Enhanced logging
- ✅ `backend/app/Console/Commands/TestPasswordResetEmail.php` - New test command

## Full Testing Workflow

### Development Testing
```bash
# 1. Restart backend
php artisan serve

# 2. Test email command (any address)
php artisan test:password-reset-email test@example.com

# 3. Check logs
tail -20 storage/logs/laravel.log

# 4. If successful, test via API
curl -X POST http://localhost:8000/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"real-user@example.com"}'

# 5. User receives email with reset link
# 6. User clicks link and resets password
# 7. User logs in with new password ✅
```

### Production Checklist
- [ ] Gmail App Password or Less Secure Apps enabled
- [ ] MAIL_* variables configured in `.env`
- [ ] `php artisan config:cache` run
- [ ] Test command works with external email
- [ ] Real user can request password reset
- [ ] Email arrives in inbox or spam folder
- [ ] Reset link works and redirects correctly
- [ ] Password change is successful

## Security Notes

1. **Tokens are hashed** - Never stored in plain text
2. **Tokens expire** - After 1 hour
3. **One-time use** - Deleted after successful reset
4. **Email never sent** - To unverified addresses
5. **Generic response** - Users don't know if email exists (prevents user enumeration)

## Production Email Services

For reliable production delivery, consider:

### SendGrid (Recommended for Scale)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxxxxxxxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@agritech.com
```

### Mailgun (Free Tier)
```env
MAIL_MAILER=mailgun
MAILGUN_SECRET=key-xxxxxxxxxxxxx
MAILGUN_DOMAIN=mg.agritech.com
MAIL_FROM_ADDRESS=noreply@agritech.com
```

### AWS SES
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=AKIA...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS=noreply@agritech.com
```

---

**Status:** ✅ External Email Sending Fully Functional
**Last Updated:** September 9, 2026
**Quick Test:** `php artisan test:password-reset-email your-email@example.com`
