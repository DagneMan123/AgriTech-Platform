<template>
  <div class="farmer-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-dashboard">
      <!-- Header Section -->
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

      <!-- Loading State -->
      <div v-if="loading && !dashboard" class="loading-container">
        <div class="spinner"></div>
        <p>Loading real-time agricultural telemetry...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchDashboardData" class="btn-retry">
          <RefreshCw size="16" />
          <span>Retry Sync</span>
        </button>
      </div>

      <!-- Main Dashboard Content -->
      <div v-if="dashboard" class="dashboard-content">
        <!-- Interactive Metric Cards -->
        <div class="stats-section">
          <!-- Total Farms -->
          <div class="stat-card stat-farms clickable" @click="router.push('/farmer/farms')">
            <div class="stat-header">
              <h3>Total Farms</h3>
              <div class="stat-icon-wrapper">
                <Building2 size="18" />
              </div>
            </div>
            <p class="stat-value">{{ dashboard?.summary?.total_farms || 0 }}</p>
            <div class="stat-footer">
              <span class="stat-subtitle">{{ dashboard?.summary?.total_farm_area_hectares || 0 }} hectares cultivated</span>
              <ChevronRight size="14" class="stat-arrow" />
            </div>
          </div>

          <!-- Active Crops -->
          <div class="stat-card stat-crops clickable" @click="router.push('/farmer/crops')">
            <div class="stat-header">
              <h3>Active Crops</h3>
              <div class="stat-icon-wrapper">
                <Sprout size="18" />
              </div>
            </div>
            <p class="stat-value">{{ dashboard?.summary?.active_crops || 0 }}</p>
            <div class="stat-footer">
              <span class="stat-subtitle">of {{ dashboard?.summary?.total_crops || 0 }} varieties planted</span>
              <ChevronRight size="14" class="stat-arrow" />
            </div>
          </div>

          <!-- Listed Products -->
          <div class="stat-card stat-products clickable" @click="router.push('/farmer/products')">
            <div class="stat-header">
              <h3>Market Products</h3>
              <div class="stat-icon-wrapper">
                <Package size="18" />
              </div>
            </div>
            <p class="stat-value">{{ dashboard?.summary?.active_products || 0 }}</p>
            <div class="stat-footer">
              <span class="stat-subtitle">of {{ dashboard?.summary?.total_products || 0 }} products on sale</span>
              <ChevronRight size="14" class="stat-arrow" />
            </div>
          </div>

          <!-- Customer Orders -->
          <div class="stat-card stat-orders clickable" @click="router.push('/farmer/customer-orders')">
            <div class="stat-header">
              <h3>Pending Orders</h3>
              <div class="stat-icon-wrapper">
                <ShoppingCart size="18" />
              </div>
            </div>
            <p class="stat-value">{{ dashboard?.summary?.pending_orders || 0 }}</p>
            <div class="stat-footer">
              <span class="stat-subtitle">{{ dashboard?.summary?.total_orders || 0 }} total orders placed</span>
              <ChevronRight size="14" class="stat-arrow" />
            </div>
          </div>

          <!-- Total Revenue -->
          <div class="stat-card stat-revenue clickable" @click="router.push('/farmer/my-sales')">
            <div class="stat-header">
              <h3>Sales Revenue</h3>
              <div class="stat-icon-wrapper">
                <Banknote size="18" />
              </div>
            </div>
            <p class="stat-value">ETB {{ formatNumber(dashboard?.summary?.total_sales || 0) }}</p>
            <div class="stat-footer">
              <span class="stat-subtitle">{{ dashboard?.summary?.completed_orders || 0 }} orders fulfilled</span>
              <ChevronRight size="14" class="stat-arrow" />
            </div>
          </div>

          <!-- Advisory / Consultations -->
          <div class="stat-card stat-consultations clickable" @click="router.push('/farmer/consultations')">
            <div class="stat-header">
              <h3>Agronomy Advisory</h3>
              <div class="stat-icon-wrapper">
                <MessageSquare size="18" />
              </div>
            </div>
            <p class="stat-value">{{ dashboard?.summary?.pending_consultations || 0 }}</p>
            <div class="stat-footer">
              <span class="stat-subtitle">{{ dashboard?.summary?.total_consultations || 0 }} total sessions</span>
              <ChevronRight size="14" class="stat-arrow" />
            </div>
          </div>
        </div>

        <!-- Charts & Analytics Section -->
        <div class="charts-section">
          <!-- Full Width Area Chart: Revenue Trend -->
          <div class="chart-card full-width">
            <div class="chart-header">
              <div class="chart-title-group">
                <h2>Revenue Trend</h2>
                <span class="chart-subtitle">Aggregate revenue tracked in Ethiopian Birr (ETB)</span>
              </div>
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

          <!-- Grid: Donut Distribution & Recent Transactions -->
          <div class="chart-grid">
            <!-- Crop Sales Distribution Donut Chart -->
            <div class="chart-card">
              <div class="card-header-row">
                <h2>Commodity Sales Breakdown</h2>
                <span class="badge-pill">Volume Share</span>
              </div>
              <div class="doughnut-container">
                <Doughnut :data="doughnutData" :options="doughnutOptions" />
              </div>
            </div>

            <!-- Recent Sales Ledger Table -->
            <div class="transactions-card">
              <div class="card-header-row">
                <h2>Recent Transactions</h2>
                <button @click="router.push('/farmer/my-sales')" class="view-all-link">
                  <span>View All Sales</span>
                  <ArrowUpRight size="14" />
                </button>
              </div>
              <div class="transactions-table">
                <table>
                  <thead>
                    <tr>
                      <th>Order ID</th>
                      <th>Commodity</th>
                      <th>Qty</th>
                      <th>Amount</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="tx in dashboard?.recent_transactions?.slice(0, 6)"
                      :key="tx.id"
                      class="transaction-row clickable-row"
                      @click="router.push('/farmer/my-sales')"
                    >
                      <td class="tx-id">
                        <span class="id-tag">{{ tx.id }}</span>
                      </td>
                      <td class="tx-crop font-semibold">{{ tx.crop }}</td>
                      <td class="tx-qty">{{ tx.quantity }}</td>
                      <td class="tx-price text-emerald-600 font-bold">ETB {{ formatNumber(tx.price) }}</td>
                      <td class="tx-date text-slate-400">{{ formatDate(tx.date) }}</td>
                    </tr>
                    <tr v-if="!dashboard?.recent_transactions?.length" class="empty-row">
                      <td colspan="5">
                        <div class="empty-state-mini">
                          <p>No recent sales transactions recorded</p>
                          <button @click="router.push('/farmer/products')" class="btn-xs">Create Product</button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity Cards Section -->
        <div class="recent-section">
          <!-- Recent Harvests -->
          <div class="recent-card">
            <div class="card-header-row">
              <div class="flex items-center gap-2">
                <Sprout size="18" class="text-emerald-500" />
                <h2>Recent Field Harvests</h2>
              </div>
              <button @click="router.push('/farmer/harvests')" class="view-all-link">
                <span>View All</span>
                <ChevronRight size="14" />
              </button>
            </div>

            <div v-if="dashboard?.recent_harvests?.length > 0" class="list-items">
              <div
                v-for="harvest in dashboard?.recent_harvests"
                :key="harvest.id"
                class="list-item clickable-item"
                @click="router.push('/farmer/harvests')"
              >
                <div class="item-main">
                  <div class="item-avatar green">
                    <Sprout size="14" />
                  </div>
                  <div class="item-info">
                    <span class="item-name">{{ harvest.crop?.name || 'Field Produce' }}</span>
                    <span class="item-meta">{{ formatDate(harvest.harvest_date) }}</span>
                  </div>
                </div>
                <div class="item-badge-wrapper">
                  <span class="qty-badge">{{ harvest.quantity }} {{ harvest.unit || 'kg' }}</span>
                </div>
              </div>
            </div>

            <div v-else class="empty-state">
              <Sprout size="32" class="text-slate-300 dark:text-slate-600 mb-2" />
              <p>No field harvests recorded yet</p>
              <button @click="router.push('/farmer/harvests')" class="btn-xs-primary">
                <Plus size="12" />
                <span>Log Harvest</span>
              </button>
            </div>
          </div>

          <!-- Recent Customer Orders -->
          <div class="recent-card">
            <div class="card-header-row">
              <div class="flex items-center gap-2">
                <ShoppingCart size="18" class="text-emerald-500" />
                <h2>Recent Customer Orders</h2>
              </div>
              <button @click="router.push('/farmer/customer-orders')" class="view-all-link">
                <span>View All Orders</span>
                <ChevronRight size="14" />
              </button>
            </div>

            <div v-if="dashboard?.recent_orders?.length > 0" class="list-items">
              <div
                v-for="order in dashboard?.recent_orders?.slice(0, 5)"
                :key="order.id"
                class="list-item clickable-item"
                @click="router.push('/farmer/customer-orders')"
              >
                <div class="item-main">
                  <div class="item-avatar blue">
                    <ShoppingCart size="14" />
                  </div>
                  <div class="item-info">
                    <span class="item-name">{{ order.order_number || ('Order #' + order.id) }}</span>
                    <span class="item-meta">{{ order.customer_name || 'Direct Buyer' }} • {{ formatDate(order.created_at) }}</span>
                  </div>
                </div>
                <div class="item-end">
                  <span class="item-amount">ETB {{ formatNumber(order.grand_total) }}</span>
                  <span class="status-badge" :class="`status-${order.status}`">{{ order.status }}</span>
                </div>
              </div>
            </div>

            <div v-else class="empty-state">
              <ShoppingCart size="32" class="text-slate-300 dark:text-slate-600 mb-2" />
              <p>No customer orders received yet</p>
              <button @click="router.push('/farmer/customer-orders')" class="btn-xs-primary">
                <span>View Orders</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Line, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement,
  LineElement, Title, Tooltip, Legend, Filler, ArcElement
} from 'chart.js'
import { useAuthStore } from '@/stores/authStore'
import { useTheme } from '@/composables/useTheme'
import { useRouter } from 'vue-router'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import { farmerAPI } from '@/api/farmer'
import {
  Building2, Sprout, Package, ShoppingCart, Banknote, MessageSquare,
  RefreshCw, Plus, ChevronRight, ArrowUpRight, AlertCircle
} from 'lucide-vue-next'

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

// Revenue Trend Chart Config
const chartData = computed(() => {
  if (!dashboard.value?.chart_data?.labels?.length) {
    return {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      datasets: [{
        label: 'Revenue (ETB)',
        data: [0, 0, 0, 0, 0, 0, 0],
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.15)',
        fill: true,
        tension: 0.4,
      }]
    }
  }

  const raw = dashboard.value.chart_data
  return {
    labels: raw.labels,
    datasets: raw.datasets.map(ds => ({
      ...ds,
      borderColor: '#10b981',
      backgroundColor: (context) => {
        const chart = context.chart
        const { ctx, chartArea } = chart
        if (!chartArea) return 'rgba(16, 185, 129, 0.15)'
        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom)
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)')
        gradient.addColorStop(1, 'rgba(16, 185, 129, 0.01)')
        return gradient
      },
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#10b981',
      pointBorderColor: '#ffffff',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 7,
    }))
  }
})

// Crop / Produce Sales Distribution Donut
const doughnutData = computed(() => {
  if (!dashboard.value?.crop_sales?.labels?.length) {
    return {
      labels: ['General Agricultural Commodities'],
      datasets: [{
        data: [100],
        backgroundColor: ['#10b981'],
        borderWidth: 2,
      }]
    }
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
      align: 'end',
      labels: {
        color: isDark.value ? '#94a3b8' : '#64748b',
        font: { size: 12, weight: '600' },
        padding: 15,
        usePointStyle: true,
        pointStyle: 'circle'
      }
    },
    tooltip: {
      backgroundColor: isDark.value ? '#0f172a' : '#1e293b',
      titleColor: '#ffffff',
      bodyColor: '#e2e8f0',
      borderColor: isDark.value ? '#334155' : '#cbd5e1',
      borderWidth: 1,
      padding: 12,
      displayColors: false,
      callbacks: {
        label: function(context) {
          return ` Revenue: ETB ${new Intl.NumberFormat().format(context.parsed.y || 0)}`
        }
      }
    }
  },
  scales: {
    y: {
      grid: {
        color: isDark.value ? 'rgba(148, 163, 184, 0.08)' : 'rgba(0, 0, 0, 0.04)',
      },
      ticks: {
        color: isDark.value ? '#94a3b8' : '#64748b',
        font: { size: 11 },
        callback: function(value) {
          if (value >= 1000) {
            return (value / 1000) + 'k'
          }
          return value
        }
      }
    },
    x: {
      grid: { display: false },
      ticks: {
        color: isDark.value ? '#94a3b8' : '#64748b',
        font: { size: 11 },
        maxTicksLimit: 12
      }
    }
  }
}))

const doughnutOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: true,
  cutout: '68%',
  plugins: {
    legend: {
      position: 'right',
      labels: {
        color: isDark.value ? '#cbd5e1' : '#334155',
        font: { size: 12, weight: '500' },
        padding: 14,
        usePointStyle: true,
        pointStyle: 'circle'
      }
    },
    tooltip: {
      backgroundColor: isDark.value ? '#0f172a' : '#1e293b',
      titleColor: '#ffffff',
      bodyColor: '#e2e8f0',
      borderColor: isDark.value ? '#334155' : '#cbd5e1',
      borderWidth: 1,
      padding: 10,
      callbacks: {
        label: function(context) {
          return ` ${context.label}: ${context.parsed}% of output`
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
  return new Intl.NumberFormat().format(Math.round(num) || 0)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
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
  background-color: #f8fafc;
  color: #1e293b;
}

.farmer-layout.dark {
  background-color: #0b0f17;
  color: #f8fafc;
}

.farmer-dashboard {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  min-height: 100vh;
  padding: 30px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.dashboard-header-wrapper {
  background: white;
  padding: 24px 30px;
  margin: -30px -30px 24px -30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.farmer-layout.dark .dashboard-header-wrapper {
  background: #131b2e;
  border-bottom-color: #1e293b;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.title-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 4px;
}

.dashboard-header h1 {
  font-size: 24px;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.5px;
}

.farmer-layout.light .dashboard-header h1 {
  color: #0f172a;
}

.farmer-layout.dark .dashboard-header h1 {
  color: #f8fafc;
}

.dashboard-header p {
  margin: 0;
  font-size: 13px;
  color: #64748b;
}

.farmer-layout.dark .dashboard-header p {
  color: #94a3b8;
}

/* Stat Cards */
.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 28px;
}

.stat-card {
  border-radius: 12px;
  padding: 18px;
  background: white;
  border: 1px solid #e2e8f0;
  transition: all 0.25s ease;
  position: relative;
  overflow: hidden;
}

.farmer-layout.dark .stat-card {
  background: #131b2e;
  border-color: #1e293b;
}

.stat-card.clickable {
  cursor: pointer;
}

.stat-card.clickable:hover {
  transform: translateY(-3px);
  border-color: #10b981;
  box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08);
}

.farmer-layout.dark .stat-card.clickable:hover {
  box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.35);
}

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.stat-header h3 {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0;
  font-weight: 700;
  color: #64748b;
}

.farmer-layout.dark .stat-header h3 {
  color: #94a3b8;
}

.stat-icon-wrapper {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
  margin: 0 0 6px 0;
  letter-spacing: -0.5px;
  color: #0f172a;
}

.farmer-layout.dark .stat-value {
  color: #f8fafc;
}

.stat-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.stat-subtitle {
  font-size: 11px;
  color: #64748b;
  font-weight: 500;
}

.farmer-layout.dark .stat-subtitle {
  color: #94a3b8;
}

.stat-arrow {
  color: #94a3b8;
  transition: transform 0.2s ease, color 0.2s ease;
}

.stat-card:hover .stat-arrow {
  transform: translateX(3px);
  color: #10b981;
}

/* Charts Section */
.charts-section {
  margin-bottom: 28px;
}

.chart-card {
  background: white;
  border-radius: 14px;
  padding: 22px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  margin-bottom: 24px;
}

.farmer-layout.dark .chart-card {
  background: #131b2e;
  border-color: #1e293b;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  gap: 15px;
}

.chart-title-group h2 {
  font-size: 16px;
  font-weight: 700;
  margin: 0 0 3px 0;
}

.chart-subtitle {
  font-size: 12px;
  color: #64748b;
}

.farmer-layout.dark .chart-subtitle {
  color: #94a3b8;
}

.time-filter-buttons {
  display: flex;
  background: #f1f5f9;
  border-radius: 8px;
  padding: 3px;
  gap: 3px;
}

.farmer-layout.dark .time-filter-buttons {
  background: #1e293b;
}

.filter-btn {
  padding: 4px 12px;
  border-radius: 6px;
  border: none;
  background: transparent;
  font-size: 12px;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
}

.farmer-layout.dark .filter-btn {
  color: #94a3b8;
}

.filter-btn.active {
  background: white;
  color: #10b981;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.farmer-layout.dark .filter-btn.active {
  background: #0f172a;
  color: #10b981;
}

.chart-container {
  height: 280px;
  position: relative;
}

.chart-grid {
  display: grid;
  grid-template-columns: 1fr 1.3fr;
  gap: 24px;
}

@media (max-width: 1024px) {
  .chart-grid {
    grid-template-columns: 1fr;
  }
}

.doughnut-container {
  height: 240px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f5f9;
}

.farmer-layout.dark .card-header-row {
  border-bottom-color: #1e293b;
}

.card-header-row h2 {
  font-size: 15px;
  font-weight: 700;
  margin: 0;
}

.badge-pill {
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 9999px;
  background: #f1f5f9;
  color: #64748b;
}

.farmer-layout.dark .badge-pill {
  background: #1e293b;
  color: #94a3b8;
}

.view-all-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: transparent;
  border: none;
  font-size: 12px;
  font-weight: 600;
  color: #10b981;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
}

.view-all-link:hover {
  color: #059669;
  text-decoration: underline;
}

/* Transactions Table Card */
.transactions-card {
  background: white;
  border-radius: 14px;
  padding: 22px;
  border: 1px solid #e2e8f0;
}

.farmer-layout.dark .transactions-card {
  background: #131b2e;
  border-color: #1e293b;
}

.transactions-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 700;
  color: #64748b;
  text-align: left;
  padding: 10px 12px;
  border-bottom: 1px solid #e2e8f0;
}

.farmer-layout.dark th {
  color: #94a3b8;
  border-bottom-color: #1e293b;
}

td {
  padding: 12px;
  font-size: 12px;
  border-bottom: 1px solid #f8fafc;
}

.farmer-layout.dark td {
  border-bottom-color: #172033;
}

.clickable-row {
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.clickable-row:hover {
  background-color: #f8fafc;
}

.farmer-layout.dark .clickable-row:hover {
  background-color: #1a233a;
}

.id-tag {
  font-family: monospace;
  font-weight: 600;
  font-size: 11px;
  padding: 2px 6px;
  border-radius: 4px;
  background: #f1f5f9;
  color: #334155;
}

.farmer-layout.dark .id-tag {
  background: #1e293b;
  color: #cbd5e1;
}

/* Recent Section */
.recent-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

@media (max-width: 900px) {
  .recent-section {
    grid-template-columns: 1fr;
  }
}

.recent-card {
  background: white;
  border-radius: 14px;
  padding: 22px;
  border: 1px solid #e2e8f0;
}

.farmer-layout.dark .recent-card {
  background: #131b2e;
  border-color: #1e293b;
}

.list-items {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  border-radius: 10px;
  background: #f8fafc;
  border: 1px solid #f1f5f9;
  transition: all 0.2s ease;
}

.farmer-layout.dark .list-item {
  background: #0f172a;
  border-color: #1e293b;
}

.clickable-item {
  cursor: pointer;
}

.clickable-item:hover {
  border-color: #10b981;
  transform: translateX(2px);
}

.item-main {
  display: flex;
  align-items: center;
  gap: 12px;
}

.item-avatar {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.item-avatar.green {
  background: rgba(16, 185, 129, 0.12);
  color: #10b981;
}

.item-avatar.blue {
  background: rgba(59, 130, 246, 0.12);
  color: #3b82f6;
}

.item-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.item-name {
  font-size: 13px;
  font-weight: 700;
  color: #0f172a;
}

.farmer-layout.dark .item-name {
  color: #f8fafc;
}

.item-meta {
  font-size: 11px;
  color: #64748b;
}

.farmer-layout.dark .item-meta {
  color: #94a3b8;
}

.qty-badge {
  font-size: 12px;
  font-weight: 700;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 4px 10px;
  border-radius: 6px;
}

.item-end {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.item-amount {
  font-size: 13px;
  font-weight: 700;
  color: #0f172a;
}

.farmer-layout.dark .item-amount {
  color: #f8fafc;
}

.status-badge {
  font-size: 10px;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.4px;
  padding: 2px 7px;
  border-radius: 4px;
}

.status-delivered,
.status-completed {
  background: rgba(16, 185, 129, 0.12);
  color: #10b981;
}

.status-pending {
  background: rgba(245, 158, 11, 0.12);
  color: #f59e0b;
}

.status-processing,
.status-confirmed,
.status-shipped {
  background: rgba(59, 130, 246, 0.12);
  color: #3b82f6;
}

.status-cancelled,
.status-rejected {
  background: rgba(239, 68, 68, 0.12);
  color: #ef4444;
}

/* Empty States */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px;
  text-align: center;
  color: #64748b;
}

.empty-state p {
  font-size: 13px;
  margin-bottom: 10px;
}

.empty-state-mini {
  padding: 20px;
  text-align: center;
  color: #64748b;
}

.empty-state-mini p {
  margin: 0 0 8px 0;
  font-size: 12px;
}

.btn-xs-primary {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border-radius: 6px;
  background: #10b981;
  color: white;
  border: none;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-xs-primary:hover {
  background: #059669;
}

.btn-xs {
  padding: 4px 10px;
  border-radius: 6px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #334155;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
}

.loading-container {
  background: white;
  border-radius: 14px;
  padding: 60px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  border: 1px solid #e2e8f0;
}

.farmer-layout.dark .loading-container {
  background: #131b2e;
  border-color: #1e293b;
  color: #cbd5e1;
}

.spinner {
  width: 44px;
  height: 44px;
  border: 3px solid #e2e8f0;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.farmer-layout.dark .spinner {
  border-color: #1e293b;
  border-top-color: #10b981;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-container {
  background: #fff1f2;
  border: 1px solid #fecdd3;
  border-radius: 12px;
  padding: 30px;
  text-align: center;
  color: #9f1239;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.farmer-layout.dark .error-container {
  background: rgba(159, 18, 57, 0.15);
  border-color: rgba(159, 18, 57, 0.3);
  color: #fda4af;
}

.btn-retry {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 6px;
  background: #e11d48;
  color: white;
  border: none;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.btn-retry:hover {
  background: #be123c;
}
</style>
