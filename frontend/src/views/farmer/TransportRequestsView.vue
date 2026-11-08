<template>
  <div class="transport-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="transport-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Transport Requests</h1>
          <p>Request and manage produce transportation, warehouse logistics, and delivery tracking</p>
        </div>
        <button @click="openCreateModal" class="btn-new" title="Create new transport request">
          <Plus size="16" />
          <span>New Request</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading transport requests from database...</p>
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
        <!-- 4-Stage Lifecycle KPI Grid -->
        <div class="stats-grid">
          <div class="stat-card cursor-pointer" @click="filterStatus = 'pending'">
            <Send size="20" class="stat-icon pending" />
            <div>
              <span class="stat-label">Stage 1: Pending</span>
              <span class="stat-value">{{ stats.pending }}</span>
            </div>
          </div>
          <div class="stat-card cursor-pointer" @click="filterStatus = 'confirmed'">
            <Truck size="20" class="stat-icon confirmed" />
            <div>
              <span class="stat-label">Stage 2: Confirmed</span>
              <span class="stat-value">{{ stats.confirmed }}</span>
            </div>
          </div>
          <div class="stat-card cursor-pointer" @click="filterStatus = 'shipped'">
            <MapPin size="20" class="stat-icon inTransit" />
            <div>
              <span class="stat-label">Stage 3: Shipped</span>
              <span class="stat-value">{{ stats.inTransit }}</span>
            </div>
          </div>
          <div class="stat-card cursor-pointer" @click="filterStatus = 'completed'">
            <CheckCircle size="20" class="stat-icon completed" />
            <div>
              <span class="stat-label">Stage 4: Delivered &amp; Paid</span>
              <span class="stat-value">{{ stats.completed }}</span>
            </div>
          </div>
        </div>

        <!-- Requests Table Section -->
        <div class="table-section">
          <div class="table-header">
            <div class="table-title-area">
              <h3>Recent Transport Requests</h3>
              <span class="count-pill">{{ filteredRequests.length }} requests</span>
            </div>
            <div class="table-controls">
              <!-- Search Input -->
              <div class="search-box">
                <Search size="14" class="search-icon" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search produce, route, ID..."
                  class="search-input"
                />
              </div>

              <!-- Status Filter Dropdown -->
              <select v-model="filterStatus" class="filter-select">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed / Assigned</option>
                <option value="in-transit">In Transit</option>
                <option value="completed">Completed / Delivered</option>
              </select>
            </div>
          </div>

          <!-- Table Content -->
          <div v-if="filteredRequests.length > 0" class="table-wrapper">
            <table class="requests-table">
              <thead>
                <tr>
                  <th>Request ID</th>
                  <th>Produce</th>
                  <th>Quantity</th>
                  <th>Pickup (From)</th>
                  <th>Destination (To)</th>
                  <th>Date</th>
                  <th>Transporter</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="request in filteredRequests" :key="request.rawId" class="table-row">
                  <td class="td-id">
                    <span class="id-badge">{{ request.id }}</span>
                  </td>
                  <td class="td-product font-semibold">{{ request.product }}</td>
                  <td class="td-quantity">
                    <span class="qty-num">{{ request.quantity }}</span>
                    <span class="qty-unit">{{ request.unit }}</span>
                  </td>
                  <td class="td-from" :title="request.from">{{ request.from }}</td>
                  <td class="td-to" :title="request.to">{{ request.to }}</td>
                  <td class="td-date">{{ formatDate(request.requestDate) }}</td>
                  <td class="td-driver">
                    <div v-if="request.driver" class="driver-badge">
                      <Truck size="13" class="driver-icon" />
                      <span>{{ request.driver }}</span>
                    </div>
                    <span v-else class="text-unassigned">Awaiting dispatch</span>
                  </td>
                  <td class="td-status">
                    <span class="status-badge" :class="`status-${request.status}`">
                      {{ capitalize(request.status) }}
                    </span>
                  </td>
                  <td class="td-actions">
                    <button @click="viewDetails(request)" class="action-btn view-btn" title="View Full Details">
                      <Eye size="15" />
                    </button>

                    <!-- Stage 1 -> Stage 2: Confirm & Assign Transporter -->
                    <button
                      v-if="request.status === 'pending'"
                      @click="advanceStatus(request, 'confirmed')"
                      class="action-btn text-blue-600 hover:text-blue-700 border-blue-200 bg-blue-50/60 dark:bg-blue-950/30"
                      title="Stage 2: Approve & Assign Transporter"
                    >
                      <ShieldCheck size="15" />
                    </button>

                    <!-- Stage 2 -> Stage 3: Ship / Dispatch Produce -->
                    <button
                      v-if="request.status === 'confirmed'"
                      @click="advanceStatus(request, 'shipped')"
                      class="action-btn text-purple-600 hover:text-purple-700 border-purple-200 bg-purple-50/60 dark:bg-purple-950/30"
                      title="Stage 3: Dispatch & Ship Produce"
                    >
                      <Truck size="15" />
                    </button>

                    <!-- Stage 3 -> Stage 4: Mark Delivered -->
                    <button
                      v-if="request.status === 'shipped' || request.status === 'in-transit'"
                      @click="advanceStatus(request, 'delivered')"
                      class="action-btn text-emerald-600 hover:text-emerald-700 border-emerald-200 bg-emerald-50/60 dark:bg-emerald-950/30"
                      title="Stage 4: Confirm Arrival & Mark Delivered"
                    >
                      <CheckCircle2 size="15" />
                    </button>

                    <button
                      v-if="request.status === 'pending'"
                      @click="editRequest(request)"
                      class="action-btn edit-btn"
                      title="Edit Request"
                    >
                      <Edit size="15" />
                    </button>
                    <button
                      v-if="request.status === 'pending'"
                      @click="deleteRequest(request)"
                      class="action-btn delete-btn"
                      title="Cancel Request"
                    >
                      <Trash2 size="15" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State (No requests in database) -->
          <div v-else class="empty-state">
            <div class="empty-icon-wrap">
              <Truck size="44" class="empty-icon" />
            </div>
            <h4 class="empty-title">
              {{ searchQuery || filterStatus ? 'No matching requests found' : 'No transport requests found' }}
            </h4>
            <p class="empty-desc">
              {{ searchQuery || filterStatus ? 'Try adjusting your search criteria or resetting the status filter.' : 'You have not submitted any transportation requests yet. Click "New Request" to dispatch your produce to warehouses or buyers.' }}
            </p>
            <button
              v-if="!searchQuery && !filterStatus"
              @click="openCreateModal"
              class="btn-primary empty-btn"
            >
              <Plus size="16" />
              <span>Create Transport Request</span>
            </button>
            <button
              v-else
              @click="clearFilters"
              class="btn-secondary empty-btn"
            >
              <span>Reset Filters</span>
            </button>
          </div>
        </div>

        <!-- Create / Edit Request Modal -->
        <div v-if="showNewRequest" class="modal-overlay" @click="closeModal">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>{{ editingRequest ? 'Edit Transport Request' : 'Create Transport Request' }}</h2>
              <button @click="closeModal" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="saveRequest" class="form">
                <!-- Produce Selection -->
                <div class="form-group">
                  <label>Produce Item / Commodity *</label>
                  <input
                    v-model="requestForm.product_name"
                    type="text"
                    required
                    placeholder="e.g. White Teff, Maize, Red Onion, Tomatoes"
                    class="form-control"
                  />
                  <!-- Suggestions pills -->
                  <div v-if="productSuggestions.length > 0 && !editingRequest" class="suggestion-pills">
                    <span class="suggestion-label">Quick select:</span>
                    <button
                      type="button"
                      v-for="p in productSuggestions"
                      :key="p"
                      @click="requestForm.product_name = p"
                      class="suggestion-pill"
                    >
                      {{ p }}
                    </button>
                  </div>
                </div>

                <!-- Quantity & Unit -->
                <div class="form-row">
                  <div class="form-group flex-1">
                    <label>Quantity *</label>
                    <input
                      v-model.number="requestForm.quantity"
                      type="number"
                      step="0.01"
                      min="0.01"
                      required
                      placeholder="e.g. 50"
                      class="form-control"
                    />
                  </div>
                  <div class="form-group w-36">
                    <label>Unit *</label>
                    <select v-model="requestForm.unit" class="form-control" required>
                      <option value="kg">kg</option>
                      <option value="tonnes">tonnes</option>
                      <option value="bags">bags (quintal)</option>
                      <option value="bundles">bundles</option>
                      <option value="pieces">pieces</option>
                    </select>
                  </div>
                </div>

                <!-- Locations -->
                <div class="form-group">
                  <label>Pickup Location (Farm Gate / Storehouse) *</label>
                  <input
                    v-model="requestForm.pickup_location"
                    type="text"
                    required
                    placeholder="e.g. Bishoftu Farm Depot, Oromia"
                    class="form-control"
                  />
                </div>

                <div class="form-group">
                  <label>Delivery Destination *</label>
                  <input
                    v-model="requestForm.delivery_location"
                    type="text"
                    required
                    placeholder="e.g. Kality Central Grain Warehouse, Addis Ababa"
                    class="form-control"
                  />
                  <!-- Quick destinations -->
                  <div class="suggestion-pills">
                    <span class="suggestion-label">Hubs:</span>
                    <button
                      type="button"
                      @click="requestForm.delivery_location = 'Kality Central Grain Warehouse, Addis Ababa'"
                      class="suggestion-pill"
                    >
                      Kality Central Grain Warehouse
                    </button>
                    <button
                      type="button"
                      @click="requestForm.delivery_location = 'Adama Agricultural Logistics Terminal'"
                      class="suggestion-pill"
                    >
                      Adama Logistics Terminal
                    </button>
                  </div>
                </div>

                <!-- Preferred Date -->
                <div class="form-group">
                  <label>Preferred Pickup Date *</label>
                  <input
                    v-model="requestForm.preferred_date"
                    type="date"
                    :min="todayString"
                    required
                    class="form-control"
                  />
                </div>

                <!-- Handling Instructions -->
                <div class="form-group">
                  <label>Handling Instructions & Notes (Optional)</label>
                  <textarea
                    v-model="requestForm.notes"
                    placeholder="e.g. Perishable cargo - protect from rain and direct heat. Keep bags dry."
                    class="form-control"
                    rows="3"
                  ></textarea>
                </div>

                <div class="form-actions">
                  <button type="button" @click="closeModal" class="btn-secondary" :disabled="submitting">
                    Cancel
                  </button>
                  <button type="submit" class="btn-primary" :disabled="submitting">
                    <span v-if="submitting">Saving...</span>
                    <span v-else>{{ editingRequest ? 'Update Request' : 'Submit Request' }}</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Details Modal -->
        <div v-if="selectedRequest" class="modal-overlay" @click="selectedRequest = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <div class="modal-header-info">
                <h2>Transport Request Details</h2>
                <span class="id-tag">{{ selectedRequest.id }}</span>
              </div>
              <button @click="selectedRequest = null" class="btn-close">
                <X size="20" />
              </button>
            </div>

            <div class="modal-body">
              <!-- Status Banner -->
              <div class="status-banner" :class="`banner-${selectedRequest.status}`">
                <div class="banner-icon-area">
                  <CheckCircle2 v-if="selectedRequest.status === 'completed'" size="22" />
                  <Truck v-else-if="selectedRequest.status === 'in-transit'" size="22" />
                  <ShieldCheck v-else-if="selectedRequest.status === 'confirmed'" size="22" />
                  <Clock v-else size="22" />
                </div>
                <div>
                  <div class="banner-title">Status: {{ capitalize(selectedRequest.status) }}</div>
                  <div class="banner-subtitle">
                    {{ getStatusExplanation(selectedRequest.status) }}
                  </div>
                </div>
              </div>

              <div class="detail-grid">
                <div class="detail-item">
                  <span class="label">Produce Commodity:</span>
                  <span class="value font-bold">{{ selectedRequest.product }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Quantity:</span>
                  <span class="value font-bold text-emerald-600 dark:text-emerald-400">
                    {{ selectedRequest.quantity }} {{ selectedRequest.unit }}
                  </span>
                </div>
                <div class="detail-item full-width">
                  <span class="label">Pickup Location:</span>
                  <span class="value">{{ selectedRequest.from }}</span>
                </div>
                <div class="detail-item full-width">
                  <span class="label">Delivery Destination:</span>
                  <span class="value">{{ selectedRequest.to }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Pickup Date:</span>
                  <span class="value">{{ formatDate(selectedRequest.requestDate) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Delivery Date:</span>
                  <span class="value">{{ selectedRequest.deliveryDate ? formatDate(selectedRequest.deliveryDate) : 'Pending Dispatch' }}</span>
                </div>
              </div>

              <!-- Driver / Transporter Section -->
              <div class="driver-info">
                <h3>Transporter & Vehicle Logistics</h3>
                <div v-if="selectedRequest.driver" class="driver-details">
                  <div class="info-row">
                    <span class="info-label">Transporter / Driver:</span>
                    <span class="info-val font-semibold">{{ selectedRequest.driver }}</span>
                  </div>
                  <div v-if="selectedRequest.driverPhone" class="info-row">
                    <span class="info-label">Contact Phone:</span>
                    <span class="info-val font-mono">{{ selectedRequest.driverPhone }}</span>
                  </div>
                  <div v-if="selectedRequest.vehicle" class="info-row">
                    <span class="info-label">Vehicle Plate / Type:</span>
                    <span class="info-val font-mono">{{ selectedRequest.vehicle }}</span>
                  </div>
                </div>
                <div v-else class="unassigned-notice">
                  <Clock size="16" class="text-amber-500" />
                  <span>Waiting for regional logistics coordinator to assign a transporter vehicle.</span>
                </div>
              </div>

              <!-- Special Handling Instructions -->
              <div v-if="selectedRequest.instructions" class="instructions">
                <h3>Handling Instructions</h3>
                <p>{{ selectedRequest.instructions }}</p>
              </div>
            </div>

            <div class="modal-footer flex items-center justify-between">
              <button @click="selectedRequest = null" class="btn-secondary">Close</button>
              <div class="flex items-center gap-2">
                <!-- Advance actions inside modal -->
                <button
                  v-if="selectedRequest.status === 'pending'"
                  @click="advanceStatus(selectedRequest, 'confirmed'); selectedRequest = null;"
                  class="btn-primary"
                  style="background: #3b82f6;"
                >
                  <ShieldCheck size="15" />
                  <span>Confirm &amp; Assign Transporter (Stage 2)</span>
                </button>

                <button
                  v-if="selectedRequest.status === 'confirmed'"
                  @click="advanceStatus(selectedRequest, 'shipped'); selectedRequest = null;"
                  class="btn-primary"
                  style="background: #8b5cf6;"
                >
                  <Truck size="15" />
                  <span>Dispatch &amp; Ship Produce (Stage 3)</span>
                </button>

                <button
                  v-if="selectedRequest.status === 'shipped' || selectedRequest.status === 'in-transit'"
                  @click="advanceStatus(selectedRequest, 'delivered'); selectedRequest = null;"
                  class="btn-primary"
                  style="background: #10b981;"
                >
                  <CheckCircle2 size="15" />
                  <span>Confirm Delivered &amp; Paid (Stage 4)</span>
                </button>

                <button
                  v-if="selectedRequest.status === 'pending'"
                  @click="openEditFromDetails"
                  class="btn-secondary"
                >
                  <Edit size="15" />
                  <span>Edit</span>
                </button>
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
import { useTheme } from '@/composables/useTheme'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import { farmerAPI } from '@/api/farmer'
import {
  Plus, Send, Truck, MapPin, CheckCircle, AlertCircle, RotateCcw, X, Eye, Edit,
  Trash2, Search, CheckCircle2, ShieldCheck, Clock
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()
const { isDark, isLight } = useTheme()

// State
const loading = ref(false)
const submitting = ref(false)
const error = ref(null)
const showNewRequest = ref(false)
const editingRequest = ref(null)
const selectedRequest = ref(null)
const filterStatus = ref('')
const searchQuery = ref('')
const productSuggestions = ref([])

// Form Date Helper
const todayString = new Date().toISOString().split('T')[0]

// Real Requests Data (Initialized empty, fetched from live database)
const requests = ref([])

const requestForm = ref({
  product_name: '',
  quantity: null,
  unit: 'bags',
  pickup_location: '',
  delivery_location: 'Kality Central Grain Warehouse, Addis Ababa',
  preferred_date: todayString,
  notes: ''
})

// Format database record to view model
const formatRequest = (item) => {
  const code = `TRN-${String(item.id).padStart(4, '0')}`
  const rawStatus = (item.status || 'pending').toLowerCase()
  const statusNorm = rawStatus.replace('_', '-') // handles in_transit -> in-transit

  return {
    id: code,
    rawId: item.id,
    from: item.pickup_location || 'Farm Gate',
    to: item.delivery_location || 'Central Warehouse',
    product: item.product_name || item.product?.name || 'Produce',
    quantity: Number(item.quantity || 0),
    unit: item.unit || 'bags',
    requestDate: item.pickup_date || item.created_at,
    deliveryDate: item.delivery_date,
    status: statusNorm,
    driver: item.transporter?.name || item.transporter?.company_name || item.contact_person || (item.transporter ? 'Assigned Driver' : null),
    driverPhone: item.transporter?.phone || item.contact_phone || null,
    vehicle: item.vehicle?.plate_number || (item.vehicle ? `${item.vehicle.make || ''} ${item.vehicle.model || ''}`.trim() : null),
    instructions: item.handling_instructions || '',
    raw: item
  }
}

// Stats computed strictly from live database records
const stats = computed(() => {
  const list = requests.value || []
  return {
    total: list.length,
    pending: list.filter(r => r.status === 'pending').length,
    confirmed: list.filter(r => ['confirmed', 'assigned'].includes(r.status)).length,
    inTransit: list.filter(r => ['in-transit', 'in_transit'].includes(r.status)).length,
    completed: list.filter(r => ['completed', 'delivered'].includes(r.status)).length
  }
})

// Filtered Requests
const filteredRequests = computed(() => {
  let list = requests.value || []

  if (filterStatus.value) {
    if (filterStatus.value === 'confirmed') {
      list = list.filter(r => ['confirmed', 'assigned'].includes(r.status))
    } else if (filterStatus.value === 'in-transit') {
      list = list.filter(r => ['in-transit', 'in_transit'].includes(r.status))
    } else if (filterStatus.value === 'completed') {
      list = list.filter(r => ['completed', 'delivered'].includes(r.status))
    } else {
      list = list.filter(r => r.status === filterStatus.value)
    }
  }

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(r =>
      (r.id && r.id.toLowerCase().includes(q)) ||
      (r.product && r.product.toLowerCase().includes(q)) ||
      (r.from && r.from.toLowerCase().includes(q)) ||
      (r.to && r.to.toLowerCase().includes(q)) ||
      (r.driver && r.driver.toLowerCase().includes(q))
    )
  }

  return list
})

// Methods
const fetchRequests = async () => {
  loading.value = true
  error.value = null
  try {
    const [transportRes, cropsRes] = await Promise.all([
      farmerAPI.getTransportRequests(),
      farmerAPI.getCrops().catch(() => ({ data: [] }))
    ])

    const rawData = transportRes.data?.data || transportRes.data || []
    requests.value = (Array.isArray(rawData) ? rawData : []).map(formatRequest)

    // Load available crop names for quick suggestions
    const rawCrops = cropsRes.data?.data || cropsRes.data || []
    if (Array.isArray(rawCrops)) {
      const names = rawCrops.map(c => c.name || c.crop_name).filter(Boolean)
      productSuggestions.value = Array.from(new Set(names)).slice(0, 4)
    }
  } catch (err) {
    console.error('Error fetching transport requests from database:', err)
    error.value = 'Failed to load transport requests from database. Please try again.'
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingRequest.value = null
  requestForm.value = {
    product_name: productSuggestions.value[0] || '',
    quantity: null,
    unit: 'bags',
    pickup_location: '',
    delivery_location: 'Kality Central Grain Warehouse, Addis Ababa',
    preferred_date: todayString,
    notes: ''
  }
  showNewRequest.value = true
}

const editRequest = (request) => {
  editingRequest.value = request
  const reqDate = request.requestDate ? new Date(request.requestDate).toISOString().split('T')[0] : todayString
  requestForm.value = {
    product_name: request.product,
    quantity: request.quantity,
    unit: request.unit || 'bags',
    pickup_location: request.from,
    delivery_location: request.to,
    preferred_date: reqDate >= todayString ? reqDate : todayString,
    notes: request.instructions
  }
  showNewRequest.value = true
}

const openEditFromDetails = () => {
  if (!selectedRequest.value) return
  const req = selectedRequest.value
  selectedRequest.value = null
  editRequest(req)
}

const closeModal = () => {
  showNewRequest.value = false
  editingRequest.value = null
}

const clearFilters = () => {
  searchQuery.value = ''
  filterStatus.value = ''
}

const saveRequest = async () => {
  submitting.value = true
  try {
    const payload = {
      product_name: requestForm.value.product_name,
      quantity: Number(requestForm.value.quantity),
      unit: requestForm.value.unit,
      pickup_location: requestForm.value.pickup_location,
      delivery_location: requestForm.value.delivery_location,
      preferred_date: requestForm.value.preferred_date,
      notes: requestForm.value.notes
    }

    if (editingRequest.value) {
      await farmerAPI.updateTransportRequest(editingRequest.value.rawId, payload)
    } else {
      await farmerAPI.createTransportRequest(payload)
    }

    closeModal()
    await fetchRequests()
  } catch (err) {
    console.error('Error saving transport request:', err)
    const msg = err.response?.data?.message || err.response?.data?.error || 'Failed to save transport request. Please verify inputs.'
    alert(msg)
  } finally {
    submitting.value = false
  }
}

const advanceStatus = async (request, newStatus) => {
  const stageLabels = {
    confirmed: 'Stage 2: Confirmed (Assign Transporter & Vehicle)',
    shipped: 'Stage 3: Shipped (Dispatch produce into transit)',
    delivered: 'Stage 4: Delivered & Paid (Verify delivery & release payment)'
  }
  const promptText = `Advance transport request ${request.id} to ${stageLabels[newStatus] || newStatus}?`
  if (!confirm(promptText)) return

  try {
    await farmerAPI.updateTransportRequestStatus(request.rawId, newStatus)
    await fetchRequests()
  } catch (err) {
    console.error('Failed to advance transport request status:', err)
    alert(err?.response?.data?.message || 'Failed to advance transport request status.')
  }
}

const deleteRequest = async (request) => {
  if (!confirm(`Are you sure you want to cancel transport request ${request.id}?`)) return
  try {
    await farmerAPI.deleteTransportRequest(request.rawId)
    await fetchRequests()
  } catch (err) {
    console.error('Error cancelling transport request:', err)
    alert(err.response?.data?.message || 'Failed to cancel transport request.')
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const capitalize = (str) => {
  if (!str) return ''
  const clean = str.replace('-', ' ')
  return clean.charAt(0).toUpperCase() + clean.slice(1)
}

const getStatusExplanation = (status) => {
  switch (status) {
    case 'pending':
      return 'Request submitted and awaiting logistics coordinator review and transporter allocation.'
    case 'confirmed':
    case 'assigned':
      return 'Transporter and vehicle have been assigned. Dispatch scheduled.'
    case 'in-transit':
      return 'Vehicle has picked up the produce and is currently en route to destination.'
    case 'completed':
    case 'delivered':
      return 'Produce has arrived at destination and passed inspection.'
    default:
      return ''
  }
}

const viewDetails = (request) => {
  selectedRequest.value = request
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  fetchRequests()
})
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

/* Page Header */
.page-header {
  background: white;
  padding: 24px 32px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
}

.header-content h1 {
  font-size: 26px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 6px 0;
  letter-spacing: -0.02em;
}

.header-content p {
  color: #6b7280;
  font-size: 13px;
  margin: 0;
}

.btn-new {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 13px;
  transition: all 0.2s;
  box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
}

.btn-new:hover {
  background: #059669;
}

/* Loading & Error */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  gap: 16px;
}

.spinner {
  width: 44px;
  height: 44px;
  border: 4px solid #e5e7eb;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-container {
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 14px;
  padding: 36px;
  margin: 30px;
  text-align: center;
}

.error-icon {
  color: #dc2626;
  margin: 0 auto 12px;
}

.error-message {
  color: #991b1b;
  font-size: 15px;
  margin-bottom: 16px;
  font-weight: 600;
}

.btn-retry {
  background: #dc2626;
  color: white;
  border: none;
  padding: 9px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* Content Area */
.transport-content {
  padding: 28px 32px;
  flex: 1;
}

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 18px;
  margin-bottom: 26px;
}

.stat-card {
  background: white;
  border-radius: 14px;
  padding: 18px 22px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  transition: all 0.2s;
}

.stat-icon {
  flex-shrink: 0;
  padding: 10px;
  border-radius: 12px;
  box-sizing: content-box;
}

.stat-icon.pending { color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
.stat-icon.confirmed { color: #3b82f6; background: rgba(59, 130, 246, 0.1); }
.stat-icon.inTransit { color: #8b5cf6; background: rgba(139, 92, 246, 0.1); }
.stat-icon.completed { color: #10b981; background: rgba(16, 185, 129, 0.1); }

.stat-label {
  display: block;
  font-size: 11px;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.stat-value {
  font-size: 26px;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.1;
  margin-top: 2px;
}

/* Table Section */
.table-section {
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.table-header {
  padding: 18px 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.table-title-area {
  display: flex;
  align-items: center;
  gap: 10px;
}

.table-title-area h3 {
  font-size: 16px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.count-pill {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 10px;
  background: #f1f5f9;
  color: #475569;
  border-radius: 20px;
}

.table-controls {
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 12px;
  color: #94a3b8;
  pointer-events: none;
}

.search-input {
  padding: 8px 14px 8px 34px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 13px;
  background: #f8fafc;
  color: #0f172a;
  outline: none;
  width: 220px;
  transition: all 0.2s;
}

.search-input:focus {
  border-color: #10b981;
  background: white;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.15);
}

.filter-select {
  padding: 8px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  background: #f8fafc;
  color: #334155;
  cursor: pointer;
  outline: none;
}

.filter-select:focus {
  border-color: #10b981;
}

/* Table */
.table-wrapper {
  overflow-x: auto;
}

.requests-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  text-align: left;
}

.requests-table th {
  background: #f8fafc;
  padding: 12px 18px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  color: #64748b;
  border-bottom: 1px solid #e2e8f0;
  letter-spacing: 0.03em;
}

.requests-table td {
  padding: 14px 18px;
  border-bottom: 1px solid #f1f5f9;
  color: #1e293b;
  vertical-align: middle;
}

.table-row:hover td {
  background-color: #f8fafc;
}

.id-badge {
  font-family: monospace;
  font-weight: 800;
  font-size: 12px;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 3px 8px;
  border-radius: 6px;
}

.qty-num {
  font-weight: 700;
}

.qty-unit {
  margin-left: 4px;
  color: #64748b;
  font-size: 12px;
}

.td-from,
.td-to {
  max-width: 170px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 12px;
}

.driver-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  color: #1e293b;
  font-size: 12px;
}

.driver-icon {
  color: #3b82f6;
}

.text-unassigned {
  font-size: 12px;
  color: #94a3b8;
  font-style: italic;
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 800;
  text-transform: capitalize;
}

.status-pending { background: rgba(245, 158, 11, 0.12); color: #b45309; }
.status-confirmed, .status-assigned { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; }
.status-in-transit { background: rgba(139, 92, 246, 0.12); color: #6d28d9; }
.status-completed, .status-delivered { background: rgba(16, 185, 129, 0.12); color: #047857; }
.status-cancelled { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }

/* Action Buttons */
.td-actions {
  white-space: nowrap;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  margin-right: 6px;
  color: #475569;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.view-btn:hover { color: #3b82f6; border-color: #bfdbfe; }
.edit-btn:hover { color: #f59e0b; border-color: #fde68a; }
.delete-btn:hover { color: #ef4444; border-color: #fecaca; }

/* Empty State */
.empty-state {
  padding: 56px 24px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.empty-icon-wrap {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  margin-bottom: 16px;
}

.empty-title {
  font-size: 16px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 6px 0;
}

.empty-desc {
  font-size: 13px;
  color: #64748b;
  max-width: 420px;
  margin: 0 0 20px 0;
  line-height: 1.5;
}

.empty-btn {
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 13px;
}

/* Modals */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 18px;
  width: 100%;
  max-width: 580px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.modal-header-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.id-tag {
  font-family: monospace;
  font-size: 12px;
  font-weight: 800;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 2px 8px;
  border-radius: 6px;
}

.btn-close {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  border-radius: 8px;
  display: flex;
}

.btn-close:hover {
  color: #0f172a;
  background: #f1f5f9;
}

.modal-body {
  padding: 24px;
}

/* Status Banner in Details */
.status-banner {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  border-radius: 12px;
  margin-bottom: 20px;
}

.banner-pending { background: rgba(245, 158, 11, 0.12); color: #92400e; border: 1px solid rgba(245, 158, 11, 0.25); }
.banner-confirmed, .banner-assigned { background: rgba(59, 130, 246, 0.12); color: #1e40af; border: 1px solid rgba(59, 130, 246, 0.25); }
.banner-in-transit { background: rgba(139, 92, 246, 0.12); color: #5b21b6; border: 1px solid rgba(139, 92, 246, 0.25); }
.banner-completed, .banner-delivered { background: rgba(16, 185, 129, 0.12); color: #065f46; border: 1px solid rgba(16, 185, 129, 0.25); }

.banner-title {
  font-weight: 800;
  font-size: 13px;
}

.banner-subtitle {
  font-size: 12px;
  opacity: 0.9;
  margin-top: 2px;
}

/* Form Styles */
.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: #334155;
  margin-bottom: 6px;
}

.form-control {
  width: 100%;
  padding: 9px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 13px;
  outline: none;
  box-sizing: border-box;
  background: #fff;
  color: #0f172a;
}

.form-control:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.15);
}

.form-row {
  display: flex;
  gap: 14px;
}

.suggestion-pills {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
}

.suggestion-label {
  font-size: 11px;
  color: #64748b;
  font-weight: 600;
}

.suggestion-pill {
  font-size: 11px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #047857;
  padding: 2px 8px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.suggestion-pill:hover {
  background: #dcfce7;
  border-color: #86efac;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.btn-primary {
  background: #10b981;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background: #059669;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f1f5f9;
  color: #334155;
  border: 1px solid #e2e8f0;
  padding: 10px 18px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover:not(:disabled) {
  background: #e2e8f0;
}

/* Detail Grid */
.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  background: #f8fafc;
  padding: 18px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  margin-bottom: 20px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.detail-item.full-width {
  grid-column: span 2;
}

.detail-item .label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.03em;
}

.detail-item .value {
  font-size: 13px;
  color: #0f172a;
}

.driver-info {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
}

.driver-info h3 {
  font-size: 13px;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 10px 0;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  font-size: 13px;
  border-bottom: 1px dashed #e2e8f0;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  color: #64748b;
}

.unassigned-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #64748b;
  padding: 6px 0;
}

.instructions {
  background: #fffbeb;
  border: 1px solid #fef3c7;
  border-radius: 12px;
  padding: 14px;
}

.instructions h3 {
  font-size: 12px;
  font-weight: 800;
  color: #92400e;
  margin: 0 0 6px 0;
  text-transform: uppercase;
}

.instructions p {
  margin: 0;
  font-size: 13px;
  color: #78350f;
  line-height: 1.4;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* ==================== DARK MODE ==================== */
.dark.transport-layout {
  background-color: #0b101b;
}

.dark .page-header {
  background: #131b2e;
  border-bottom-color: #1e293b;
}

.dark .header-content h1 {
  color: #f8fafc;
}

.dark .header-content p {
  color: #94a3b8;
}

.dark .stat-card {
  background: #131b2e;
  border-color: #1e293b;
}

.dark .stat-label {
  color: #94a3b8;
}

.dark .stat-value {
  color: #f8fafc;
}

.dark .table-section {
  background: #131b2e;
  border-color: #1e293b;
}

.dark .table-header {
  border-bottom-color: #1e293b;
}

.dark .table-title-area h3 {
  color: #f8fafc;
}

.dark .count-pill {
  background: #1e293b;
  color: #94a3b8;
}

.dark .search-input {
  background: #0f172a;
  border-color: #334155;
  color: #f8fafc;
}

.dark .filter-select {
  background: #0f172a;
  border-color: #334155;
  color: #f8fafc;
}

.dark .requests-table th {
  background: #0b101b;
  border-bottom-color: #1e293b;
  color: #94a3b8;
}

.dark .requests-table td {
  border-bottom-color: #1e293b;
  color: #e2e8f0;
}

.dark .table-row:hover td {
  background-color: rgba(30, 41, 59, 0.4);
}

.dark .action-btn {
  background: #0f172a;
  border-color: #334155;
  color: #94a3b8;
}

.dark .action-btn:hover {
  background: #1e293b;
  color: #f8fafc;
}

.dark .empty-icon-wrap {
  background: #1e293b;
  color: #64748b;
}

.dark .empty-title {
  color: #f8fafc;
}

.dark .empty-desc {
  color: #94a3b8;
}

.dark .modal-content {
  background: #131b2e;
  border: 1px solid #1e293b;
  color: #e2e8f0;
}

.dark .modal-header {
  border-bottom-color: #1e293b;
}

.dark .modal-header h2 {
  color: #f8fafc;
}

.dark .form-group label {
  color: #cbd5e1;
}

.dark .form-control {
  background: #0f172a;
  border-color: #334155;
  color: #f8fafc;
}

.dark .detail-grid,
.dark .driver-info {
  background: #0f172a;
  border-color: #1e293b;
}

.dark .detail-item .value {
  color: #f8fafc;
}

.dark .driver-info h3 {
  color: #f8fafc;
}

.dark .info-row {
  border-bottom-color: #1e293b;
}

.dark .modal-footer {
  border-top-color: #1e293b;
}

.dark .btn-secondary {
  background: #1e293b;
  border-color: #334155;
  color: #e2e8f0;
}

.dark .btn-secondary:hover:not(:disabled) {
  background: #334155;
}

@media (max-width: 1024px) {
  .transport-container {
    margin-left: 0;
  }
}
</style>
