<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Transport Requests</h1>
          <p>Request and track product transportation services</p>
        </div>
        <button class="btn-primary btn-large" @click="openNewRequestDialog">
          <i class="fas fa-plus"></i> New Request
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon total">
            <i class="fas fa-box-open"></i>
          </div>
          <div class="stat-content">
            <h3>Total Requests</h3>
            <p class="stat-value">{{ transportRequests.length }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon pending">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-content">
            <h3>Pending</h3>
            <p class="stat-value">{{ pendingCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon transit">
            <i class="fas fa-truck"></i>
          </div>
          <div class="stat-content">
            <h3>In Transit</h3>
            <p class="stat-value">{{ inTransitCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon completed">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-content">
            <h3>Delivered</h3>
            <p class="stat-value">{{ deliveredCount }}</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="controls-section">
        <div class="filter-group">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search requests..."
            class="search-input"
          />
          <select v-model="statusFilter" class="status-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="assigned">Assigned</option>
            <option value="in_transit">In Transit</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <!-- Transport Requests Table -->
      <div class="content-section">
        <div v-if="loading" class="loading-state">
          <p><i class="fas fa-spinner fa-spin"></i> Loading requests...</p>
        </div>

        <div v-else-if="filteredRequests.length === 0" class="empty-state">
          <i class="fas fa-inbox"></i>
          <h3>No transport requests</h3>
          <p>{{ transportRequests.length === 0 ? 'Create your first transport request' : 'No requests match your filters' }}</p>
        </div>

        <table v-else class="data-table">
          <thead>
            <tr>
              <th>Request ID</th>
              <th>Product</th>
              <th>Quantity</th>
              <th>From</th>
              <th>To</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="request in filteredRequests" :key="request.id">
              <td><strong>#{{ request.id }}</strong></td>
              <td>{{ request.product_name }}</td>
              <td>{{ request.quantity }} {{ request.unit }}</td>
              <td>{{ request.pickup_location }}</td>
              <td>{{ request.delivery_location }}</td>
              <td>
                <span class="status-badge" :class="`status-${request.status}`">
                  {{ formatStatus(request.status) }}
                </span>
              </td>
              <td>{{ formatDate(request.created_at) }}</td>
              <td class="actions">
                <button class="btn-icon btn-track" @click="trackRequest(request)" title="Track">
                  <i class="fas fa-map-marker-alt"></i>
                </button>
                <button v-if="request.status === 'pending'" class="btn-icon btn-edit" @click="editRequest(request)" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn-icon btn-delete" @click="deleteRequest(request.id)" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- New Request Modal -->
      <div v-if="showNewRequestDialog" class="modal-overlay" @click="closeNewRequestDialog">
        <div class="modal-dialog" @click.stop>
          <div class="modal-header">
            <h2>{{ editingRequest ? 'Edit Transport Request' : 'Create New Transport Request' }}</h2>
            <button class="close-btn" @click="closeNewRequestDialog">&times;</button>
          </div>

          <div class="modal-content">
            <form @submit.prevent="submitRequest">
              <!-- Product Selection -->
              <div class="form-group">
                <label for="req-product">Product Name *</label>
                <input
                  id="req-product"
                  v-model="requestForm.product_name"
                  type="text"
                  placeholder="e.g., Organic Maize"
                  required
                />
                <span v-if="formErrors.product_name" class="error-text">{{ formErrors.product_name }}</span>
              </div>

              <!-- Quantity -->
              <div class="form-row">
                <div class="form-group">
                  <label for="req-quantity">Quantity *</label>
                  <input
                    id="req-quantity"
                    v-model.number="requestForm.quantity"
                    type="number"
                    placeholder="100"
                    step="0.1"
                    min="0"
                    required
                  />
                  <span v-if="formErrors.quantity" class="error-text">{{ formErrors.quantity }}</span>
                </div>

                <div class="form-group">
                  <label for="req-unit">Unit *</label>
                  <select id="req-unit" v-model="requestForm.unit" required>
                    <option value="kg">kg</option>
                    <option value="tonnes">tonnes</option>
                    <option value="bags">bags</option>
                    <option value="bundles">bundles</option>
                    <option value="pieces">pieces</option>
                  </select>
                </div>
              </div>

              <!-- Pickup Location -->
              <div class="form-group">
                <label for="req-pickup">Pickup Location *</label>
                <input
                  id="req-pickup"
                  v-model="requestForm.pickup_location"
                  type="text"
                  placeholder="Your farm address"
                  required
                />
                <span v-if="formErrors.pickup_location" class="error-text">{{ formErrors.pickup_location }}</span>
              </div>

              <!-- Delivery Location -->
              <div class="form-group">
                <label for="req-delivery">Delivery Location *</label>
                <input
                  id="req-delivery"
                  v-model="requestForm.delivery_location"
                  type="text"
                  placeholder="Market or buyer location"
                  required
                />
                <span v-if="formErrors.delivery_location" class="error-text">{{ formErrors.delivery_location }}</span>
              </div>

              <!-- Preferred Date -->
              <div class="form-group">
                <label for="req-date">Preferred Pickup Date *</label>
                <input
                  id="req-date"
                  v-model="requestForm.preferred_date"
                  type="date"
                  required
                />
                <span v-if="formErrors.preferred_date" class="error-text">{{ formErrors.preferred_date }}</span>
              </div>

              <!-- Special Instructions -->
              <div class="form-group">
                <label for="req-notes">Special Instructions</label>
                <textarea
                  id="req-notes"
                  v-model="requestForm.notes"
                  placeholder="Any special handling requirements..."
                  rows="3"
                ></textarea>
              </div>

              <!-- Form Actions -->
              <div class="form-actions">
                <button type="button" class="btn-secondary" @click="closeNewRequestDialog">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="submitting">
                  {{ submitting ? 'Saving...' : (editingRequest ? 'Update Request' : 'Create Request') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Tracking Modal -->
      <div v-if="showTrackingModal" class="modal-overlay" @click="closeTrackingModal">
        <div class="modal-dialog" @click.stop>
          <div class="modal-header">
            <h2>Track Request #{{ selectedRequest?.id }}</h2>
            <button class="close-btn" @click="closeTrackingModal">&times;</button>
          </div>

          <div class="modal-content">
            <div class="tracking-info">
              <div class="tracking-item">
                <span class="label">Product:</span>
                <span class="value">{{ selectedRequest?.product_name }}</span>
              </div>
              <div class="tracking-item">
                <span class="label">Quantity:</span>
                <span class="value">{{ selectedRequest?.quantity }} {{ selectedRequest?.unit }}</span>
              </div>
              <div class="tracking-item">
                <span class="label">From:</span>
                <span class="value">{{ selectedRequest?.pickup_location }}</span>
              </div>
              <div class="tracking-item">
                <span class="label">To:</span>
                <span class="value">{{ selectedRequest?.delivery_location }}</span>
              </div>
              <div class="tracking-item">
                <span class="label">Status:</span>
                <span class="value">
                  <span class="status-badge" :class="`status-${selectedRequest?.status}`">
                    {{ formatStatus(selectedRequest?.status) }}
                  </span>
                </span>
              </div>
              <div class="tracking-item">
                <span class="label">Created:</span>
                <span class="value">{{ formatDate(selectedRequest?.created_at) }}</span>
              </div>
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
const transportRequests = ref([])
const loading = ref(true)
const searchQuery = ref('')
const statusFilter = ref('')
const showNewRequestDialog = ref(false)
const showTrackingModal = ref(false)
const editingRequest = ref(null)
const selectedRequest = ref(null)
const submitting = ref(false)
const formErrors = ref({})

// Form
const requestForm = ref({
  product_name: '',
  quantity: '',
  unit: 'kg',
  pickup_location: '',
  delivery_location: '',
  preferred_date: '',
  notes: '',
})

// Computed
const filteredRequests = computed(() => {
  return transportRequests.value.filter(req => {
    const matchesSearch = 
      req.product_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      req.pickup_location.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      req.delivery_location.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesStatus = !statusFilter.value || req.status === statusFilter.value
    return matchesSearch && matchesStatus
  })
})

const pendingCount = computed(() => transportRequests.value.filter(r => r.status === 'pending').length)
const inTransitCount = computed(() => transportRequests.value.filter(r => r.status === 'in_transit').length)
const deliveredCount = computed(() => transportRequests.value.filter(r => r.status === 'delivered').length)

// Lifecycle
onMounted(async () => {
  await fetchTransportRequests()
})

// API Functions
const fetchTransportRequests = async () => {
  try {
    loading.value = true
    const res = await fetch('/api/farmer/transport-requests', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    
    if (!res.ok) {
      transportRequests.value = []
      return
    }

    const data = await res.json()
    transportRequests.value = data.data?.data || data.data || []
  } catch (error) {
    console.error('Error fetching requests:', error)
    transportRequests.value = []
  } finally {
    loading.value = false
  }
}

// Modal Functions
const openNewRequestDialog = () => {
  resetRequestForm()
  editingRequest.value = null
  showNewRequestDialog.value = true
}

const closeNewRequestDialog = () => {
  showNewRequestDialog.value = false
  resetRequestForm()
}

const resetRequestForm = () => {
  requestForm.value = {
    product_name: '',
    quantity: '',
    unit: 'kg',
    pickup_location: '',
    delivery_location: '',
    preferred_date: new Date().toISOString().split('T')[0],
    notes: '',
  }
  formErrors.value = {}
}

const editRequest = (request) => {
  editingRequest.value = request
  requestForm.value = {
    product_name: request.product_name,
    quantity: request.quantity,
    unit: request.unit,
    pickup_location: request.pickup_location,
    delivery_location: request.delivery_location,
    preferred_date: request.preferred_date,
    notes: request.notes || '',
  }
  showNewRequestDialog.value = true
}

const trackRequest = (request) => {
  selectedRequest.value = request
  showTrackingModal.value = true
}

const closeTrackingModal = () => {
  showTrackingModal.value = false
  selectedRequest.value = null
}

// Form Submission
const submitRequest = async () => {
  try {
    submitting.value = true
    formErrors.value = {}

    const payload = {
      ...requestForm.value,
      quantity: parseFloat(requestForm.value.quantity),
    }

    const url = editingRequest.value 
      ? `/api/farmer/transport-requests/${editingRequest.value.id}`
      : '/api/farmer/transport-requests'

    const method = editingRequest.value ? 'PUT' : 'POST'

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
      formErrors.value = data.errors || { general: data.message || 'Error occurred' }
      return
    }

    showNewRequestDialog.value = false
    resetRequestForm()
    await fetchTransportRequests()
  } catch (error) {
    console.error('Error:', error)
    formErrors.value = { general: 'An error occurred' }
  } finally {
    submitting.value = false
  }
}

const deleteRequest = async (id) => {
  if (!confirm('Delete this transport request?')) return

  try {
    const response = await fetch(`/api/farmer/transport-requests/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })

    if (response.ok) {
      await fetchTransportRequests()
    }
  } catch (error) {
    console.error('Error deleting:', error)
  }
}

// Utility Functions
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const formatStatus = (status) => {
  const statuses = {
    pending: 'Pending',
    assigned: 'Assigned',
    in_transit: 'In Transit',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
  }
  return statuses[status] || status
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout { display: flex; height: 100vh; }
.farmer-page { margin-left: 260px; flex: 1; overflow-y: auto; background-color: #f5f5f5; padding: 20px; }

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
  display: inline-flex;
  align-items: center;
  gap: 8px;
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

.stat-card:hover { transform: translateY(-2px); }

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

.stat-icon.total { background-color: #10b981; }
.stat-icon.pending { background-color: #f59e0b; }
.stat-icon.transit { background-color: #3b82f6; }
.stat-icon.completed { background-color: #8b5cf6; }

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

.search-input { flex: 1; min-width: 250px; }

/* Content Section */
.content-section { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }

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
.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background-color: #f9fafb;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
}

.data-table td {
  padding: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.data-table tbody tr:hover { background-color: #f9fafb; }

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending { background-color: #fef3c7; color: #92400e; }
.status-badge.status-assigned { background-color: #dbeafe; color: #1e40af; }
.status-badge.status-in_transit { background-color: #e0e7ff; color: #3730a3; }
.status-badge.status-delivered { background-color: #d1fae5; color: #065f46; }
.status-badge.status-cancelled { background-color: #fee2e2; color: #991b1b; }

.actions {
  display: flex;
  gap: 6px;
  justify-content: center;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  font-size: 14px;
}

.btn-track {
  background-color: #e0e7ff;
  color: #3b82f6;
}

.btn-track:hover {
  background-color: #3b82f6;
  color: white;
}

.btn-edit {
  background-color: #fef3c7;
  color: #f59e0b;
}

.btn-edit:hover {
  background-color: #f59e0b;
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
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover:not(:disabled) { background-color: #059669; }
.btn-primary:disabled { background-color: #9ca3af; cursor: not-allowed; }

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

.btn-secondary:hover { background-color: #4b5563; }

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

.close-btn:hover { color: #333; }

.modal-content { padding: 25px; }

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

.form-group textarea { resize: vertical; }

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
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

/* Tracking Info */
.tracking-info {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.tracking-item {
  display: flex;
  justify-content: space-between;
  padding: 12px;
  background: #f9fafb;
  border-radius: 4px;
}

.tracking-item .label {
  font-weight: 600;
  color: #666;
}

.tracking-item .value {
  color: #333;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .farmer-page { margin-left: 0; }
  .page-header { flex-direction: column; align-items: stretch; }
  .btn-large { width: 100%; text-align: center; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .filter-group { flex-direction: column; }
  .search-input { width: 100%; }
  .form-row { grid-template-columns: 1fr; }
  .modal-dialog { width: 95%; max-height: 95vh; }
}
</style>
