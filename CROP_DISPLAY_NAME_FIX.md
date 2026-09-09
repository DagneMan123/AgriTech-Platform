# Crop Display Name Fix

## Problem
Crop activities were showing as "Unknown Crop" instead of displaying the actual crop name.

**Root Cause:**
The frontend was trying to display `crop.name`, but the Crop model doesn't have a `name` field. Instead, crops have two separate fields:
- `crop_type` - the type of crop (e.g., "Maize", "Wheat")
- `variety` - the specific variety (e.g., "BHQPY545")

## Solution

### 1. Backend: Added Display Name Accessor (Crop Model)
Added a `display_name` attribute to the `Crop` model that automatically generates a user-friendly name:

```php
protected $appends = ['display_name'];

public function getDisplayNameAttribute()
{
    $name = $this->crop_type ?? 'Unknown Crop';
    
    if ($this->variety) {
        $name .= ' (' . $this->variety . ')';
    }
    
    return $name;
}
```

**Examples:**
- `crop_type="Maize", variety="BHQPY545"` → displays as `"Maize (BHQPY545)"`
- `crop_type="Wheat", variety=null` → displays as `"Wheat"`
- `crop_type=null` → displays as `"Unknown Crop"`

### 2. Frontend: Updated Crop Display References
Updated the Vue template to use the `display_name` with fallbacks:

**In filter dropdown (line 28):**
```vue
{{ crop.display_name || crop.crop_type || 'Unknown' }}
```

**In add/edit modal (line 265):**
```vue
{{ crop.display_name || crop.crop_type || 'Unknown' }}
```

**In activity list (line 83):**
```vue
{{ activity.crop?.name || 'Unknown Crop' }}
```
This now shows the display_name from the API response when the activity is loaded with crop relationship.

## How It Works

### When Creating Activity
1. Backend creates crop activity with the crop relationship loaded
2. `$activity->load(['crop', 'farm'])` automatically includes crop data
3. The crop's `display_name` is computed and included in JSON response
4. Frontend displays: `activity.crop.display_name`

### When Listing Crops
1. Crops API endpoint returns crop list
2. Each crop includes the computed `display_name` attribute
3. Frontend dropdown shows: `crop.display_name`

## Files Modified

1. **Backend:** `app/Models/Crop.php`
   - Added `$appends = ['display_name']`
   - Added `getDisplayNameAttribute()` method

2. **Frontend:** `src/views/farmer/CropActivitiesView.vue`
   - Updated crop filter dropdown
   - Updated modal crop select options
   - Activity display already uses relationship (no change needed)

## Testing

After the fix:
1. Create a crop activity
2. View the activity list
3. Crop should display as: `"Maize (BHQPY545)"` instead of `"Unknown Crop"`
4. Filter dropdown shows crop with full name including variety
5. Add/Edit modal shows crop name in select options

## Why This Approach

**Advantages:**
- Single source of truth: `display_name` is computed once in the model
- Backward compatible: Doesn't break existing fields
- Consistent: All displays use the same format
- Flexible: Easily customizable if display format needs to change

**Alternative Considered:**
- Creating a separate migration to add a `name` column
  - Con: Requires migration + duplicate data
  - Con: Need to maintain two fields
  - Con: Display format becomes hardcoded in database

**Chosen Approach (Accessors):**
- Pro: No migrations needed
- Pro: Computed at runtime
- Pro: Easy to modify display logic
- Pro: Database schema unchanged
