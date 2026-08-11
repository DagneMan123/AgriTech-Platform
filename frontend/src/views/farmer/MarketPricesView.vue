<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <h1>Market Prices</h1>
        <p>Track current market prices and price trends for agricultural products</p>
      </div>

      <!-- Statistics Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Total Products</div>
            <div class="stat-value">{{ prices.length }}</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: #dbeafe;">
            <i class="fas fa-arrow-up" style="color: #3b82f6;"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Avg Price Increase</div>
            <div class="stat-value">{{ averagePriceChange }}%</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: #fef3c7;">
            <i class="fas fa-bookmark" style="color: #f59e0b;"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Selected Category</div>
            <div class="stat-value">{{ selectedCategory || 'All' }}</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: #f3e8ff;">
            <i class="fas fa-calendar-alt" style="color: #a855f7;"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Last Updated</div>
            <div class="stat-value">{{ lastUpdated }}</div>
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="content-section">
        <div class="filters-container">
          <div class="filter-group">
            <label>Category</label>
            <select v-model="selectedCategory" @change="loadMarketPrices" class="form-input">
              <option value="">All Categories</option>
              <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
          </div>
          <div class="filter-group">
            <label>City/Region</label>
            <select v-model="selectedCity" @change="loadMarketPrices" class="form-input">
              <option value="">All Regions</option>
              <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
            </select>
          </div>
          <div class="filter-group">
            <label>Sort By</label>
            <select v-model="sortBy" @change="sortPrices" class="form-input">
              <option value="price-high">Price: High to Low</option>
              <option value="price-low">Price: Low to High</option>
              <option value="name">Product Name</option>
              <option value="updated">Recently Updated</option>
            </select>
          </div>
          <button @click="refreshPrices" class="btn btn-primary">
            <i class="fas fa-sync" :class="{ 'fa-spin': loading }"></i> Refresh
          </button>
        </div>
      </div>

      <!-- Current Market Prices Table -->
      <div class="content-section">
        <div class="section-header">
          <h2>Current Market Prices</h2>
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input v-model="searchQuery" type="text" placeholder="Search products...">
          </div>
        </div>

        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i> Loading market prices...
        </div>

        <table v-else-if="filteredPrices.length > 0" class="data-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Category</th>
              <th>City/Region</th>
              <th>Current Price</th>
              <th>Unit</th>
              <th>Quality</th>
              <th>Source</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="price in filteredPrices" :key="price.id" class="data-row">
              <td class="product-cell">
                <strong>{{ price.product_name }}</strong>
              </td>
              <td>
                <span class="category-badge">{{ price.category }}</span>
              </td>
              <td>{{ price.city || price.region || 'N/A' }}</td>
              <td class="price-cell">
                <span class="price-value">{{ formatPrice(price.price) }}</span>
              </td>
              <td>{{ price.price_unit || 'ETB/kg' }}</td>
              <td>
                <span :class="['quality-badge', `quality-${(price.quality || 'standard').toLowerCase()}`]">
                  {{ price.quality || 'Standard' }}
                </span>
              </td>
              <td>
                <span class="source-badge" :class="{ verified: price.is_verified }">
                  <i v-if="price.is_verified" class="fas fa-check-circle"></i>
                  {{ price.source || 'Market' }}
                </span>
              </td>
              <td>
                <button @click="showPriceDetail(price)" class="action-btn" title="View Details">
                  <i class="fas fa-eye"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="empty-state">
          <i class="fas fa-chart-line"></i>
          <p>No market prices available for selected filters</p>
        </div>
      </div>

      <!-- Price Trends Section -->
      <div class="content-section">
        <h2>Price Trends</h2>
        <div class="trends-container">
          <div v-if="selectedCategory" class="trend-filters">
            <label>Days to Display:</label>
            <select v-model.number="trendDays" @change="loadPriceTrends" class="form-input" style="width: 150px;">
              <option :value="7">Last 7 Days</option>
              <option :value="14">Last 14 Days</option>
              <option :value="30">Last 30 Days</option>
              <option :value="60">Last 60 Days</option>
              <option :value="90">Last 90 Days</option>
            </select>
          </div>
          <div v-if="trendLoading" class="loading-state" style="margin-top: 20px;">
            <i class="fas fa-spinner fa-spin"></i> Loading price trends...
          </div>
          <div v-else-if="priceForecasts.length > 0" class="forecast-grid">
            <div v-for="forecast in priceForecasts" :key="forecast.date" class="forecast-card">
              <div class="forecast-date">{{ formatDate(forecast.date) }}</div>
              <div class="forecast-price">{{ formatPrice(forecast.forecast_price) }}</div>
              <div class="forecast-confidence">
                Confidence: <span class="confidence-bar">{{ forecast.confidence }}%</span>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            <p>Select a category to view price trends</p>
          </div>
        </div>
      </div>

      <!-- Price Detail Modal -->
      <div v-if="selectedPrice" class="modal-overlay" @click.self="selectedPrice = null">
        <div class="modal-content">
          <div class="modal-header">
            <h3>{{ selectedPrice.product_name }}</h3>
            <button @click="selectedPrice = null" class="close-btn">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body">
            <div class="detail-grid">
              <div class="detail-item">
                <label>Category</label>
                <div>{{ selectedPrice.category }}</div>
              </div>
              <div class="detail-item">
                <label>City/Region</label>
                <div>{{ selectedPrice.city || selectedPrice.region || 'N/A' }}</div>
              </div>
              <div class="detail-item">
                <label>Current Price</label>
                <div class="detail-value-large">{{ formatPrice(selectedPrice.price) }}</div>
              </div>
              <div class="detail-item">
                <label>Unit</label>
                <div>{{ selectedPrice.price_unit || 'ETB/kg' }}</div>
              </div>
              <div class="detail-item">
                <label>Quality Grade</label>
                <div>
                  <span :class="['quality-badge', `quality-${(selectedPrice.quality || 'standard').toLowerCase()}`]">
                    {{ selectedPrice.quality || 'Standard' }}
                  </span>
                </div>
              </div>
              <div class="detail-item">
                <label>Source</label>
                <div>
                  <span class="source-badge" :class="{ verified: selectedPrice.is_verified }">
                    <i v-if="selectedPrice.is_verified" class="fas fa-check-circle"></i>
                    {{ selectedPrice.source || 'Market' }}
                  </span>
                </div>
              </div>
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

const router = useRouter()
const auth = useAuthStore()

// State
const prices = ref([])
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedCity = ref('')
const sortBy = ref('price-high')
const loading = ref(false)
const trendLoading = ref(false)
const priceForecasts = ref([])
const trendDays = ref(30)
const selectedPrice = ref(null)
const lastUpdated = ref('Just now')

// Data for filters
const categories = ref([
  'Grains',
  'Vegetables',
  'Fruits',
  'Pulses',
  'Spices',
  'Dairy',
  'Livestock'
])

const cities = ref([
  'Addis Ababa',
  'Dire Dawa',
  'Adama',
  'Hawassa',
  'Mekelle',
  'Bahir Dar',
  'Jimma'
])

// API base
const API_BASE = 'http://localhost:8000/api'

// Lifecycle
onMounted(() => {
  loadMarketPrices()
})

// Load market prices
const loadMarketPrices = async () => {
  loading.value = true
  try {
    let url = `${API_BASE}/market-prices`
    const params = new URLSearchParams()

    if (selectedCategory.value) {
      params.append('category', selectedCategory.value)
    }

    if (params.toString()) {
      url += '?' + params.toString()
    }

    const response = await fetch(url, {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      prices.value = data.data || []
      lastUpdated.value = new Date().toLocaleTimeString()
    }
  } catch (err) {
    console.error('Error loading market prices:', err)
  } finally {
    loading.value = false
  }
}

// Load price trends
const loadPriceTrends = async () => {
  if (!selectedCategory.value) return

  trendLoading.value = true
  try {
    const response = await fetch(
      `${API_BASE}/market-prices/trends?category=${selectedCategory.value}&days=${trendDays.value}`,
      {
        headers: {
          'Authorization': `Bearer ${auth.token}`,
          'Content-Type': 'application/json'
        }
      }
    )

    if (response.ok) {
      const data = await response.json()
      // Convert trends to forecast format
      priceForecasts.value = (data.data || []).map(item => ({
        date: item.date,
        forecast_price: item.average_price,
        confidence: 85
      }))
    }
  } catch (err) {
    console.error('Error loading price trends:', err)
  } finally {
    trendLoading.value = false
  }
}

// Compute average price change
const averagePriceChange = computed(() => {
  if (prices.value.length === 0) return '0'
  // Mock calculation - in real app would calculate from previous data
  return (Math.random() * 10 - 5).toFixed(1)
})

// Filtered prices
const filteredPrices = computed(() => {
  let filtered = prices.value

  // Filter by search query
  if (searchQuery.value) {
    filtered = filtered.filter(p =>
      p.product_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Filter by city
  if (selectedCity.value) {
    filtered = filtered.filter(p =>
      (p.city || p.region) === selectedCity.value
    )
  }

  // Sort
  switch (sortBy.value) {
    case 'price-high':
      filtered.sort((a, b) => b.price - a.price)
      break
    case 'price-low':
      filtered.sort((a, b) => a.price - b.price)
      break
    case 'name':
      filtered.sort((a, b) => a.product_name.localeCompare(b.product_name))
      break
    case 'updated':
      // Would use timestamp if available
      break
  }

  return filtered
})

// Format price
const formatPrice = (price) => {
  return `${parseFloat(price || 0).toFixed(2)} ETB`
}

// Format date
const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

// Show price detail
const showPriceDetail = (price) => {
  selectedPrice.value = price
}

// Refresh prices
const refreshPrices = () => {
  loadMarketPrices()
  if (selectedCategory.value) {
    loadPriceTrends()
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
.farmer-page { margin-left: 260px; flex: 1; overflow-y: auto; background-color: #f5f5f5; padding: 20px; }
.page-header { margin-bottom: 30px; }
.page-header h1 { font-size: 28px; font-weight: bold; color: #333; margin-bottom: 5px; }
.page-header p { color: #666; font-size: 14px; }

/* Statistics Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  background: #d1fae5;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #10b981;
}

.stat-content {
  flex: 1;
}

.stat-label { font-size: 12px; color: #999; margin-bottom: 5px; }
.stat-value { font-size: 24px; font-weight: bold; color: #333; }

/* Content Section */
.content-section {
  background: white;
  border-radius: 8px;
  padding: 25px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

.content-section h2 {
  font-size: 20px;
  font-weight: bold;
  color: #333;
  margin-bottom: 20px;
}

/* Filters */
.filters-container {
  display: flex;
  gap: 15px;
  align-items: flex-end;
  flex-wrap: wrap;
  padding-bottom: 20px;
  border-bottom: 1px solid #eee;
}

.filter-group {
  flex: 1;
  min-width: 150px;
}

.filter-group label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #666;
  margin-bottom: 6px;
}

.form-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.form-input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background-color: #10b981;
  color: white;
}

.btn-primary:hover {
  background-color: #059669;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* Section Header */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 15px;
}

.search-box {
  position: relative;
  width: 250px;
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 12px;
  color: #999;
}

.search-box input {
  width: 100%;
  padding: 10px 12px 10px 35px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

.search-box input:focus {
  outline: none;
  border-color: #10b981;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 40px;
  color: #999;
}

.loading-state i {
  font-size: 24px;
  margin-right: 10px;
}

/* Data Table */
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.data-table th {
  background-color: #f9fafb;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  font-size: 13px;
}

.data-table td {
  padding: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.data-row:hover {
  background-color: #f9fafb;
}

.product-cell {
  font-weight: 600;
  color: #10b981;
}

.price-cell {
  font-weight: bold;
  color: #333;
}

.price-value {
  background: #d1fae5;
  padding: 4px 8px;
  border-radius: 4px;
  color: #065f46;
  font-weight: 600;
}

.category-badge {
  background: #dbeafe;
  color: #1e40af;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.quality-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.quality-standard {
  background: #dbeafe;
  color: #1e40af;
}

.quality-premium {
  background: #d1fae5;
  color: #065f46;
}

.quality-export {
  background: #fce7f3;
  color: #831843;
}

.source-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  background: #f3f4f6;
  color: #6b7280;
}

.source-badge.verified {
  background: #d1fae5;
  color: #065f46;
}

.source-badge i {
  font-size: 10px;
}

.action-btn {
  background: #f3f4f6;
  border: none;
  padding: 6px 10px;
  border-radius: 4px;
  cursor: pointer;
  color: #10b981;
  transition: all 0.3s;
}

.action-btn:hover {
  background: #10b981;
  color: white;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #999;
}

.empty-state i {
  font-size: 48px;
  color: #ddd;
  margin-bottom: 15px;
}

/* Price Trends */
.trends-container {
  padding-top: 15px;
}

.trend-filters {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #eee;
}

.trend-filters label {
  font-weight: 600;
  color: #333;
  margin: 0;
}

.forecast-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 12px;
}

.forecast-card {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border-radius: 8px;
  padding: 15px;
  text-align: center;
  transition: transform 0.3s;
}

.forecast-card:hover {
  transform: translateY(-5px);
}

.forecast-date {
  font-size: 12px;
  opacity: 0.8;
  margin-bottom: 8px;
}

.forecast-price {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 8px;
}

.forecast-confidence {
  font-size: 12px;
  opacity: 0.8;
}

.confidence-bar {
  background: rgba(255, 255, 255, 0.3);
  padding: 2px 6px;
  border-radius: 3px;
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
  z-index: 1000;
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  max-width: 500px;
  width: 90%;
  animation: slideUp 0.3s;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  margin: 0;
  font-size: 20px;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #999;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.3s;
}

.close-btn:hover {
  color: #333;
}

.modal-body {
  padding: 20px;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.detail-item label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #999;
  margin-bottom: 6px;
}

.detail-item div {
  font-size: 15px;
  color: #333;
  font-weight: 500;
}

.detail-value-large {
  font-size: 24px;
  font-weight: bold;
  color: #10b981;
}

/* Responsive */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .farmer-page { margin-left: 0; padding: 15px; }
  .stats-grid { grid-template-columns: 1fr; }
  .filters-container { flex-direction: column; align-items: stretch; }
  .section-header { flex-direction: column; align-items: flex-start; }
  .search-box { width: 100%; }
  .data-table { font-size: 12px; }
  .data-table th, .data-table td { padding: 8px; }
  .forecast-grid { grid-template-columns: repeat(2, 1fr); }
  .detail-grid { grid-template-columns: 1fr; }
}
</style>
