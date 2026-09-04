<template>
  <div class="sales-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="sales-container">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h1>My Sales</h1>
          <p>Track and manage your product sales and revenue</p>
        </div>
        <div class="header-actions">
          <button @click="openFilters" class="btn-filter">
            <Sliders size="16" />
            <span>Filters</span>
          </button>
          <button @click="exportData" class="btn-export">
            <Download size="16" />
            <span>Export</span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading sales data...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchSalesData" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="sales-content">
        <!-- Summary Cards -->
        <div class="summary-section">
          <div class="summary-card">
            <div class="card-header">
              <h3>Total Revenue</h3>
              <DollarSign size="20" class="card-icon revenue-icon" />
            </div>
            <p class="card-value">${{ formatNumber(summaryData.totalRevenue) }}</p>
            <p class="card-trend" :class="summaryData.revenueGrowth >= 0 ? 'positive' : 'negative'">
              <TrendingUp v-if="summaryData.revenueGrowth >= 0" size="14" />
              <TrendingDown v-else size="14" />
              {{ Math.abs(summaryData.revenueGrowth) }}% vs last month
            </p>
          </div>

          <div class="summary-card">
            <div class="card-header">
              <h3>Total Sales</h3>
              <ShoppingCart size="20" class="card-icon sales-icon" />
            </div>
            <p class="card-value">{{ summaryData.totalSales }}</p>
            <p class="card-subtitle">orders completed</p>
          </div>

          <div class="summary-card">
            <div class="card-header">
              <h3>Average Order</h3>
              <BarChart3 size="20" class="card-icon average-icon" />
            </div>
            <p class="card-value">${{ formatNumber(summaryData.averageOrder) }}</p>
            <p class="card-subtitle">value per order</p>
          </div>

          <div class="summary-card">
            <div class="card-header">
              <h3>Pending Orders</h3>
              <Clock size="20" class="card-icon pending-icon" />
            </div>
            <p class="card-value">{{ summaryData.pendingOrders }}</p>
            <p class="card-subtitle">awaiting fulfillment</p>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
          <div class="chart-card">
            <h3 class="chart-title">Revenue Trend (Last 12 Months)</h3>
            <div class="chart-placeholder">
              <p>Chart visualization will be displayed here</p>
            </div>
          </div>

          <div class="chart-card">
            <h3 class="chart-title">Sales by Product</h3>
            <div class="chart-placeholder">
              <p>Product breakdown will be displayed here</p>
            </div>
          </div>
        </div>

        <!-- Filters Section -->
        <div v-if="showFilters" class="filters-panel">
          <div class="filters-header">
            <h3>Filters</h3>
            <button @click="closeFilters" class="btn-close">
              <X size="20" />
            </button>
          </div>
          
          <div class="filters-content">
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
              <label>Product</label>
              <select v-model="filters.product" class="filter-select">
                <option value="">All Products</option>
                <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                  {{ product.name }}
                </option>
              </select>
            </div>

            <div class="filter-group">
              <label>Status</label>
              <select v-model="filters.status" class="filter-select">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div class="filter-actions">
              <button @click="applyFilters" class="btn-apply">Apply Filters</button>
              <button @click="clearFilters" class="btn-clear">Clear</button>
            </div>
          </div>
        </div>

        <!-- Sales Table -->
        <div class="table-section">
          <div class="table-header">
            <h3>Recent Sales</h3>
            <div class="table-controls">
              <select v-model="itemsPerPage" class="items-select">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
              </select>
            </div>
          </div>

          <div v-if="filteredSales.length > 0" class="table-wrapper">
            <table class="sales-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Amount</th>
                  <th>Buyer</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sale in paginatedSales" :key="sale.id" class="table-row">
                  <td class="td-id">
                    <span class="order-badge">{{ sale.id }}</span>
                  </td>
                  <td class="td-product">{{ sale.productName }}</td>
                  <td class="td-quantity">{{ sale.quantity }} {{ sale.unit }}</td>
                  <td class="td-price">${{ formatNumber(sale.pricePerUnit) }}</td>
                  <td class="td-amount">
                    <span class="amount-badge">${{ formatNumber(sale.totalAmount) }}</span>
                  </td>
                  <td class="td-buyer">{{ sale.buyerName }}</td>
                  <td class="td-date">{{ formatDate(sale.saleDate) }}</td>
                  <td class="td-status">
                    <span class="status-badge" :class="`status-${sale.status}`">
                      {{ capitalize(sale.status) }}
                    </span>
                  </td>
                  <td class="td-actions">
                    <button @click="viewDetails(sale)" class="action-btn view-btn" title="View Details">
                      <Eye size="16" />
                    </button>
                    <button @click="downloadInvoice(sale)" class="action-btn download-btn" title="Download Invoice">
                      <FileDown size="16" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="empty-state">
            <ShoppingCart size="48" class="empty-icon" />
            <p>No sales found</p>
            <span class="empty-hint">Your sales will appear here once you make them</span>
          </div>

          <!-- Pagination -->
          <div v-if="filteredSales.length > 0" class="pagination">
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
        <div v-if="selectedSale" class="modal-overlay" @click="selectedSale = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Order Details</h2>
              <button @click="selectedSale = null" class="btn-close">
                <X size="20" />
              </button>
            </div>

            <div class="modal-body">
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="detail-label">Order ID</span>
                  <span class="detail-value">{{ selectedSale.id }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Product</span>
                  <span class="detail-value">{{ selectedSale.productName }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Quantity</span>
                  <span class="detail-value">{{ selectedSale.quantity }} {{ selectedSale.unit }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Price per Unit</span>
                  <span class="detail-value">${{ formatNumber(selectedSale.pricePerUnit) }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Total Amount</span>
                  <span class="detail-value detail-amount">${{ formatNumber(selectedSale.totalAmount) }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Status</span>
                  <span class="detail-value">
                    <span class="status-badge" :class="`status-${selectedSale.status}`">
                      {{ capitalize(selectedSale.status) }}
                    </span>
                  </span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Buyer</span>
                  <span class="detail-value">{{ selectedSale.buyerName }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Sale Date</span>
                  <span class="detail-value">{{ formatDate(selectedSale.saleDate) }}</span>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedSale = null" class="btn-secondary">Close</button>
              <button @click="downloadInvoice(selectedSale)" class="btn-primary">
                <Download size="16" />
                Download Invoice
              </button>
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
import {
  TrendingUp, TrendingDown, DollarSign, ShoppingCart, BarChart3, Clock,
  AlertCircle, RotateCcw, Sliders, Download, X, Eye, FileDown, ChevronLeft,
  ChevronRight
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

// State
const loading = ref(false)
const error = ref(null)
const showFilters = ref(false)
const selectedSale = ref(null)
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Data
const sales = ref([])
const availableProducts = ref([])
const summaryData = ref({
  totalRevenue: 0,
  totalSales: 0,
  averageOrder: 0,
  pendingOrders: 0,
  revenueGrowth: 0
})

// Filters
const filters = ref({
  dateRange: 'all',
  product: '',
  status: ''
})

// Mock data - replace with API calls
const mockSalesData = [
  {
    id: 'ORD-001',
    productName: 'Tomatoes (Fresh)',
    quantity: 50,
    unit: 'kg',
    pricePerUnit: 2.50,
    totalAmount: 125.00,
    buyerName: 'Fresh Market Ltd',
    saleDate: '2026-09-01',
    status: 'completed'
  },
  {
    id: 'ORD-002',
    productName: 'Organic Carrots',
    quantity: 100,
    unit: 'kg',
    pricePerUnit: 1.80,
    totalAmount: 180.00,
    buyerName: 'Green Grocery Store',
    saleDate: '2026-09-02',
    status: 'completed'
  },
  {
    id: 'ORD-003',
    productName: 'Maize (Corn)',
    quantity: 200,
    unit: 'kg',
    pricePerUnit: 0.50,
    totalAmount: 100.00,
    buyerName: 'Agricultural Cooperative',
    saleDate: '2026-09-03',
    status: 'pending'
  },
  {
    id: 'ORD-004',
    productName: 'Tomatoes (Fresh)',
    quantity: 75,
    unit: 'kg',
    pricePerUnit: 2.50,
    totalAmount: 187.50,
    buyerName: 'Restaurant Supply Co',
    saleDate: '2026-09-04',
    status: 'completed'
  },
  {
    id: 'ORD-005',
    productName: 'Potatoes',
    quantity: 150,
    unit: 'kg',
    pricePerUnit: 1.20,
    totalAmount: 180.00,
    buyerName: 'Food Processing Plant',
    saleDate: '2026-09-05',
    status: 'completed'
  }
]

const mockProducts = [
  { id: 1, name: 'Tomatoes (Fresh)' },
  { id: 2, name: 'Organic Carrots' },
  { id: 3, name: 'Maize (Corn)' },
  { id: 4, name: 'Potatoes' }
]

// Computed
const filteredSales = computed(() => {
  let result = sales.value

  if (filters.value.status) {
    result = result.filter(s => s.status === filters.value.status)
  }

  if (filters.value.product) {
    result = result.filter(s => s.productName === filters.value.product)
  }

  return result
})

const totalPages = computed(() => {
  return Math.ceil(filteredSales.value.length / parseInt(itemsPerPage.value))
})

const paginatedSales = computed(() => {
  const start = (currentPage.value - 1) * parseInt(itemsPerPage.value)
  const end = start + parseInt(itemsPerPage.value)
  return filteredSales.value.slice(start, end)
})

// Methods
const fetchSalesData = async () => {
  loading.value = true
  error.value = null
  try {
    // Replace with actual API call
    // const res = await farmerAPI.getSales()
    sales.value = mockSalesData
    availableProducts.value = mockProducts
    
    // Calculate summary data
    const completedSales = sales.value.filter(s => s.status === 'completed')
    summaryData.value = {
      totalRevenue: completedSales.reduce((sum, s) => sum + s.totalAmount, 0),
      totalSales: completedSales.length,
      averageOrder: completedSales.length > 0 
        ? completedSales.reduce((sum, s) => sum + s.totalAmount, 0) / completedSales.length
        : 0,
      pendingOrders: sales.value.filter(s => s.status === 'pending').length,
      revenueGrowth: 12.5 // Mock growth percentage
    }
  } catch (err) {
    console.error('Error fetching sales data:', err)
    error.value = 'Failed to load sales data. Please try again.'
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

const openFilters = () => {
  showFilters.value = true
}

const closeFilters = () => {
  showFilters.value = false
}

const applyFilters = () => {
  currentPage.value = 1
  closeFilters()
}

const clearFilters = () => {
  filters.value = {
    dateRange: 'all',
    product: '',
    status: ''
  }
}

const exportData = () => {
  // Implement CSV export
  const headers = ['Order ID', 'Product', 'Quantity', 'Price', 'Amount', 'Buyer', 'Date', 'Status']
  const rows = filteredSales.value.map(sale => [
    sale.id,
    sale.productName,
    `${sale.quantity} ${sale.unit}`,
    `$${formatNumber(sale.pricePerUnit)}`,
    `$${formatNumber(sale.totalAmount)}`,
    sale.buyerName,
    formatDate(sale.saleDate),
    sale.status
  ])

  const csv = [headers, ...rows].map(row => row.join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `sales-report-${new Date().toISOString().split('T')[0]}.csv`
  a.click()
}

const viewDetails = (sale) => {
  selectedSale.value = sale
}

const downloadInvoice = (sale) => {
  // Implement invoice download
  alert(`Downloading invoice for Order ${sale.id}`)
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  fetchSalesData()
})
</script>

<style scoped>
.sales-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.sales-container {
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
.sales-content {
  padding: 30px;
  flex: 1;
}

/* Summary Section */
.summary-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s;
  border-left: 5px solid #10b981;
}

.summary-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
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

.summary-card:nth-child(2) {
  border-left-color: #3b82f6;
}

.summary-card:nth-child(3) {
  border-left-color: #8b5cf6;
}

.summary-card:nth-child(4) {
  border-left-color: #f59e0b;
}

.card-value {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 8px 0 4px 0;
}

.card-subtitle,
.card-trend {
  font-size: 12px;
  color: #9ca3af;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.card-trend.positive {
  color: #10b981;
}

.card-trend.negative {
  color: #ef4444;
}

/* Charts Section */
.charts-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.chart-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.chart-title {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 16px 0;
}

.chart-placeholder {
  height: 300px;
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  font-size: 14px;
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
  display: flex;
  align-items: center;
  justify-content: center;
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

.sales-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.sales-table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.sales-table th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 12px;
}

.sales-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
  transition: background 0.2s;
}

.sales-table tbody tr:hover {
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

.status-completed {
  background: #d1fae5;
  color: #065f46;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-cancelled {
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

.download-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
  background: #eff6ff;
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
  max-width: 600px;
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

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
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
  font-weight: 600;
}

.detail-amount {
  font-size: 18px;
  color: #10b981;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-primary,
.btn-secondary {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #10b981;
  color: white;
}

.btn-primary:hover {
  background: #059669;
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
  .sales-container {
    margin-left: 0;
  }

  .charts-section {
    grid-template-columns: 1fr;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .sales-content {
    padding: 20px;
  }

  .summary-section {
    grid-template-columns: repeat(2, 1fr);
  }

  .sales-table {
    font-size: 12px;
  }

  .sales-table th,
  .table-row td {
    padding: 12px 8px;
  }

  .td-actions {
    flex-direction: column;
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
</style>
