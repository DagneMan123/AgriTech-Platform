import { ref, onMounted, onUnmounted } from 'vue'
import L from 'leaflet'

interface MapOptions {
  center?: [number, number]
  zoom?: number
  mapContainer?: string
}

interface FarmMarker {
  id: number | string
  latitude: number
  longitude: number
  name: string
  size_hectares?: number
  farm_type?: string
}

/**
 * Composable for managing Leaflet map operations
 * Provides map initialization, marker management, and layer controls
 */
export function useMap(options: MapOptions = {}) {
  const defaultOptions = {
    center: [9.0320, 38.7469] as [number, number],
    zoom: 6,
    mapContainer: 'farm-map',
    ...options
  }

  let mapInstance: L.Map | null = null
  const markers = ref<L.Marker[]>([])
  const circles = ref<L.Circle[]>([])
  const isMapReady = ref(false)

  /**
   * Initialize the map with Leaflet
   */
  const initializeMap = (containerId: string = defaultOptions.mapContainer) => {
    try {
      const container = document.getElementById(containerId)
      if (!container) {
        console.error(`Container ${containerId} not found`)
        return null
      }

      // Remove existing map if present
      if (mapInstance) {
        mapInstance.remove()
      }

      mapInstance = L.map(containerId).setView(
        defaultOptions.center,
        defaultOptions.zoom
      )

      // Add default OSM tile layer
      addTileLayer('osm')

      isMapReady.value = true
      return mapInstance
    } catch (error) {
      console.error('Error initializing map:', error)
      return null
    }
  }

  /**
   * Add different tile layers to the map
   */
  const addTileLayer = (type: 'osm' | 'satellite' | 'terrain' = 'osm') => {
    if (!mapInstance) return

    const layers = {
      osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
      }),
      satellite: L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        {
          attribution: 'Tiles &copy; Esri',
          maxZoom: 18
        }
      ),
      terrain: L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
        {
          attribution: 'Tiles &copy; Esri',
          maxZoom: 18
        }
      )
    }

    layers[type].addTo(mapInstance)
  }

  /**
   * Add a marker to the map with popup
   */
  const addMarker = (
    farm: FarmMarker,
    options: any = {}
  ): L.CircleMarker | null => {
    if (!mapInstance || !farm.latitude || !farm.longitude) return null

    const isSelected = options.selected || false

    const marker = L.circleMarker([farm.latitude, farm.longitude], {
      radius: isSelected ? 12 : 8,
      fillColor: isSelected ? '#10b981' : '#3b82f6',
      color: isSelected ? '#059669' : '#1e40af',
      weight: 2,
      opacity: 1,
      fillOpacity: 0.8,
      ...options.markerOptions
    })

    const popupContent = `
      <div class="farm-popup" style="font-size: 12px;">
        <strong>${farm.name}</strong><br>
        ${farm.size_hectares ? `Size: ${farm.size_hectares} ha<br>` : ''}
        ${farm.farm_type ? `Type: ${capitalizeFirstLetter(farm.farm_type)}` : ''}
      </div>
    `

    marker.bindPopup(popupContent).addTo(mapInstance)
    markers.value.push(marker as any)

    return marker
  }

  /**
   * Add multiple farm markers to the map
   */
  const addFarmMarkers = (farms: FarmMarker[], selectedId?: number | string) => {
    clearMarkers()

    farms.forEach((farm) => {
      if (farm.latitude && farm.longitude) {
        addMarker(farm, {
          selected: farm.id === selectedId,
          markerOptions: {}
        })
      }
    })
  }

  /**
   * Add a circular boundary around a farm
   */
  const addFarmBoundary = (
    farm: FarmMarker,
    options: any = {}
  ): L.Circle | null => {
    if (!mapInstance || !farm.latitude || !farm.longitude) return null

    const defaultRadius = farm.size_hectares
      ? Math.sqrt(farm.size_hectares * 10000) / Math.PI
      : 500

    const circle = L.circle([farm.latitude, farm.longitude], {
      radius: defaultRadius * 2,
      color: '#10b981',
      weight: 1,
      opacity: 0.3,
      fillOpacity: 0.1,
      dashArray: '5, 5',
      ...options
    }).addTo(mapInstance)

    circles.value.push(circle)
    return circle
  }

  /**
   * Add coordinate labels to the map
   */
  const addCoordinateLabels = (farms: FarmMarker[]) => {
    if (!mapInstance) return

    farms.forEach((farm) => {
      if (farm.latitude && farm.longitude) {
        L.marker([farm.latitude, farm.longitude], {
          icon: L.divIcon({
            html: `<div style="
              background: rgba(255, 255, 255, 0.9);
              border: 1px solid #d1d5db;
              border-radius: 4px;
              padding: 4px 8px;
              font-size: 11px;
              text-align: center;
              font-weight: 600;
              color: #555;
            ">${farm.latitude.toFixed(4)}<br>${farm.longitude.toFixed(4)}</div>`,
            className: 'coordinate-label',
            iconSize: [80, 40]
          })
        }).addTo(mapInstance)
      }
    })
  }

  /**
   * Clear all markers from the map
   */
  const clearMarkers = () => {
    if (!mapInstance) return

    markers.value.forEach((marker) => {
      mapInstance?.removeLayer(marker)
    })
    markers.value = []

    circles.value.forEach((circle) => {
      mapInstance?.removeLayer(circle)
    })
    circles.value = []
  }

  /**
   * Zoom to a specific farm or location
   */
  const zoomToLocation = (latitude: number, longitude: number, zoomLevel: number = 13) => {
    if (!mapInstance) return

    mapInstance.setView([latitude, longitude], zoomLevel)
  }

  /**
   * Fit map bounds to show all markers
   */
  const fitBounds = () => {
    if (!mapInstance || markers.value.length === 0) return

    const group = new L.FeatureGroup(markers.value)
    mapInstance.fitBounds(group.getBounds(), { padding: [50, 50] })
  }

  /**
   * Get current map center
   */
  const getCenter = () => {
    if (!mapInstance) return null
    const center = mapInstance.getCenter()
    return [center.lat, center.lng]
  }

  /**
   * Get current zoom level
   */
  const getZoom = () => {
    return mapInstance?.getZoom() || null
  }

  /**
   * Add layer control to the map
   */
  const addLayerControl = (baseLayers: Record<string, L.TileLayer>, overlayLayers?: Record<string, L.Layer>) => {
    if (!mapInstance) return

    L.control.layers(baseLayers, overlayLayers).addTo(mapInstance)
  }

  /**
   * Destroy the map instance
   */
  const destroyMap = () => {
    if (mapInstance) {
      mapInstance.remove()
      mapInstance = null
      markers.value = []
      circles.value = []
      isMapReady.value = false
    }
  }

  // Utility function
  const capitalizeFirstLetter = (string: string) => {
    if (!string) return ''
    return string.charAt(0).toUpperCase() + string.slice(1)
  }

  onUnmounted(() => {
    destroyMap()
  })

  return {
    mapInstance,
    markers,
    circles,
    isMapReady,
    initializeMap,
    addTileLayer,
    addMarker,
    addFarmMarkers,
    addFarmBoundary,
    addCoordinateLabels,
    clearMarkers,
    zoomToLocation,
    fitBounds,
    getCenter,
    getZoom,
    addLayerControl,
    destroyMap
  }
}
