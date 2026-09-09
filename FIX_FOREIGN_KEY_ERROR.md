# Fix: Foreign Key Constraint Violation

## Problem
When trying to save a crop activity, you get this error:
```
Foreign key violation: 7 ERROR: insert or update on table "crop_activities" 
violates foreign key constraint "crop_activities_farmer_id_foreign" 
DETAIL: Key (farmer_id)=(3) is not present in table "farmers".
```

## Root Cause
The `crop_activities` table was created with a foreign key constraint on `farmer_id` that references the `farmers` table instead of the `users` table.

However, we're storing `user_id` (3) in the `farmer_id` column, which doesn't exist in the `farmers` table.

The correct relationship should be:
- `crop_activities.farmer_id` → `users.id` (NOT `farmers.id`)

This aligns with how the `farms` table works (`farms.farmer_id` references `users.id`).

## Solution

### Option 1: Quick Fix (Recommended)
Run the provided PHP script:

```bash
cd backend
php fix_crop_activities_table.php
```

This script will:
1. Drop the existing crop_activities table
2. Recreate it with the correct foreign key constraint
3. Create all necessary indexes

### Option 2: Manual Database Fix

#### PostgreSQL:
```sql
-- Drop the old table
DROP TABLE IF EXISTS crop_activities CASCADE;

-- Recreate with correct foreign key
CREATE TABLE crop_activities (
    id BIGSERIAL PRIMARY KEY,
    crop_id BIGINT NOT NULL REFERENCES crops(id) ON DELETE CASCADE,
    farm_id BIGINT NOT NULL REFERENCES farms(id) ON DELETE CASCADE,
    farmer_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    activity_type VARCHAR(50) NOT NULL,
    activity_date DATE NOT NULL,
    activity_time TIME,
    description TEXT,
    quantity NUMERIC(10,2),
    unit VARCHAR(50),
    cost NUMERIC(12,2),
    weather VARCHAR(50),
    notes TEXT,
    deleted_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Create indexes
CREATE INDEX crop_activities_crop_id_activity_date_idx ON crop_activities(crop_id, activity_date);
CREATE INDEX crop_activities_farm_id_activity_date_idx ON crop_activities(farm_id, activity_date);
CREATE INDEX crop_activities_farmer_id_activity_date_idx ON crop_activities(farmer_id, activity_date);
CREATE INDEX crop_activities_activity_type_idx ON crop_activities(activity_type);
```

#### MySQL:
```sql
-- Drop the old table
DROP TABLE IF EXISTS crop_activities;

-- Recreate with correct foreign key
CREATE TABLE crop_activities (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    crop_id BIGINT UNSIGNED NOT NULL,
    farm_id BIGINT UNSIGNED NOT NULL,
    farmer_id BIGINT UNSIGNED NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    activity_date DATE NOT NULL,
    activity_time TIME,
    description LONGTEXT,
    quantity DECIMAL(10,2),
    unit VARCHAR(50),
    cost DECIMAL(12,2),
    weather VARCHAR(50),
    notes LONGTEXT,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_crop_activity_date (crop_id, activity_date),
    INDEX idx_farm_activity_date (farm_id, activity_date),
    INDEX idx_farmer_activity_date (farmer_id, activity_date),
    INDEX idx_activity_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Option 3: Using Laravel Migrations
Run the new migration that fixes the constraint:

```bash
cd backend
php artisan migrate --force
```

This will run all pending migrations, including the new `2026_09_08_fix_crop_activities_foreign_key.php` migration.

## Files Modified

✓ `database/migrations/2026_09_02_create_crop_activities_table.php`
  - Changed: `constrained('farmers')` → `constrained('users')`

✓ `database/migrations/2026_09_08_fix_crop_activities_foreign_key.php` (NEW)
  - New migration that recreates the table with correct foreign key

✓ `app/Http/Middleware/EnsureCropActivitiesTableExists.php`
  - Changed: `constrained('farmers')` → `constrained('users')`

✓ `run_migrations.php`
  - Changed: `constrained('farmers')` → `constrained('users')`

✓ `fix_crop_activities_table.php` (NEW)
  - Quick fix script for manual table recreation

## Verification

After applying the fix, verify the constraint is correct:

### PostgreSQL:
```sql
SELECT constraint_name, table_name, column_name
FROM information_schema.constraint_column_usage
WHERE table_name = 'crop_activities' AND column_name = 'farmer_id';
```

Should show: `crop_activities_farmer_id_foreign` referencing `users`

### MySQL:
```sql
SELECT CONSTRAINT_NAME, TABLE_NAME, REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
WHERE TABLE_NAME = 'crop_activities';
```

Should show: `farmer_id` references `users.id`

## Testing

After the fix:

1. **Reload the application**
   ```bash
   # Backend
   php artisan serve --port=8000
   
   # Frontend (in another terminal)
   npm run dev
   ```

2. **Log in as a farmer**

3. **Navigate to Crop Activities**

4. **Create a new activity**
   - Select crop and farm
   - Fill in activity details
   - Click "Save Activity"

5. **Verify success**
   - Activity should appear in the timeline
   - No foreign key error should occur

## Why This Happened

The original migration was written with the assumption that `farmer_id` should reference the `farmers` table. However:

1. The system doesn't create a separate Farmer profile for every user
2. The `farms` table uses `farmer_id` to reference `users.id` directly
3. For consistency, `crop_activities.farmer_id` should also reference `users.id`

This ensures that any authenticated farmer (user with role='farmer') can store their crop activities without requiring a separate Farmer model instance.

## Related Information

- See `CROP_ACTIVITIES_INTEGRATION.md` for full integration guide
- See `FIXES_APPLIED.md` for other fixes applied to the system
