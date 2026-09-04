<template>
  <div class="orders-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="orders-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>My Input Orders</h1>
          <p>Track your farm input purchases and deliveries</p>
        </div>
        <div class="header-actions">
          <button @click="toggleFilters" class="btn-filter">
            <Filter size="16" />
            <span>Filters</span>
          </button>
          <button @click="exportOrders" class="btn-export">
            <Download size="16" />
            <span>Export</span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading orders...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchOrders" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="orders-content">
        <!-- Summary Stats -->
        <div class="stats-section">
          <div class="stat-card">
            <FileText size="20" class="stat-icon" />
            <div class="stat-info">
              <span class="stat-label">Total Orders</span>
              <span class="stat-value">{{ summaryData.totalOrders }}</span>
            </div>
          </div>

          <div class="stat-card">
            <DollarSign size="20" class="stat-icon" />
            <div class="stat-info">
              <span class="stat-label">Total Spent</span>
              <span class="stat-value">${{ formatNumber(summaryData.totalSpent) }}</span>
            </div>
          </div>

          <div class="stat-card">
            <Truck size="20" class="stat-icon" />
            <div class="stat-info">
              <span class="stat-label">In Transit</span>
              <span class="stat-value">{{ summaryData.inTransit }}</span>
            </div>
          </div>

          <div class="stat-card">
            <CheckCircle size="20" class="stat-icon" />
            <div class="stat-info">
              <span class="stat-label">Delivered</span>
              <span class="stat-value">{{ summaryData.delivered }}</span>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div v-if="showFilters" class="filters-panel">
          <div class="filters-header">
            <h3>Filters</h3>
            <button @click="toggleFilters" class="btn-close">
              <X size="20" />
            </button>
          </div>
          
          <div class="filters-content">
            <div class="filter-group">
              <label>Status</label>
              <select v-model="filters.status" class="filter-select">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Date Range</label>
              <select v-model="filters.dateRange" class="filter-select">
                <option value="all">All Time</option>
                <option value="month">Last Month</option>
                <option value="quarter">Last Quarter</option>
                <option value="year">Last Year</option>
              </select>
            </div>

            <div class="filter-actions">
              <button @click="applyFilters" class="btn-apply">Apply</button>
              <button @click="clearFilters" class="btn-clear">Clear</button>
            </div>
          </div>
        </div>

        <!-- Orders Table -->
        <div class="table-section">
          <div v-if="filteredOrders.length > 0" class="table-wrapper">
            <table class="orders-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Total Amount</th>
                  <th>Order Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in filteredOrders" :key="order.id" class="table-row">
                  <td class="td-id">{{ order.id }}</td>
                  <td class="td-product">{{ order.product }}</td>
                  <td class="td-quantity">{{ order.quantity }} {{ order.unit }}</td>
                  <td class="td-amount">${{ formatNumber(order.totalAmount) }}</td>
                  <td class="td-date">{{ formatDate(order.orderDate) }}</td>
                  <td class="td-status">
                    <span class="status-badge" :class="`status-${order.status}`">
                      {{ capitalize(order.status) }}
                    </span>
                  </td>
                  <td class="td-actions">
                    <button @click="viewDetails(order)" class="action-btn view-btn">
                      <Eye size="16" />
                    </button>
                    <button @click="downloadInvoice(order)" class="action-btn download-btn">
                      <FileDown size="16" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="empty-state">
            <ShoppingBag size="48" class="empty-icon" />
            <p>No orders found</p>
          </div>
        </div>

        <!-- Details Modal -->
        <div v-if="selectedOrder" class="modal-overlay" @click="selectedOrder = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Order {{ selectedOrder.id }}</h2>
              <button @click="selectedOrder = null" class="btn-close">
                <X size="20" />
              </button>
            </div>

            <div class="modal-body">
              <div class="order-summary">
                <div class="summary-item">
                  <span class="label">Product</span>
                  <span class="value">{{ selectedOrder.product }}</span>
                </div>
                <div class="summary-item">
                  <span class="label">Quantity</span>
                  <span class="value">{{ selectedOrder.quantity }} {{ selectedOrder.unit }}</span>
                </div>
                <div class="summary-item">
                  <span class="label">Unit Price</span>
                  <span class="value">${{ formatNumber(selectedOrder.unitPrice) }}</span>
                </div>
                <div class="summary-item">
                  <span class="label">Total Amount</span>
                  <span class="value">${{ formatNumber(selectedOrder.totalAmount) }}</span>
                </div>
                <div class="summary-item">
                  <span class="label">Status</span>
                  <span class="status-badge" :class="`status-${selectedOrder.status}`">
                    {{ capitalize(selectedOrder.status) }}
                  </span>
                </div>
              </div>

              <div class="timeline">
                <h3>Order Timeline</h3>
                <div class="timeline-item">
                  <CheckCircle size="20" class="timeline-icon completed" />
                  <div class="timeline-content">
                    <span class="timeline-title">Order Placed</span>
                    <span class="timeline-date">{{ formatDate(selectedOrder.orderDate) }}</span>
                  </div>
                </div>
                <div class="timeline-item" :class="selectedOrder.status !== 'pending' ? 'completed' : ''">
                  <Clock size="20" class="timeline-icon" />
                  <div class="timeline-content">
                    <span class="timeline-title">Confirmed by Supplier</span>
                    <span class="timeline-date">Expected within 24 hours</span>
                  </div>
                </div>
                <div class="timeline-item" :class="['shipped', 'delivered'].includes(selectedOrder.status) ? 'completed' : ''">
                  <Truck size="20" class="timeline-icon" />
                  <div class="timeline-content">
                    <span class="timeline-title">Shipped</span>
                    <span class="timeline-date">{{ selectedOrder.shippedDate || 'Pending' }}</span>
                  </div>
                </div>
                <div class="timeline-item" :class="selectedOrder.status === 'delivered' ? 'completed' : ''">
                  <Package size="20" class="timeline-icon" />
                  <div class="timeline-content">
                    <span class="timeline-title">Delivered</span>
                    <span class="timeline-date">{{ selectedOrder.deliveredDate || 'Pending' }}</span>
                  </div>
                </div>
              </div>

              <div class="supplier-info">
                <h3>Supplier Information</h3>
                <p><strong>Name:</strong> {{ selectedOrder.supplier }}</p>
                <p><strong>Contact:</strong> {{ selectedOrder.supplierPhone }}</p>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedOrder = null" class="btn-secondary">Close</button>
              <button @click="downloadInvoice(selectedOrder)" class="btn-primary">
                <Download size="16" />
                Download Invoice
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
  Filter, Download, AlertCircle, RotateCcw, X, Eye, FileDown, 
  FileText, DollarSign, Truck, CheckCircle, Clock, Package, ShoppingBag
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

// State
const loading = ref(false)
const error = ref(null)
const showFilters = ref(false)
const selectedOrder = ref(null)

// Filters
const filters = ref({
  status: '',
  dateRange: 'all'
})

// Mock data
const mockOrders = [
  {
    id: 'INP-001',
    product: 'NPK 10-10-10 Fertilizer',
    quantity: 50,
    unit: 'bags',
    unitPrice: 24.99,
    totalAmount: 1249.50,
    orderDate: '2026-09-01',
    status: 'delivered',
    supplier: 'Green Farms Supply',
    supplierPhone: '+1-555-0301',
    shippedDate: '2026-09-03',
    deliveredDate: '2026-09-05'
  },
  {
    id: 'INP-002',
    product: 'Tomato Seeds - Cherry',
    quantity: 10,
    unit: 'packets',
    unitPrice: 3.99,
    totalAmount: 39.90,
    orderDate: '2026-09-02',
    status: 'delivered',
    supplier: 'Seed Kingdom',
    supplierPhone: '+1-555-0302',
    shippedDate: '2026-09-03',
    deliveredDate: '2026-09-04'
  },
  {
    id: 'INP-003',
    product: 'Neem Oil Spray',
    quantity: 20,
    unit: 'quarts',
    unitPrice: 18.99,
    totalAmount: 379.80,
    orderDate: '2026-09-03',
    status: 'shipped',
    supplier: 'Bio-Protection Inc',
    supplierPhone: '+1-555-0303',
    shippedDate: '2026-09-04',
    deliveredDate: null
  },
  {
    id: 'INP-004',
    product: 'Corn Seeds',
    quantity: 30,
    unit: 'lbs',
    unitPrice: 8.50,
    totalAmount: 255.00,
    orderDate: '2026-09-04',
    status: 'confirmed',
    supplier: 'Premium Seeds Co',
    supplierPhone: '+1-555-0304',
    shippedDate: null,
    deliveredDate: null
  },
  {
    id: 'INP-005',
    product: 'Sulfur Dust',
    quantity: 15,
    unit: 'bags',
    unitPrice: 12.99,
    totalAmount: 194.85,
    orderDate: '2026-09-05',
    status: 'pending',
    supplier: 'Agricultural Chemicals Ltd',
    supplierPhone: '+1-555-0305',
    shippedDate: null,
    deliveredDate: null
  }
]

// Computed
const filteredOrders = computed(() => {
  let result = mockOrders

  if (filters.value.status) {
    result = result.filter(o => o.status === filters.value.status)
  }

  return result
})

const summaryData = computed(() => {
  return {
    totalOrders: mockOrders.length,
    totalSpent: mockOrders.reduce((sum, o) => sum + o.totalAmount, 0),
    inTransit: mockOrders.filter(o => o.status === 'shipped').length,
    delivered: mockOrders.filter(o => o.status === 'delivered').length
  }
})

// Methods
const fetchOrders = async () => {
  loading.value = true
  error.value = null
  try {
    await new Promise(r => setTimeout(r, 500))
  } catch (err) {
    error.value = 'Failed to load orders'
  } finally {
    loading.value = false
  }
}

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)
const formatDate = (date) => new Date(date).toLocaleDateString()
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)

const toggleFilters = () => { showFilters.value = !showFilters.value }
const applyFilters = () => { currentPage.value = 1; toggleFilters() }
const clearFilters = () => {
  filters.value = { status: '', dateRange: 'all' }
}

const viewDetails = (order) => { selectedOrder.value = order }
const downloadInvoice = (order) => { alert(`Invoice downloaded for ${order.id}`) }
const exportOrders = () => { alert('Orders exported to CSV') }

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchOrders() })
</script>

<style scoped>
.orders-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.orders-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* Header */
.page-header {
  background: white;
  padding: 25px 30px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
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

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-filter,
.btn-export {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  color: #4b5563;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-filter:hover,
.btn-export:hover {
  background: #f9fafb;
}

.btn-export {
  background: #10b981;
  color: white;
  border-color: #10b981;
}

.btn-export:hover {
  background: #059669;
}

/* Loading & Error */
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
  border-top-color: #10b981;
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

/* Content */
.orders-content {
  padding: 30px;
  flex: 1;
}

/* Stats */
.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  color: #d1d5db;
  flex-shrink: 0;
}

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
}

/* Filters */
.filters-panel {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.filters-header h3 {
  font-size: 16px;
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

.filters-content {
  padding: 20px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-group label {
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
}

.filter-select {
  padding: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  color: #1f2937;
  background: white;
  cursor: pointer;
}

.filter-actions {
  grid-column: 1 / -1;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn-apply,
.btn-clear {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-apply {
  background: #10b981;
  color: white;
}

.btn-apply:hover {
  background: #059669;
}

.btn-clear {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.btn-clear:hover {
  background: #e5e7eb;
}

/* Table */
.table-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  margin-bottom: 20px;
}

.table-wrapper {
  overflow-x: auto;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.orders-table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.orders-table th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 12px;
}

.orders-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
  transition: background 0.2s;
}

.orders-table tbody tr:hover {
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

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-confirmed {
  background: #dbeafe;
  color: #1e40af;
}

.status-shipped {
  background: #e0e7ff;
  color: #3730a3;
}

.status-delivered {
  background: #d1fae5;
  color: #065f46;
}

.status-cancelled {
  background: #fee2e2;
  color: #991b1b;
}

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
  border-color: #10b981;
  color: #10b981;
  background: #ecfdf5;
}

.empty-state {
  padding: 60px 20px;
  text-align: center;
  color: #9ca3af;
}

.empty-icon {
  color: #d1d5db;
  margin-bottom: 16px;
}

.empty-state p {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  color: #6b7280;
}

/* Modal */
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

.modal-body {
  padding: 24px;
}

.order-summary {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 24px;
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.summary-item .label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.summary-item .value {
  font-size: 14px;
  color: #1f2937;
  font-weight: 600;
}

.timeline {
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.timeline h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 16px 0;
}

.timeline-item {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.timeline-item.completed {
  opacity: 1;
}

.timeline-icon {
  color: #d1d5db;
  flex-shrink: 0;
  margin-top: 2px;
}

.timeline-item.completed .timeline-icon {
  color: #10b981;
}

.timeline-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.timeline-title {
  font-weight: 600;
  color: #1f2937;
  font-size: 13px;
}

.timeline-date {
  font-size: 12px;
  color: #9ca3af;
}

.supplier-info {
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
}

.supplier-info h3 {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.supplier-info p {
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
  background: #10b981;
  color: white;
}

.btn-primary:hover {
  background: #059669;
}

.btn-secondary {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
  .orders-container {
    margin-left: 0;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .header-actions {
    width: 100%;
  }

  .order-summary {
    grid-template-columns: 1fr;
  }

  .orders-table {
    font-size: 12px;
  }

  .orders-table th,
  .table-row td {
    padding: 12px 8px;
  }
}
</style>
