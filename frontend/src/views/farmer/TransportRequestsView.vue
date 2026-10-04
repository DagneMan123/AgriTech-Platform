<template>
  <div class="transport-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="transport-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Transport Requests</h1>
          <p>Request and manage transportation for your farm produce</p>
        </div>
        <button @click="showNewRequest = true" class="btn-new">
          <Plus size="16" />
          <span>New Request</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading requests...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchRequests" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="transport-content">
        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <Send size="20" class="stat-icon pending" />
            <div>
              <span class="stat-label">Pending</span>
              <span class="stat-value">{{ stats.pending }}</span>
            </div>
          </div>
          <div class="stat-card">
            <Truck size="20" class="stat-icon confirmed" />
            <div>
              <span class="stat-label">Confirmed</span>
              <span class="stat-value">{{ stats.confirmed }}</span>
            </div>
          </div>
          <div class="stat-card">
            <MapPin size="20" class="stat-icon inTransit" />
            <div>
              <span class="stat-label">In Transit</span>
              <span class="stat-value">{{ stats.inTransit }}</span>
            </div>
          </div>
          <div class="stat-card">
            <CheckCircle size="20" class="stat-icon completed" />
            <div>
              <span class="stat-label">Completed</span>
              <span class="stat-value">{{ stats.completed }}</span>
            </div>
          </div>
        </div>

        <!-- Requests Table -->
        <div class="table-section">
          <div class="table-header">
            <h3>Recent Requests</h3>
            <div class="table-controls">
              <select v-model="filterStatus" class="filter-select">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="in-transit">In Transit</option>
                <option value="completed">Completed</option>
              </select>
            </div>
          </div>

          <div v-if="filteredRequests.length > 0" class="table-wrapper">
            <table class="requests-table">
              <thead>
                <tr>
                  <th>Request ID</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="request in filteredRequests" :key="request.id" class="table-row">
                  <td class="td-id">{{ request.id }}</td>
                  <td class="td-from">{{ request.from }}</td>
                  <td class="td-to">{{ request.to }}</td>
                  <td class="td-product">{{ request.product }}</td>
                  <td class="td-quantity">{{ request.quantity }} {{ request.unit }}</td>
                  <td class="td-date">{{ formatDate(request.requestDate) }}</td>
                  <td class="td-status">
                    <span class="status-badge" :class="`status-${request.status}`">
                      {{ capitalize(request.status) }}
                    </span>
                  </td>
                  <td class="td-actions">
                    <button @click="viewDetails(request)" class="action-btn">
                      <Eye size="16" />
                    </button>
                    <button v-if="request.status === 'pending'" @click="editRequest(request)" class="action-btn">
                      <Edit size="16" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="empty-state">
            <Truck size="48" class="empty-icon" />
            <p>No transport requests</p>
          </div>
        </div>

        <!-- New Request Modal -->
        <div v-if="showNewRequest" class="modal-overlay" @click="showNewRequest = false">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Create Transport Request</h2>
              <button @click="showNewRequest = false" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="submitRequest" class="form">
                <div class="form-group">
                  <label>Pickup Location</label>
                  <input v-model="newRequest.from" type="text" required placeholder="Farm/Location" class="form-control" />
                </div>

                <div class="form-group">
                  <label>Delivery Location</label>
                  <input v-model="newRequest.to" type="text" required placeholder="Destination" class="form-control" />
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label>Product</label>
                    <input v-model="newRequest.product" type="text" required placeholder="Product name" class="form-control" />
                  </div>
                  <div class="form-group">
                    <label>Quantity</label>
                    <div class="input-group">
                      <input v-model.number="newRequest.quantity" type="number" required class="form-control" />
                      <select v-model="newRequest.unit" class="form-control">
                        <option>kg</option>
                        <option>tons</option>
                        <option>bags</option>
                        <option>crates</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label>Requested Date</label>
                  <input v-model="newRequest.date" type="date" required class="form-control" />
                </div>

                <div class="form-group">
                  <label>Special Instructions</label>
                  <textarea v-model="newRequest.instructions" placeholder="Any special handling..." class="form-control" rows="3"></textarea>
                </div>

                <div class="form-actions">
                  <button type="button" @click="showNewRequest = false" class="btn-secondary">Cancel</button>
                  <button type="submit" class="btn-primary">Submit Request</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Details Modal -->
        <div v-if="selectedRequest" class="modal-overlay" @click="selectedRequest = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Request {{ selectedRequest.id }}</h2>
              <button @click="selectedRequest = null" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="label">From:</span>
                  <span class="value">{{ selectedRequest.from }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">To:</span>
                  <span class="value">{{ selectedRequest.to }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Product:</span>
                  <span class="value">{{ selectedRequest.product }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Quantity:</span>
                  <span class="value">{{ selectedRequest.quantity }} {{ selectedRequest.unit }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Date:</span>
                  <span class="value">{{ formatDate(selectedRequest.requestDate) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Status:</span>
                  <span class="status-badge" :class="`status-${selectedRequest.status}`">
                    {{ capitalize(selectedRequest.status) }}
                  </span>
                </div>
              </div>

              <div v-if="selectedRequest.driver" class="driver-info">
                <h3>Driver Information</h3>
                <p><strong>Name:</strong> {{ selectedRequest.driver }}</p>
                <p><strong>Phone:</strong> {{ selectedRequest.driverPhone }}</p>
                <p><strong>Vehicle:</strong> {{ selectedRequest.vehicle }}</p>
              </div>

              <div v-if="selectedRequest.instructions" class="instructions">
                <h3>Special Instructions</h3>
                <p>{{ selectedRequest.instructions }}</p>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedRequest = null" class="btn-secondary">Close</button>
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
import { useTheme } from '@/composables/useTheme'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import {
  Plus, Send, Truck, MapPin, CheckCircle, AlertCircle, RotateCcw, X, Eye, Edit
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()
const { isDark, isLight } = useTheme()

const loading = ref(false)
const error = ref(null)
const showNewRequest = ref(false)
const selectedRequest = ref(null)
const filterStatus = ref('')

const newRequest = ref({
  from: '',
  to: '',
  product: '',
  quantity: null,
  unit: 'kg',
  date: '',
  instructions: ''
})

const mockRequests = [
  {
    id: 'TRN-001',
    from: 'Green Valley Farm',
    to: 'City Market, Central Hub',
    product: 'Tomatoes',
    quantity: 500,
    unit: 'kg',
    requestDate: '2026-09-01',
    status: 'completed',
    driver: 'John Smith',
    driverPhone: '+1-555-0401',
    vehicle: 'TRK-2024-001',
    instructions: 'Handle with care, keep cool'
  },
  {
    id: 'TRN-002',
    from: 'Sunny Acres',
    to: 'Regional Distribution Center',
    product: 'Corn',
    quantity: 1000,
    unit: 'kg',
    requestDate: '2026-09-02',
    status: 'in-transit',
    driver: 'Maria Garcia',
    driverPhone: '+1-555-0402',
    vehicle: 'TRK-2024-002',
    instructions: 'Deliver by 5 PM'
  },
  {
    id: 'TRN-003',
    from: 'Harvest Farm',
    to: 'Wholesale Center',
    product: 'Lettuce',
    quantity: 300,
    unit: 'kg',
    requestDate: '2026-09-03',
    status: 'confirmed',
    driver: 'Robert Wilson',
    driverPhone: '+1-555-0403',
    vehicle: 'TRK-2024-003',
    instructions: ''
  },
  {
    id: 'TRN-004',
    from: 'My Farm',
    to: 'Export Terminal',
    product: 'Potatoes',
    quantity: 2000,
    unit: 'kg',
    requestDate: '2026-09-04',
    status: 'pending',
    driver: null,
    driverPhone: null,
    vehicle: null,
    instructions: 'Urgent delivery needed'
  }
]

const requests = ref(mockRequests)

const filteredRequests = computed(() => {
  if (!filterStatus.value) return requests.value
  return requests.value.filter(r => r.status === filterStatus.value)
})

const stats = computed(() => ({
  pending: requests.value.filter(r => r.status === 'pending').length,
  confirmed: requests.value.filter(r => r.status === 'confirmed').length,
  inTransit: requests.value.filter(r => r.status === 'in-transit').length,
  completed: requests.value.filter(r => r.status === 'completed').length
}))

const fetchRequests = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const formatDate = (date) => new Date(date).toLocaleDateString()
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)
const viewDetails = (request) => { selectedRequest.value = request }
const editRequest = (request) => { alert('Edit functionality for: ' + request.id) }
const submitRequest = () => {
  alert('Transport request submitted successfully')
  showNewRequest.value = false
  newRequest.value = { from: '', to: '', product: '', quantity: null, unit: 'kg', date: '', instructions: '' }
}
const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchRequests() })
</script>

<style scoped>
.transport-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.transport-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.page-header {
  background: white;
  padding: 25px 30px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.header-content h1 {
  font-size: 28px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.header-content p {
  color: #6b7280;
  font-size: 14px;
  margin: 0;
}

.btn-new {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-new:hover {
  background: #2563eb;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  gap: 20px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-container {
  background: #fee2e2;
  border: 2px solid #fca5a5;
  border-radius: 12px;
  padding: 40px;
  margin: 30px;
  text-align: center;
}

.error-icon {
  color: #dc2626;
  margin-bottom: 15px;
}

.error-message {
  color: #991b1b;
  font-size: 16px;
  margin-bottom: 20px;
}

.btn-retry {
  background: #dc2626;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.transport-content {
  padding: 30px;
  flex: 1;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.stat-icon {
  color: #d1d5db;
  flex-shrink: 0;
}

.stat-icon.pending { color: #f59e0b; }
.stat-icon.confirmed { color: #3b82f6; }
.stat-icon.inTransit { color: #8b5cf6; }
.stat-icon.completed { color: #10b981; }

.stat-label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
}

.table-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.table-header {
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-header h3 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 13px;
  color: #4b5563;
  background: white;
  cursor: pointer;
}

.table-wrapper {
  overflow-x: auto;
}

.requests-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.requests-table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.requests-table th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 12px;
}

.requests-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
  transition: background 0.2s;
}

.requests-table tbody tr:hover {
  background: #f9fafb;
}

.table-row td {
  padding: 16px;
  color: #1f2937;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-confirmed { background: #dbeafe; color: #1e40af; }
.status-in-transit { background: #e9d5ff; color: #6b21a8; }
.status-completed { background: #d1fae5; color: #065f46; }

.td-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  background: none;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 6px 8px;
  cursor: pointer;
  color: #6b7280;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
  background: #eff6ff;
}

.empty-state {
  padding: 60px 20px;
  text-align: center;
}

.empty-icon {
  color: #d1d5db;
  margin-bottom: 16px;
}

.empty-state p {
  font-size: 16px;
  font-weight: 600;
  color: #6b7280;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #6b7280;
  padding: 0;
}

.modal-body {
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #4b5563;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  color: #1f2937;
  font-family: inherit;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.input-group {
  display: flex;
  gap: 8px;
}

.input-group .form-control {
  flex: 1;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.btn-primary,
.btn-secondary {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item .label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.detail-item .value {
  font-size: 14px;
  color: #1f2937;
}

.driver-info,
.instructions {
  margin-bottom: 20px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.driver-info h3,
.instructions h3 {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.driver-info p,
.instructions p {
  margin: 8px 0;
  font-size: 13px;
  color: #4b5563;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

@media (max-width: 768px) {
  .transport-container {
    margin-left: 0;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-new {
    width: 100%;
    justify-content: center;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .requests-table {
    font-size: 12px;
  }

  .requests-table th,
  .table-row td {
    padding: 12px 8px;
  }
}

/* Dark Mode Styles */

.transport-layout.dark {
  background-color: #0b0f17;
}

.transport-layout.dark .transport-container {
  background-color: #0b0f17;
}

/* Page Header - Dark Mode */
.transport-layout.dark .page-header {
  background: #131b2e;
  border-bottom-color: #1e293b;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.transport-layout.dark .header-content h1 {
  color: #f1f5f9;
}

.transport-layout.dark .header-content p {
  color: #cbd5e1;
}

/* New Request Button - Dark Mode */
.transport-layout.dark .btn-new {
  background: #3b82f6;
}

.transport-layout.dark .btn-new:hover {
  background: #2563eb;
}

/* Loading State - Dark Mode */
.transport-layout.dark .loading-container {
  color: #cbd5e1;
}

.transport-layout.dark .spinner {
  border-color: #2d3f52;
  border-top-color: #3b82f6;
}

/* Error Container - Dark Mode */
.transport-layout.dark .error-container {
  background: #7f1d1d;
  border-color: #b91c1c;
}

.transport-layout.dark .error-icon {
  color: #f87171;
}

.transport-layout.dark .error-message {
  color: #fca5a5;
}

.transport-layout.dark .btn-retry {
  background: #ef4444;
}

.transport-layout.dark .btn-retry:hover {
  background: #dc2626;
}

/* Stats Cards - Dark Mode */
.transport-layout.dark .stat-card {
  background: #1a2338;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.transport-layout.dark .stat-label {
  color: #94a3b8;
}

.transport-layout.dark .stat-value {
  color: #f1f5f9;
}

/* Table Section - Dark Mode */
.transport-layout.dark .table-section {
  background: #131b2e;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  border: 1px solid #1e293b;
}

.transport-layout.dark .table-header {
  border-bottom-color: #2d3f52;
}

.transport-layout.dark .table-header h3 {
  color: #f1f5f9;
}

/* Filter Select - Dark Mode */
.transport-layout.dark .filter-select {
  background-color: #1a2338;
  color: #f3f4f6;
  border-color: #2d3f52;
}

.transport-layout.dark .filter-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Table - Dark Mode */
.transport-layout.dark .requests-table thead {
  background: #1a2338;
  border-bottom-color: #2d3f52;
}

.transport-layout.dark .requests-table th {
  color: #cbd5e1;
}

.transport-layout.dark .requests-table tbody tr {
  border-bottom-color: #2d3f52;
}

.transport-layout.dark .requests-table tbody tr:hover {
  background: #1a2338;
}

.transport-layout.dark .table-row td {
  color: #f3f4f6;
}

/* Status Badges - Dark Mode */
.transport-layout.dark .status-pending { background: #5a3a0a; color: #fbbf24; }
.transport-layout.dark .status-confirmed { background: #0f3a66; color: #60a5fa; }
.transport-layout.dark .status-in-transit { background: #3a2a5a; color: #c4b5fd; }
.transport-layout.dark .status-completed { background: #0a3f2a; color: #4ade80; }

/* Action Buttons - Dark Mode */
.transport-layout.dark .action-btn {
  border-color: #2d3f52;
  color: #94a3b8;
}

.transport-layout.dark .action-btn:hover {
  border-color: #3b82f6;
  color: #60a5fa;
  background: #0f3a66;
}

/* Empty State - Dark Mode */
.transport-layout.dark .empty-state {
  color: #cbd5e1;
}

.transport-layout.dark .empty-icon {
  color: #475569;
}

.transport-layout.dark .empty-state p {
  color: #cbd5e1;
}

/* Modal - Dark Mode */
.transport-layout.dark .modal-overlay {
  background: rgba(0, 0, 0, 0.8);
}

.transport-layout.dark .modal-content {
  background: #131b2e;
  border: 1px solid #1e293b;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.transport-layout.dark .modal-header {
  border-bottom-color: #2d3f52;
  background: #1a2338;
}

.transport-layout.dark .modal-header h2 {
  color: #f1f5f9;
}

.transport-layout.dark .btn-close {
  color: #94a3b8;
}

.transport-layout.dark .btn-close:hover {
  color: #cbd5e1;
}

/* Form Elements - Dark Mode */
.transport-layout.dark .form-control {
  background-color: #1a2338;
  color: #f3f4f6;
  border-color: #2d3f52;
}

.transport-layout.dark .form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.transport-layout.dark .form-group label {
  color: #cbd5e1;
}

/* Form Actions - Dark Mode */
.transport-layout.dark .btn-primary {
  background: #3b82f6;
}

.transport-layout.dark .btn-primary:hover {
  background: #2563eb;
}

.transport-layout.dark .btn-secondary {
  background: #2d3f52;
  color: #cbd5e1;
  border-color: #475569;
}

.transport-layout.dark .btn-secondary:hover {
  background: #475569;
}

/* Detail Grid - Dark Mode */
.transport-layout.dark .detail-grid {
  background: #1a2338;
  border-color: #2d3f52;
}

.transport-layout.dark .detail-item .label {
  color: #94a3b8;
}

.transport-layout.dark .detail-item .value {
  color: #f1f5f9;
}

/* Driver Info & Instructions - Dark Mode */
.transport-layout.dark .driver-info,
.transport-layout.dark .instructions {
  background: #1a2338;
  border-color: #2d3f52;
}

.transport-layout.dark .driver-info h3,
.transport-layout.dark .instructions h3 {
  color: #f1f5f9;
}

.transport-layout.dark .driver-info p,
.transport-layout.dark .instructions p {
  color: #cbd5e1;
}

/* Modal Footer - Dark Mode */
.transport-layout.dark .modal-footer {
  border-top-color: #2d3f52;
  background: #1a2338;
}
</style>
