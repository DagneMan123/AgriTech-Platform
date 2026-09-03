# Farm Map Feature Documentation

## Overview
The Farm Map feature provides farmers with an interactive map interface to visualize and manage their farm locations, boundaries, and geographical information.

## Features

### 1. **Interactive Map Visualization**
- Display all farms on an interactive Leaflet-based map
- Support for multiple map types:
  - Street Map (OpenStreetMap)
  - Satellite Imagery (ESRI)
  - Terrain Map (ESRI)
- Real-time marker rendering for each farm
- Popup information on marker hover/click

### 2. **Farm Selection and Filtering**
- Dropdown selector to view specific farms
- Selected farm highlighting with visual indicators
- Real-time farm information panel showing:
  - Farm name and size
  - Farm type (crop, livestock, mixed, fishery)
  - GPS coordinates
  - Region and administrative boundaries
  - Number of active crops

### 3. **Map Display Options**
- **Show Farm Boundaries**: Toggle visibility of circular farm boundary approximations
- **Show Crop Areas**: Prepare for future crop area visualization
- **Show Coordinates**: Display GPS coordinates on the map
- These options update the map in real-time

### 4. **Navigation Controls**
- **Zoom to Farm**: Automatically center and zoom to selected farm
- **Refresh**: Reload farm data from server
- **Download Map**: Export map as image (feature ready for extension)

### 5. **Detailed View Modal**
- Click "View Details" to open a detailed modal showing:
  - Enlarged map view of selected farm
  - Complete farm information in a side panel
  - GPS coordinates
  - Farm location details (address, zone, woreda, kebele)
  - Farm size and type information
  - Farm description (if available)

## File Structure

```
frontend/src/
├── views/
│   └── farmer/
│       └── FarmMapView.vue          # Main map page component
├── components/
│   └── Map/
│       └── MapControls.vue          # Control panel component
├── composables/
│   └── useMap.ts                    # Map utility composable
└── router/
    └── index.ts                     # Route configuration (updated)

index.html                           # Updated with Leaflet CDN links
```

## Components

### FarmMapView.vue
Main view component that orchestrates the farm map interface.

**Key Props:**
- `farms`: Array of farm objects with location data
- `selectedFarmId`: Currently selected farm ID
- `mapType`: Current map type ('street', 'satellite', 'terrain')
- `showBoundaries`: Toggle farm boundaries visibility
- `showCoordinates`: Toggle coordinate labels visibility

**Key Methods:**
- `fetchFarms()`: Load farms from API
- `updateMapDisplay()`: Refresh map with current settings
- `zoomToSelectedFarm()`: Center map on selected farm
- `showFarmDetails()`: Open detailed modal

### MapControls.vue
Reusable control panel component for map interactions.

**Props:**
- `farms`: Array of available farms
- `selectedFarmId`: Current selection
- `showBoundaries`, `showCropAreas`, `showCoordinates`: Display options
- `mapType`: Selected map type
- `loading`: Loading state

**Emits:**
- `update:selectedFarmId`: When farm selection changes
- `zoom-to-farm`: When zoom button clicked
- `view-details`: When details button clicked
- `download-map`: When download button clicked
- `refresh`: When refresh button clicked

## Composable: useMap.ts

A reusable Vue composable for managing Leaflet map operations.

### Methods

```typescript
// Initialize the map with specified container
initializeMap(containerId: string): L.Map | null

// Add different tile layers
addTileLayer(type: 'osm' | 'satellite' | 'terrain'): void

// Add individual marker to map
addMarker(farm: FarmMarker, options?: any): L.Marker | null

// Add multiple farm markers at once
addFarmMarkers(farms: FarmMarker[], selectedId?: number | string): void

// Add circular farm boundary
addFarmBoundary(farm: FarmMarker, options?: any): L.Circle | null

// Add coordinate labels to map
addCoordinateLabels(farms: FarmMarker[]): void

// Clear all markers and circles
clearMarkers(): void

// Zoom to specific location
zoomToLocation(latitude: number, longitude: number, zoomLevel?: number): void

// Fit all markers in view
fitBounds(): void

// Get current map center
getCenter(): [number, number] | null

// Get current zoom level
getZoom(): number | null

// Add layer control UI
addLayerControl(baseLayers: Record, overlayLayers?: Record): void

// Destroy map instance
destroyMap(): void
```

## API Integration

The Farm Map feature integrates with the backend API:

### Endpoints Used
- `GET /farmer/farms` - Fetch all farms for authenticated farmer

### Data Structure
```typescript
interface Farm {
  id: number
  name: string
  size_hectares: number
  farm_type: 'crop' | 'livestock' | 'mixed' | 'fishery'
  region: string
  zone: string
  woreda: string
  kebele?: string
  address: string
  latitude: number
  longitude: number
  crops_count?: number
  description?: string
}
```

## Styling

### CSS Variables and Classes
- Color Scheme:
  - Primary Green: `#10b981` (selected/active)
  - Secondary Blue: `#3b82f6` (inactive)
  - Text: `#333`, `#666`, `#999`
  - Borders: `#e5e7eb`

- Responsive Breakpoints:
  - Desktop: Full layout (map + controls side-by-side)
  - Tablet (≤1024px): Stacked layout
  - Mobile (≤768px): Simplified layout with collapsible controls

## Installation & Setup

### 1. Dependencies
The feature requires Leaflet.js library loaded via CDN in `index.html`:

```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
```

### 2. Router Configuration
The route is configured in `frontend/src/router/index.ts`:

```typescript
{
  path: 'farm-map',
  name: 'farmer-farm-map',
  component: FarmerFarmMapView,
  meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Farm Map' }
}
```

### 3. Access
Navigate to `/farmer/farm-map` or click "Farm Map" in the Farmer sidebar.

## Usage Examples

### Basic Farm Map Display
```vue
<FarmMapView />
```

### Programmatically Select a Farm
```vue
<script setup>
const selectedFarmId = ref(1)
// Map will automatically update when selectedFarmId changes
</script>
```

### Using the useMap Composable Standalone
```vue
<script setup>
import { useMap } from '@/composables/useMap'

const { initializeMap, addFarmMarkers, zoomToLocation } = useMap()

onMounted(() => {
  initializeMap('my-map-container')
  addFarmMarkers(farms.value)
  zoomToLocation(9.0320, 38.7469, 10)
})
</script>
```

## Performance Considerations

1. **Map Initialization**: Lazy-loaded with 500ms delay to ensure DOM is ready
2. **Marker Clustering**: For future enhancement - consider leaflet-markercluster for 100+ farms
3. **Lazy Layer Loading**: Only active tiles are loaded based on viewport
4. **Debounced Updates**: Map updates are consolidated when multiple settings change

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Future Enhancements

1. **Crop Area Visualization**: Draw actual crop field boundaries using GeoJSON
2. **Historical Map Data**: Show farm changes over time
3. **Weather Layer Integration**: Overlay weather data on map
4. **Delivery Route Planning**: Integration with logistics module
5. **Map Sharing**: Share farm location with buyers/experts
6. **Drawing Tools**: Allow farmers to draw boundaries on map
7. **Export Functionality**: Export map as PDF or image with proper legend
8. **Offline Maps**: Enable offline map viewing for areas without internet
9. **3D Terrain Visualization**: Use Mapbox 3D terrain for elevation visualization

## Troubleshooting

### Map Not Displaying
- Verify Leaflet CDN links in `index.html`
- Check browser console for JS errors
- Ensure container element with ID `farm-map` exists

### Markers Not Showing
- Verify farms have valid latitude/longitude coordinates
- Check if `loading` state is resolved
- Verify API is returning farm data correctly

### Performance Issues
- Consider implementing marker clustering for 100+ farms
- Reduce popup content complexity
- Use vector tiles instead of raster tiles for better performance

### Coordinate Display Issues
- Ensure GPS coordinates are stored with proper precision (min 6 decimal places)
- Verify coordinate validation in backend

## Support & Maintenance

For issues or enhancements:
1. Check browser console for errors
2. Verify API connectivity
3. Check farm data has valid GPS coordinates
4. Review Leaflet documentation: https://leafletjs.com/

## References

- Leaflet.js: https://leafletjs.com/
- Vue 3 Composition API: https://vue3js.cn/docs/guide/composition-api.html
- ESRI Tile Services: https://server.arcgisonline.com/
- OpenStreetMap: https://www.openstreetmap.org/
