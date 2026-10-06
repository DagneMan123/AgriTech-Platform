<template>
  <div class="farmer-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-dashboard">
      <div class="dashboard-header-wrapper">
        <div class="dashboard-header">
          <h1>Farmer Dashboard</h1>
          <p>Manage your farms, crops, and agricultural business</p>
        </div>
        <button
          @click="toggleTheme"
          class="theme-toggle-btn"
          :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
          :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        >
          <span class="theme-icon">{{ isDark ? '☀️' : '🌙' }}</span>
        </button>
      </div>

      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading dashboard data...</p>
      </div>

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
          <p class="stat-value">{{ formatNumber(dashboard?.summary?.total_sales || 0) }}</p>
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

      <div v-if="!loading && !error" class="charts-section">
        <div class="chart-card full-width">
          <div class="chart-header">
            <h2>Revenue Trend</h2>
            <div class="time-filter-buttons">
              <button
                v-for="option in timeRangeOptions"
                :key="option.value"
                @click="onTimeRangeChange(option.value)"
                :class="['filter-btn', { active: selectedTimeRange === option.value }]"
              >
                {{ option.label }}
              </button>
            </div>
          </div>
          <div class="chart-container">
            <Line :data="chartData" :options="chartOptions" />
          </div>
        </div>

        <div class="chart-grid">
          <div class="chart-card">
            <h2>Crop Sales Distribution</h2>
            <div class="doughnut-container">
              <Doughnut :data="doughnutData" :options="doughnutOptions" />
            </div>
          </div>

          <div class="transactions-card">
            <h2>Recent Transactions</h2>
            <div class="transactions-table">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Crop</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="tx in dashboard?.recent_transactions" :key="tx.id" class="transaction-row">
                    <td class="tx-id">{{ tx.id }}</td>
                    <td class="tx-crop">{{ tx.crop }}</td>
                    <td class="tx-qty">{{ tx.quantity }}</td>
                    <td class="tx-price">{{ formatNumber(tx.price) }}</td>
                    <td class="tx-date">{{ formatDate(tx.date) }}</td>
                  </tr>
                  <tr v-if="!dashboard?.recent_transactions?.length" class="empty-row">
                    <td colspan="5">No transactions yet</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

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
          <h2>Recent Orders</h2>
          <div v-if="dashboard?.recent_orders?.length > 0" class="list-items">
            <div v-for="order in dashboard?.recent_orders?.slice(0, 3)" :key="order.id" class="list-item">
              <span class="item-name">Order #{{ order.id }}</span>
              <span class="item-amount">{{ formatNumber(order.grand_total) }}</span>
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
import { ref, onMounted, computed } from 'vue'
import { Line, Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler, ArcElement } from 'chart.js'
import { useAuthStore } from '@/stores/authStore'
import { useTheme } from '@/composables/useTheme'
import { useRouter } from 'vue-router'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import { farmerAPI } from '@/api/farmer'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler, ArcElement)

const auth = useAuthStore()
const router = useRouter()
const { isDark, isLight, toggleTheme } = useTheme()

const dashboard = ref(null)
const loading = ref(false)
const error = ref(null)
const selectedTimeRange = ref('30')

const timeRangeOptions = [
  { label: '7D', value: '7' },
  { label: '30D', value: '30' },
  { label: '1Y', value: '365' }
]

const chartData = computed(() => {
  if (!dashboard.value?.chart_data) {
    return { labels: [], datasets: [] }
  }
  return dashboard.value.chart_data
})

const doughnutData = computed(() => {
  if (!dashboard.value?.crop_sales) {
    return { labels: [], datasets: [] }
  }
  return dashboard.value.crop_sales
})

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        color: isDark.value ? '#f3f4f6' : '#1f2937',
        font: { size: 14, weight: '600' },
        padding: 20,
        usePointStyle: true,
      }
    },
    tooltip: {
      backgroundColor: isDark.value ? 'rgba(19, 27, 46, 0.9)' : 'rgba(255, 255, 255, 0.9)',
      titleColor: isDark.value ? '#f3f4f6' : '#1f2937',
      bodyColor: isDark.value ? '#f3f4f6' : '#1f2937',
      borderColor: isDark.value ? '#1e293b' : '#e5e7eb',
      borderWidth: 1,
      padding: 12,
      displayColors: true,
    }
  },
  scales: {
    y: {
      grid: {
        color: isDark.value ? 'rgba(148, 163, 184, 0.1)' : 'rgba(0, 0, 0, 0.05)',
      },
      ticks: {
        color: isDark.value ? '#94a3b8' : '#6b7280',
        font: { size: 12 },
      }
    },
    x: {
      grid: { display: false },
      ticks: {
        color: isDark.value ? '#94a3b8' : '#6b7280',
        font: { size: 12 },
      }
    }
  }
}))

const doughnutOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      position: 'right',
      labels: {
        color: isDark.value ? '#f3f4f6' : '#1f2937',
        font: { size: 13, weight: '500' },
        padding: 15,
        usePointStyle: true,
      }
    },
    tooltip: {
      backgroundColor: isDark.value ? 'rgba(19, 27, 46, 0.9)' : 'rgba(255, 255, 255, 0.9)',
      titleColor: isDark.value ? '#f3f4f6' : '#1f2937',
      bodyColor: isDark.value ? '#f3f4f6' : '#1f2937',
      borderColor: isDark.value ? '#1e293b' : '#e5e7eb',
      borderWidth: 1,
      padding: 12,
      callbacks: {
        label: function(context) {
          return context.label + ': ' + context.parsed + '%'
        }
      }
    }
  }
}))

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  loading.value = true
  error.value = null
  try {
    const token = localStorage.getItem('auth_token')
    if (!token) {
      error.value = 'No authentication token found. Please log in again.'
      loading.value = false
      return
    }

    const res = await farmerAPI.getDashboard(selectedTimeRange.value)
    dashboard.value = res.data
    
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

const onTimeRangeChange = async (range) => {
  selectedTimeRange.value = range
  await fetchDashboardData()
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

.farmer-layout.light {
  background-color: #f0f2f5;
  color: #1f2937;
}

.farmer-layout.dark {
  background-color: #0b0f17;
  color: #f3f4f6;
}

.farmer-dashboard {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  min-height: 100vh;
  padding: 20px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.farmer-layout.light .farmer-dashboard {
  background-color: #f0f2f5;
  color: #1f2937;
}

.farmer-layout.dark .farmer-dashboard {
  background-color: #0b0f17;
  color: #f3f4f6;
}

.dashboard-header-wrapper {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 25px;
  gap: 15px;
}

.dashboard-header {
  flex: 1;
}

.dashboard-header h1 {
  font-size: 26px;
  font-weight: 800;
  margin-bottom: 4px;
  transition: color 0.3s ease;
}

.farmer-layout.light .dashboard-header h1 {
  color: #1f2937;
}

.farmer-layout.dark .dashboard-header h1 {
  color: #ffffff;
}

.dashboard-header p {
  font-size: 14px;
  transition: color 0.3s ease;
}

.farmer-layout.light .dashboard-header p {
  color: #6b7280;
}

.farmer-layout.dark .dashboard-header p {
  color: #9ca3af;
}

.theme-toggle-btn {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: 2px solid;
  background-color: transparent;
  cursor: pointer;
  font-size: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.farmer-layout.light .theme-toggle-btn {
  border-color: #d1d5db;
  background-color: #ffffff;
  color: #1f2937;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.farmer-layout.light .theme-toggle-btn:hover {
  background-color: #f3f4f6;
  border-color: #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
  transform: translateY(-2px);
}

.farmer-layout.dark .theme-toggle-btn {
  border-color: #1e293b;
  background-color: #131b2e;
  color: #f3f4f6;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.farmer-layout.dark .theme-toggle-btn:hover {
  background-color: #1e293b;
  border-color: #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  transform: translateY(-2px);
}

.loading-container {
  border-radius: 12px;
  padding: 60px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  transition: background-color 0.3s ease;
}

.farmer-layout.light .loading-container {
  background-color: #ffffff;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  color: #666;
}

.farmer-layout.dark .loading-container {
  background-color: #131b2e;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
  color: #d1d5db;
  border: 1px solid #1e293b;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.farmer-layout.light .spinner {
  border-color: #e5e7eb;
  border-top-color: #10b981;
}

.farmer-layout.dark .spinner {
  border-color: #1e293b;
  border-top-color: #10b981;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-container {
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  transition: all 0.3s ease;
}

.farmer-layout.light .error-container {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border: 2px solid #fca5a5;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
}

.farmer-layout.dark .error-container {
  background: linear-gradient(135deg, rgba(127, 29, 29, 0.3) 0%, rgba(153, 27, 27, 0.3) 100%);
  border: 2px solid rgba(220, 38, 38, 0.5);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
}

.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px;
  margin-bottom: 30px;
}

.stat-card {
  border-radius: 8px;
  padding: 14px 12px;
  transition: all 0.3s;
  border-left: 4px solid #10b981;
}

.farmer-layout.light .stat-card {
  background: white;
  color: #1f2937;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.farmer-layout.dark .stat-card {
  background: #131b2e;
  color: #f3f4f6;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  border: 1px solid #1e293b;
  border-left: 4px solid #10b981;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 10px;
}

.stat-header h3 {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  margin: 0;
  font-weight: 700;
  transition: color 0.3s ease;
  flex: 1;
}

.stat-header svg {
  width: 20px;
  height: 20px;
  stroke-width: 1.5;
  flex-shrink: 0;
  margin-left: 6px;
}

.stat-value {
  font-size: 20px;
  font-weight: 700;
  margin: 4px 0 2px 0;
}

.stat-subtitle {
  font-size: 11px;
  margin: 0;
  transition: color 0.3s ease;
  opacity: 0.85;
}

.charts-section {
  margin-bottom: 30px;
}

.chart-card {
  border-radius: 10px;
  padding: 18px;
  transition: all 0.3s;
}

.farmer-layout.light .chart-card {
  background: white;
  color: #1f2937;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.farmer-layout.dark .chart-card {
  background: #131b2e;
  color: #f3f4f6;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  border: 1px solid #1e293b;
}

.chart-card.full-width {
  grid-column: 1 / -1;
  margin-bottom: 20px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.chart-header h2 {
  font-size: 16px;
  font-weight: 700;
  margin: 0;
  transition: color 0.3s ease;
}

.farmer-layout.light .chart-header h2 {
  color: #1f2937;
}

.farmer-layout.dark .chart-header h2 {
  color: #ffffff;
}

.time-filter-buttons {
  display: flex;
  gap: 8px;
}

.filter-btn {
  padding: 6px 12px;
  border-radius: 6px;
  border: 2px solid;
  background: transparent;
  cursor: pointer;
  font-weight: 600;
  font-size: 12px;
  transition: all 0.3s;
}

.farmer-layout.light .filter-btn {
  border-color: #d1d5db;
  color: #6b7280;
}

.farmer-layout.light .filter-btn:hover {
  border-color: #10b981;
  color: #10b981;
}

.farmer-layout.light .filter-btn.active {
  background-color: #10b981;
  color: white;
  border-color: #10b981;
}

.farmer-layout.dark .filter-btn {
  border-color: #1e293b;
  color: #94a3b8;
}

.farmer-layout.dark .filter-btn:hover {
  border-color: #10b981;
  color: #10b981;
}

.farmer-layout.dark .filter-btn.active {
  background-color: #10b981;
  color: white;
  border-color: #10b981;
}

.chart-container {
  position: relative;
  height: 300px;
  margin-top: 12px;
}

.chart-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.doughnut-container {
  position: relative;
  height: 280px;
  margin-top: 12px;
}

.transactions-card {
  border-radius: 10px;
  padding: 18px;
  transition: all 0.3s;
}

.farmer-layout.light .transactions-card {
  background: white;
  color: #1f2937;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.farmer-layout.dark .transactions-card {
  background: #131b2e;
  color: #f3f4f6;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  border: 1px solid #1e293b;
}

.transactions-card h2 {
  font-size: 16px;
  font-weight: 700;
  margin: 0 0 14px 0;
}

.transactions-table {
  overflow-x: auto;
  max-height: 320px;
  overflow-y: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}

thead {
  position: sticky;
  top: 0;
}

.farmer-layout.light thead {
  background-color: #f9fafb;
}

.farmer-layout.dark thead {
  background-color: #1a2338;
}

th {
  padding: 10px 8px;
  text-align: left;
  font-weight: 700;
  border-bottom: 2px solid;
}

.farmer-layout.light th {
  border-color: #e5e7eb;
  color: #6b7280;
}

.farmer-layout.dark th {
  border-color: #1e293b;
  color: #94a3b8;
}

.transaction-row {
  border-bottom: 1px solid;
  transition: background-color 0.3s;
}

.farmer-layout.light .transaction-row {
  border-color: #e5e7eb;
}

.farmer-layout.light .transaction-row:hover {
  background-color: #f9fafb;
}

.farmer-layout.dark .transaction-row {
  border-color: #1e293b;
}

.farmer-layout.dark .transaction-row:hover {
  background-color: #1a2338;
}

td {
  padding: 10px 8px;
}

.tx-id {
  font-weight: 600;
  color: #10b981;
}

.empty-row {
  text-align: center;
}

.farmer-layout.light .empty-row {
  color: #9ca3af;
}

.farmer-layout.dark .empty-row {
  color: #64748b;
}

.recent-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
  margin-top: 30px;
}

.recent-card {
  border-radius: 10px;
  padding: 16px;
  transition: all 0.3s;
}

.farmer-layout.light .recent-card {
  background: white;
  color: #1f2937;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.farmer-layout.dark .recent-card {
  background: #131b2e;
  color: #f3f4f6;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  border: 1px solid #1e293b;
}

.recent-card h2 {
  font-size: 15px;
  margin-bottom: 14px;
  font-weight: 700;
  transition: color 0.3s ease;
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
  border-radius: 6px;
  font-size: 13px;
  border-left: 3px solid #10b981;
  transition: all 0.3s ease;
}

.farmer-layout.light .list-item {
  background-color: #f9fafb;
  color: #1f2937;
}

.farmer-layout.dark .list-item {
  background-color: #1a2338;
  color: #e2e8f0;
  border: 1px solid #222f46;
  border-left: 3px solid #10b981;
}

.list-item:hover {
  transform: translateX(2px);
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.status-badge.status-completed,
.status-badge.status-active {
  background-color: #d1fae5;
  color: #065f46;
}

.farmer-layout.dark .status-badge.status-completed,
.farmer-layout.dark .status-badge.status-active {
  background-color: rgba(16, 185, 129, 0.25);
  color: #6ee7b7;
}

.empty-state {
  padding: 16px;
  text-align: center;
  font-size: 13px;
  transition: color 0.3s ease;
}

.farmer-layout.light .empty-state {
  color: #9ca3af;
}

.farmer-layout.dark .empty-state {
  color: #64748b;
}

@media (max-width: 1024px) {
  .chart-grid {
    grid-template-columns: 1fr;
  }

  .farmer-dashboard {
    padding: 16px;
  }

  .stats-section {
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
  }
}

@media (max-width: 768px) {
  .farmer-dashboard {
    margin-left: 0;
    padding: 12px;
  }

  .dashboard-header h1 {
    font-size: 20px;
  }

  .stats-section {
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
  }

  .stat-card {
    padding: 10px 8px;
  }

  .stat-header h3 {
    font-size: 9px;
  }

  .stat-value {
    font-size: 16px;
  }

  .stat-subtitle {
    font-size: 10px;
  }

  .recent-section {
    grid-template-columns: 1fr;
  }

  .chart-container {
    height: 220px;
  }

  .doughnut-container {
    height: 220px;
  }

  .transactions-table {
    font-size: 12px;
  }

  .time-filter-buttons {
    gap: 6px;
  }

  .filter-btn {
    padding: 6px 10px;
    font-size: 11px;
  }
}

@media (max-width: 480px) {
  .stats-section {
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
  }

  .stat-card {
    padding: 8px 6px;
  }

  .stat-header h3 {
    font-size: 8px;
  }

  .stat-value {
    font-size: 14px;
  }

  .stat-subtitle {
    font-size: 9px;
  }

  .dashboard-header h1 {
    font-size: 18px;
  }

  .dashboard-header p {
    font-size: 13px;
  }

  .chart-container {
    height: 180px;
  }

  .doughnut-container {
    height: 180px;
  }
}
</style>
