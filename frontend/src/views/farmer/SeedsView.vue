<template>
  <div class="seeds-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="seeds-container">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h1>Seeds & Planting Materials</h1>
          <p>Browse and purchase quality seeds for your crops</p>
        </div>
        <div class="header-actions">
          <div class="search-box">
            <Search size="16" />
            <input v-model="searchQuery" type="text" placeholder="Search seeds..." />
          </div>
          <button @click="openFilters" class="btn-filter">
            <Filter size="16" />
            <span>Filters</span>
          </button>
          <button @click="toggleGrid" class="btn-view" :title="`Switch to ${gridView ? 'list' : 'grid'} view`">
            <component :is="gridView ? List : Grid3x3" size="16" />
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading seeds...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchSeeds" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="seeds-content">
        <!-- Filters Panel -->
        <div v-if="showFilters" class="filters-panel">
          <div class="filters-header">
            <h3>Filters</h3>
            <button @click="closeFilters" class="btn-close">
              <X size="20" />
            </button>
          </div>
          
          <div class="filters-content">
            <div class="filter-group">
              <label>Crop Type</label>
              <select v-model="filters.cropType" class="filter-select">
                <option value="">All Types</option>
                <option value="vegetables">Vegetables</option>
                <option value="grains">Grains</option>
                <option value="fruits">Fruits</option>
                <option value="herbs">Herbs</option>
                <option value="legumes">Legumes</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Season</label>
              <select v-model="filters.season" class="filter-select">
                <option value="">All Seasons</option>
                <option value="spring">Spring</option>
                <option value="summer">Summer</option>
                <option value="fall">Fall</option>
                <option value="winter">Winter</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Organic</label>
              <div class="checkbox-group">
                <label class="checkbox-label">
                  <input v-model="filters.organic" type="checkbox" />
                  <span>Organic Only</span>
                </label>
              </div>
            </div>

            <div class="filter-group">
              <label>Price Range</label>
              <input v-model.number="filters.priceMin" type="number" placeholder="Min" class="price-input" />
              <input v-model.number="filters.priceMax" type="number" placeholder="Max" class="price-input" />
            </div>

            <div class="filter-actions">
              <button @click="applyFilters" class="btn-apply">Apply</button>
              <button @click="clearFilters" class="btn-clear">Clear</button>
            </div>
          </div>
        </div>

        <!-- Products Grid/List -->
        <div class="products-container">
          <div v-if="filteredSeeds.length > 0" :class="gridView ? 'grid-view' : 'list-view'">
            <div v-for="seed in paginatedSeeds" :key="seed.id" :class="gridView ? 'grid-item' : 'list-item'">
              <div class="product-card">
                <div class="product-image">
                  <img :src="seed.image" :alt="seed.name" />
                  <div v-if="seed.organic" class="organic-badge">Organic</div>
                  <div v-if="seed.discount > 0" class="discount-badge">-{{ seed.discount }}%</div>
                </div>

                <div class="product-info">
                  <h3 class="product-name">{{ seed.name }}</h3>
                  <p class="product-variety">{{ seed.variety }}</p>
                  <p class="product-description">{{ seed.description }}</p>
                  
                  <div class="product-meta">
                    <span class="meta-item">
                      <Calendar size="14" />
                      Days: {{ seed.daysToMaturity }}
                    </span>
                    <span class="meta-item">
                      <Droplets size="14" />
                      {{ seed.waterNeeds }}
                    </span>
                    <span class="meta-item">
                      <Sun size="14" />
                      {{ seed.sunlight }}
                    </span>
                  </div>

                  <div class="rating">
                    <div class="stars">
                      <Star v-for="i in 5" :key="i" size="14" :class="i <= seed.rating ? 'filled' : ''" />
                    </div>
                    <span class="rating-count">({{ seed.reviews }})</span>
                  </div>

                  <div class="product-footer">
                    <div class="price-section">
                      <span v-if="seed.discount > 0" class="original-price">${{ seed.originalPrice }}</span>
                      <span class="current-price">${{ seed.price }}</span>
                      <span class="unit">per packet</span>
                    </div>
                    <button @click="addToCart(seed)" class="btn-add-cart">
                      <ShoppingCart size="16" />
                      Add
                    </button>
                  </div>
                </div>

                <button @click="viewDetails(seed)" class="btn-details">
                  <Eye size="16" />
                  Details
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <Package size="48" class="empty-icon" />
            <p>No seeds found</p>
            <span class="empty-hint">Try adjusting your filters</span>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="filteredSeeds.length > 0" class="pagination">
          <button 
            @click="currentPage--" 
            :disabled="currentPage === 1"
            class="pagination-btn"
          >
            <ChevronLeft size="16" />
            Previous
          </button>
          
          <div class="pagination-info">
            Page {{ currentPage }} of {{ totalPages }} ({{ filteredSeeds.length }} items)
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

        <!-- Details Modal -->
        <div v-if="selectedSeed" class="modal-overlay" @click="selectedSeed = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>{{ selectedSeed.name }}</h2>
              <button @click="selectedSeed = null" class="btn-close">
                <X size="20" />
              </button>
            </div>

            <div class="modal-body">
              <div class="detail-grid">
                <div class="detail-image">
                  <img :src="selectedSeed.image" :alt="selectedSeed.name" />
                </div>

                <div class="detail-info">
                  <p class="detail-variety">{{ selectedSeed.variety }}</p>
                  <p class="detail-description">{{ selectedSeed.fullDescription }}</p>

                  <div class="detail-specs">
                    <div class="spec-item">
                      <span class="spec-label">Days to Maturity</span>
                      <span class="spec-value">{{ selectedSeed.daysToMaturity }} days</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Water Needs</span>
                      <span class="spec-value">{{ selectedSeed.waterNeeds }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Sunlight</span>
                      <span class="spec-value">{{ selectedSeed.sunlight }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Soil Type</span>
                      <span class="spec-value">{{ selectedSeed.soilType }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Temperature</span>
                      <span class="spec-value">{{ selectedSeed.temperature }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Quantity</span>
                      <span class="spec-value">{{ selectedSeed.quantity }} seeds/packet</span>
                    </div>
                  </div>

                  <div class="detail-price">
                    <span class="label">Price:</span>
                    <span v-if="selectedSeed.discount > 0" class="original">${{ selectedSeed.originalPrice }}</span>
                    <span class="price">${{ selectedSeed.price }}</span>
                  </div>

                  <div class="detail-rating">
                    <div class="stars">
                      <Star v-for="i in 5" :key="i" size="16" :class="i <= selectedSeed.rating ? 'filled' : ''" />
                    </div>
                    <span>({{ selectedSeed.reviews }} reviews)</span>
                  </div>
                </div>
              </div>

              <div class="usage-tips">
                <h3>Growing Tips</h3>
                <ul>
                  <li v-for="(tip, index) in selectedSeed.growingTips" :key="index">{{ tip }}</li>
                </ul>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedSeed = null" class="btn-secondary">Close</button>
              <button @click="addToCart(selectedSeed); selectedSeed = null" class="btn-primary">
                <ShoppingCart size="16" />
                Add to Cart
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
  Search, Filter, Grid3x3, List, AlertCircle, RotateCcw, X, Eye, ShoppingCart,
  ChevronLeft, ChevronRight, Package, Star, Calendar, Droplets, Sun
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

// State
const loading = ref(false)
const error = ref(null)
const showFilters = ref(false)
const selectedSeed = ref(null)
const gridView = ref(true)
const currentPage = ref(1)
const itemsPerPage = ref(12)
const searchQuery = ref('')

// Filters
const filters = ref({
  cropType: '',
  season: '',
  organic: false,
  priceMin: 0,
  priceMax: 100
})

// Mock data
const mockSeeds = [
  {
    id: 1,
    name: 'Cherry Tomato Seeds',
    variety: 'Sweet 100',
    description: 'Fast-growing, prolific cherry tomato',
    fullDescription: 'High-yielding cherry tomato variety producing sweet, bite-sized fruits throughout the season. Perfect for containers and small spaces.',
    price: 3.99,
    originalPrice: 5.99,
    discount: 33,
    image: 'https://via.placeholder.com/300x300?text=Tomato+Seeds',
    organic: true,
    daysToMaturity: 65,
    waterNeeds: 'High',
    sunlight: 'Full Sun (6+ hrs)',
    soilType: 'Well-draining loam',
    temperature: '70-85°F',
    quantity: 50,
    rating: 5,
    reviews: 234,
    cropType: 'vegetables',
    season: 'spring',
    growingTips: [
      'Start seeds indoors 6-8 weeks before last frost',
      'Provide support with stakes or cages',
      'Water deeply but let soil dry between waterings',
      'Fertilize every 2 weeks during growing season'
    ]
  },
  {
    id: 2,
    name: 'Lettuce Mix Seeds',
    variety: 'Salad Mix',
    description: 'Colorful salad leaf mix',
    fullDescription: 'A mix of red leaf, green leaf, and romaine lettuce varieties for beautiful, diverse salads.',
    price: 2.49,
    originalPrice: 2.49,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Lettuce+Seeds',
    organic: true,
    daysToMaturity: 45,
    waterNeeds: 'Moderate',
    sunlight: 'Partial Shade (3-4 hrs)',
    soilType: 'Rich, moist soil',
    temperature: '60-70°F',
    quantity: 200,
    rating: 4,
    reviews: 156,
    cropType: 'vegetables',
    season: 'spring',
    growingTips: [
      'Cool season crop - plant early spring or fall',
      'Sow seeds directly in garden beds',
      'Keep soil consistently moist',
      'Harvest outer leaves while plant grows'
    ]
  },
  {
    id: 3,
    name: 'Carrot Seeds',
    variety: 'Nantes',
    description: 'Sweet, crisp orange carrots',
    fullDescription: 'Classic Nantes carrot with excellent flavor and smooth texture. Great for fresh eating or storage.',
    price: 1.99,
    originalPrice: 1.99,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Carrot+Seeds',
    organic: true,
    daysToMaturity: 70,
    waterNeeds: 'Moderate',
    sunlight: 'Full Sun (6+ hrs)',
    soilType: 'Loose, well-draining',
    temperature: '60-80°F',
    quantity: 300,
    rating: 5,
    reviews: 189,
    cropType: 'vegetables',
    season: 'spring',
    growingTips: [
      'Sow seeds directly in garden',
      'Thin seedlings to 2 inches apart',
      'Keep soil moist during germination',
      'Harvest when shoulders are 1/2 to 3/4 inch diameter'
    ]
  },
  {
    id: 4,
    name: 'Bell Pepper Seeds',
    variety: 'California Wonder',
    description: 'Large, thick-walled peppers',
    fullDescription: 'Strong, productive plants producing large, blocky peppers that ripen from green to red.',
    price: 4.49,
    originalPrice: 4.49,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Pepper+Seeds',
    organic: false,
    daysToMaturity: 90,
    waterNeeds: 'High',
    sunlight: 'Full Sun (8+ hrs)',
    soilType: 'Well-draining loam',
    temperature: '70-85°F',
    quantity: 30,
    rating: 4,
    reviews: 112,
    cropType: 'vegetables',
    season: 'spring',
    growingTips: [
      'Start seeds indoors 8-10 weeks before last frost',
      'Slow to germinate - be patient',
      'Provide consistent warmth and light',
      'Support heavy fruit with stakes or cages'
    ]
  },
  {
    id: 5,
    name: 'Spinach Seeds',
    variety: 'Space',
    description: 'Cold-tolerant spinach',
    fullDescription: 'Frost-hardy spinach with thick, dark green leaves. Excellent for cool season gardening.',
    price: 2.29,
    originalPrice: 3.29,
    discount: 30,
    image: 'https://via.placeholder.com/300x300?text=Spinach+Seeds',
    organic: true,
    daysToMaturity: 40,
    waterNeeds: 'Moderate',
    sunlight: 'Partial Shade (4-5 hrs)',
    soilType: 'Rich, well-draining',
    temperature: '50-70°F',
    quantity: 250,
    rating: 5,
    reviews: 198,
    cropType: 'vegetables',
    season: 'fall',
    growingTips: [
      'Direct sow in early spring or fall',
      'Plant in rows 6 inches apart',
      'Keep consistently moist',
      'Harvest young leaves for tenderness'
    ]
  },
  {
    id: 6,
    name: 'Corn Seeds',
    variety: 'Golden Bantam',
    description: 'Sweet corn for fresh eating',
    fullDescription: 'Classic sweet corn variety with superior flavor. Plant in succession for continuous harvest.',
    price: 3.99,
    originalPrice: 3.99,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Corn+Seeds',
    organic: true,
    daysToMaturity: 75,
    waterNeeds: 'Moderate',
    sunlight: 'Full Sun (8+ hrs)',
    soilType: 'Well-draining soil',
    temperature: '65-75°F',
    quantity: 80,
    rating: 4,
    reviews: 145,
    cropType: 'grains',
    season: 'spring',
    growingTips: [
      'Plant after last frost date',
      'Sow 1-2 inches deep, 8 inches apart',
      'Plant in blocks for better pollination',
      'Water regularly during ear development'
    ]
  }
]

// Computed
const filteredSeeds = computed(() => {
  let result = mockSeeds

  if (searchQuery.value) {
    result = result.filter(s => 
      s.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      s.variety.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (filters.value.cropType) {
    result = result.filter(s => s.cropType === filters.value.cropType)
  }

  if (filters.value.season) {
    result = result.filter(s => s.season === filters.value.season)
  }

  if (filters.value.organic) {
    result = result.filter(s => s.organic === true)
  }

  result = result.filter(s => 
    s.price >= filters.value.priceMin && 
    s.price <= filters.value.priceMax
  )

  return result
})

const totalPages = computed(() => {
  return Math.ceil(filteredSeeds.value.length / itemsPerPage.value)
})

const paginatedSeeds = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredSeeds.value.slice(start, end)
})

// Methods
const fetchSeeds = async () => {
  loading.value = true
  error.value = null
  try {
    // Replace with actual API call
    // const res = await farmerAPI.getSeeds()
    await new Promise(r => setTimeout(r, 500))
  } catch (err) {
    console.error('Error fetching seeds:', err)
    error.value = 'Failed to load seeds. Please try again.'
  } finally {
    loading.value = false
  }
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
    cropType: '',
    season: '',
    organic: false,
    priceMin: 0,
    priceMax: 100
  }
  currentPage.value = 1
}

const toggleGrid = () => {
  gridView.value = !gridView.value
}

const viewDetails = (seed) => {
  selectedSeed.value = seed
}

const addToCart = (seed) => {
  // Implement add to cart logic
  alert(`Added ${seed.name} to cart`)
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  fetchSeeds()
})
</script>

<style scoped>
.seeds-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.seeds-container {
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
  padding: 25px 30px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  flex-wrap: wrap;
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
  align-items: center;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
}

.search-box input {
  border: none;
  outline: none;
  font-size: 14px;
  width: 200px;
}

.btn-filter,
.btn-view {
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
.btn-view:hover {
  background: #f9fafb;
  border-color: #d1d5db;
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
}

/* Content */
.seeds-content {
  padding: 30px;
  flex: 1;
  display: flex;
  flex-direction: column;
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
}

.filters-content {
  padding: 20px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 14px;
  color: #4b5563;
}

.checkbox-label input {
  cursor: pointer;
}

.price-input {
  padding: 8px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 13px;
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

/* Products Grid/List */
.products-container {
  flex: 1;
  overflow-y: auto;
}

.grid-view {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.list-view {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 20px;
}

.list-item {
  max-width: 100%;
}

.grid-item {
  max-width: 100%;
}

.product-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
  height: 100%;
  position: relative;
}

.list-view .product-card {
  flex-direction: row;
  height: auto;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.product-image {
  position: relative;
  overflow: hidden;
  background: #f3f4f6;
  height: 200px;
  flex-shrink: 0;
}

.list-view .product-image {
  width: 200px;
  height: 200px;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.product-card:hover .product-image img {
  transform: scale(1.05);
}

.organic-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: #10b981;
  color: white;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.discount-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: #ef4444;
  color: white;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
}

.product-info {
  padding: 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.product-name {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  line-height: 1.4;
}

.product-variety {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
}

.product-description {
  font-size: 13px;
  color: #9ca3af;
  margin: 0;
  line-height: 1.4;
}

.product-meta {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  font-size: 12px;
  color: #6b7280;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.rating {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}

.stars {
  display: flex;
  gap: 2px;
}

.stars :deep(svg) {
  color: #d1d5db;
}

.stars :deep(svg.filled) {
  color: #fbbf24;
  fill: #fbbf24;
}

.rating-count {
  color: #9ca3af;
}

.product-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-top: auto;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.price-section {
  display: flex;
  align-items: baseline;
  gap: 8px;
}

.original-price {
  font-size: 12px;
  color: #9ca3af;
  text-decoration: line-through;
}

.current-price {
  font-size: 18px;
  font-weight: 700;
  color: #10b981;
}

.unit {
  font-size: 11px;
  color: #9ca3af;
}

.btn-add-cart {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-add-cart:hover {
  background: #059669;
}

.btn-details {
  position: absolute;
  bottom: 12px;
  right: 12px;
  background: white;
  border: 1px solid #e5e7eb;
  padding: 8px;
  border-radius: 6px;
  cursor: pointer;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  opacity: 0;
}

.product-card:hover .btn-details {
  opacity: 1;
}

.btn-details:hover {
  border-color: #10b981;
  color: #10b981;
  background: #ecfdf5;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
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
}

/* Pagination */
.pagination {
  padding: 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  background: white;
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

.modal-body {
  padding: 24px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-bottom: 24px;
}

.detail-image {
  overflow: hidden;
  border-radius: 8px;
  background: #f3f4f6;
  height: 300px;
}

.detail-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.detail-variety {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.detail-description {
  font-size: 14px;
  color: #4b5563;
  line-height: 1.6;
  margin: 12px 0 0 0;
}

.detail-specs {
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;
  margin: 16px 0;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
}

.spec-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.spec-label {
  font-size: 13px;
  color: #6b7280;
  font-weight: 600;
}

.spec-value {
  font-size: 14px;
  color: #1f2937;
}

.detail-price {
  display: flex;
  align-items: baseline;
  gap: 12px;
  font-weight: 600;
  margin: 16px 0;
}

.detail-price .label {
  color: #6b7280;
  font-size: 14px;
}

.detail-price .original {
  color: #9ca3af;
  text-decoration: line-through;
  font-size: 14px;
  font-weight: normal;
}

.detail-price .price {
  font-size: 24px;
  color: #10b981;
}

.detail-rating {
  display: flex;
  align-items: center;
  gap: 12px;
}

.usage-tips {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.usage-tips h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.usage-tips ul {
  margin: 0;
  padding-left: 20px;
}

.usage-tips li {
  color: #4b5563;
  font-size: 13px;
  line-height: 1.6;
  margin-bottom: 8px;
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
  .seeds-container {
    margin-left: 0;
  }

  .grid-view {
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .header-actions {
    flex-direction: column;
    width: 100%;
  }

  .search-box,
  .btn-filter,
  .btn-view {
    width: 100%;
    justify-content: center;
  }

  .search-box input {
    width: 100%;
  }

  .grid-view {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }

  .list-view .product-card {
    flex-direction: column;
  }

  .list-view .product-image {
    width: 100%;
    height: 200px;
  }
}

@media (max-width: 480px) {
  .grid-view {
    grid-template-columns: 1fr;
  }

  .filters-content {
    grid-template-columns: 1fr;
  }
}
</style>
