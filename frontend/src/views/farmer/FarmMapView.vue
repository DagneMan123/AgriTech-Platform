<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <h1>Farm Map</h1>
        <p>Visualize and manage your farm locations and boundaries</p>
      </div>

      <div class="map-container">
        <MapControls 
          :farms="farms"
          :selectedFarmId="selectedFarmId"
          :showBoundaries="showBoundaries"
          :showCropAreas="showCropAreas"
          :showCoordinates="showCoordinates"
          :mapType="mapType"
          :loading="loading"
          @update:selectedFarmId="selectedFarmId = $event"
          @update:showBoundaries="showBoundaries = $event; updateMapDisplay()"
          @update:showCropAreas="showCropAreas = $event; updateMapDisplay()"
          @update:showCoordinates="showCoordinates = $event; updateMapDisplay()"
          @update:mapType="mapType = $event; updateMapType()"
          @zoom-to-farm="zoomToSelectedFarm"
          @view-details="showFarmDetails"
          @download-map="downloadMapImage"
          @refresh="fetchFarms"
        />

        <div class="map-section">
          <div id="farm-map" class="map"></div>
          <div v-if="loading" class="map-loading">
            <p>Loading map...</p>
          </div>
          <div v-if="error" class="map-error">
            <p>{{ error }}</p>
            <button @click="fetchFarms" class="btn btn-small btn-primary">Retry</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Farm Details Modal -->
    <div v-if="showDetailsModal" class="modal-overlay" @click="closeDetailsModal">
      <div class="modal-dialog" @click.stop>
        <div class="modal-header">
          <h2>{{ selectedFarm?.name }} - Detailed Map</h2>
          <button class="close-btn" @click="closeDetailsModal">&times;</button>
        </div>
        <div class="modal-content">
          <div id="detail-map" class="detail-map"></div>
          <div class="detail-info">
            <h3>Farm Information</h3>
            <div class="info-grid">
              <div class="info-item">
                <span class="label">Address:</span>
                <span class="value">{{ selectedFarm?.address }}</span>
              </div>
              <div class="info-item">
                <span class="label">Zone:</span>
                <span class="value">{{ selectedFarm?.zone || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <span class="label">Woreda:</span>
                <span class="value">{{ selectedFarm?.woreda || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <span class="label">Kebele:</span>
                <span class="value">{{ selectedFarm?.kebele || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <span class="label">Size:</span>
                <span class="value">{{ selectedFarm?.size_hectares }} hectares</span>
              </div>
              <div class="info-item">
                <span class="label">Farm Type:</span>
                <span class="value">{{ capitalizeFirstLetter(selectedFarm?.farm_type) }}</span>
              </div>
              <div class="info-item">
                <span class="label">Region:</span>
                <span class="value">{{ selectedFarm?.region }}</span>
              </div>
              <div class="info-item">
                <span class="label">Active Crops:</span>
                <span class="value">{{ selectedFarm?.crops_count || 0 }}</span>
              </div>
            </div>
            <div v-if="selectedFarm?.description" class="description">
              <h4>Description</h4>
              <p>{{ selectedFarm.description }}</p>
            </div>
            <div class="coordinates-info">
              <h4>GPS Coordinates</h4>
              <p v-if="selectedFarm?.latitude && selectedFarm?.longitude">
                Latitude: {{ selectedFarm.latitude.toFixed(6) }}<br>
                Longitude: {{ selectedFarm.longitude.toFixed(6) }}
              </p>
              <p v-else style="color: #999;">Not available</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useMap } from '@/composables/useMap'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import MapControls from '@/components/Map/MapControls.vue'
import apiClient from '@/api/config'

const router = useRouter()
const auth = useAuthStore()

// State
const farms = ref([])
const selectedFarmId = ref('')
const loading = ref(true)
const error = ref(null)
const showBoundaries = ref(true)
const showCropAreas = ref(true)
const showCoordinates = ref(true)
const mapType = ref('street')
const showDetailsModal = ref(false)

// Use map composable
const {
  mapInstance,
  initializeMap,
  addTileLayer,
  addFarmMarkers,
  addFarmBoundary,
  addCoordinateLabels,
  clearMarkers,
  zoomToLocation,
  destroyMap
} = useMap({
  mapContainer: 'farm-map',
  center: [9.0320, 38.7469],
  zoom: 6
})

let detailMap = null

// Computed properties
const selectedFarm = computed(() => {
  return farms.value.find(f => f.id === parseInt(selectedFarmId.value))
})

// Fetch farms on mount
onMounted(async () => {
  await fetchFarms()
})

// Fetch all farms
const fetchFarms = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await apiClient.get('/farmer/farms')
    farms.value = Array.isArray(response.data.data) ? response.data.data : []
    
    // Initialize map after farms are loaded
    setTimeout(() => {
      initializeMap()
      updateMapDisplay()
    }, 500)
  } catch (err) {
    console.error('Error fetching farms:', err)
    error.value = err.response?.data?.message || 'Failed to load farms'
    farms.value = []
  } finally {
    loading.value = false
  }
}

// Update map display based on current settings
const updateMapDisplay = () => {
  if (!mapInstance) return

  clearMarkers()

  const farmsToShow = selectedFarmId.value 
    ? farms.value.filter(f => f.id === parseInt(selectedFarmId.value))
    : farms.value

  // Add farm markers
  farmsToShow.forEach((farm) => {
    if (farm.latitude && farm.longitude) {
      const marker = L.circleMarker([farm.latitude, farm.longitude], {
        radius: selectedFarmId.value ? 12 : 8,
        fillColor: selectedFarmId.value === farm.id ? '#10b981' : '#3b82f6',
        color: selectedFarmId.value === farm.id ? '#059669' : '#1e40af',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.8
      })
      
      marker.bindPopup(`
        <div class="farm-popup">
          <strong>${farm.name}</strong><br>
          Size: ${farm.size_hectares} ha<br>
          Type: ${capitalizeFirstLetter(farm.farm_type)}
        </div>
      `)
      
      marker.addTo(mapInstance)
      
      // Add farm boundaries if enabled
      if (showBoundaries.value) {
        const radius = Math.sqrt(farm.size_hectares * 10000) / Math.PI
        L.circle([farm.latitude, farm.longitude], {
          radius: radius * 2,
          color: selectedFarmId.value === farm.id ? '#10b981' : '#3b82f6',
          weight: 1,
          opacity: 0.3,
          fillOpacity: 0.1,
          dashArray: '5, 5'
        }).addTo(mapInstance)
      }
      
      // Add coordinates if enabled
      if (showCoordinates.value) {
        L.marker([farm.latitude, farm.longitude], {
          icon: L.divIcon({
            html: `<div class="coord-label">${farm.latitude.toFixed(4)}<br>${farm.longitude.toFixed(4)}</div>`,
            className: 'coordinate-label',
            iconSize: [80, 40]
          })
        }).addTo(mapInstance)
      }
    }
  })
}

// Update map type
const updateMapType = () => {
  if (!mapInstance) return

  // Remove existing layers
  mapInstance.eachLayer((layer) => {
    if (layer instanceof L.TileLayer) {
      mapInstance.removeLayer(layer)
    }
  })

  // Add new tile layer based on mapType
  if (mapType.value === 'satellite') {
    addTileLayer('satellite')
  } else if (mapType.value === 'terrain') {
    addTileLayer('terrain')
  } else {
    addTileLayer('osm')
  }
}

// Zoom to selected farm
const zoomToSelectedFarm = () => {
  if (!selectedFarm.value || !mapInstance) return
  
  const farm = selectedFarm.value
  if (farm.latitude && farm.longitude) {
    zoomToLocation(farm.latitude, farm.longitude, 12)
  }
}

// Show farm details modal
const showFarmDetails = () => {
  showDetailsModal.value = true
  
  setTimeout(() => {
    initializeDetailMap()
  }, 300)
}

// Close details modal
const closeDetailsModal = () => {
  showDetailsModal.value = false
  if (detailMap) {
    detailMap.remove()
    detailMap = null
  }
}

// Initialize detail map
const initializeDetailMap = () => {
  if (detailMap) {
    detailMap.remove()
  }

  if (!selectedFarm.value) return

  detailMap = L.map('detail-map').setView(
    [selectedFarm.value.latitude, selectedFarm.value.longitude], 
    13
  )

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(detailMap)

  const farm = selectedFarm.value
  if (farm.latitude && farm.longitude) {
    L.circleMarker([farm.latitude, farm.longitude], {
      radius: 12,
      fillColor: '#10b981',
      color: '#059669',
      weight: 2,
      opacity: 1,
      fillOpacity: 0.8
    }).bindPopup(`<strong>${farm.name}</strong>`).addTo(detailMap)

    const radius = Math.sqrt(farm.size_hectares * 10000) / Math.PI
    L.circle([farm.latitude, farm.longitude], {
      radius: radius * 2,
      color: '#10b981',
      weight: 2,
      opacity: 0.5,
      fillOpacity: 0.1
    }).addTo(detailMap)
  }
}

// Download map image
const downloadMapImage = async () => {
  try {
    alert('Map download feature will be available soon. You can use your browser\'s screenshot tool for now.')
  } catch (err) {
    console.error('Error downloading map:', err)
    alert('Failed to download map. Please try again.')
  }
}

// Watch for selectedFarmId changes
watch(selectedFarmId, () => {
  updateMapDisplay()
})

// Utility functions
const capitalizeFirstLetter = (string) => {
  if (!string) return ''
  return string.charAt(0).toUpperCase() + string.slice(1)
}

// Logout handler
const handleLogout = async () => {
  destroyMap()
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout {
  display: flex;
  height: 100vh;
}

.farmer-page {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f5f5;
  padding: 20px;
}

.page-header {
  margin-bottom: 20px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.page-header p {
  color: #666;
}

.map-container {
  display: flex;
  gap: 20px;
  height: calc(100vh - 200px);
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.map-section {
  flex: 1;
  position: relative;
}

#farm-map {
  width: 100%;
  height: 100%;
  border-radius: 8px;
}

#detail-map {
  width: 100%;
  height: 100%;
  border-radius: 4px;
}

.map-loading,
.map-error {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  padding: 20px 30px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  text-align: center;
}

.map-error {
  background: #fee2e2;
  border: 1px solid #fecaca;
}

.map-error p {
  color: #991b1b;
  margin: 0 0 15px 0;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-dialog {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 900px;
  max-height: 85vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.modal-header h2 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: #333;
}

.modal-content {
  flex: 1;
  overflow: hidden;
  display: flex;
  gap: 20px;
  padding: 20px;
}

.detail-map {
  flex: 1;
  border-radius: 4px;
  min-height: 300px;
}

.detail-info {
  width: 280px;
  overflow-y: auto;
  padding-right: 10px;
}

.detail-info h3 {
  margin: 0 0 15px 0;
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;
  margin-bottom: 20px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-item .label {
  font-size: 12px;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-item .value {
  font-size: 14px;
  color: #333;
}

.description,
.coordinates-info {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.description h4,
.coordinates-info h4 {
  margin: 0 0 10px 0;
  font-size: 13px;
  font-weight: 600;
  color: #333;
}

.description p,
.coordinates-info p {
  margin: 0;
  font-size: 13px;
  color: #555;
  line-height: 1.5;
}

/* Leaflet overrides */
:deep(.leaflet-popup-content) {
  font-size: 12px;
  margin: 0;
}

:deep(.farm-popup) {
  margin: 0;
}

:deep(.coordinate-label) {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid #d1d5db;
  border-radius: 4px;
  padding: 4px 8px;
  font-size: 11px;
  text-align: center;
  font-weight: 600;
  color: #555;
}

/* Responsive */
@media (max-width: 1024px) {
  .map-container {
    flex-direction: column;
    height: auto;
    min-height: calc(100vh - 200px);
  }

  .map-section {
    height: 400px;
  }

  .modal-content {
    flex-direction: column;
  }

  .detail-info {
    width: 100%;
    max-height: 200px;
  }
}

@media (max-width: 768px) {
  .farmer-page {
    margin-left: 0;
    padding: 10px;
  }

  .page-header h1 {
    font-size: 20px;
  }

  .map-section {
    height: 300px;
  }

  .modal-dialog {
    width: 95%;
    max-height: 95vh;
  }

  .modal-content {
    flex-direction: column;
    padding: 15px;
  }

  .detail-info {
    width: 100%;
    max-height: 250px;
  }

  #detail-map {
    height: 250px;
  }
}
</style>
