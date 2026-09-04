<template>
  <div class="market-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="market-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Market Prices</h1>
          <p>Track real-time commodity prices and market trends</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading market data...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchMarketData" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="market-content">
        <!-- Market Summary -->
        <div class="summary-grid">
          <div class="summary-card trending-up">
            <TrendingUp size="20" class="summary-icon" />
            <div>
              <span class="label">Market Trend</span>
              <span class="value">Bullish</span>
            </div>
          </div>
          <div class="summary-card">
            <BarChart3 size="20" class="summary-icon" />
            <div>
              <span class="label">Avg Price Change</span>
              <span class="value positive">+2.5%</span>
            </div>
          </div>
          <div class="summary-card">
            <Calendar size="20" class="summary-icon" />
            <div>
              <span class="label">Last Updated</span>
              <span class="value">2 hours ago</span>
            </div>
          </div>
          <div class="summary-card">
            <MapPin size="20" class="summary-icon" />
            <div>
              <span class="label">Selected Market</span>
              <span class="value">Central Region</span>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="filters-bar">
          <input v-model="searchQuery" type="text" placeholder="Search products..." class="search-input" />
          <select v-model="selectedMarket" class="filter-select">
            <option value="">All Markets</option>
            <option value="central">Central Region</option>
            <option value="north">Northern Region</option>
            <option value="south">Southern Region</option>
          </select>
          <select v-model="sortBy" class="filter-select">
            <option value="price-desc">Highest Price</option>
            <option value="price-asc">Lowest Price</option>
            <option value="change">Price Change</option>
          </select>
        </div>

        <!-- Price Cards -->
        <div class="price-grid">
          <div v-for="product in filteredProducts" :key="product.id" class="price-card" @click="selectProduct(product)">
            <div class="card-header">
              <span class="product-name">{{ product.name }}</span>
              <span class="unit">per {{ product.unit }}</span>
            </div>

            <div class="price-display">
              <span class="current-price">${{ product.price }}</span>
              <span class="price-change" :class="{ positive: product.change > 0, negative: product.change < 0 }">
                <component :is="product.change > 0 ? TrendingUp : TrendingDown" size="14" />
                {{ Math.abs(product.change) }}%
              </span>
            </div>

            <div class="price-details">
              <div class="detail">
                <span class="label">Previous</span>
                <span class="value">${{ product.previousPrice }}</span>
              </div>
              <div class="detail">
                <span class="label">High</span>
                <span class="value">${{ product.high }}</span>
              </div>
              <div class="detail">
                <span class="label">Low</span>
                <span class="value">${{ product.low }}</span>
              </div>
            </div>

            <div class="market-status">
              <span class="label">Market</span>
              <span class="market-badge">{{ product.market }}</span>
            </div>
          </div>
        </div>

        <!-- Product Details Modal -->
        <div v-if="selectedProductDetail" class="modal-overlay" @click="selectedProductDetail = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>{{ selectedProductDetail.name }} - Price Analysis</h2>
              <button @click="selectedProductDetail = null" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <!-- Price Chart -->
              <div class="chart-section">
                <h3>7-Day Price Trend</h3>
                <div class="chart-placeholder">
                  <BarChart3 size="64" class="chart-icon" />
                  <p>Interactive price chart</p>
                </div>
              </div>

              <!-- Detailed Stats -->
              <div class="stats-section">
                <h3>Detailed Statistics</h3>
                <div class="stats-grid">
                  <div class="stat">
                    <span class="stat-label">Current Price</span>
                    <span class="stat-value">${{ selectedProductDetail.price }}</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">24h Change</span>
                    <span class="stat-value" :class="{ positive: selectedProductDetail.change > 0 }">
                      {{ selectedProductDetail.change > 0 ? '+' : '' }}{{ selectedProductDetail.change }}%
                    </span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">52-Week High</span>
                    <span class="stat-value">${{ selectedProductDetail.high }}</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">52-Week Low</span>
                    <span class="stat-value">${{ selectedProductDetail.low }}</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">Market Cap</span>
                    <span class="stat-value">${{ selectedProductDetail.marketCap }}M</span>
                  </div>
                  <div class="stat">
                    <span class="stat-label">Volume</span>
                    <span class="stat-value">{{ selectedProductDetail.volume }}</span>
                  </div>
                </div>
              </div>

              <!-- Market Insights -->
              <div class="insights-section">
                <h3>Market Insights</h3>
                <div v-for="insight in selectedProductDetail.insights" :key="insight.id" class="insight-item" :class="`insight-${insight.type}`">
                  <Lightbulb size="16" />
                  <span>{{ insight.text }}</span>
                </div>
              </div>

              <!-- Trading Recommendation -->
              <div class="recommendation-section" :class="`rec-${selectedProductDetail.recommendation}`">
                <h3>Market Recommendation</h3>
                <p>{{ selectedProductDetail.recommendationText }}</p>
              </div>

              <!-- Markets Listing -->
              <div class="markets-section">
                <h3>Available in Markets</h3>
                <div class="markets-list">
                  <div v-for="market in selectedProductDetail.markets" :key="market.id" class="market-listing">
                    <span class="market-name">{{ market.name }}</span>
                    <span class="market-price">${{ market.price }}</span>
                    <span class="market-status" :class="{ live: market.status === 'live' }">{{ market.status }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedProductDetail = null" class="btn-secondary">Close</button>
              <button class="btn-primary">
                <Bell size="16" />
                Set Price Alert
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
  TrendingUp, TrendingDown, BarChart3, AlertCircle, RotateCcw, X, MapPin, Calendar,
  Lightbulb, Bell
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const selectedMarket = ref('')
const sortBy = ref('price-desc')
const selectedProductDetail = ref(null)

const mockProducts = ref([
  {
    id: 1,
    name: 'Tomatoes',
    price: 45.50,
    previousPrice: 44.25,
    change: 2.8,
    high: 48.75,
    low: 42.10,
    unit: 'kg',
    market: 'Central Region',
    marketCap: 125,
    volume: '2.5K tons',
    insights: [
      { id: 1, type: 'positive', text: 'Demand increasing due to summer season' },
      { id: 2, type: 'caution', text: 'Supply slightly reducing, prices expected to hold steady' }
    ],
    recommendation: 'buy',
    recommendationText: 'Good buying opportunity. Prices are expected to rise further due to seasonal demand.',
    markets: [
      { id: 1, name: 'Central Region Market', price: 45.50, status: 'live' },
      { id: 2, name: 'Northern Region Market', price: 44.75, status: 'live' },
      { id: 3, name: 'Southern Region Market', price: 46.25, status: 'live' }
    ]
  },
  {
    id: 2,
    name: 'Potatoes',
    price: 28.75,
    previousPrice: 30.10,
    change: -4.5,
    high: 32.50,
    low: 26.25,
    unit: 'kg',
    market: 'Central Region',
    marketCap: 98,
    volume: '3.1K tons',
    insights: [
      { id: 1, type: 'caution', text: 'Oversupply in the market currently' },
      { id: 2, type: 'negative', text: 'Prices declining week-over-week' }
    ],
    recommendation: 'hold',
    recommendationText: 'Wait for stabilization. Market currently oversupplied, avoid selling now.',
    markets: [
      { id: 1, name: 'Central Region Market', price: 28.75, status: 'live' },
      { id: 2, name: 'Northern Region Market', price: 29.10, status: 'live' },
      { id: 3, name: 'Southern Region Market', price: 27.95, status: 'live' }
    ]
  },
  {
    id: 3,
    name: 'Corn',
    price: 36.20,
    previousPrice: 35.80,
    change: 1.1,
    high: 39.50,
    low: 34.10,
    unit: 'kg',
    market: 'Central Region',
    marketCap: 156,
    volume: '4.2K tons',
    insights: [
      { id: 1, type: 'positive', text: 'Stable demand from processors' },
      { id: 2, type: 'positive', text: 'Export demand remains strong' }
    ],
    recommendation: 'buy',
    recommendationText: 'Steady market with good fundamentals. Consider buying for export opportunities.',
    markets: [
      { id: 1, name: 'Central Region Market', price: 36.20, status: 'live' },
      { id: 2, name: 'Northern Region Market', price: 35.95, status: 'live' },
      { id: 3, name: 'Southern Region Market', price: 36.45, status: 'live' }
    ]
  },
  {
    id: 4,
    name: 'Lettuce',
    price: 52.10,
    previousPrice: 48.75,
    change: 6.9,
    high: 54.50,
    low: 45.20,
    unit: 'kg',
    market: 'Central Region',
    marketCap: 67,
    volume: '1.8K tons',
    insights: [
      { id: 1, type: 'positive', text: 'Strong retail demand for fresh produce' },
      { id: 2, type: 'positive', text: 'Limited supply pushing prices up' }
    ],
    recommendation: 'buy',
    recommendationText: 'Excellent market conditions. Sell at current high prices or hold for further gains.',
    markets: [
      { id: 1, name: 'Central Region Market', price: 52.10, status: 'live' },
      { id: 2, name: 'Northern Region Market', price: 51.50, status: 'live' },
      { id: 3, name: 'Southern Region Market', price: 52.75, status: 'live' }
    ]
  },
  {
    id: 5,
    name: 'Wheat',
    price: 31.45,
    previousPrice: 31.55,
    change: -0.3,
    high: 34.20,
    low: 29.10,
    unit: 'kg',
    market: 'Central Region',
    marketCap: 198,
    volume: '5.6K tons',
    insights: [
      { id: 1, type: 'caution', text: 'Slightly lower global demand this quarter' },
      { id: 2, type: 'positive', text: 'Price support from export contracts' }
    ],
    recommendation: 'hold',
    recommendationText: 'Stable with slight weakness. Hold current positions and monitor global trends.',
    markets: [
      { id: 1, name: 'Central Region Market', price: 31.45, status: 'live' },
      { id: 2, name: 'Northern Region Market', price: 31.70, status: 'live' },
      { id: 3, name: 'Southern Region Market', price: 31.20, status: 'live' }
    ]
  },
  {
    id: 6,
    name: 'Beans',
    price: 42.80,
    previousPrice: 40.50,
    change: 5.7,
    high: 43.50,
    low: 38.75,
    unit: 'kg',
    market: 'Central Region',
    marketCap: 85,
    volume: '2.3K tons',
    insights: [
      { id: 1, type: 'positive', text: 'Growing demand from food manufacturers' },
      { id: 2, type: 'positive', text: 'Good crop yields expected' }
    ],
    recommendation: 'buy',
    recommendationText: 'Strong upward trend with good fundamentals. Ideal time for market entry.',
    markets: [
      { id: 1, name: 'Central Region Market', price: 42.80, status: 'live' },
      { id: 2, name: 'Northern Region Market', price: 42.15, status: 'live' },
      { id: 3, name: 'Southern Region Market', price: 43.45, status: 'live' }
    ]
  }
])

const filteredProducts = computed(() => {
  let filtered = mockProducts.value

  if (searchQuery.value) {
    filtered = filtered.filter(p => p.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
  }

  if (selectedMarket.value) {
    filtered = filtered.filter(p => p.market.toLowerCase().includes(selectedMarket.value))
  }

  if (sortBy.value === 'price-desc') {
    filtered.sort((a, b) => b.price - a.price)
  } else if (sortBy.value === 'price-asc') {
    filtered.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'change') {
    filtered.sort((a, b) => b.change - a.change)
  }

  return filtered
})

const fetchMarketData = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const selectProduct = (product) => {
  selectedProductDetail.value = product
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchMarketData() })
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
</style>
