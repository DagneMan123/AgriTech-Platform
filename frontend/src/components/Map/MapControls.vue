<template>
  <div class="map-controls">
    <div class="controls-section">
      <h3>Farm Selection</h3>
      <select v-model="selectedFarmId" class="form-select" @change="handleFarmChange">
        <option value="">-- Select a farm to view --</option>
        <option v-for="farm in farms" :key="farm.id" :value="farm.id">
          {{ farm.name }} ({{ farm.size_hectares }} ha)
        </option>
      </select>
    </div>

    <div class="controls-section">
      <h3>Map Options</h3>
      <div class="checkbox-group">
        <label>
          <input v-model="showBoundaries" type="checkbox" @change="handleOptionChange" />
          Show Farm Boundaries
        </label>
        <label>
          <input v-model="showCropAreas" type="checkbox" @change="handleOptionChange" />
          Show Crop Areas
        </label>
        <label>
          <input v-model="showCoordinates" type="checkbox" @change="handleOptionChange" />
          Show Coordinates
        </label>
      </div>
    </div>

    <div class="controls-section">
      <h3>Map Type</h3>
      <div class="radio-group">
        <label>
          <input v-model="mapType" type="radio" value="satellite" @change="handleMapTypeChange" />
          Satellite
        </label>
        <label>
          <input v-model="mapType" type="radio" value="terrain" @change="handleMapTypeChange" />
          Terrain
        </label>
        <label>
          <input v-model="mapType" type="radio" value="street" @change="handleMapTypeChange" />
          Street
        </label>
      </div>
    </div>

    <div class="controls-section">
      <h3>Actions</h3>
      <button 
        class="btn btn-primary"
        @click="handleZoomToFarm"
        :disabled="!selectedFarmId"
      >
        Zoom to Farm
      </button>
      <button 
        class="btn btn-secondary"
        @click="handleViewDetails"
        :disabled="!selectedFarmId"
      >
        View Details
      </button>
      <button 
        class="btn btn-info"
        @click="handleDownloadMap"
        :disabled="props.downloadingMap"
      >
        {{ props.downloadingMap ? 'Downloading...' : 'Download Map' }}
      </button>
      <button 
        class="btn btn-secondary"
        @click="handleRefresh"
      >
        Refresh
      </button>
    </div>

    <div class="info-section">
      <h3>Selected Farm Info</h3>
      <div v-if="selectedFarm" class="farm-info">
        <p><strong>Name:</strong> {{ selectedFarm.name }}</p>
        <p><strong>Size:</strong> {{ selectedFarm.size_hectares }} hectares</p>
        <p><strong>Type:</strong> {{ capitalizeFirstLetter(selectedFarm.farm_type) }}</p>
        <p><strong>Region:</strong> {{ selectedFarm.region }}</p>
        <p v-if="selectedFarm.latitude && selectedFarm.longitude">
          <strong>Coordinates:</strong><br>
          Lat: {{ selectedFarm.latitude.toFixed(6) }}<br>
          Lng: {{ selectedFarm.longitude.toFixed(6) }}
        </p>
        <p><strong>Address:</strong> {{ selectedFarm.address }}</p>
        <p v-if="selectedFarm.woreda"><strong>Woreda:</strong> {{ selectedFarm.woreda }}</p>
        <p><strong>Crops:</strong> {{ selectedFarm.crops_count || 0 }}</p>
      </div>
      <div v-else class="empty-info">
        <p>Select a farm to view details</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Farm {
  id: number | string
  name: string
  size_hectares: number
  farm_type: string
  region: string
  address: string
  zone?: string
  woreda?: string
  kebele?: string
  latitude?: number
  longitude?: number
  crops_count?: number
  description?: string
}

interface Props {
  farms: Farm[]
  selectedFarmId?: number | string
  showBoundaries?: boolean
  showCropAreas?: boolean
  showCoordinates?: boolean
  mapType?: string
  loading?: boolean
  downloadingMap?: boolean
}

interface Emits {
  (e: 'update:selectedFarmId', value: number | string): void
  (e: 'update:showBoundaries', value: boolean): void
  (e: 'update:showCropAreas', value: boolean): void
  (e: 'update:showCoordinates', value: boolean): void
  (e: 'update:mapType', value: string): void
  (e: 'zoom-to-farm'): void
  (e: 'view-details'): void
  (e: 'download-map'): void
  (e: 'refresh'): void
}

const props = withDefaults(defineProps<Props>(), {
  farms: () => [],
  selectedFarmId: '',
  showBoundaries: true,
  showCropAreas: true,
  showCoordinates: true,
  mapType: 'street',
  loading: false,
  downloadingMap: false
})

const emit = defineEmits<Emits>()

const selectedFarmId = computed({
  get: () => props.selectedFarmId,
  set: (value) => emit('update:selectedFarmId', value)
})

const showBoundaries = computed({
  get: () => props.showBoundaries,
  set: (value) => emit('update:showBoundaries', value)
})

const showCropAreas = computed({
  get: () => props.showCropAreas,
  set: (value) => emit('update:showCropAreas', value)
})

const showCoordinates = computed({
  get: () => props.showCoordinates,
  set: (value) => emit('update:showCoordinates', value)
})

const mapType = computed({
  get: () => props.mapType,
  set: (value) => emit('update:mapType', value)
})

const selectedFarm = computed(() => {
  return props.farms.find(f => f.id === props.selectedFarmId)
})

const capitalizeFirstLetter = (string: string) => {
  if (!string) return ''
  return string.charAt(0).toUpperCase() + string.slice(1)
}

const handleFarmChange = () => {
  emit('update:selectedFarmId', selectedFarmId.value)
}

const handleOptionChange = () => {
  emit('update:showBoundaries', showBoundaries.value)
  emit('update:showCropAreas', showCropAreas.value)
  emit('update:showCoordinates', showCoordinates.value)
}

const handleMapTypeChange = () => {
  emit('update:mapType', mapType.value)
}

const handleZoomToFarm = () => {
  emit('zoom-to-farm')
}

const handleViewDetails = () => {
  emit('view-details')
}

const handleDownloadMap = () => {
  emit('download-map')
}

const handleRefresh = () => {
  emit('refresh')
}
</script>

<style scoped>
.map-controls {
  width: 320px;
  background: white;
  border-right: 1px solid #e5e7eb;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.controls-section {
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 15px;
}

.controls-section:last-child {
  border-bottom: none;
}

.controls-section h3 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-select {
  width: 100%;
  padding: 10px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
  transition: border-color 0.3s;
}

.form-select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.checkbox-group,
.radio-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.checkbox-group label,
.radio-group label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 14px;
  color: #555;
  transition: color 0.2s;
}

.checkbox-group label:hover,
.radio-group label:hover {
  color: #333;
}

.checkbox-group input,
.radio-group input {
  cursor: pointer;
  width: 16px;
  height: 16px;
}

.btn {
  width: 100%;
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.3s;
  margin-bottom: 8px;
}

.btn:last-of-type {
  margin-bottom: 0;
}

.btn-primary {
  background-color: #10b981;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #059669;
}

.btn-primary:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #4b5563;
}

.btn-secondary:disabled {
  background-color: #d1d5db;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-info {
  background-color: #3b82f6;
  color: white;
}

.btn-info:hover {
  background-color: #2563eb;
}

.info-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.farm-info,
.empty-info {
  font-size: 12px;
  line-height: 1.6;
}

.farm-info p {
  margin: 6px 0;
  color: #555;
}

.farm-info strong {
  color: #333;
  display: inline-block;
  min-width: 70px;
}

.empty-info {
  color: #999;
  text-align: center;
  padding: 20px 10px;
  font-style: italic;
}

@media (max-width: 1024px) {
  .map-controls {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
    max-height: 300px;
  }
}
</style>
