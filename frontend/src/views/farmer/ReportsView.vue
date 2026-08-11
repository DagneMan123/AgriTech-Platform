<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <h1>Reports & Analytics</h1>
        <p>View comprehensive reports and download data for your farming business</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading reports...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-state">
        <i class="fas fa-exclamation-circle"></i>
        <p>{{ error }}</p>
        <button @click="loadReports" class="btn btn-primary">
          <i class="fas fa-sync"></i> Retry
        </button>
      </div>

      <!-- Period Selector -->
      <div class="content-section" v-if="!loading && !error">
        <div class="period-controls">
          <div class="control-group">
            <label>Select Period:</label>
            <div class="period-buttons">
              <button 
                v-for="period in ['week', 'month', 'quarter', 'year']" 
                :key="period"
                @click="selectedPeriod = period"
                :class="['period-btn', { active: selectedPeriod === period }]"
              >
                {{ period.charAt(0).toUpperCase() + period.slice(1) }}
              </button>
            </div>
          </div>
          <button @click="loadReports" class="btn btn-primary" :disabled="loading">
            <i class="fas fa-sync" :class="{ 'fa-spin': loading }"></i> Generate Report
          </button>
        </div>
      </div>

      <!-- Summary Metrics -->
      <div class="stats-grid" v-if="reportData && reportData.summary">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-cash-register"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Total Sales</div>
            <div class="stat-value">{{ formatCurrency(reportData.summary.total_sales || 0) }}</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: #dbeafe;">
            <i class="fas fa-shopping-cart" style="color: #3b82f6;"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">{{ reportData.summary.total_orders || 0 }}</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: #fef3c7;">
            <i class="fas fa-chart-bar" style="color: #f59e0b;"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Avg Order Value</div>
            <div class="stat-value">{{ formatCurrency(reportData.summary.average_order_value || 0) }}</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: #d1fae5;">
            <i class="fas fa-percent" style="color: #10b981;"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Conversion Rate</div>
            <div class="stat-value">{{ (metrics?.conversion_rate || 0).toFixed(1) }}%</div>
          </div>
        </div>
      </div>

      <!-- Top Products Report -->
      <div class="content-section" v-if="reportData && reportData.by_product">
        <h2>Top Selling Products</h2>
        <div v-if="reportData.by_product.length > 0" class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Product</th>
                <th>Quantity Sold</th>
                <th>Total Sales</th>
                <th>Orders</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in reportData.by_product.slice(0, 10)" :key="product.product_id">
                <td>{{ product.product_name || 'N/A' }}</td>
                <td>{{ product.total_quantity || 0 }}</td>
                <td>{{ formatCurrency(product.total_sales || 0) }}</td>
                <td>{{ product.order_count || 0 }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>No product sales data available for this period</p>
        </div>
      </div>

      <!-- Sales Trends -->
      <div class="content-section" v-if="reportData && reportData.by_day && reportData.by_day.length > 0">
        <h2>Daily Sales Trends</h2>
        <div class="trends-container">
          <div v-for="day in reportData.by_day.slice(-7)" :key="day.date" class="trend-card">
            <div class="trend-date">{{ formatDate(day.date) }}</div>
            <div class="trend-stat">
              <div class="trend-label">Orders</div>
              <div class="trend-value">{{ day.orders || 0 }}</div>
            </div>
            <div class="trend-stat">
              <div class="trend-label">Sales</div>
              <div class="trend-value">{{ formatCurrency(day.total_sales || 0) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Farmers/Buyers -->
      <div class="content-grid">
        <div class="content-section" v-if="reportData && reportData.by_farmer && reportData.by_farmer.length > 0">
          <h2>Top Farmers by Sales</h2>
          <div class="list-container">
            <div v-for="farmer in reportData.by_farmer.slice(0, 5)" :key="farmer.farmer_id" class="list-item">
              <div class="item-info">
                <h4>{{ farmer.farmer_name || 'N/A' }}</h4>
                <p>{{ farmer.order_count || 0 }} orders</p>
              </div>
              <div class="item-value">{{ formatCurrency(farmer.total_sales || 0) }}</div>
            </div>
          </div>
        </div>
        <div v-else-if="reportData" class="content-section">
          <h2>Top Farmers by Sales</h2>
          <div class="empty-state">
            <p>No farmer data available</p>
          </div>
        </div>

        <div class="content-section" v-if="reportData && reportData.by_buyer && reportData.by_buyer.length > 0">
          <h2>Top Buyers</h2>
          <div class="list-container">
            <div v-for="buyer in reportData.by_buyer.slice(0, 5)" :key="buyer.buyer_id" class="list-item">
              <div class="item-info">
                <h4>{{ buyer.buyer_name || 'N/A' }}</h4>
                <p>{{ buyer.order_count || 0 }} orders</p>
              </div>
              <div class="item-value">{{ formatCurrency(buyer.total_spending || 0) }}</div>
            </div>
          </div>
        </div>
        <div v-else-if="reportData" class="content-section">
          <h2>Top Buyers</h2>
          <div class="empty-state">
            <p>No buyer data available</p>
          </div>
        </div>
      </div>

      <!-- Export Options -->
      <div class="content-section">
        <h2>Export Report</h2>
        <div class="export-buttons">
          <button @click="exportReport('csv')" class="btn btn-export btn-csv">
            <i class="fas fa-file-csv"></i> Export as CSV
          </button>
          <button @click="exportReport('xlsx')" class="btn btn-export btn-xlsx">
            <i class="fas fa-file-excel"></i> Export as Excel
          </button>
          <button @click="exportReport('pdf')" class="btn btn-export btn-pdf">
            <i class="fas fa-file-pdf"></i> Export as PDF
          </button>
        </div>
      </div>

      <!-- Performance Metrics -->
      <div class="content-section" v-if="metrics">
        <h2>Performance Metrics</h2>
        <div class="metrics-grid">
          <div class="metric-item">
            <div class="metric-label">Total Sales</div>
            <div class="metric-value">{{ formatCurrency(metrics.total_sales || 0) }}</div>
          </div>
          <div class="metric-item">
            <div class="metric-label">Total Orders</div>
            <div class="metric-value">{{ metrics.total_orders || 0 }}</div>
          </div>
          <div class="metric-item">
            <div class="metric-label">Avg Order Value</div>
            <div class="metric-value">{{ formatCurrency(metrics.avg_order_value || 0) }}</div>
          </div>
          <div class="metric-item">
            <div class="metric-label">Order Fulfillment</div>
            <div class="metric-value">{{ (metrics.order_fulfillment_rate || 0).toFixed(1) }}%</div>
          </div>
          <div class="metric-item">
            <div class="metric-label">Repeat Customer Rate</div>
            <div class="metric-value">{{ (metrics.repeat_customer_rate || 0).toFixed(1) }}%</div>
          </div>
          <div class="metric-item">
            <div class="metric-label">Conversion Rate</div>
            <div class="metric-value">{{ (metrics.conversion_rate || 0).toFixed(1) }}%</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()

// State
const selectedPeriod = ref('month')
const loading = ref(false)
const reportData = ref(null)
const metrics = ref(null)
const error = ref(null)

// API base
const API_BASE = 'http://localhost:8000/api'

// Lifecycle
onMounted(() => {
  loadReports()
})

// Load reports from API
const loadReports = async () => {
  loading.value = true
  try {
    const response = await fetch(
      `${API_BASE}/farmer/dashboard/sales-reports?period=${selectedPeriod.value}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${auth.token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    )

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    
    if (data.success && data.data) {
      reportData.value = data.data
      console.log('Reports loaded successfully:', reportData.value)
      
      // Also fetch performance metrics
      await fetchMetrics()
    } else {
      console.warn('Invalid response format:', data)
      reportData.value = null
    }
  } catch (err) {
    console.error('Error loading reports:', err)
    reportData.value = null
    alert(`Failed to load reports: ${err.message}`)
  } finally {
    loading.value = false
  }
}

// Fetch performance metrics
const fetchMetrics = async () => {
  try {
    const response = await fetch(
      `${API_BASE}/farmer/dashboard/performance-metrics?period=${selectedPeriod.value}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${auth.token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    )

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    
    if (data.success && data.data) {
      metrics.value = data.data
      console.log('Metrics loaded successfully:', metrics.value)
    } else {
      console.warn('Invalid metrics response:', data)
      metrics.value = null
    }
  } catch (err) {
    console.error('Error fetching metrics:', err)
    metrics.value = null
  }
}

// Format currency
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-ET', {
    style: 'currency',
    currency: 'ETB',
    minimumFractionDigits: 0
  }).format(amount || 0)
}

// Format date
const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

// Export report
const exportReport = async (format) => {
  try {
    const response = await fetch(
      `${API_BASE}/farmer/dashboard/export-report?format=${format}&period=${selectedPeriod.value}`,
      {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${auth.token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    )

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    
    if (data.success) {
      console.log('Report exported:', data)
      alert(`✓ Report exported as ${format.toUpperCase()}`)
      
      // In production, trigger actual download
      if (data.data && data.data.download_link) {
        // window.open(data.data.download_link, '_blank')
      }
    } else {
      throw new Error(data.message || 'Export failed')
    }
  } catch (err) {
    console.error('Error exporting report:', err)
    alert(`✗ Failed to export report: ${err.message}`)
  }
}

// Logout handler
const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout { display: flex; height: 100vh; }
.farmer-page { 
  margin-left: 260px; 
  flex: 1; 
  overflow-y: auto; 
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); 
  padding: 30px; 
}
.page-header { 
  margin-bottom: 40px; 
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2);
  color: white;
}
.page-header h1 { 
  font-size: 32px; 
  font-weight: 700; 
  color: white; 
  margin-bottom: 8px;
  letter-spacing: -0.5px;
}
.page-header p { 
  color: rgba(255, 255, 255, 0.9); 
  font-size: 15px;
  font-weight: 500;
  letter-spacing: 0.3px;
}

/* Content Section */
.content-section {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  margin-bottom: 25px;
  border: 1px solid rgba(16, 185, 129, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.content-section:hover {
  box-shadow: 0 15px 40px rgba(16, 185, 129, 0.12);
  transform: translateY(-2px);
}

.content-section h2 {
  font-size: 22px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 25px;
  letter-spacing: -0.3px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Period Controls */
.period-controls {
  display: flex;
  gap: 25px;
  align-items: center;
  flex-wrap: wrap;
  padding-bottom: 25px;
  border-bottom: 2px solid #e5e7eb;
}

.control-group {
  display: flex;
  align-items: center;
  gap: 20px;
}

.control-group label {
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  font-size: 15px;
  letter-spacing: 0.3px;
}

.period-buttons {
  display: flex;
  gap: 12px;
}

.period-btn {
  padding: 10px 20px;
  background-color: white;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  color: #6b7280;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  letter-spacing: 0.2px;
}

.period-btn:hover {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border-color: transparent;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
}

.period-btn.active {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border-color: transparent;
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  gap: 10px;
  letter-spacing: 0.2px;
  text-transform: uppercase;
}

.btn-primary {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 8px 15px rgba(16, 185, 129, 0.2);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 12px 25px rgba(16, 185, 129, 0.35);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  display: flex;
  align-items: center;
  gap: 18px;
  border: 2px solid #f0fdf4;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08);
}

.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 35px rgba(16, 185, 129, 0.15);
  border-color: rgba(16, 185, 129, 0.3);
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 10px;
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: #10b981;
  flex-shrink: 0;
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15);
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 13px;
  color: #9ca3af;
  margin-bottom: 6px;
  font-weight: 700;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}

.stat-value {
  font-size: 26px;
  font-weight: 700;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Table */
.table-container {
  overflow-x: auto;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.data-table th {
  background: linear-gradient(135deg, #f0fdf4 0%, #f0f9ff 100%);
  padding: 16px;
  text-align: left;
  font-weight: 700;
  color: #10b981;
  border-bottom: 3px solid #d1fae5;
  font-size: 13px;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}

.data-table td {
  padding: 15px 16px;
  border-bottom: 1px solid #f0fdf4;
  color: #4b5563;
  font-weight: 500;
}

.data-table tbody tr {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.data-table tbody tr:hover {
  background: linear-gradient(90deg, #f0fdf4 0%, white 100%);
  box-shadow: inset 0 0 10px rgba(16, 185, 129, 0.08);
}

/* Trends */
.trends-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 15px;
}

.trend-card {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.trend-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 15px 35px rgba(16, 185, 129, 0.3);
}

.trend-date {
  font-size: 13px;
  opacity: 0.9;
  margin-bottom: 15px;
  font-weight: 700;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}

.trend-stat {
  margin-bottom: 12px;
}

.trend-label {
  font-size: 12px;
  opacity: 0.85;
  font-weight: 600;
  letter-spacing: 0.2px;
}

.trend-value {
  font-size: 20px;
  font-weight: 700;
  margin-top: 4px;
}

/* Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 25px;
}

/* List Container */
.list-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  border-radius: 10px;
  border: 2px solid #e5e7eb;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.list-item:hover {
  background: white;
  border-color: #10b981;
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.12);
  transform: translateX(4px);
}

.item-info h4 {
  margin: 0 0 6px 0;
  color: #1f2937;
  font-size: 15px;
  font-weight: 700;
  letter-spacing: -0.2px;
}

.item-info p {
  margin: 0;
  font-size: 13px;
  color: #9ca3af;
  font-weight: 500;
}

.item-value {
  font-size: 18px;
  font-weight: 700;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Export Buttons */
.export-buttons {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.btn-export {
  background: white;
  color: #333;
  border: 2px solid #e5e7eb;
  padding: 14px 24px;
  font-size: 14px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  letter-spacing: 0.2px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-export:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.btn-csv {
  border-color: #16a34a;
  color: #16a34a;
}

.btn-csv:hover {
  background: linear-gradient(135deg, #dcfce7 0%, #f0fdf4 100%);
  border-color: transparent;
}

.btn-xlsx {
  border-color: #2563eb;
  color: #2563eb;
}

.btn-xlsx:hover {
  background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
  border-color: transparent;
}

.btn-pdf {
  border-color: #dc2626;
  color: #dc2626;
}

.btn-pdf:hover {
  background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%);
  border-color: transparent;
}

/* Metrics Grid */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 18px;
}

.metric-item {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border: 2px solid #bae6fd;
  border-radius: 12px;
  padding: 25px;
  text-align: center;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 8px 20px rgba(3, 102, 214, 0.1);
}

.metric-item:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 35px rgba(3, 102, 214, 0.15);
  border-color: #3b82f6;
}

.metric-label {
  font-size: 13px;
  color: #0369a1;
  font-weight: 700;
  margin-bottom: 10px;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}

.metric-value {
  font-size: 22px;
  font-weight: 700;
  color: #0c4a6e;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 40px;
  color: #9ca3af;
}

.empty-state i {
  font-size: 60px;
  color: #e5e7eb;
  margin-bottom: 20px;
  display: inline-block;
}

.empty-state p {
  font-size: 16px;
  font-weight: 500;
  letter-spacing: 0.2px;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 80px 40px;
  color: #10b981;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e5e7eb;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  font-size: 16px;
  font-weight: 600;
  letter-spacing: 0.2px;
}

/* Error State */
.error-state {
  text-align: center;
  padding: 60px 40px;
  background: linear-gradient(135deg, #fee2e2 0%, #fef2f2 100%);
  border: 2px solid #fca5a5;
  border-radius: 12px;
  margin: 20px;
}

.error-state i {
  font-size: 48px;
  color: #dc2626;
  margin-bottom: 15px;
  display: inline-block;
}

.error-state p {
  font-size: 15px;
  color: #991b1b;
  font-weight: 600;
  margin-bottom: 20px;
  letter-spacing: 0.2px;
}

/* Responsive */
@media (max-width: 1200px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .content-grid { grid-template-columns: 1fr; }
  .metrics-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
  .farmer-page { 
    margin-left: 0; 
    padding: 20px;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  }
  .page-header { 
    padding: 20px;
    margin-bottom: 25px;
  }
  .page-header h1 { font-size: 24px; }
  .stats-grid { grid-template-columns: 1fr; }
  .period-controls { 
    flex-direction: column; 
    align-items: flex-start;
    gap: 15px;
  }
  .trends-container { grid-template-columns: repeat(2, 1fr); }
  .metrics-grid { grid-template-columns: 1fr; }
  .data-table { font-size: 12px; }
  .export-buttons { flex-direction: column; }
  .export-buttons .btn { width: 100%; }
  .content-grid { grid-template-columns: 1fr; }
}

@media (max-width: 480px) {
  .trends-container { grid-template-columns: 1fr; }
  .period-buttons { flex-direction: column; width: 100%; }
  .period-btn { width: 100%; }
  .page-header h1 { font-size: 20px; }
  .content-section { padding: 20px; }
  .stat-card { flex-direction: column; text-align: center; }
}
</style>
