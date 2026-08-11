<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <h1>My Farms</h1>
        <p>Manage and view all your farms</p>
      </div>

      <div class="content-section">
        <div class="section-header">
          <h2>Farm List</h2>
          <button class="btn-primary" @click="openAddFarmDialog">+ Add New Farm</button>
        </div>

        <div v-if="loading" class="loading-message">
          <p>Loading farms...</p>
        </div>

        <div v-else-if="farms.length === 0" class="empty-state">
          <p>No farms yet. Click "Add New Farm" to create your first farm.</p>
        </div>

        <div v-else class="farms-grid">
          <div class="farm-card" v-for="farm in farms" :key="farm.id">
            <div class="farm-header">
              <h3>{{ farm.name }}</h3>
              <span class="badge">{{ farm.size_hectares }} ha</span>
            </div>
            <div class="farm-details">
              <p><strong>Location:</strong> {{ farm.address }}</p>
              <p><strong>Region:</strong> {{ farm.region }}</p>
              <p><strong>Type:</strong> {{ capitalizeFirstLetter(farm.farm_type) }}</p>
              <p><strong>Crops:</strong> {{ farm.crops_count || 0 }}</p>
            </div>
            <div class="farm-actions">
              <button class="btn-small btn-edit" @click="editFarm(farm)">Edit</button>
              <button class="btn-small btn-view">View Details</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Farm Modal -->
    <div v-if="showAddFarmModal" class="modal-overlay" @click="closeAddFarmDialog">
      <div class="modal-dialog" @click.stop>
        <div class="modal-header">
          <h2>{{ isEditingFarm ? 'Edit Farm' : 'Add New Farm' }}</h2>
          <button class="close-btn" @click="closeAddFarmDialog">&times;</button>
        </div>

        <div class="modal-content">
          <form @submit.prevent="submitFarmForm">
            <!-- Farm Name -->
            <div class="form-group">
              <label for="farm-name">Farm Name *</label>
              <input
                id="farm-name"
                v-model="farmForm.name"
                type="text"
                placeholder="e.g., Green Valley Farm"
                required
              />
              <span v-if="formErrors.name" class="error-text">{{ formErrors.name }}</span>
            </div>

            <!-- Description -->
            <div class="form-group">
              <label for="farm-description">Description</label>
              <textarea
                id="farm-description"
                v-model="farmForm.description"
                placeholder="Brief description of your farm"
                rows="3"
              ></textarea>
            </div>

            <!-- Address -->
            <div class="form-group">
              <label for="farm-address">Address *</label>
              <input
                id="farm-address"
                v-model="farmForm.address"
                type="text"
                placeholder="Street address"
                required
              />
              <span v-if="formErrors.address" class="error-text">{{ formErrors.address }}</span>
            </div>

            <!-- Region -->
            <div class="form-group">
              <label for="farm-region">Region *</label>
              <input
                id="farm-region"
                v-model="farmForm.region"
                type="text"
                placeholder="e.g., Oromia"
                required
              />
              <span v-if="formErrors.region" class="error-text">{{ formErrors.region }}</span>
            </div>

            <!-- Zone -->
            <div class="form-group">
              <label for="farm-zone">Zone *</label>
              <input
                id="farm-zone"
                v-model="farmForm.zone"
                type="text"
                placeholder="e.g., North Shewa"
                required
              />
              <span v-if="formErrors.zone" class="error-text">{{ formErrors.zone }}</span>
            </div>

            <!-- Woreda -->
            <div class="form-group">
              <label for="farm-woreda">Woreda *</label>
              <input
                id="farm-woreda"
                v-model="farmForm.woreda"
                type="text"
                placeholder="District/Woreda"
                required
              />
              <span v-if="formErrors.woreda" class="error-text">{{ formErrors.woreda }}</span>
            </div>

            <!-- Kebele -->
            <div class="form-group">
              <label for="farm-kebele">Kebele</label>
              <input
                id="farm-kebele"
                v-model="farmForm.kebele"
                type="text"
                placeholder="Village/Kebele"
              />
            </div>

            <!-- Size (Hectares) -->
            <div class="form-group">
              <label for="farm-size">Farm Size (Hectares) *</label>
              <input
                id="farm-size"
                v-model.number="farmForm.size_hectares"
                type="number"
                placeholder="e.g., 5.5"
                step="0.1"
                min="0.1"
                required
              />
              <span v-if="formErrors.size_hectares" class="error-text">{{ formErrors.size_hectares }}</span>
            </div>

            <!-- Farm Type -->
            <div class="form-group">
              <label for="farm-type">Farm Type *</label>
              <select id="farm-type" v-model="farmForm.farm_type" required>
                <option value="">Select farm type</option>
                <option value="crop">Crop</option>
                <option value="livestock">Livestock</option>
                <option value="mixed">Mixed</option>
                <option value="fishery">Fishery</option>
              </select>
              <span v-if="formErrors.farm_type" class="error-text">{{ formErrors.farm_type }}</span>
            </div>

            <!-- Coordinates (Optional) -->
            <div class="coordinates-section">
              <h4>Coordinates (Optional)</h4>
              <div class="form-row">
                <div class="form-group">
                  <label for="farm-latitude">Latitude</label>
                  <input
                    id="farm-latitude"
                    v-model.number="farmForm.latitude"
                    type="number"
                    placeholder="e.g., 9.0320"
                    step="0.0001"
                  />
                  <span v-if="formErrors.latitude" class="error-text">{{ formErrors.latitude }}</span>
                </div>
                <div class="form-group">
                  <label for="farm-longitude">Longitude</label>
                  <input
                    id="farm-longitude"
                    v-model.number="farmForm.longitude"
                    type="number"
                    placeholder="e.g., 38.7469"
                    step="0.0001"
                  />
                  <span v-if="formErrors.longitude" class="error-text">{{ formErrors.longitude }}</span>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="closeAddFarmDialog">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="submitting">
                {{ submitting ? 'Saving...' : (isEditingFarm ? 'Update Farm' : 'Create Farm') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()

// State management
const farms = ref([])
const loading = ref(true)
const showAddFarmModal = ref(false)
const submitting = ref(false)
const isEditingFarm = ref(false)
const editingFarmId = ref(null)

const formErrors = ref({})

const farmForm = ref({
  name: '',
  description: '',
  address: '',
  region: '',
  zone: '',
  woreda: '',
  kebele: '',
  size_hectares: '',
  farm_type: '',
  latitude: '',
  longitude: '',
})

// Fetch farms on component mount
onMounted(async () => {
  await fetchFarms()
})

// Fetch all farms for the authenticated farmer
const fetchFarms = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/farmer/farms', {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json',
      },
    })

    if (!response.ok) {
      throw new Error('Failed to fetch farms')
    }

    const data = await response.json()
    farms.value = data.data.data || data.data || []
  } catch (error) {
    console.error('Error fetching farms:', error)
  } finally {
    loading.value = false
  }
}

// Open add farm dialog
const openAddFarmDialog = () => {
  resetForm()
  isEditingFarm.value = false
  editingFarmId.value = null
  showAddFarmModal.value = true
}

// Open edit farm dialog
const editFarm = (farm) => {
  farmForm.value = {
    name: farm.name,
    description: farm.description || '',
    address: farm.address,
    region: farm.region,
    zone: farm.zone,
    woreda: farm.woreda,
    kebele: farm.kebele || '',
    size_hectares: farm.size_hectares,
    farm_type: farm.farm_type,
    latitude: farm.latitude || '',
    longitude: farm.longitude || '',
  }
  isEditingFarm.value = true
  editingFarmId.value = farm.id
  showAddFarmModal.value = true
}

// Close add farm dialog
const closeAddFarmDialog = () => {
  showAddFarmModal.value = false
  resetForm()
}

// Reset form to initial state
const resetForm = () => {
  farmForm.value = {
    name: '',
    description: '',
    address: '',
    region: '',
    zone: '',
    woreda: '',
    kebele: '',
    size_hectares: '',
    farm_type: '',
    latitude: '',
    longitude: '',
  }
  formErrors.value = {}
}

// Submit farm form
const submitFarmForm = async () => {
  try {
    submitting.value = true
    formErrors.value = {}

    const payload = {
      ...farmForm.value,
      size_hectares: parseFloat(farmForm.value.size_hectares),
      latitude: farmForm.value.latitude ? parseFloat(farmForm.value.latitude) : null,
      longitude: farmForm.value.longitude ? parseFloat(farmForm.value.longitude) : null,
    }

    const url = isEditingFarm.value
      ? `/api/farmer/farms/${editingFarmId.value}`
      : '/api/farmer/farms'

    const method = isEditingFarm.value ? 'PUT' : 'POST'

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

    // Success
    showAddFarmModal.value = false
    resetForm()
    await fetchFarms()

    // Show success message
    const message = isEditingFarm.value ? 'Farm updated successfully!' : 'Farm created successfully!'
    console.log(message)
  } catch (error) {
    console.error('Error submitting farm form:', error)
    formErrors.value = { general: 'An error occurred while saving the farm' }
  } finally {
    submitting.value = false
  }
}

// Utility function to capitalize first letter
const capitalizeFirstLetter = (string) => {
  if (!string) return ''
  return string.charAt(0).toUpperCase() + string.slice(1)
}

// Logout handler
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

.page-header {
  margin-bottom: 30px;
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

.content-section {
  background: white;
  border-radius: 8px;
  padding: 25px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}

.section-header h2 {
  font-size: 20px;
  font-weight: bold;
  color: #333;
}

.btn-primary {
  background-color: #10b981;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
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

.loading-message,
.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.farms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.farm-card {
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  transition: all 0.3s;
}

.farm-card:hover {
  border-color: #10b981;
  box-shadow: 0 4px 8px rgba(16, 185, 129, 0.1);
}

.farm-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.farm-header h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

.badge {
  display: inline-block;
  background-color: #dbeafe;
  color: #1e40af;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.farm-details {
  margin-bottom: 15px;
}

.farm-details p {
  margin: 8px 0;
  color: #666;
  font-size: 14px;
}

.farm-actions {
  display: flex;
  gap: 10px;
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
}

.btn-edit {
  background-color: #3b82f6;
  color: white;
}

.btn-edit:hover {
  background-color: #2563eb;
}

.btn-view {
  background-color: #8b5cf6;
  color: white;
}

.btn-view:hover {
  background-color: #7c3aed;
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

/* Form Styles */
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
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.coordinates-section {
  margin-top: 25px;
  padding-top: 15px;
  border-top: 1px solid #e5e7eb;
}

.coordinates-section h4 {
  margin: 0 0 15px 0;
  color: #333;
  font-size: 14px;
  font-weight: 600;
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

.form-actions button {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

/* Responsive */
@media (max-width: 768px) {
  .farmer-page {
    margin-left: 0;
  }

  .page-header h1 {
    font-size: 20px;
  }

  .farms-grid {
    grid-template-columns: 1fr;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .btn-primary {
    width: 100%;
  }

  .modal-dialog {
    width: 95%;
    max-height: 95vh;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions button {
    width: 100%;
  }
}
</style>
