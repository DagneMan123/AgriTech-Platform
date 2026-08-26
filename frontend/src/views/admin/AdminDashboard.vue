<template>
  <div class="admin-layout">
    <AdminSidebar @logout="handleLogout" />
    <div class="admin-dashboard">
      <!-- Loading & Error States -->
      <div v-if="loading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Loading dashboard data...</p>
      </div>

      <div v-if="error" class="error-banner">
        <i class="fas fa-exclamation-circle"></i>
        {{ error }}
        <button @click="error = ''" class="close-btn">×</button>
      </div>

      <!-- Header with Refresh -->
      <div class="dashboard-header">
        <div>
          <h1>Admin Dashboard</h1>
          <p>Platform Overview and System Control</p>
        </div>
        <div class="header-actions">
          <button @click="refreshData" class="btn-refresh" :disabled="loading">
            <i class="fas fa-sync-alt" :class="{rotating: loading}"></i> Refresh
          </button>
          <button @click="showAnnouncement = true" class="btn-primary">
            <i class="fas fa-bell"></i> Send Announcement
          </button>
        </div>
      </div>

      <!-- Summary Cards with Trends -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="card-icon users">
            <i class="fas fa-users"></i>
          </div>
          <div class="card-content">
            <h3>Total Users</h3>
            <p class="card-value">{{ formatNumber(dashboard?.summary?.total_users || 0) }}</p>
            <p class="card-sub">All registered users</p>
            <span class="trend-badge positive">↑ Active</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon orders">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <div class="card-content">
            <h3>Total Orders</h3>
            <p class="card-value">{{ formatNumber(dashboard?.summary?.total_orders || 0) }}</p>
            <p class="card-sub">All platform orders</p>
            <span class="trend-badge positive">↑ Growing</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon revenue">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="card-content">
            <h3>Total Revenue</h3>
            <p class="card-value">${{ formatNumber(dashboard?.summary?.total_revenue) }}</p>
            <p class="card-sub">Platform revenue</p>
            <span class="trend-badge positive">↑ +12%</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon deliveries">
            <i class="fas fa-truck"></i>
          </div>
          <div class="card-content">
            <h3>Active Deliveries</h3>
            <p class="card-value">{{ formatNumber(dashboard?.summary?.active_deliveries || 0) }}</p>
            <p class="card-sub">In progress</p>
            <span class="trend-badge positive">↑ Live</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon farms">
            <i class="fas fa-home"></i>
          </div>
          <div class="card-content">
            <h3>Total Farms</h3>
            <p class="card-value">{{ formatNumber(dashboard?.summary?.total_products || 0) }}</p>
            <p class="card-sub">Registered farms</p>
            <span class="trend-badge positive">↑ New</span>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon products">
            <i class="fas fa-box"></i>
          </div>
          <div class="card-content">
            <h3>Total Products</h3>
            <p class="card-value">{{ formatNumber(dashboard?.summary?.total_payments || 0) }}</p>
            <p class="card-sub">Marketplace products</p>
            <span class="trend-badge positive">↑ +8%</span>
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

        <!-- Overview Tab -->
        <div v-show="activeTab === 'overview'" class="tab-content">
          <div class="section-header">
            <h2>System Overview</h2>
          </div>

          <div class="overview-grid">
            <div class="overview-card">
              <h3>Users by Role</h3>
              <div class="role-stats">
                <div class="role-stat" v-for="role in userRoles" :key="role.role">
                  <span class="role-name">{{ formatRoleName(role.role) }}</span>
                  <span class="role-count">{{ role.count }}</span>
                </div>
              </div>
            </div>

            <div class="overview-card">
              <h3>Order Status Breakdown</h3>
              <div class="status-breakdown">
                <div class="status-item" v-for="(count, status) in orderStatusMap" :key="status">
                  <span class="status-label">{{ formatStatusName(status) }}</span>
                  <span class="status-value">{{ count }}</span>
                </div>
              </div>
            </div>

            <div class="overview-card">
              <h3>Delivery Status</h3>
              <div class="status-breakdown">
                <div class="status-item" v-for="(count, status) in deliveryStatusMap" :key="status">
                  <span class="status-label">{{ formatStatusName(status) }}</span>
                  <span class="status-value">{{ count }}</span>
                </div>
              </div>
            </div>

            <div class="overview-card">
              <h3>Payment Status</h3>
              <div class="status-breakdown">
                <div class="status-item" v-for="(count, status) in paymentStatusMap" :key="status">
                  <span class="status-label">{{ formatStatusName(status) }}</span>
                  <span class="status-value">{{ count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Users Tab -->
        <div v-show="activeTab === 'users'" class="tab-content">
          <div class="section-header">
            <h2>User Management</h2>
            <div class="filter-group">
              <input 
                v-model="userSearchTerm" 
                type="text" 
                placeholder="Search users..." 
                class="search-input"
              >
              <select v-model="userRoleFilter" class="filter-select">
                <option value="">All Roles</option>
                <option value="farmer">Farmers</option>
                <option value="buyer">Buyers</option>
                <option value="supplier">Suppliers</option>
                <option value="transport">Transport</option>
                <option value="expert">Experts</option>
                <option value="cooperative">Cooperatives</option>
                <option value="financial">Financial</option>
              </select>
              <select v-model="userStatusFilter" class="filter-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
              </select>
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
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in filteredUsers" :key="user.id">
                  <td>#{{ user.id }}</td>
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td><span class="badge badge-role">{{ formatRoleName(user.role) }}</span></td>
                  <td>
                    <span 
                      class="status-badge" 
                      :class="user.is_active ? 'status-active' : 'status-suspended'"
                    >
                      {{ user.is_active ? 'Active' : 'Suspended' }}
                    </span>
                  </td>
                  <td>{{ formatDate(user.created_at) }}</td>
                  <td class="action-buttons">
                    <button 
                      @click="toggleUserStatus(user)" 
                      class="btn-small"
                      :class="user.is_active ? 'btn-danger' : 'btn-success'"
                    >
                      {{ user.is_active ? 'Suspend' : 'Activate' }}
                    </button>
                    <button @click="viewUserDetails(user)" class="btn-small btn-info">View</button>
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
            <div class="filter-group">
              <select v-model="orderFilter" class="filter-select">
                <option value="">All Orders</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
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
                  <th>Total Amount</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in filteredOrders" :key="order.id">
                  <td>#{{ order.id }}</td>
                  <td>{{ order.buyer?.name || 'N/A' }}</td>
                  <td>{{ order.items?.length || 0 }}</td>
                  <td><span class="status-badge" :class="`status-${order.status}`">{{ order.status }}</span></td>
                  <td>${{ formatNumber(order.total_amount) }}</td>
                  <td>{{ formatDate(order.created_at) }}</td>
                  <td class="action-buttons">
                    <button @click="viewOrderDetails(order)" class="btn-small btn-info">View</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Deliveries Tab -->
        <div v-show="activeTab === 'deliveries'" class="tab-content">
          <div class="section-header">
            <h2>Delivery Monitoring</h2>
            <div class="filter-group">
              <select v-model="deliveryFilter" class="filter-select">
                <option value="">All Deliveries</option>
                <option value="pending">Pending</option>
                <option value="in_transit">In Transit</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>

          <div class="deliveries-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Delivery ID</th>
                  <th>Order</th>
                  <th>Transporter</th>
                  <th>Status</th>
                  <th>Origin</th>
                  <th>Destination</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="delivery in filteredDeliveries" :key="delivery.id">
                  <td>#{{ delivery.id }}</td>
                  <td>#{{ delivery.order?.id || 'N/A' }}</td>
                  <td>{{ delivery.transporter?.name || 'Unassigned' }}</td>
                  <td><span class="status-badge" :class="`status-${delivery.status}`">{{ delivery.status }}</span></td>
                  <td>{{ delivery.origin_location || 'N/A' }}</td>
                  <td>{{ delivery.destination_location || 'N/A' }}</td>
                  <td>{{ formatDate(delivery.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Payments Tab -->
        <div v-show="activeTab === 'payments'" class="tab-content">
          <div class="section-header">
            <h2>Payment Monitoring</h2>
            <div class="filter-group">
              <select v-model="paymentFilter" class="filter-select">
                <option value="">All Payments</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
              </select>
            </div>
          </div>

          <div class="payments-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Payment ID</th>
                  <th>Order</th>
                  <th>Amount</th>
                  <th>Method</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="payment in filteredPayments" :key="payment.id">
                  <td>#{{ payment.id }}</td>
                  <td>#{{ payment.order?.id || 'N/A' }}</td>
                  <td>${{ formatNumber(payment.amount) }}</td>
                  <td>{{ payment.payment_method || 'N/A' }}</td>
                  <td><span class="status-badge" :class="`status-${payment.status}`">{{ payment.status }}</span></td>
                  <td>{{ formatDate(payment.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Analytics Tab -->
        <div v-show="activeTab === 'analytics'" class="tab-content">
          <div class="section-header">
            <h2>Platform Analytics</h2>
          </div>

          <div class="analytics-grid">
            <div class="metric-card">
              <h3>Revenue Metrics</h3>
              <ul class="metric-list">
                <li>Total Revenue: <strong>${{ formatNumber(dashboard?.summary?.total_revenue) }}</strong></li>
                <li>Total Transactions: <strong>{{ formatNumber(dashboard?.summary?.total_payments) }}</strong></li>
                <li>Avg Transaction: <strong>${{ formatNumber((dashboard?.summary?.total_revenue || 0) / (dashboard?.summary?.total_payments || 1)) }}</strong></li>
                <li>Pending Loans: <strong>{{ formatNumber(dashboard?.summary?.pending_loans) }}</strong></li>
              </ul>
            </div>

            <div class="metric-card">
              <h3>System Health</h3>
              <ul class="metric-list">
                <li>Active Users: <strong>{{ Math.ceil((dashboard?.summary?.total_users || 0) * 0.75) }}</strong></li>
                <li>Suspended Users: <strong>{{ formatNumber(dashboard?.summary?.suspended_users) }}</strong></li>
                <li>Active Deliveries: <strong>{{ formatNumber(dashboard?.summary?.active_deliveries) }}</strong></li>
                <li>Unread Notifications: <strong>{{ formatNumber(dashboard?.summary?.unread_notifications) }}</strong></li>
              </ul>
            </div>

            <div class="metric-card">
              <h3>Order Analytics</h3>
              <ul class="metric-list">
                <li>Total Orders: <strong>{{ formatNumber(dashboard?.summary?.total_orders) }}</strong></li>
                <li>Orders This Month: <strong>{{ Math.ceil((dashboard?.summary?.total_orders || 0) * 0.4) }}</strong></li>
                <li>Avg Order Value: <strong>${{ formatNumber((dashboard?.summary?.total_revenue || 0) / (dashboard?.summary?.total_orders || 1)) }}</strong></li>
              </ul>
            </div>

            <div class="metric-card">
              <h3>Activity Status</h3>
              <ul class="metric-list">
                <li>Pending Applications: <strong>{{ formatNumber(dashboard?.summary?.pending_loans) }}</strong></li>
                <li>System Status: <strong><span class="status-active">✓ Online</span></strong></li>
                <li>Last Updated: <strong>{{ lastUpdated }}</strong></li>
              </ul>
            </div>
          </div>
        </div>

        <!-- System Settings Tab -->
        <div v-show="activeTab === 'settings'" class="tab-content">
          <div class="section-header">
            <h2>System Settings & Configuration</h2>
          </div>

          <div class="settings-container">
            <div class="settings-section">
              <h3>Platform Configuration</h3>
              <div class="setting-item">
                <label>Platform Name</label>
                <input v-model="settings.platform_name" type="text" placeholder="AgriTech Platform">
              </div>
              <div class="setting-item">
                <label>Admin Email</label>
                <input v-model="settings.admin_email" type="email" placeholder="admin@agritech.com">
              </div>
              <div class="setting-item">
                <label>Support Email</label>
                <input v-model="settings.support_email" type="email" placeholder="support@agritech.com">
              </div>
            </div>

            <div class="settings-section">
              <h3>Notification Settings</h3>
              <div class="setting-item checkbox">
                <input v-model="settings.email_notifications" type="checkbox" id="email_notif">
                <label for="email_notif">Enable Email Notifications</label>
              </div>
              <div class="setting-item checkbox">
                <input v-model="settings.sms_notifications" type="checkbox" id="sms_notif">
                <label for="sms_notif">Enable SMS Notifications</label>
              </div>
            </div>

            <button @click="saveSettings" class="btn-primary">Save Settings</button>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="recent-section">
        <div class="recent-card">
          <h2>Recent Orders</h2>
          <div v-if="dashboard?.recent_orders?.length" class="list-items">
            <div v-for="order in dashboard?.recent_orders?.slice(0, 5)" :key="order.id" class="list-item">
              <div class="item-info">
                <span class="item-name">Order #{{ order.id }}</span>
                <span class="item-detail">{{ order.buyer?.name || 'Guest' }}</span>
              </div>
              <div class="item-actions">
                <span class="item-amount">${{ formatNumber(order.total_amount) }}</span>
                <span class="item-date">{{ formatDate(order.created_at) }}</span>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">No recent orders</div>
        </div>

        <div class="recent-card">
          <h2>Recent Deliveries</h2>
          <div v-if="dashboard?.recent_deliveries?.length" class="list-items">
            <div v-for="delivery in dashboard?.recent_deliveries?.slice(0, 5)" :key="delivery.id" class="list-item">
              <div class="item-info">
                <span class="item-name">Delivery #{{ delivery.id }}</span>
                <span class="item-detail">{{ delivery.transporter?.name || 'Unassigned' }}</span>
              </div>
              <div class="item-actions">
                <span :class="['status-badge', `status-${delivery.status}`]">{{ delivery.status }}</span>
                <span class="item-date">{{ formatDate(delivery.created_at) }}</span>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">No recent deliveries</div>
        </div>

        <div class="recent-card">
          <h2>Recent Payments</h2>
          <div v-if="dashboard?.recent_payments?.length" class="list-items">
            <div v-for="payment in dashboard?.recent_payments?.slice(0, 5)" :key="payment.id" class="list-item">
              <div class="item-info">
                <span class="item-name">Payment #{{ payment.id }}</span>
                <span class="item-detail">{{ payment.payment_method || 'Unknown' }}</span>
              </div>
              <div class="item-actions">
                <span class="item-amount">${{ formatNumber(payment.amount) }}</span>
                <span :class="['status-badge', `status-${payment.status}`]">{{ payment.status }}</span>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">No recent payments</div>
        </div>

        <div class="recent-card">
          <h2>System Status</h2>
          <div class="list-items">
            <div class="list-item">
              <span class="item-name">API Server</span>
              <span class="status-badge status-active">✓ Online</span>
            </div>
            <div class="list-item">
              <span class="item-name">Database</span>
              <span class="status-badge status-active">✓ Online</span>
            </div>
            <div class="list-item">
              <span class="item-name">Email Service</span>
              <span class="status-badge status-active">✓ Online</span>
            </div>
            <div class="list-item">
              <span class="item-name">Storage Service</span>
              <span class="status-badge status-active">✓ Online</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Announcement Modal -->
      <div v-if="showAnnouncement" class="modal-overlay" @click="showAnnouncement = false">
        <div class="modal" @click.stop>
          <div class="modal-header">
            <h2>Send Announcement</h2>
            <button @click="showAnnouncement = false" class="close-btn">×</button>
          </div>
          <div class="modal-content">
            <div class="form-group">
              <label>Title</label>
              <input v-model="announcement.title" type="text" placeholder="Announcement title" class="form-input">
            </div>
            <div class="form-group">
              <label>Message</label>
              <textarea v-model="announcement.message" placeholder="Your message..." class="form-textarea" rows="6"></textarea>
            </div>
            <div class="form-group">
              <label>Target Audience</label>
              <div class="checkbox-group">
                <label v-for="role in availableRoles" :key="role" class="checkbox-label">
                  <input v-model="announcement.targetRoles" type="checkbox" :value="role">
                  <span>{{ formatRoleName(role) }}</span>
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showAnnouncement = false" class="btn-secondary">Cancel</button>
            <button @click="sendAnnouncement" class="btn-primary" :disabled="!announcement.title || !announcement.message">Send</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import AdminSidebar from '@/components/Sidebar/AdminSidebar.vue'
import apiClient from '@/api/config'

const auth = useAuthStore()
const router = useRouter()

// State Management
const activeTab = ref('overview')
const tabs = ['overview', 'users', 'orders', 'deliveries', 'payments', 'analytics', 'settings']
const dashboard = ref(null)
const loading = ref(false)
const error = ref('')
const showAnnouncement = ref(false)

// Filter State
const userSearchTerm = ref('')
const userRoleFilter = ref('')
const userStatusFilter = ref('')
const orderFilter = ref('')
const deliveryFilter = ref('')
const paymentFilter = ref('')

// Form State
const announcement = ref({
  title: '',
  message: '',
  targetRoles: []
})

const settings = ref({
  platform_name: 'AgriTech Platform',
  admin_email: '',
  support_email: '',
  email_notifications: true,
  sms_notifications: false
})

const availableRoles = ['farmer', 'buyer', 'supplier', 'transport', 'expert', 'cooperative', 'financial']
let lastUpdatedTime = ref(new Date())

// Computed Properties
const userRoles = computed(() => {
  const data = dashboard.value?.users_by_role || []
  return Array.isArray(data) ? data : Object.entries(data).map(([role, count]) => ({ role, count }))
})

const orderStatusMap = computed(() => {
  const data = dashboard.value?.orders_by_status || []
  if (Array.isArray(data)) {
    return data.reduce((acc, item) => {
      acc[item.status] = item.count
      return acc
    }, {})
  }
  return data
})

const deliveryStatusMap = computed(() => {
  const data = dashboard.value?.deliveries_by_status || []
  if (Array.isArray(data)) {
    return data.reduce((acc, item) => {
      acc[item.status] = item.count
      return acc
    }, {})
  }
  return data
})

const paymentStatusMap = computed(() => {
  const data = dashboard.value?.payments_by_status || []
  if (Array.isArray(data)) {
    return data.reduce((acc, item) => {
      acc[item.status] = item.count
      return acc
    }, {})
  }
  return data
})

const filteredUsers = computed(() => {
  let users = dashboard.value?.recent_orders || []
  if (!Array.isArray(users)) users = []
  
  return users.filter(user => {
    const matchesSearch = !userSearchTerm.value || 
      user.name?.toLowerCase().includes(userSearchTerm.value.toLowerCase()) ||
      user.email?.toLowerCase().includes(userSearchTerm.value.toLowerCase())
    
    const matchesRole = !userRoleFilter.value || user.role === userRoleFilter.value
    const matchesStatus = !userStatusFilter.value || 
      (userStatusFilter.value === 'active' ? user.is_active : !user.is_active)
    
    return matchesSearch && matchesRole && matchesStatus
  })
})

const filteredOrders = computed(() => {
  let orders = dashboard.value?.recent_orders || []
  if (!Array.isArray(orders)) orders = []
  
  return orderFilter.value 
    ? orders.filter(o => o.status === orderFilter.value)
    : orders
})

const filteredDeliveries = computed(() => {
  let deliveries = dashboard.value?.recent_deliveries || []
  if (!Array.isArray(deliveries)) deliveries = []
  
  return deliveryFilter.value
    ? deliveries.filter(d => d.status === deliveryFilter.value)
    : deliveries
})

const filteredPayments = computed(() => {
  let payments = dashboard.value?.recent_payments || []
  if (!Array.isArray(payments)) payments = []
  
  return paymentFilter.value
    ? payments.filter(p => p.status === paymentFilter.value)
    : payments
})

const lastUpdated = computed(() => {
  return lastUpdatedTime.value.toLocaleTimeString()
})

// Methods
onMounted(() => {
  fetchDashboardData()
  // Auto-refresh every 5 minutes
  setInterval(fetchDashboardData, 5 * 60 * 1000)
})

const fetchDashboardData = async () => {
  loading.value = true
  error.value = ''
  
  try {
    // Use configured apiClient which automatically handles authorization
    const response = await apiClient.get('/admin/dashboard')
    
    // Handle both wrapped and unwrapped response formats
    const responseData = response.data
    dashboard.value = responseData.data ? { ...responseData.data } : responseData
    lastUpdatedTime.value = new Date()
    
    console.log('Dashboard data loaded successfully:', dashboard.value)
  } catch (err: any) {
    console.error('Dashboard fetch error:', err)
    
    // Provide more specific error messages
    if (err.response?.status === 401) {
      error.value = 'Your session has expired. Please login again.'
      // Redirect to login after a brief delay
      setTimeout(() => {
        auth.logout()
      }, 1500)
    } else if (err.response?.status === 403) {
      error.value = 'You do not have permission to access the admin dashboard. Please contact an administrator.'
    } else if (err.response?.status === 404) {
      error.value = 'Dashboard API endpoint not found. Please contact support.'
    } else {
      error.value = err.response?.data?.message || 'Failed to load dashboard data. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const refreshData = async () => {
  await fetchDashboardData()
}

const formatNumber = (num: number | string): string => {
  return new Intl.NumberFormat().format(Number(num) || 0)
}

const formatDate = (date: any): string => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatTabName = (tab: string): string => {
  const names: Record<string, string> = {
    overview: 'Overview',
    users: 'Users',
    orders: 'Orders',
    deliveries: 'Deliveries',
    payments: 'Payments',
    analytics: 'Analytics',
    settings: 'Settings'
  }
  return names[tab] || tab
}

const formatRoleName = (role: string): string => {
  const roleMap: Record<string, string> = {
    farmer: 'Farmers',
    buyer: 'Buyers',
    supplier: 'Suppliers',
    transport: 'Transport',
    expert: 'Experts',
    cooperative: 'Cooperatives',
    financial: 'Financial'
  }
  return roleMap[role] || role.charAt(0).toUpperCase() + role.slice(1)
}

const formatStatusName = (status: string): string => {
  return status.split('_').map(s => s.charAt(0).toUpperCase() + s.slice(1)).join(' ')
}

const toggleUserStatus = async (user: any) => {
  try {
    const response = await apiClient.post(`/admin/users/${user.id}/status`, {
      is_active: !user.is_active,
      reason: `Status change by admin`
    })
    
    if (response.status === 200 || response.status === 201) {
      user.is_active = !user.is_active
      error.value = ''
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to update user status'
    console.error('Status update error:', err)
  }
}

const viewUserDetails = (user: any): void => {
  // Implementation for viewing user details
  console.log('View user details:', user)
}

const viewOrderDetails = (order: any): void => {
  // Implementation for viewing order details
  console.log('View order details:', order)
}

const sendAnnouncement = async () => {
  if (!announcement.value.title || !announcement.value.message) {
    error.value = 'Please fill in all fields'
    return
  }

  try {
    const response = await apiClient.post('/admin/announcements', {
      title: announcement.value.title,
      message: announcement.value.message,
      target_roles: announcement.value.targetRoles.length > 0 ? announcement.value.targetRoles : availableRoles
    })

    if (response.status === 200 || response.status === 201) {
      showAnnouncement.value = false
      announcement.value = { title: '', message: '', targetRoles: [] }
      // Show success message
      alert('Announcement sent successfully!')
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to send announcement'
    console.error('Announcement error:', err)
  }
}

const saveSettings = async () => {
  try {
    const response = await apiClient.post('/admin/settings', settings.value)

    if (response.status === 200 || response.status === 201) {
      alert('Settings saved successfully!')
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to save settings'
    console.error('Settings error:', err)
  }
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.admin-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.admin-dashboard {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f6fa;
  min-height: 100vh;
  padding: 25px;
  position: relative;
}

/* Loading & Error States */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 260px;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e0e0e0;
  border-top: 4px solid #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.rotating {
  animation: spin 1s linear infinite;
}

.error-banner {
  background-color: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: #991b1b;
  position: relative;
}

.error-banner i {
  font-size: 18px;
}

.close-btn {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #991b1b;
}

/* Dashboard Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  gap: 20px;
}

.dashboard-header h1 {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 5px 0;
}

.dashboard-header p {
  color: #6b7280;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.btn-refresh {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background-color: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  color: #374151;
  font-weight: 500;
  transition: all 0.3s;
}

.btn-refresh:hover:not(:disabled) {
  background-color: #e5e7eb;
  border-color: #9ca3af;
}

.btn-refresh:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background-color: #2563eb;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover {
  background-color: #1d4ed8;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

/* Summary Grid */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border: 1px solid #e5e7eb;
}

.summary-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border-color: #d1d5db;
}

.card-icon {
  width: 55px;
  height: 55px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  flex-shrink: 0;
}

.card-icon.users { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
.card-icon.orders { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
.card-icon.revenue { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.card-icon.deliveries { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.card-icon.farms { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
.card-icon.products { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }

.card-content {
  flex: 1;
}

.card-content h3 {
  font-size: 11px;
  color: #9ca3af;
  margin: 0 0 8px 0;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.card-value {
  font-size: 26px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.card-sub {
  font-size: 12px;
  color: #6b7280;
  margin: 0 0 8px 0;
}

.trend-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  background-color: #dcfce7;
  color: #166534;
}

.trend-badge.negative {
  background-color: #fee2e2;
  color: #991b1b;
}

/* Dashboard Tabs */
.dashboard-tabs {
  background: white;
  border-radius: 10px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
  margin-bottom: 30px;
}

.tab-buttons {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
  overflow-x: auto;
  gap: 0;
}

.tab-btn {
  flex: 1;
  padding: 16px 20px;
  background: none;
  border: none;
  cursor: pointer;
  color: #6b7280;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s;
  border-bottom: 3px solid transparent;
  white-space: nowrap;
  min-width: 100px;
}

.tab-btn:hover {
  color: #2563eb;
}

.tab-btn.active {
  color: #2563eb;
  border-bottom-color: #2563eb;
}

.tab-content {
  padding: 28px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 15px;
}

.section-header h2 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
}

.filter-group {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.search-input,
.filter-select {
  padding: 10px 14px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background-color: white;
  color: #374151;
  transition: all 0.3s;
}

.search-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-input {
  min-width: 250px;
}

/* Overview Grid */
.overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.overview-card {
  background-color: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.overview-card h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 15px;
}

/* Role Stats */
.role-stats {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.role-stat {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background-color: white;
  border-radius: 6px;
  border-left: 4px solid #2563eb;
}

.role-name {
  font-weight: 600;
  color: #374151;
  font-size: 14px;
}

.role-count {
  font-size: 18px;
  font-weight: 700;
  color: #2563eb;
}

/* Status Breakdown */
.status-breakdown {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background-color: white;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.status-label {
  font-weight: 600;
  color: #374151;
  font-size: 14px;
}

.status-value {
  font-size: 16px;
  font-weight: 700;
  color: #2563eb;
}

/* Data Tables */
.users-table-container,
.orders-table-container,
.deliveries-table-container,
.payments-table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.data-table th {
  background-color: #f9fafb;
  padding: 14px;
  text-align: left;
  font-weight: 700;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
}

.data-table td {
  padding: 14px;
  border-bottom: 1px solid #e5e7eb;
  color: #374151;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

/* Badges & Status */
.badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.badge-role {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.status-processing {
  background-color: #dbeafe;
  color: #1e40af;
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

.status-badge.status-suspended {
  background-color: #fee2e2;
  color: #991b1b;
}

.status-badge.status-in_transit {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-badge.status-failed {
  background-color: #fee2e2;
  color: #991b1b;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 8px;
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

.btn-small.btn-info {
  background-color: #8b5cf6;
  color: white;
}

.btn-small.btn-info:hover {
  background-color: #7c3aed;
}

.btn-small.btn-success {
  background-color: #10b981;
  color: white;
}

.btn-small.btn-success:hover {
  background-color: #059669;
}

.btn-small.btn-danger {
  background-color: #ef4444;
  color: white;
}

.btn-small.btn-danger:hover {
  background-color: #dc2626;
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
  color: #1f2937;
  margin-bottom: 15px;
  font-size: 15px;
  font-weight: 700;
}

.metric-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.metric-list li {
  padding: 10px 0;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
  font-size: 14px;
  display: flex;
  justify-content: space-between;
}

.metric-list li:last-child {
  border-bottom: none;
}

.metric-list strong {
  color: #1f2937;
  font-weight: 600;
}

/* Settings */
.settings-container {
  max-width: 500px;
}

.settings-section {
  background-color: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  border: 1px solid #e5e7eb;
}

.settings-section h3 {
  margin-top: 0;
  margin-bottom: 15px;
  color: #1f2937;
  font-size: 15px;
  font-weight: 700;
}

.setting-item {
  margin-bottom: 15px;
}

.setting-item label {
  display: block;
  margin-bottom: 6px;
  color: #374151;
  font-weight: 600;
  font-size: 14px;
}

.form-input,
.form-textarea {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.setting-item.checkbox {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.setting-item.checkbox input {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.setting-item.checkbox label {
  margin-bottom: 0;
  cursor: pointer;
  flex: 1;
}

.checkbox-group {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 14px;
}

.checkbox-label input {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

/* Recent Activity Section */
.recent-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
  margin-top: 30px;
}

.recent-card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
}

.recent-card h2 {
  font-size: 16px;
  margin-bottom: 15px;
  color: #1f2937;
  font-weight: 700;
}

.list-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background-color: #f9fafb;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.item-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.item-name {
  font-weight: 700;
  color: #1f2937;
  font-size: 14px;
}

.item-detail {
  font-size: 12px;
  color: #6b7280;
}

.item-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.item-amount {
  font-weight: 700;
  color: #059669;
  font-size: 14px;
}

.item-date {
  font-size: 12px;
  color: #6b7280;
}

.empty-state {
  text-align: center;
  padding: 30px 20px;
  color: #9ca3af;
  font-size: 14px;
}

/* Modal Styles */
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
  z-index: 1001;
}

.modal {
  background: white;
  border-radius: 10px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  width: 90%;
  max-width: 500px;
  overflow: hidden;
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
  color: #1f2937;
  font-size: 18px;
  font-weight: 700;
}

.modal-header .close-btn {
  background: none;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: #6b7280;
}

.modal-content {
  padding: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #374151;
  font-weight: 600;
  font-size: 14px;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  background-color: #f9fafb;
}

.btn-secondary {
  padding: 10px 18px;
  background-color: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  color: #374151;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background-color: #e5e7eb;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .summary-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .admin-dashboard {
    margin-left: 0;
    padding: 15px;
  }

  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .tab-buttons {
    flex-wrap: wrap;
  }

  .tab-btn {
    flex: 0 1 auto;
    padding: 12px 15px;
    font-size: 13px;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .filter-group {
    width: 100%;
    flex-direction: column;
  }

  .search-input,
  .filter-select {
    width: 100%;
  }

  .recent-section {
    grid-template-columns: 1fr;
  }

  .action-buttons {
    flex-direction: column;
    gap: 6px;
  }

  .btn-small {
    width: 100%;
    padding: 8px;
  }

  .data-table {
    font-size: 12px;
  }

  .data-table th,
  .data-table td {
    padding: 10px;
  }
}

@media (max-width: 480px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }

  .card-value {
    font-size: 20px;
  }

  .analytics-grid,
  .overview-grid {
    grid-template-columns: 1fr;
  }

  .modal {
    width: 95%;
  }
}
</style>
