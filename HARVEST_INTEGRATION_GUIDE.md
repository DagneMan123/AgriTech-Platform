# Harvest Management - Integration Guide

## Overview
The Harvest Management page is now fully functional with complete frontend and backend integration. Farmers can record, view, edit, and delete harvest records for their crops.

## What Was Done

### 1. Backend Updates

#### Model (Harvest.php)
- Updated fillable attributes to use `quantity` instead of `quantity_harvested`
- Updated to use `unit` instead of `quantity_unit`
- Added proper relationship to Crop model
- Configured date casting for `harvest_date`

#### Controller (HarvestController.php)
- Added proper authorization checks
- All methods now return formatted data using HarvestResource
- Added input validation with specific units: kg, tonnes, bags, liters
- Implemented pagination for list endpoint
- Returns detailed crop and farm information with harvests

#### Resource (HarvestResource.php)
- Transforms harvest data to include nested crop and farm information
- Handles both old and new column names for backward compatibility
- Ensures consistent API response format

#### Migration (2026_09_08_update_harvests_table.php)
- Renames `quantity_harvested` to `quantity` if needed
- Ensures `notes` column exists
- Maintains backward compatibility

#### Routes (api.php)
- Harvest routes already configured at `/api/farmer/harvests`
- Full RESTful API support (GET, POST, PUT, DELETE)

### 2. Frontend Updates

#### HarvestsView.vue
- Imported apiClient for consistent API calls
- Updated all API calls to use axios client instead of raw fetch
- Improved error handling and response parsing
- Added support for both paginated and non-paginated API responses
- Form validation with user-friendly error messages
- Modal-based form for creating and editing harvests
- Real-time filtering by crop name, quality grade, and year
- Statistics display (total harvests, quantity, excellent grade count, average yield)

#### FarmerSidebar.vue
- Navigation item already in place under "Farm Management"
- Route `/farmer/harvests` properly mapped
- Automatic section expansion when navigating to harvest management

### 3. Database Schema

The `harvests` table includes:
- `id` - Primary key
- `crop_id` - Foreign key to crops table
- `harvest_date` - Date of harvest
- `quantity` - Amount harvested
- `unit` - Measurement unit (kg, tonnes, bags, liters)
- `quality_grade` - Grade (excellent, good, fair, poor)
- `notes` - Additional notes
- `timestamps` - Created and updated timestamps

## Features

### Frontend Features
1. **View Harvests**: Browse all harvests in a sortable, filterable table
2. **Record Harvest**: Create new harvest records with modal form
3. **Edit Harvest**: Update existing harvest information
4. **Delete Harvest**: Remove harvest records
5. **Search & Filter**: 
   - Search by crop type or variety
   - Filter by quality grade
   - Filter by year
6. **Statistics**:
   - Total harvests count
   - Total quantity in kg
   - Count of excellent quality harvests
   - Average yield per harvest
7. **Responsive Design**: Works on desktop and mobile devices

### Backend Features
1. **Authorization**: Only farmers can view/edit their own harvests
2. **Data Validation**: Comprehensive input validation
3. **Error Handling**: Detailed error responses
4. **Pagination**: Efficient data retrieval for large datasets
5. **Relationships**: Proper data relationships with crops and farms

## API Endpoints

### List Harvests
```
GET /api/farmer/harvests
```
Response: Paginated list of harvests for the authenticated farmer

### Create Harvest
```
POST /api/farmer/harvests
Body: {
  "crop_id": 1,
  "harvest_date": "2026-09-08",
  "quantity": 250.50,
  "unit": "kg",
  "quality_grade": "excellent",
  "notes": "Good harvest season"
}
```

### Get Harvest
```
GET /api/farmer/harvests/{id}
```

### Update Harvest
```
PUT /api/farmer/harvests/{id}
Body: { fields to update }
```

### Delete Harvest
```
DELETE /api/farmer/harvests/{id}
```

### Harvest Statistics
```
GET /api/farmer/harvests/statistics?year=2026
```

## How to Use

### From Frontend
1. Navigate to "Farm Management" > "Harvests" in sidebar
2. Click "Record Harvest" button
3. Select crop from ready crops list
4. Enter harvest date, quantity, and quality grade
5. Add optional notes
6. Click "Record Harvest"

### Available Units
- kg (kilograms)
- tonnes (metric tons)
- bags
- liters

### Quality Grades
- Excellent
- Good
- Fair
- Poor

## Important Notes

1. **Crop Status Update**: When recording a harvest, the associated crop status is automatically set to "harvested"

2. **Ready Crops**: Only crops with status "growing", "ready_for_harvest", or "planted" can have harvests recorded

3. **Authorization**: Farmers can only see and manage their own harvests (based on farm ownership)

4. **Date Format**: Harvest dates are stored as dates and displayed in user-friendly format

5. **Quantity Precision**: Quantities are stored with 2 decimal places

## Database Migration

To apply the latest changes to the harvests table structure:
```bash
php artisan migrate
```

This will run the migration that ensures all columns are properly named and exist.

## Testing the Integration

1. **Create Test Data**: Navigate to Crops section and ensure you have crops with "ready_for_harvest" or "growing" status

2. **Record Harvest**: 
   - Go to Harvests section
   - Click "Record Harvest"
   - Select a crop
   - Fill in the form
   - Submit

3. **View Harvests**: 
   - Should see the harvest in the table
   - Test the search and filter functionality

4. **Edit Harvest**: 
   - Click the edit button on a harvest row
   - Modify the data
   - Submit

5. **Delete Harvest**: 
   - Click the delete button on a harvest row
   - Confirm deletion

## File Structure

```
Frontend:
- src/views/farmer/HarvestsView.vue (Main component)
- src/components/Sidebar/FarmerSidebar.vue (Navigation)
- src/api/config.ts (API client)

Backend:
- app/Models/Harvest.php (Model)
- app/Http/Controllers/Api/Farmer/HarvestController.php (Controller)
- app/Http/Resources/Farmer/HarvestResource.php (Resource)
- database/migrations/2026_07_24_000053_create_harvests_table.php (Schema)
- database/migrations/2026_09_08_update_harvests_table.php (Updates)
- routes/api.php (Routes)
```

## Common Issues & Solutions

### Issue: "No harvests match your filters"
- **Solution**: Ensure you have crops with "ready_for_harvest" status. Record at least one harvest first.

### Issue: "Crop dropdown is empty"
- **Solution**: Create crops first in the Crops section and ensure they have appropriate status.

### Issue: API errors in browser console
- **Solution**: Check that Laravel backend is running on http://localhost:8000

### Issue: Unauthorized error
- **Solution**: Ensure you're logged in as a farmer. Check authentication token in localStorage.

## Next Steps

1. Test the complete harvest workflow
2. Verify data persists in the database
3. Test authorization (try accessing another farmer's harvests)
4. Check responsive design on mobile devices
5. Test error handling scenarios

