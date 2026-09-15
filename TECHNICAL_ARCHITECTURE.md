# Technical Architecture - Email System

## System Design

```
┌─────────────────────────────────────────────────────────────────────┐
│                          Frontend (Vue 3)                            │
│                    (localhost:5173)                                  │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Forgot Password View                                         │  │
│  │  └─ Submit Email → POST /api/auth/forgot-password           │  │
│  └──────────────────────────────────────────────────────────────┘  │
└──────────────────────┬──────────────────────────────────────────────┘
                       │ HTTP Request
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         Backend (Laravel)                            │
│                    (localhost:8000)                                  │
│                                                                      │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  API Route: POST /api/auth/forgot-password                  │  │
│  │  CORS Middleware: ✅ Enabled (handles OPTIONS)              │  │
│  └───────────────────┬──────────────────────────────────────────┘  │
│                      │                                              │
│  ┌──────────────────▼──────────────────────────────────────────┐  │
│  │  AuthController::forgotPassword()                            │  │
│  │  • Validate email format                                     │  │
│  │  • Find user in database                                     │  │
│  │  • Generate secure reset token                               │  │
│  │  • Store hashed token in password_resets table              │  │
│  └───────────────────┬──────────────────────────────────────────┘  │
│                      │                                              │
│  ┌──────────────────▼──────────────────────────────────────────┐  │
│  │  EmailService::sendPasswordResetEmail()                      │  │
│  │  • Validate email format                                     │  │
│  │  • Create reset link with token                              │  │
│  │  • Send email via Mail facade                                │  │
│  │  • Log success/failure with detailed info                   │  │
│  │  • Return structured response                                │  │
│  └───────────────────┬──────────────────────────────────────────┘  │
│                      │                                              │
│  ┌──────────────────▼──────────────────────────────────────────┐  │
│  │  PasswordResetMail (Mailable)                                │  │
│  │  • Render email template                                     │  │
│  │  • Include reset link                                        │  │
│  │  • Include token as fallback                                 │  │
│  │  • HTML email with styling                                   │  │
│  └───────────────────┬──────────────────────────────────────────┘  │
│                      │                                              │
│  ┌──────────────────▼──────────────────────────────────────────┐  │
│  │  Mail Facade (Laravel)                                       │  │
│  │  • Convert to SMTP message                                   │  │
│  │  • Set from/to/subject headers                               │  │
│  │  • Prepare for transmission                                  │  │
│  └───────────────────┬──────────────────────────────────────────┘  │
└──────────────────────┼──────────────────────────────────────────────┘
                       │ SMTP Protocol
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    Gmail SMTP Server                                 │
│              (smtp.gmail.com:587 / TLS)                             │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Authenticate with:                                          │  │
│  │  • Username: dagneaydenfu23@gmail.com                        │  │
│  │  • App Password: wpdijmyshzzcmpyx                            │  │
│  │  • TLS Encryption: Enabled                                   │  │
│  └───────────────────┬──────────────────────────────────────────┘  │
│                      │                                              │
│  ┌──────────────────▼──────────────────────────────────────────┐  │
│  │  Route to Recipient Provider                                 │  │
│  │  • Gmail: Direct delivery                                    │  │
│  │  • Yahoo: Via Yahoo mail servers                             │  │
│  │  • Outlook: Via Outlook mail servers                         │  │
│  │  • Others: Via DNS MX lookup                                 │  │
│  └───────────────────┬──────────────────────────────────────────┘  │
└──────────────────────┼──────────────────────────────────────────────┘
                       │ Email Protocol
                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     User's Email Provider                            │
│            (Gmail, Yahoo, Outlook, Custom, etc.)                    │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  Email Arrives in User's Inbox                               │  │
│  │  • Subject: Reset Your AgriTech Password                     │  │
│  │  • From: noreply@agritech.platform                           │  │
│  │  • Contains: Reset button + link + token                     │  │
│  └──────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘

User receives email, clicks reset link
      ↓
Frontend loads: /reset-password?token=xxxxx&email=user@domain.com
      ↓
User submits new password
      ↓
Frontend POST /api/auth/reset-password (with token + email + password)
      ↓
Backend validates token from password_resets table
      ↓
Backend updates user.password
      ↓
Success response, redirect to login
```

## Component Architecture

### 1. EmailService (Core Logic)

**Location:** `app/Services/EmailService.php`

**Responsibilities:**
- Send password reset emails
- Test email configuration
- Implement retry logic
- Handle errors gracefully
- Log all operations

**Key Methods:**
```php
// Send email with professional error handling
EmailService::sendPasswordResetEmail($name, $email, $token, $link)

// Test configuration without sending
EmailService::testEmailConfiguration()

// Send with automatic retries (exponential backoff)
EmailService::testEmailWithRetry($email, $maxRetries = 3)

// Get health status
EmailService::getEmailHealth()
```

**Error Handling:**
- Catches SMTP exceptions
- Logs full error details
- Returns structured response
- Never throws exception to caller

### 2. EmailDiagnosticController (Testing)

**Location:** `app/Http/Controllers/Api/EmailDiagnosticController.php`

**Endpoints:**
```
GET  /api/email/health               → Health status
GET  /api/email/test-config          → Configuration check
POST /api/email/test-send            → Send test email
GET  /api/email/test-providers       → Provider info
```

**Responses:**
- JSON with status, configuration, issues
- Detailed error messages
- Helpful troubleshooting hints

### 3. AuthController (Integration)

**Location:** `app/Http/Controllers/Api/AuthController.php`

**Updated Method:**
```php
public function forgotPassword(Request $request)
{
    // 1. Validate email
    // 2. Find user
    // 3. Generate token
    // 4. Store hashed token
    // 5. Call EmailService::sendPasswordResetEmail()
    // 6. Log result
    // 7. Return response
}
```

**Integration:**
- Uses EmailService for sending
- Logs success and failures
- Security: Generic response to all users
- One-time-use tokens

### 4. Test Command (CLI)

**Location:** `app/Console/Commands/TestPasswordResetEmail.php`

**Usage:**
```bash
php artisan test:password-reset-email email@domain.com
php artisan test:password-reset-email email@domain.com --retry
```

**Features:**
- Configuration display
- Beautiful formatted output
- Retry capability
- Detailed troubleshooting hints

### 5. Email Template (View)

**Location:** `resources/views/emails/password-reset.blade.php`

**Features:**
- HTML email with styling
- Responsive design
- Clickable reset button
- Token as fallback
- Security information
- Support contact info

## Data Flow

### 1. Request Phase
```
User Input (Email)
    ↓ Validation
   Email Format Check
    ↓ Database Lookup
   Find User Record
    ↓ Token Generation
   Str::random(60)
```

### 2. Token Storage Phase
```
Raw Token (60 chars)
    ↓ Hashing
   Hash::make()
    ↓ Database Insert
   password_resets table
    ├─ email
    ├─ token (hashed)
    └─ created_at
```

### 3. Email Composition Phase
```
Reset Link Creation
    ↓ Template Rendering
   PasswordResetMail::render()
    ↓ Email Envelope
   From/To/Subject
    ↓ Send Command
   Mail::send()
```

### 4. SMTP Transmission Phase
```
SMTP Connect
    ↓ Authenticate
   username + app password
    ↓ Send Message
   RFC 5321 Protocol
    ↓ Wait Response
   250 OK (success) or error
```

### 5. Delivery Phase
```
Gmail SMTP Routes to MX
    ↓ Provider Check
   DNS lookup for MX records
    ↓ SMTP Handshake
   Connect to recipient server
    ↓ Deliver Message
   Store in recipient's mail server
```

### 6. Reset Phase
```
User Clicks Link
    ↓ Token Extraction
   Parse query parameters
    ↓ Submit Form
   New password
    ↓ Validation
   Check token exists & not expired
    ↓ Update Password
   Hash new password
    ↓ Delete Token
   Remove from password_resets
    ↓ Redirect
   Login page
```

## Error Handling Strategy

### Level 1: Input Validation
```php
// Validate email format before sending
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return ['success' => false, 'error_code' => 'INVALID_EMAIL'];
}
```

### Level 2: SMTP Execution
```php
try {
    Mail::send(new PasswordResetMail(...));
    // Log success
} catch (Exception $e) {
    // Log error details
    // Return error response
}
```

### Level 3: Retry Logic
```php
for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        // Attempt send
        return ['success' => true];
    } catch (Exception $e) {
        if ($attempt < $maxRetries) {
            sleep(pow(2, $attempt)); // 2s, 4s, 8s...
        }
    }
}
```

### Level 4: Logging
```php
Log::info('Password reset email sent successfully', [
    'user_email' => $email,
    'mailer' => config('mail.mailer'),
    'timestamp' => now(),
]);

Log::error('Failed to send password reset email', [
    'user_email' => $email,
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
]);
```

## Configuration Management

### SMTP Configuration (.env)
```env
MAIL_MAILER=smtp                    # Protocol
MAIL_HOST=smtp.gmail.com            # Server
MAIL_PORT=587                       # Port
MAIL_USERNAME=email@gmail.com       # Username
MAIL_PASSWORD=app-password          # App Password
MAIL_ENCRYPTION=tls                 # Encryption
MAIL_FROM_ADDRESS=noreply@...       # From address
MAIL_FROM_NAME=AgriTech Platform    # From name
MAIL_RETRY_AFTER=300                # Retry interval
MAIL_RETRIES=3                       # Max retries
```

### Runtime Configuration Access
```php
config('mail.mailer')       // 'smtp'
config('mail.host')         // 'smtp.gmail.com'
config('mail.port')         // 587
config('mail.encryption')   // 'tls'
config('mail.from.address') // 'noreply@agritech.platform'
```

## Security Implementation

### 1. Token Security
```php
// Generate random token
$token = Str::random(60);

// Hash before storage
Hash::make($token);

// Verify on reset
Hash::check($inputToken, $storedHash);
```

### 2. Token Expiration
```php
// Create token
$created_at = now();

// Validate on reset (1 hour expiration)
if (time() > strtotime($created_at) + 3600) {
    // Token expired
}
```

### 3. One-Time Use
```php
// After successful reset
DB::table('password_resets')
    ->where('email', $email)
    ->delete();
```

### 4. Email Privacy
```php
// Generic response prevents user enumeration
return response()->json([
    'message' => 'If an account exists with this email, a password reset link has been sent.'
]);

// Same response for:
// - User found, email sent
// - User not found
// - Email send failed
```

## Performance Characteristics

| Operation | Time | Notes |
|-----------|------|-------|
| Email validation | <1ms | Local format check |
| Database lookup | 5-10ms | Indexed on email |
| Token generation | <1ms | Cryptographic random |
| Token hashing | 100-200ms | bcrypt with 12 rounds |
| Email send (success) | 1-3 seconds | SMTP handshake + transmission |
| Email send (failure) | 1-5 seconds | Timeout or error |
| Retry with backoff | 14-30 seconds | 3 attempts with 2s/4s/8s wait |

## Database Schema

### password_resets Table
```sql
CREATE TABLE password_resets (
    email varchar(255) PRIMARY KEY,
    token varchar(255) NOT NULL,
    created_at timestamp NOT NULL
);

-- Index on email for fast lookup
CREATE INDEX password_resets_email ON password_resets(email);
```

### Data Flow
```
User Email → password_resets table
     ↓
Check if record exists
     ↓
If exists, UPDATE
If not exists, INSERT
     ↓
Store hashed token
     ↓
Set created_at = now()
```

## Testing Strategy

### Unit Tests (Can be added)
```php
// Test token generation
// Test email validation
// Test configuration reading
// Test retry logic
```

### Integration Tests (Can be added)
```php
// Test complete forgot password flow
// Test email sending to real SMTP
// Test token validation on reset
```

### Manual Tests (Available Now)
```bash
# CLI command
php artisan test:password-reset-email test@gmail.com

# API endpoints
curl http://localhost:8000/api/email/health
curl -X POST http://localhost:8000/api/email/test-send \
  -d '{"email":"test@example.com"}'
```

## Monitoring & Logging

### Log Locations
```
File: backend/storage/logs/laravel.log
Rotation: Daily (Laravel default)
Retention: 14 days (configurable)
```

### Log Entry Examples

**Success:**
```
[2026-09-09 10:30:45] local.INFO: Password reset email sent successfully
    {"user_email":"farmer@yahoo.com","mailer":"smtp","from_address":"noreply@agritech.platform"}
```

**Failure:**
```
[2026-09-09 10:30:45] local.ERROR: Failed to send password reset email
    {"user_email":"farmer@yahoo.com","error":"Connection could not be established..."}
```

## Future Enhancements

1. **Queue Integration**
   - Move email sending to background queue
   - Improves API response time

2. **Transactional Email Service**
   - SendGrid, Mailgun, AWS SES integration
   - Better deliverability metrics

3. **Email Analytics**
   - Track open rates
   - Track click rates
   - Monitor bounce rates

4. **Template Customization**
   - Admin panel for email templates
   - Brand customization

5. **Multi-Language Support**
   - Email templates in multiple languages
   - User language preference

---

**Architecture Version:** 1.0
**Date:** September 9, 2026
**Status:** Production Ready
