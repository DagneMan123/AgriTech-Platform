<template>
  <div class="orders-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="orders-container">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h1>Customer Orders</h1>
          <p>Manage and track orders from your customers</p>
        </div>
        <div class="header-actions">
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
        <!-- 4-Stage Lifecycle KPI Summary Cards -->
        <div class="summary-section">
          <!-- Stage 1: Pending -->
          <div class="summary-card cursor-pointer" @click="filters.status = 'pending'">
            <div class="card-header">
              <h3>Pending</h3>
              <Clock size="20" class="card-icon pending-icon" />
            </div>
            <p class="card-value">{{ summaryData.pendingOrders }}</p>
            <p class="card-subtitle">awaiting review &amp; confirmation</p>
          </div>

          <!-- Stage 2: Confirmed -->
          <div class="summary-card cursor-pointer" @click="filters.status = 'confirmed'">
            <div class="card-header">
              <h3>Confirmed</h3>
              <ShieldCheck size="20" class="card-icon" style="color: #3b82f6;" />
            </div>
            <p class="card-value">{{ summaryData.confirmedOrders }}</p>
            <p class="card-subtitle">escrow secured &amp; dispatch planned</p>
          </div>

          <!-- Stage 3: Shipped -->
          <div class="summary-card cursor-pointer" @click="filters.status = 'shipped'">
            <div class="card-header">
              <h3>Shipped</h3>
              <Truck size="20" class="card-icon" style="color: #8b5cf6;" />
            </div>
            <p class="card-value">{{ summaryData.shippedOrders }}</p>
            <p class="card-subtitle">dispatched &amp; in transit</p>
          </div>

          <!-- Stage 4: Delivered & Paid -->
          <div class="summary-card cursor-pointer" @click="filters.status = 'delivered'">
            <div class="card-header">
              <h3>Delivered &amp; Paid</h3>
              <CheckCircle size="20" class="card-icon completed-icon" style="color: #10b981;" />
            </div>
            <p class="card-value">{{ summaryData.completedOrders }}</p>
            <p class="card-subtitle">escrow funds released to wallet</p>
          </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-panel">
          <div class="filters-header">
            <h3>Filters</h3>
          </div>
          
          <div class="filters-content">
            <div class="filter-group">
              <label>Order Status</label>
              <select v-model="filters.status" class="filter-select">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered &amp; Paid</option>
                <option value="refund_requested">Refund Requested / Under Review</option>
                <option value="refunded">Refunded</option>
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
                    <span class="amount-badge">ETB {{ formatNumber(order.totalAmount) }}</span>
                  </td>
                  <td class="td-date">{{ formatDate(order.orderDate) }}</td>
                  <td class="td-status">
                    <span class="status-badge" :class="getOrderStatusBadge(order).class">
                      {{ getOrderStatusBadge(order).label }}
                    </span>
                  </td>
                  <td class="td-payment">
                    <span class="payment-badge" :class="getOrderPaymentBadge(order).class">
                      {{ getOrderPaymentBadge(order).label }}
                    </span>
                  </td>
                  <td class="td-actions">
                    <button @click="viewDetails(order)" class="action-btn view-btn" title="Review Order Tracking & Details">
                      <Eye size="16" />
                    </button>
                    <button
                      v-if="['pending', 'confirmed', 'processing', 'refund_requested', 'under_review', 'disputed'].includes(order.status)"
                      :disabled="isFulfillmentDisabled(order)"
                      @click="!isFulfillmentDisabled(order) && router.push('/farmer/transport-requests')"
                      class="action-btn ship-btn"
                      :class="isFulfillmentDisabled(order) ? 'opacity-40 cursor-not-allowed text-slate-400 border-slate-300 dark:border-slate-700 dark:text-slate-500' : 'text-blue-600 border-blue-200 hover:bg-blue-50 dark:hover:bg-blue-950/30'"
                      :title="isFulfillmentDisabled(order) ? 'Fulfillment Locked: Order has an active refund claim or dispute under review' : 'Request Transport Dispatch'"
                    >
                      <Truck size="16" />
                    </button>
                    <button
                      v-if="['shipped', 'in_transit'].includes(order.status) && !isFulfillmentDisabled(order)"
                      @click="router.push('/farmer/active-deliveries')"
                      class="action-btn deliver-btn text-purple-600 border-purple-200"
                      title="View Live Delivery Tracking"
                    >
                      <Navigation size="16" />
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
              <!-- Refund / Dispute Safety Guard Banner -->
              <div v-if="hasActiveRefundClaim(selectedOrder)" class="claim-alert-banner">
                <div class="banner-icon-box">
                  <AlertTriangle size="22" class="text-amber-600 dark:text-amber-400" />
                </div>
                <div class="banner-text">
                  <h4 class="banner-title">
                    Fulfillment Locked &mdash; Active {{ String(selectedOrder.refund?.status || '').includes('dispute') ? 'Dispute' : 'Refund Claim' }} Under Review
                  </h4>
                  <p class="banner-desc">
                    The customer has initiated a refund/dispute claim for this order. Transport dispatch and shipping actions are strictly disabled. Escrow funds remain held securely in Chapa Escrow Custody until resolution.
                  </p>
                  <div v-if="selectedOrder.refund?.reason" class="banner-claim-detail">
                    <strong>Claim Reason:</strong> {{ selectedOrder.refund.reason }}
                  </div>
                </div>
              </div>
              <div v-else-if="isOrderRefundSettled(selectedOrder)" class="settled-alert-banner">
                <div class="banner-icon-box">
                  <RotateCcw size="22" class="text-rose-600 dark:text-rose-400" />
                </div>
                <div class="banner-text">
                  <h4 class="banner-title">Order Refunded</h4>
                  <p class="banner-desc">
                    This order has been resolved and refunded to the customer. Fulfillment is permanently closed.
                  </p>
                </div>
              </div>

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
                  <span class="status-badge" :class="getOrderStatusBadge(selectedOrder).class">
                    {{ getOrderStatusBadge(selectedOrder).label }}
                  </span>
                </div>
                <div class="summary-item">
                  <span class="label">Payment Status</span>
                  <span class="payment-badge" :class="getOrderPaymentBadge(selectedOrder).class">
                    {{ getOrderPaymentBadge(selectedOrder).label }}
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
                    <span class="item-price">ETB {{ formatNumber(item.pricePerUnit) }}</span>
                    <span class="item-total">ETB {{ formatNumber(item.totalPrice) }}</span>
                  </div>
                </div>
              </div>

              <!-- Order Summary Totals -->
              <div class="section">
                <h3 class="section-title">Order Summary</h3>
                <div class="totals">
                  <div class="total-row">
                    <span>Subtotal:</span>
                    <span>ETB {{ formatNumber(selectedOrder.subtotal) }}</span>
                  </div>
                  <div class="total-row">
                    <span>Shipping:</span>
                    <span>ETB {{ formatNumber(selectedOrder.shippingCost) }}</span>
                  </div>
                  <div class="total-row">
                    <span>Tax:</span>
                    <span>ETB {{ formatNumber(selectedOrder.tax) }}</span>
                  </div>
                  <div class="total-row total">
                    <span>Total:</span>
                    <span>ETB {{ formatNumber(selectedOrder.totalAmount) }}</span>
                  </div>
                </div>
              </div>

              <!-- Delivery & Logistics Information -->
              <div class="section">
                <h3 class="section-title">Logistics & Warehouse Inspection</h3>
                <div class="delivery-info">
                  <div class="info-item">
                    <span class="info-label">Inspection Hub:</span>
                    <span class="info-value font-semibold">Kality Central Grain Warehouse</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Delivery Status:</span>
                    <span class="info-value font-semibold">{{ selectedOrder.deliveryStatus }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Chapa Escrow Status:</span>
                    <span class="info-value font-semibold" :class="selectedOrder.paymentStatus === 'paid' ? 'text-emerald-600' : 'text-amber-500'">
                      {{ selectedOrder.paymentStatus === 'paid' ? 'Paid (Released directly to Digital Wallet)' : 'Pending (Locked in Chapa Neutral Escrow)' }}
                    </span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Tracking / Custody Ref:</span>
                    <span class="info-value font-mono">{{ selectedOrder.trackingNumber || ('ET-WH-' + selectedOrder.id) }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Final Destination:</span>
                    <span class="info-value">{{ selectedOrder.deliveryAddress }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedOrder = null" class="btn-secondary">Close</button>
              <div class="action-buttons">
                <button
                  v-if="['pending', 'confirmed', 'processing', 'refund_requested', 'under_review', 'disputed'].includes(selectedOrder.status)"
                  :disabled="isFulfillmentDisabled(selectedOrder)"
                  @click="!isFulfillmentDisabled(selectedOrder) && router.push('/farmer/transport-requests')"
                  class="btn-primary ship"
                  :class="isFulfillmentDisabled(selectedOrder) ? 'opacity-50 cursor-not-allowed' : ''"
                  :style="isFulfillmentDisabled(selectedOrder) ? 'background: #64748b; cursor: not-allowed;' : 'background: #3b82f6;'"
                  :title="isFulfillmentDisabled(selectedOrder) ? 'Fulfillment Locked: Order has an active refund claim or dispute under review' : 'Request Transport Dispatch'"
                >
                  <Truck size="14" />
                  <span>{{ isFulfillmentDisabled(selectedOrder) ? 'Dispatch Locked (Dispute/Refund)' : 'Request Transport Dispatch' }}</span>
                </button>
                <button
                  v-if="['shipped', 'in_transit'].includes(selectedOrder.status) && !isFulfillmentDisabled(selectedOrder)"
                  @click="router.push('/farmer/active-deliveries')"
                  class="btn-primary"
                  style="background: #8b5cf6;"
                >
                  <Navigation size="14" />
                  <span>Track Delivery Telemetry</span>
                </button>
                <span v-if="['shipped', 'in_transit'].includes(selectedOrder.status) && !hasActiveRefundClaim(selectedOrder)" class="escrow-paid-badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border-color: rgba(139, 92, 246, 0.2);">
                  <ShieldCheck size="14" />
                  Awaiting Buyer Delivery Confirmation
                </span>
                <span v-if="selectedOrder.status === 'delivered' && !isOrderRefundSettled(selectedOrder) && !hasActiveRefundClaim(selectedOrder)" class="escrow-paid-badge">
                  <ShieldCheck size="14" />
                  Delivered &amp; Paid (Chapa Escrow Disbursed to Wallet)
                </span>
                <span v-if="isOrderRefundSettled(selectedOrder)" class="escrow-refunded-badge">
                  <RotateCcw size="14" />
                  Escrow Refund Settled to Buyer
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useTheme } from '@/composables/useTheme'
import { farmerAPI } from '@/api/farmer'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import {
  Download, Navigation, AlertCircle, RotateCcw, ShoppingCart, Clock, Zap, 
  CheckCircle, X, Eye, Play, Truck, MoreVertical, ChevronLeft, ChevronRight, ShieldCheck,
  ShieldAlert, AlertTriangle
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()
const { isDark, isLight } = useTheme()

// State
const loading = ref(false)
const error = ref(null)
const selectedOrder = ref(null)
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Data
const orders = ref([])
const availableCustomers = ref([])
const summaryData = ref({
  totalOrders: 0,
  pendingOrders: 0,
  confirmedOrders: 0,
  shippedOrders: 0,
  completedOrders: 0
})

// Filters
const filters = ref({
  status: '',
  dateRange: 'all',
  customer: '',
  paymentStatus: ''
})

// Computed
const filteredOrders = computed(() => {
  let result = orders.value

  if (filters.value.status) {
    if (filters.value.status === 'refund_requested') {
      result = result.filter(o => hasActiveRefundClaim(o))
    } else if (filters.value.status === 'refunded') {
      result = result.filter(o => isOrderRefundSettled(o))
    } else {
      result = result.filter(o => o.status === filters.value.status && !hasActiveRefundClaim(o) && !isOrderRefundSettled(o))
    }
  }

  if (filters.value.customer) {
    result = result.filter(o => o.customerName === filters.value.customer)
  }

  if (filters.value.paymentStatus) {
    result = result.filter(o => o.paymentStatus === filters.value.paymentStatus)
  }

  if (filters.value.dateRange && filters.value.dateRange !== 'all') {
    const now = new Date()
    result = result.filter(o => {
      if (!o.orderDate) return false
      const orderDate = new Date(o.orderDate)
      switch (filters.value.dateRange) {
        case 'today':
          return orderDate.toDateString() === now.toDateString()
        case 'week': {
          const d = new Date(now)
          d.setDate(now.getDate() - 7)
          return orderDate >= d
        }
        case 'month': {
          const d = new Date(now)
          d.setMonth(now.getMonth() - 1)
          return orderDate >= d
        }
        case 'quarter': {
          const d = new Date(now)
          d.setMonth(now.getMonth() - 3)
          return orderDate >= d
        }
        case 'year': {
          const d = new Date(now)
          d.setFullYear(now.getFullYear() - 1)
          return orderDate >= d
        }
        default:
          return true
      }
    })
  }

  return result
})

const totalPages = computed(() => {
  return Math.ceil(filteredOrders.value.length / parseInt(itemsPerPage.value)) || 1
})

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * parseInt(itemsPerPage.value)
  const end = start + parseInt(itemsPerPage.value)
  return filteredOrders.value.slice(start, end)
})

// Methods - Fetch live orders strictly from database
const fetchOrders = async () => {
  loading.value = true
  error.value = null
  try {
    const res = farmerAPI.getFarmerOrders ? await farmerAPI.getFarmerOrders() : await farmerAPI.getOrders()
    const rawOrders = res.data?.data || res.data || []

    orders.value = (Array.isArray(rawOrders) ? rawOrders : []).map(raw => {
      const buyerName = raw.buyer?.name || raw.buyer?.phone || 'Customer'
      const items = (raw.items || raw.order_items || []).map((item, idx) => ({
        id: item.id || idx,
        productName: item.product?.name || item.product_name || 'Agricultural Produce',
        quantity: Number(item.quantity || 1),
        unit: item.product?.quantity_unit || item.product?.unit || item.unit || 'kg',
        pricePerUnit: Number(item.price || 0),
        totalPrice: Number(item.subtotal || (item.quantity * item.price) || 0)
      }))

      const subtotal = Number(raw.subtotal || items.reduce((s, i) => s + i.totalPrice, 0))
      const shippingCost = Number(raw.delivery_fee || 0)
      const tax = Number(raw.tax || 0)
      const totalAmount = Number(raw.grand_total || (subtotal + shippingCost + tax))

      // Dynamic Status Mapping: synchronize from centralized database & refund relation
      const refundObj = raw.refund || raw.order_refund || null
      const refundStatus = String(refundObj?.status || '').toLowerCase()
      let mappedStatus = String(raw.status || 'pending').toLowerCase()

      if (['requested', 'pending'].includes(refundStatus)) {
        mappedStatus = 'refund_requested'
      } else if (['under_review', 'in_review'].includes(refundStatus)) {
        mappedStatus = 'under_review'
      } else if (['dispute', 'disputed'].includes(refundStatus)) {
        mappedStatus = 'disputed'
      } else if (['approved', 'completed', 'refunded'].includes(refundStatus) || mappedStatus === 'refunded') {
        mappedStatus = 'refunded'
      }

      return {
        id: raw.order_number || ('ORD-' + String(raw.id).padStart(4, '0')),
        rawId: raw.id,
        customerName: buyerName,
        customerEmail: raw.buyer?.email || 'N/A',
        customerPhone: raw.delivery_phone || raw.buyer?.phone || 'N/A',
        deliveryAddress: [raw.delivery_address, raw.delivery_woreda, raw.delivery_zone, raw.delivery_region].filter(Boolean).join(', ') || 'Standard Agro Depot Pickup',
        totalAmount,
        subtotal,
        shippingCost,
        tax,
        orderDate: raw.created_at,
        status: mappedStatus,
        paymentStatus: String(raw.payment_status || (raw.payment?.status === 'completed' ? 'paid' : 'pending')).toLowerCase(),
        deliveryStatus: capitalize(mappedStatus),
        trackingNumber: raw.payment_reference || null,
        expectedDelivery: raw.delivered_at || null,
        refund: refundObj,
        rawOrder: raw,
        items: items.length > 0 ? items : [
          {
            id: 1,
            productName: 'Agricultural Produce',
            quantity: 1,
            unit: 'lot',
            pricePerUnit: totalAmount,
            totalPrice: totalAmount
          }
        ]
      }
    })

    // Populate available customers from real database records
    const customerMap = new Map()
    orders.value.forEach(o => {
      if (o.customerName && !customerMap.has(o.customerName)) {
        customerMap.set(o.customerName, { id: o.customerName, name: o.customerName })
      }
    })
    availableCustomers.value = Array.from(customerMap.values())

    // Live summary counts aligned to 4 sequential stages
    summaryData.value = {
      totalOrders: orders.value.length,
      pendingOrders: orders.value.filter(o => o.status === 'pending' && !hasActiveRefundClaim(o) && !isOrderRefundSettled(o)).length,
      confirmedOrders: orders.value.filter(o => ['confirmed', 'processing'].includes(o.status) && !hasActiveRefundClaim(o) && !isOrderRefundSettled(o)).length,
      shippedOrders: orders.value.filter(o => ['shipped', 'in_transit'].includes(o.status) && !hasActiveRefundClaim(o) && !isOrderRefundSettled(o)).length,
      completedOrders: orders.value.filter(o => ['delivered', 'completed'].includes(o.status) && !isOrderRefundSettled(o)).length
    }
  } catch (err) {
    console.error('Error fetching orders from database:', err)
    error.value = 'Failed to load customer orders from database. Please try again.'
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

// Fulfillment Safety Guard: strictly disable or hide shipping/delivery actions for active claims or settled refunds
const isFulfillmentDisabled = (order) => {
  if (!order) return true
  const s = String(order.status || '').toLowerCase()
  if (['refund_requested', 'under_review', 'dispute', 'disputed', 'refunded', 'cancelled'].includes(s)) {
    return true
  }
  if (hasActiveRefundClaim(order) || isOrderRefundSettled(order)) {
    return true
  }
  return false
}

// Active refund or dispute claim detection (e.g. requested, under_review, pending, dispute, in_review)
const hasActiveRefundClaim = (order) => {
  if (!order) return false
  if (isOrderRefundSettled(order)) return false
  const s = String(order.status || '').toLowerCase()
  if (['refund_requested', 'under_review', 'dispute', 'disputed'].includes(s)) return true
  const r = order.refund || order.rawOrder?.refund
  if (r) {
    const rStatus = String(r.status || '').toLowerCase()
    return ['requested', 'pending', 'under_review', 'in_review', 'dispute', 'disputed'].includes(rStatus)
  }
  return false
}

// Settled / Completed refund detection
const isOrderRefundSettled = (order) => {
  if (!order) return false
  const s = String(order.status || '').toLowerCase()
  if (s === 'refunded') return true
  const payStatus = String(order.paymentStatus || order.payment_status || order.rawOrder?.payment_status || order.rawOrder?.payment?.status || '').toLowerCase()
  if (payStatus === 'refunded') return true
  const escrowStatus = String(order.escrow_status || order.rawOrder?.escrow_status || order.rawOrder?.payment?.escrow_status || '').toLowerCase()
  if (escrowStatus === 'refunded') return true
  const r = order.refund || order.rawOrder?.refund
  if (r) {
    const rStatus = String(r.status || '').toLowerCase()
    if (['approved', 'completed', 'refunded'].includes(rStatus)) return true
  }
  return false
}

// Status badge styling and label resolver
const getOrderStatusBadge = (order) => {
  if (!order) return { label: 'Pending', class: 'status-pending' }
  if (hasActiveRefundClaim(order)) {
    const r = order.refund || order.rawOrder?.refund
    const rStatus = String(r?.status || order.status || '').toLowerCase()
    if (['dispute', 'disputed'].includes(rStatus)) {
      return { label: 'DISPUTED', class: 'status-dispute' }
    }
    if (['under_review', 'in_review'].includes(rStatus)) {
      return { label: 'UNDER REVIEW', class: 'status-under-review' }
    }
    return { label: 'REFUND REQUESTED', class: 'status-refund-requested' }
  }
  if (isOrderRefundSettled(order)) {
    return { label: 'REFUNDED', class: 'status-refunded' }
  }
  return { label: capitalize(order.status), class: `status-${order.status}` }
}

// Payment badge styling and label resolver
const getOrderPaymentBadge = (order) => {
  if (!order) return { label: 'Pending', class: 'payment-pending' }
  if (isOrderRefundSettled(order)) {
    return { label: 'REFUNDED', class: 'payment-refunded' }
  }
  if (hasActiveRefundClaim(order)) {
    return { label: 'DISPUTE (ESCROW HELD)', class: 'payment-disputed' }
  }
  const ps = String(order.paymentStatus || 'pending').toLowerCase()
  return { label: capitalize(ps), class: `payment-${ps}` }
}

watch(filters, () => {
  currentPage.value = 1
}, { deep: true })

const applyFilters = () => {
  currentPage.value = 1
}

const clearFilters = () => {
  filters.value = {
    status: '',
    dateRange: 'all',
    customer: '',
    paymentStatus: ''
  }
  currentPage.value = 1
}

const exportData = () => {
  const headers = ['Order ID', 'Customer', 'Total', 'Date', 'Status', 'Payment']
  const rows = filteredOrders.value.map(order => [
    order.id,
    order.customerName,
    `ETB ${formatNumber(order.totalAmount)}`,
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

// RBAC: Status updates are managed exclusively via Escrow, Logistics, and Buyer confirmation
const navigateToTransport = () => {
  router.push('/farmer/transport-requests')
}

const navigateToTracking = () => {
  router.push('/farmer/active-deliveries')
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

.status-refund-requested,
.status-refund_requested,
.status-under-review,
.status-under_review {
  background: #fef3c7;
  color: #b45309;
  border: 1px solid #fde68a;
  font-weight: 700;
}

.status-dispute {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
  font-weight: 700;
}

.status-refunded {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
  font-weight: 700;
}

.payment-refunded {
  background: #fee2e2;
  color: #991b1b;
  font-weight: 700;
}

.payment-disputed {
  background: #fef3c7;
  color: #b45309;
  font-weight: 700;
  border: 1px solid #fde68a;
}

/* Claim Banners & Locked Badges */
.claim-alert-banner {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 24px;
  color: #92400e;
}

.orders-layout.dark .claim-alert-banner {
  background: rgba(120, 53, 15, 0.2);
  border-color: rgba(245, 158, 11, 0.3);
  color: #fde68a;
}

.settled-alert-banner {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 24px;
  color: #991b1b;
}

.orders-layout.dark .settled-alert-banner {
  background: rgba(127, 29, 29, 0.2);
  border-color: rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

.banner-icon-box {
  flex-shrink: 0;
  margin-top: 2px;
}

.banner-text {
  flex: 1;
}

.banner-title {
  font-size: 14px;
  font-weight: 700;
  margin: 0 0 4px 0;
}

.banner-desc {
  font-size: 13px;
  line-height: 1.5;
  margin: 0;
  opacity: 0.9;
}

.banner-claim-detail {
  margin-top: 8px;
  font-size: 12px;
  padding: 6px 10px;
  background: rgba(0, 0, 0, 0.04);
  border-radius: 6px;
}

.locked-action-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  background: #fef3c7;
  color: #b45309;
  border: 1px solid #fde68a;
}

.orders-layout.dark .locked-action-badge {
  background: rgba(120, 53, 15, 0.3);
  color: #fbbf24;
  border-color: rgba(245, 158, 11, 0.4);
}

.escrow-refunded-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.orders-layout.dark .escrow-refunded-badge {
  background: rgba(127, 29, 29, 0.3);
  color: #f87171;
  border-color: rgba(239, 68, 68, 0.4);
}

.action-btn.locked-btn {
  background: #fef3c7;
  border-color: #fde68a;
  color: #b45309;
}

.action-btn.locked-btn:hover {
  background: #fef3c7;
  border-color: #fde68a;
  color: #b45309;
}

.orders-layout.dark .action-btn.locked-btn {
  background: rgba(120, 53, 15, 0.3);
  border-color: rgba(245, 158, 11, 0.3);
  color: #fbbf24;
}

.orders-layout.dark .status-refund-requested,
.orders-layout.dark .status-refund_requested,
.orders-layout.dark .status-under-review,
.orders-layout.dark .status-under_review {
  background: #451a03;
  color: #fbbf24;
  border: 1px solid #78350f;
}

.orders-layout.dark .status-dispute,
.orders-layout.dark .status-refunded {
  background: #450a0a;
  color: #f87171;
  border: 1px solid #7f1d1d;
}

.orders-layout.dark .payment-refunded {
  background: #450a0a;
  color: #f87171;
}

.orders-layout.dark .payment-disputed {
  background: #451a03;
  color: #fbbf24;
  border: 1px solid #78350f;
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

/* ==================== DARK MODE STYLES ==================== */

/* Light Mode (Default) */
.orders-layout.light {
  background-color: #f0f2f5;
  color: #1f2937;
}

/* Dark Mode */
.orders-layout.dark {
  background-color: #0b0f17;
  color: #f3f4f6;
}

.orders-container {
  transition: background-color 0.3s ease, color 0.3s ease;
}

.orders-layout.light .orders-container {
  background-color: #f0f2f5;
}

.orders-layout.dark .orders-container {
  background-color: #0b0f17;
}

/* Page Header */
.orders-layout.light .page-header {
  background: white;
  border-bottom-color: #e5e7eb;
}

.orders-layout.dark .page-header {
  background: #131b2e;
  border-bottom-color: #1e293b;
}

.orders-layout.dark .header-content h1 {
  color: #f1f5f9;
}

.orders-layout.dark .header-content p {
  color: #cbd5e1;
}

/* Buttons */
.orders-layout.dark .btn-filter {
  background: #1a2338;
  color: #cbd5e1;
  border-color: #2d3f52;
}

.orders-layout.dark .btn-filter:hover {
  background: #1e293b;
  border-color: #3d4f63;
}

/* Loading & Error */
.orders-layout.dark .loading-container {
  color: #cbd5e1;
}

.orders-layout.dark .spinner {
  border-color: #2d3f52;
  border-top-color: #4ade80;
}

.orders-layout.dark .error-container {
  background: #7f1d1d;
  border-color: #991b1b;
}

.orders-layout.dark .error-icon {
  color: #f87171;
}

.orders-layout.dark .error-message {
  color: #fee2e2;
}

/* Summary Cards */
.orders-layout.light .summary-card {
  background: white;
}

.orders-layout.dark .summary-card {
  background: #1a2338;
  border-left-color: #60a5fa;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.orders-layout.dark .summary-card:nth-child(2) {
  border-left-color: #fbbf24;
}

.orders-layout.dark .summary-card:nth-child(3) {
  border-left-color: #a78bfa;
}

.orders-layout.dark .summary-card:nth-child(4) {
  border-left-color: #4ade80;
}

.orders-layout.dark .card-header h3 {
  color: #cbd5e1;
}

.orders-layout.dark .card-value {
  color: #f1f5f9;
}

.orders-layout.dark .card-icon {
  color: #2d3f52;
}

.orders-layout.dark .card-subtitle {
  color: #94a3b8;
}

/* Filters Panel */
.orders-layout.light .filters-panel {
  background: white;
  border-color: #e5e7eb;
}

.orders-layout.dark .filters-panel {
  background: #1a2338;
  border-color: #2d3f52;
}

.orders-layout.dark .filters-header {
  border-bottom-color: #2d3f52;
}

.orders-layout.dark .filters-header h3 {
  color: #f1f5f9;
}

.orders-layout.dark .btn-close {
  color: #cbd5e1;
}

.orders-layout.dark .filter-group label {
  color: #cbd5e1;
}

.orders-layout.light .filter-select {
  background: white;
  color: #1f2937;
  border-color: #e5e7eb;
}

.orders-layout.dark .filter-select {
  background: #131b2e;
  color: #f1f5f9;
  border-color: #2d3f52;
}

.orders-layout.dark .filter-select:focus {
  border-color: #4ade80;
  box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.12);
}

.orders-layout.dark .btn-apply {
  background: #10b981;
}

.orders-layout.dark .btn-clear {
  background: #1e293b;
  color: #cbd5e1;
  border-color: #2d3f52;
}

.orders-layout.dark .btn-clear:hover {
  background: #2d3f52;
}

/* Table Section */
.orders-layout.light .table-section {
  background: white;
}

.orders-layout.dark .table-section {
  background: #1a2338;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.orders-layout.dark .table-header {
  border-bottom-color: #2d3f52;
}

.orders-layout.dark .table-header h3 {
  color: #f1f5f9;
}

.orders-layout.light .items-select {
  background: white;
  color: #4b5563;
  border-color: #e5e7eb;
}

.orders-layout.dark .items-select {
  background: #131b2e;
  color: #f1f5f9;
  border-color: #2d3f52;
}

.orders-layout.dark .orders-table thead {
  background: #131b2e;
  border-bottom-color: #2d3f52;
}

.orders-layout.dark .orders-table th {
  color: #cbd5e1;
}

.orders-layout.dark .orders-table tbody tr {
  border-bottom-color: #2d3f52;
}

.orders-layout.dark .orders-table tbody tr:hover {
  background: #1e293b;
}

.orders-layout.dark .table-row td {
  color: #f1f5f9;
}

.orders-layout.dark .order-badge {
  background: #0f4c3a;
  color: #86efac;
}

.orders-layout.dark .amount-badge {
  background: #5d4307;
  color: #fde047;
}

.orders-layout.dark .status-pending {
  background: #5d4307;
  color: #fde047;
}

.orders-layout.dark .status-processing {
  background: #0c2d52;
  color: #60a5fa;
}

.orders-layout.dark .status-shipped {
  background: #0c3e4d;
  color: #06b6d4;
}

.orders-layout.dark .status-delivered {
  background: #0f4c3a;
  color: #86efac;
}

.orders-layout.dark .status-cancelled {
  background: #7f1d1d;
  color: #f87171;
}

.orders-layout.dark .payment-pending {
  background: #5d4307;
  color: #fde047;
}

.orders-layout.dark .payment-paid {
  background: #0f4c3a;
  color: #86efac;
}

.orders-layout.dark .payment-failed {
  background: #7f1d1d;
  color: #f87171;
}

.orders-layout.dark .action-btn {
  background: none;
  border-color: #2d3f52;
  color: #cbd5e1;
}

.orders-layout.dark .action-btn:hover {
  border-color: #4ade80;
  color: #4ade80;
  background: #0f4c3a;
}

.orders-layout.dark .view-btn:hover {
  border-color: #60a5fa;
  color: #60a5fa;
  background: #0c2d52;
}

/* Empty State */
.orders-layout.dark .empty-state {
  color: #94a3b8;
}

.orders-layout.dark .empty-state p {
  color: #cbd5e1;
}

/* Pagination */
.orders-layout.light .pagination {
  border-top-color: #e5e7eb;
}

.orders-layout.dark .pagination {
  border-top-color: #2d3f52;
}

.orders-layout.dark .pagination-btn {
  background: #1a2338;
  color: #cbd5e1;
  border-color: #2d3f52;
}

.orders-layout.dark .pagination-btn:hover:not(:disabled) {
  background: #1e293b;
  border-color: #4ade80;
  color: #4ade80;
}

.orders-layout.dark .pagination-info {
  color: #cbd5e1;
}

/* Modal */
.orders-layout.dark .modal-overlay {
  background: rgba(0, 0, 0, 0.7);
}

.orders-layout.light .modal-content {
  background: white;
}

.orders-layout.dark .modal-content {
  background: #1a2338;
}

.orders-layout.dark .modal-header {
  border-bottom-color: #2d3f52;
}

.orders-layout.dark .modal-header h2 {
  color: #f1f5f9;
}

.orders-layout.dark .modal-body {
  color: #f1f5f9;
}

.orders-layout.dark .order-summary {
  background: #131b2e;
  border-color: #2d3f52;
}

.orders-layout.dark .summary-item .label {
  color: #cbd5e1;
}

.orders-layout.dark .summary-item .value {
  color: #f1f5f9;
}

.orders-layout.dark .section {
  border-top-color: #2d3f52;
}

.orders-layout.dark .section-title {
  color: #f1f5f9;
}

.orders-layout.dark .customer-details {
  background: #131b2e;
}

.orders-layout.dark .detail-item {
  border-bottom-color: #2d3f52;
}

.orders-layout.dark .detail-label {
  color: #cbd5e1;
}

.orders-layout.dark .detail-value {
  color: #f1f5f9;
}

.orders-layout.dark .items-list {
  background: #131b2e;
}

.orders-layout.dark .item-row {
  border-bottom-color: #2d3f52;
  color: #f1f5f9;
}

.orders-layout.dark .item-name {
  color: #f1f5f9;
}

.orders-layout.dark .item-qty,
.orders-layout.dark .item-price,
.orders-layout.dark .item-total {
  color: #cbd5e1;
}

.orders-layout.dark .totals {
  background: #131b2e;
}

.orders-layout.dark .total-row {
  border-bottom-color: #2d3f52;
  color: #cbd5e1;
}

.orders-layout.dark .total-row.total {
  color: #f1f5f9;
  font-weight: 700;
}

.orders-layout.dark .delivery-info {
  background: #131b2e;
}

.orders-layout.dark .info-label {
  color: #cbd5e1;
}

.orders-layout.dark .info-value {
  color: #f1f5f9;
}

.orders-layout.dark .modal-footer {
  border-top-color: #2d3f52;
}

.orders-layout.dark .btn-primary {
  background: #10b981;
}

.orders-layout.dark .btn-primary.process {
  background: #3b82f6;
}

.orders-layout.dark .btn-primary.ship {
  background: #06b6d4;
}

.orders-layout.dark .btn-primary.deliver {
  background: #8b5cf6;
}

.orders-layout.dark .btn-secondary {
  background: #1e293b;
  color: #cbd5e1;
  border-color: #2d3f52;
}

.orders-layout.dark .btn-secondary:hover {
  background: #2d3f52;
}
</style>
