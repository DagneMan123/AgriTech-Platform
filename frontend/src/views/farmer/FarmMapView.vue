<template>
  <div class="farmer-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <div class="header-content">
          <h1>Farm Map</h1>
          <p>Visualize and manage your farm locations and boundaries</p>
        </div>
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

    <!-- Farm Details Modal (Professional & Modern Layout) -->
    <div v-if="showDetailsModal" class="modal-overlay" @click="closeDetailsModal">
      <div class="modal-dialog" @click.stop>
        <div class="modal-header">
          <h2>{{ selectedFarm?.name }} - Detailed Map</h2>
          <button class="close-btn" @click="closeDetailsModal">&times;</button>
        </div>
        <div class="modal-content">
          <div id="detail-map" class="detail-map"></div>
          
          <div class="detail-info">
            <h3 class="section-title">Farm Information</h3>
            
            <div class="info-cards">
              <div class="info-card">
                <span class="label">Region</span>
                <span class="value">{{ selectedFarm?.region || 'N/A' }}</span>
              </div>
              <div class="info-card">
                <span class="label">Zone</span>
                <span class="value">{{ selectedFarm?.zone || 'N/A' }}</span>
              </div>
              <div class="info-card">
                <span class="label">Woreda</span>
                <span class="value">{{ selectedFarm?.woreda || 'N/A' }}</span>
              </div>
              <div class="info-card">
                <span class="label">Kebele</span>
                <span class="value">{{ selectedFarm?.kebele || 'N/A' }}</span>
              </div>
              <div class="info-card">
                <span class="label">Address</span>
                <span class="value">{{ selectedFarm?.address || 'N/A' }}</span>
              </div>
              <div class="info-card">
                <span class="label">Size</span>
                <span class="value badge-size">{{ selectedFarm?.size_hectares }} ha</span>
              </div>
              <div class="info-card">
                <span class="label">Farm Type</span>
                <span class="value badge-type">{{ capitalizeFirstLetter(selectedFarm?.farm_type) }}</span>
              </div>
              <div class="info-card">
                <span class="label">Active Crops</span>
                <span class="value">{{ selectedFarm?.crops_count || 0 }}</span>
              </div>
            </div>

            <div v-if="selectedFarm?.description" class="extra-block">
              <h4 class="sub-title">Description</h4>
              <p class="desc-text">{{ selectedFarm.description }}</p>
            </div>

            <div class="extra-block">
              <h4 class="sub-title">GPS Coordinates</h4>
              <p v-if="selectedFarm?.latitude && selectedFarm?.longitude" class="coords-text">
                <span><strong>Lat:</strong> {{ selectedFarm.latitude.toFixed(6) }}</span>
                <span><strong>Lng:</strong> {{ selectedFarm.longitude.toFixed(6) }}</span>
              </p>
              <p v-else class="no-coords">Not available</p>
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
import { useTheme } from '@/composables/useTheme'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import MapControls from '@/components/Map/MapControls.vue'
import apiClient from '@/api/config'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const router = useRouter()
const auth = useAuthStore()
const { isDark, isLight } = useTheme()

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

    await fetchFarmsWithRetry()

    if (!mapInstance.value) {
      initializeMainMap()
    }
    updateMapDisplay()
  } catch (err: any) {
    console.error('Error fetching farms:', err)
    
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
  const baseDelay = 1000
  
  while (retryCount.value <= maxRetries) {
    try {
      const response = await apiClient.get('/farmer/farms', {
        timeout: 30000,
      })
      farms.value = Array.isArray(response.data.data) ? response.data.data : []
      return
    } catch (err: any) {
      retryCount.value++
      
      const isTimeout = err.code === 'ECONNABORTED' || err.message === 'timeout of 30000ms exceeded'
      const isNetworkError = !err.response && err.code !== 'ECONNABORTED'
      const isRetryableStatus = [408, 429, 500, 502, 503, 504].includes(err.response?.status)
      
      const shouldRetry = (isTimeout || isNetworkError || isRetryableStatus) && retryCount.value <= maxRetries
      
      if (shouldRetry) {
        const delay = baseDelay * Math.pow(2, retryCount.value - 1)
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
/* Page & Layout Styles */
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
  background: white;
  padding: 30px;
  margin: -20px -20px 30px -20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.header-content {
  flex: 1;
}

.page-header h1 {
  font-size: 28px;
  font-weight: 800;
  margin: 0 0 8px 0;
  transition: color 0.3s ease;
}

.page-header p {
  margin: 0;
  font-size: 14px;
  transition: color 0.3s ease;
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

/* =========================================================
   Modal Base & Professional Layout
   ========================================================= */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-dialog {
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  width: 90%;
  max-width: 950px;
  max-height: 85vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f8fafc;
}

.modal-header h2 {
  margin: 0;
  color: #1e293b;
  font-size: 18px;
  font-weight: 700;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #64748b;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.close-btn:hover {
  background-color: #e2e8f0;
  color: #0f172a;
}

.modal-content {
  flex: 1;
  overflow: hidden;
  display: flex;
  gap: 20px;
  padding: 20px;
}

/* Detail Map Container */
.detail-map {
  flex: 1.4;
  border-radius: 8px;
  min-height: 380px;
  overflow: hidden;
  box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.05);
}

/* Detail Info Panel (Card-based Layout) */
.detail-info {
  flex: 1;
  min-width: 300px;
  overflow-y: auto;
  padding: 4px 12px 10px 4px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Scrollbar ማስወገጃ */
.detail-info::-webkit-scrollbar {
  display: none;
}
.detail-info {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.section-title {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: -0.2px;
}

/* 2-Column Card Grid System */
.info-cards {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.info-card {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.info-card:hover {
  border-color: #cbd5e1;
}

.info-card .label {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-card .value {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
  word-break: break-word;
}

.badge-size {
  color: #0284c7 !important;
}

.badge-type {
  color: #16a34a !important;
}

/* Description & Coordinates Extra Blocks */
.extra-block {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.sub-title {
  margin: 0;
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.desc-text {
  margin: 0;
  font-size: 13px;
  color: #334155;
  line-height: 1.5;
}

.coords-text {
  margin: 0;
  font-size: 12px;
  color: #334155;
  display: flex;
  gap: 12px;
}

.coords-text strong {
  color: #0f172a;
}

.no-coords {
  margin: 0;
  font-size: 12px;
  color: #94a3b8;
  font-style: italic;
}

/* =========================================================
   Dark Mode Overrides
   ========================================================= */
.farmer-layout.dark {
  background-color: #0b0f17;
  color: #f3f4f6;
}

.farmer-layout.dark .farmer-page {
  background-color: #0b0f17;
  color: #f3f4f6;
}

.farmer-layout.dark .page-header {
  background: #131b2e;
  border-bottom-color: #1e293b;
}

.farmer-layout.dark .page-header h1 {
  color: #f1f5f9;
}

.farmer-layout.dark .page-header p {
  color: #cbd5e1;
}

.farmer-layout.dark .map-container {
  background: #131b2e;
  border: 1px solid #1e293b;
}

.farmer-layout.dark .modal-dialog {
  background-color: #111827;
  border: 1px solid #1f2937;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7);
}

.farmer-layout.dark .modal-header {
  background-color: #1f2937;
  border-bottom-color: #374151;
}

.farmer-layout.dark .modal-header h2 {
  color: #f9fafb;
}

.farmer-layout.dark .close-btn {
  color: #9ca3af;
}

.farmer-layout.dark .close-btn:hover {
  background-color: #374151;
  color: #ffffff;
}

.farmer-layout.dark .section-title {
  color: #f9fafb;
}

.farmer-layout.dark .info-card,
.farmer-layout.dark .extra-block {
  background-color: #1f2937;
  border-color: #374151;
}

.farmer-layout.dark .info-card:hover {
  border-color: #4b5563;
}

.farmer-layout.dark .info-card .label {
  color: #9ca3af;
}

.farmer-layout.dark .info-card .value {
  color: #f3f4f6;
}

.farmer-layout.dark .sub-title {
  color: #9ca3af;
}

.farmer-layout.dark .desc-text,
.farmer-layout.dark .coords-text {
  color: #e5e7eb;
}

.farmer-layout.dark .coords-text strong {
  color: #f9fafb;
}

/* Responsive Handling */
@media (max-width: 1024px) {
  .map-container {
    flex-direction: column;
    height: auto;
    min-height: calc(100vh - 200px);
  }

  .map-section {
    height: 400px;
  }
}

@media (max-width: 768px) {
  .modal-content {
    flex-direction: column;
    overflow-y: auto;
  }
  .detail-map {
    min-height: 250px;
  }
  .info-cards {
    grid-template-columns: 1fr;
  }
}
</style>