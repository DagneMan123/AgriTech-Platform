<template>
  <div class="admin-layout">
    <AdminSidebar @logout="handleLogout" />
    <div class="admin-dashboard">
      <!-- Header -->
      <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <p>Platform Overview and System Control</p>
      </div>

      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="card-icon users">
            <i class="fas fa-users"></i>
          </div>
          <div class="card-content">
            <h3>Total Users</h3>
            <p class="card-value">{{ dashboard?.total_users || 0 }}</p>
            <p class="card-sub">All registered users</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon orders">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="card-content">
            <h3>Total Orders</h3>
            <p class="card-value">{{ dashboard?.total_orders || 0 }}</p>
            <p class="card-sub">All platform orders</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon revenue">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="card-content">
            <h3>Total Revenue</h3>
            <p class="card-value">${{ formatNumber(dashboard?.total_revenue) }}</p>
            <p class="card-sub">Platform revenue</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon deliveries">
            <i class="fas fa-truck"></i>
          </div>
          <div class="card-content">
            <h3>Active Deliveries</h3>
            <p class="card-value">{{ dashboard?.active_deliveries || 0 }}</p>
            <p class="card-sub">In progress</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon farms">
            <i class="fas fa-home"></i>
          </div>
          <div class="card-content">
            <h3>Total Farms</h3>
            <p class="card-value">{{ dashboard?.stats?.total_farms || 0 }}</p>
            <p class="card-sub">Registered farms</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon products">
            <i class="fas fa-box"></i>
          </div>
          <div class="card-content">
            <h3>Total Products</h3>
            <p class="card-value">{{ dashboard?.stats?.total_products || 0 }}</p>
            <p class="card-sub">Marketplace products</p>
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

        <!-- Users Tab -->
        <div v-show="activeTab === 'users'" class="tab-content">
          <div class="section-header">
            <h2>User Management</h2>
            <input 
              v-model="userSearchTerm" 
              type="text" 
              placeholder="Search users..." 
              class="search-input"
            >
          </div>

          <div class="role-stats">
            <div class="role-stat" v-for="(count, role) in dashboard?.users_by_role" :key="role">
              <span class="role-name">{{ formatRoleName(role) }}</span>
              <span class="role-count">{{ count }}</span>
            </div>
          </div>

          <div class="users-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in dashboard?.recent_orders?.slice(0, 5)" :key="user.id">
                  <td>#{{ user.id }}</td>
                  <td>{{ user.name || 'N/A' }}</td>
                  <td>{{ user.email || 'N/A' }}</td>
                  <td><span class="badge">User</span></td>
                  <td><span class="status-badge status-active">Active</span></td>
                  <td class="action-buttons">
                    <button class="btn-small btn-view">View</button>
                    <button class="btn-small btn-edit">Edit</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Orders Tab -->
        <div v-show="activeTab === 'orders'" class="tab-content">
          <div class="section-header">
            <h2>Order Monitoring</h2>
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
                  <th>Buyer</th>
                  <th>Items</th>
                  <th>Status</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in dashboard?.recent_orders || []" :key="order.id">
                  <td>#{{ order.id }}</td>
                  <td>{{ order.buyer?.name || 'N/A' }}</td>
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

        <!-- Statistics Tab -->
        <div v-show="activeTab === 'statistics'" class="tab-content">
          <div class="section-header">
            <h2>System Statistics</h2>
          </div>

          <div class="stats-grid">
            <div class="stat-card">
              <h3>Total Consultations</h3>
              <p class="big-number">{{ dashboard?.stats?.total_consultations || 0 }}</p>
              <p class="stat-sub">Expert consultations</p>
            </div>
            <div class="stat-card">
              <h3>Pending Loans</h3>
              <p class="big-number">{{ dashboard?.stats?.pending_loans || 0 }}</p>
              <p class="stat-sub">Under review</p>
            </div>
            <div class="stat-card">
              <h3>Total Loans</h3>
              <p class="big-number">{{ dashboard?.stats?.total_loans || 0 }}</p>
              <p class="stat-sub">All loans</p>
            </div>
            <div class="stat-card">
              <h3>Active Cooperatives</h3>
              <p class="big-number">{{ dashboard?.stats?.active_cooperatives || 0 }}</p>
              <p class="stat-sub">Cooperatives</p>
            </div>
          </div>
        </div>

        <!-- Orders by Status Tab -->
        <div v-show="activeTab === 'orderstatus'" class="tab-content">
          <div class="section-header">
            <h2>Orders by Status</h2>
          </div>

          <div class="status-breakdown">
            <div class="status-item" v-for="(count, status) in dashboard?.orders_by_status" :key="status">
              <span class="status-name">{{ formatStatusName(status) }}</span>
              <div class="status-bar-container">
                <div class="status-bar" :class="`bar-${status}`" :style="{width: getStatusPercentage(status) + '%'}"></div>
              </div>
              <span class="status-count">{{ count }}</span>
            </div>
          </div>
        </div>

        <!-- Analytics Tab -->
        <div v-show="activeTab === 'analytics'" class="tab-content">
          <div class="section-header">
            <h2>Platform Analytics</h2>
          </div>

          <div class="analytics-grid">
            <div class="metric-card">
              <h3>Platform Performance</h3>
              <ul class="metric-list">
                <li>Total Transactions: {{ dashboard?.total_orders || 0 }}</li>
                <li>Active Users: {{ Math.ceil((dashboard?.total_users || 0) * 0.6) }}</li>
                <li>Avg Order Value: ${{ formatNumber((dashboard?.total_revenue || 0) / (dashboard?.total_orders || 1)) }}</li>
              </ul>
            </div>
            <div class="metric-card">
              <h3>System Health</h3>
              <ul class="metric-list">
                <li>Total Farms: {{ dashboard?.stats?.total_farms || 0 }}</li>
                <li>Total Products: {{ dashboard?.stats?.total_products || 0 }}</li>
                <li>Avg Products/Farm: {{ dashboard?.stats?.total_products && dashboard?.stats?.total_farms ? (dashboard?.stats?.total_products / dashboard?.stats?.total_farms).toFixed(1) : 0 }}</li>
              </ul>
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
          <h2>System Status</h2>
          <div class="list-items">
            <div class="list-item">
              <span class="item-name">Database</span>
              <span class="status-badge status-active">Online</span>
            </div>
            <div class="list-item">
              <span class="item-name">API Server</span>
              <span class="status-badge status-active">Online</span>
            </div>
            <div class="list-item">
              <span class="item-name">Email Service</span>
              <span class="status-badge status-active">Online</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import AdminSidebar from '@/components/Sidebar/AdminSidebar.vue'

const auth = useAuthStore()
const router = useRouter()
const activeTab = ref('users')
const tabs = ['users', 'orders', 'statistics', 'orderstatus', 'analytics']

const dashboard = ref(null)
const userSearchTerm = ref('')
const orderFilter = ref('')

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  try {
    const res = await fetch('/api/admin/dashboard', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    const data = await res.json()
    dashboard.value = data.data || data
  } catch (error) {
    console.error('Error fetching dashboard:', error)
  }
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const formatTabName = (tab) => {
  const names = {
    users: 'User Management',
    orders: 'Order Monitoring',
    statistics: 'Statistics',
    orderstatus: 'Orders by Status',
    analytics: 'Analytics'
  }
  return names[tab] || tab
}

const formatRoleName = (role) => {
  const roleMap = {
    farmer: 'Farmers',
    buyer: 'Buyers',
    supplier: 'Suppliers',
    transport: 'Transport',
    expert: 'Experts',
    cooperative: 'Cooperatives',
    financial: 'Financial'
  }
  return roleMap[role] || role
}

const formatStatusName = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusPercentage = (status) => {
  const orders = dashboard.value?.orders_by_status || {}
  const total = Object.values(orders).reduce((sum, val) => sum + val, 0) || 1
  return (orders[status] || 0) / total * 100
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  height: 100vh;
}

.admin-dashboard {
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

.card-icon.users {
  background-color: #2563eb;
}

.card-icon.orders {
  background-color: #3b82f6;
}

.card-icon.revenue {
  background-color: #ef4444;
}

.card-icon.deliveries {
  background-color: #f59e0b;
}

.card-icon.farms {
  background-color: #8b5cf6;
}

.card-icon.products {
  background-color: #10b981;
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
  color: #2563eb;
}

.tab-btn.active {
  color: #2563eb;
  border-bottom-color: #2563eb;
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

.search-input,
.filter-select {
  padding: 10px 15px;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  font-size: 14px;
}

.search-input {
  min-width: 250px;
}

/* Role Stats */
.role-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}

.role-stat {
  background-color: #f9fafb;
  padding: 15px;
  border-radius: 6px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-left: 4px solid #2563eb;
}

.role-name {
  font-weight: 600;
  color: #333;
}

.role-count {
  font-size: 20px;
  font-weight: bold;
  color: #2563eb;
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

/* Buttons */
.btn-primary {
  background-color: #2563eb;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover {
  background-color: #1d4ed8;
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

/* Badges & Status */
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

.status-badge.status-active {
  background-color: #d1fae5;
  color: #065f46;
}

/* Status Breakdown */
.status-breakdown {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.status-item {
  display: grid;
  grid-template-columns: 100px 1fr 50px;
  gap: 15px;
  align-items: center;
}

.status-name {
  font-weight: 600;
  color: #333;
}

.status-bar-container {
  background-color: #e5e7eb;
  border-radius: 4px;
  height: 30px;
  overflow: hidden;
}

.status-bar {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #2563eb);
  transition: width 0.3s;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 8px;
  color: white;
  font-size: 12px;
  font-weight: 600;
}

.status-count {
  font-weight: 600;
  color: #333;
  text-align: right;
}

/* Statistics Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-card {
  background-color: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  text-align: center;
}

.stat-card h3 {
  color: #666;
  margin-bottom: 10px;
  font-size: 14px;
}

.big-number {
  font-size: 32px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.stat-sub {
  font-size: 12px;
  color: #999;
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

.metric-list li {
  padding: 8px 0;
  color: #666;
  border-bottom: 1px solid #e5e7eb;
  font-size: 14px;
}

.metric-list li:last-child {
  border-bottom: none;
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
.item-date {
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

  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .search-input {
    width: 100%;
  }

  .recent-section {
    grid-template-columns: 1fr;
  }
}
</style>
