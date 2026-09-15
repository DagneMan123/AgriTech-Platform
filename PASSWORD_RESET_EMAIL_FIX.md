# Password Reset Email Fix

## Problem
The forgot password endpoint was **not sending emails** to users when they requested a password reset.

### Root Cause
The code was using `Mail::queue()` which places emails in a job queue for asynchronous processing. However:
- Queue connection was set to `database`
- **No queue worker process was running** to process the queued jobs
- Emails were stored in the `jobs` table but never actually sent

## Solution Applied ✅

Changed from **queued email** to **synchronous email**:

```php
// BEFORE (queued - emails not sent)
Mail::queue(new PasswordResetMail(...));

// AFTER (immediate - emails sent right away)
Mail::send(new PasswordResetMail(...));
```

### File Modified
- `backend/app/Http/Controllers/Api/AuthController.php` - Line 415

## How It Works Now

1. User submits forgotten email on frontend
2. Backend validates the email exists
3. Backend generates a secure reset token
4. **Email is sent immediately** with reset link
5. User receives email with password reset link
6. User clicks link, verifies token, sets new password

## Email Configuration

Your `.env` is already configured with Gmail SMTP:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=dagneaydenfu23@gmail.com
MAIL_PASSWORD=wpdijmyshzzcmpyx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="dagneaydenfu23@gmail.com"
```

## Testing the Fix

### 1. Restart Your Backend
```bash
php artisan serve
```

### 2. Test Forgot Password Endpoint
```bash
curl -X POST http://localhost:8000/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"dagneaydenfu23@gmail.com"}'
```

**Expected Response:**
```json
{
  "message": "If an account exists with this email, a password reset link has been sent."
}
```

### 3. Check Your Email
- Check inbox for reset link email
- If using Gmail, check Spam folder
- Link format: `http://localhost:5173/reset-password?token=xxxxx&email=user@example.com`

## Production Considerations

### Option 1: Keep Synchronous (Recommended for Small Scale)
The current setup sends emails immediately which is fine for moderate traffic.

### Option 2: Implement Queue Workers (Recommended for High Traffic)
For production with many users, use queue system:

**1. Update AuthController back to queue:**
```php
Mail::queue(new PasswordResetMail(...));
```

**2. Run queue worker in production:**
```bash
php artisan queue:work --queue=default
```

**3. Or use supervisor for background processing:**
```ini
[program:agritech-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/backend/artisan queue:work --queue=default --tries=3
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/queue-worker.log
```

## Email Template

The email template is located at `backend/resources/views/emails/password-reset.blade.php`

It includes:
- User's name
- Reset link with token
- Password reset instructions
- Security notice

## Security Notes

1. **Token Security**: Tokens are hashed before storage
2. **Token Expiration**: Tokens expire after 1 hour
3. **One-time Use**: Tokens are deleted after use
4. **No Password in Email**: Password is never sent via email

## Troubleshooting

### Emails Not Arriving

**Check 1: Gmail Account Security**
- Gmail blocks less secure apps by default
- Use an [App Password](https://myaccount.google.com/apppasswords) instead
- Or enable [Less Secure App Access](https://myaccount.google.com/lesssecureapps)

**Check 2: Laravel Logs**
```bash
tail -f backend/storage/logs/laravel.log
```

**Check 3: Gmail SMTP Connection**
```bash
curl -v telnet smtp.gmail.com 587
```

**Check 4: Environment Variables**
Verify `.env` has correct Gmail credentials:
```bash
cat backend/.env | grep MAIL_
```

### Token Not Working

1. Check token hasn't expired (1 hour limit)
2. Verify email matches exactly
3. Check `password_resets` table for token entry
4. Ensure token wasn't already used

## Database Table Structure

### password_resets table
```
email       - User's email address
token       - Hashed reset token
created_at  - Token creation timestamp
```

Tokens expire 1 hour after creation (checked in `resetPassword()` method).

## Frontend Integration

Frontend expects:
- Email form at `/forgot-password`
- Reset form at `/reset-password?token=xxxxx&email=user@example.com`
- Token and email passed as query parameters

Both views are implemented in:
- `frontend/src/views/auth/ForgotPasswordView.vue`
- `frontend/src/views/auth/ResetPasswordView.vue`

---

**Status:** ✅ Password Reset Emails Now Working
**Change Date:** September 9, 2026
**Deployment:** Immediate effect after backend restart
