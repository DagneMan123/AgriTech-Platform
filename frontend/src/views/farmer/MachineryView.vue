<template>
  <div class="machinery-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="machinery-container">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h1>Machinery & Equipment</h1>
          <p>Browse, rent, or purchase agricultural machinery and equipment</p>
        </div>
        <div class="header-actions">
          <div class="search-box">
            <Search size="16" />
            <input v-model="searchQuery" type="text" placeholder="Search equipment..." />
          </div>
          <button @click="toggleFilters" class="btn-filter">
            <Filter size="16" />
            <span>Filters</span>
          </button>
          <button @click="toggleListType" class="btn-view" :title="`Switch to ${viewType === 'grid' ? 'list' : 'grid'} view`">
            <component :is="viewType === 'grid' ? List : Grid3x3" size="16" />
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading machinery...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchMachinery" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="machinery-content">
        <!-- Filters Panel -->
        <div v-if="showFilters" class="filters-panel">
          <div class="filters-header">
            <h3>Filters</h3>
            <button @click="toggleFilters" class="btn-close">
              <X size="20" />
            </button>
          </div>
          
          <div class="filters-content">
            <div class="filter-group">
              <label>Equipment Type</label>
              <select v-model="filters.type" class="filter-select">
                <option value="">All Types</option>
                <option value="tractor">Tractors</option>
                <option value="harvester">Harvesters</option>
                <option value="tiller">Tillers</option>
                <option value="pump">Pumps</option>
                <option value="sprayer">Sprayers</option>
                <option value="other">Other Equipment</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Availability</label>
              <select v-model="filters.availability" class="filter-select">
                <option value="">All</option>
                <option value="buy">For Purchase</option>
                <option value="rent">For Rent</option>
                <option value="both">Both Options</option>
              </select>
            </div>

            <div class="filter-group">
              <label>Condition</label>
              <select v-model="filters.condition" class="filter-select">
                <option value="">All Conditions</option>
                <option value="new">New</option>
                <option value="used">Used</option>
                <option value="refurbished">Refurbished</option>
              </select>
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

        <!-- Equipment Grid/List -->
        <div class="equipment-container">
          <div v-if="filteredMachinery.length > 0" :class="`view-${viewType}`">
            <div v-for="equipment in paginatedMachinery" :key="equipment.id" class="equipment-card">
              <div class="equipment-image">
                <img :src="equipment.image" :alt="equipment.name" />
                <div v-if="equipment.condition === 'new'" class="condition-badge new">New</div>
                <div v-else-if="equipment.condition === 'refurbished'" class="condition-badge refurbished">Refurbished</div>
                <div v-else class="condition-badge used">Used</div>
              </div>

              <div class="equipment-info">
                <h3 class="equipment-name">{{ equipment.name }}</h3>
                <p class="equipment-type">{{ equipment.type }}</p>
                <p class="equipment-description">{{ equipment.description }}</p>
                
                <div class="equipment-specs">
                  <span class="spec-item">
                    <Zap size="14" />
                    {{ equipment.power }}
                  </span>
                  <span class="spec-item">
                    <RotateCw size="14" />
                    {{ equipment.hoursUsed }} hrs
                  </span>
                </div>

                <div class="equipment-pricing">
                  <div v-if="equipment.buyPrice" class="price-option">
                    <span class="price-label">Buy:</span>
                    <span class="price-value">${{ formatNumber(equipment.buyPrice) }}</span>
                  </div>
                  <div v-if="equipment.rentPrice" class="price-option">
                    <span class="price-label">Rent:</span>
                    <span class="price-value">${{ formatNumber(equipment.rentPrice) }}/day</span>
                  </div>
                </div>

                <div class="equipment-footer">
                  <div class="availability-badge" :class="`status-${equipment.availability}`">
                    <CheckCircle size="14" />
                    {{ equipment.availability === 'both' ? 'Both Options' : capitalize(equipment.availability) }}
                  </div>
                  <button @click="viewDetails(equipment)" class="btn-details">
                    <Eye size="16" />
                    Details
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <Wrench size="48" class="empty-icon" />
            <p>No equipment found</p>
            <span class="empty-hint">Try adjusting your filters</span>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="filteredMachinery.length > 0" class="pagination">
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

        <!-- Details Modal -->
        <div v-if="selectedEquipment" class="modal-overlay" @click="selectedEquipment = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>{{ selectedEquipment.name }}</h2>
              <button @click="selectedEquipment = null" class="btn-close">
                <X size="20" />
              </button>
            </div>

            <div class="modal-body">
              <div class="detail-grid">
                <div class="detail-image">
                  <img :src="selectedEquipment.image" :alt="selectedEquipment.name" />
                </div>

                <div class="detail-info">
                  <p class="detail-type">{{ selectedEquipment.type }}</p>
                  <p class="detail-description">{{ selectedEquipment.fullDescription }}</p>

                  <div class="detail-specs">
                    <div class="spec-item">
                      <span class="spec-label">Power</span>
                      <span class="spec-value">{{ selectedEquipment.power }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Hours Used</span>
                      <span class="spec-value">{{ selectedEquipment.hoursUsed }} hours</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Condition</span>
                      <span class="spec-value">{{ capitalize(selectedEquipment.condition) }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Year</span>
                      <span class="spec-value">{{ selectedEquipment.year }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Owner</span>
                      <span class="spec-value">{{ selectedEquipment.ownerName }}</span>
                    </div>
                    <div class="spec-item">
                      <span class="spec-label">Location</span>
                      <span class="spec-value">{{ selectedEquipment.location }}</span>
                    </div>
                  </div>

                  <div class="pricing-details">
                    <div v-if="selectedEquipment.buyPrice" class="price-block">
                      <span class="label">Purchase Price:</span>
                      <span class="price">${{ formatNumber(selectedEquipment.buyPrice) }}</span>
                    </div>
                    <div v-if="selectedEquipment.rentPrice" class="price-block">
                      <span class="label">Daily Rental:</span>
                      <span class="price">${{ formatNumber(selectedEquipment.rentPrice) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="features-section">
                <h3>Features & Specifications</h3>
                <ul class="features-list">
                  <li v-for="(feature, index) in selectedEquipment.features" :key="index">{{ feature }}</li>
                </ul>
              </div>

              <div class="contact-section">
                <h3>Contact Owner</h3>
                <div class="owner-contact">
                  <p><strong>Name:</strong> {{ selectedEquipment.ownerName }}</p>
                  <p><strong>Phone:</strong> {{ selectedEquipment.ownerPhone }}</p>
                  <p><strong>Email:</strong> {{ selectedEquipment.ownerEmail }}</p>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedEquipment = null" class="btn-secondary">Close</button>
              <button @click="requestEquipment(selectedEquipment)" class="btn-primary">
                <Send size="16" />
                Send Inquiry
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
  Search, Filter, Grid3x3, List, AlertCircle, RotateCcw, X, Eye, 
  ChevronLeft, ChevronRight, Wrench, Zap, RotateCw, CheckCircle, Send
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

// State
const loading = ref(false)
const error = ref(null)
const showFilters = ref(false)
const selectedEquipment = ref(null)
const viewType = ref('grid')
const currentPage = ref(1)
const itemsPerPage = ref(12)
const searchQuery = ref('')

// Filters
const filters = ref({
  type: '',
  availability: '',
  condition: '',
  priceMin: 0,
  priceMax: 100000
})

// Mock data
const mockMachinery = [
  {
    id: 1,
    name: 'John Deere 5100R Tractor',
    type: 'Tractor',
    description: 'Powerful mid-range tractor for farming',
    fullDescription: 'Professional grade tractor suitable for various farming operations. Well-maintained and reliable.',
    image: 'https://via.placeholder.com/300x300?text=Tractor',
    power: '100 HP',
    hoursUsed: 2400,
    year: 2018,
    condition: 'used',
    availability: 'both',
    buyPrice: 45000,
    rentPrice: 150,
    ownerName: 'Farm Solutions Ltd',
    ownerPhone: '+1-555-0201',
    ownerEmail: 'contact@farmsolutions.com',
    location: 'Springfield, IL',
    features: [
      '100 HP engine',
      'Transmission: PowerShift Plus',
      'Hydraulic system: Open center',
      'Comfortable cabin with climate control',
      'Equipment ready - 3-point linkage',
      'PTO shaft included'
    ]
  },
  {
    id: 2,
    name: 'CLAAS Lexion 600 Combine',
    type: 'Harvester',
    description: 'Modern combine harvester',
    fullDescription: 'High-efficiency combine harvester for grain and crop harvesting.',
    image: 'https://via.placeholder.com/300x300?text=Harvester',
    power: '290 HP',
    hoursUsed: 800,
    year: 2020,
    condition: 'new',
    availability: 'both',
    buyPrice: 350000,
    rentPrice: 800,
    ownerName: 'Agricultural Equipment Co',
    ownerPhone: '+1-555-0202',
    ownerEmail: 'sales@agequip.com',
    location: 'Chicago, IL',
    features: [
      'Advanced GPS guidance system',
      'Automatic header control',
      'Grain tank capacity: 70 bushels',
      'Fuel efficiency optimization',
      'Real-time yield mapping',
      'Remote diagnostics capable'
    ]
  },
  {
    id: 3,
    name: 'Kubota MX5200 Tractor',
    type: 'Tractor',
    description: 'Mid-size tractor for small to medium farms',
    fullDescription: 'Reliable and cost-effective tractor perfect for small operations.',
    image: 'https://via.placeholder.com/300x300?text=Kubota',
    power: '52 HP',
    hoursUsed: 3200,
    year: 2015,
    condition: 'refurbished',
    availability: 'rent',
    buyPrice: null,
    rentPrice: 80,
    ownerName: 'Equipment Rental Hub',
    ownerPhone: '+1-555-0203',
    ownerEmail: 'rentals@equiphub.com',
    location: 'Peoria, IL',
    features: [
      '52 HP diesel engine',
      'Manual transmission',
      '4WD capability',
      'Recently serviced',
      'New tires',
      'Loader attached'
    ]
  },
  {
    id: 4,
    name: 'Deep Tillage Plow',
    type: 'Tiller',
    description: 'Heavy-duty soil preparation equipment',
    fullDescription: 'Professional deep tillage plow for soil preparation and cultivation.',
    image: 'https://via.placeholder.com/300x300?text=Plow',
    power: 'Requires 80+ HP',
    hoursUsed: 150,
    year: 2019,
    condition: 'new',
    availability: 'buy',
    buyPrice: 18000,
    rentPrice: null,
    ownerName: 'Farm Equipment Supplier',
    ownerPhone: '+1-555-0204',
    ownerEmail: 'sales@farmequip.com',
    location: 'Bloomington, IL',
    features: [
      '5-bottom heavy duty plow',
      'Adjustable depth control',
      '3-point linkage mount',
      'Durable steel construction',
      'Reversible bottoms',
      'Perfect for virgin land'
    ]
  },
  {
    id: 5,
    name: 'Centrifugal Water Pump',
    type: 'Pump',
    description: 'Irrigation water pump system',
    fullDescription: 'High-capacity centrifugal pump for efficient irrigation.',
    image: 'https://via.placeholder.com/300x300?text=Pump',
    power: '15 HP',
    hoursUsed: 500,
    year: 2017,
    condition: 'used',
    availability: 'both',
    buyPrice: 5000,
    rentPrice: 40,
    ownerName: 'Irrigation Solutions',
    ownerPhone: '+1-555-0205',
    ownerEmail: 'info@irrigsolutions.com',
    location: 'Decatur, IL',
    features: [
      'Flow capacity: 1500 GPM',
      'Diesel powered',
      'Cast iron construction',
      'Low maintenance design',
      'Quick coupling system',
      'Strainer included'
    ]
  },
  {
    id: 6,
    name: 'Boom Sprayer',
    type: 'Sprayer',
    description: 'Crop spraying equipment',
    fullDescription: 'Professional boom sprayer for pesticide and fertilizer application.',
    image: 'https://via.placeholder.com/300x300?text=Sprayer',
    power: '25 HP',
    hoursUsed: 800,
    year: 2018,
    condition: 'used',
    availability: 'rent',
    buyPrice: null,
    rentPrice: 60,
    ownerName: 'Chemical Application Services',
    ownerPhone: '+1-555-0206',
    ownerEmail: 'spray@caservices.com',
    location: 'Urbana, IL',
    features: [
      '90-foot boom width',
      'Tank capacity: 500 gallons',
      'Adjustable pressure system',
      'GPS section control',
      'Automatic nozzle selection',
      'Foam marker included'
    ]
  }
]

// Computed
const filteredMachinery = computed(() => {
  let result = mockMachinery

  if (searchQuery.value) {
    result = result.filter(m => 
      m.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      m.type.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (filters.value.type) {
    result = result.filter(m => m.type === filters.value.type)
  }

  if (filters.value.availability) {
    result = result.filter(m => m.availability === filters.value.availability || m.availability === 'both')
  }

  if (filters.value.condition) {
    result = result.filter(m => m.condition === filters.value.condition)
  }

  const minPrice = Math.min(...result.map(m => Math.min(m.buyPrice || Infinity, m.rentPrice ? m.rentPrice * 100 : Infinity)))
  const maxPrice = Math.max(...result.map(m => Math.max(m.buyPrice || 0, m.rentPrice ? m.rentPrice * 100 : 0)))

  result = result.filter(m => {
    const itemPrice = m.buyPrice || (m.rentPrice ? m.rentPrice * 100 : 0)
    return itemPrice >= filters.value.priceMin && itemPrice <= filters.value.priceMax
  })

  return result
})

const totalPages = computed(() => {
  return Math.ceil(filteredMachinery.value.length / itemsPerPage.value)
})

const paginatedMachinery = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredMachinery.value.slice(start, end)
})

// Methods
const fetchMachinery = async () => {
  loading.value = true
  error.value = null
  try {
    await new Promise(r => setTimeout(r, 500))
  } catch (err) {
    console.error('Error fetching machinery:', err)
    error.value = 'Failed to load machinery. Please try again.'
  } finally {
    loading.value = false
  }
}

const toggleFilters = () => {
  showFilters.value = !showFilters.value
}

const applyFilters = () => {
  currentPage.value = 1
  toggleFilters()
}

const clearFilters = () => {
  filters.value = {
    type: '',
    availability: '',
    condition: '',
    priceMin: 0,
    priceMax: 100000
  }
  currentPage.value = 1
}

const toggleListType = () => {
  viewType.value = viewType.value === 'grid' ? 'list' : 'grid'
}

const capitalize = (str) => {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
}

const viewDetails = (equipment) => {
  selectedEquipment.value = equipment
}

const requestEquipment = (equipment) => {
  alert(`Inquiry sent for ${equipment.name}. Owner will contact you soon.`)
  selectedEquipment.value = null
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  fetchMachinery()
})
</script>

<style scoped>
.machinery-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.machinery-container {
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
  border-top-color: #7c3aed;
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
.machinery-content {
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
  background: #7c3aed;
  color: white;
}

.btn-apply:hover {
  background: #6d28d9;
}

.btn-clear {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.btn-clear:hover {
  background: #e5e7eb;
}

/* Equipment Container */
.equipment-container {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 20px;
}

.view-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.view-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.equipment-card {
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

.view-list .equipment-card {
  flex-direction: row;
  height: auto;
}

.equipment-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.equipment-image {
  position: relative;
  overflow: hidden;
  background: #f3f4f6;
  height: 220px;
  flex-shrink: 0;
}

.view-list .equipment-image {
  width: 280px;
  height: 200px;
}

.equipment-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.equipment-card:hover .equipment-image img {
  transform: scale(1.05);
}

.condition-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.condition-badge.new {
  background: #dbeafe;
  color: #1e40af;
}

.condition-badge.used {
  background: #fed7aa;
  color: #92400e;
}

.condition-badge.refurbished {
  background: #d1fae5;
  color: #065f46;
}

.equipment-info {
  padding: 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.equipment-name {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
  line-height: 1.4;
}

.equipment-type {
  font-size: 13px;
  font-weight: 600;
  color: #7c3aed;
  margin: 0;
}

.equipment-description {
  font-size: 13px;
  color: #9ca3af;
  margin: 0;
  line-height: 1.4;
}

.equipment-specs {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  font-size: 12px;
  color: #6b7280;
}

.spec-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.equipment-pricing {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
}

.price-option {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
}

.price-label {
  color: #6b7280;
  font-weight: 600;
}

.price-value {
  color: #7c3aed;
  font-weight: 700;
  font-size: 16px;
}

.equipment-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-top: auto;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.availability-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.status-buy {
  background: #dbeafe;
  color: #1e40af;
}

.status-rent {
  background: #fce7f3;
  color: #be185d;
}

.status-both {
  background: #d1fae5;
  color: #065f46;
}

.btn-details {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: #7c3aed;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-details:hover {
  background: #6d28d9;
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
  border-color: #7c3aed;
  color: #7c3aed;
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
  max-width: 800px;
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
  color: #7c3aed;
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

.pricing-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 12px;
  background: #ede9fe;
  border-radius: 6px;
  margin-top: 16px;
}

.price-block {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.price-block .label {
  font-size: 14px;
  font-weight: 600;
  color: #4b5563;
}

.price-block .price {
  font-size: 18px;
  font-weight: 700;
  color: #7c3aed;
}

.features-section {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e5e7eb;
}

.features-section h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.features-list {
  margin: 0;
  padding-left: 20px;
}

.features-list li {
  color: #4b5563;
  font-size: 13px;
  line-height: 1.6;
  margin-bottom: 8px;
}

.contact-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.contact-section h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.owner-contact {
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
}

.owner-contact p {
  margin: 8px 0;
  font-size: 13px;
  color: #4b5563;
}

.owner-contact strong {
  color: #1f2937;
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
  background: #7c3aed;
  color: white;
}

.btn-primary:hover {
  background: #6d28d9;
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
  .machinery-container {
    margin-left: 0;
  }

  .view-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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

  .view-grid {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }

  .view-list .equipment-card {
    flex-direction: column;
  }

  .view-list .equipment-image {
    width: 100%;
    height: 200px;
  }
}

@media (max-width: 480px) {
  .view-grid {
    grid-template-columns: 1fr;
  }

  .filters-content {
    grid-template-columns: 1fr;
  }
}
</style>
