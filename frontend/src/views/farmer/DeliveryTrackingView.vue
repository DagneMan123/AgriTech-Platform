<template>
  <div class="delivery-tracking-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="tracking-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Delivery Tracking</h1>
          <p>View history and details of all your deliveries</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading deliveries...</p>
      </div>

      <!-- Content -->
      <div v-if="!loading" class="tracking-content">
        <!-- Filters -->
        <div class="filters-section">
          <div class="filter-group">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search delivery ID or product..."
              class="search-input"
            />
          </div>
          <div class="filter-group">
            <select v-model="filterStatus" class="filter-select">
              <option value="">All Status</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
              <option value="returned">Returned</option>
            </select>
          </div>
          <div class="filter-group">
            <select v-model="sortBy" class="filter-select">
              <option value="recent">Most Recent</option>
              <option value="oldest">Oldest First</option>
              <option value="product">Product Name</option>
            </select>
          </div>
        </div>

        <!-- Deliveries List -->
        <div class="deliveries-list">
          <div v-if="filteredDeliveries.length > 0">
            <div v-for="delivery in filteredDeliveries" :key="delivery.id" class="delivery-item">
              <div class="item-left">
                <div class="delivery-status">
                  <div class="status-icon" :class="`icon-${delivery.status}`">
                    <CheckCircle v-if="delivery.status === 'delivered'" size="24" />
                    <AlertCircle v-else-if="delivery.status === 'cancelled'" size="24" />
                    <RotateCcw v-else-if="delivery.status === 'returned'" size="24" />
                  </div>
                </div>
              </div>

              <div class="item-center">
                <div class="delivery-header">
                  <h3>{{ delivery.id }}</h3>
                  <span class="status-badge" :class="`status-${delivery.status}`">
                    {{ capitalize(delivery.status) }}
                  </span>
                </div>
                <p class="delivery-route">{{ delivery.from }} → {{ delivery.to }}</p>
                <p class="product-info">{{ delivery.product }} • {{ delivery.quantity }} {{ delivery.unit }}</p>
                <div class="delivery-meta">
                  <span class="meta-item">
                    <Calendar size="14" />
                    {{ formatDate(delivery.deliveredDate) }}
                  </span>
                  <span class="meta-item">
                    <User size="14" />
                    {{ delivery.driver }}
                  </span>
                  <span class="meta-item" v-if="delivery.totalDistance">
                    <MapPin size="14" />
                    {{ delivery.totalDistance }} km
                  </span>
                </div>
              </div>

              <div class="item-right">
                <button @click="viewDetails(delivery)" class="btn-view-details">
                  View Details
                  <ChevronRight size="16" />
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <Package size="48" class="empty-icon" />
            <p>No deliveries found</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="filteredDeliveries.length > 0" class="pagination">
          <button @click="prevPage" :disabled="currentPage === 1" class="btn-pagination">
            <ChevronLeft size="16" />
            Previous
          </button>
          <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
          <button @click="nextPage" :disabled="currentPage === totalPages" class="btn-pagination">
            Next
            <ChevronRight size="16" />
          </button>
        </div>

        <!-- Details Modal -->
        <div v-if="selectedDelivery" class="modal-overlay" @click="selectedDelivery = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Delivery Details - {{ selectedDelivery.id }}</h2>
              <button @click="selectedDelivery = null" class="btn-close">
                <X size="20" />
              </button>
            </div>

            <div class="modal-body">
              <!-- Status Summary -->
              <div class="status-summary" :class="`summary-${selectedDelivery.status}`">
                <div class="summary-icon">
                  <CheckCircle v-if="selectedDelivery.status === 'delivered'" size="32" />
                  <AlertCircle v-else-if="selectedDelivery.status === 'cancelled'" size="32" />
                  <RotateCcw v-else-if="selectedDelivery.status === 'returned'" size="32" />
                </div>
                <div class="summary-content">
                  <h3>{{ capitalize(selectedDelivery.status) }}</h3>
                  <p>{{ selectedDelivery.statusMessage }}</p>
                </div>
              </div>

              <!-- Route Information -->
              <div class="info-section">
                <h3 class="section-title">Route Information</h3>
                <div class="route-info">
                  <div class="location-info">
                    <span class="label">Pickup</span>
                    <p class="location">{{ selectedDelivery.from }}</p>
                  </div>
                  <div class="route-line"></div>
                  <div class="location-info">
                    <span class="label">Delivery</span>
                    <p class="location">{{ selectedDelivery.to }}</p>
                  </div>
                </div>
              </div>

              <!-- Product Details -->
              <div class="info-section">
                <h3 class="section-title">Product Details</h3>
                <div class="detail-grid">
                  <div class="detail-item">
                    <span class="label">Product:</span>
                    <span class="value">{{ selectedDelivery.product }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Quantity:</span>
                    <span class="value">{{ selectedDelivery.quantity }} {{ selectedDelivery.unit }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Batch ID:</span>
                    <span class="value">{{ selectedDelivery.batchId }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Value:</span>
                    <span class="value highlight">${{ formatNumber(selectedDelivery.value) }}</span>
                  </div>
                </div>
              </div>

              <!-- Journey Timeline -->
              <div class="info-section">
                <h3 class="section-title">Journey Timeline</h3>
                <div class="timeline">
                  <div v-for="event in selectedDelivery.events" :key="event.id" class="timeline-item">
                    <div class="timeline-marker" :class="event.type"></div>
                    <div class="timeline-content">
                      <p class="event-title">{{ event.title }}</p>
                      <p class="event-description">{{ event.description }}</p>
                      <small>{{ formatDate(event.timestamp) }} at {{ event.time }}</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Driver Information -->
              <div class="info-section">
                <h3 class="section-title">Driver Information</h3>
                <div class="driver-info">
                  <div class="driver-detail">
                    <span class="label">Driver Name:</span>
                    <span class="value">{{ selectedDelivery.driver }}</span>
                  </div>
                  <div class="driver-detail">
                    <span class="label">Vehicle:</span>
                    <span class="value">{{ selectedDelivery.vehicle }}</span>
                  </div>
                  <div class="driver-detail">
                    <span class="label">Contact:</span>
                    <span class="value">{{ selectedDelivery.driverPhone }}</span>
                  </div>
                </div>
              </div>

              <!-- Delivery Stats -->
              <div class="info-section">
                <h3 class="section-title">Delivery Statistics</h3>
                <div class="stats-grid">
                  <div class="stat">
                    <span class="stat-label">Total Distance</span>
                    <span class="stat-value">{{ selectedDelivery.totalDistance }} km</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">Duration</span>
                    <span class="stat-value">{{ selectedDelivery.duration }}</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">Avg Speed</span>
                    <span class="stat-value">{{ selectedDelivery.avgSpeed }} km/h</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">Delivery Date</span>
                    <span class="stat-value">{{ formatDate(selectedDelivery.deliveredDate) }}</span>
                  </div>
                </div>
              </div>

              <!-- Signature & Documents -->
              <div class="info-section">
                <h3 class="section-title">Documents & Confirmation</h3>
                <div class="documents">
                  <div class="document-item">
                    <FileText size="20" />
                    <div>
                      <p class="doc-title">Delivery Confirmation</p>
                      <small>Signed by {{ selectedDelivery.signedBy }}</small>
                    </div>
                    <button class="btn-download">
                      <Download size="16" />
                    </button>
                  </div>
                  <div class="document-item">
                    <FileText size="20" />
                    <div>
                      <p class="doc-title">Route Map</p>
                      <small>GPS tracking data</small>
                    </div>
                    <button class="btn-download">
                      <Download size="16" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedDelivery = null" class="btn-secondary">Close</button>
              <button @click="downloadDeliveryReport(selectedDelivery)" class="btn-primary">
                <Download size="16" />
                Download Report
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
import {
  CheckCircle, AlertCircle, RotateCcw, X, ChevronRight, ChevronLeft, Package, 
  Calendar, User, MapPin, FileText, Download
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const selectedDelivery = ref(null)
const searchQuery = ref('')
const filterStatus = ref('')
const sortBy = ref('recent')
const currentPage = ref(1)
const pageSize = 5

const mockDeliveries = [
  {
    id: 'DLV-2026-001',
    from: 'Green Valley Farm',
    to: 'City Market Central Hub',
    product: 'Tomatoes',
    quantity: 500,
    unit: 'kg',
    batchId: 'BATCH-2026-089',
    value: 1500,
    status: 'delivered',
    statusMessage: 'Successfully delivered on Sep 1, 2026',
    driver: 'John Smith',
    vehicle: 'TRK-2024-001',
    driverPhone: '+1-555-0401',
    deliveredDate: '2026-09-01',
    totalDistance: 100,
    duration: '2 hours 30 mins',
    avgSpeed: 40,
    signedBy: 'Receiver Name',
    events: [
      { id: 1, type: 'pickup', title: 'Pickup', description: 'Package picked up from source', timestamp: '2026-09-01', time: '06:00 AM' },
      { id: 2, type: 'in-transit', title: 'In Transit', description: 'On the way to destination', timestamp: '2026-09-01', time: '07:30 AM' },
      { id: 3, type: 'delivered', title: 'Delivered', description: 'Package delivered successfully', timestamp: '2026-09-01', time: '08:30 AM' }
    ]
  },
  {
    id: 'DLV-2026-002',
    from: 'Sunny Acres',
    to: 'Regional Distribution Center',
    product: 'Corn',
    quantity: 1000,
    unit: 'kg',
    batchId: 'BATCH-2026-090',
    value: 2500,
    status: 'delivered',
    statusMessage: 'Successfully delivered on Aug 31, 2026',
    driver: 'Maria Garcia',
    vehicle: 'TRK-2024-002',
    driverPhone: '+1-555-0402',
    deliveredDate: '2026-08-31',
    totalDistance: 120,
    duration: '3 hours',
    avgSpeed: 40,
    signedBy: 'Receiver Name',
    events: [
      { id: 1, type: 'pickup', title: 'Pickup', description: 'Package picked up', timestamp: '2026-08-31', time: '05:30 AM' },
      { id: 2, type: 'in-transit', title: 'In Transit', description: 'On the way', timestamp: '2026-08-31', time: '07:00 AM' },
      { id: 3, type: 'delivered', title: 'Delivered', description: 'Successfully delivered', timestamp: '2026-08-31', time: '08:30 AM' }
    ]
  },
  {
    id: 'DLV-2026-003',
    from: 'Harvest Farm',
    to: 'Wholesale Center',
    product: 'Lettuce',
    quantity: 300,
    unit: 'kg',
    batchId: 'BATCH-2026-091',
    value: 900,
    status: 'cancelled',
    statusMessage: 'Delivery cancelled on Aug 30, 2026 - Customer request',
    driver: 'Robert Wilson',
    vehicle: 'TRK-2024-003',
    driverPhone: '+1-555-0403',
    deliveredDate: '2026-08-30',
    totalDistance: 0,
    duration: '0 mins',
    avgSpeed: 0,
    signedBy: 'N/A',
    events: [
      { id: 1, type: 'pickup', title: 'Scheduled', description: 'Delivery scheduled', timestamp: '2026-08-30', time: '08:00 AM' },
      { id: 2, type: 'cancelled', title: 'Cancelled', description: 'Customer requested cancellation', timestamp: '2026-08-30', time: '09:00 AM' }
    ]
  },
  {
    id: 'DLV-2026-004',
    from: 'My Farm',
    to: 'Export Terminal',
    product: 'Potatoes',
    quantity: 2000,
    unit: 'kg',
    batchId: 'BATCH-2026-092',
    value: 4000,
    status: 'returned',
    statusMessage: 'Delivery returned on Aug 29, 2026 - Quality issues',
    driver: 'David Lee',
    vehicle: 'TRK-2024-004',
    driverPhone: '+1-555-0404',
    deliveredDate: '2026-08-29',
    totalDistance: 150,
    duration: '4 hours 30 mins',
    avgSpeed: 33,
    signedBy: 'N/A',
    events: [
      { id: 1, type: 'pickup', title: 'Picked up', description: 'Package picked up', timestamp: '2026-08-29', time: '05:00 AM' },
      { id: 2, type: 'in-transit', title: 'In Transit', description: 'On the way', timestamp: '2026-08-29', time: '06:30 AM' },
      { id: 3, type: 'cancelled', title: 'Returned', description: 'Quality issues detected - returned to sender', timestamp: '2026-08-29', time: '09:30 AM' }
    ]
  },
  {
    id: 'DLV-2026-005',
    from: 'Green Valley Farm',
    to: 'City Supermarket',
    product: 'Carrots',
    quantity: 400,
    unit: 'kg',
    batchId: 'BATCH-2026-093',
    value: 800,
    status: 'delivered',
    statusMessage: 'Successfully delivered on Aug 28, 2026',
    driver: 'John Smith',
    vehicle: 'TRK-2024-001',
    driverPhone: '+1-555-0401',
    deliveredDate: '2026-08-28',
    totalDistance: 80,
    duration: '2 hours',
    avgSpeed: 40,
    signedBy: 'Receiver Name',
    events: [
      { id: 1, type: 'pickup', title: 'Picked up', description: 'Package picked up from source', timestamp: '2026-08-28', time: '06:30 AM' },
      { id: 2, type: 'in-transit', title: 'In Transit', description: 'On the way to destination', timestamp: '2026-08-28', time: '07:45 AM' },
      { id: 3, type: 'delivered', title: 'Delivered', description: 'Package delivered successfully', timestamp: '2026-08-28', time: '08:30 AM' }
    ]
  }
]

const deliveries = ref(mockDeliveries)

const filteredDeliveries = computed(() => {
  let result = deliveries.value
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(d => 
      d.id.toLowerCase().includes(query) || 
      d.product.toLowerCase().includes(query)
    )
  }
  
  if (filterStatus.value) {
    result = result.filter(d => d.status === filterStatus.value)
  }
  
  if (sortBy.value === 'oldest') {
    result.sort((a, b) => new Date(a.deliveredDate) - new Date(b.deliveredDate))
  } else if (sortBy.value === 'product') {
    result.sort((a, b) => a.product.localeCompare(b.product))
  } else {
    result.sort((a, b) => new Date(b.deliveredDate) - new Date(a.deliveredDate))
  }
  
  return result
})

const paginatedDeliveries = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredDeliveries.value.slice(start, start + pageSize)
})

const totalPages = computed(() => Math.ceil(filteredDeliveries.value.length / pageSize))

const fetchDeliveries = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const formatDate = (date) => new Date(date).toLocaleDateString()
const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)
const viewDetails = (delivery) => { selectedDelivery.value = delivery }
const downloadDeliveryReport = (delivery) => { alert(`Downloading report for ${delivery.id}`) }

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchDeliveries() })
</script>

<style scoped>
.delivery-tracking-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.tracking-container {
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

.tracking-content {
  padding: 30px;
  flex: 1;
}

.filters-section {
  display: grid;
  grid-template-columns: 1fr auto auto;
  gap: 16px;
  margin-bottom: 24px;
  background: white;
  padding: 16px;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.filter-group {
  display: flex;
  align-items: center;
}

.search-input,
.filter-select {
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
  color: #4b5563;
  background: white;
  font-family: inherit;
}

.search-input {
  width: 100%;
}

.search-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.deliveries-list {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  margin-bottom: 24px;
}

.delivery-item {
  display: flex;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  transition: all 0.2s;
}

.delivery-item:hover {
  background: #f9fafb;
}

.delivery-item:last-child {
  border-bottom: none;
}

.item-left {
  flex-shrink: 0;
  margin-right: 16px;
}

.delivery-status {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-icon.icon-delivered {
  color: #10b981;
}

.status-icon.icon-cancelled {
  color: #ef4444;
}

.status-icon.icon-returned {
  color: #f59e0b;
}

.item-center {
  flex: 1;
}

.delivery-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 6px;
}

.delivery-header h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
}

.status-delivered { background: #d1fae5; color: #065f46; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.status-returned { background: #fef3c7; color: #92400e; }

.delivery-route {
  font-size: 13px;
  color: #4b5563;
  margin: 4px 0;
}

.product-info {
  font-size: 12px;
  color: #6b7280;
  margin: 4px 0 8px 0;
}

.delivery-meta {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #6b7280;
}

.meta-item svg {
  color: #9ca3af;
}

.item-right {
  flex-shrink: 0;
}

.btn-view-details {
  padding: 8px 16px;
  background: #eff6ff;
  color: #1e40af;
  border: 1px solid #3b82f6;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.btn-view-details:hover {
  background: #3b82f6;
  color: white;
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

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
}

.btn-pagination {
  padding: 10px 16px;
  background: white;
  color: #4b5563;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.btn-pagination:hover:not(:disabled) {
  background: #f3f4f6;
  border-color: #3b82f6;
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 13px;
  color: #6b7280;
  font-weight: 600;
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
  overflow-y: auto;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 700px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  margin: 20px auto;
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  background: white;
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

.status-summary {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 24px;
}

.status-summary.summary-delivered {
  background: #d1fae5;
  border: 2px solid #10b981;
}

.status-summary.summary-cancelled {
  background: #fee2e2;
  border: 2px solid #ef4444;
}

.status-summary.summary-returned {
  background: #fef3c7;
  border: 2px solid #f59e0b;
}

.summary-icon {
  display: flex;
  align-items: center;
}

.summary-icon svg {
  color: currentColor;
}

.status-summary.summary-delivered .summary-icon { color: #065f46; }
.status-summary.summary-cancelled .summary-icon { color: #991b1b; }
.status-summary.summary-returned .summary-icon { color: #92400e; }

.summary-content h3 {
  margin: 0 0 4px 0;
  font-size: 16px;
}

.summary-content p {
  margin: 0;
  font-size: 13px;
  opacity: 0.9;
}

.info-section {
  margin-bottom: 24px;
}

.section-title {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 16px 0;
  padding-bottom: 12px;
  border-bottom: 2px solid #e5e7eb;
}

.route-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.location-info {
  flex: 1;
}

.location-info .label {
  display: block;
  font-size: 11px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
  margin-bottom: 4px;
}

.location-info .location {
  font-size: 13px;
  color: #1f2937;
  font-weight: 600;
  margin: 0;
}

.route-line {
  width: 40px;
  height: 2px;
  background: #e5e7eb;
  flex-shrink: 0;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
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
  font-size: 11px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.detail-item .value {
  font-size: 13px;
  color: #1f2937;
  font-weight: 600;
}

.detail-item .value.highlight {
  color: #059669;
}

.timeline {
  position: relative;
  padding-left: 30px;
}

.timeline-item {
  position: relative;
  margin-bottom: 20px;
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: -30px;
  top: 6px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 3px solid #e5e7eb;
  background: white;
}

.timeline-item.pickup::before {
  background: #10b981;
  border-color: #10b981;
}

.timeline-item.delivered::before {
  background: #10b981;
  border-color: #10b981;
}

.timeline-item.cancelled::before {
  background: #ef4444;
  border-color: #ef4444;
}

.timeline-item.in-transit::before {
  background: #3b82f6;
  border-color: #3b82f6;
}

.timeline-item:not(:last-child)::after {
  content: '';
  position: absolute;
  left: -23px;
  top: 22px;
  width: 2px;
  height: calc(100% + 20px);
  background: #e5e7eb;
}

.event-title {
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  font-size: 13px;
}

.event-description {
  color: #4b5563;
  margin: 4px 0;
  font-size: 12px;
}

.timeline-content small {
  color: #9ca3af;
  font-size: 11px;
}

.driver-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
}

.driver-detail {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}

.driver-detail .label {
  color: #6b7280;
  font-weight: 600;
}

.driver-detail .value {
  color: #1f2937;
  font-weight: 600;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.stat {
  background: #f9fafb;
  border-radius: 8px;
  padding: 12px;
  text-align: center;
}

.stat-label {
  font-size: 11px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
  display: block;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 13px;
  color: #1f2937;
  font-weight: 700;
}

.documents {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.document-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
  justify-content: space-between;
}

.document-item svg {
  color: #3b82f6;
  flex-shrink: 0;
}

.doc-title {
  font-size: 13px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.document-item small {
  font-size: 11px;
  color: #6b7280;
}

.btn-download {
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px 8px;
  color: #3b82f6;
  display: flex;
  align-items: center;
  transition: all 0.2s;
}

.btn-download:hover {
  color: #1e40af;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: white;
  position: sticky;
  bottom: 0;
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
  display: inline-flex;
  align-items: center;
  gap: 8px;
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

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .tracking-container {
    margin-left: 0;
  }

  .filters-section {
    grid-template-columns: 1fr;
  }

  .delivery-item {
    flex-wrap: wrap;
  }

  .item-center {
    width: 100%;
  }

  .item-right {
    width: 100%;
    margin-top: 12px;
  }

  .btn-view-details {
    width: 100%;
    justify-content: center;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .modal-content {
    width: 95%;
  }
}
</style>
