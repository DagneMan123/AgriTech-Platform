# Crop Activities - Complete Setup Guide

## What Has Been Done

### ✅ Backend Setup
1. **CropActivityController** - Fully implemented with all CRUD operations
2. **CropActivity Model** - With proper relationships
3. **Database Migration** - Configured to create table with correct foreign keys
4. **API Routes** - Registered at `/api/farmer/crop-activities`
5. **Error Handling** - Gracefully handles missing tables

### ✅ Frontend Setup
1. **CropActivitiesView.vue** - Complete form with filters and timeline
2. **API Integration** - Proper request/response handling
3. **Form Validation** - Client-side validation with error display
4. **Error Handling** - User-friendly error messages

## Current Status

The system is **ready to use**, but the database table may not exist yet.

## How to Fix (Choose ONE option)

### Option 1: Quick Setup (Run ONE command)

**Copy this entire SQL block and paste into your PostgreSQL client (pgAdmin/SQL Shell):**

```sql
DROP TABLE IF EXISTS crop_activities CASCADE;

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

CREATE INDEX crop_activities_crop_id_activity_date_idx ON crop_activities(crop_id, activity_date);
CREATE INDEX crop_activities_farm_id_activity_date_idx ON crop_activities(farm_id, activity_date);
CREATE INDEX crop_activities_farmer_id_activity_date_idx ON crop_activities(farmer_id, activity_date);
CREATE INDEX crop_activities_activity_type_idx ON crop_activities(activity_type);
```

### Option 2: Using artisan (if migrations are set up)

```bash
cd backend
php artisan migrate --force
```

### Option 3: The automated fix script

There's a `fix_now.php` in the backend folder that can auto-fix the database.

## After Setup

1. **Restart your Laravel server**
   ```bash
   cd backend
   php artisan serve --port=8000
   ```

2. **Hard refresh the browser** (Ctrl+F5)

3. **Log in as a farmer**

4. **Go to Crop Activities** - Should now work without errors

5. **Test by creating an activity**:
   - Click "Add Activity"
   - Select crop and farm
   - Fill in details
   - Click "Save Activity"
   - Activity should appear in timeline

## Database Schema

```
crop_activities table:
├── id (BIGSERIAL PRIMARY KEY)
├── crop_id (BIGINT, references crops.id)
├── farm_id (BIGINT, references farms.id)
├── farmer_id (BIGINT, references users.id) ← KEY POINT
├── activity_type (VARCHAR - enum)
├── activity_date (DATE)
├── activity_time (TIME, optional)
├── description (TEXT, optional)
├── quantity (NUMERIC, optional)
├── unit (VARCHAR, optional)
├── cost (NUMERIC, optional)
├── weather (VARCHAR, optional)
├── notes (TEXT, optional)
├── deleted_at (TIMESTAMP, for soft deletes)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

Indexes:
- (crop_id, activity_date)
- (farm_id, activity_date)
- (farmer_id, activity_date)
- (activity_type)
```

## API Endpoints

### Get All Activities
```
GET /api/farmer/crop-activities
Auth: Bearer {token}
Response: { success: true, data: [], total: 0 }
```

### Create Activity
```
POST /api/farmer/crop-activities
Auth: Bearer {token}
Body: {
  crop_id: 1,
  farm_id: 1,
  activity_type: "planting",
  activity_date: "2026-09-08",
  description: "...",
  quantity: 50,
  unit: "kg",
  cost: 1500,
  weather: "sunny",
  notes: "..."
}
```

### Edit Activity
```
PUT /api/farmer/crop-activities/{id}
Auth: Bearer {token}
Body: { same fields as create }
```

### Delete Activity
```
DELETE /api/farmer/crop-activities/{id}
Auth: Bearer {token}
```

## Key Points

### Important:
- `farmer_id` in `crop_activities` table references `users.id` (NOT `farmers.id`)
- This is consistent with how `farms` table stores `farmer_id`
- Only authenticated farmers can access their own activities
- All endpoints require `auth:api` middleware

### Features:
- ✅ Create, read, update, delete activities
- ✅ Filter by crop, activity type, date range
- ✅ Timeline view of all activities
- ✅ Form validation
- ✅ Soft deletes (activities can be recovered)
- ✅ Proper error handling
- ✅ Performance indexes

## Troubleshooting

### Error: "404 Not Found"
- The server hasn't been restarted
- **Solution**: Restart Laravel server with `php artisan serve --port=8000`

### Error: "Foreign key violation"
- The table has the wrong foreign key constraint
- **Solution**: Run the SQL script to drop and recreate the table

### Error: "Table crop_activities doesn't exist"
- Table hasn't been created yet
- **Solution**: Run the SQL script or `php artisan migrate`

### Error: "Unauthorized"
- You're not logged in or not a farmer
- **Solution**: Log in with a farmer account

## Files Reference

### Backend
- `app/Http/Controllers/Api/Farmer/CropActivityController.php` - API controller
- `app/Models/CropActivity.php` - Database model
- `database/migrations/2026_09_02_create_crop_activities_table.php` - Main migration
- `database/migrations/2026_09_08_fix_crop_activities_foreign_key.php` - Fix migration
- `app/Http/Middleware/EnsureCropActivitiesTableExists.php` - Auto-create table

### Frontend
- `src/views/farmer/CropActivitiesView.vue` - Main page component
- `src/api/config.ts` - API client configuration

## Support

If you encounter any issues:

1. Check the browser console for error messages
2. Check Laravel logs in `backend/storage/logs/laravel.log`
3. Verify database connection in `.env`
4. Verify the table exists in your PostgreSQL database
5. Ensure farmer user has crops and farms created

## Next Steps

After setup is complete, you can:
1. Add more activity types as needed
2. Create reports/analytics based on activities
3. Add notifications for upcoming activities
4. Export activities to CSV/PDF
5. Add activity templates
