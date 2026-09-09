# Crop Activities Feature - Setup & Integration Guide

## Overview
The Crop Activities feature allows farmers to track and manage all crop-related field operations and activities throughout the crop lifecycle. This includes planting, watering, fertilizing, weeding, pesticide application, pruning, and harvesting activities.

## System Architecture

### Database Layer
- **Table**: `crop_activities`
- **Location**: `database/migrations/2026_09_02_create_crop_activities_table.php`
- **Auto-Creation**: Middleware automatically creates the table on first API request if it doesn't exist

#### Table Structure:
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
  
  -- Foreign Keys
  FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE,
  FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
  FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE,
  
  -- Indexes
  INDEX idx_crop_date (crop_id, activity_date),
  INDEX idx_farm_date (farm_id, activity_date),
  INDEX idx_farmer_date (farmer_id, activity_date),
  INDEX idx_activity_type (activity_type)
);
```

### Backend API Layer

#### Controller
- **File**: `backend/app/Http/Controllers/Api/Farmer/CropActivityController.php`
- **Namespace**: `App\Http\Controllers\Api\Farmer`
- **Routes**: `/api/farmer/crop-activities`

#### Endpoints:
1. **GET** `/api/farmer/crop-activities` - List all activities for authenticated farmer
   - Optional filters: `crop_id`, `farm_id`, `activity_type`, `from_date`, `to_date`
   - Returns paginated results (50 per page by default)

2. **POST** `/api/farmer/crop-activities` - Create a new crop activity
   - Required fields: `crop_id`, `farm_id`, `activity_type`, `activity_date`
   - Optional fields: `activity_time`, `description`, `quantity`, `unit`, `cost`, `weather`, `notes`

3. **GET** `/api/farmer/crop-activities/{id}` - Get specific activity details

4. **PUT** `/api/farmer/crop-activities/{id}` - Update existing activity

5. **DELETE** `/api/farmer/crop-activities/{id}` - Delete activity (soft delete)

#### Model
- **File**: `backend/app/Models/CropActivity.php`
- **Relations**:
  - `belongsTo(Crop)`
  - `belongsTo(Farm)`
  - `belongsTo(Farmer)`

### Frontend Layer

#### Vue Component
- **File**: `frontend/src/views/farmer/CropActivitiesView.vue`
- **Route**: `/farmer/crop-activities`
- **Layout**: Uses `FarmerSidebar` and `FarmerLayout`

#### Features Implemented:
- ✅ List all crop activities in timeline view
- ✅ Filter by crop, activity type, and date range
- ✅ Add new crop activity via modal form
- ✅ Edit existing crop activities
- ✅ Delete activities with confirmation
- ✅ Real-time form validation
- ✅ Error handling and user feedback
- ✅ Responsive design
- ✅ Activity badges with color coding
- ✅ Currency formatting for costs (ETB)
- ✅ Date formatting

#### API Client Configuration
- **File**: `frontend/src/api/config.ts`
- **Base URL**: `http://localhost:8000/api` (configurable via `.env`)
- **Default Port**: 5173 for frontend, 8000 for backend

## Setup Instructions

### 1. Backend Setup

#### Ensure Database Migration
The migration file creates the necessary table automatically when accessed. To manually run:

```bash
cd backend
php artisan migrate
```

#### Verify Routes are Registered
Routes are registered in `backend/routes/api.php`:
```php
Route::apiResource('/crop-activities', CropActivityController::class);
```

The route is protected by:
- `auth:api` - User must be authenticated
- `role:farmer` - User must have farmer role

#### Check Middleware
Verify the following middleware is registered in `bootstrap/app.php`:
- `EnsureCropActivitiesTableExists` - Auto-creates table if missing
- `CorsMiddleware` - Handles CORS for frontend requests

### 2. Frontend Setup

#### Environment Configuration
File: `.env` in frontend root
```
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=AgriConnect
```

#### Install Dependencies
```bash
cd frontend
npm install
```

#### Run Development Server
```bash
npm run dev
```
Server runs on `http://localhost:5173`

### 3. Verify Integration

#### Test Backend API
```bash
# Get crop activities (requires valid JWT token)
curl -X GET http://localhost:8000/api/farmer/crop-activities \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# Create new activity
curl -X POST http://localhost:8000/api/farmer/crop-activities \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "crop_id": 1,
    "farm_id": 1,
    "activity_type": "watering",
    "activity_date": "2024-09-08",
    "description": "Regular watering cycle",
    "quantity": 500,
    "unit": "liters",
    "cost": 50
  }'
```

#### Test Frontend
1. Navigate to `http://localhost:5173/farmer/crop-activities`
2. Login with farmer account
3. Click "Add Activity" button
4. Fill in the form and submit
5. Verify activity appears in the list

## Key Features Explained

### Activity Types
- **Planting**: Initial crop planting
- **Watering**: Irrigation and water management
- **Fertilizing**: Application of fertilizers
- **Weeding**: Removal of unwanted plants
- **Pesticide**: Pest and disease control
- **Pruning**: Plant trimming and maintenance
- **Harvesting**: Crop collection
- **Other**: Miscellaneous activities

### Weather Tracking
Activities can be associated with weather conditions:
- Sunny
- Cloudy
- Rainy
- Windy
- Cold
- Hot

### Cost Tracking
Each activity can have associated costs (in ETB - Ethiopian Birr):
- Input costs (seeds, fertilizer, pesticides)
- Labor costs
- Equipment usage
- Transportation

### Data Validation

#### Backend Validation Rules:
```php
'crop_id' => 'required|exists:crops,id',
'farm_id' => 'required|exists:farms,id',
'activity_type' => 'required|in:planting,watering,fertilizing,weeding,pesticide,pruning,harvesting,other',
'activity_date' => 'required|date',
'activity_time' => 'nullable|date_format:H:i',
'description' => 'nullable|string|max:1000',
'quantity' => 'nullable|numeric|min:0',
'unit' => 'nullable|string|max:50',
'cost' => 'nullable|numeric|min:0',
'weather' => 'nullable|string|max:50',
'notes' => 'nullable|string|max:1000'
```

#### Authorization:
- Users can only access/modify their own crop activities
- System verifies crop and farm ownership before allowing operations

## Error Handling

### Common Errors and Solutions:

1. **"Farmer profile not found"**
   - Cause: User doesn't have associated farmer record
   - Solution: Complete farmer profile setup during registration

2. **"Crop or farm does not belong to you"**
   - Cause: Attempting to create activity for someone else's crop/farm
   - Solution: Verify crop/farm selection belongs to your account

3. **"Unauthorized" (403)**
   - Cause: User doesn't have farmer role
   - Solution: Login with correct farmer account

4. **"Validation error" (422)**
   - Cause: Invalid form data
   - Solution: Check error messages and correct field values

5. **"Failed to load activity logs" (500)**
   - Cause: Database or server error
   - Solution: Check backend logs, ensure database is running

## Performance Considerations

### Database Indexes
The following indexes are created for optimal query performance:
- `(crop_id, activity_date)` - Fast filtering by crop and date
- `(farm_id, activity_date)` - Fast filtering by farm and date
- `(farmer_id, activity_date)` - Fast filtering by farmer and date
- `(activity_type)` - Fast filtering by activity type

### Pagination
- Default: 50 records per page
- Customizable via `per_page` query parameter
- Pagination info returned with response

### Soft Deletes
Activities use soft deletes (not permanently deleted):
- Allows data recovery if needed
- Maintains referential integrity
- Original timestamps preserved

## Frontend Components Used

### Icons (from lucide-vue-next)
- `PlusCircle` - Add button
- `Droplets` - Watering activity
- `Leaf` - Fertilizing activity
- `Bug` - Pesticide application
- `Scissors` - Pruning activity
- `Flame` - Planting activity
- `Sprout` - Harvesting/empty state
- `AlertCircle` - Errors and other activities

### Styling
- Custom CSS with responsive design
- Color-coded activity badges
- Timeline layout for chronological display
- Modal dialogs for forms
- Toast notifications for feedback

## Related Models & Relationships

### Models Connected:
1. **Farmer** → has many CropActivities
2. **Crop** → has many CropActivities
3. **Farm** → has many CropActivities
4. **User** → has Farmer → has many CropActivities

### Related Features:
- **Crops Management**: Create and manage crops
- **Farms Management**: Manage farm information
- **Harvests**: Record harvest data
- **Weather Forecasts**: Track weather conditions
- **Market Prices**: Monitor crop prices

## Development Notes

### Files Modified/Created:
- ✅ Database: Migration file for crop_activities table
- ✅ Backend: CropActivityController with full CRUD
- ✅ Backend: CropActivity model with relationships
- ✅ Backend: Middleware for table auto-creation
- ✅ Frontend: CropActivitiesView.vue with full functionality
- ✅ Backend: Routes configured in api.php
- ✅ Backend: CORS middleware enabled

### Testing Recommendations:
1. Test all CRUD operations
2. Verify authorization (can't access others' activities)
3. Test form validation
4. Test pagination
5. Test filters (crop, type, date)
6. Test error scenarios
7. Test responsive design on mobile

## Troubleshooting

### If activities aren't loading:
1. Check authentication token in browser DevTools
2. Verify farmer profile exists
3. Check backend logs for errors
4. Ensure database connection is working
5. Check CORS configuration

### If table doesn't exist:
1. Run `php artisan migrate`
2. Or access any crop-activities endpoint to trigger auto-creation via middleware
3. Check database permissions

### If form submission fails:
1. Check browser console for validation errors
2. Verify all required fields are filled
3. Check API response in Network tab
4. Ensure crop and farm are selected
5. Verify date format (YYYY-MM-DD)

## Future Enhancements

Potential improvements for the feature:
- [ ] Bulk upload activities via CSV
- [ ] Activity templates for common operations
- [ ] Automatic reminders for seasonal activities
- [ ] Activity analytics and insights
- [ ] Integration with weather API for automatic weather data
- [ ] Photo/attachment support for activities
- [ ] Activity history and audit trail
- [ ] Collaborative activity planning
- [ ] Mobile app support

## Support & Documentation

For more information:
- Backend: Check `/backend/app/Http/Controllers/Api/Farmer/CropActivityController.php`
- Frontend: Check `/frontend/src/views/farmer/CropActivitiesView.vue`
- Database: Check migration file `/backend/database/migrations/2026_09_02_create_crop_activities_table.php`
- API: See `/backend/routes/api.php`
