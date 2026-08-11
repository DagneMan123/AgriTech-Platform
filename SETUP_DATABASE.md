# Database Setup Guide - Fix Missing personal_access_tokens Table

## Problem
The `personal_access_tokens` table doesn't exist in your PostgreSQL database, causing login to fail with:
```
SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "personal_access_tokens" does not exist
```

## Solution Options

### Option 1: Run PHP Migration Script (Recommended)
Open a terminal in the `backend` directory and run:
```bash
php migrate.php
```

### Option 2: Use Artisan Command
```bash
cd backend
php artisan migrate --force
```

### Option 3: Manual SQL (If Other Options Fail)
If PHP commands aren't working, connect to your PostgreSQL database and run this SQL:

```sql
-- Create personal_access_tokens table
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(80) NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Create indexes
CREATE INDEX IF NOT EXISTS personal_access_tokens_tokenable_type_tokenable_id_index 
ON personal_access_tokens (tokenable_type, tokenable_id);
```

**To execute this SQL:**

**Using pgAdmin:**
1. Connect to your `agritech` database
2. Open Query Editor
3. Copy and paste the SQL above
4. Click Execute

**Using psql CLI:**
```bash
psql -U postgres -h 127.0.0.1 -d agritech -f migration.sql
```

## Verify the Fix

After creating the table, test the login again:

1. Make a POST request to: `http://localhost:8000/api/auth/login`
2. Body (JSON):
```json
{
  "email": "test@example.com",
  "password": "password"
}
```

Expected Response (200):
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com",
    "phone": "1234567890",
    "role": "farmer",
    "location": "Ethiopia",
    "region": "Oromia",
    "is_active": true
  },
  "token": "your-api-token-here"
}
```

## Troubleshooting

### Still getting the error?
1. **Verify the table exists**: Connect to PostgreSQL and run:
   ```sql
   SELECT * FROM information_schema.tables WHERE table_name = 'personal_access_tokens';
   ```

2. **Check all migrations ran**: Run in backend directory:
   ```bash
   php artisan migrate:status
   ```

3. **Check database connection**: Verify `.env` has correct credentials:
   - DB_HOST=127.0.0.1
   - DB_PORT=5432
   - DB_DATABASE=agritech
   - DB_USERNAME=postgres
   - DB_PASSWORD=MYlove8

4. **Refresh migrations**: If migrations are corrupted, run:
   ```bash
   php artisan migrate:refresh --force --seed
   ```
   ⚠️ **WARNING**: This will reset all data!

## Files Affected
- `backend/database/migrations/2026_07_24_000062_create_personal_access_tokens_table.php` - Migration definition
- `backend/app/Models/PersonalAccessToken.php` - Model
- `backend/app/Traits/HasApiTokens.php` - Token generation trait
