<template>
  <div class="farmer-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Harvest Management</h1>
          <p>Record and track all your harvests</p>
        </div>
        <button class="btn-primary btn-large" @click="openRecordHarvestDialog">
          <i class="fas fa-plus"></i> Record Harvest
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon harvests">
            <i class="fas fa-wheat-awn"></i>
          </div>
          <div class="stat-content">
            <h3>Total Harvests</h3>
            <p class="stat-value">{{ harvests.length }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon quantity">
            <i class="fas fa-balance-scale"></i>
          </div>
          <div class="stat-content">
            <h3>Total Quantity</h3>
            <p class="stat-value">{{ totalQuantity }} kg</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon excellent">
            <i class="fas fa-star"></i>
          </div>
          <div class="stat-content">
            <h3>Excellent Grade</h3>
            <p class="stat-value">{{ excellentCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon average">
            <i class="fas fa-chart-pie"></i>
          </div>
          <div class="stat-content">
            <h3>Avg Yield</h3>
            <p class="stat-value">{{ averageYield }} kg</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="controls-section">
        <div class="filter-group">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by crop name..."
            class="search-input"
          />
          <select v-model="qualityFilter" class="quality-select">
            <option value="">All Quality Grades</option>
            <option value="excellent">Excellent</option>
            <option value="good">Good</option>
            <option value="fair">Fair</option>
            <option value="poor">Poor</option>
          </select>
          <select v-model="yearFilter" class="year-select">
            <option value="">All Years</option>
            <option v-for="year in availableYears" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>
      </div>

      <!-- Harvests List -->
      <div class="harvests-section">
        <div v-if="loading" class="loading-state">
          <p><i class="fas fa-spinner fa-spin"></i> Loading harvests...</p>
        </div>

        <div v-else-if="filteredHarvests.length === 0" class="empty-state">
          <i class="fas fa-wheat-awn"></i>
          <h3>No harvests found</h3>
          <p>{{ harvests.length === 0 ? 'Start by recording your first harvest' : 'No harvests match your filters' }}</p>
        </div>

        <div v-else class="harvests-table-wrapper">
          <table class="harvests-table">
            <thead>
              <tr>
                <th>Crop</th>
                <th>Farm</th>
                <th>Harvest Date</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Quality Grade</th>
                <th>Notes</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="harvest in filteredHarvests" :key="harvest.id">
                <td class="crop-name">
                  <strong>{{ harvest.crop?.crop_type }}</strong>
                  <br>
                  <small>{{ harvest.crop?.variety }}</small>
                </td>
                <td>{{ harvest.crop?.farm?.name || 'N/A' }}</td>
                <td>{{ formatDate(harvest.harvest_date) }}</td>
                <td class="quantity-value">{{ harvest.quantity }}</td>
                <td>{{ harvest.unit }}</td>
                <td>
                  <span class="quality-badge" :class="`quality-${harvest.quality_grade}`">
                    {{ formatQuality(harvest.quality_grade) }}
                  </span>
                </td>
                <td class="notes-cell">
                  <span v-if="harvest.notes" :title="harvest.notes" class="notes-preview">
                    {{ truncateText(harvest.notes, 30) }}
                  </span>
                  <span v-else class="no-notes">—</span>
                </td>
                <td class="actions-cell">
                  <button class="btn-icon btn-edit" @click="editHarvest(harvest)" title="Edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn-icon btn-delete" @click="deleteHarvest(harvest.id)" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Record Harvest Modal -->
      <div v-if="showRecordDialog" class="modal-overlay" @click="closeRecordHarvestDialog">
        <div class="modal-dialog" @click.stop>
          <div class="modal-header">
            <h2>{{ editingHarvest ? 'Edit Harvest' : 'Record New Harvest' }}</h2>
            <button class="close-btn" @click="closeRecordHarvestDialog">&times;</button>
          </div>

          <div class="modal-content">
            <form @submit.prevent="submitHarvestForm">
              <!-- Crop Selection -->
              <div class="form-group">
                <label for="harvest-crop">Select Crop</label>
                <select id="harvest-crop" v-model="harvestForm.crop_id" required @change="onCropSelected">
                  <option value="">Choose a crop</option>
                  <option v-for="crop in readyCrops" :key="crop.id" :value="crop.id">
                    {{ crop.crop_type }} - {{ crop.variety }}
                  </option>
                </select>
                <span v-if="formErrors.crop_id" class="error-text">{{ formErrors.crop_id }}</span>
              </div>

              <!-- Harvest Date -->
              <div class="form-group">
                <label for="harvest-date">Harvest Date</label>
                <input
                  id="harvest-date"
                  v-model="harvestForm.harvest_date"
                  type="date"
                  required
                />
                <span v-if="formErrors.harvest_date" class="error-text">{{ formErrors.harvest_date }}</span>
              </div>

              <!-- Quantity (Small) & Unit Dropdown (Wide) -->
              <div class="form-group">
                <label for="harvest-quantity">Quantity</label>
                <div class="input-with-unit">
                  <input
                    id="harvest-quantity"
                    v-model.number="harvestForm.quantity"
                    type="number"
                    placeholder="e.g., 250"
                    step="0.01"
                    min="0"
                    required
                  />
                  <!-- Unit Dropdown -->
                  <div class="select-wrapper">
                    <select v-model="harvestForm.unit" class="unit-select">
                      <option value="kg">kg</option>
                      <option value="tonnes">tonnes</option>
                      <option value="bags">bags</option>
                      <option value="liters">liters</option>
                    </select>
                  </div>
                </div>
                <span v-if="formErrors.quantity" class="error-text">{{ formErrors.quantity }}</span>
              </div>

              <!-- Quality Grade -->
              <div class="form-group">
                <label for="harvest-quality">Quality Grade</label>
                <select id="harvest-quality" v-model="harvestForm.quality_grade">
                  <option value="">Select quality grade</option>
                  <option value="excellent">Excellent</option>
                  <option value="good">Good</option>
                  <option value="fair">Fair</option>
                  <option value="poor">Poor</option>
                </select>
              </div>

              <!-- Notes -->
              <div class="form-group">
                <label for="harvest-notes">Notes</label>
                <textarea
                  id="harvest-notes"
                  v-model="harvestForm.notes"
                  placeholder="Additional notes about this harvest"
                  rows="3"
                ></textarea>
              </div>

              <!-- Form Actions -->
              <div class="form-actions">
                <button type="button" class="btn-secondary" @click="closeRecordHarvestDialog">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="submittingHarvest">
                  {{ submittingHarvest ? 'Recording...' : (editingHarvest ? 'Update Harvest' : 'Record Harvest') }}
                </button>
              </div>
            </form>
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
import { useTheme } from '@/composables/useTheme'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()
const { isDark, isLight } = useTheme()

// State
const harvests = ref([])
const crops = ref([])
const loading = ref(true)
const searchQuery = ref('')
const qualityFilter = ref('')
const yearFilter = ref('')
const showRecordDialog = ref(false)
const editingHarvest = ref(null)
const submittingHarvest = ref(false)
const formErrors = ref({})

// Harvest Form
const harvestForm = ref({
  crop_id: '',
  harvest_date: '',
  quantity: '',
  unit: 'kg',
  quality_grade: '',
  notes: '',
})

// Computed Properties
const readyCrops = computed(() => {
  return crops.value.sort((a, b) => {
    return (a.crop_type || '').localeCompare(b.crop_type || '')
  })
})

const filteredHarvests = computed(() => {
  return harvests.value.filter(harvest => {
    const matchesSearch = 
      (harvest.crop?.crop_type || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (harvest.crop?.variety || '').toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesQuality = !qualityFilter.value || harvest.quality_grade === qualityFilter.value
    const matchesYear = !yearFilter.value || new Date(harvest.harvest_date).getFullYear() === parseInt(yearFilter.value)
    
    return matchesSearch && matchesQuality && matchesYear
  })
})

const availableYears = computed(() => {
  const years = new Set()
  harvests.value.forEach(h => {
    years.add(new Date(h.harvest_date).getFullYear())
  })
  return Array.from(years).sort((a, b) => b - a)
})

const totalQuantity = computed(() => {
  return harvests.value.reduce((sum, h) => sum + h.quantity, 0).toFixed(2)
})

const excellentCount = computed(() => {
  return harvests.value.filter(h => h.quality_grade === 'excellent').length
})

const averageYield = computed(() => {
  return harvests.value.length > 0 
    ? (harvests.value.reduce((sum, h) => sum + h.quantity, 0) / harvests.value.length).toFixed(2)
    : 0
})

// Lifecycle
onMounted(async () => {
  try {
    await fetchCrops()
    await fetchHarvests()
  } catch (error) {
    console.error('Error during mount:', error)
  }
})

// API Functions
const fetchHarvests = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/farmer/harvests', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    
    if (!response.ok) throw new Error('Failed to fetch harvests')
    
    const data = await response.json()
    harvests.value = data.data?.data || data.data || []
  } catch (error) {
    console.error('Error fetching harvests:', error)
  } finally {
    loading.value = false
  }
}

const fetchCrops = async () => {
  try {
    const response = await fetch('/api/farmer/crops', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    
    if (!response.ok) throw new Error('Failed to fetch crops')
    
    const data = await response.json()
    
    if (Array.isArray(data.data)) {
      crops.value = data.data
    } else if (Array.isArray(data)) {
      crops.value = data
    } else {
      crops.value = []
    }
  } catch (error) {
    console.error('Error fetching crops:', error)
    formErrors.value.general = 'Failed to load crops. Please try again.'
    crops.value = []
  }
}

// Modal Functions
const openRecordHarvestDialog = () => {
  resetHarvestForm()
  editingHarvest.value = null
  showRecordDialog.value = true
}

const closeRecordHarvestDialog = () => {
  showRecordDialog.value = false
  resetHarvestForm()
}

const resetHarvestForm = () => {
  harvestForm.value = {
    crop_id: '',
    harvest_date: new Date().toISOString().split('T')[0],
    quantity: '',
    unit: 'kg',
    quality_grade: '',
    notes: '',
  }
  formErrors.value = {}
}

const editHarvest = (harvest) => {
  editingHarvest.value = harvest
  harvestForm.value = {
    crop_id: harvest.crop_id,
    harvest_date: harvest.harvest_date,
    quantity: harvest.quantity,
    unit: harvest.unit,
    quality_grade: harvest.quality_grade || '',
    notes: harvest.notes || '',
  }
  showRecordDialog.value = true
}

const onCropSelected = () => {}

// Form Submission
const submitHarvestForm = async () => {
  try {
    submittingHarvest.value = true
    formErrors.value = {}

    if (!harvestForm.value.crop_id) {
      formErrors.value.crop_id = 'Please select a crop'
      submittingHarvest.value = false
      return
    }

    if (!harvestForm.value.quantity || harvestForm.value.quantity <= 0) {
      formErrors.value.quantity = 'Please enter a valid quantity'
      submittingHarvest.value = false
      return
    }

    const payload = {
      ...harvestForm.value,
      crop_id: parseInt(harvestForm.value.crop_id),
      quantity: parseFloat(harvestForm.value.quantity),
    }

    const url = editingHarvest.value 
      ? `/api/farmer/harvests/${editingHarvest.value.id}`
      : '/api/farmer/harvests'
    
    const method = editingHarvest.value ? 'PUT' : 'POST'

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

    showRecordDialog.value = false
    resetHarvestForm()
    await fetchHarvests()
  } catch (error) {
    console.error('Error submitting harvest form:', error)
    formErrors.value = { general: 'An error occurred while recording the harvest' }
  } finally {
    submittingHarvest.value = false
  }
}

const deleteHarvest = async (harvestId) => {
  if (!confirm('Are you sure you want to delete this harvest record?')) return

  try {
    const response = await fetch(`/api/farmer/harvests/${harvestId}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })

    if (!response.ok) throw new Error('Failed to delete harvest')

    await fetchHarvests()
  } catch (error) {
    console.error('Error deleting harvest:', error)
    alert('Failed to delete harvest record')
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

const formatQuality = (quality) => {
  const qualities = {
    excellent: 'Excellent',
    good: 'Good',
    fair: 'Fair',
    poor: 'Poor'
  }
  return qualities[quality] || quality
}

const truncateText = (text, length) => {
  return text.length > length ? text.substring(0, length) + '...' : text
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

.stat-icon.harvests {
  background-color: #10b981;
}

.stat-icon.quantity {
  background-color: #3b82f6;
}

.stat-icon.excellent {
  background-color: #f59e0b;
}

.stat-icon.average {
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
.quality-select,
.year-select {
  padding: 10px 15px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.search-input:focus,
.quality-select:focus,
.year-select:focus {
  outline: none;
  border-color: #10b981;
}

.search-input {
  flex: 1;
  min-width: 250px;
}

/* Harvests Section */
.harvests-section {
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

/* Table */
.harvests-table-wrapper {
  overflow-x: auto;
}

.harvests-table {
  width: 100%;
  border-collapse: collapse;
}

.harvests-table th {
  background-color: #f9fafb;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  font-size: 13px;
}

.harvests-table td {
  padding: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.harvests-table tbody tr:hover {
  background-color: #f9fafb;
}

.crop-name {
  font-weight: 600;
  color: #333;
}

.crop-name small {
  display: block;
  font-weight: normal;
  color: #666;
  font-size: 12px;
}

.quantity-value {
  font-weight: 600;
  color: #3b82f6;
}

.quality-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.quality-badge.quality-excellent {
  background-color: #fef3c7;
  color: #92400e;
}

.quality-badge.quality-good {
  background-color: #dcfce7;
  color: #166534;
}

.quality-badge.quality-fair {
  background-color: #dbeafe;
  color: #1e40af;
}

.quality-badge.quality-poor {
  background-color: #fee2e2;
  color: #991b1b;
}

.notes-cell {
  max-width: 150px;
  color: #666;
  font-size: 13px;
}

.notes-preview {
  cursor: help;
  border-bottom: 1px dotted #10b981;
}

.no-notes {
  color: #d1d5db;
}

.actions-cell {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  font-size: 14px;
}

.btn-edit {
  background-color: #e0e7ff;
  color: #3b82f6;
}

.btn-edit:hover {
  background-color: #3b82f6;
  color: white;
}

.btn-delete {
  background-color: #fee2e2;
  color: #ef4444;
}

.btn-delete:hover {
  background-color: #ef4444;
  color: white;
}

/* Buttons */
.btn-primary {
  background-color: #10b981;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
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
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background-color: #4b5563;
}

/* Modal */
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
  padding: 20px;
  box-sizing: border-box;
  backdrop-filter: blur(4px);
}

.modal-dialog {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  width: 100%;
  max-width: 550px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modalScale 0.25s ease-out;
}

@keyframes modalScale {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.modal-header h2 {
  margin: 0;
  color: #111827;
  font-size: 18px;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #9ca3af;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.close-btn:hover {
  background-color: #f3f4f6;
  color: #374151;
}

.modal-content {
  padding: 24px;
  overflow-y: auto;
}

/* Form Styles */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 500;
  color: #374151;
  font-size: 13px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
  color: #1f2937;
  background-color: #ffffff;
  transition: all 0.2s;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
}

.form-group textarea {
  resize: vertical;
}


.input-with-unit {
  display: flex;
  gap: 10px;
  width: 100%;
  align-items: center;
}

.input-with-unit input[type="number"] {
  width: 140px;
  flex-shrink: 0;
  height: 42px;
  padding: 10px 14px;
  margin: 0;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background-color: #ffffff;
  box-sizing: border-box;
  -moz-appearance: textfield;
}

.input-with-unit input[type="number"]::-webkit-outer-spin-button,
.input-with-unit input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.input-with-unit input[type="number"]:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
}

.select-wrapper {
  flex: 1;
  min-width: 0;
  height: 42px;
  position: relative;
}

.unit-select {
  width: 100% !important;
  height: 42px !important;
  padding: 0 32px 0 14px !important;
  margin: 0 !important;
  border: 1px solid #d1d5db !important;
  border-radius: 6px !important;
  background-color: #ffffff !important;
  font-size: 14px !important;
  color: #1f2937 !important;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 16px;
  box-sizing: border-box;
}

.unit-select:focus {
  outline: none;
  border-color: #10b981 !important;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12) !important;
}

.error-text {
  display: block;
  color: #ef4444;
  font-size: 12px;
  margin-top: 4px;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 16px;
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

  .modal-dialog {
    width: 95%;
  }
}

/* Light Mode (Default) */
.farmer-layout.light {
  background-color: #f5f5f5;
  color: #1f2937;
}

/* Dark Mode */
.farmer-layout.dark {
  background-color: #0b0f17;
  color: #f3f4f6;
}

.farmer-page {
  transition: background-color 0.3s ease, color 0.3s ease;
}

.farmer-layout.light .farmer-page {
  background-color: #f5f5f5;
  color: #1f2937;
}

.farmer-layout.dark .farmer-page {
  background-color: #0b0f17;
  color: #f3f4f6;
}

/* Text Colors */
.farmer-layout.light h1, .farmer-layout.light h2, .farmer-layout.light h3, .farmer-layout.light h4, .farmer-layout.light h5, .farmer-layout.light h6 {
  color: #1f2937;
}

.farmer-layout.dark h1, .farmer-layout.dark h2, .farmer-layout.dark h3, .farmer-layout.dark h4, .farmer-layout.dark h5, .farmer-layout.dark h6 {
  color: #ffffff;
}

.farmer-layout.light p {
  color: #4b5563;
}

.farmer-layout.dark p {
  color: #cbd5e1;
}

/* Cards and Containers */
.farmer-layout.light .card, .farmer-layout.light .section, .farmer-layout.light [class*="card"], .farmer-layout.light [class*="container"] {
  background-color: #ffffff;
  color: #1f2937;
}

.farmer-layout.dark .card, .farmer-layout.dark .section, .farmer-layout.dark [class*="card"], .farmer-layout.dark [class*="container"] {
  background-color: #131b2e;
  color: #f3f4f6;
  border: 1px solid #1e293b;
}

/* Stat Cards */
.farmer-layout.light .stat-card {
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  color: #1f2937;
}

.farmer-layout.dark .stat-card {
  background: #131b2e;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  border: 1px solid #1e293b;
  color: #f3f4f6;
}

/* Form Elements */
.farmer-layout.light input, .farmer-layout.light textarea, .farmer-layout.light select {
  background-color: #ffffff;
  color: #1f2937;
  border: 1px solid #d1d5db;
}

.farmer-layout.dark input, .farmer-layout.dark textarea, .farmer-layout.dark select {
  background-color: #1a2338;
  color: #f3f4f6;
  border: 1px solid #2d3f52;
}

.farmer-layout.light input:focus, .farmer-layout.light textarea:focus, .farmer-layout.light select:focus {
  border-color: #10b981;
}

.farmer-layout.dark input:focus, .farmer-layout.dark textarea:focus, .farmer-layout.dark select:focus {
  border-color: #10b981;
}

/* Modals */
.farmer-layout.light .modal-dialog, .farmer-layout.light .modal-content {
  background-color: white;
  color: #1f2937;
}

.farmer-layout.dark .modal-dialog, .farmer-layout.dark .modal-content {
  background-color: #131b2e;
  color: #f3f4f6;
  border: 1px solid #1e293b;
}

/* Tables */
.farmer-layout.light .stat-content h3 {
  color: #666;
}

.farmer-layout.dark .stat-content h3 {
  color: #cbd5e1;
}

.farmer-layout.light .stat-value {
  color: #333;
}

.farmer-layout.dark .stat-value {
  color: #f1f5f9;
}

/* Controls Section */
.farmer-layout.light .controls-section {
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  color: #1f2937;
}

.farmer-layout.dark .controls-section {
  background: #131b2e;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  border: 1px solid #1e293b;
  color: #f3f4f6;
}

.farmer-layout.light .search-input,
.farmer-layout.light .quality-select,
.farmer-layout.light .year-select {
  background-color: #ffffff;
  color: #1f2937;
  border-color: #d1d5db;
}

.farmer-layout.dark .search-input,
.farmer-layout.dark .quality-select,
.farmer-layout.dark .year-select {
  background-color: #1a2338;
  color: #f3f4f6;
  border-color: #2d3f52;
}

.farmer-layout.light .search-input:focus,
.farmer-layout.light .quality-select:focus,
.farmer-layout.light .year-select:focus {
  border-color: #10b981;
}

.farmer-layout.dark .search-input:focus,
.farmer-layout.dark .quality-select:focus,
.farmer-layout.dark .year-select:focus {
  border-color: #10b981;
  background-color: #1a2338;
}

/* Harvests Section */
.farmer-layout.light .harvests-section {
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  color: #1f2937;
}

.farmer-layout.dark .harvests-section {
  background: #131b2e;
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  border: 1px solid #1e293b;
  color: #f3f4f6;
}

.farmer-layout.light .loading-state,
.farmer-layout.light .empty-state {
  color: #666;
}

.farmer-layout.dark .loading-state,
.farmer-layout.dark .empty-state {
  color: #cbd5e1;
}

.farmer-layout.light .empty-state i {
  color: #d1d5db;
}

.farmer-layout.dark .empty-state i {
  color: #475569;
}

.farmer-layout.light .empty-state h3 {
  color: #333;
}

.farmer-layout.dark .empty-state h3 {
  color: #f1f5f9;
}

/* Table */
.farmer-layout.light .harvests-table th {
  background-color: #f9fafb;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
}

.farmer-layout.dark .harvests-table th {
  background-color: #1a2338;
  color: #cbd5e1;
  border-bottom: 2px solid #2d3f52;
}

.farmer-layout.light .harvests-table td {
  color: #1f2937;
  border-bottom: 1px solid #e5e7eb;
}

.farmer-layout.dark .harvests-table td {
  color: #f3f4f6;
  border-bottom: 1px solid #2d3f52;
}

.farmer-layout.light .harvests-table tbody tr:hover {
  background-color: #f9fafb;
}

.farmer-layout.dark .harvests-table tbody tr:hover {
  background-color: #1a2338;
}

.farmer-layout.light .crop-name {
  color: #333;
}

.farmer-layout.dark .crop-name {
  color: #f1f5f9;
}

.farmer-layout.light .crop-name small {
  color: #666;
}

.farmer-layout.dark .crop-name small {
  color: #cbd5e1;
}

.farmer-layout.light .quantity-value {
  color: #3b82f6;
}

.farmer-layout.dark .quantity-value {
  color: #60a5fa;
}

.farmer-layout.light .notes-cell {
  color: #666;
}

.farmer-layout.dark .notes-cell {
  color: #cbd5e1;
}

.farmer-layout.light .no-notes {
  color: #d1d5db;
}

.farmer-layout.dark .no-notes {
  color: #475569;
}

.farmer-layout.light .notes-preview {
  border-bottom: 1px dotted #10b981;
}

.farmer-layout.dark .notes-preview {
  border-bottom: 1px dotted #4ade80;
}

/* Buttons */
.farmer-layout.light .btn-edit {
  background-color: #e0e7ff;
  color: #3b82f6;
}

.farmer-layout.dark .btn-edit {
  background-color: #1e3a8a;
  color: #60a5fa;
}

.farmer-layout.light .btn-edit:hover {
  background-color: #3b82f6;
  color: white;
}

.farmer-layout.dark .btn-edit:hover {
  background-color: #3b82f6;
  color: white;
}

.farmer-layout.light .btn-delete {
  background-color: #fee2e2;
  color: #ef4444;
}

.farmer-layout.dark .btn-delete {
  background-color: #7f1d1d;
  color: #f87171;
}

.farmer-layout.light .btn-delete:hover {
  background-color: #ef4444;
  color: white;
}

.farmer-layout.dark .btn-delete:hover {
  background-color: #ef4444;
  color: white;
}

/* Modal Header */
.farmer-layout.light .modal-header {
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  color: #111827;
}

.farmer-layout.dark .modal-header {
  background-color: #1a2338;
  border-bottom: 1px solid #2d3f52;
  color: #f1f5f9;
}

.farmer-layout.light .modal-header h2 {
  color: #111827;
}

.farmer-layout.dark .modal-header h2 {
  color: #f1f5f9;
}

.farmer-layout.light .close-btn {
  color: #9ca3af;
}

.farmer-layout.dark .close-btn {
  color: #94a3b8;
}

.farmer-layout.light .close-btn:hover {
  background-color: #f3f4f6;
  color: #374151;
}

.farmer-layout.dark .close-btn:hover {
  background-color: #2d3f52;
  color: #cbd5e1;
}

/* Form Labels */
.farmer-layout.light .form-group label {
  color: #374151;
}

.farmer-layout.dark .form-group label {
  color: #cbd5e1;
}

.farmer-layout.light .form-group input,
.farmer-layout.light .form-group textarea,
.farmer-layout.light .form-group select {
  background-color: #ffffff;
  color: #1f2937;
  border-color: #d1d5db;
}

.farmer-layout.dark .form-group input,
.farmer-layout.dark .form-group textarea,
.farmer-layout.dark .form-group select {
  background-color: #1a2338;
  color: #f3f4f6;
  border-color: #2d3f52;
}

.farmer-layout.light .form-group input:focus,
.farmer-layout.light .form-group textarea:focus,
.farmer-layout.light .form-group select:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
}

.farmer-layout.dark .form-group input:focus,
.farmer-layout.dark .form-group textarea:focus,
.farmer-layout.dark .form-group select:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.farmer-layout.light .unit-select {
  background-color: #ffffff !important;
  color: #1f2937 !important;
  border-color: #d1d5db !important;
}

.farmer-layout.dark .unit-select {
  background-color: #1a2338 !important;
  color: #f3f4f6 !important;
  border-color: #2d3f52 !important;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%94a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") !important;
}

.farmer-layout.dark .unit-select:focus {
  border-color: #10b981 !important;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
}

.farmer-layout.light .error-text {
  color: #ef4444;
}

.farmer-layout.dark .error-text {
  color: #f87171;
}

/* Form Actions */
.farmer-layout.light .form-actions {
  border-top: 1px solid #e5e7eb;
}

.farmer-layout.dark .form-actions {
  border-top: 1px solid #2d3f52;
}

/* Header Text */
.farmer-layout.light .header-content h1 {
  color: #333;
}

.farmer-layout.dark .header-content h1 {
  color: #f1f5f9;
}

.farmer-layout.light .header-content p {
  color: #666;
}

.farmer-layout.dark .header-content p {
  color: #cbd5e1;
}

/* Modal Overlay */
.farmer-layout.dark .modal-overlay {
  background-color: rgba(0, 0, 0, 0.7);
}mer-layout.dark td, .farmer-layout.dark th {
  background-color: #131b2e;
  color: #f3f4f6;
  border-color: #2d3f52;
}

/* Buttons stay consistent but ensure text contrast */
.btn-primary { color: white; }
.btn-secondary { color: white; }
</style>