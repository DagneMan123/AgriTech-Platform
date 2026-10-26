<template>
  <div class="buyer-layout">
    <!-- Sidebar -->
    <BuyerSidebar @logout="handleLogout" />

    <!-- Main Content -->
    <div class="orders-container">
      <!-- Header Section -->
      <div class="orders-header">
        <div>
          <h1 class="page-title">My Orders</h1>
          <p class="page-subtitle">Track and manage your purchases</p>
        </div>
        <div class="header-actions">
          <button @click="showFilters = !showFilters" class="btn-secondary">
            <i class="fas fa-sliders-h"></i> Filters
          </button>
          <button @click="refreshOrders" class="btn-secondary">
            <i class="fas fa-sync-alt"></i> Refresh
          </button>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="stats-section">
        <div class="stat-card">
          <div class="stat-icon total">
            <i class="fas fa-shopping-bag"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ totalOrders }}</div>
            <div class="stat-label">Total Orders</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon pending">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ pendingOrders }}</div>
            <div class="stat-label">Pending</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon shipped">
            <i class="fas fa-box"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ shippedOrders }}</div>
            <div class="stat-label">In Transit</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon delivered">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ deliveredOrders }}</div>
            <div class="stat-label">Delivered</div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="buyerStore.orders.length === 0" class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-inbox"></i>
        </div>
        <h2>No orders yet</h2>
        <p>Start shopping to see your orders here</p>
        <router-link to="/buyer/marketplace" class="btn-primary">
          <i class="fas fa-shopping-bag"></i> Continue Shopping
        </router-link>
      </div>

      <!-- Filters Section -->
      <div v-if="showFilters" class="filter-section">
        <div class="filter-group-row">
          <div class="filter-group">
            <label>Status:</label>
            <select v-model="selectedStatus" class="filter-select">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="filter-group">
            <label>Date Range:</label>
            <select v-model="selectedDateRange" class="filter-select">
              <option value="">All Time</option>
              <option value="today">Today</option>
              <option value="week">Last 7 Days</option>
              <option value="month">Last 30 Days</option>
              <option value="year">This Year</option>
            </select>
          </div>
          <div class="filter-group">
            <label>Sort by:</label>
            <select v-model="sortBy" class="filter-select">
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="highest-amount">Highest Amount</option>
              <option value="lowest-amount">Lowest Amount</option>
            </select>
          </div>
        </div>
        <div class="filter-actions">
          <button @click="applyFilters" class="btn-primary">Apply Filters</button>
          <button @click="clearFilters" class="btn-secondary">Clear Filters</button>
        </div>
      </div>

      <!-- Orders List -->
      <div v-if="buyerStore.orders.length > 0" class="orders-list">
        <div v-for="order in filteredAndSortedOrders" :key="order.id" class="order-card">
          <!-- Order Header -->
          <div class="order-header">
            <div class="order-title">
              <h3 class="order-id">Order #{{ order.id }}</h3>
              <p class="order-date">{{ formatDate(order.created_at) }}</p>
            </div>
            <div class="order-status">
              <span :class="['status-badge', `status-${order.status}`]">
                {{ formatStatus(order.status) }}
              </span>
              <span v-if="order.status !== 'delivered'" class="progress-indicator">
                <i class="fas fa-arrow-right"></i>
              </span>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="order-summary">
            <div class="summary-item">
              <span class="label">Items:</span>
              <span class="value">{{ order.items_count || 0 }} item(s)</span>
            </div>
            <div class="summary-item">
              <span class="label">From:</span>
              <span class="value">{{ order.supplier_name || 'Supplier' }}</span>
            </div>
            <div class="summary-item">
              <span class="label">Est. Delivery:</span>
              <span class="value">{{ formatDate(order.estimated_delivery) || 'Pending' }}</span>
            </div>
            <div class="summary-item">
              <span class="label">Amount:</span>
              <span class="value amount">Ksh {{ formatNumber(order.total_amount) }}</span>
            </div>
          </div>

          <!-- Items Preview -->
          <div v-if="order.items && order.items.length > 0" class="items-preview">
            <div class="preview-title">Items:</div>
            <div class="items-list">
              <div v-for="(item, index) in order.items.slice(0, 3)" :key="index" class="item">
                <span class="item-name">{{ item.product_name }}</span>
                <span class="item-qty">x{{ item.quantity }}</span>
              </div>
              <div v-if="order.items.length > 3" class="item more-items">
                +{{ order.items.length - 3 }} more
              </div>
            </div>
          </div>

          <!-- Order Timeline -->
          <div class="order-timeline">
            <div :class="['timeline-item', { active: isStatusReached('pending', order.status) }]">
              <div class="timeline-dot"></div>
              <div class="timeline-label">Order Placed</div>
            </div>
            <div class="timeline-line"></div>
            <div :class="['timeline-item', { active: isStatusReached('confirmed', order.status) }]">
              <div class="timeline-dot"></div>
              <div class="timeline-label">Confirmed</div>
            </div>
            <div class="timeline-line"></div>
            <div :class="['timeline-item', { active: isStatusReached('shipped', order.status) }]">
              <div class="timeline-dot"></div>
              <div class="timeline-label">Shipped</div>
            </div>
            <div class="timeline-line"></div>
            <div :class="['timeline-item', { active: isStatusReached('delivered', order.status) }]">
              <div class="timeline-dot"></div>
              <div class="timeline-label">Delivered</div>
            </div>
          </div>

          <!-- Order Actions -->
          <div class="order-actions">
            <button @click="viewOrderDetails(order.id)" class="btn-action btn-primary">
              <i class="fas fa-eye"></i> View Details
            </button>
            <button v-if="order.status === 'delivered'" @click="leaveReview(order.id)" class="btn-action btn-secondary">
              <i class="fas fa-star"></i> Leave Review
            </button>
            <button v-if="order.status === 'pending'" @click="cancelOrder(order.id)" class="btn-action btn-danger">
              <i class="fas fa-times"></i> Cancel
            </button>
            <button @click="contactSupport(order.id)" class="btn-action btn-outline">
              <i class="fas fa-headset"></i> Support
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="buyerStore.orders.length > 0" class="pagination">
        <button :disabled="currentPage === 1" class="btn-pagination">
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
        <button :disabled="currentPage === totalPages" class="btn-pagination">
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useBuyerStore } from '@/stores/buyerStore'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { onMounted, computed, ref } from 'vue'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

const buyerStore = useBuyerStore()
const authStore = useAuthStore()
const router = useRouter()

// State
const showFilters = ref(false)
const selectedStatus = ref('')
const selectedDateRange = ref('')
const sortBy = ref('newest')
const currentPage = ref(1)
const itemsPerPage = 10

onMounted(async () => {
  try {
    await buyerStore.fetchOrders()
  } catch (error) {
    console.error('Failed to load orders:', error)
  }
})

// Computed properties
const filteredAndSortedOrders = computed(() => {
  let orders = [...buyerStore.orders]

  // Filter by status
  if (selectedStatus.value) {
    orders = orders.filter(o => o.status === selectedStatus.value)
  }

  // Filter by date range
  if (selectedDateRange.value) {
    const now = new Date()
    const createdDate = new Date()
    switch (selectedDateRange.value) {
      case 'today':
        createdDate.setHours(0, 0, 0, 0)
        orders = orders.filter(o => new Date(o.created_at) >= createdDate)
        break
      case 'week':
        createdDate.setDate(createdDate.getDate() - 7)
        orders = orders.filter(o => new Date(o.created_at) >= createdDate)
        break
      case 'month':
        createdDate.setDate(createdDate.getDate() - 30)
        orders = orders.filter(o => new Date(o.created_at) >= createdDate)
        break
      case 'year':
        createdDate.setFullYear(createdDate.getFullYear() - 1)
        orders = orders.filter(o => new Date(o.created_at) >= createdDate)
        break
    }
  }

  // Sort
  switch (sortBy.value) {
    case 'oldest':
      orders.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
      break
    case 'highest-amount':
      orders.sort((a, b) => (b.total_amount || 0) - (a.total_amount || 0))
      break
    case 'lowest-amount':
      orders.sort((a, b) => (a.total_amount || 0) - (b.total_amount || 0))
      break
    case 'newest':
    default:
      orders.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      break
  }

  return orders.slice((currentPage.value - 1) * itemsPerPage, currentPage.value * itemsPerPage)
})

const totalOrders = computed(() => buyerStore.orders.length)
const pendingOrders = computed(() => buyerStore.orders.filter(o => o.status === 'pending').length)
const shippedOrders = computed(() => buyerStore.orders.filter(o => o.status === 'shipped').length)
const deliveredOrders = computed(() => buyerStore.orders.filter(o => o.status === 'delivered').length)
const totalPages = computed(() => Math.ceil(buyerStore.orders.length / itemsPerPage))

// Methods
const formatDate = (date: string | null) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  }).format(num || 0)
}

const formatStatus = (status: string) => {
  const map: Record<string, string> = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    shipped: 'In Transit',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
  }
  return map[status] || status
}

const isStatusReached = (checkStatus: string, orderStatus: string) => {
  const statusOrder = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled']
  return statusOrder.indexOf(orderStatus) >= statusOrder.indexOf(checkStatus)
}

const applyFilters = () => {
  currentPage.value = 1
  console.log('Filters applied')
}

const clearFilters = () => {
  selectedStatus.value = ''
  selectedDateRange.value = ''
  sortBy.value = 'newest'
  currentPage.value = 1
  showFilters.value = false
}

const refreshOrders = async () => {
  try {
    await buyerStore.fetchOrders()
  } catch (error) {
    console.error('Failed to refresh orders:', error)
  }
}

const viewOrderDetails = (orderId: number) => {
  router.push(`/buyer/orders/${orderId}`)
}

const leaveReview = (orderId: number) => {
  router.push(`/buyer/reviews?order_id=${orderId}`)
}

const cancelOrder = async (orderId: number) => {
  if (confirm('Are you sure you want to cancel this order?')) {
    try {
      // API call to cancel order
      console.log('Cancelling order:', orderId)
      await buyerStore.fetchOrders()
    } catch (error) {
      console.error('Failed to cancel order:', error)
    }
  }
}

const contactSupport = (orderId: number) => {
  // Open support ticket
  console.log('Contacting support for order:', orderId)
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

</script>

<style scoped>
/* Layout */
.buyer-layout {
  display: flex;
  height: 100vh;
}

.orders-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f5f5;
  min-height: 100vh;
  padding: 40px 20px;
}

/* Header */
.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 40px;
  gap: 20px;
}

.page-title {
  font-size: 36px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.page-subtitle {
  font-size: 16px;
  color: #6b7280;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

/* Stats Section */
.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  display: flex;
  gap: 16px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
}

.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-2px);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: white;
  flex-shrink: 0;
}

.stat-icon.total {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.pending {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-icon.shipped {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-icon.delivered {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  margin-top: 4px;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 12px;
  border: 2px dashed #d1d5db;
  margin: 40px 0;
}

.empty-icon {
  font-size: 64px;
  color: #d1d5db;
  margin-bottom: 20px;
}

.empty-state h2 {
  font-size: 24px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.empty-state p {
  font-size: 16px;
  color: #6b7280;
  margin: 0 0 24px 0;
}

/* Filter Section */
.filter-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 30px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.filter-group-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-group label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.filter-select {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: all 0.3s;
}

.filter-select:hover,
.filter-select:focus {
  border-color: #3b82f6;
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-actions {
  display: flex;
  gap: 12px;
}

/* Orders List */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.order-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s;
  border-left: 4px solid #3b82f6;
}

.order-card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

/* Order Header */
.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: linear-gradient(to right, #f9fafb, #ffffff);
}

.order-title {
  display: flex;
  gap: 16px;
  align-items: center;
}

.order-id {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.order-date {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
}

.order-status {
  display: flex;
  align-items: center;
  gap: 12px;
}

.status-badge {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
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
  background: #e9d5ff;
  color: #6b21a8;
}

.status-delivered {
  background: #d1fae5;
  color: #065f46;
}

.status-cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.progress-indicator {
  color: #9ca3af;
  font-size: 14px;
}

/* Order Summary */
.order-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.summary-item .label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
}

.summary-item .value {
  font-size: 15px;
  font-weight: 600;
  color: #1f2937;
}

.summary-item .amount {
  font-size: 18px;
  color: #10b981;
}

/* Items Preview */
.items-preview {
  padding: 16px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}

.preview-title {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  margin-bottom: 12px;
}

.items-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 13px;
  color: #374151;
}

.item-name {
  font-weight: 500;
}

.item-qty {
  color: #9ca3af;
}

.more-items {
  background: #f3f4f6;
  border-color: #d1d5db;
  color: #6b7280;
}

/* Order Timeline */
.order-timeline {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
  overflow-x: auto;
  gap: 4px;
}

.timeline-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  opacity: 0.4;
  transition: all 0.3s;
}

.timeline-item.active {
  opacity: 1;
}

.timeline-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #e5e7eb;
  border: 3px solid white;
  transition: all 0.3s;
}

.timeline-item.active .timeline-dot {
  background: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.timeline-label {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
  white-space: nowrap;
  text-align: center;
}

.timeline-line {
  flex: 1;
  height: 2px;
  background: #e5e7eb;
  margin: 8px 0;
  min-width: 20px;
}

/* Order Actions */
.order-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
  padding: 20px 24px;
}

.btn-action {
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  white-space: nowrap;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
  background: #f0f0f0;
  color: #1f2937;
  border: 1px solid #e5e7eb;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.btn-danger {
  background: #fee2e2;
  color: #dc2626;
}

.btn-danger:hover {
  background: #fecaca;
}

.btn-outline {
  background: white;
  color: #6b7280;
  border: 1px solid #e5e7eb;
}

.btn-outline:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  margin-top: 40px;
  padding: 20px;
}

.btn-pagination {
  padding: 10px 16px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  color: #1f2937;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-pagination:hover:not(:disabled) {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 14px;
  color: #6b7280;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 1024px) {
  .orders-container {
    padding: 30px 15px;
  }

  .stats-section {
    grid-template-columns: repeat(2, 1fr);
  }

  .order-summary {
    grid-template-columns: repeat(2, 1fr);
  }

  .order-actions {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .orders-container {
    margin-left: 0;
    padding: 20px 15px;
  }

  .orders-header {
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions button {
    flex: 1;
  }

  .page-title {
    font-size: 28px;
  }

  .stats-section {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .stat-card {
    padding: 16px;
  }

  .stat-value {
    font-size: 24px;
  }

  .stat-icon {
    width: 50px;
    height: 50px;
    font-size: 22px;
  }

  .filter-group-row {
    grid-template-columns: 1fr;
  }

  .order-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .order-summary {
    grid-template-columns: repeat(2, 1fr);
  }

  .order-timeline {
    overflow-x: auto;
    padding: 16px;
    gap: 2px;
  }

  .order-actions {
    grid-template-columns: repeat(2, 1fr);
  }

  .pagination {
    flex-wrap: wrap;
  }
}

@media (max-width: 480px) {
  .orders-container {
    padding: 16px 12px;
  }

  .page-title {
    font-size: 24px;
  }

  .stats-section {
    grid-template-columns: 1fr;
  }

  .stat-card {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .order-summary {
    grid-template-columns: 1fr;
  }

  .order-actions {
    grid-template-columns: 1fr;
  }

  .filter-group-row {
    grid-template-columns: 1fr;
  }

  .order-timeline {
    flex-wrap: wrap;
  }

  .timeline-line {
    display: none;
  }

  .btn-action {
    padding: 8px 12px;
    font-size: 12px;
  }
}
</style>
