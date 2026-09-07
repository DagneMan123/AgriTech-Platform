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
          :downloadingMap="downloadingMap"
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
            <p v-if="retryCount > 0" class="retry-info">Retry attempt {{ retryCount }}/{{ maxRetries }}</p>
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

<script setup lang="ts">
import { ref, computed, onMounted, watch, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import MapControls from '@/components/Map/MapControls.vue'
import apiClient from '@/api/config'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const router = useRouter()
const auth = useAuthStore()

const farms = ref<any[]>([])
const selectedFarmId = ref<string | number>('')
const loading = ref(true)
const error = ref<string | null>(null)
const showBoundaries = ref(true)
const showCropAreas = ref(true)
const showCoordinates = ref(true)
const mapType = ref('street')
const showDetailsModal = ref(false)
const downloadingMap = ref(false)
const retryCount = ref(0)
const maxRetries = 3

// Use 'any' type to prevent TypeScript validation errors on mapInstance methods
const mapInstance = ref<any>(null)
let detailMap: any = null

const selectedFarm = computed(() => {
  return farms.value.find(f => f.id === parseInt(String(selectedFarmId.value)))
})

onMounted(async () => {
  await nextTick()
  await fetchFarms()
})

onUnmounted(() => {
  if (mapInstance.value) {
    mapInstance.value.remove()
    mapInstance.value = null
  }
  if (detailMap) {
    detailMap.remove()
    detailMap = null
  }
})

const fetchFarms = async () => {
  try {
    loading.value = true
    error.value = null
    retryCount.value = 0

    // Attempt to fetch with retry logic
    await fetchFarmsWithRetry()

    if (!mapInstance.value) {
      initializeMainMap()
    }
    updateMapDisplay()
  } catch (err: any) {
    console.error('Error fetching farms:', err)
    
    // Provide specific error messages
    if (err.code === 'ECONNABORTED') {
      error.value = 'Request timed out. The server is taking too long to respond. Please check your connection and try again.'
    } else if (err.response?.status === 401) {
      error.value = 'Your session has expired. Please login again.'
    } else if (err.response?.status === 403) {
      error.value = 'You do not have permission to view farms.'
    } else if (err.response?.status === 500) {
      error.value = 'Server error. Please try again later.'
    } else if (!err.response) {
      error.value = 'Network error. Please check your connection and try again.'
    } else {
      error.value = err.response?.data?.message || 'Failed to load farms. Please try again.'
    }
    farms.value = []
  } finally {
    loading.value = false
  }
}

const fetchFarmsWithRetry = async (): Promise<void> => {
  const baseDelay = 1000 // Start with 1 second
  
  while (retryCount.value <= maxRetries) {
    try {
      const response = await apiClient.get('/farmer/farms', {
        // Specific timeout for this request
        timeout: 30000,
      })
      farms.value = Array.isArray(response.data.data) ? response.data.data : []
      return
    } catch (err: any) {
      retryCount.value++
      
      // Check if error is retryable
      const isTimeout = err.code === 'ECONNABORTED' || err.message === 'timeout of 30000ms exceeded'
      const isNetworkError = !err.response && err.code !== 'ECONNABORTED'
      const isRetryableStatus = [408, 429, 500, 502, 503, 504].includes(err.response?.status)
      
      const shouldRetry = (isTimeout || isNetworkError || isRetryableStatus) && retryCount.value <= maxRetries
      
      if (shouldRetry) {
        const delay = baseDelay * Math.pow(2, retryCount.value - 1) // Exponential backoff
        console.log(`Retry attempt ${retryCount.value}/${maxRetries} after ${delay}ms delay`)
        await new Promise(resolve => setTimeout(resolve, delay))
      } else {
        throw err
      }
    }
  }
}

const initializeMainMap = () => {
  try {
    const container = document.getElementById('farm-map')
    if (!container) {
      error.value = 'Map container not found'
      return
    }

    mapInstance.value = L.map('farm-map').setView([9.0320, 38.7469], 6)
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19,
    }).addTo(mapInstance.value)
  } catch (err) {
    console.error('Error initializing map:', err)
    error.value = 'Failed to initialize map'
  }
}

const updateMapDisplay = () => {
  if (!mapInstance.value) return

  mapInstance.value.eachLayer((layer: any) => {
    if (layer instanceof L.CircleMarker || layer instanceof L.Circle || 
        (layer instanceof L.Marker && !(layer instanceof L.TileLayer))) {
      mapInstance.value.removeLayer(layer)
    }
  })

  const farmsToShow = selectedFarmId.value 
    ? farms.value.filter(f => f.id === parseInt(String(selectedFarmId.value)))
    : farms.value

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
      
      marker.addTo(mapInstance.value)
      
      if (showBoundaries.value) {
        const radius = Math.sqrt(farm.size_hectares * 10000) / Math.PI
        L.circle([farm.latitude, farm.longitude], {
          radius: radius * 2,
          color: selectedFarmId.value === farm.id ? '#10b981' : '#3b82f6',
          weight: 1,
          opacity: 0.3,
          fillOpacity: 0.1,
          dashArray: '5, 5'
        }).addTo(mapInstance.value)
      }
      
      if (showCoordinates.value) {
        L.marker([farm.latitude, farm.longitude], {
          icon: L.divIcon({
            html: `<div class="coord-label">${farm.latitude.toFixed(4)}<br>${farm.longitude.toFixed(4)}</div>`,
            className: 'coordinate-label',
            iconSize: [80, 40]
          })
        }).addTo(mapInstance.value)
      }
    }
  })
}

const updateMapType = () => {
  if (!mapInstance.value) return

  mapInstance.value.eachLayer((layer: any) => {
    if (layer instanceof L.TileLayer) {
      mapInstance.value.removeLayer(layer)
    }
  })

  let tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
  let attribution = '&copy; OpenStreetMap contributors'

  if (mapType.value === 'satellite') {
    tileUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
    attribution = 'Tiles &copy; Esri'
  } else if (mapType.value === 'terrain') {
    tileUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}'
    attribution = 'Tiles &copy; Esri'
  }

  L.tileLayer(tileUrl, {
    attribution,
    maxZoom: 19,
  }).addTo(mapInstance.value)
}

const zoomToSelectedFarm = () => {
  if (!selectedFarm.value) {
    alert('Please select a farm first.')
    return
  }
  
  const farm = selectedFarm.value
  if (!farm.latitude || !farm.longitude) {
    alert('This farm does not have GPS coordinates.')
    return
  }
  
  if (!mapInstance.value) {
    try {
      initializeMainMap()
      updateMapDisplay()
    } catch (err) {
      console.error('Error initializing map:', err)
      alert('Unable to initialize map. Please refresh the page and try again.')
      return
    }
  }

  if (mapInstance.value) {
    mapInstance.value.setView([farm.latitude, farm.longitude], 14)
  } else {
    alert('Map is not ready. Please try again.')
  }
}

const showFarmDetails = () => {
  if (!selectedFarm.value) {
    alert('Please select a farm first.')
    return
  }
  showDetailsModal.value = true
  
  setTimeout(() => {
    try {
      initializeDetailMap()
    } catch (err) {
      console.error('Error initializing detail map:', err)
      alert('Could not load detail map. Please try again.')
    }
  }, 100)
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  if (detailMap) {
    detailMap.remove()
    detailMap = null
  }
}

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

const downloadMapImage = () => {
  try {
    if (!mapInstance.value) {
      alert('Map is still loading. Please wait a moment and try again.')
      return
    }
    
    downloadingMap.value = true
    setTimeout(() => {
      alert('Map download feature:\n\n1. Right-click on the map and select "Save image as"\n2. Or use your browser screenshot tool (Print Screen or Cmd+Shift+4)\n3. Or press Ctrl+P to print and save as PDF')
      downloadingMap.value = false
    }, 100)
  } catch (err) {
    console.error('Error downloading map:', err)
    alert('Please use your browser\'s screenshot tool to capture the map.')
    downloadingMap.value = false
  }
}

watch(selectedFarmId, () => {
  updateMapDisplay()
})

const capitalizeFirstLetter = (string: string) => {
  if (!string) return ''
  return string.charAt(0).toUpperCase() + string.slice(1)
}

const handleLogout = async () => {
  if (mapInstance.value) {
    mapInstance.value.remove()
    mapInstance.value = null
  }
  if (detailMap) {
    detailMap.remove()
    detailMap = null
  }
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

.retry-info {
  font-size: 12px;
  color: #666;
  margin-top: 10px;
}

.map-error {
  background: #fee2e2;
  border: 1px solid #fecaca;
}

.map-error p {
  color: #991b1b;
  margin: 0 0 15px 0;
}

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