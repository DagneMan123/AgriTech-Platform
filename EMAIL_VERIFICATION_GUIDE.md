# Email Verification & Testing Guide

## Current Implementation Status ✅

Your forgot password system is configured to:
1. ✅ Accept email from user
2. ✅ Validate email exists in database
3. ✅ Generate secure reset token
4. ✅ **Send email immediately** using `Mail::send()`
5. ✅ Include clickable reset link in email
6. ✅ Email template with styling and security info

## Email Configuration

### Gmail SMTP Setup (Already Configured)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=dagneaydenfu23@gmail.com
MAIL_PASSWORD=wpdijmyshzzcmpyx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="dagneaydenfu23@gmail.com"
MAIL_FROM_NAME="AgriTech"
```

## Potential Issues & Solutions

### Issue 1: Gmail Security Blocks

Gmail blocks "less secure apps" by default. **Fix:**

**Option A: Use App Password (Recommended)**
1. Go to https://myaccount.google.com/apppasswords
2. Select "Mail" and "Windows Computer"
3. Google generates 16-character password
4. Update `.env`:
```env
MAIL_PASSWORD=your16characterapppassword
```
5. Restart backend

**Option B: Enable Less Secure Apps**
1. Go to https://myaccount.google.com/lesssecureapps
2. Toggle "Allow less secure app access" ON
3. Restart backend

### Issue 2: Email Not In Inbox

**Check Spam Folder**
- Gmail may filter as spam initially
- Mark as "Not spam" to improve delivery

**Check Laravel Logs**
```bash
tail -f backend/storage/logs/laravel.log
```

Look for:
```
[Mail sent successfully]
[PasswordResetMail sent to user@example.com]
```

### Issue 3: Testing Without Real Email

Use **Laravel Logs** to verify email was processed:

**Check what was sent:**
```bash
# View recent logs
tail -20 backend/storage/logs/laravel.log

# Search for mail errors
grep -i "mail\|error" backend/storage/logs/laravel.log | tail -10
```

## How to Verify Email is Sending

### Step 1: Check if Backend is Running
```bash
curl http://localhost:8000/api/health
# Response: {"status":"ok","time":"2026-09-09T..."}
```

### Step 2: Test Forgot Password Endpoint

**Option A: Using Frontend**
1. Go to http://localhost:5173/forgot-password
2. Enter any registered user's email
3. Submit form
4. Check Laravel logs: `tail -f backend/storage/logs/laravel.log`

**Option B: Using cURL**
```bash
curl -X POST http://localhost:8000/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"dagneaydenfu23@gmail.com"}'

# Expected Response:
# {"message":"If an account exists with this email, a password reset link has been sent."}
```

### Step 3: Check Laravel Logs for Email Confirmation

After making the request, check logs:

```bash
# Navigate to backend
cd backend

# View logs (last 50 lines)
tail -50 storage/logs/laravel.log
```

**Look for these log entries:**

```
[2026-09-09 10:30:45] local.INFO: Password reset request processed [user_id] => 1, [email] => user@example.com
```

If no mail logs appear, add this to `app.php`:

```php
'log' => [
    'driver' => 'stack',
    'channels' => ['single'],
],
```

### Step 4: Check Gmail Inbox

1. Login to dagneaydenfu23@gmail.com
2. Check Inbox
3. Look for "Reset Your AgriTech Password" email
4. Click the "Reset Password" button in the email
5. Verify it takes you to: `http://localhost:5173/reset-password?token=xxxxx&email=user@example.com`

## Database Verification

Check if password reset token is stored:

```bash
cd backend

php artisan tinker
>>> use Illuminate\Support\Facades\DB;
>>> DB::table('password_resets')->where('email', 'dagneaydenfu23@gmail.com')->first();

# Output should show:
# {
#   "email": "dagneaydenfu23@gmail.com",
#   "token": "$2y$12$...",
#   "created_at": "2026-09-09 10:30:45"
# }
```

## Testing Reset Password

After clicking email link:

1. Frontend should show reset password form
2. Enter new password
3. Confirm password
4. Submit form
5. Backend validates token and updates password
6. Redirect to login page
7. Login with new password

## Email Content Verification

The email includes:
- ✅ User's name greeting
- ✅ "Reset Password" button
- ✅ Full reset link as fallback text
- ✅ Reset token displayed
- ✅ 60-minute expiration warning
- ✅ Security tips
- ✅ Support contact info

## Troubleshooting Checklist

- [ ] Backend is running (`php artisan serve`)
- [ ] Frontend is running on port 5173
- [ ] Gmail account has correct password in `.env`
- [ ] `MAIL_MAILER=smtp` is set
- [ ] `MAIL_HOST=smtp.gmail.com` is correct
- [ ] `MAIL_PORT=587` is correct
- [ ] Gmail App Password is used OR Less Secure Apps is enabled
- [ ] No firewall blocking port 587
- [ ] Database `password_resets` table exists
- [ ] User email exists in `users` table

## If Email Still Not Sending

### Debug Mode: Check Email Config

```bash
cd backend

php artisan tinker
>>> config('mail.mailer')
"smtp"

>>> config('mail.from')
["address" => "dagneaydenfu23@gmail.com", "name" => "AgriTech"]

>>> config('mail.host')
"smtp.gmail.com"

>>> config('mail.port')
587

>>> config('mail.encryption')
"tls"
```

### Test SMTP Connection

```bash
# Test Gmail SMTP connectivity
telnet smtp.gmail.com 587

# On Windows:
telnet smtp.gmail.com 587
# Type "QUIT" to exit
```

### Manual Email Test

```bash
cd backend

php artisan tinker
>>> use App\Mail\PasswordResetMail;
>>> use Illuminate\Support\Facades\Mail;

>>> Mail::send(new PasswordResetMail(
...   'Test User',
...   'dagneaydenfu23@gmail.com',
...   'test123token',
...   'http://localhost:5173/reset-password?token=test123token&email=test@example.com'
... ));

# If successful, you should receive the email immediately
```

## Production Considerations

### Use Environment Variables
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=${GMAIL_USERNAME}
MAIL_PASSWORD=${GMAIL_APP_PASSWORD}
MAIL_FROM_ADDRESS=${GMAIL_USERNAME}
```

### Use Transactional Email Service
For production, use services like:
- **SendGrid**: Reliable, good deliverability
- **Mailgun**: Developer-friendly, good API
- **AWS SES**: Scalable, cost-effective
- **Postmark**: Great for transactional emails

Example with SendGrid:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxxxxxxxxx
```

## Summary

Your password reset email system is **correctly implemented** and should be sending emails immediately when users submit the forgot password form. If you're not receiving emails:

1. **Check Gmail spam folder** (most common)
2. **Verify Gmail App Password** or enable Less Secure Apps
3. **Check Laravel logs** for errors
4. **Test SMTP connection** manually
5. **Verify database** has reset token stored

---

**Status:** ✅ Email System Ready
**Last Updated:** September 9, 2026
**Testing:** Ready for verification
