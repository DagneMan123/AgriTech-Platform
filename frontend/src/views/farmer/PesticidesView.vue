<template>
  <div class="pesticides-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="pesticides-container">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h1>Pesticides & Pest Management</h1>
          <p>Browse and purchase effective pest control solutions</p>
        </div>
        <div class="header-actions">
          <div class="search-box">
            <Search size="16" />
            <input v-model="searchQuery" type="text" placeholder="Search pesticides..." />
          </div>
          <button @click="openFilters" class="btn-filter">
            <Filter size="16" />
            <span>Filters</span>
          </button>
          <button @click="toggleGrid" class="btn-view">
            <component :is="gridView ? List : Grid3x3" size="16" />
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading pesticides...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchPesticides" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="pesticides-content">
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
              <label>Pest Type</label>
              <select v-model="filters.pestType" class="filter-select">
                <option value="">All Types</option>
                <option value="insect">Insect Control</option>
                <option value="fungal">Fungal</option>
                <option value="bacterial">Bacterial</option>
                <option value="herbicide">Herbicide</option>
                <option value="organic">Organic/Bio</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Application Type</label>
              <select v-model="filters.applicationType" class="filter-select">
                <option value="">All Types</option>
                <option value="spray">Spray</option>
                <option value="dust">Dust</option>
                <option value="granular">Granular</option>
                <option value="concentrate">Concentrate</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Organic Approved</label>
              <div class="checkbox-group">
                <label class="checkbox-label">
                  <input v-model="filters.organicApproved" type="checkbox" />
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
          <div v-if="filteredPesticides.length > 0" :class="gridView ? 'grid-view' : 'list-view'">
            <div v-for="pesticide in paginatedPesticides" :key="pesticide.id" :class="gridView ? 'grid-item' : 'list-item'">
              <div class="product-card">
                <div class="product-image">
                  <img :src="pesticide.image" :alt="pesticide.name" />
                  <div v-if="pesticide.organicApproved" class="organic-badge">Organic</div>
                  <div v-if="pesticide.discount > 0" class="discount-badge">-{{ pesticide.discount }}%</div>
                </div>

                <div class="product-info">
                  <h3 class="product-name">{{ pesticide.name }}</h3>
                  <p class="pest-type">{{ pesticide.pestType }}</p>
                  <p class="product-description">{{ pesticide.description }}</p>
                  
                  <div class="product-meta">
                    <span class="meta-item">
                      <Droplets size="14" />
                      {{ pesticide.applicationType }}
                    </span>
                    <span class="meta-item">
                      <AlertTriangle size="14" />
                      {{ pesticide.toxicity }}
                    </span>
                  </div>

                  <div class="coverage-info">
                    <span class="coverage-label">Coverage:</span>
                    <span class="coverage-value">{{ pesticide.coverage }} sqft</span>
                  </div>

                  <div class="product-footer">
                    <div class="price-section">
                      <span v-if="pesticide.discount > 0" class="original-price">${{ pesticide.originalPrice }}</span>
                      <span class="current-price">${{ pesticide.price }}</span>
                      <span class="unit">{{ pesticide.unit }}</span>
                    </div>
                    <button @click="addToCart(pesticide)" class="btn-add-cart">
                      <ShoppingCart size="16" />
                      Add
                    </button>
                  </div>
                </div>

                <button @click="viewDetails(pesticide)" class="btn-details">
                  <Eye size="16" />
                  Details
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <Package size="48" class="empty-icon" />
            <p>No pesticides found</p>
            <span class="empty-hint">Try adjusting your filters</span>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="filteredPesticides.length > 0" class="pagination">
          <button 
            @click="currentPage--" 
            :disabled="currentPage === 1"
            class="pagination-btn"
          >
            <ChevronLeft size="16" />
            Previous
          </button>
          
          <div class="pagination-info">
            Page {{ currentPage }} of {{ totalPages }} ({{ filteredPesticides.length }} items)
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
        <div v-if="selectedPesticide" class="modal-overlay" @click="selectedPesticide = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>{{ selectedPesticide.name }}</h2>
              <button @click="selectedPesticide = null" class="btn-close">
                <X size="20" />
              </button>
            </div>

            <div class="modal-body">
              <div class="detail-grid">
                <div class="detail-image">
                  <img :src="selectedPesticide.image" :alt="selectedPesticide.name" />
                </div>

                <div class="detail-info">
                  <p class="detail-type">{{ selectedPesticide.pestType }}</p>
                  <p class="detail-description">{{ selectedPesticide.fullDescription }}</p>

                  <div class="detail-specs">
                    <div class="spec-item">
                      <span class="spec-label">Active Ingredient</span>
                      <span class="spec-value">{{ selectedPesticide.activeIngredient }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Toxicity Level</span>
                      <span class="spec-value">{{ selectedPesticide.toxicity }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Application Type</span>
                      <span class="spec-value">{{ capitalize(selectedPesticide.applicationType) }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Coverage</span>
                      <span class="spec-value">{{ selectedPesticide.coverage }} sqft</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Shelf Life</span>
                      <span class="spec-value">{{ selectedPesticide.shelfLife }}</span>
                    </div>
                  </div>

                  <div class="detail-price">
                    <span class="label">Price:</span>
                    <span v-if="selectedPesticide.discount > 0" class="original">${{ selectedPesticide.originalPrice }}</span>
                    <span class="price">${{ selectedPesticide.price }}</span>
                  </div>
                </div>
              </div>

              <div class="usage-guide">
                <h3>Safety & Application</h3>
                <div class="safety-warning">
                  <strong>⚠️ Safety Warning:</strong> {{ selectedPesticide.safetyWarning }}
                </div>
                <div class="guide-content">
                  <h4>Application Instructions:</h4>
                  <ul>
                    <li v-for="(instruction, index) in selectedPesticide.instructions" :key="index">{{ instruction }}</li>
                  </ul>
                  
                  <h4>Effective Against:</h4>
                  <ul>
                    <li v-for="(pest, index) in selectedPesticide.effectiveAgainst" :key="index">{{ pest }}</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedPesticide = null" class="btn-secondary">Close</button>
              <button @click="addToCart(selectedPesticide); selectedPesticide = null" class="btn-primary">
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
  ChevronLeft, ChevronRight, Package, Droplets, AlertTriangle
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

// State
const loading = ref(false)
const error = ref(null)
const showFilters = ref(false)
const selectedPesticide = ref(null)
const gridView = ref(true)
const currentPage = ref(1)
const itemsPerPage = ref(12)
const searchQuery = ref('')

// Filters
const filters = ref({
  pestType: '',
  applicationType: '',
  organicApproved: false,
  priceMin: 0,
  priceMax: 200
})

// Mock data
const mockPesticides = [
  {
    id: 1,
    name: 'Neem Oil Spray',
    pestType: 'Insect Control',
    description: 'Organic insecticide for multiple pests',
    fullDescription: 'Cold-pressed neem oil effective against aphids, spider mites, whiteflies, and many other insects. Safe for organic gardening.',
    price: 18.99,
    originalPrice: 18.99,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Neem+Oil',
    organicApproved: true,
    applicationType: 'spray',
    toxicity: 'Low',
    activeIngredient: 'Azadirachtin',
    coverage: 5000,
    unit: 'quart',
    shelfLife: '3 years',
    safetyWarning: 'Keep away from children and pets. Wear gloves when handling. Do not ingest.',
    instructions: [
      'Mix 1-2 tbsp per gallon of water',
      'Apply in early morning or evening',
      'Spray all plant surfaces',
      'Reapply every 7-14 days as needed'
    ],
    effectiveAgainst: ['Aphids', 'Spider mites', 'Whiteflies', 'Mealybugs', 'Scale insects']
  },
  {
    id: 2,
    name: 'Sulfur Dust',
    pestType: 'Fungal',
    description: 'Prevents and controls fungal diseases',
    fullDescription: 'Elemental sulfur dust powder for controlling powdery mildew, leaf spot, and other fungal infections. Organic approved.',
    price: 12.99,
    originalPrice: 12.99,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Sulfur+Dust',
    organicApproved: true,
    applicationType: 'dust',
    toxicity: 'Very Low',
    activeIngredient: 'Sulfur (99%)',
    coverage: 3000,
    unit: 'bag',
    shelfLife: '5 years',
    safetyWarning: 'Do not apply within 2 weeks of oil sprays. Avoid inhaling dust.',
    instructions: [
      'Apply when temperature below 85°F',
      'Dust evenly over plant surfaces',
      'Apply every 7-10 days',
      'Do not apply during hot weather'
    ],
    effectiveAgainst: ['Powdery mildew', 'Leaf spot', 'Rust', 'Mites']
  },
  {
    id: 3,
    name: 'Spinosad Concentrate',
    pestType: 'Organic/Bio',
    description: 'Organic insect control concentrate',
    fullDescription: 'OMRI-certified organic concentrate derived from soil bacteria. Highly effective against caterpillars, leafminers, and thrips.',
    price: 32.99,
    originalPrice: 39.99,
    discount: 17,
    image: 'https://via.placeholder.com/300x300?text=Spinosad',
    organicApproved: true,
    applicationType: 'concentrate',
    toxicity: 'Low',
    activeIngredient: 'Spinosyn A & D',
    coverage: 4000,
    unit: 'bottle',
    shelfLife: '2 years',
    safetyWarning: 'Keep in cool, dark place. Toxic to bees - apply in early morning.',
    instructions: [
      'Mix 1-2 oz per gallon water',
      'Apply to all leaf surfaces',
      'Apply in early morning before bees active',
      'Reapply every 7-10 days'
    ],
    effectiveAgainst: ['Caterpillars', 'Leafminers', 'Thrips', 'Sawfly larvae', 'Beetles']
  },
  {
    id: 4,
    name: 'Copper Fungicide',
    pestType: 'Fungal',
    description: 'Broad-spectrum fungal disease control',
    fullDescription: 'Copper-based fungicide for prevention and control of bacterial and fungal diseases on vegetables, fruits, and ornamentals.',
    price: 16.49,
    originalPrice: 16.49,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Copper+Fungicide',
    organicApproved: true,
    applicationType: 'spray',
    toxicity: 'Moderate',
    activeIngredient: 'Copper Sulfate',
    coverage: 4500,
    unit: 'pint',
    shelfLife: '4 years',
    safetyWarning: 'Avoid contact with skin and eyes. Do not spray in direct sunlight.',
    instructions: [
      'Mix according to label directions',
      'Apply in early morning or late evening',
      'Cover all leaf surfaces including undersides',
      'Reapply every 7-14 days'
    ],
    effectiveAgainst: ['Powdery mildew', 'Early blight', 'Leaf spot', 'Bacterial diseases']
  },
  {
    id: 5,
    name: 'Pyrethrin Spray',
    pestType: 'Insect Control',
    description: 'Natural botanical insecticide',
    fullDescription: 'Extract from chrysanthemum flowers. Fast-acting organic insecticide for control of multiple garden pests.',
    price: 14.99,
    originalPrice: 14.99,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Pyrethrin',
    organicApproved: true,
    applicationType: 'spray',
    toxicity: 'Very Low',
    activeIngredient: 'Pyrethrin 1.3%',
    coverage: 2500,
    unit: 'quart',
    shelfLife: '2 years',
    safetyWarning: 'Keep away from fish ponds. Toxic to aquatic life.',
    instructions: [
      'Mix per label instructions',
      'Apply when insects appear',
      'Spray all affected plant parts',
      'Reapply every 5-7 days if needed'
    ],
    effectiveAgainst: ['Aphids', 'Beetles', 'Caterpillars', 'Flies', 'Mosquitoes']
  },
  {
    id: 6,
    name: 'Insecticidal Soap',
    pestType: 'Insect Control',
    description: 'Safe spray for soft-bodied insects',
    fullDescription: 'Potassium salts of fatty acids. Highly effective against soft-bodied insects like mites, whiteflies, and aphids.',
    price: 11.49,
    originalPrice: 11.49,
    discount: 0,
    image: 'https://via.placeholder.com/300x300?text=Soap+Spray',
    organicApproved: true,
    applicationType: 'spray',
    toxicity: 'Very Low',
    activeIngredient: 'Potassium Salts 49%',
    coverage: 3000,
    unit: 'quart',
    shelfLife: '3 years',
    safetyWarning: 'May cause leaf spotting on delicate plants. Test on small area first.',
    instructions: [
      'Dilute as per label',
      'Spray thorough coverage',
      'Apply every 7-10 days',
      'Spray in early morning or evening'
    ],
    effectiveAgainst: ['Aphids', 'Mites', 'Whiteflies', 'Mealybugs', 'Thrips']
  }
]

// Computed
const filteredPesticides = computed(() => {
  let result = mockPesticides

  if (searchQuery.value) {
    result = result.filter(p => 
      p.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (filters.value.pestType) {
    result = result.filter(p => p.pestType === filters.value.pestType)
  }

  if (filters.value.applicationType) {
    result = result.filter(p => p.applicationType === filters.value.applicationType)
  }

  if (filters.value.organicApproved) {
    result = result.filter(p => p.organicApproved === true)
  }

  result = result.filter(p => 
    p.price >= filters.value.priceMin && 
    p.price <= filters.value.priceMax
  )

  return result
})

const totalPages = computed(() => {
  return Math.ceil(filteredPesticides.value.length / itemsPerPage.value)
})

const paginatedPesticides = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredPesticides.value.slice(start, end)
})

// Methods
const fetchPesticides = async () => {
  loading.value = true
  error.value = null
  try {
    await new Promise(r => setTimeout(r, 500))
  } catch (err) {
    console.error('Error fetching pesticides:', err)
    error.value = 'Failed to load pesticides. Please try again.'
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
    pestType: '',
    applicationType: '',
    organicApproved: false,
    priceMin: 0,
    priceMax: 200
  }
  currentPage.value = 1
}

const toggleGrid = () => {
  gridView.value = !gridView.value
}

const capitalize = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const viewDetails = (pesticide) => {
  selectedPesticide.value = pesticide
}

const addToCart = (pesticide) => {
  alert(`Added ${pesticide.name} to cart`)
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  fetchPesticides()
})
</script>

<style scoped>
.pesticides-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.pesticides-container {
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
  border-top-color: #ef4444;
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
.pesticides-content {
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
  background: #ef4444;
  color: white;
}

.btn-apply:hover {
  background: #dc2626;
}

.btn-clear {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.btn-clear:hover {
  background: #e5e7eb;
}

/* Products */
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

.pest-type {
  font-size: 13px;
  font-weight: 600;
  color: #dc2626;
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
  color: #ef4444;
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
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-add-cart:hover {
  background: #dc2626;
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
  border-color: #ef4444;
  color: #ef4444;
  background: #fee2e2;
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
  border-color: #ef4444;
  color: #ef4444;
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
  color: #dc2626;
  margin: 0;
  font-weight: 600;
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
  color: #ef4444;
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

.safety-warning {
  background: #fee2e2;
  border: 1px solid #fca5a5;
  padding: 12px;
  border-radius: 6px;
  font-size: 13px;
  color: #991b1b;
  margin-bottom: 12px;
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
  background: #ef4444;
  color: white;
}

.btn-primary:hover {
  background: #dc2626;
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
  .pesticides-container {
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
