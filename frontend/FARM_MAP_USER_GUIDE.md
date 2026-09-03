# Farm Map - User Guide for Farmers

## Getting Started

### Accessing the Farm Map

1. **From Sidebar:**
   - Look for "Farm Management" section in the left sidebar
   - Click "Farm Map"
   - The map interface will load

2. **Direct URL:**
   - Navigate to: `/farmer/farm-map`

### Page Layout

The Farm Map page consists of three main sections:

```
┌─────────────────────────────────────────────────────────┐
│                    Page Header                          │
│              Farm Map Visualization                     │
└─────────────────────────────────────────────────────────┘
┌──────────────────┬────────────────────────────────────┐
│                  │                                    │
│  Control Panel   │         Interactive Map           │
│  (Left Side)     │         (Right Side)              │
│                  │                                    │
│  • Farm Select   │    [Shows all your farms]          │
│  • Map Options   │    • Clickable markers             │
│  • Map Type      │    • Boundary circles              │
│  • Actions       │    • Coordinates (optional)        │
│  • Farm Info     │                                    │
│                  │                                    │
└──────────────────┴────────────────────────────────────┘
```

## Control Panel (Left Side)

### 1. Farm Selection

**What It Does:**
- Lists all your registered farms
- Shows farm name and size in hectares
- Allows you to focus on one farm at a time

**How to Use:**
```
Step 1: Click the dropdown that says "-- Select a farm to view --"
Step 2: Choose your farm from the list
Step 3: The map will automatically update to show that farm
Step 4: The farm info section will display details about the selected farm
```

**Example:**
```
Farm Selection
▼ -- Select a farm to view --
  ▼ Green Valley Farm (5.5 ha)
    Sunny Hills Farm (8.0 ha)
    Riverside Plot (3.2 ha)
```

### 2. Map Options

**Show Farm Boundaries**
- ✓ **Checked** (Default): Displays a circular boundary around each farm
- □ **Unchecked**: Hides the boundary circles
- **Use Case:** Turn off when map gets too cluttered

**Show Crop Areas**
- ✓ **Checked** (Default): Prepares for future crop visualization
- □ **Unchecked**: Hides crop areas
- **Use Case:** For future agricultural data layers

**Show Coordinates**
- ✓ **Checked** (Default): Displays GPS latitude/longitude on map
- □ **Unchecked**: Hides coordinate labels
- **Use Case:** Turn off when you don't need exact coordinates

### 3. Map Type Selection

Choose how you want to view your farms:

**☉ Satellite**
- Aerial view of your land
- Best for: Seeing actual farm terrain
- Shows: Real satellite imagery
- Updates: Daily/Weekly from satellite providers

**☉ Terrain**
- Topographic view with elevation
- Best for: Understanding land slopes
- Shows: Elevation contours and terrain features
- Updates: Less frequently

**☉ Street** (Default)
- Road map style
- Best for: Location reference
- Shows: Roads, labels, basic geography
- Updates: Frequently

### 4. Action Buttons

**Zoom to Farm Button**
- **Status:** Disabled until you select a farm
- **Function:** Centers the map on your selected farm
- **Zoom Level:** Auto-zoomed to see entire farm
- **Time:** Instant

**Example:**
```
Before Clicking:              After Clicking:
[Far away view of country]   [Close-up of your farm]
                               Selected farm in center
                               Boundary visible
                               Coordinates visible
```

**View Details Button**
- **Status:** Disabled until you select a farm
- **Function:** Opens a detailed modal/popup
- **Shows:** Large map + detailed farm information
- **Contains:** All farm details in organized format

**Download Map Button**
- **Status:** Always enabled
- **Function:** Exports current map view
- **Format:** PNG image
- **Includes:** Current zoom level and visible layers

**Refresh Button**
- **Status:** Always enabled
- **Function:** Reloads farm data from server
- **Use Case:** After adding new farm in another window
- **Time:** 1-2 seconds

### 5. Selected Farm Info Panel

**Shows When:**
- A farm is selected from the dropdown

**Information Displayed:**
```
Selected Farm Info
─────────────────────
Name:          Green Valley Farm
Size:          5.5 hectares
Type:          Crop
Region:        Oromia
Coordinates:   Lat: 9.032000
               Lng: 38.746900
Address:       Kebele 15, North Shewa
Woreda:        Debre Berhan
Crops:         3 active crops
```

**Information NOT Shown:**
```
Shown Only in Details Modal:
• Detailed description
• Kebele (sub-village)
• Zone information
• Complete administrative hierarchy
```

## Interactive Map (Right Side)

### Map Controls (Built-in Leaflet Controls)

**Zoom Controls (+ and -)**
- Located in top-left corner of map
- **+** Button: Zooms in one level
- **-** Button: Zooms out one level

**Scroll Wheel Zoom**
- Scroll up: Zoom in
- Scroll down: Zoom out
- Can be disabled in settings (future feature)

**Pan (Drag) Map**
- Click and drag anywhere on map
- Moves the view in that direction
- Works with touch on mobile

### Visual Elements on Map

**🔴 Farm Markers (Circle Points)**
- **Blue circles**: Unselected farms
- **Green circles**: Currently selected farm
- **Size**: Larger = more important/selected
- **Clickable**: Click for popup info

**📍 Popup Information**
```
Clicking a marker shows:
┌──────────────────────┐
│ Green Valley Farm    │
│ Size: 5.5 ha         │
│ Type: Crop           │
└──────────────────────┘
```

**🔵 Farm Boundaries (Dashed Circles)**
- **Blue dashed circles**: Unselected farms
- **Green dashed circles**: Selected farm
- **Meaning**: Approximate farm coverage area
- **Accuracy**: Approximation based on size
- **Note:** Real boundaries shown in details view

**📌 GPS Coordinates**
- **Format:** Latitude (North/South)
                 Longitude (East/West)
- **Precision:** 6 decimal places
- **Example:** 9.032000, 38.746900
- **Location:** Displayed at farm marker point

### Interacting with Map

**Click on a Farm Marker:**
```
Result:
✓ Popup shows with farm info
✓ Marker turns green (if not already selected)
✓ Left panel farm info updates
✓ Boundary becomes more visible
```

**Close Popup:**
- Click the X button in the popup
- Click elsewhere on map
- Press Escape key

**View Different Farms:**
1. Use dropdown on left panel (Recommended)
2. OR Click different markers on map

## Detailed View Modal

### Opening the Modal

**Method 1: Button Click**
1. Select a farm from dropdown
2. Click "View Details" button
3. Modal opens with enlarged view

**Method 2: Keyboard Shortcut**
- (Future feature - not yet implemented)

### Modal Layout

```
┌──────────────────────────────────────────────────┐
│  Green Valley Farm - Detailed Map        [X]    │
├──────────────────────────────────────────────────┤
│  ┌──────────────────────┐  ┌────────────────┐   │
│  │                      │  │ Farm Inform... │   │
│  │   Large Map View     │  │  Name: Green   │   │
│  │                      │  │  Size: 5.5 ha  │   │
│  │                      │  │  Type: Crop    │   │
│  │                      │  │  Region:       │   │
│  │                      │  │  Oromia        │   │
│  │                      │  │                │   │
│  └──────────────────────┘  │ Coordinates:   │   │
│                             │ Lat: 9.0320  │   │
│                             │ Lng: 38.7469 │   │
│                             └────────────────┘   │
└──────────────────────────────────────────────────┘
```

### Left Side: Enlarged Map

**Shows:**
- Farm marker (green circle)
- Farm boundary (dashed circle)
- Satellite/Terrain/Street view based on selection
- Zoom controls

**Can Do:**
- Scroll to zoom in/out
- Drag to pan around farm
- Click marker for farm info popup

### Right Side: Farm Information

**Section 1: Basic Information**
```
Name:               Green Valley Farm
Size:               5.5 hectares
Type:               Crop
Region:             Oromia
```

**Section 2: Location Details**
```
Zone:               North Shewa
Woreda:             Debre Berhan
Kebele:             Kebele 15
Address:            Main road, Plot 5
```

**Section 3: GPS Coordinates**
```
Latitude:           9.032000 (°North)
Longitude:          38.746900 (°East)
```

**Section 4: Farm Statistics**
```
Active Crops:       3
```

**Section 5: Description (If Available)**
```
A productive crop farm specializing in teff 
and wheat cultivation. Well irrigated with 
modern equipment.
```

### Closing the Modal

**Method 1: Click X Button**
- Located in top-right corner of modal header

**Method 2: Click Outside Modal**
- Click on the dark overlay outside modal
- Modal will close

**Method 3: Keyboard**
- Press Escape key

**After Closing:**
- Map remains on same zoom level
- Farm selection remains unchanged
- Can reopen by clicking "View Details" again

## Common Tasks

### Task 1: View All Your Farms

**Steps:**
1. Open Farm Map from sidebar
2. Don't select any farm (leave dropdown empty)
3. View all farms as blue markers on map
4. Can see all farm locations at a glance

**Result:**
- All farms visible
- Each marked with blue circle
- Boundaries shown (if enabled)

### Task 2: Focus on One Farm

**Steps:**
1. Open dropdown "Select a farm to view"
2. Choose your farm
3. Click "Zoom to Farm" button
4. Farm details appear in left panel

**Result:**
- Map zoomed to farm location
- Farm marker is green (selected)
- Boundary clearly visible
- Farm info displayed on left

### Task 3: Check Farm Exact Location

**Steps:**
1. Select farm from dropdown
2. Enable "Show Coordinates" (if not enabled)
3. Look at map - coordinates visible at marker
4. Check GPS coordinates in farm info panel

**Result:**
- Coordinates displayed on map
- GPS location confirmed
- Can share coordinates with others

### Task 4: Export Farm Map

**Steps:**
1. Select farm (optional)
2. Adjust zoom level and view as desired
3. Click "Download Map" button
4. Map image downloads to computer

**Result:**
- PNG image file downloaded
- Can be printed or shared
- Shows current map view with all visible markers

### Task 5: Compare Multiple Farms

**Steps:**
1. Leave farm dropdown empty (show all farms)
2. Adjust zoom to see multiple farms
3. Observe all farms at once
4. Switch between farms using dropdown
5. Use "Zoom to Farm" for detailed view of each

**Result:**
- Visual comparison of farm locations
- Relative sizes apparent
- Easy to see farm distribution

## Tips & Tricks

### 💡 Pro Tips

**Tip 1: Use Map Type for Different Purposes**
- **Satellite:** See actual farm layout and terrain
- **Terrain:** Understand elevation and slopes
- **Street:** Get location reference with nearby roads

**Tip 2: Zoom Levels**
- **Zoomed Out (Level 6):** See all farms, entire region
- **Medium (Level 12):** See individual farm details
- **Zoomed In (Level 16):** See farm with roads and structures

**Tip 3: Coordinate Display**
- Enable coordinates when precise location is needed
- Useful for giving directions to buyers or experts
- Coordinates help with delivery/transportation planning

**Tip 4: Regular Refresh**
- After adding a new farm, refresh map to see it
- Updates farmer info if you changed details elsewhere
- Good practice once per session

**Tip 5: Download Before Sharing**
- Download map image before presenting to others
- Better than screenshots (higher quality)
- Can annotate downloaded images

### ⚠️ Common Issues & Solutions

**Issue: Map Not Loading**
- Solution: Check internet connection
- Try: Refresh page with F5
- Contact: Support if persists

**Issue: Farms Not Showing**
- Solution: Make sure farms have GPS coordinates
- Check: Farm info has Latitude/Longitude values
- Try: Add coordinates to farm details

**Issue: Marker Not in Right Place**
- Solution: Coordinates might be incorrect
- Check: Latitude and Longitude values
- Update: Farm location with correct coordinates

**Issue: Map Too Slow**
- Solution: Disable "Show Coordinates"
- Try: Use "Street" map type instead of "Satellite"
- Refresh: Browser cache (Ctrl+Shift+Delete)

## Keyboard Shortcuts

| Shortcut | Function | Status |
|----------|----------|--------|
| Escape | Close Modal | ✅ Active |
| +/- | Zoom In/Out | ✅ (via buttons) |
| Ctrl+S | Save Map | 🔜 Future |
| Ctrl+L | Show All Farms | 🔜 Future |
| Ctrl+D | Download Map | 🔜 Future |

## Frequently Asked Questions

**Q: Why don't my farms appear on the map?**
A: Farms need GPS coordinates (Latitude/Longitude) to appear. Update your farm details with coordinates.

**Q: Can I edit farm boundaries on the map?**
A: Not yet, but coming soon. For now, edit farm size in farm details page.

**Q: What's the difference between the boundary circle and actual farm?**
A: The circle is an approximation. Real boundaries depend on exact coordinates of all corners.

**Q: Can I print the map?**
A: Yes! Download the map image first, then print it from your photo viewer.

**Q: Why are some map options disabled?**
A: "Crop Areas" option is for future features. "Show Coordinates" is optional.

**Q: How often does the satellite imagery update?**
A: Satellite maps update daily from providers. May take time to propagate globally.

**Q: Can I see weather on the map?**
A: Not yet. Weather integration coming in future updates.

**Q: What if my GPS coordinates are inaccurate?**
A: You can update them in the Farm Details page. Use a GPS device or Google Maps for accuracy.

## Support & Help

### Getting Help

1. **In-App Help:**
   - Hover over question marks (?) for tooltips
   - Check control labels for descriptions

2. **Documentation:**
   - See FARM_MAP_FEATURE.md for technical details
   - Check this guide for user instructions

3. **Contact Support:**
   - Email: support@agritech-platform.com
   - Phone: +251-XXX-XXXX
   - Online Chat: Available 8am-5pm

### Reporting Issues

If you encounter bugs:
1. Note the exact issue and steps to reproduce
2. Take a screenshot if possible
3. Note your browser and device
4. Contact support with these details

### Feedback & Suggestions

We'd love to hear your feedback!
- Suggestions for improvements
- Features you'd like to see
- Any usability issues

Send to: feedback@agritech-platform.com

---

**Last Updated:** August 31, 2026
**Version:** 1.0
**Status:** Ready for User