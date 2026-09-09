<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Crop Management</h1>
          <p>Track and manage all your crops</p>
        </div>
        <button class="btn-primary btn-large" @click="openPlantCropDialog">
          <i class="fas fa-plus"></i> Plant New Crop
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon crops">
            <i class="fas fa-leaf"></i>
          </div>
          <div class="stat-content">
            <h3>Total Crops</h3>
            <p class="stat-value">{{ crops.length }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon active">
            <i class="fas fa-seedling"></i>
          </div>
          <div class="stat-content">
            <h3>Active Crops</h3>
            <p class="stat-value">{{ activeCropsCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon ready">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-content">
            <h3>Ready to Harvest</h3>
            <p class="stat-value">{{ readyToHarvestCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon area">
            <i class="fas fa-square"></i>
          </div>
          <div class="stat-content">
            <h3>Total Area</h3>
            <p class="stat-value">{{ totalArea }} ha</p>
          </div>
        </div>
      </div>

      <!-- Filters and Controls -->
      <div class="controls-section">
        <div class="filter-group">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search crops by name..."
            class="search-input"
          />
          <select v-model="statusFilter" class="status-select">
            <option value="">All Status</option>
            <option value="planning">Planning</option>
            <option value="planted">Planted</option>
            <option value="growing">Growing</option>
            <option value="ready_for_harvest">Ready for Harvest</option>
            <option value="harvested">Harvested</option>
          </select>
        </div>
      </div>

      <!-- Crops List/Table -->
      <div class="crops-section">
        <div v-if="loading" class="loading-state">
          <p><i class="fas fa-spinner fa-spin"></i> Loading crops...</p>
        </div>

        <div v-else-if="filteredCrops.length === 0" class="empty-state">
          <i class="fas fa-seedling"></i>
          <h3>No crops found</h3>
          <p>{{ crops.length === 0 ? 'Start by planting a new crop' : 'No crops match your filters' }}</p>
        </div>

        <div v-else class="crops-grid">
          <div v-for="crop in filteredCrops" :key="crop.id" class="crop-card">
            <div class="crop-header">
              <div class="crop-title">
                <h3>{{ crop.crop_type }}</h3>
                <span class="crop-variety">{{ crop.variety }}</span>
              </div>
              <span class="status-badge" :class="`status-${crop.status}`">
                {{ formatStatus(crop.status) }}
              </span>
            </div>

            <div class="crop-info">
              <div class="info-row">
                <span class="info-label">
                  <i class="fas fa-home"></i> Farm:
                </span>
                <span class="info-value">{{ crop.farm?.name || 'N/A' }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">
                  <i class="fas fa-ruler"></i> Area:
                </span>
                <span class="info-value">{{ crop.area_hectares }} hectares</span>
              </div>
              <div class="info-row">
                <span class="info-label">
                  <i class="fas fa-calendar-alt"></i> Planted:
                </span>
                <span class="info-value">{{ formatDate(crop.planting_date) }}</span>
              </div>
              <div class="info-row">
                <span class="info-label">
                  <i class="fas fa-calendar-check"></i> Expected Harvest:
                </span>
                <span class="info-value">{{ formatDate(crop.expected_harvest_date) }}</span>
              </div>
              <div v-if="crop.expected_yield_kg" class="info-row">
                <span class="info-label">
                  <i class="fas fa-weight"></i> Expected Yield:
                </span>
                <span class="info-value">{{ crop.expected_yield_kg }} kg</span>
              </div>
            </div>

            <div v-if="crop.notes" class="crop-notes">
              <p>{{ crop.notes }}</p>
            </div>

            <div class="crop-actions">
              <button class="btn-small btn-view" @click="viewCropDetails(crop)">
                <i class="fas fa-eye"></i> View
              </button>
              <button class="btn-small btn-edit" @click="editCrop(crop)">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button class="btn-small btn-delete" @click="deleteCrop(crop.id)">
                <i class="fas fa-trash"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Plant Crop Modal -->
      <div v-if="showPlantCropDialog" class="modal-overlay" @click="closePlantCropDialog">
        <div class="modal-dialog" @click.stop>
          <div class="modal-header">
            <h2>{{ editingCrop ? 'Edit Crop' : 'Plant New Crop' }}</h2>
            <button class="close-btn" @click="closePlantCropDialog">&times;</button>
          </div>

          <div class="modal-content">
            <form @submit.prevent="submitCropForm">
              <!-- Farm Selection -->
              <div class="form-group">
                <label for="crop-farm">Select Farm *</label>
                <select id="crop-farm" v-model="cropForm.farm_id" required>
                  <option value="">Choose a farm</option>
                  <option v-for="farm in farms" :key="farm.id" :value="farm.id">
                    {{ farm.name }} ({{ farm.size_hectares }} ha)
                  </option>
                </select>
                <span v-if="formErrors.farm_id" class="error-text">{{ formErrors.farm_id }}</span>
              </div>

              <!-- Crop Type -->
              <div class="form-group">
                <label for="crop-type">Crop Type *</label>
                <input
                  id="crop-type"
                  v-model="cropForm.crop_type"
                  type="text"
                  placeholder="e.g., Maize, Wheat, Tomato"
                  required
                />
                <span v-if="formErrors.crop_type" class="error-text">{{ formErrors.crop_type }}</span>
              </div>

              <!-- Variety -->
              <div class="form-group">
                <label for="crop-variety">Variety *</label>
                <input
                  id="crop-variety"
                  v-model="cropForm.variety"
                  type="text"
                  placeholder="e.g., DK777, PAN12, Roma"
                  required
                />
                <span v-if="formErrors.variety" class="error-text">{{ formErrors.variety }}</span>
              </div>

              <!-- Planting Date -->
              <div class="form-group">
                <label for="crop-planting">Planting Date *</label>
                <input
                  id="crop-planting"
                  v-model="cropForm.planting_date"
                  type="date"
                  required
                />
                <span v-if="formErrors.planting_date" class="error-text">{{ formErrors.planting_date }}</span>
              </div>

              <!-- Expected Harvest Date -->
              <div class="form-group">
                <label for="crop-harvest">Expected Harvest Date *</label>
                <input
                  id="crop-harvest"
                  v-model="cropForm.expected_harvest_date"
                  type="date"
                  required
                />
                <span v-if="formErrors.expected_harvest_date" class="error-text">{{ formErrors.expected_harvest_date }}</span>
              </div>

              <!-- Area in Hectares -->
              <div class="form-group">
                <label for="crop-area">Area (Hectares) *</label>
                <input
                  id="crop-area"
                  v-model.number="cropForm.area_hectares"
                  type="number"
                  placeholder="e.g., 2.5"
                  step="0.1"
                  min="0.1"
                  required
                />
                <span v-if="formErrors.area_hectares" class="error-text">{{ formErrors.area_hectares }}</span>
              </div>

              <!-- Expected Yield -->
              <div class="form-group">
                <label for="crop-yield">Expected Yield (kg)</label>
                <input
                  id="crop-yield"
                  v-model.number="cropForm.expected_yield_kg"
                  type="number"
                  placeholder="e.g., 5000"
                  step="0.01"
                  min="0"
                />
                <span v-if="formErrors.expected_yield_kg" class="error-text">{{ formErrors.expected_yield_kg }}</span>
              </div>

              <!-- Notes -->
              <div class="form-group">
                <label for="crop-notes">Notes</label>
                <textarea
                  id="crop-notes"
                  v-model="cropForm.notes"
                  placeholder="Additional notes about this crop"
                  rows="3"
                ></textarea>
              </div>

              <!-- Form Actions -->
              <div class="form-actions">
                <button type="button" class="btn-secondary" @click="closePlantCropDialog">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="submittingCrop">
                  {{ submittingCrop ? 'Saving...' : (editingCrop ? 'Update Crop' : 'Plant Crop') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Crop Details Modal -->
      <div v-if="showDetailsModal" class="modal-overlay" @click="closeDetailsModal">
        <div class="modal-dialog modal-large" @click.stop>
          <div class="modal-header">
            <h2>{{ selectedCrop?.crop_type }} - {{ selectedCrop?.variety }}</h2>
            <button class="close-btn" @click="closeDetailsModal">&times;</button>
          </div>

          <div class="modal-content">
            <div class="details-grid">
              <div class="detail-section">
                <h3>Basic Information</h3>
                <div class="detail-row">
                  <span class="detail-label">Crop Type:</span>
                  <span class="detail-value">{{ selectedCrop?.crop_type }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Variety:</span>
                  <span class="detail-value">{{ selectedCrop?.variety }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Status:</span>
                  <span class="detail-value">
                    <span class="status-badge" :class="`status-${selectedCrop?.status}`">
                      {{ formatStatus(selectedCrop?.status) }}
                    </span>
                  </span>
                </div>
              </div>

              <div class="detail-section">
                <h3>Location & Area</h3>
                <div class="detail-row">
                  <span class="detail-label">Farm:</span>
                  <span class="detail-value">{{ selectedCrop?.farm?.name }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Area:</span>
                  <span class="detail-value">{{ selectedCrop?.area_hectares }} hectares</span>
                </div>
              </div>

              <div class="detail-section">
                <h3>Timeline</h3>
                <div class="detail-row">
                  <span class="detail-label">Planting Date:</span>
                  <span class="detail-value">{{ formatDate(selectedCrop?.planting_date) }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Expected Harvest:</span>
                  <span class="detail-value">{{ formatDate(selectedCrop?.expected_harvest_date) }}</span>
                </div>
                <div class="detail-row">
                  <span class="detail-label">Days Until Harvest:</span>
                  <span class="detail-value">{{ daysUntilHarvest(selectedCrop?.expected_harvest_date) }} days</span>
                </div>
              </div>

              <div class="detail-section">
                <h3>Yield Information</h3>
                <div class="detail-row">
                  <span class="detail-label">Expected Yield:</span>
                  <span class="detail-value">{{ selectedCrop?.expected_yield_kg || 'Not specified' }} kg</span>
                </div>
              </div>

              <div v-if="selectedCrop?.notes" class="detail-section full-width">
                <h3>Notes</h3>
                <p class="notes-text">{{ selectedCrop?.notes }}</p>
              </div>
            </div>

            <div class="modal-actions">
              <button class="btn-secondary" @click="closeDetailsModal">Close</button>
              <button class="btn-primary" @click="editFromDetails">
                <i class="fas fa-edit"></i> Edit Crop
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()

// State
const crops = ref([])
const farms = ref([])
const loading = ref(true)
const searchQuery = ref('')
const statusFilter = ref('')
const showPlantCropDialog = ref(false)
const showDetailsModal = ref(false)
const selectedCrop = ref(null)
const editingCrop = ref(null)
const submittingCrop = ref(false)
const formErrors = ref({})

// Crop Form
const cropForm = ref({
  farm_id: '',
  crop_type: '',
  variety: '',
  planting_date: '',
  expected_harvest_date: '',
  area_hectares: '',
  expected_yield_kg: '',
  notes: '',
  status: 'growing',  // Default to 'growing' so it appears in harvest form
})

// Computed properties
const filteredCrops = computed(() => {
  return crops.value.filter(crop => {
    const matchesSearch = 
      crop.crop_type.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      crop.variety.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesStatus = !statusFilter.value || crop.status === statusFilter.value
    
    return matchesSearch && matchesStatus
  })
})

const activeCropsCount = computed(() => {
  return crops.value.filter(c => 
    c.status === 'planted' || c.status === 'growing'
  ).length
})

const readyToHarvestCount = computed(() => {
  return crops.value.filter(c => c.status === 'ready_for_harvest').length
})

const totalArea = computed(() => {
  const total = crops.value.reduce((sum, crop) => {
    const area = parseFloat(crop.area_hectares) || 0
    return sum + area
  }, 0)
  return total.toFixed(1)
})

// Lifecycle
onMounted(async () => {
  await fetchCrops()
  await fetchFarms()
})

// API Functions
const fetchCrops = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/farmer/crops', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    
    if (!response.ok) throw new Error('Failed to fetch crops')
    
    const data = await response.json()
    crops.value = data.data?.data || data.data || []
  } catch (error) {
    console.error('Error fetching crops:', error)
  } finally {
    loading.value = false
  }
}

const fetchFarms = async () => {
  try {
    const response = await fetch('/api/farmer/farms', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    
    if (!response.ok) throw new Error('Failed to fetch farms')
    
    const data = await response.json()
    farms.value = data.data?.data || data.data || []
  } catch (error) {
    console.error('Error fetching farms:', error)
  }
}

// Modal Functions
const openPlantCropDialog = () => {
  resetCropForm()
  editingCrop.value = null
  showPlantCropDialog.value = true
}

const closePlantCropDialog = () => {
  showPlantCropDialog.value = false
  resetCropForm()
}

const resetCropForm = () => {
  cropForm.value = {
    farm_id: '',
    crop_type: '',
    variety: '',
    planting_date: '',
    expected_harvest_date: '',
    area_hectares: '',
    expected_yield_kg: '',
    notes: '',
    status: 'growing',  // Default to 'growing'
  }
  formErrors.value = {}
}

const editCrop = (crop) => {
  editingCrop.value = crop
  cropForm.value = {
    farm_id: crop.farm_id,
    crop_type: crop.crop_type,
    variety: crop.variety,
    planting_date: crop.planting_date,
    expected_harvest_date: crop.expected_harvest_date,
    area_hectares: crop.area_hectares,
    expected_yield_kg: crop.expected_yield_kg || '',
    notes: crop.notes || '',
    status: crop.status || 'growing',  // Include status when editing
  }
  showPlantCropDialog.value = true
}

const viewCropDetails = (crop) => {
  selectedCrop.value = crop
  showDetailsModal.value = true
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedCrop.value = null
}

const editFromDetails = () => {
  closeDetailsModal()
  editCrop(selectedCrop.value)
}

// Form Submission
const submitCropForm = async () => {
  try {
    submittingCrop.value = true
    formErrors.value = {}

    // Validate dates
    if (cropForm.value.planting_date && cropForm.value.expected_harvest_date) {
      const plantingDate = new Date(cropForm.value.planting_date)
      const harvestDate = new Date(cropForm.value.expected_harvest_date)
      if (harvestDate <= plantingDate) {
        formErrors.value.expected_harvest_date = 'Harvest date must be after planting date'
        submittingCrop.value = false
        return
      }
    }

    const payload = {
      ...cropForm.value,
      farm_id: parseInt(cropForm.value.farm_id),
      area_hectares: parseFloat(cropForm.value.area_hectares),
      expected_yield_kg: cropForm.value.expected_yield_kg ? parseFloat(cropForm.value.expected_yield_kg) : null,
    }

    const url = editingCrop.value 
      ? `/api/farmer/crops/${editingCrop.value.id}`
      : '/api/farmer/crops'
    
    const method = editingCrop.value ? 'PUT' : 'POST'

    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        formErrors.value = data.errors
      } else {
        formErrors.value = { general: data.message || 'An error occurred' }
      }
      return
    }

    showPlantCropDialog.value = false
    resetCropForm()
    await fetchCrops()
  } catch (error) {
    console.error('Error submitting crop form:', error)
    formErrors.value = { general: 'An error occurred while saving the crop' }
  } finally {
    submittingCrop.value = false
  }
}

const deleteCrop = async (cropId) => {
  if (!confirm('Are you sure you want to delete this crop?')) return

  try {
    const response = await fetch(`/api/farmer/crops/${cropId}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })

    if (!response.ok) throw new Error('Failed to delete crop')

    await fetchCrops()
  } catch (error) {
    console.error('Error deleting crop:', error)
    alert('Failed to delete crop')
  }
}

// Utility Functions
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}

const formatStatus = (status) => {
  const statuses = {
    planning: 'Planning',
    planted: 'Planted',
    growing: 'Growing',
    ready_for_harvest: 'Ready for Harvest',
    harvested: 'Harvested'
  }
  return statuses[status] || status
}

const daysUntilHarvest = (date) => {
  if (!date) return 'N/A'
  const today = new Date()
  const harvest = new Date(date)
  const diff = Math.ceil((harvest - today) / (1000 * 60 * 60 * 24))
  return diff > 0 ? diff : 0
}

// Logout
const handleLogout = async () => {
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

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  gap: 20px;
}

.header-content h1 {
  font-size: 32px;
  font-weight: bold;
  color: #333;
  margin: 0 0 5px 0;
}

.header-content p {
  color: #666;
  margin: 0;
}

.btn-large {
  padding: 12px 24px;
  font-size: 16px;
  white-space: nowrap;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  gap: 15px;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.stat-icon.crops {
  background-color: #10b981;
}

.stat-icon.active {
  background-color: #3b82f6;
}

.stat-icon.ready {
  background-color: #f59e0b;
}

.stat-icon.area {
  background-color: #8b5cf6;
}

.stat-content h3 {
  margin: 0;
  font-size: 12px;
  color: #666;
  text-transform: uppercase;
  font-weight: 600;
}

.stat-value {
  margin: 5px 0 0 0;
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

/* Controls */
.controls-section {
  background: white;
  border-radius: 8px;
  padding: 15px 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-group {
  display: flex;
  gap: 15px;
  align-items: center;
}

.search-input,
.status-select {
  padding: 10px 15px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.search-input:focus,
.status-select:focus {
  outline: none;
  border-color: #10b981;
}

.search-input {
  flex: 1;
  min-width: 250px;
}

/* Crops Section */
.crops-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.empty-state i {
  font-size: 48px;
  color: #d1d5db;
  margin-bottom: 15px;
  display: block;
}

.empty-state h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

/* Crops Grid */
.crops-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.crop-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  background: #f9fafb;
  transition: all 0.3s;
}

.crop-card:hover {
  border-color: #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.crop-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
  gap: 10px;
}

.crop-title h3 {
  margin: 0;
  font-size: 18px;
  color: #333;
}

.crop-variety {
  display: block;
  font-size: 12px;
  color: #666;
  margin-top: 2px;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  white-space: nowrap;
}

.status-badge.status-planning {
  background-color: #e0e7ff;
  color: #3730a3;
}

.status-badge.status-planted {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-badge.status-growing {
  background-color: #dcfce7;
  color: #166534;
}

.status-badge.status-ready_for_harvest {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.status-harvested {
  background-color: #dbeafe;
  color: #1e40af;
}

.crop-info {
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e5e7eb;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  font-size: 13px;
}

.info-label {
  color: #666;
  font-weight: 500;
}

.info-label i {
  margin-right: 6px;
  color: #10b981;
}

.info-value {
  color: #333;
  font-weight: 600;
}

.crop-notes {
  background: white;
  padding: 12px;
  border-radius: 4px;
  margin-bottom: 15px;
  font-size: 13px;
  color: #555;
  font-style: italic;
}

.crop-notes p {
  margin: 0;
}

.crop-actions {
  display: flex;
  gap: 8px;
}

.btn-small {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}

.btn-view {
  background-color: #8b5cf6;
  color: white;
}

.btn-view:hover {
  background-color: #7c3aed;
}

.btn-edit {
  background-color: #3b82f6;
  color: white;
}

.btn-edit:hover {
  background-color: #2563eb;
}

.btn-delete {
  background-color: #ef4444;
  color: white;
}

.btn-delete:hover {
  background-color: #dc2626;
}

/* Buttons */
.btn-primary {
  background-color: #10b981;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover:not(:disabled) {
  background-color: #059669;
}

.btn-primary:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background-color: #4b5563;
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
  z-index: 1000;
}

.modal-dialog {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-dialog.modal-large {
  max-width: 800px;
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
  font-size: 20px;
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
  padding: 25px;
}

/* Form */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-group textarea {
  resize: vertical;
}

.error-text {
  display: block;
  color: #ef4444;
  font-size: 12px;
  margin-top: 5px;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 25px;
  padding-top: 15px;
  border-top: 1px solid #e5e7eb;
}

/* Details Modal */
.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 25px;
}

.detail-section {
  background: #f9fafb;
  padding: 15px;
  border-radius: 6px;
}

.detail-section.full-width {
  grid-column: 1 / -1;
}

.detail-section h3 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
  text-transform: uppercase;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  color: #666;
  font-weight: 500;
  font-size: 13px;
}

.detail-value {
  color: #333;
  font-weight: 600;
  text-align: right;
}

.notes-text {
  margin: 0;
  color: #555;
  line-height: 1.6;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  padding-top: 15px;
  border-top: 1px solid #e5e7eb;
}

/* Responsive */
@media (max-width: 768px) {
  .farmer-page {
    margin-left: 0;
    padding: 15px;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-large {
    width: 100%;
    text-align: center;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }

  .filter-group {
    flex-direction: column;
  }

  .search-input {
    width: 100%;
  }

  .crops-grid {
    grid-template-columns: 1fr;
  }

  .modal-dialog {
    width: 95%;
    max-height: 95vh;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }
}
</style>
