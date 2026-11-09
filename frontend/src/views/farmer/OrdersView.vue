<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Customer Orders</h1>
          <p>View and manage customer orders for your products</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon total">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="stat-content">
            <h3>Total Orders</h3>
            <p class="stat-value">{{ orders.length }}</p>
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
          <div class="stat-icon completed">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-content">
            <h3>Completed</h3>
            <p class="stat-value">{{ completedCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon revenue">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="stat-content">
            <h3>Total Revenue</h3>
            <p class="stat-value">ETB {{ formatNumber(totalRevenue) }}</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="controls-section">
        <div class="filter-group">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by order ID or customer..."
            class="search-input"
          />
          <select v-model="statusFilter" class="status-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="shipped">Shipped</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <!-- Orders List -->
      <div class="orders-section">
        <div v-if="loading" class="loading-state">
          <p><i class="fas fa-spinner fa-spin"></i> Loading orders...</p>
        </div>

        <div v-else-if="filteredOrders.length === 0" class="empty-state">
          <i class="fas fa-inbox"></i>
          <h3>No orders found</h3>
          <p>{{ orders.length === 0 ? 'You have no customer orders yet' : 'No orders match your filters' }}</p>
        </div>

        <div v-else class="orders-table">
          <table class="data-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in filteredOrders" :key="order.id">
                <td><strong>#{{ order.id }}</strong></td>
                <td>{{ order.buyer?.name || 'Unknown' }}</td>
                <td>
                  <span class="item-count">{{ order.items?.length || 0 }} item(s)</span>
                </td>
                <td><strong>ETB {{ formatNumber(order.total_amount) }}</strong></td>
                <td>
                  <span class="status-badge" :class="getStatusBadgeClass(order)">
                    {{ formatStatus(order.status, order) }}
                  </span>
                </td>
                <td>{{ formatDate(order.created_at) }}</td>
                <td class="action-buttons">
                  <button class="btn-small btn-view" @click="viewOrderDetails(order)">
                    <i class="fas fa-eye"></i> View
                  </button>
                  <button 
                    v-if="order.status === 'pending' && !isFulfillmentDisabled(order)"
                    class="btn-small btn-accept"
                    @click="acceptOrder(order.id)"
                  >
                    <i class="fas fa-check"></i> Accept
                  </button>
                  <button 
                    v-if="order.status === 'pending' && !isFulfillmentDisabled(order)"
                    class="btn-small btn-reject"
                    @click="rejectOrder(order.id)"
                  >
                    <i class="fas fa-times"></i> Reject
                  </button>
                  <span v-else-if="isFulfillmentDisabled(order) && (hasActiveRefundClaim(order) || ['refund_requested', 'under_review', 'disputed'].includes(order.status))" class="badge-locked" title="Fulfillment locked: Order is under refund/dispute review">
                    <i class="fas fa-shield-alt"></i> {{ formatStatus(order.status, order) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Order Details Modal -->
      <div v-if="showDetailsModal" class="modal-overlay" @click="closeDetailsModal">
        <div class="modal-dialog modal-large" @click.stop>
          <div class="modal-header">
            <h2>Order #{{ selectedOrder?.id }}</h2>
            <button class="close-btn" @click="closeDetailsModal">&times;</button>
          </div>

          <div class="modal-content">
            <!-- Refund / Dispute Safety Guard Banner -->
          <div v-if="hasActiveRefundClaim(selectedOrder)" class="claim-alert-box">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
              <strong>Fulfillment Locked &mdash; Active Refund Claim Under Review:</strong>
              The buyer has submitted a dispute/refund claim for this order. Transport dispatch is disabled and escrow payout is frozen until administrator resolution.
              <div v-if="selectedOrder?.refund?.reason" class="claim-reason-text">
                Reason: {{ selectedOrder.refund.reason }}
              </div>
            </div>
          </div>
          <div v-else-if="isOrderRefundSettled(selectedOrder)" class="settled-alert-box">
            <i class="fas fa-undo-alt"></i>
            <div>
              <strong>Order Refunded:</strong>
              This order has been resolved and refunded to the buyer. Fulfillment is permanently closed.
            </div>
          </div>

          <div class="details-grid">
              <div class="detail-section">
                <h3>Order Information</h3>
                <div class="detail-row">
                  <span class="label">Order ID:</span>
                  <span class="value">#{{ selectedOrder?.id }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Status:</span>
                  <span class="value status-badge" :class="getStatusBadgeClass(selectedOrder)">
                    {{ formatStatus(selectedOrder?.status, selectedOrder) }}
                  </span>
                </div>
                <div class="detail-row">
                  <span class="label">Date:</span>
                  <span class="value">{{ formatDate(selectedOrder?.created_at) }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Total Amount:</span>
                  <span class="value">ETB {{ formatNumber(selectedOrder?.total_amount) }}</span>
                </div>
              </div>

              <div class="detail-section">
                <h3>Customer Information</h3>
                <div class="detail-row">
                  <span class="label">Name:</span>
                  <span class="value">{{ selectedOrder?.buyer?.name }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Email:</span>
                  <span class="value">{{ selectedOrder?.buyer?.email }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Phone:</span>
                  <span class="value">{{ selectedOrder?.buyer?.phone }}</span>
                </div>
              </div>

              <div class="detail-section full-width">
                <h3>Order Items</h3>
                <div class="items-table">
                  <table>
                    <thead>
                      <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in selectedOrder?.items" :key="item.id">
                        <td>{{ item.product?.name }}</td>
                        <td>{{ item.quantity }} {{ item.unit }}</td>
                        <td>ETB {{ formatNumber(item.unit_price) }}</td>
                        <td>ETB {{ formatNumber(item.quantity * item.unit_price) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="detail-section full-width">
                <h3>Delivery Information</h3>
                <div class="detail-row">
                  <span class="label">Delivery Address:</span>
                  <span class="value">{{ selectedOrder?.delivery_address || 'N/A' }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Notes:</span>
                  <span class="value">{{ selectedOrder?.notes || 'No notes' }}</span>
                </div>
              </div>
            </div>

            <div class="modal-actions">
              <button class="btn-secondary" @click="closeDetailsModal">Close</button>
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
const orders = ref([])
const loading = ref(true)
const searchQuery = ref('')
const statusFilter = ref('')
const showDetailsModal = ref(false)
const selectedOrder = ref(null)

// Lifecycle
onMounted(async () => {
  await fetchOrders()
})

// API Functions
const fetchOrders = async () => {
  try {
    loading.value = true
    const res = await fetch('/api/farmer/orders', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })

    if (!res.ok) {
      orders.value = []
      return
    }

    const data = await res.json()
    const rawList = data.data || data || []
    orders.value = (Array.isArray(rawList) ? rawList : []).map(raw => {
      const refundObj = raw.refund || null
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
        ...raw,
        status: mappedStatus,
        refund: refundObj
      }
    })
  } catch (error) {
    console.error('Error fetching orders:', error)
    orders.value = []
  } finally {
    loading.value = false
  }
}

// Computed
const filteredOrders = computed(() => {
  return orders.value.filter(order => {
    const matchesSearch = 
      String(order.id).includes(searchQuery.value) ||
      order.buyer?.name?.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesStatus = !statusFilter.value || order.status === statusFilter.value

    return matchesSearch && matchesStatus
  })
})

const pendingCount = computed(() => orders.value.filter(o => o.status === 'pending' && !hasActiveRefundClaim(o) && !isOrderRefundSettled(o)).length)
const completedCount = computed(() => orders.value.filter(o => o.status === 'completed' && !isOrderRefundSettled(o)).length)
const totalRevenue = computed(() => {
  return orders.value
    .filter(o => o.status === 'completed' && !isOrderRefundSettled(o))
    .reduce((sum, o) => sum + (o.total_amount || 0), 0)
})

// Modal Functions
const viewOrderDetails = (order) => {
  selectedOrder.value = order
  showDetailsModal.value = true
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedOrder.value = null
}

// Order Actions
const acceptOrder = async (orderId) => {
  if (!confirm('Accept this order?')) return

  try {
    const response = await fetch(`/api/farmer/orders/${orderId}/accept`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })

    if (response.ok) {
      await fetchOrders()
      closeDetailsModal()
    }
  } catch (error) {
    console.error('Error accepting order:', error)
    alert('Failed to accept order')
  }
}

const rejectOrder = async (orderId) => {
  if (!confirm('Reject this order?')) return

  try {
    const response = await fetch(`/api/farmer/orders/${orderId}/reject`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })

    if (response.ok) {
      await fetchOrders()
      closeDetailsModal()
    }
  } catch (error) {
    console.error('Error rejecting order:', error)
    alert('Failed to reject order')
  }
}

// Utility Functions
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num || 0)
}

// Fulfillment Safety Guard
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

// Active refund or dispute claim detection
const hasActiveRefundClaim = (order) => {
  if (!order) return false
  if (isOrderRefundSettled(order)) return false
  const s = String(order.status || '').toLowerCase()
  if (['refund_requested', 'under_review', 'dispute', 'disputed'].includes(s)) return true
  const r = order.refund
  if (r) {
    const rStatus = String(r.status || '').toLowerCase()
    return ['requested', 'pending', 'under_review', 'in_review', 'dispute', 'disputed'].includes(rStatus)
  }
  return false
}

// Settled refund detection
const isOrderRefundSettled = (order) => {
  if (!order) return false
  const s = String(order.status || '').toLowerCase()
  if (s === 'refunded') return true
  const payStatus = String(order.payment_status || order.payment?.status || '').toLowerCase()
  if (payStatus === 'refunded') return true
  const r = order.refund
  if (r) {
    const rStatus = String(r.status || '').toLowerCase()
    return ['approved', 'completed', 'refunded'].includes(rStatus)
  }
  return false
}

const getStatusBadgeClass = (order) => {
  if (!order) return 'status-pending'
  if (hasActiveRefundClaim(order)) {
    const rStatus = String(order.refund?.status || order.status || '').toLowerCase()
    if (['dispute', 'disputed'].includes(rStatus)) return 'status-dispute'
    if (['under_review', 'in_review'].includes(rStatus)) return 'status-under-review'
    return 'status-refund-requested'
  }
  if (isOrderRefundSettled(order)) return 'status-refunded'
  return `status-${order.status}`
}

const formatStatus = (status, order) => {
  if (order && hasActiveRefundClaim(order)) {
    const rStatus = String(order.refund?.status || order.status || '').toLowerCase()
    if (['dispute', 'disputed'].includes(rStatus)) return 'Disputed'
    if (['under_review', 'in_review'].includes(rStatus)) return 'Under Review'
    return 'Refund Requested'
  }
  if (order && isOrderRefundSettled(order)) {
    return 'Refunded'
  }
  const statuses = {
    'pending': 'Pending',
    'confirmed': 'Confirmed',
    'shipped': 'Shipped',
    'completed': 'Completed',
    'cancelled': 'Cancelled',
    'refunded': 'Refunded',
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
.stat-icon.completed { background-color: #8b5cf6; }
.stat-icon.revenue { background-color: #3b82f6; }

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

/* Orders Section */
.orders-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

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

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

.item-count {
  background: #eff6ff;
  color: #1e40af;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending { background-color: #fef3c7; color: #92400e; }
.status-badge.status-confirmed { background-color: #dbeafe; color: #1e40af; }
.status-badge.status-shipped { background-color: #f3e8ff; color: #6b21a8; }
.status-badge.status-completed { background-color: #d1fae5; color: #065f46; }
.status-badge.status-cancelled { background-color: #fee2e2; color: #991b1b; }

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.btn-small {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  min-width: 70px;
}

.btn-view {
  background-color: #8b5cf6;
  color: white;
}

.btn-view:hover {
  background-color: #7c3aed;
}

.btn-accept {
  background-color: #10b981;
  color: white;
}

.btn-accept:hover {
  background-color: #059669;
}

.btn-reject {
  background-color: #ef4444;
  color: white;
}

.btn-reject:hover {
  background-color: #dc2626;
}

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
  max-width: 650px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-dialog.modal-large {
  max-width: 900px;
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

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 25px;
}

.detail-section {
  background: #f9fafb;
  padding: 15px;
  border-radius: 6px;
}

.detail-section.full-width {
  grid-column: 1 / -1;
}

.detail-section h3 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
  text-transform: uppercase;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row .label {
  color: #666;
  font-weight: 500;
  font-size: 13px;
}

.detail-row .value {
  color: #333;
  font-weight: 600;
  text-align: right;
}

.items-table {
  background: white;
  border-radius: 4px;
  overflow: hidden;
}

.items-table table {
  width: 100%;
  border-collapse: collapse;
}

.items-table th {
  background: #f3f4f6;
  padding: 10px;
  text-align: left;
  font-weight: 600;
  font-size: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.items-table td {
  padding: 10px;
  border-bottom: 1px solid #e5e7eb;
  font-size: 13px;
}

.items-table tbody tr:hover {
  background: #f9fafb;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  padding-top: 15px;
  border-top: 1px solid #e5e7eb;
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

/* Responsive */
@media (max-width: 768px) {
  .farmer-page { margin-left: 0; }
  .page-header { flex-direction: column; align-items: stretch; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .filter-group { flex-direction: column; }
  .search-input { width: 100%; }
  .modal-dialog { width: 95%; max-height: 95vh; }
  .details-grid { grid-template-columns: 1fr; }
  .data-table { font-size: 12px; }
  .action-buttons { flex-direction: column; }
  .btn-small { width: 100%; }
}

.status-refund-requested,
.status-under-review {
  background-color: #fef3c7;
  color: #b45309;
  border: 1px solid #fde68a;
  font-weight: 700;
}

.status-dispute {
  background-color: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fca5a5;
  font-weight: 700;
}

.status-refunded {
  background-color: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
  font-weight: 700;
}

.badge-locked {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  background-color: #fef3c7;
  color: #b45309;
  border: 1px solid #fde68a;
}

.claim-alert-box {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 10px;
  padding: 14px 18px;
  margin-bottom: 20px;
  color: #92400e;
  font-size: 13px;
  line-height: 1.5;
}

.claim-alert-box i {
  font-size: 18px;
  margin-top: 2px;
  color: #d97706;
}

.claim-reason-text {
  margin-top: 6px;
  font-size: 12px;
  padding: 4px 8px;
  background: rgba(0, 0, 0, 0.04);
  border-radius: 4px;
}

.settled-alert-box {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 10px;
  padding: 14px 18px;
  margin-bottom: 20px;
  color: #991b1b;
  font-size: 13px;
  line-height: 1.5;
}

.settled-alert-box i {
  font-size: 18px;
  margin-top: 2px;
  color: #dc2626;
}
</style>
