<template>
  <div class="market-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="market-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Market Prices</h1>
          <p>Real-time commodity market benchmarks from certified agricultural exchange terminals</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading market data from database...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchMarketData" class="btn-retry">
          <RotateCcw size="16" />
          Retry Connection
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="market-content">
        <!-- Market Summary -->
        <div class="summary-grid">
          <div class="summary-card trending-up">
            <TrendingUp size="20" class="summary-icon" />
            <div>
              <span class="label">Commodities Tracked</span>
              <span class="value">{{ products.length }}</span>
            </div>
          </div>
          <div class="summary-card">
            <BarChart3 size="20" class="summary-icon" />
            <div>
              <span class="label">National Avg Benchmark</span>
              <span class="value positive">ETB {{ formatNumber(summaryStats.avgPrice) }}</span>
            </div>
          </div>
          <div class="summary-card">
            <Calendar size="20" class="summary-icon" />
            <div>
              <span class="label">Price Momentum</span>
              <span class="value text-sm">{{ summaryStats.increasing }} Up / {{ summaryStats.stable }} Stable / {{ summaryStats.decreasing }} Down</span>
            </div>
          </div>
          <div class="summary-card">
            <MapPin size="20" class="summary-icon" />
            <div>
              <span class="label">Active Market Hubs</span>
              <span class="value">{{ availableMarkets.length }} Hubs</span>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
          <input v-model="searchQuery" type="text" placeholder="Search commodity, hub or region..." class="search-input" />
          <select v-model="selectedMarket" class="filter-select">
            <option value="">All Markets</option>
            <option v-for="m in availableMarkets" :key="m" :value="m">{{ m }}</option>
          </select>
          <select v-model="sortBy" class="filter-select">
            <option value="price-desc">Highest Price</option>
            <option value="price-asc">Lowest Price</option>
            <option value="name">Name (A-Z)</option>
          </select>
        </div>

        <!-- Empty State (No records in DB) -->
        <div v-if="products.length === 0" class="empty-state-card">
          <BarChart3 size="48" class="empty-icon" />
          <h3>No Market Prices in Database</h3>
          <p>There are currently no commodity price records found in the database.</p>
          <button @click="fetchMarketData" class="btn-retry">
            <RotateCcw size="16" />
            Reload from Database
          </button>
        </div>

        <!-- No Matching Filter State -->
        <div v-else-if="filteredProducts.length === 0" class="empty-state-card">
          <AlertCircle size="48" class="empty-icon text-amber-500" />
          <h3>No Matching Commodities</h3>
          <p>No commodity prices match your search criteria. Try clearing your search filter.</p>
          <button @click="resetFilters" class="btn-retry">
            Reset Filters
          </button>
        </div>

        <!-- Price Cards -->
        <div v-else class="price-grid">
          <div v-for="product in filteredProducts" :key="product.id" class="price-card" @click="selectProduct(product)">
            <div class="card-header">
              <span class="product-name">{{ product.name }}</span>
              <span class="unit">per {{ product.unit }}</span>
            </div>

            <div class="price-display">
              <span class="current-price">ETB {{ formatNumber(product.price) }}</span>
              <span class="price-change" :class="getTrendClass(product.trend)">
                <component :is="getTrendIcon(product.trend)" size="14" />
                <span class="capitalize">{{ product.trend }}</span>
              </span>
            </div>

            <div class="price-details">
              <div class="detail">
                <span class="label">Region</span>
                <span class="value">{{ product.region || '—' }}</span>
              </div>
              <div class="detail">
                <span class="label">Market Hub</span>
                <span class="value truncate">{{ product.market }}</span>
              </div>
              <div class="detail">
                <span class="label">Date</span>
                <span class="value">{{ formatDate(product.date) }}</span>
              </div>
            </div>

            <div v-if="product.notes" class="market-status">
              <span class="label">Note</span>
              <span class="market-badge truncate">{{ product.notes }}</span>
            </div>
          </div>
        </div>

        <!-- Product Details Modal -->
        <div v-if="selectedProductDetail" class="modal-overlay" @click="selectedProductDetail = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>{{ selectedProductDetail.name }} - Price Intelligence</h2>
              <button @click="selectedProductDetail = null" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <!-- Detailed Stats -->
              <div class="stats-section">
                <h3>Terminal Benchmark Details</h3>
                <div class="stats-grid">
                  <div class="stat">
                    <span class="stat-label">Wholesale Price</span>
                    <span class="stat-value font-bold text-emerald-600">ETB {{ formatNumber(selectedProductDetail.price) }} / {{ selectedProductDetail.unit }}</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">Market Location</span>
                    <span class="stat-value">{{ selectedProductDetail.market }}</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">Region</span>
                    <span class="stat-value">{{ selectedProductDetail.region || '—' }}</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">Price Trend</span>
                    <span class="stat-value capitalize" :class="getTrendClass(selectedProductDetail.trend)">{{ selectedProductDetail.trend }}</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">Benchmark Date</span>
                    <span class="stat-value">{{ formatDate(selectedProductDetail.date) }}</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">Verification</span>
                    <span class="stat-value text-emerald-500 font-semibold">ECX Certified</span>
                  </div>
                </div>
              </div>

              <!-- Market Notes -->
              <div v-if="selectedProductDetail.notes" class="insights-section">
                <h3>Market Commentary</h3>
                <div class="insight-item insight-positive">
                  <Lightbulb size="16" />
                  <span>{{ selectedProductDetail.notes }}</span>
                </div>
              </div>

              <!-- Other Regional Markets for this Crop -->
              <div class="markets-section" v-if="relatedMarkets.length > 0">
                <h3>Prices in Other Regional Markets</h3>
                <div class="markets-list">
                  <div v-for="rel in relatedMarkets" :key="rel.id" class="market-listing">
                    <span class="market-name">{{ rel.market }} ({{ rel.region }})</span>
                    <span class="market-price">ETB {{ formatNumber(rel.price) }} / {{ rel.unit }}</span>
                    <span class="market-status live capitalize">{{ rel.trend }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedProductDetail = null" class="btn-secondary">Close</button>
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
import { useTheme } from '@/composables/useTheme'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import apiClient from '@/api/config'
import {
  TrendingUp, TrendingDown, Minus, BarChart3, AlertCircle, RotateCcw, X, MapPin, Calendar,
  Lightbulb
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()
const { isDark, isLight } = useTheme()

const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const selectedMarket = ref('')
const sortBy = ref('price-desc')
const selectedProductDetail = ref(null)

const products = ref([])

const availableMarkets = computed(() => {
  const markets = products.value.map(p => p.market).filter(Boolean)
  return Array.from(new Set(markets))
})

const summaryStats = computed(() => {
  const total = products.value.length
  if (total === 0) return { avgPrice: 0, increasing: 0, decreasing: 0, stable: 0 }
  const sum = products.value.reduce((acc, p) => acc + (parseFloat(p.price) || 0), 0)
  return {
    avgPrice: Math.round(sum / total),
    increasing: products.value.filter(p => p.trend === 'increasing').length,
    decreasing: products.value.filter(p => p.trend === 'decreasing').length,
    stable: products.value.filter(p => p.trend === 'stable').length
  }
})

const filteredProducts = computed(() => {
  let list = [...products.value]

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(p =>
      p.name.toLowerCase().includes(q) ||
      p.market.toLowerCase().includes(q) ||
      p.region.toLowerCase().includes(q) ||
      p.notes.toLowerCase().includes(q)
    )
  }

  if (selectedMarket.value) {
    list = list.filter(p => p.market.toLowerCase() === selectedMarket.value.toLowerCase())
  }

  if (sortBy.value === 'price-desc') {
    list.sort((a, b) => b.price - a.price)
  } else if (sortBy.value === 'price-asc') {
    list.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'name') {
    list.sort((a, b) => a.name.localeCompare(b.name))
  }

  return list
})

const relatedMarkets = computed(() => {
  if (!selectedProductDetail.value) return []
  const baseName = selectedProductDetail.value.name.split('(')[0].trim().toLowerCase()
  return products.value.filter(p =>
    p.id !== selectedProductDetail.value.id &&
    p.name.toLowerCase().includes(baseName)
  )
})

const fetchMarketData = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await apiClient.get('/market-prices')
    if (res?.data?.success && Array.isArray(res.data.data)) {
      products.value = res.data.data.map(item => ({
        id: item.id,
        name: item.crop_name || item.product_name,
        price: parseFloat(item.price_per_unit || item.price) || 0,
        unit: item.unit || 'kg',
        market: item.market_location || item.city || 'General Hub',
        region: item.market_region || '',
        trend: item.price_trend || 'stable',
        date: item.price_date,
        notes: item.notes || ''
      }))
    } else {
      products.value = []
    }
  } catch (err) {
    console.error('Failed to load market prices from database:', err)
    error.value = 'Failed to load market prices from database. Please check your connection and retry.'
    products.value = []
  } finally {
    loading.value = false
  }
}

const selectProduct = (product) => {
  selectedProductDetail.value = product
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedMarket.value = ''
  sortBy.value = 'price-desc'
}

const formatNumber = (num) => {
  if (!num && num !== 0) return '0'
  return Number(num).toLocaleString('en-US')
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'Recent'
  try {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
  } catch {
    return dateStr
  }
}

const getTrendIcon = (trend) => {
  if (trend === 'increasing') return TrendingUp
  if (trend === 'decreasing') return TrendingDown
  return Minus
}

const getTrendClass = (trend) => {
  if (trend === 'increasing') return 'positive'
  if (trend === 'decreasing') return 'negative'
  return 'stable'
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  fetchMarketData()
})
</script>

<style scoped>
.market-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.market-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.page-header {
  background: white;
  padding: 25px 30px;
  border-bottom: 1px solid #e5e7eb;
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
  border-top-color: #3b82f6;
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
}

.error-icon {
  color: #dc2626;
  margin-bottom: 15px;
}

.error-message {
  color: #991b1b;
  font-size: 16px;
  margin-bottom: 20px;
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
}

.market-content {
  padding: 30px;
  flex: 1;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.summary-card.trending-up {
  border-left: 4px solid #10b981;
}

.summary-icon {
  color: #3b82f6;
  flex-shrink: 0;
}

.summary-card .label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.summary-card .value {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
}

.value.positive {
  color: #10b981;
}

.filters-bar {
  background: white;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.search-input,
.filter-select {
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 13px;
  color: #4b5563;
  background: white;
  font-family: inherit;
}

.search-input {
  flex: 1;
  min-width: 200px;
}

.search-input::placeholder {
  color: #9ca3af;
}

.filter-select:hover,
.search-input:focus {
  border-color: #3b82f6;
}

.price-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

.price-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  cursor: pointer;
  transition: all 0.3s;
  border: 1px solid transparent;
}

.price-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  border-color: #3b82f6;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.product-name {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
}

.unit {
  font-size: 11px;
  color: #6b7280;
  text-transform: uppercase;
}

.price-display {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.current-price {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
}

.price-change {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
}

.price-change.positive {
  background: #ecfdf5;
  color: #10b981;
}

.price-change.negative {
  background: #fee2e2;
  color: #dc2626;
}

.price-details {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 8px;
  margin-bottom: 16px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
}

.detail {
  display: flex;
  flex-direction: column;
  gap: 2px;
  text-align: center;
}

.detail .label {
  font-size: 10px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.detail .value {
  font-size: 12px;
  font-weight: 600;
  color: #1f2937;
}

.market-status {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
}

.market-status .label {
  color: #6b7280;
}

.market-badge {
  background: #dbeafe;
  color: #1e40af;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 600;
}

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
  max-width: 700px;
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

.btn-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #6b7280;
  padding: 0;
}

.modal-body {
  padding: 24px;
}

.chart-section,
.stats-section,
.insights-section,
.recommendation-section,
.markets-section {
  margin-bottom: 24px;
}

.chart-section h3,
.stats-section h3,
.insights-section h3,
.recommendation-section h3,
.markets-section h3 {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 16px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.chart-placeholder {
  background: #f9fafb;
  border: 2px dashed #e5e7eb;
  border-radius: 8px;
  padding: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  text-align: center;
}

.chart-icon {
  color: #d1d5db;
}

.chart-placeholder p {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}

.stat {
  background: #f9fafb;
  border-radius: 8px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  font-size: 11px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.stat-value {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
}

.stat-value.positive {
  color: #10b981;
}

.insight-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 8px;
  font-size: 13px;
}

.insight-positive {
  background: #ecfdf5;
  color: #065f46;
}

.insight-positive svg {
  color: #10b981;
  flex-shrink: 0;
}

.insight-caution {
  background: #fef3c7;
  color: #92400e;
}

.insight-caution svg {
  color: #f59e0b;
  flex-shrink: 0;
}

.insight-negative {
  background: #fee2e2;
  color: #991b1b;
}

.insight-negative svg {
  color: #dc2626;
  flex-shrink: 0;
}

.recommendation-section {
  border-radius: 8px;
  padding: 16px;
}

.rec-buy {
  background: #ecfdf5;
  border-left: 4px solid #10b981;
}

.rec-buy h3 {
  color: #065f46;
}

.rec-buy p {
  color: #065f46;
  margin: 0;
}

.rec-hold {
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
}

.rec-hold h3 {
  color: #92400e;
}

.rec-hold p {
  color: #92400e;
  margin: 0;
}

.rec-sell {
  background: #fee2e2;
  border-left: 4px solid #dc2626;
}

.rec-sell h3 {
  color: #991b1b;
}

.rec-sell p {
  color: #991b1b;
  margin: 0;
}

.markets-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.market-listing {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
  border-left: 3px solid #e5e7eb;
}

.market-name {
  font-size: 13px;
  font-weight: 600;
  color: #1f2937;
  flex: 1;
}

.market-price {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin-right: 16px;
}

.market-status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  background: #f3f4f6;
  color: #6b7280;
}

.market-status.live {
  background: #ecfdf5;
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
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

@media (max-width: 768px) {
  .market-container {
    margin-left: 0;
  }

  .filters-bar {
    flex-direction: column;
  }

  .search-input {
    min-width: auto;
  }

  .price-grid {
    grid-template-columns: 1fr;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}

/* ==================== DARK MODE STYLES ==================== */

/* Layout & Background */
.market-layout.dark {
  background-color: #0b0f17;
}

.market-layout.dark .market-container {
  background-color: #0b0f17;
}

/* Page Header */
.market-layout.dark .page-header {
  background: #131b2e;
  border-bottom-color: #2d3f52;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.market-layout.dark .header-content h1 {
  color: #f1f5f9;
}

.market-layout.dark .header-content p {
  color: #cbd5e1;
}

/* Loading */
.market-layout.dark .loading-container {
  color: #cbd5e1;
}

.market-layout.dark .spinner {
  border-color: #2d3f52;
  border-top-color: #3b82f6;
}

/* Error State */
.market-layout.dark .error-container {
  background: #2e1a1a;
  border-color: #dc2626;
}

.market-layout.dark .error-icon {
  color: #f87171;
}

.market-layout.dark .error-message {
  color: #f87171;
}

.market-layout.dark .btn-retry {
  background: #dc2626;
  color: white;
}

.market-layout.dark .btn-retry:hover {
  background: #b91c1c;
}

/* Content */
.market-layout.dark .market-content {
  background-color: #0b0f17;
}

/* Summary Grid */
.market-layout.dark .summary-card {
  background: #131b2e;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.market-layout.dark .summary-card.trending-up {
  border-left-color: #10b981;
}

.market-layout.dark .summary-card .label {
  color: #94a3b8;
}

.market-layout.dark .summary-card .value {
  color: #f1f5f9;
}

.market-layout.dark .value.positive {
  color: #4ade80;
}

/* Filters Bar */
.market-layout.dark .filters-bar {
  background: #131b2e;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.market-layout.dark .search-input,
.market-layout.dark .filter-select {
  background: #1a2338;
  border-color: #2d3f52;
  color: #f1f5f9;
}

.market-layout.dark .search-input::placeholder {
  color: #64748b;
}

.market-layout.dark .search-input:focus,
.market-layout.dark .filter-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Price Cards */
.market-layout.dark .price-card {
  background: #131b2e;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  border-color: #2d3f52;
}

.market-layout.dark .price-card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
  border-color: #3b82f6;
}

.market-layout.dark .card-header {
  border-bottom-color: #2d3f52;
}

.market-layout.dark .product-name {
  color: #f1f5f9;
}

.market-layout.dark .unit {
  color: #cbd5e1;
}

.market-layout.dark .current-price {
  color: #f1f5f9;
}

.market-layout.dark .price-change.positive {
  background: #1a2e2e;
  color: #4ade80;
}

.market-layout.dark .price-change.negative {
  background: #2e1a1a;
  color: #f87171;
}

.market-layout.dark .price-details {
  background: #0f1620;
}

.market-layout.dark .detail .label {
  color: #94a3b8;
}

.market-layout.dark .detail .value {
  color: #cbd5e1;
}

.market-layout.dark .market-status {
  color: #cbd5e1;
}

.market-layout.dark .market-badge {
  background: #1a2e4d;
  color: #60a5fa;
}

/* Modal */
.market-layout.dark .modal-overlay {
  background: rgba(0, 0, 0, 0.7);
}

.market-layout.dark .modal-content {
  background: #131b2e;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.market-layout.dark .modal-header {
  background: #1a2338;
  border-bottom-color: #2d3f52;
}

.market-layout.dark .modal-header h2 {
  color: #f1f5f9;
}

.market-layout.dark .btn-close {
  color: #94a3b8;
}

.market-layout.dark .btn-close:hover {
  color: #f1f5f9;
}

/* Modal Body */
.market-layout.dark .modal-body {
  background: #131b2e;
  color: #cbd5e1;
}

.market-layout.dark .chart-section h3,
.market-layout.dark .stats-section h3,
.market-layout.dark .insights-section h3,
.market-layout.dark .recommendation-section h3,
.market-layout.dark .markets-section h3 {
  color: #f1f5f9;
}

.market-layout.dark .chart-placeholder {
  background: #0f1620;
  border-color: #2d3f52;
}

.market-layout.dark .chart-icon {
  color: #2d3f52;
}

.market-layout.dark .chart-placeholder p {
  color: #cbd5e1;
}

.market-layout.dark .stat {
  background: #0f1620;
}

.market-layout.dark .stat-label {
  color: #94a3b8;
}

.market-layout.dark .stat-value {
  color: #f1f5f9;
}

.market-layout.dark .stat-value.positive {
  color: #4ade80;
}

.market-layout.dark .insight-positive {
  background: #1a2e2e;
  color: #4ade80;
}

.market-layout.dark .insight-positive svg {
  color: #4ade80;
}

.market-layout.dark .insight-caution {
  background: #2e2a1a;
  color: #fbbf24;
}

.market-layout.dark .insight-caution svg {
  color: #fbbf24;
}

.market-layout.dark .insight-negative {
  background: #2e1a1a;
  color: #f87171;
}

.market-layout.dark .insight-negative svg {
  color: #f87171;
}

.market-layout.dark .rec-buy {
  background: #1a2e2e;
  border-left-color: #10b981;
}

.market-layout.dark .rec-buy h3 {
  color: #4ade80;
}

.market-layout.dark .rec-buy p {
  color: #cbd5e1;
}

.market-layout.dark .rec-hold {
  background: #2e2a1a;
  border-left-color: #f59e0b;
}

.market-layout.dark .rec-hold h3 {
  color: #fbbf24;
}

.market-layout.dark .rec-hold p {
  color: #cbd5e1;
}

.market-layout.dark .rec-sell {
  background: #2e1a1a;
  border-left-color: #dc2626;
}

.market-layout.dark .rec-sell h3 {
  color: #f87171;
}

.market-layout.dark .rec-sell p {
  color: #cbd5e1;
}

.market-layout.dark .market-listing {
  background: #0f1620;
  border-left-color: #2d3f52;
}

.market-layout.dark .market-name {
  color: #f1f5f9;
}

.market-layout.dark .market-price {
  color: #f1f5f9;
}

.market-layout.dark .market-status {
  background: #1a2338;
  color: #cbd5e1;
}

.market-layout.dark .market-status.live {
  background: #1a2e2e;
  color: #4ade80;
}

/* Modal Footer */
.market-layout.dark .modal-footer {
  background: #1a2338;
  border-top-color: #2d3f52;
}

.market-layout.dark .btn-primary {
  background: #3b82f6;
  color: white;
}

.market-layout.dark .btn-primary:hover {
  background: #2563eb;
}

.market-layout.dark .btn-secondary {
  background: #0f1620;
  border-color: #2d3f52;
  color: #cbd5e1;
}

.market-layout.dark .btn-secondary:hover {
  background: #1a2338;
}

.empty-state-card {
  background: white;
  border-radius: 12px;
  padding: 60px 20px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-top: 20px;
}
.empty-state-card h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin: 16px 0 8px 0;
}
.empty-state-card p {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 20px 0;
}
.empty-icon {
  color: #9ca3af;
  margin: 0 auto;
}
.market-layout.dark .empty-state-card {
  background: #1e293b;
}
.market-layout.dark .empty-state-card h3 {
  color: #f1f5f9;
}
.market-layout.dark .empty-state-card p {
  color: #94a3b8;
}

</style>
