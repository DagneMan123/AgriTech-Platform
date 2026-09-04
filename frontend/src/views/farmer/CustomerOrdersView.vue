<template>
  <div class="orders-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="orders-container">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h1>Customer Orders</h1>
          <p>Manage and track orders from your customers</p>
        </div>
        <div class="header-actions">
          <button @click="openFilters" class="btn-filter">
            <Filter size="16" />
            <span>Filters</span>
          </button>
          <button @click="exportData" class="btn-export">
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
        <!-- Summary Cards -->
        <div class="summary-section">
          <div class="summary-card">
            <div class="card-header">
              <h3>Total Orders</h3>
              <ShoppingCart size="20" class="card-icon total-icon" />
            </div>
            <p class="card-value">{{ summaryData.totalOrders }}</p>
            <p class="card-subtitle">all time</p>
          </div>

          <div class="summary-card">
            <div class="card-header">
              <h3>Pending</h3>
              <Clock size="20" class="card-icon pending-icon" />
            </div>
            <p class="card-value">{{ summaryData.pendingOrders }}</p>
            <p class="card-subtitle">awaiting action</p>
          </div>

          <div class="summary-card">
            <div class="card-header">
              <h3>Processing</h3>
              <Zap size="20" class="card-icon processing-icon" />
            </div>
            <p class="card-value">{{ summaryData.processingOrders }}</p>
            <p class="card-subtitle">in progress</p>
          </div>

          <div class="summary-card">
            <div class="card-header">
              <h3>Completed</h3>
              <CheckCircle size="20" class="card-icon completed-icon" />
            </div>
            <p class="card-value">{{ summaryData.completedOrders }}</p>
            <p class="card-subtitle">fulfilled</p>
          </div>
        </div>

        <!-- Filters Section -->
        <div v-if="showFilters" class="filters-panel">
          <div class="filters-header">
            <h3>Filters</h3>
            <button @click="closeFilters" class="btn-close">
              <X size="20" />
            </button>
          </div>
          
          <div class="filters-content">
            <div class="filter-group">
              <label>Order Status</label>
              <select v-model="filters.status" class="filter-select">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Date Range</label>
              <select v-model="filters.dateRange" class="filter-select">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="quarter">This Quarter</option>
                <option value="year">This Year</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Customer</label>
              <select v-model="filters.customer" class="filter-select">
                <option value="">All Customers</option>
                <option v-for="customer in availableCustomers" :key="customer.id" :value="customer.id">
                  {{ customer.name }}
                </option>
              </select>
            </div>

            <div class="filter-group">
              <label>Payment Status</label>
              <select v-model="filters.paymentStatus" class="filter-select">
                <option value="">All</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
              </select>
            </div>

            <div class="filter-actions">
              <button @click="applyFilters" class="btn-apply">Apply Filters</button>
              <button @click="clearFilters" class="btn-clear">Clear</button>
            </div>
          </div>
        </div>

        <!-- Orders Table -->
        <div class="table-section">
          <div class="table-header">
            <h3>Orders List</h3>
            <div class="table-controls">
              <select v-model="itemsPerPage" class="items-select">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
              </select>
            </div>
          </div>

          <div v-if="paginatedOrders.length > 0" class="table-wrapper">
            <table class="orders-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Products</th>
                  <th>Total Amount</th>
                  <th>Order Date</th>
                  <th>Status</th>
                  <th>Payment</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in paginatedOrders" :key="order.id" class="table-row">
                  <td class="td-id">
                    <span class="order-badge">{{ order.id }}</span>
                  </td>
                  <td class="td-customer">
                    <div class="customer-info">
                      <span class="customer-name">{{ order.customerName }}</span>
                      <span class="customer-phone">{{ order.customerPhone }}</span>
                    </div>
                  </td>
                  <td class="td-products">
                    <span class="product-count">{{ order.items.length }} item(s)</span>
                  </td>
                  <td class="td-amount">
                    <span class="amount-badge">${{ formatNumber(order.totalAmount) }}</span>
                  </td>
                  <td class="td-date">{{ formatDate(order.orderDate) }}</td>
                  <td class="td-status">
                    <span class="status-badge" :class="`status-${order.status}`">
                      {{ capitalize(order.status) }}
                    </span>
                  </td>
                  <td class="td-payment">
                    <span class="payment-badge" :class="`payment-${order.paymentStatus}`">
                      {{ capitalize(order.paymentStatus) }}
                    </span>
                  </td>
                  <td class="td-actions">
                    <button @click="viewDetails(order)" class="action-btn view-btn" title="View Details">
                      <Eye size="16" />
                    </button>
                    <button v-if="order.status === 'pending'" @click="updateStatus(order, 'processing')" class="action-btn process-btn" title="Mark as Processing">
                      <Play size="16" />
                    </button>
                    <button v-if="order.status === 'processing'" @click="updateStatus(order, 'shipped')" class="action-btn ship-btn" title="Mark as Shipped">
                      <Truck size="16" />
                    </button>
                    <button @click="openActions(order)" class="action-btn menu-btn" title="More Options">
                      <MoreVertical size="16" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="empty-state">
            <ShoppingCart size="48" class="empty-icon" />
            <p>No orders found</p>
            <span class="empty-hint">Your customer orders will appear here</span>
          </div>

          <!-- Pagination -->
          <div v-if="paginatedOrders.length > 0" class="pagination">
            <button 
              @click="currentPage--" 
              :disabled="currentPage === 1"
              class="pagination-btn"
            >
              <ChevronLeft size="16" />
              Previous
            </button>
            
            <div class="pagination-info">
              Page {{ currentPage }} of {{ totalPages }}
            </div>
            
            <button 
              @click="currentPage++" 
              :disabled="currentPage === totalPages"
              class="pagination-btn"
            >
              Next
              <ChevronRight size="16" />
            </button>
          </div>
        </div>

        <!-- Details Modal -->
        <div v-if="selectedOrder" class="modal-overlay" @click="selectedOrder = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Order Details</h2>
              <button @click="selectedOrder = null" class="btn-close">
                <X size="20" />
              </button>
            </div>

            <div class="modal-body">
              <!-- Order Summary -->
              <div class="order-summary">
                <div class="summary-item">
                  <span class="label">Order ID</span>
                  <span class="value">{{ selectedOrder.id }}</span>
                </div>
                <div class="summary-item">
                  <span class="label">Order Date</span>
                  <span class="value">{{ formatDate(selectedOrder.orderDate) }}</span>
                </div>
                <div class="summary-item">
                  <span class="label">Status</span>
                  <span class="status-badge" :class="`status-${selectedOrder.status}`">
                    {{ capitalize(selectedOrder.status) }}
                  </span>
                </div>
                <div class="summary-item">
                  <span class="label">Payment Status</span>
                  <span class="payment-badge" :class="`payment-${selectedOrder.paymentStatus}`">
                    {{ capitalize(selectedOrder.paymentStatus) }}
                  </span>
                </div>
              </div>

              <!-- Customer Information -->
              <div class="section">
                <h3 class="section-title">Customer Information</h3>
                <div class="customer-details">
                  <div class="detail-item">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">{{ selectedOrder.customerName }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ selectedOrder.customerEmail }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">{{ selectedOrder.customerPhone }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Address</span>
                    <span class="detail-value">{{ selectedOrder.deliveryAddress }}</span>
                  </div>
                </div>
              </div>

              <!-- Order Items -->
              <div class="section">
                <h3 class="section-title">Order Items</h3>
                <div class="items-list">
                  <div v-for="item in selectedOrder.items" :key="item.id" class="item-row">
                    <span class="item-name">{{ item.productName }}</span>
                    <span class="item-qty">{{ item.quantity }} {{ item.unit }}</span>
                    <span class="item-price">${{ formatNumber(item.pricePerUnit) }}</span>
                    <span class="item-total">${{ formatNumber(item.totalPrice) }}</span>
                  </div>
                </div>
              </div>

              <!-- Order Summary Totals -->
              <div class="section">
                <h3 class="section-title">Order Summary</h3>
                <div class="totals">
                  <div class="total-row">
                    <span>Subtotal:</span>
                    <span>${{ formatNumber(selectedOrder.subtotal) }}</span>
                  </div>
                  <div class="total-row">
                    <span>Shipping:</span>
                    <span>${{ formatNumber(selectedOrder.shippingCost) }}</span>
                  </div>
                  <div class="total-row">
                    <span>Tax:</span>
                    <span>${{ formatNumber(selectedOrder.tax) }}</span>
                  </div>
                  <div class="total-row total">
                    <span>Total:</span>
                    <span>${{ formatNumber(selectedOrder.totalAmount) }}</span>
                  </div>
                </div>
              </div>

              <!-- Delivery Information -->
              <div class="section">
                <h3 class="section-title">Delivery Information</h3>
                <div class="delivery-info">
                  <div class="info-item">
                    <span class="info-label">Delivery Status:</span>
                    <span class="info-value">{{ selectedOrder.deliveryStatus }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Tracking Number:</span>
                    <span class="info-value">{{ selectedOrder.trackingNumber || 'Not available' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Expected Delivery:</span>
                    <span class="info-value">{{ formatDate(selectedOrder.expectedDelivery) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedOrder = null" class="btn-secondary">Close</button>
              <div class="action-buttons">
                <button v-if="selectedOrder.status === 'pending'" @click="updateStatusAndClose('processing')" class="btn-primary process">
                  <Play size="14" />
                  Mark as Processing
                </button>
                <button v-if="selectedOrder.status === 'processing'" @click="updateStatusAndClose('shipped')" class="btn-primary ship">
                  <Truck size="14" />
                  Mark as Shipped
                </button>
                <button v-if="selectedOrder.status !== 'delivered'" @click="updateStatusAndClose('delivered')" class="btn-primary deliver">
                  <CheckCircle size="14" />
                  Mark as Delivered
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
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import {
  Filter, Download, AlertCircle, RotateCcw, ShoppingCart, Clock, Zap, 
  CheckCircle, X, Eye, Play, Truck, MoreVertical, ChevronLeft, ChevronRight
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

// State
const loading = ref(false)
const error = ref(null)
const showFilters = ref(false)
const selectedOrder = ref(null)
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Data
const orders = ref([])
const availableCustomers = ref([])
const summaryData = ref({
  totalOrders: 0,
  pendingOrders: 0,
  processingOrders: 0,
  completedOrders: 0
})

// Filters
const filters = ref({
  status: '',
  dateRange: 'all',
  customer: '',
  paymentStatus: ''
})

// Mock data - replace with API calls
const mockOrders = [
  {
    id: 'ORD-2026-001',
    customerName: 'John Smith',
    customerEmail: 'john@example.com',
    customerPhone: '+1-555-0101',
    deliveryAddress: '123 Main St, Springfield, IL 62701',
    totalAmount: 450.50,
    subtotal: 425.00,
    shippingCost: 15.00,
    tax: 10.50,
    orderDate: '2026-09-01',
    status: 'delivered',
    paymentStatus: 'paid',
    deliveryStatus: 'Delivered',
    trackingNumber: 'TRK123456789',
    expectedDelivery: '2026-09-05',
    items: [
      { id: 1, productName: 'Tomatoes', quantity: 25, unit: 'kg', pricePerUnit: 2.50, totalPrice: 62.50 },
      { id: 2, productName: 'Carrots', quantity: 15, unit: 'kg', pricePerUnit: 1.80, totalPrice: 27.00 }
    ]
  },
  {
    id: 'ORD-2026-002',
    customerName: 'Jane Doe',
    customerEmail: 'jane@example.com',
    customerPhone: '+1-555-0102',
    deliveryAddress: '456 Oak Ave, Chicago, IL 60601',
    totalAmount: 320.00,
    subtotal: 300.00,
    shippingCost: 12.00,
    tax: 8.00,
    orderDate: '2026-09-02',
    status: 'processing',
    paymentStatus: 'paid',
    deliveryStatus: 'In Transit',
    trackingNumber: 'TRK123456790',
    expectedDelivery: '2026-09-06',
    items: [
      { id: 1, productName: 'Potatoes', quantity: 50, unit: 'kg', pricePerUnit: 1.20, totalPrice: 60.00 },
      { id: 2, productName: 'Corn', quantity: 30, unit: 'kg', pricePerUnit: 0.80, totalPrice: 24.00 }
    ]
  },
  {
    id: 'ORD-2026-003',
    customerName: 'Bob Johnson',
    customerEmail: 'bob@example.com',
    customerPhone: '+1-555-0103',
    deliveryAddress: '789 Pine Rd, Miami, FL 33101',
    totalAmount: 580.75,
    subtotal: 555.00,
    shippingCost: 18.00,
    tax: 7.75,
    orderDate: '2026-09-03',
    status: 'pending',
    paymentStatus: 'pending',
    deliveryStatus: 'Awaiting Pickup',
    trackingNumber: null,
    expectedDelivery: '2026-09-07',
    items: [
      { id: 1, productName: 'Organic Tomatoes', quantity: 40, unit: 'kg', pricePerUnit: 3.50, totalPrice: 140.00 }
    ]
  },
  {
    id: 'ORD-2026-004',
    customerName: 'Alice Williams',
    customerEmail: 'alice@example.com',
    customerPhone: '+1-555-0104',
    deliveryAddress: '321 Elm St, Houston, TX 77001',
    totalAmount: 275.25,
    subtotal: 260.00,
    shippingCost: 12.00,
    tax: 3.25,
    orderDate: '2026-09-04',
    status: 'shipped',
    paymentStatus: 'paid',
    deliveryStatus: 'Shipped',
    trackingNumber: 'TRK123456791',
    expectedDelivery: '2026-09-08',
    items: [
      { id: 1, productName: 'Fresh Lettuce', quantity: 20, unit: 'kg', pricePerUnit: 1.50, totalPrice: 30.00 }
    ]
  }
]

const mockCustomers = [
  { id: 1, name: 'John Smith' },
  { id: 2, name: 'Jane Doe' },
  { id: 3, name: 'Bob Johnson' },
  { id: 4, name: 'Alice Williams' }
]

// Computed
const filteredOrders = computed(() => {
  let result = orders.value

  if (filters.value.status) {
    result = result.filter(o => o.status === filters.value.status)
  }

  if (filters.value.customer) {
    result = result.filter(o => o.customerName === filters.value.customer)
  }

  if (filters.value.paymentStatus) {
    result = result.filter(o => o.paymentStatus === filters.value.paymentStatus)
  }

  return result
})

const totalPages = computed(() => {
  return Math.ceil(filteredOrders.value.length / parseInt(itemsPerPage.value))
})

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * parseInt(itemsPerPage.value)
  const end = start + parseInt(itemsPerPage.value)
  return filteredOrders.value.slice(start, end)
})

// Methods
const fetchOrders = async () => {
  loading.value = true
  error.value = null
  try {
    // Replace with actual API call
    // const res = await farmerAPI.getCustomerOrders()
    orders.value = mockOrders
    availableCustomers.value = mockCustomers
    
    // Calculate summary
    summaryData.value = {
      totalOrders: orders.value.length,
      pendingOrders: orders.value.filter(o => o.status === 'pending').length,
      processingOrders: orders.value.filter(o => o.status === 'processing').length,
      completedOrders: orders.value.filter(o => o.status === 'delivered').length
    }
  } catch (err) {
    console.error('Error fetching orders:', err)
    error.value = 'Failed to load orders. Please try again.'
  } finally {
    loading.value = false
  }
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
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
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const openFilters = () => {
  showFilters.value = true
}

const closeFilters = () => {
  showFilters.value = false
}

const applyFilters = () => {
  currentPage.value = 1
  closeFilters()
}

const clearFilters = () => {
  filters.value = {
    status: '',
    dateRange: 'all',
    customer: '',
    paymentStatus: ''
  }
}

const exportData = () => {
  const headers = ['Order ID', 'Customer', 'Total', 'Date', 'Status', 'Payment']
  const rows = filteredOrders.value.map(order => [
    order.id,
    order.customerName,
    `$${formatNumber(order.totalAmount)}`,
    formatDate(order.orderDate),
    order.status,
    order.paymentStatus
  ])

  const csv = [headers, ...rows].map(row => row.join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `customer-orders-${new Date().toISOString().split('T')[0]}.csv`
  a.click()
}

const viewDetails = (order) => {
  selectedOrder.value = { ...order }
}

const updateStatus = (order, newStatus) => {
  // Update order status
  const index = orders.value.findIndex(o => o.id === order.id)
  if (index !== -1) {
    orders.value[index].status = newStatus
  }
}

const updateStatusAndClose = (newStatus) => {
  if (selectedOrder.value) {
    updateStatus(selectedOrder.value, newStatus)
    selectedOrder.value = null
  }
}

const openActions = (order) => {
  // Implement additional actions menu
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  fetchOrders()
})
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

/* Page Header */
.page-header {
  background: white;
  padding: 30px;
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
  border-color: #d1d5db;
}

.btn-export {
  background: #10b981;
  color: white;
  border-color: #10b981;
}

.btn-export:hover {
  background: #059669;
  border-color: #059669;
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
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
}

.error-icon {
  color: #dc2626;
}

.error-message {
  color: #991b1b;
  font-size: 16px;
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
  transition: all 0.2s;
}

.btn-retry:hover {
  background: #b91c1c;
}

/* Content */
.orders-content {
  padding: 30px;
  flex: 1;
}

/* Summary Section */
.summary-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s;
  border-left: 5px solid #3b82f6;
}

.summary-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.summary-card:nth-child(2) {
  border-left-color: #f59e0b;
}

.summary-card:nth-child(3) {
  border-left-color: #8b5cf6;
}

.summary-card:nth-child(4) {
  border-left-color: #10b981;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.card-header h3 {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0;
  font-weight: 700;
}

.card-icon {
  color: #d1d5db;
}

.card-value {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 8px 0 4px 0;
}

.card-subtitle {
  font-size: 12px;
  color: #9ca3af;
  margin: 0;
}

/* Filters Panel */
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

.filter-select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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

/* Table Section */
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

.table-controls {
  display: flex;
  gap: 12px;
}

.items-select {
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

.td-id {
  font-weight: 600;
}

.order-badge {
  background: #ecfdf5;
  color: #10b981;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.customer-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.customer-name {
  font-weight: 600;
  color: #1f2937;
}

.customer-phone {
  font-size: 12px;
  color: #9ca3af;
}

.product-count {
  background: #dbeafe;
  color: #1e40af;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.amount-badge {
  background: #fef3c7;
  color: #92400e;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  text-align: center;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-processing {
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

.payment-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.payment-paid {
  background: #d1fae5;
  color: #065f46;
}

.payment-pending {
  background: #fef3c7;
  color: #92400e;
}

.payment-failed {
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

.ship-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
  background: #eff6ff;
}

.menu-btn:hover {
  border-color: #8b5cf6;
  color: #8b5cf6;
  background: #f5f3ff;
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
  margin: 0 0 8px 0;
  color: #6b7280;
}

.empty-hint {
  font-size: 14px;
  color: #9ca3af;
}

/* Pagination */
.pagination {
  padding: 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
}

.pagination-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  color: #4b5563;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background: #f3f4f6;
  border-color: #10b981;
  color: #10b981;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-info {
  font-size: 14px;
  color: #6b7280;
  font-weight: 600;
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
  max-width: 800px;
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
  position: sticky;
  top: 0;
  background: white;
  z-index: 10;
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
  letter-spacing: 0.5px;
}

.summary-item .value {
  font-size: 14px;
  color: #1f2937;
  font-weight: 600;
}

.section {
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

.customer-details {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 14px;
  color: #1f2937;
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.item-row {
  display: grid;
  grid-template-columns: 1fr 150px 120px 120px;
  gap: 16px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
  align-items: center;
  font-size: 13px;
}

.item-name {
  font-weight: 600;
  color: #1f2937;
}

.item-qty,
.item-price,
.item-total {
  color: #6b7280;
}

.item-total {
  color: #10b981;
  font-weight: 600;
}

.totals {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  font-size: 13px;
  color: #6b7280;
}

.total-row.total {
  border-top: 2px solid #e5e7eb;
  padding-top: 12px;
  margin-top: 4px;
  font-weight: 700;
  color: #1f2937;
  font-size: 14px;
}

.delivery-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
}

.info-label {
  font-weight: 600;
  color: #4b5563;
  font-size: 13px;
}

.info-value {
  color: #1f2937;
  font-size: 13px;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.btn-primary,
.btn-secondary {
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-primary {
  background: #10b981;
  color: white;
}

.btn-primary:hover {
  background: #059669;
}

.btn-primary.ship {
  background: #3b82f6;
}

.btn-primary.ship:hover {
  background: #2563eb;
}

.btn-primary.deliver {
  background: #8b5cf6;
}

.btn-primary.deliver:hover {
  background: #7c3aed;
}

.btn-secondary {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

/* Responsive */
@media (max-width: 1024px) {
  .orders-container {
    margin-left: 0;
  }

  .customer-details {
    grid-template-columns: 1fr;
  }

  .item-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .orders-content {
    padding: 20px;
  }

  .summary-section {
    grid-template-columns: repeat(2, 1fr);
  }

  .orders-table {
    font-size: 12px;
  }

  .orders-table th,
  .table-row td {
    padding: 12px 8px;
  }

  .td-actions {
    flex-direction: column;
  }

  .order-summary {
    grid-template-columns: 1fr;
  }

  .modal-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .action-buttons {
    width: 100%;
  }

  .action-buttons .btn-primary {
    flex: 1;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .summary-section {
    grid-template-columns: 1fr;
  }

  .filters-content {
    grid-template-columns: 1fr;
  }
}
</style>
