<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <header class="page-header">
        <div class="header-titles">
          <h1>Crop Activities</h1>
          <p>Track, monitor, and optimize all field operations and crop lifecycles.</p>
        </div>
        <button class="btn-primary" @click="openAddActivityDialog">
          <PlusCircle class="icon" />
          <span>Add Activity</span>
        </button>
      </header>

      <main class="content-section">
        <!-- Filters Toolbar -->
        <div class="filters-bar">
          <div class="filter-group">
            <label for="filter-crop">Filter by Crop</label>
            <select id="filter-crop" v-model="selectedCrop" class="filter-select">
              <option value="">All Crops</option>
              <option v-for="crop in crops" :key="crop.id" :value="crop.id">
                {{ crop.name }}
              </option>
            </select>
          </div>

          <div class="filter-group">
            <label for="filter-type">Filter by Type</label>
            <select id="filter-type" v-model="selectedActivityType" class="filter-select">
              <option value="">All Types</option>
              <option value="planting">Planting</option>
              <option value="watering">Watering</option>
              <option value="fertilizing">Fertilizing</option>
              <option value="weeding">Weeding</option>
              <option value="pesticide">Pesticide Application</option>
              <option value="pruning">Pruning</option>
              <option value="harvesting">Harvesting</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div class="filter-group date-range-group">
            <label>Date Range</label>
            <div class="date-inputs">
              <input v-model="dateFrom" type="date" class="filter-input" aria-label="From date" />
              <span class="date-separator">to</span>
              <input v-model="dateTo" type="date" class="filter-input" aria-label="To date" />
            </div>
          </div>

          <div class="filter-actions">
            <button class="btn-secondary" @click="applyFilters">Filter</button>
            <button class="btn-outline" @click="resetFilters">Reset</button>
          </div>
        </div>

        <!-- Error Alert -->
        <div v-if="error" class="error-alert">
          <AlertCircle class="icon-alert" />
          <p>{{ error }}</p>
          <button @click="fetchActivities" class="btn-small btn-primary">Retry</button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Loading activity logs...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredActivities.length === 0" class="empty-state">
          <Sprout class="empty-icon" />
          <h3>No activities found</h3>
          <p>Get started by recording your first crop management operation.</p>
          <button class="btn-primary mt-3" @click="openAddActivityDialog">Add Activity</button>
        </div>

        <!-- Timeline Activities List -->
        <div v-else class="activities-container">
          <div class="timeline">
            <div v-for="activity in filteredActivities" :key="activity.id" class="timeline-item">
              <div class="timeline-marker" :class="`type-${activity.activity_type}`">
                <component :is="getActivityIcon(activity.activity_type)" />
              </div>
              <div class="timeline-content">
                <div class="activity-header">
                  <div>
                    <span class="activity-badge" :class="`badge-${activity.activity_type}`">
                      {{ formatActivityType(activity.activity_type) }}
                    </span>
                    <h3>{{ activity.crop?.name || 'Unknown Crop' }}</h3>
                  </div>
                  <span class="activity-date">{{ formatDate(activity.activity_date) }}</span>
                </div>
                
                <div class="activity-body">
                  <p v-if="activity.description" class="description">{{ activity.description }}</p>
                  
                  <div class="details-grid">
                    <div v-if="activity.farm" class="detail-item">
                      <span class="label">Farm:</span>
                      <span class="value">{{ activity.farm.name }}</span>
                    </div>
                    <div v-if="activity.quantity" class="detail-item">
                      <span class="label">Quantity:</span>
                      <span class="value">{{ activity.quantity }} {{ activity.unit || 'units' }}</span>
                    </div>
                    <div v-if="activity.cost" class="detail-item">
                      <span class="label">Cost:</span>
                      <span class="value highlight-cost">{{ formatCurrency(activity.cost) }}</span>
                    </div>
                    <div v-if="activity.weather" class="detail-item">
                      <span class="label">Weather:</span>
                      <span class="value capitalize">{{ activity.weather }}</span>
                    </div>
                  </div>

                  <div v-if="activity.notes" class="notes-box">
                    <strong>Notes:</strong> {{ activity.notes }}
                  </div>
                </div>

                <div class="activity-actions">
                  <button class="btn-small btn-edit" @click="editActivity(activity)">Edit</button>
                  <button class="btn-small btn-delete" @click="deleteActivity(activity.id)">Delete</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Add/Edit Activity Modal -->
    <div v-if="showActivityModal" class="modal-overlay" @click="closeActivityDialog">
      <div class="modal-dialog" @click.stop>
        <div class="modal-header">
          <h2>{{ isEditingActivity ? 'Edit Crop Activity' : 'Record New Activity' }}</h2>
          <button class="close-btn" @click="closeActivityDialog" aria-label="Close modal">&times;</button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="submitActivityForm">
            <div class="form-row">
              <div class="form-group">
                <label for="crop-select">Crop *</label>
                <select id="crop-select" v-model="activityForm.crop_id" required>
                  <option value="" disabled>Select a crop</option>
                  <option v-for="crop in crops" :key="crop.id" :value="crop.id">{{ crop.name }}</option>
                </select>
                <span v-if="formErrors.crop_id" class="error-text">{{ formErrors.crop_id }}</span>
              </div>

              <div class="form-group">
                <label for="farm-select">Farm *</label>
                <select id="farm-select" v-model="activityForm.farm_id" required>
                  <option value="" disabled>Select a farm</option>
                  <option v-for="farm in farms" :key="farm.id" :value="farm.id">{{ farm.name }}</option>
                </select>
                <span v-if="formErrors.farm_id" class="error-text">{{ formErrors.farm_id }}</span>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="activity-type">Activity Type *</label>
                <select id="activity-type" v-model="activityForm.activity_type" required>
                  <option value="" disabled>Select activity type</option>
                  <option value="planting">Planting</option>
                  <option value="watering">Watering</option>
                  <option value="fertilizing">Fertilizing</option>
                  <option value="weeding">Weeding</option>
                  <option value="pesticide">Pesticide Application</option>
                  <option value="pruning">Pruning</option>
                  <option value="harvesting">Harvesting</option>
                  <option value="other">Other</option>
                </select>
                <span v-if="formErrors.activity_type" class="error-text">{{ formErrors.activity_type }}</span>
              </div>

              <div class="form-group">
                <label for="activity-date">Date *</label>
                <input id="activity-date" v-model="activityForm.activity_date" type="date" required />
                <span v-if="formErrors.activity_date" class="error-text">{{ formErrors.activity_date }}</span>
              </div>
            </div>

            <div class="form-group">
              <label for="description">Description</label>
              <textarea id="description" v-model="activityForm.description" placeholder="Briefly describe what was done..." rows="2"></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="quantity">Quantity</label>
                <input id="quantity" v-model.number="activityForm.quantity" type="number" placeholder="e.g., 50" step="0.01" />
              </div>
              <div class="form-group">
                <label for="unit">Unit</label>
                <input id="unit" v-model="activityForm.unit" type="text" placeholder="e.g., kg, liters, bags" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="cost">Cost (ETB)</label>
                <input id="cost" v-model.number="activityForm.cost" type="number" placeholder="0.00" step="0.01" />
              </div>
              <div class="form-group">
                <label for="weather">Weather Condition</label>
                <select id="weather" v-model="activityForm.weather">
                  <option value="">Select weather</option>
                  <option value="sunny">Sunny</option>
                  <option value="cloudy">Cloudy</option>
                  <option value="rainy">Rainy</option>
                  <option value="windy">Windy</option>
                  <option value="cold">Cold</option>
                  <option value="hot">Hot</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="notes">Additional Notes</label>
              <textarea id="notes" v-model="activityForm.notes" placeholder="Observations, remarks, or next steps..." rows="2"></textarea>
            </div>

            <div v-if="formSubmitError" class="error-alert error-large">
              <AlertCircle class="icon-alert" />
              <p><strong>Error:</strong> {{ formSubmitError }}</p>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-outline" @click="closeActivityDialog">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="submitting">
                {{ submitting ? 'Saving...' : (isEditingActivity ? 'Update Activity' : 'Save Activity') }}
              </button>
            </div>
          </form>
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
import { 
  PlusCircle, Droplets, Leaf, Bug, Scissors, Flame, Sprout, AlertCircle
} from 'lucide-vue-next'
import apiClient from '@/api/config'

const router = useRouter()
const auth = useAuthStore()

// State
const activities = ref([])
const crops = ref([])
const farms = ref([])
const loading = ref(true)
const error = ref(null)
const showActivityModal = ref(false)
const submitting = ref(false)
const isEditingActivity = ref(false)
const editingActivityId = ref(null)

// Filters
const selectedCrop = ref('')
const selectedActivityType = ref('')
const dateFrom = ref('')
const dateTo = ref('')

// Form
const formErrors = ref({})
const formSubmitError = ref(null)

const activityForm = ref({
  crop_id: '',
  farm_id: '',
  activity_type: '',
  activity_date: '',
  activity_time: '',
  description: '',
  quantity: '',
  unit: '',
  cost: '',
  weather: '',
  notes: ''
})

// Computed
const filteredActivities = computed(() => {
  return activities.value.filter(activity => {
    if (selectedCrop.value && activity.crop_id !== parseInt(selectedCrop.value)) return false
    if (selectedActivityType.value && activity.activity_type !== selectedActivityType.value) return false
    
    if (dateFrom.value) {
      const actDate = new Date(activity.activity_date)
      const fromDate = new Date(dateFrom.value)
      if (actDate < fromDate) return false
    }
    
    if (dateTo.value) {
      const actDate = new Date(activity.activity_date)
      const toDate = new Date(dateTo.value)
      if (actDate > toDate) return false
    }
    
    return true
  }).sort((a, b) => new Date(b.activity_date) - new Date(a.activity_date))
})

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchActivities(),
    fetchCrops(),
    fetchFarms()
  ])
})

// Methods
const fetchActivities = async () => {
  try {
    loading.value = true
    error.value = null
    try {
      const response = await apiClient.get('/farmer/crop-activities')
      activities.value = Array.isArray(response.data.data) ? response.data.data : []
    } catch (apiError) {
      // If 404, the table might not exist yet - show empty state
      if (apiError.response?.status === 404) {
        activities.value = []
        error.value = null
      } else {
        throw apiError
      }
    }
  } catch (err) {
    console.error('Error fetching activities:', err)
    if (err.response?.status !== 404) {
      error.value = err.response?.data?.message || 'Failed to load activity logs.'
    }
  } finally {
    loading.value = false
  }
}

const fetchCrops = async () => {
  try {
    const response = await apiClient.get('/farmer/crops')
    crops.value = Array.isArray(response.data.data) ? response.data.data : []
  } catch (err) {
    console.error('Error fetching crops:', err)
  }
}

const fetchFarms = async () => {
  try {
    const response = await apiClient.get('/farmer/farms')
    farms.value = Array.isArray(response.data.data) ? response.data.data : []
  } catch (err) {
    console.error('Error fetching farms:', err)
  }
}

const openAddActivityDialog = () => {
  resetForm()
  isEditingActivity.value = false
  editingActivityId.value = null
  showActivityModal.value = true
}

const closeActivityDialog = () => {
  showActivityModal.value = false
  resetForm()
}

const resetForm = () => {
  activityForm.value = {
    crop_id: '',
    farm_id: '',
    activity_type: '',
    activity_date: '',
    activity_time: '',
    description: '',
    quantity: '',
    unit: '',
    cost: '',
    weather: '',
    notes: ''
  }
  formErrors.value = {}
  formSubmitError.value = null
}

const editActivity = (activity) => {
  activityForm.value = {
    crop_id: activity.crop_id,
    farm_id: activity.farm_id,
    activity_type: activity.activity_type,
    activity_date: activity.activity_date,
    activity_time: activity.activity_time || '',
    description: activity.description || '',
    quantity: activity.quantity || '',
    unit: activity.unit || '',
    cost: activity.cost || '',
    weather: activity.weather || '',
    notes: activity.notes || ''
  }
  isEditingActivity.value = true
  editingActivityId.value = activity.id
  showActivityModal.value = true
}

const submitActivityForm = async () => {
  try {
    submitting.value = true
    formErrors.value = {}
    formSubmitError.value = null

    const payload = {
      crop_id: parseInt(activityForm.value.crop_id),
      farm_id: parseInt(activityForm.value.farm_id),
      activity_type: activityForm.value.activity_type,
      activity_date: activityForm.value.activity_date,
      activity_time: activityForm.value.activity_time || null,
      description: activityForm.value.description || null,
      quantity: activityForm.value.quantity ? parseFloat(activityForm.value.quantity) : null,
      unit: activityForm.value.unit || null,
      cost: activityForm.value.cost ? parseFloat(activityForm.value.cost) : null,
      weather: activityForm.value.weather || null,
      notes: activityForm.value.notes || null
    }

    const url = isEditingActivity.value
      ? `/farmer/crop-activities/${editingActivityId.value}`
      : '/farmer/crop-activities'
    const method = isEditingActivity.value ? 'put' : 'post'

    await apiClient[method](url, payload)

    showActivityModal.value = false
    resetForm()
    await fetchActivities()
  } catch (error) {
    console.error('Error submitting activity:', error)
    const responseData = error.response?.data

    if (error.response?.status === 422 && responseData?.errors) {
      const fieldErrors = {}
      for (const [field, messages] of Object.entries(responseData.errors)) {
        fieldErrors[field] = Array.isArray(messages) ? messages[0] : messages
      }
      formErrors.value = fieldErrors
    }

    formSubmitError.value = responseData?.message || 'An error occurred while saving the activity.'
  } finally {
    submitting.value = false
  }
}

const deleteActivity = async (id) => {
  if (confirm('Are you sure you want to delete this activity record?')) {
    try {
      await apiClient.delete(`/farmer/crop-activities/${id}`)
      await fetchActivities()
    } catch (err) {
      error.value = 'Failed to delete activity record.'
    }
  }
}

const applyFilters = () => {}

const resetFilters = () => {
  selectedCrop.value = ''
  selectedActivityType.value = ''
  dateFrom.value = ''
  dateTo.value = ''
}

const getActivityIcon = (type) => {
  const icons = {
    watering: Droplets,
    fertilizing: Leaf,
    pesticide: Bug,
    pruning: Scissors,
    planting: Flame,
    harvesting: Sprout,
    other: AlertCircle
  }
  return icons[type] || AlertCircle
}

const formatActivityType = (type) => {
  const types = {
    planting: 'Planting',
    watering: 'Watering',
    fertilizing: 'Fertilizing',
    weeding: 'Weeding',
    pesticide: 'Pesticide Application',
    pruning: 'Pruning',
    harvesting: 'Harvesting',
    other: 'Other'
  }
  return types[type] || type
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-ET', {
    style: 'currency',
    currency: 'ETB'
  }).format(amount || 0)
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
/* Layout */
.farmer-layout {
  display: flex;
  height: 100vh;
  background-color: #f8fafc;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  color: #1e293b;
}

.farmer-page {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  padding: 32px;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.page-header h1 {
  font-size: 24px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 4px;
}

.page-header p {
  color: #64748b;
  font-size: 14px;
}

/* Content Section */
.content-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

/* Filters Bar */
.filters-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  align-items: flex-end;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
  min-width: 140px;
}

.filter-group label {
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.filter-select,
.filter-input {
  padding: 8px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 13px;
  background-color: white;
  color: #1e293b;
  transition: all 0.2s;
}

.filter-select:focus,
.filter-input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.date-inputs {
  display: flex;
  align-items: center;
  gap: 8px;
}

.date-separator {
  font-size: 12px;
  color: #64748b;
}

.filter-actions {
  display: flex;
  gap: 8px;
}

/* Buttons */
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: #10b981;
  color: white;
  padding: 9px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: background-color 0.2s;
}

.btn-primary:hover {
  background-color: #059669;
}

.btn-secondary {
  background-color: #475569;
  color: white;
  padding: 9px 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: background-color 0.2s;
}

.btn-secondary:hover {
  background-color: #334155;
}

.btn-outline {
  background-color: white;
  color: #475569;
  padding: 9px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-outline:hover {
  background-color: #f1f5f9;
  border-color: #94a3b8;
}

.icon {
  width: 16px;
  height: 16px;
}

/* States */
.error-alert {
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.error-alert p {
  margin: 0;
  flex: 1;
  font-size: 13px;
}

.icon-alert {
  width: 20px;
  height: 20px;
  color: #dc2626;
  flex-shrink: 0;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e2e8f0;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 12px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  width: 48px;
  height: 48px;
  color: #cbd5e1;
  margin-bottom: 12px;
}

.empty-state h3 {
  font-size: 16px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 4px;
}

.empty-state p {
  font-size: 13px;
  margin-bottom: 16px;
}

/* Timeline Layout */
.timeline {
  position: relative;
  padding: 10px 0;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 23px;
  top: 0;
  bottom: 0;
  width: 2px;
  background-color: #e2e8f0;
}

.timeline-item {
  display: flex;
  gap: 20px;
  margin-bottom: 24px;
  position: relative;
}

.timeline-marker {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: white;
  border: 2px solid #cbd5e1;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  z-index: 1;
  color: #475569;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

/* Timeline Activity Colors */
.type-watering { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; }
.type-fertilizing { background: #ecfdf5; border-color: #10b981; color: #10b981; }
.type-pesticide { background: #fef2f2; border-color: #ef4444; color: #ef4444; }
.type-pruning { background: #fffbeb; border-color: #f59e0b; color: #f59e0b; }
.type-planting { background: #f5f3ff; border-color: #8b5cf6; color: #8b5cf6; }
.type-harvesting { background: #fdf2f8; border-color: #ec4899; color: #ec4899; }

.timeline-content {
  flex: 1;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

.activity-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
}

.activity-header h3 {
  margin: 4px 0 0 0;
  font-size: 15px;
  color: #0f172a;
  font-weight: 600;
}

.activity-badge {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 2px 8px;
  border-radius: 4px;
  display: inline-block;
}

.badge-watering { background: #eff6ff; color: #1d4ed8; }
.badge-fertilizing { background: #ecfdf5; color: #047857; }
.badge-pesticide { background: #fef2f2; color: #b91c1c; }
.badge-pruning { background: #fffbeb; color: #b45309; }
.badge-planting { background: #f5f3ff; color: #6d28d9; }
.badge-harvesting { background: #fdf2f8; color: #be185d; }

.activity-date {
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
}

.description {
  margin: 8px 0 12px 0;
  font-size: 13px;
  color: #475569;
  line-height: 1.4;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px;
  margin: 12px 0;
  padding: 10px 12px;
  background: #f8fafc;
  border-radius: 6px;
}

.detail-item {
  display: flex;
  gap: 6px;
  font-size: 12px;
}

.detail-item .label {
  font-weight: 600;
  color: #64748b;
}

.detail-item .value {
  color: #1e293b;
}

.highlight-cost {
  font-weight: 600;
  color: #059669;
}

.capitalize {
  text-transform: capitalize;
}

.notes-box {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px dashed #e2e8f0;
  font-size: 12px;
  color: #64748b;
}

.notes-box strong {
  color: #334155;
}

.activity-actions {
  display: flex;
  gap: 6px;
  margin-top: 12px;
  justify-content: flex-end;
}

.btn-small {
  padding: 4px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 11px;
  font-weight: 600;
  transition: background-color 0.2s;
}

.btn-edit {
  background-color: #e0f2fe;
  color: #0369a1;
}

.btn-edit:hover {
  background-color: #bae6fd;
}

.btn-delete {
  background-color: #fee2e2;
  color: #b91c1c;
}

.btn-delete:hover {
  background-color: #fecaca;
}

/* Modal Styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-dialog {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  width: 90%;
  max-width: 560px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #64748b;
  padding: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
}

.close-btn:hover {
  background-color: #f1f5f9;
  color: #0f172a;
}

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 16px;
  flex: 1;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #334155;
  font-size: 13px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 13px;
  font-family: inherit;
  color: #1e293b;
  background-color: #fff;
  transition: all 0.2s;
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

.form-row {
  display: flex;
  gap: 16px;
}

.error-text {
  display: block;
  color: #ef4444;
  font-size: 11px;
  margin-top: 4px;
}

.error-large {
  margin-top: 16px;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}

/* Responsive */
@media (max-width: 768px) {
  .farmer-page {
    margin-left: 0;
    padding: 16px;
  }

  .filters-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-group {
    width: 100%;
  }

  .form-row {
    flex-direction: column;
    gap: 0;
  }

  .timeline::before {
    left: 15px;
  }

  .timeline-marker {
    width: 32px;
    height: 32px;
  }
}
</style>