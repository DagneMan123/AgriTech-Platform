<template>
  <div class="supplier-layout">
    <SupplierSidebar @logout="handleLogout" />
    <div class="supplier-dashboard">
      <!-- Header -->
      <div class="dashboard-header">
        <h1>Supplier Dashboard</h1>
        <p>Manage inventory, orders, and business performance</p>
      </div>

      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="card-icon products">
            <i class="fas fa-box"></i>
          </div>
          <div class="card-content">
            <h3>Total Products</h3>
            <p class="card-value">{{ dashboard?.product_statistics?.total || 0 }}</p>
            <p class="card-sub">Active listings</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon inventory">
            <i class="fas fa-boxes"></i>
          </div>
          <div class="card-content">
            <h3>Inventory Value</h3>
            <p class="card-value">${{ formatNumber(dashboard?.inventory_value) }}</p>
            <p class="card-sub">Total stock</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon stock">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div class="card-content">
            <h3>Low Stock Items</h3>
            <p class="card-value">{{ dashboard?.low_stock_items || 0 }}</p>
            <p class="card-sub">Needs reorder</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon orders">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="card-content">
            <h3>Total Orders</h3>
            <p class="card-value">{{ dashboard?.order_statistics?.total || 0 }}</p>
            <p class="card-sub">All sales</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon revenue">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="card-content">
            <h3>Revenue</h3>
            <p class="card-value">${{ formatNumber(dashboard?.revenue) }}</p>
            <p class="card-sub">Avg: ${{ formatNumber(dashboard?.average_order_value) }}</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon warehouse">
            <i class="fas fa-warehouse"></i>
          </div>
          <div class="card-content">
            <h3>Warehouses</h3>
            <p class="card-value">{{ dashboard?.warehouses?.length || 0 }}</p>
            <p class="card-sub">Storage facilities</p>
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

        <!-- Inventory Tab -->
        <div v-show="activeTab === 'inventory'" class="tab-content">
          <div class="section-header">
            <h2>Inventory Management</h2>
            <button class="btn-primary" @click="showAddProductDialog = true">+ Add Product</button>
          </div>

          <div class="inventory-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Category</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Total Value</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in dashboard?.low_stock_items || []" :key="product.id">
                  <td>{{ product.name || 'Product' }}</td>
                  <td>{{ product.category || 'General' }}</td>
                  <td>{{ product.quantity_in_stock || 0 }}</td>
                  <td>${{ formatNumber(product.price) }}</td>
                  <td>${{ formatNumber((product.price || 0) * (product.quantity_in_stock || 0)) }}</td>
                  <td><span class="status-badge status-warning">Low Stock</span></td>
                  <td class="action-buttons">
                    <button class="btn-small btn-edit">Edit</button>
                    <button class="btn-small btn-reorder">Reorder</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Orders Tab -->
        <div v-show="activeTab === 'orders'" class="tab-content">
          <div class="section-header">
            <h2>Sales Orders</h2>
            <div class="filter-controls">
              <select v-model="orderFilter" class="filter-select">
                <option value="">All Orders</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
              </select>
            </div>
          </div>

          <div class="orders-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Buyer</th>
                  <th>Items</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in dashboard?.recent_orders || []" :key="order.id">
                  <td>#{{ order.id }}</td>
                  <td>{{ order.buyer?.name || 'N/A' }}</td>
                  <td>{{ order.items?.length || 0 }}</td>
                  <td>${{ formatNumber(order.total_amount) }}</td>
                  <td><span class="status-badge" :class="`status-${order.status}`">{{ order.status }}</span></td>
                  <td>{{ formatDate(order.created_at) }}</td>
                  <td class="action-buttons">
                    <button class="btn-small btn-view">View</button>
                    <button v-if="order.status === 'pending'" class="btn-small btn-accept">Accept</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Warehouses Tab -->
        <div v-show="activeTab === 'warehouses'" class="tab-content">
          <div class="section-header">
            <h2>Warehouse Management</h2>
            <button class="btn-primary" @click="showAddWarehouseDialog = true">+ Add Warehouse</button>
          </div>

          <div class="warehouses-grid">
            <div v-for="warehouse in dashboard?.warehouses || []" :key="warehouse.id" class="warehouse-card">
              <div class="warehouse-header">
                <h3>{{ warehouse.name }}</h3>
                <span class="badge">{{ warehouse.location }}</span>
              </div>
              <div class="warehouse-info">
                <p><strong>Capacity:</strong> {{ warehouse.total_capacity }} units</p>
                <p><strong>Current Stock:</strong> {{ warehouse.current_stock }} units</p>
                <p><strong>Utilization:</strong> {{ ((warehouse.current_stock / warehouse.total_capacity) * 100).toFixed(1) }}%</p>
              </div>
              <div class="warehouse-bar">
                <div class="utilization-bar" :style="{width: ((warehouse.current_stock / warehouse.total_capacity) * 100) + '%'}"></div>
              </div>
              <div class="warehouse-actions">
                <button class="btn-small btn-edit">Edit</button>
                <button class="btn-small btn-view">Details</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Sales Analytics Tab -->
        <div v-show="activeTab === 'analytics'" class="tab-content">
          <div class="section-header">
            <h2>Sales Analytics</h2>
            <div class="period-selector">
              <button 
                v-for="period in [7, 30, 90]"
                :key="period"
                :class="['period-btn', { active: selectedPeriod === period }]"
                @click="selectedPeriod = period"
              >
                {{ period }} Days
              </button>
            </div>
          </div>

          <div class="analytics-grid">
            <div class="metric-card">
              <h3>Total Sales</h3>
              <p class="big-number">${{ formatNumber(dashboard?.revenue) }}</p>
              <p class="metric-sub">In selected period</p>
            </div>
            <div class="metric-card">
              <h3>Orders Count</h3>
              <p class="big-number">{{ dashboard?.order_statistics?.total || 0 }}</p>
              <p class="metric-sub">Total transactions</p>
            </div>
            <div class="metric-card">
              <h3>Avg Order Value</h3>
              <p class="big-number">${{ formatNumber(dashboard?.average_order_value) }}</p>
              <p class="metric-sub">Per order</p>
            </div>
            <div class="metric-card">
              <h3>Top Product</h3>
              <p class="big-number">{{ dashboard?.recent_products?.[0]?.name || 'N/A' }}</p>
              <p class="metric-sub">Best seller</p>
            </div>
          </div>
        </div>

        <!-- License Status Tab -->
        <div v-show="activeTab === 'license'" class="tab-content">
          <div class="section-header">
            <h2>License Management</h2>
          </div>

          <div class="license-info">
            <div class="license-card">
              <h3>Business License</h3>
              <div class="license-details">
                <p><strong>Status:</strong> <span class="status-badge status-active">Active</span></p>
                <p><strong>License Number:</strong> LIC-2024-001</p>
                <p><strong>Issued:</strong> January 1, 2024</p>
                <p><strong>Expires:</strong> December 31, 2024</p>
              </div>
              <button class="btn-primary">Renew License</button>
            </div>

            <div class="license-card">
              <h3>Compliance Status</h3>
              <div class="compliance-items">
                <div class="compliance-item">
                  <i class="fas fa-check-circle"></i>
                  <span>Tax Registration</span>
                </div>
                <div class="compliance-item">
                  <i class="fas fa-check-circle"></i>
                  <span>Health & Safety</span>
                </div>
                <div class="compliance-item">
                  <i class="fas fa-check-circle"></i>
                  <span>Product Testing</span>
                </div>
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
              <span class="item-date">{{ formatDate(order.created_at) }}</span>
            </div>
          </div>
        </div>

        <div class="recent-card">
          <h2>Recent Products</h2>
          <div class="list-items">
            <div v-for="product in (dashboard?.recent_products || []).slice(0, 5)" :key="product.id" class="list-item">
              <span class="item-name">{{ product.name }}</span>
              <span class="item-amount">${{ formatNumber(product.price) }}</span>
              <span class="item-quantity">Stock: {{ product.quantity_in_stock }}</span>
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
import SupplierSidebar from '@/components/Sidebar/SupplierSidebar.vue'

const auth = useAuthStore()
const router = useRouter()
const activeTab = ref('inventory')
const tabs = ['inventory', 'orders', 'warehouses', 'analytics', 'license']

const dashboard = ref(null)
const orderFilter = ref('')
const selectedPeriod = ref(30)
const showAddProductDialog = ref(false)
const showAddWarehouseDialog = ref(false)

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  try {
    const res = await fetch('/api/supplier/dashboard', {
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
    inventory: 'Inventory',
    orders: 'Orders',
    warehouses: 'Warehouses',
    analytics: 'Sales Analytics',
    license: 'License Status'
  }
  return names[tab] || tab
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.supplier-layout {
  display: flex;
  height: 100vh;
}

.supplier-dashboard {
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

.card-icon.products {
  background-color: #f59e0b;
}

.card-icon.inventory {
  background-color: #8b5cf6;
}

.card-icon.stock {
  background-color: #ef4444;
}

.card-icon.orders {
  background-color: #3b82f6;
}

.card-icon.revenue {
  background-color: #10b981;
}

.card-icon.warehouse {
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
  color: #f59e0b;
}

.tab-btn.active {
  color: #f59e0b;
  border-bottom-color: #f59e0b;
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

/* Warehouses Grid */
.warehouses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.warehouse-card {
  background-color: #f9fafb;
  border-radius: 8px;
  padding: 15px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s;
}

.warehouse-card:hover {
  border-color: #f59e0b;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
}

.warehouse-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.warehouse-header h3 {
  margin: 0;
  color: #333;
  font-size: 16px;
}

.warehouse-info p {
  margin: 5px 0;
  font-size: 14px;
  color: #666;
}

.warehouse-bar {
  background-color: #e5e7eb;
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
  margin: 10px 0;
}

.utilization-bar {
  height: 100%;
  background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

.warehouse-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

/* Period Selector */
.period-selector {
  display: flex;
  gap: 10px;
}

.period-btn {
  padding: 8px 16px;
  border: 1px solid #e5e7eb;
  background-color: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: #666;
  transition: all 0.3s;
}

.period-btn:hover,
.period-btn.active {
  background-color: #f59e0b;
  color: white;
  border-color: #f59e0b;
}

/* Analytics Grid */
.analytics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.metric-card {
  background-color: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  text-align: center;
}

.metric-card h3 {
  color: #666;
  margin-bottom: 10px;
  font-size: 14px;
}

.big-number {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.metric-sub {
  font-size: 12px;
  color: #999;
}

/* License Info */
.license-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.license-card {
  background-color: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.license-card h3 {
  color: #333;
  margin-bottom: 15px;
  font-size: 16px;
  font-weight: 600;
}

.license-details p {
  padding: 8px 0;
  color: #666;
  border-bottom: 1px solid #e5e7eb;
  font-size: 14px;
}

.license-details p:last-child {
  border-bottom: none;
}

.compliance-items {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 15px;
}

.compliance-item {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #333;
  font-size: 14px;
}

.compliance-item i {
  color: #10b981;
  font-size: 16px;
}

/* Buttons */
.btn-primary {
  background-color: #f59e0b;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover {
  background-color: #d97706;
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

.btn-edit {
  background-color: #3b82f6;
  color: white;
}

.btn-edit:hover {
  background-color: #2563eb;
}

.btn-view {
  background-color: #8b5cf6;
  color: white;
}

.btn-view:hover {
  background-color: #7c3aed;
}

.btn-reorder {
  background-color: #ef4444;
  color: white;
}

.btn-reorder:hover {
  background-color: #dc2626;
}

.btn-accept {
  background-color: #10b981;
  color: white;
}

.btn-accept:hover {
  background-color: #059669;
}

/* Status Badges */
.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-warning {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.status-confirmed {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-badge.status-shipped {
  background-color: #cffafe;
  color: #164e63;
}

.status-badge.status-delivered {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-active {
  background-color: #d1fae5;
  color: #065f46;
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
  flex: 1;
}

.item-amount,
.item-date,
.item-quantity {
  font-size: 14px;
  color: #666;
  text-align: right;
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

  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .warehouses-grid,
  .analytics-grid,
  .license-info,
  .recent-section {
    grid-template-columns: 1fr;
  }
}
</style>
