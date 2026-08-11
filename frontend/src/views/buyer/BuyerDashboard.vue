<template>
  <div class="buyer-layout">
    <BuyerSidebar @logout="handleLogout" />
    <div class="buyer-dashboard">
      <!-- Header -->
      <div class="dashboard-header">
        <h1>Buyer Dashboard</h1>
        <p>Manage your orders, deliveries, and purchases</p>
      </div>

      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="card-icon orders">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="card-content">
            <h3>Total Orders</h3>
            <p class="card-value">{{ dashboard?.order_statistics?.total || 0 }}</p>
            <p class="card-sub">All purchases</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon pending">
            <i class="fas fa-clock"></i>
          </div>
          <div class="card-content">
            <h3>Pending Orders</h3>
            <p class="card-value">{{ dashboard?.order_statistics?.pending || 0 }}</p>
            <p class="card-sub">Awaiting shipment</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon completed">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="card-content">
            <h3>Completed Orders</h3>
            <p class="card-value">{{ dashboard?.order_statistics?.completed || 0 }}</p>
            <p class="card-sub">Delivered</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon spent">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="card-content">
            <h3>Total Spent</h3>
            <p class="card-value">${{ formatNumber(dashboard?.total_spent) }}</p>
            <p class="card-sub">Avg: ${{ formatNumber(dashboard?.average_order_value) }}</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon cart">
            <i class="fas fa-shopping-bag"></i>
          </div>
          <div class="card-content">
            <h3>Cart Items</h3>
            <p class="card-value">{{ dashboard?.cart_items_count || 0 }}</p>
            <p class="card-sub">Ready to checkout</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon wishlist">
            <i class="fas fa-heart"></i>
          </div>
          <div class="card-content">
            <h3>Wishlist Items</h3>
            <p class="card-value">{{ dashboard?.wishlist_items_count || 0 }}</p>
            <p class="card-sub">Saved items</p>
          </div>
        </div>
      </div>

      <!-- Main Tabs -->
      <div class="dashboard-tabs">
        <div class="tab-buttons">
          <button 
            v-for="tab in tabs" 
            :key="tab"
            :class="['tab-btn', { active: activeTab === tab }]"
            @click="activeTab = tab"
          >
            {{ formatTabName(tab) }}
          </button>
        </div>

        <!-- Orders Tab -->
        <div v-show="activeTab === 'orders'" class="tab-content">
          <div class="section-header">
            <h2>My Orders</h2>
            <div class="filter-controls">
              <select v-model="orderFilter" class="filter-select">
                <option value="">All Orders</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>

          <div class="orders-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Supplier</th>
                  <th>Items</th>
                  <th>Status</th>
                  <th>Total</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in dashboard?.recent_orders || []" :key="order.id">
                  <td>#{{ order.id }}</td>
                  <td>{{ order.supplier?.name || 'N/A' }}</td>
                  <td>{{ order.items?.length || 0 }}</td>
                  <td><span class="status-badge" :class="`status-${order.status}`">{{ order.status }}</span></td>
                  <td>${{ formatNumber(order.total_amount) }}</td>
                  <td>{{ formatDate(order.created_at) }}</td>
                  <td class="action-buttons">
                    <button class="btn-small btn-view">View</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Deliveries Tab -->
        <div v-show="activeTab === 'deliveries'" class="tab-content">
          <div class="section-header">
            <h2>Active Deliveries</h2>
          </div>

          <div class="deliveries-grid">
            <div v-for="delivery in dashboard?.active_deliveries || []" :key="delivery.id" class="delivery-card">
              <div class="delivery-header">
                <h3>Order #{{ delivery.order_id }}</h3>
                <span class="badge">{{ delivery.status }}</span>
              </div>
              <div class="delivery-info">
                <p><strong>From:</strong> {{ delivery.from_location }}</p>
                <p><strong>To:</strong> {{ delivery.to_location }}</p>
                <p><strong>Driver:</strong> {{ delivery.driver_name }}</p>
                <p><strong>Est. Date:</strong> {{ formatDate(delivery.estimated_delivery_date) }}</p>
              </div>
              <div class="delivery-actions">
                <button class="btn-small btn-track">Track</button>
                <button class="btn-small btn-contact">Contact</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Cart Tab -->
        <div v-show="activeTab === 'cart'" class="tab-content">
          <div class="section-header">
            <h2>Shopping Cart</h2>
          </div>

          <div class="cart-container">
            <div class="cart-items">
              <div class="list-item" v-if="dashboard?.cart_items_count === 0">
                <p class="empty-message">Your cart is empty</p>
              </div>
              <div v-for="i in (dashboard?.cart_items_count || 0)" :key="i" class="cart-item">
                <div class="item-details">
                  <h4>Product {{ i }}</h4>
                  <p>Quantity: 5 units</p>
                </div>
                <div class="item-price">
                  <p class="price">$150.00</p>
                </div>
                <button class="btn-small btn-remove">Remove</button>
              </div>
            </div>

            <div class="cart-summary">
              <div class="summary-item">
                <span>Subtotal:</span>
                <span>${{ formatNumber(dashboard?.total_spent || 0) }}</span>
              </div>
              <div class="summary-item">
                <span>Tax (15%):</span>
                <span>${{ formatNumber((dashboard?.total_spent || 0) * 0.15) }}</span>
              </div>
              <div class="summary-item total">
                <span>Total:</span>
                <span>${{ formatNumber((dashboard?.total_spent || 0) * 1.15) }}</span>
              </div>
              <button class="btn-primary btn-checkout">Proceed to Checkout</button>
            </div>
          </div>
        </div>

        <!-- Reviews Tab -->
        <div v-show="activeTab === 'reviews'" class="tab-content">
          <div class="section-header">
            <h2>My Reviews</h2>
          </div>

          <div class="reviews-list">
            <div v-for="i in 3" :key="i" class="review-card">
              <div class="review-header">
                <h4>Product Review</h4>
                <div class="rating">
                  <i v-for="j in 5" :key="j" class="fas fa-star"></i>
                </div>
              </div>
              <p class="review-text">Great quality products! Fast delivery and excellent customer service.</p>
              <p class="review-date">{{ formatDate(new Date()) }}</p>
            </div>
          </div>
        </div>

        <!-- Wishlist Tab -->
        <div v-show="activeTab === 'wishlist'" class="tab-content">
          <div class="section-header">
            <h2>My Wishlist</h2>
          </div>

          <div class="wishlist-grid">
            <div v-for="i in (dashboard?.wishlist_items_count || 0)" :key="i" class="wishlist-card">
              <div class="product-image-placeholder">
                <i class="fas fa-image"></i>
              </div>
              <div class="product-info">
                <h4>Product {{ i }}</h4>
                <p class="price">${{ (100 + i * 50).toFixed(2) }}</p>
                <p class="supplier">Supplier {{ i }}</p>
              </div>
              <div class="wishlist-actions">
                <button class="btn-small btn-add-cart">Add to Cart</button>
                <button class="btn-small btn-remove">Remove</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Analytics Tab -->
        <div v-show="activeTab === 'analytics'" class="tab-content">
          <div class="section-header">
            <h2>Purchase Analytics</h2>
          </div>

          <div class="analytics-grid">
            <div class="metric-card">
              <h3>Spending Overview</h3>
              <div class="metric-list">
                <p><strong>Total Spent:</strong> ${{ formatNumber(dashboard?.total_spent) }}</p>
                <p><strong>Avg Order Value:</strong> ${{ formatNumber(dashboard?.average_order_value) }}</p>
                <p><strong>Total Orders:</strong> {{ dashboard?.order_statistics?.total || 0 }}</p>
              </div>
            </div>
            <div class="metric-card">
              <h3>Top Purchases</h3>
              <div class="metric-list">
                <p v-for="i in 3" :key="i">{{ i }}. Product {{ i }} - 3 purchases</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="recent-section">
        <div class="recent-card">
          <h2>Recent Orders</h2>
          <div class="list-items">
            <div v-for="order in (dashboard?.recent_orders || []).slice(0, 5)" :key="order.id" class="list-item">
              <span class="item-name">Order #{{ order.id }}</span>
              <span class="item-amount">${{ formatNumber(order.total_amount) }}</span>
              <span class="status-badge" :class="`status-${order.status}`">{{ order.status }}</span>
            </div>
          </div>
        </div>

        <div class="recent-card">
          <h2>Payment Methods</h2>
          <div class="list-items">
            <div class="list-item">
              <span class="item-name">Credit Card</span>
              <span class="item-sub">****1234</span>
            </div>
            <div class="list-item">
              <span class="item-name">Bank Transfer</span>
              <span class="item-sub">Primary</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

const auth = useAuthStore()
const router = useRouter()
const activeTab = ref('orders')
const tabs = ['orders', 'deliveries', 'cart', 'reviews', 'wishlist', 'analytics']

const dashboard = ref(null)
const orderFilter = ref('')

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  try {
    const res = await fetch('/api/buyer/dashboard', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    const data = await res.json()
    dashboard.value = data.data || data
  } catch (error) {
    console.error('Error fetching dashboard:', error)
  }
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const formatTabName = (tab) => {
  const names = {
    orders: 'My Orders',
    deliveries: 'Deliveries',
    cart: 'Shopping Cart',
    reviews: 'Reviews',
    wishlist: 'Wishlist',
    analytics: 'Analytics'
  }
  return names[tab] || tab
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.buyer-layout {
  display: flex;
  height: 100vh;
}

.buyer-dashboard {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f5f5;
  min-height: 100vh;
  padding: 20px;
}

.dashboard-header {
  margin-bottom: 30px;
}

.dashboard-header h1 {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.dashboard-header p {
  color: #666;
}

/* Summary Grid */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.summary-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.card-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.card-icon.orders {
  background-color: #3b82f6;
}

.card-icon.pending {
  background-color: #f59e0b;
}

.card-icon.completed {
  background-color: #10b981;
}

.card-icon.spent {
  background-color: #ef4444;
}

.card-icon.cart {
  background-color: #8b5cf6;
}

.card-icon.wishlist {
  background-color: #ec4899;
}

.card-content h3 {
  font-size: 12px;
  color: #666;
  margin-bottom: 5px;
  text-transform: uppercase;
  font-weight: 600;
}

.card-value {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.card-sub {
  font-size: 12px;
  color: #999;
}

/* Tabs */
.dashboard-tabs {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 30px;
}

.tab-buttons {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
  overflow-x: auto;
}

.tab-btn {
  flex: 1;
  padding: 15px 20px;
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
  font-weight: 500;
  transition: all 0.3s;
  border-bottom: 3px solid transparent;
  white-space: nowrap;
}

.tab-btn:hover {
  color: #3b82f6;
}

.tab-btn.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

.tab-content {
  padding: 25px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
}

.section-header h2 {
  font-size: 18px;
  font-weight: bold;
  color: #333;
}

.filter-select {
  padding: 10px 15px;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  font-size: 14px;
}

/* Tables */
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
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

/* Deliveries Grid */
.deliveries-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.delivery-card {
  background-color: #f9fafb;
  border-radius: 8px;
  padding: 15px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s;
}

.delivery-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.delivery-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.delivery-header h3 {
  margin: 0;
  color: #333;
  font-size: 16px;
}

.delivery-info p {
  margin: 5px 0;
  font-size: 14px;
  color: #666;
}

.delivery-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.btn-track,
.btn-contact {
  flex: 1;
}

/* Cart Container */
.cart-container {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 20px;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.empty-message {
  text-align: center;
  color: #999;
  padding: 40px;
  background-color: #f9fafb;
  border-radius: 8px;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  background-color: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.item-details {
  flex: 1;
}

.item-details h4 {
  margin: 0 0 5px 0;
  color: #333;
}

.item-details p {
  margin: 0;
  font-size: 14px;
  color: #666;
}

.item-price {
  margin-right: 15px;
  font-weight: bold;
  color: #3b82f6;
}

.btn-remove {
  background-color: #ef4444;
  color: white;
}

.btn-remove:hover {
  background-color: #dc2626;
}

/* Cart Summary */
.cart-summary {
  background-color: #f9fafb;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  height: fit-content;
  position: sticky;
  top: 20px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #e5e7eb;
  font-size: 14px;
}

.summary-item.total {
  border-bottom: none;
  font-weight: bold;
  font-size: 16px;
  color: #333;
  margin-bottom: 15px;
}

.btn-checkout {
  width: 100%;
}

/* Reviews */
.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.review-card {
  background-color: #f9fafb;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.review-header h4 {
  margin: 0;
  color: #333;
}

.rating {
  display: flex;
  gap: 3px;
}

.rating i {
  color: #fbbf24;
  font-size: 14px;
}

.review-text {
  color: #666;
  margin: 10px 0;
  font-size: 14px;
}

.review-date {
  font-size: 12px;
  color: #999;
}

/* Wishlist Grid */
.wishlist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.wishlist-card {
  background-color: #f9fafb;
  border-radius: 8px;
  padding: 15px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s;
}

.wishlist-card:hover {
  border-color: #ec4899;
  box-shadow: 0 2px 8px rgba(236, 72, 153, 0.1);
}

.product-image-placeholder {
  width: 100%;
  height: 150px;
  background-color: #e5e7eb;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  color: #d1d5db;
  margin-bottom: 10px;
}

.product-info h4 {
  margin: 0 0 5px 0;
  color: #333;
}

.product-info .price {
  color: #ef4444;
  font-weight: bold;
  font-size: 16px;
}

.product-info .supplier {
  font-size: 12px;
  color: #666;
  margin-top: 5px;
}

.wishlist-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.btn-add-cart {
  flex: 1;
  background-color: #3b82f6;
  color: white;
}

.btn-add-cart:hover {
  background-color: #2563eb;
}

/* Analytics Grid */
.analytics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.metric-card {
  background-color: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.metric-card h3 {
  color: #333;
  margin-bottom: 15px;
  font-size: 16px;
  font-weight: 600;
}

.metric-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.metric-list p {
  padding: 8px 0;
  color: #666;
  border-bottom: 1px solid #e5e7eb;
  font-size: 14px;
}

.metric-list p:last-child {
  border-bottom: none;
}

/* Buttons */
.btn-primary {
  background-color: #3b82f6;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-small {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-view {
  background-color: #8b5cf6;
  color: white;
}

.btn-view:hover {
  background-color: #7c3aed;
}

/* Status Badges */
.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  background-color: #dbeafe;
  color: #1e40af;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.status-completed {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-cancelled {
  background-color: #fee2e2;
  color: #991b1b;
}

/* Recent Section */
.recent-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 30px;
}

.recent-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recent-card h2 {
  font-size: 18px;
  margin-bottom: 15px;
  color: #333;
}

.list-items {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background-color: #f9fafb;
  border-radius: 4px;
}

.item-name {
  font-weight: 600;
  color: #333;
}

.item-amount,
.item-sub {
  font-size: 14px;
  color: #666;
}

/* Responsive */
@media (max-width: 768px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .tab-buttons {
    flex-wrap: wrap;
  }

  .tab-btn {
    flex: 0 1 auto;
    padding: 12px 15px;
  }

  .cart-container {
    grid-template-columns: 1fr;
  }

  .cart-summary {
    position: static;
  }

  .deliveries-grid,
  .wishlist-grid,
  .analytics-grid {
    grid-template-columns: 1fr;
  }

  .recent-section {
    grid-template-columns: 1fr;
  }
}
</style>
