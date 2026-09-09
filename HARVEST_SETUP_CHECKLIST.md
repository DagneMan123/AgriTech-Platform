# Harvest Management - Setup Checklist

## Pre-Deployment Verification

### Backend Setup
- [x] Harvest Model created/updated (`app/Models/Harvest.php`)
  - ✓ Correct fillable fields
  - ✓ Proper relationships
  - ✓ Date casting configured

- [x] HarvestController implemented (`app/Http/Controllers/Api/Farmer/HarvestController.php`)
  - ✓ index() - List all harvests
  - ✓ store() - Create harvest
  - ✓ show() - Get single harvest
  - ✓ update() - Update harvest
  - ✓ destroy() - Delete harvest
  - ✓ statistics() - Get harvest statistics
  - ✓ Authorization checks added

- [x] HarvestResource created (`app/Http/Resources/Farmer/HarvestResource.php`)
  - ✓ Proper data transformation
  - ✓ Includes crop and farm information
  - ✓ Backward compatibility for column names

- [x] Database Migrations
  - [x] Original migration: `2026_07_24_000053_create_harvests_table.php`
  - [x] Update migration: `2026_09_08_update_harvests_table.php`
    - ✓ Renames quantity_harvested to quantity
    - ✓ Adds notes column if missing

- [x] API Routes configured (`routes/api.php`)
  - ✓ Route::apiResource('/harvests', HarvestController::class)
  - ✓ Harvest dashboard route included

### Frontend Setup
- [x] HarvestsView.vue component (`src/views/farmer/HarvestsView.vue`)
  - ✓ Complete form for recording harvests
  - ✓ Table display with filtering
  - ✓ Edit and delete functionality
  - ✓ Statistics display
  - ✓ Using apiClient for API calls
  - ✓ Error handling implemented

- [x] FarmerSidebar.vue updated (`src/components/Sidebar/FarmerSidebar.vue`)
  - ✓ Navigation link to harvests
  - ✓ Proper routing configured
  - ✓ Section auto-expansion working

- [x] API Client configured (`src/api/config.ts`)
  - ✓ Base URL set correctly
  - ✓ Authorization headers configured
  - ✓ Error handling for 401 responses

- [x] Frontend .env configured (`frontend/.env`)
  - ✓ VITE_API_URL set to http://localhost:8000/api

## Required Database Structure

### harvests Table
```
Columns Required:
- id (PRIMARY KEY)
- crop_id (FOREIGN KEY -> crops.id)
- harvest_date (DATE)
- quantity (DECIMAL 10,2)
- unit (VARCHAR)
- quality_grade (VARCHAR, nullable)
- notes (TEXT, nullable)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)

Indexes:
- crop_id
- harvest_date
```

## Deployment Steps

### Step 1: Database Migration
```bash
cd backend
php artisan migrate
```
This will:
- Ensure harvests table exists with proper schema
- Rename quantity_harvested to quantity if needed
- Add notes column if missing

### Step 2: Backend Verification
```bash
# Check routes are registered
php artisan route:list | grep harvests

# Verify models can be instantiated
php artisan tinker
> App\Models\Harvest::count()
```

### Step 3: Frontend Build (if needed)
```bash
cd frontend
npm run build  # or yarn build
```

### Step 4: Start Services
```bash
# Terminal 1 - Backend
cd backend
php artisan serve

# Terminal 2 - Frontend (if not built)
cd frontend
npm run dev  # or yarn dev
```

### Step 5: Test Endpoints
```bash
# Test GET harvests (need valid auth token)
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/farmer/harvests

# Test POST harvest
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "crop_id": 1,
       "harvest_date": "2026-09-08",
       "quantity": 100,
       "unit": "kg",
       "quality_grade": "excellent",
       "notes": "Test harvest"
     }' \
     http://localhost:8000/api/farmer/harvests
```

## Frontend Testing Checklist

### Basic Functionality
- [ ] Can navigate to Harvests page from sidebar
- [ ] Page loads without errors
- [ ] Statistics cards display correctly
- [ ] Harvests table shows existing records (if any)

### Create Harvest
- [ ] Can click "Record Harvest" button
- [ ] Modal opens with form
- [ ] Crop dropdown shows available crops
- [ ] Date picker works
- [ ] Can enter quantity and select unit
- [ ] Can select quality grade
- [ ] Can add notes
- [ ] Form submits successfully
- [ ] New harvest appears in table

### Read Harvest
- [ ] Harvests display in table with all columns
- [ ] Crop information displays correctly
- [ ] Date formatting is correct
- [ ] Quality badges show correct styling
- [ ] Notes preview works on hover

### Edit Harvest
- [ ] Can click edit button
- [ ] Form populates with existing data
- [ ] Can modify fields
- [ ] Can save changes
- [ ] Table updates after save

### Delete Harvest
- [ ] Can click delete button
- [ ] Confirmation dialog appears
- [ ] Can confirm deletion
- [ ] Harvest is removed from table

### Filters & Search
- [ ] Search by crop name filters results
- [ ] Quality filter works
- [ ] Year filter works
- [ ] Multiple filters combine correctly
- [ ] Empty results message shows when no matches

### Error Handling
- [ ] Required fields validation works
- [ ] Error messages display in form
- [ ] Server errors show in alert
- [ ] Network errors are handled gracefully

### Responsive Design
- [ ] Desktop layout is correct
- [ ] Mobile layout is functional
- [ ] Table scrolls horizontally on small screens
- [ ] Modal displays properly on all sizes

## Data Validation Rules

### Create/Update Validation
- `crop_id`: Required, must exist in crops table
- `harvest_date`: Required, valid date
- `quantity`: Required, numeric, minimum 0.01
- `unit`: Required, one of: kg, tonnes, bags, liters
- `quality_grade`: Optional, one of: excellent, good, fair, poor
- `notes`: Optional, string

## Authorization Rules

- Farmers can only view harvests for crops they own
- Farmers can only edit their own harvests
- Farmers can only delete their own harvests
- Non-farmers cannot access harvest endpoints

## Performance Considerations

- Harvests are paginated (default 20 per page)
- Related crop and farm data is eager-loaded
- Results are ordered by harvest_date DESC
- Indexes on crop_id and harvest_date for fast queries

## Troubleshooting

### Issue: 404 Not Found on harvest endpoints
- [ ] Check routes are registered: `php artisan route:list`
- [ ] Verify HarvestController exists
- [ ] Check file permissions on controller file

### Issue: 401 Unauthorized
- [ ] Verify user is authenticated
- [ ] Check auth token is being sent in header
- [ ] Verify token hasn't expired
- [ ] Check GUARD is set to 'api' in config/auth.php

### Issue: 403 Forbidden
- [ ] Verify harvest belongs to authenticated farmer
- [ ] Check farmer_id in farms table matches user id

### Issue: Validation errors
- [ ] Check all required fields are provided
- [ ] Verify field values match allowed options
- [ ] Check data types match (numeric for quantity, date for harvest_date)

### Issue: Frontend API errors
- [ ] Verify VITE_API_URL in .env is correct
- [ ] Check backend is running on correct port
- [ ] Verify CORS is configured correctly
- [ ] Check network tab in browser dev tools

## Rollback Procedure

If issues occur:

1. **Database Rollback**:
   ```bash
   php artisan migrate:rollback
   php artisan migrate  # Then re-run migrations
   ```

2. **Code Rollback**:
   ```bash
   git checkout HEAD -- app/Models/Harvest.php
   git checkout HEAD -- app/Http/Controllers/Api/Farmer/HarvestController.php
   git checkout HEAD -- frontend/src/views/farmer/HarvestsView.vue
   ```

## Success Criteria

The Harvest Management feature is successfully deployed when:

1. ✓ Farmers can navigate to Harvests page
2. ✓ Farmers can record new harvests for their crops
3. ✓ Harvests appear in the harvest table immediately
4. ✓ Farmers can edit existing harvests
5. ✓ Farmers can delete harvests with confirmation
6. ✓ Statistics display correct calculations
7. ✓ Search and filtering work correctly
8. ✓ Data persists after page refresh
9. ✓ Authorization prevents accessing other farmers' data
10. ✓ No console errors in browser developer tools

