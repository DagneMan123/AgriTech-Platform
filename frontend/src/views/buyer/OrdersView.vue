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
          <p class="page-subtitle">Track and manage your purchases seamlessly</p>
        </div>
        <div class="header-actions">
          
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

      <!-- Filters Section (Without Apply/Clear Buttons - Auto Applies) -->
      <div class="filter-section">
        <div class="filter-grid-3">
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
      </div>

      <!-- Empty State -->
      <div v-if="totalOrders === 0" class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-inbox"></i>
        </div>
        <h2>No orders yet</h2>
        <p>Start shopping to see your orders here</p>
        <router-link to="/buyer/marketplace" class="btn-primary">
          <i class="fas fa-shopping-bag"></i> Continue Shopping
        </router-link>
      </div>

      <!-- Orders List -->
      <div v-if="filteredAndSortedOrders.length > 0" class="orders-list">
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
      <div v-if="totalOrders > 0" class="pagination">
        <button :disabled="currentPage === 1" @click="currentPage--" class="btn-pagination">
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
        <button :disabled="currentPage === totalPages" @click="currentPage++" class="btn-pagination">
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
import { onMounted, computed, ref, watch } from 'vue'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

const buyerStore = useBuyerStore()
const authStore = useAuthStore()
const router = useRouter()

// State
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

// Reset pagination when filters change
watch([selectedStatus, selectedDateRange, sortBy], () => {
  currentPage.value = 1
})

// Computed properties with safe Array check
const ordersArray = computed(() => {
  return Array.isArray(buyerStore.orders) ? buyerStore.orders : []
})

const filteredAndSortedOrders = computed(() => {
  let orders = [...ordersArray.value]

  if (selectedStatus.value) {
    orders = orders.filter(o => o && o.status === selectedStatus.value)
  }

  if (selectedDateRange.value) {
    const createdDate = new Date()
    switch (selectedDateRange.value) {
      case 'today':
        createdDate.setHours(0, 0, 0, 0)
        orders = orders.filter(o => o && new Date(o.created_at) >= createdDate)
        break
      case 'week':
        createdDate.setDate(createdDate.getDate() - 7)
        orders = orders.filter(o => o && new Date(o.created_at) >= createdDate)
        break
      case 'month':
        createdDate.setDate(createdDate.getDate() - 30)
        orders = orders.filter(o => o && new Date(o.created_at) >= createdDate)
        break
      case 'year':
        createdDate.setFullYear(createdDate.getFullYear() - 1)
        orders = orders.filter(o => o && new Date(o.created_at) >= createdDate)
        break
    }
  }

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

const totalOrders = computed(() => ordersArray.value.length)
const pendingOrders = computed(() => ordersArray.value.filter(o => o?.status === 'pending').length)
const shippedOrders = computed(() => ordersArray.value.filter(o => o?.status === 'shipped').length)
const deliveredOrders = computed(() => ordersArray.value.filter(o => o?.status === 'delivered').length)
const totalPages = computed(() => Math.ceil(totalOrders.value / itemsPerPage) || 1)

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
      console.log('Cancelling order:', orderId)
      await buyerStore.fetchOrders()
    } catch (error) {
      console.error('Failed to cancel order:', error)
    }
  }
}

const contactSupport = (orderId: number) => {
  console.log('Contacting support for order:', orderId)
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.buyer-layout {
  display: flex;
  height: 100vh;
  background-color: #0b0f19;
  color: #f3f4f6;
  font-family: 'Inter', sans-serif;
}

.orders-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #0b0f19;
  min-height: 100vh;
  padding: 40px 30px;
}

.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 35px;
  gap: 20px;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 6px 0;
  letter-spacing: -0.5px;
}

.page-subtitle {
  font-size: 15px;
  color: #9ca3af;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.header-actions button:focus {
  outline: none;
  box-shadow: none;
}

.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: #111827;
  border: 1px solid #1f2937;
  border-radius: 14px;
  padding: 22px;
  display: flex;
  gap: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.stat-card:hover {
  border-color: #374151;
  transform: translateY(-3px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
}

.stat-icon {
  width: 54px;
  height: 54px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  flex-shrink: 0;
}

.stat-icon.total { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
.stat-icon.pending { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.stat-icon.shipped { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
.stat-icon.delivered { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }

.stat-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #ffffff;
}

.stat-label {
  font-size: 13px;
  color: #9ca3af;
  margin-top: 2px;
  font-weight: 500;
}

/* Filter Section (3 Columns Layout without buttons) */
.filter-section {
  background: #111827;
  border: 1px solid #1f2937;
  border-radius: 14px;
  padding: 24px;
  margin-bottom: 30px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
}

.filter-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
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
  color: #d1d5db;
}

.filter-select {
  width: 100%;
  padding: 10px 14px;
  background: #1f2937;
  border: 1px solid #374151;
  border-radius: 8px;
  font-size: 14px;
  color: #f3f4f6;
  cursor: pointer;
  transition: all 0.3s;
}

.filter-select:hover,
.filter-select:focus {
  border-color: #6366f1;
  outline: none;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

.empty-state {
  text-align: center;
  padding: 80px 20px;
  background: #111827;
  border-radius: 14px;
  border: 2px dashed #374151;
  margin: 30px 0;
}

.empty-icon {
  font-size: 56px;
  color: #4b5563;
  margin-bottom: 16px;
}

.empty-state h2 {
  font-size: 22px;
  font-weight: 600;
  color: #ffffff;
  margin: 0 0 6px 0;
}

.empty-state p {
  font-size: 15px;
  color: #9ca3af;
  margin: 0 0 20px 0;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.order-card {
  background: #111827;
  border: 1px solid #1f2937;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  border-left: 4px solid #6366f1;
}

.order-card:hover {
  border-color: #374151;
  box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.4);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  border-bottom: 1px solid #1f2937;
  background: #161e2e;
}

.order-title {
  display: flex;
  gap: 16px;
  align-items: center;
}

.order-id {
  font-size: 17px;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}

.order-date {
  font-size: 13px;
  color: #9ca3af;
  margin: 0;
}

.order-status {
  display: flex;
  align-items: center;
  gap: 10px;
}

.status-badge {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.status-confirmed { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.status-shipped { background: rgba(168, 85, 247, 0.15); color: #c084fc; }
.status-delivered { background: rgba(16, 185, 129, 0.15); color: #34d399; }
.status-cancelled { background: rgba(239, 68, 68, 0.15); color: #f87171; }

.progress-indicator {
  color: #6b7280;
  font-size: 13px;
}

.order-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  padding: 18px 24px;
  border-bottom: 1px solid #1f2937;
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.summary-item .label {
  font-size: 11px;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.summary-item .value {
  font-size: 14px;
  font-weight: 600;
  color: #f3f4f6;
}

.summary-item .amount {
  font-size: 16px;
  color: #34d399;
}

.items-preview {
  padding: 14px 24px;
  border-bottom: 1px solid #1f2937;
  background: #0d1322;
}

.preview-title {
  font-size: 11px;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  margin-bottom: 10px;
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
  padding: 5px 10px;
  background: #1f2937;
  border: 1px solid #374151;
  border-radius: 6px;
  font-size: 12px;
  color: #e5e7eb;
}

.item-name {
  font-weight: 500;
}

.item-qty {
  color: #9ca3af;
}

.more-items {
  background: #111827;
  color: #9ca3af;
}

.order-timeline {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px;
  border-bottom: 1px solid #1f2937;
  background: #0d1322;
  overflow-x: auto;
  gap: 4px;
}

.timeline-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
  opacity: 0.35;
  transition: all 0.3s;
}

.timeline-item.active {
  opacity: 1;
}

.timeline-dot {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #374151;
  border: 3px solid #111827;
}

.timeline-item.active .timeline-dot {
  background: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
}

.timeline-label {
  font-size: 11px;
  font-weight: 600;
  color: #9ca3af;
  white-space: nowrap;
}

.timeline-line {
  flex: 1;
  height: 2px;
  background: #374151;
  margin: 6px 0;
  min-width: 20px;
}

.order-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
  padding: 18px 24px;
}

.btn-action {
  padding: 9px 14px;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  white-space: nowrap;
}

.btn-primary {
  background: #6366f1;
  color: white;
}

.btn-primary:hover {
  background: #4f46e5;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

.btn-secondary {
  background: #1f2937;
  color: #f3f4f6;
  border: 1px solid #374151;
}

.btn-secondary:hover {
  background: #374151;
  border-color: #4b5563;
}

.btn-danger {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.btn-danger:hover {
  background: rgba(239, 68, 68, 0.25);
}

.btn-outline {
  background: transparent;
  color: #9ca3af;
  border: 1px solid #374151;
}

.btn-outline:hover {
  background: #1f2937;
  color: #ffffff;
  border-color: #4b5563;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 35px;
  padding: 10px;
}

.btn-pagination {
  padding: 8px 14px;
  border: 1px solid #374151;
  border-radius: 8px;
  background: #111827;
  color: #f3f4f6;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-pagination:hover:not(:disabled) {
  background: #1f2937;
  border-color: #4b5563;
}

.btn-pagination:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-info {
  font-size: 13px;
  color: #9ca3af;
  font-weight: 600;
}

@media (max-width: 1024px) {
  .orders-container {
    padding: 25px 15px;
  }
  .filter-grid-3 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .orders-container {
    margin-left: 0;
    padding: 20px 12px;
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
  .filter-grid-3 {
    grid-template-columns: 1fr;
  }
}
</style>