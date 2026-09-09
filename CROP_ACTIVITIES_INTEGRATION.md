# Crop Activities Page - Integration Guide

## Summary
The Crop Activities page has been fully integrated with the backend database and is now fully functional for recording, viewing, editing, and deleting crop management activities.

## Key Changes Made

### 1. Backend Controller Fix (CropActivityController.php)
**Problem**: The controller was trying to access `Auth::user()->farmer->id`, but many users don't have a separate Farmer profile record.

**Solution**: Changed all methods to use `Auth::user()->id` directly as `farmer_id`, which aligns with how the `farms` table stores the relationship (farms.farmer_id references users.id).

**Methods Updated**:
- `index()` - Retrieve all crop activities for authenticated user
- `store()` - Create new crop activity with proper validation
- `show()` - Get a specific activity
- `update()` - Edit existing activity
- `destroy()` - Delete activity

### 2. Frontend Fixes
- Removed unsafe `Accept-Encoding` header that was causing browser warnings
- Improved error handling in `fetchCrops()` and `fetchFarms()` functions
- Better support for different API response formats
- Added proper error messages for form validation failures

### 3. Database Setup
- Created `EnsureCropActivitiesTableExists.php` middleware that auto-creates the table if it doesn't exist
- Table includes proper foreign key constraints and indexes for performance
- Soft deletes enabled for audit trail

## API Endpoints

### Get All Activities
```
GET /api/farmer/crop-activities
```
Returns all crop activities for authenticated farmer with related crop and farm data.

### Create Activity
```
POST /api/farmer/crop-activities
```

Request body:
```json
{
  "crop_id": 1,
  "farm_id": 1,
  "activity_type": "planting",
  "activity_date": "2026-09-08",
  "activity_time": null,
  "description": "Initial planting on main field",
  "quantity": 50,
  "unit": "kg",
  "cost": 1500,
  "weather": "sunny",
  "notes": "Used certified seeds"
}
```

### Update Activity
```
PUT /api/farmer/crop-activities/:id
```

### Delete Activity
```
DELETE /api/farmer/crop-activities/:id
```

## Frontend Component Features

### CropActivitiesView.vue
- **Filters**: By crop, activity type, and date range
- **Display**: Timeline view showing all activities chronologically
- **Actions**: 
  - Add new activity (button in header)
  - Edit existing activity
  - Delete activity
  - Filter and search

### Form Fields
- Crop selection (dropdown - loads from API)
- Farm selection (dropdown - loads from API)
- Activity type (preset options: planting, watering, fertilizing, etc.)
- Activity date (required)
- Activity time (optional)
- Description (optional)
- Quantity and unit (optional)
- Cost in ETB (optional)
- Weather condition (optional)
- Additional notes (optional)

## Error Handling

### "Farmer profile not found" Error
This was the main error that has been fixed. It occurred because:
1. The system was looking for a separate Farmer model instance
2. The relationship between User and Farmer wasn't established for all farmers
3. The farms table uses `user_id` directly as `farmer_id`, not `farmer.id`

**Resolution**: Updated all methods to use `Auth::user()->id` instead of `Auth::user()->farmer->id`

### Validation Errors
- Crop selection is required
- Farm selection is required
- Activity type must be one of the predefined types
- Activity date is required
- All numeric fields (quantity, cost) must be non-negative

## Database Schema

```sql
CREATE TABLE crop_activities (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    crop_id BIGINT NOT NULL,
    farm_id BIGINT NOT NULL,
    farmer_id BIGINT NOT NULL,
    activity_type ENUM('planting', 'watering', 'fertilizing', 'weeding', 'pesticide', 'pruning', 'harvesting', 'other'),
    activity_date DATE NOT NULL,
    activity_time TIME NULL,
    description TEXT NULL,
    quantity DECIMAL(10,2) NULL,
    unit VARCHAR(50) NULL,
    cost DECIMAL(12,2) NULL,
    weather VARCHAR(50) NULL,
    notes TEXT NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX (crop_id, activity_date),
    INDEX (farm_id, activity_date),
    INDEX (farmer_id, activity_date),
    INDEX (activity_type)
);
```

## Testing the Integration

1. **Login as Farmer**: Use a farmer account
2. **Navigate to Crop Activities**: From the farmer dashboard
3. **Create Activity**: Click "Add Activity" button
4. **Select Crop and Farm**: Should populate from your farms
5. **Fill Form**: Complete all required fields
6. **Save**: Click "Save Activity" button
7. **View**: Should appear in the timeline
8. **Edit**: Click edit button on any activity
9. **Delete**: Click delete button (with confirmation)

## Security

- All endpoints require authentication (`auth:api` middleware)
- All endpoints require farmer role (`role:farmer` check)
- Farmers can only access their own crops and activities
- Ownership verification on update/delete operations

## Performance Optimizations

- Indexes on frequently queried columns
- Lazy loading of relationships (crop, farm)
- Single query for fetching activities with related data
- No pagination (all activities loaded, as dataset is typically small)

## Next Steps (Optional)

1. Add bulk actions (select multiple activities)
2. Add export to CSV/PDF
3. Add activity statistics and analytics
4. Add activity templates for common operations
5. Add notifications for upcoming activities
6. Add mobile app integration
