<template>
  <div class="fertilizers-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="fertilizers-container">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h1>Fertilizers & Soil Amendments</h1>
          <p>Browse and purchase quality fertilizers for crop nutrition</p>
        </div>
        <div class="header-actions">
          <div class="search-box">
            <Search size="16" />
            <input v-model="searchQuery" type="text" placeholder="Search fertilizers..." />
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
        <p>Loading fertilizers...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchFertilizers" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="fertilizers-content">
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
              <label>Fertilizer Type</label>
              <select v-model="filters.type" class="filter-select">
                <option value="">All Types</option>
                <option value="organic">Organic</option>
                <option value="inorganic">Inorganic</option>
                <option value="liquid">Liquid</option>
                <option value="powder">Powder</option>
              </select>
            </div>

            <div class="filter-group">
              <label>NPK Ratio</label>
              <select v-model="filters.npk" class="filter-select">
                <option value="">Any Ratio</option>
                <option value="10-10-10">10-10-10 (Balanced)</option>
                <option value="20-5-5">20-5-5 (High N)</option>
                <option value="5-10-10">5-10-10 (High P/K)</option>
                <option value="0-0-60">0-0-60 (Potassium)</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Organic Certified</label>
              <div class="checkbox-group">
                <label class="checkbox-label">
                  <input v-model="filters.organicCertified" type="checkbox" />
                  <span>OMRI Certified</span>
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
          <div v-if="filteredFertilizers.length > 0" :class="gridView ? 'grid-view' : 'list-view'">
            <div v-for="fertilizer in paginatedFertilizers" :key="fertilizer.id" :class="gridView ? 'grid-item' : 'list-item'">
              <div class="product-card">
                <div class="product-image">
                  <img :src="fertilizer.image" :alt="fertilizer.name" />
                  <div v-if="fertilizer.organicCertified" class="certified-badge">OMRI Certified</div>
                  <div v-if="fertilizer.discount > 0" class="discount-badge">-{{ fertilizer.discount }}%</div>
                </div>

                <div class="product-info">
                  <h3 class="product-name">{{ fertilizer.name }}</h3>
                  <p class="npk-ratio">NPK: {{ fertilizer.npk }}</p>
                  <p class="product-description">{{ fertilizer.description }}</p>
                  
                  <div class="product-meta">
                    <span class="meta-item">
                      <Package size="14" />
                      {{ fertilizer.weight }}
                    </span>
                    <span class="meta-item">
                      <Leaf size="14" />
                      {{ fertilizer.type }}
                    </span>
                  </div>

                  <div class="coverage-info">
                    <span class="coverage-label">Coverage:</span>
                    <span class="coverage-value">{{ fertilizer.coverage }} sqft</span>
                  </div>

                  <div class="product-footer">
                    <div class="price-section">
                      <span v-if="fertilizer.discount > 0" class="original-price">${{ fertilizer.originalPrice }}</span>
                      <span class="current-price">${{ fertilizer.price }}</span>
                      <span class="unit">{{ fertilizer.unit }}</span>
                    </div>
                    <button @click="addToCart(fertilizer)" class="btn-add-cart">
                      <ShoppingCart size="16" />
                      Add
                    </button>
                  </div>
                </div>

                <button @click="viewDetails(fertilizer)" class="btn-details">
                  <Eye size="16" />
                  Details
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <Package size="48" class="empty-icon" />
            <p>No fertilizers found</p>
            <span class="empty-hint">Try adjusting your filters</span>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="filteredFertilizers.length > 0" class="pagination">
          <button 
            @click="currentPage--" 
            :disabled="currentPage === 1"
            class="pagination-btn"
          >
            <ChevronLeft size="16" />
            Previous
          </button>
          
          <div class="pagination-info">
            Page {{ currentPage }} of {{ totalPages }} ({{ filteredFertilizers.length }} items)
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
        <div v-if="selectedFertilizer" class="modal-overlay" @click="selectedFertilizer = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>{{ selectedFertilizer.name }}</h2>
              <button @click="selectedFertilizer = null" class="btn-close">
                <X size="20" />
              </button>
            </div>

            <div class="modal-body">
              <div class="detail-grid">
                <div class="detail-image">
                  <img :src="selectedFertilizer.image" :alt="selectedFertilizer.name" />
                </div>

                <div class="detail-info">
                  <p class="detail-type">{{ selectedFertilizer.type }} Fertilizer</p>
                  <p class="detail-description">{{ selectedFertilizer.fullDescription }}</p>

                  <div class="detail-specs">
                    <div class="spec-item">
                      <span class="spec-label">NPK Ratio</span>
                      <span class="spec-value">{{ selectedFertilizer.npk }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Weight</span>
                      <span class="spec-value">{{ selectedFertilizer.weight }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Type</span>
                      <span class="spec-value">{{ capitalize(selectedFertilizer.type) }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Coverage</span>
                      <span class="spec-value">{{ selectedFertilizer.coverage }} sqft</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Application Rate</span>
                      <span class="spec-value">{{ selectedFertilizer.applicationRate }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Shelf Life</span>
                      <span class="spec-value">{{ selectedFertilizer.shelfLife }}</span>
                    </div>
                  </div>

                  <div class="detail-price">
                    <span class="label">Price:</span>
                    <span v-if="selectedFertilizer.discount > 0" class="original">${{ selectedFertilizer.originalPrice }}</span>
                    <span class="price">${{ selectedFertilizer.price }}</span>
                  </div>
                </div>
              </div>

              <div class="usage-guide">
                <h3>Usage Guide</h3>
                <div class="guide-content">
                  <h4>Application Instructions:</h4>
                  <ul>
                    <li v-for="(instruction, index) in selectedFertilizer.instructions" :key="index">{{ instruction }}</li>
                  </ul>
                  
                  <h4>Best For:</h4>
                  <ul>
                    <li v-for="(crop, index) in selectedFertilizer.bestFor" :key="index">{{ crop }}</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedFertilizer = null" class="btn-secondary">Close</button>
              <button @click="addToCart(selectedFertilizer); selectedFertilizer = null" class="btn-primary">
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
  ChevronLeft, ChevronRight, Package, Leaf
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

// State
const loading = ref(false)
const error = ref(null)
const showFilters = ref(false)
const selectedFertilizer = ref(null)
const gridView = ref(true)
const currentPage = ref(1)
const itemsPerPage = ref(12)
const searchQuery = ref('')

// Filters
const filters = ref({
  type: '',
  npk: '',
  organicCertified: false,
  priceMin: 0,
  priceMax: 500
})

// Mock data
const mockFertilizers = [
  {
    id: 1,
    name: 'All-Purpose NPK 10-10-10',
    npk: '10-10-10',
    description: 'Balanced general-purpose fertilizer',
    fullDescription: 'A well-balanced, granular fertilizer ideal for general garden use and most vegetables. Provides balanced nutrition for healthy growth.',
    price: 24.99,
    originalPrice: 24.99,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=NPK+10-10-10',
    organicCertified: false,
    type: 'granular',
    weight: '50 lbs',
    coverage: 5000,
    unit: 'bag',
    applicationRate: '1 lb per 100 sqft',
    shelfLife: '5 years',
    instructions: [
      'Apply evenly across garden bed',
      'Water thoroughly after application',
      'Reapply every 4-6 weeks during growing season',
      'Follow label instructions for exact dosage'
    ],
    bestFor: ['Vegetables', 'Flowers', 'General gardening', 'Lawns']
  },
  {
    id: 2,
    name: 'Organic Compost Tea',
    npk: '2-2-2',
    description: 'OMRI certified organic soil enhancer',
    fullDescription: 'Premium organic compost tea enriched with beneficial microbes. Perfect for sustainable gardening and soil health improvement.',
    price: 19.99,
    originalPrice: 29.99,
    discount: 33,
    image: 'https://via.placeholder.com/300x300?text=Compost+Tea',
    organicCertified: true,
    type: 'liquid',
    weight: '1 Gallon',
    coverage: 1000,
    unit: 'bottle',
    applicationRate: '1 cup per 100 sqft',
    shelfLife: '1 year',
    instructions: [
      'Dilute 1 part tea to 10 parts water',
      'Apply to base of plants',
      'Apply every 2-3 weeks',
      'Best used in early morning or evening'
    ],
    bestFor: ['Organic gardens', 'Vegetables', 'Flowers', 'Soil health']
  },
  {
    id: 3,
    name: 'High Nitrogen 20-5-5',
    npk: '20-5-5',
    description: 'Promotes lush green foliage growth',
    fullDescription: 'Nitrogen-rich formula that stimulates vigorous leaf and stem growth. Ideal for leafy greens, grasses, and foliage plants.',
    price: 22.49,
    originalPrice: 22.49,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=High+Nitrogen',
    organicCertified: false,
    type: 'granular',
    weight: '40 lbs',
    coverage: 4000,
    unit: 'bag',
    applicationRate: '0.8 lb per 100 sqft',
    shelfLife: '5 years',
    instructions: [
      'Spread evenly over soil surface',
      'Work into top inch of soil',
      'Water well after application',
      'Use monthly during growing season'
    ],
    bestFor: ['Leafy greens', 'Grasses', 'Herbs', 'Foliage plants']
  },
  {
    id: 4,
    name: 'Bloom Booster 5-30-30',
    npk: '5-30-30',
    description: 'Encourages flowering and fruit development',
    fullDescription: 'Phosphorus and potassium-rich formula designed to promote abundant blooms and strong fruit development in vegetables and ornamentals.',
    price: 27.99,
    originalPrice: 27.99,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Bloom+Booster',
    organicCertified: false,
    type: 'powder',
    weight: '25 lbs',
    coverage: 2500,
    unit: 'bag',
    applicationRate: '1 lb per 200 sqft',
    shelfLife: '5 years',
    instructions: [
      'Apply when plants begin to flower',
      'Work into soil around plant base',
      'Water thoroughly after application',
      'Reapply every 3-4 weeks'
    ],
    bestFor: ['Tomatoes', 'Peppers', 'Fruits', 'Flowering plants']
  },
  {
    id: 5,
    name: 'Potassium Sulfate 0-0-53',
    npk: '0-0-53',
    description: 'Pure potassium for strong roots and disease resistance',
    fullDescription: 'High-potassium formula that strengthens root systems and improves disease resistance. Ideal for vegetables and perennials.',
    price: 34.99,
    originalPrice: 34.99,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Potassium+Sulfate',
    organicCertified: true,
    type: 'granular',
    weight: '30 lbs',
    coverage: 3000,
    unit: 'bag',
    applicationRate: '0.5 lb per 100 sqft',
    shelfLife: '10 years',
    instructions: [
      'Broadcast over garden bed',
      'Till or work into upper soil',
      'Water deeply after application',
      'Apply once per season'
    ],
    bestFor: ['Root vegetables', 'Potatoes', 'Legumes', 'Perennials']
  },
  {
    id: 6,
    name: 'Liquid Seaweed Extract',
    npk: '1-1-5',
    description: 'Natural trace minerals and growth stimulant',
    fullDescription: 'Premium seaweed extract containing natural trace minerals, amino acids, and growth hormones for overall plant health.',
    price: 16.99,
    originalPrice: 16.99,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Seaweed+Extract',
    organicCertified: true,
    type: 'liquid',
    weight: '32 oz',
    coverage: 2000,
    unit: 'bottle',
    applicationRate: '1 oz per gallon water',
    shelfLife: '2 years',
    instructions: [
      'Mix 1 oz concentrate per gallon water',
      'Spray on leaves and soil',
      'Apply every 2-3 weeks',
      'Best in cool seasons'
    ],
    bestFor: ['All plants', 'Stress recovery', 'Beneficial microbes', 'Sustainable gardening']
  }
]

// Computed
const filteredFertilizers = computed(() => {
  let result = mockFertilizers

  if (searchQuery.value) {
    result = result.filter(f => 
      f.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      f.npk.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (filters.value.type) {
    result = result.filter(f => f.type === filters.value.type)
  }

  if (filters.value.npk) {
    result = result.filter(f => f.npk === filters.value.npk)
  }

  if (filters.value.organicCertified) {
    result = result.filter(f => f.organicCertified === true)
  }

  result = result.filter(f => 
    f.price >= filters.value.priceMin && 
    f.price <= filters.value.priceMax
  )

  return result
})

const totalPages = computed(() => {
  return Math.ceil(filteredFertilizers.value.length / itemsPerPage.value)
})

const paginatedFertilizers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredFertilizers.value.slice(start, end)
})

// Methods
const fetchFertilizers = async () => {
  loading.value = true
  error.value = null
  try {
    await new Promise(r => setTimeout(r, 500))
  } catch (err) {
    console.error('Error fetching fertilizers:', err)
    error.value = 'Failed to load fertilizers. Please try again.'
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
    type: '',
    npk: '',
    organicCertified: false,
    priceMin: 0,
    priceMax: 500
  }
  currentPage.value = 1
}

const toggleGrid = () => {
  gridView.value = !gridView.value
}

const capitalize = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const viewDetails = (fertilizer) => {
  selectedFertilizer.value = fertilizer
}

const addToCart = (fertilizer) => {
  alert(`Added ${fertilizer.name} to cart`)
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  fetchFertilizers()
})
</script>

<style scoped>
.fertilizers-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.fertilizers-container {
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
.fertilizers-content {
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

.certified-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: #059669;
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

.npk-ratio {
  font-size: 14px;
  font-weight: 600;
  color: #3b82f6;
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

.coverage-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px;
  background: #f9fafb;
  border-radius: 6px;
  font-size: 13px;
}

.coverage-label {
  color: #6b7280;
  font-weight: 600;
}

.coverage-value {
  color: #1f2937;
  font-weight: 600;
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

.detail-type {
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

.usage-guide {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.usage-guide h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.guide-content h4 {
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
  margin: 12px 0 8px 0;
}

.guide-content ul {
  margin: 0;
  padding-left: 20px;
}

.guide-content li {
  color: #4b5563;
  font-size: 13px;
  line-height: 1.6;
  margin-bottom: 6px;
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
  .fertilizers-container {
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
