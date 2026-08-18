<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-dashboard">
      <!-- Header -->
      <div class="dashboard-header">
        <h1>Farmer Dashboard</h1>
        <p>Manage your farms, crops, and agricultural business</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading dashboard data...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <svg class="error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <p class="error-message">{{ error }}</p>
        <button @click="fetchDashboardData" class="btn-retry">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 4v6h6M23 20v-6h-6"></path>
            <path d="M20.49 9A9 9 0 0 0 5.64 5.64M3.51 15A9 9 0 0 0 18.36 18.36"></path>
          </svg>
          Retry
        </button>
      </div>

      <!-- Summary Statistics -->
      <div v-if="!loading && !error" class="stats-section">
        <div class="stat-card stat-farms">
          <div class="stat-header">
            <h3>Total Farms</h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          </div>
          <p class="stat-value">{{ dashboard?.summary?.total_farms || 0 }}</p>
          <p class="stat-subtitle">{{ dashboard?.summary?.total_farm_area_hectares || 0 }} hectares</p>
        </div>

        <div class="stat-card stat-crops">
          <div class="stat-header">
            <h3>Active Crops</h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2c1.1 0 2 .9 2 2 0 1-2 6-2 6s-2-5-2-6c0-1.1.9-2 2-2z"></path><path d="M12 10c-5.5 0-10 5-10 10s4.5 10 10 10 10-5 10-10-4.5-10-10-10z"></path></svg>
          </div>
          <p class="stat-value">{{ dashboard?.summary?.active_crops || 0 }}</p>
          <p class="stat-subtitle">of {{ dashboard?.summary?.total_crops || 0 }} total</p>
        </div>

        <div class="stat-card stat-products">
          <div class="stat-header">
            <h3>Products</h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
          </div>
          <p class="stat-value">{{ dashboard?.summary?.active_products || 0 }}</p>
          <p class="stat-subtitle">of {{ dashboard?.summary?.total_products || 0 }} total</p>
        </div>

        <div class="stat-card stat-orders">
          <div class="stat-header">
            <h3>Orders</h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 3H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4m0-18v18m0-18h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-10"></path></svg>
          </div>
          <p class="stat-value">{{ dashboard?.summary?.pending_orders || 0 }}</p>
          <p class="stat-subtitle">pending</p>
        </div>

        <div class="stat-card stat-revenue">
          <div class="stat-header">
            <h3>Revenue</h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="1"></circle><path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m5.08 5.08l4.24 4.24M1 12h6m6 0h6M4.22 19.78l4.24-4.24m5.08-5.08l4.24-4.24"></path></svg>
          </div>
          <p class="stat-value">${{ formatNumber(dashboard?.summary?.total_sales || 0) }}</p>
          <p class="stat-subtitle">total sales</p>
        </div>

        <div class="stat-card stat-consultations">
          <div class="stat-header">
            <h3>Consultations</h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
          </div>
          <p class="stat-value">{{ dashboard?.summary?.pending_consultations || 0 }}</p>
          <p class="stat-subtitle">pending</p>
        </div>
      </div>

      <!-- Recent Activity -->
      <div v-if="!loading && !error" class="recent-section">
        <div class="recent-card">
          <h2>Recent Harvests</h2>
          <div v-if="dashboard?.recent_harvests?.length > 0" class="list-items">
            <div v-for="harvest in dashboard?.recent_harvests" :key="harvest.id" class="list-item">
              <span class="item-name">{{ harvest.crop?.name }}</span>
              <span class="item-quantity">{{ harvest.quantity }} units</span>
              <span class="item-date">{{ formatDate(harvest.harvest_date) }}</span>
            </div>
          </div>
          <div v-else class="empty-state">
            <p>No harvests yet</p>
          </div>
        </div>

        <div class="recent-card">
          <h2>Pending Loans</h2>
          <div class="list-items">
            <div class="list-item">
              <span class="item-name">Seasonal Funding</span>
              <span class="item-amount">$2,000</span>
              <span class="status-badge status-pending">Pending</span>
            </div>
          </div>
        </div>

        <div class="recent-card">
          <h2>Recent Orders</h2>
          <div v-if="dashboard?.recent_orders?.length > 0" class="list-items">
            <div v-for="order in dashboard?.recent_orders?.slice(0, 3)" :key="order.id" class="list-item">
              <span class="item-name">Order #{{ order.id }}</span>
              <span class="item-amount">${{ formatNumber(order.grand_total) }}</span>
              <span class="status-badge" :class="`status-${order.status}`">{{ order.status }}</span>
            </div>
          </div>
          <div v-else class="empty-state">
            <p>No orders yet</p>
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
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import { farmerAPI } from '@/api/farmer'

const auth = useAuthStore()
const router = useRouter()

const dashboard = ref(null)
const loading = ref(false)
const error = ref(null)

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  loading.value = true
  error.value = null
  try {
    // Debug: Check if token exists
    const token = localStorage.getItem('auth_token')
    if (!token) {
      error.value = 'No authentication token found. Please log in again.'
      loading.value = false
      return
    }

    const res = await farmerAPI.getDashboard()
    dashboard.value = res.data
    
    // Ensure we have the expected data structure
    if (!dashboard.value.summary) {
      dashboard.value.summary = {
        total_farms: 0,
        total_farm_area_hectares: 0,
        total_crops: 0,
        active_crops: 0,
        total_products: 0,
        active_products: 0,
        pending_orders: 0,
        total_orders: 0,
        completed_orders: 0,
        total_sales: 0,
        average_order_value: 0,
        pending_consultations: 0,
        total_consultations: 0,
      }
    }
  } catch (err) {
    console.error('Error fetching dashboard:', err)
    
    // Provide more detailed error messages
    if (err.response?.status === 401) {
      error.value = 'Your session has expired. Please log in again.'
    } else if (err.response?.status === 403) {
      error.value = 'You do not have permission to access this dashboard.'
    } else if (err.response?.status === 500) {
      error.value = 'Server error. Please try again later or contact support.'
    } else if (err.message === 'Network Error') {
      error.value = 'Network error. Please check your internet connection.'
    } else {
      error.value = err.response?.data?.message || err.message || 'Failed to load dashboard data. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout {
  display: flex;
  height: 100vh;
}

.farmer-dashboard {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f0f2f5;
  min-height: 100vh;
  padding: 30px;
}

.loading-container {
  background: white;
  border-radius: 12px;
  padding: 60px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  display: flex;
  flex-direction: column;
  align-items: center;
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

.loading-container p {
  color: #666;
  font-size: 16px;
  font-weight: 500;
}

.error-container {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border: 2px solid #fca5a5;
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
}

.error-icon {
  width: 60px;
  height: 60px;
  color: #dc2626;
  margin-bottom: 15px;
}

.error-message {
  color: #991b1b;
  margin-bottom: 20px;
  font-size: 16px;
  line-height: 1.5;
}

.btn-retry {
  background: #dc2626;
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
}

.btn-retry:hover {
  background: #b91c1c;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-retry svg {
  width: 16px;
  height: 16px;
}

.dashboard-header {
  margin-bottom: 40px;
}

.dashboard-header h1 {
  font-size: 32px;
  font-weight: 800;
  color: #1f2937;
  margin-bottom: 8px;
}

.dashboard-header p {
  color: #6b7280;
  font-size: 16px;
}

/* Stats Section */
.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s;
  border-left: 5px solid #10b981;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.stat-card.stat-crops {
  border-left-color: #8b5cf6;
}

.stat-card.stat-products {
  border-left-color: #f59e0b;
}

.stat-card.stat-orders {
  border-left-color: #3b82f6;
}

.stat-card.stat-revenue {
  border-left-color: #ef4444;
}

.stat-card.stat-consultations {
  border-left-color: #ec4899;
}

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.stat-header h3 {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0;
  font-weight: 700;
}

.stat-header svg {
  width: 20px;
  height: 20px;
  color: #d1d5db;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 8px 0 4px 0;
}

.stat-subtitle {
  font-size: 12px;
  color: #9ca3af;
  margin: 0;
}

/* Recent Activity Section */
.recent-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 40px;
}

.recent-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.recent-card h2 {
  font-size: 18px;
  margin-bottom: 20px;
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
  border-radius: 8px;
  font-size: 14px;
  border-left: 3px solid #10b981;
}

.item-name {
  font-weight: 600;
  color: #1f2937;
  flex: 1;
}

.item-quantity,
.item-amount,
.item-date {
  font-size: 13px;
  color: #6b7280;
  margin: 0 12px;
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

.status-badge.status-completed {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-active {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-cancelled {
  background-color: #fee2e2;
  color: #991b1b;
}

.empty-state {
  padding: 20px;
  text-align: center;
  color: #9ca3af;
  font-size: 14px;
}

/* Responsive */
@media (max-width: 1024px) {
  .farmer-dashboard {
    padding: 20px;
  }

  .stats-section {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}

@media (max-width: 768px) {
  .farmer-dashboard {
    margin-left: 0;
    padding: 15px;
  }

  .dashboard-header h1 {
    font-size: 24px;
  }

  .stats-section {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }

  .stat-value {
    font-size: 22px;
  }

  .recent-section {
    grid-template-columns: 1fr;
  }
}
</style>
