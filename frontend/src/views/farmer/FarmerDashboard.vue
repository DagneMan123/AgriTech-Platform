<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
      <h1>Farmer Dashboard</h1>
      <p>Manage your farms, crops, and agricultural business</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
      <div class="summary-card">
        <div class="card-icon farm">
          <i class="fas fa-home"></i>
        </div>
        <div class="card-content">
          <h3>Total Farms</h3>
          <p class="card-value">{{ dashboard?.summary.total_farms }}</p>
          <p class="card-sub">{{ dashboard?.summary.total_farm_area_hectares }} hectares</p>
        </div>
      </div>

      <div class="summary-card">
        <div class="card-icon crops">
          <i class="fas fa-leaf"></i>
        </div>
        <div class="card-content">
          <h3>Active Crops</h3>
          <p class="card-value">{{ dashboard?.summary.active_crops }}</p>
          <p class="card-sub">{{ dashboard?.summary.total_crops }} total</p>
        </div>
      </div>

      <div class="summary-card">
        <div class="card-icon products">
          <i class="fas fa-box"></i>
        </div>
        <div class="card-content">
          <h3>Active Products</h3>
          <p class="card-value">{{ dashboard?.summary.active_products }}</p>
          <p class="card-sub">{{ dashboard?.summary.total_products }} total</p>
        </div>
      </div>

      <div class="summary-card">
        <div class="card-icon orders">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="card-content">
          <h3>Pending Orders</h3>
          <p class="card-value">{{ dashboard?.summary.pending_orders }}</p>
          <p class="card-sub">{{ dashboard?.summary.total_orders }} total</p>
        </div>
      </div>

      <div class="summary-card">
        <div class="card-icon revenue">
          <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="card-content">
          <h3>Total Sales</h3>
          <p class="card-value">${{ formatNumber(dashboard?.summary.total_sales) }}</p>
          <p class="card-sub">Avg: ${{ dashboard?.summary.average_order_value | 0 }}</p>
        </div>
      </div>

      <div class="summary-card">
        <div class="card-icon consultations">
          <i class="fas fa-comments"></i>
        </div>
        <div class="card-content">
          <h3>Pending Consultations</h3>
          <p class="card-value">{{ dashboard?.summary.pending_consultations }}</p>
          <p class="card-sub">{{ dashboard?.summary.total_consultations }} total</p>
        </div>
      </div>
    </div>

    <!-- Main Tabs -->
    <div class="dashboard-tabs">
      <div class="tab-buttons">
        <button 
          v-for="tab in tabs" 
          :key="tab"
          :class="['tab-btn', { active: activeTab === tab }]"
          @click="activeTab = tab"
        >
          {{ formatTabName(tab) }}
        </button>
      </div>

      <!-- Farm Management Tab -->
      <div v-show="activeTab === 'farms'" class="tab-content">
        <div class="farms-grid">
          <div v-for="farm in dashboard?.farms || []" :key="farm.id" class="farm-card">
            <div class="farm-header">
              <h3>{{ farm.name }}</h3>
              <span class="badge">{{ farm.size_hectares }} ha</span>
            </div>
            <div class="farm-details">
              <p><strong>Location:</strong> {{ farm.location }}</p>
              <p><strong>Soil:</strong> {{ farm.soil_type }}</p>
              <p><strong>Crops:</strong> {{ farm.crops_count }}</p>
            </div>
            <div class="farm-actions">
              <button class="btn-small btn-edit">Edit</button>
              <button class="btn-small btn-view">View</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Crop Management Tab -->
      <div v-show="activeTab === 'crops'" class="tab-content">
        <div class="section-header">
          <h2>Crop Management</h2>
          <button class="btn-primary" @click="showAddCropDialog = true">+ Plant Crop</button>
        </div>

        <div class="crops-table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Crop</th>
                <th>Farm</th>
                <th>Status</th>
                <th>Planted</th>
                <th>Expected Harvest</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="crop in dashboard?.crops || []" :key="crop.id">
                <td>{{ crop.name }}</td>
                <td>{{ crop.farm?.name }}</td>
                <td><span class="status-badge" :class="`status-${crop.status}`">{{ crop.status }}</span></td>
                <td>{{ formatDate(crop.planted_date) }}</td>
                <td>{{ formatDate(crop.expected_harvest_date) }}</td>
                <td class="action-buttons">
                  <button class="btn-small btn-edit">Edit</button>
                  <button class="btn-small btn-view">View</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Products Tab -->
      <div v-show="activeTab === 'products'" class="tab-content">
        <div class="section-header">
          <h2>Product Management</h2>
          <button class="btn-primary" @click="showAddProductDialog = true">+ List Product</button>
        </div>

        <div class="products-grid">
          <div v-for="product in dashboard?.recent_products || []" :key="product.id" class="product-card">
            <img v-if="product.image" :src="product.image" :alt="product.name" class="product-image">
            <div v-else class="product-image-placeholder">
              <i class="fas fa-image"></i>
            </div>
            <div class="product-info">
              <h4>{{ product.name }}</h4>
              <p class="price">${{ formatNumber(product.price) }}</p>
              <p class="stock">Stock: {{ product.quantity }}</p>
              <span class="badge">{{ product.status }}</span>
            </div>
            <div class="product-actions">
              <button class="btn-small btn-edit">Edit</button>
              <button class="btn-small btn-delete">Delete</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Orders Tab -->
      <div v-show="activeTab === 'orders'" class="tab-content">
        <div class="section-header">
          <h2>Customer Orders</h2>
          <div class="filter-controls">
            <select v-model="orderFilter" class="filter-select">
              <option value="">All Orders</option>
              <option value="pending">Pending</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>

        <div class="orders-table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Buyer</th>
                <th>Items</th>
                <th>Status</th>
                <th>Total</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in dashboard?.recent_orders || []" :key="order.id">
                <td>#{{ order.id }}</td>
                <td>{{ order.buyer?.name }}</td>
                <td>{{ order.items?.length }}</td>
                <td><span class="status-badge" :class="`status-${order.status}`">{{ order.status }}</span></td>
                <td>${{ formatNumber(order.total_amount) }}</td>
                <td>{{ formatDate(order.created_at) }}</td>
                <td class="action-buttons">
                  <button class="btn-small btn-view">View</button>
                  <button v-if="order.status === 'pending'" class="btn-small btn-accept">Accept</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Weather & Market Tab -->
      <div v-show="activeTab === 'weather'" class="tab-content">
        <div class="section-header">
          <h2>Weather & Market Information</h2>
        </div>

        <div class="weather-market-grid">
          <div class="weather-card">
            <h3>Current Weather</h3>
            <div class="weather-info">
              <div class="weather-icon">
                <i class="fas fa-cloud-sun"></i>
              </div>
              <div class="weather-details">
                <p class="temperature">24°C</p>
                <p class="condition">Partly Cloudy</p>
                <p class="humidity">Humidity: 65%</p>
                <p class="wind">Wind: 12 km/h</p>
              </div>
            </div>
          </div>

          <div class="market-card">
            <h3>Market Prices</h3>
            <div class="prices-list">
              <div class="price-item">
                <span class="crop-name">Maize</span>
                <span class="price-value">$450/bag</span>
              </div>
              <div class="price-item">
                <span class="crop-name">Wheat</span>
                <span class="price-value">$520/bag</span>
              </div>
              <div class="price-item">
                <span class="crop-name">Tomatoes</span>
                <span class="price-value">$380/crate</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Consultations Tab -->
      <div v-show="activeTab === 'consultations'" class="tab-content">
        <div class="section-header">
          <h2>Expert Consultations</h2>
          <button class="btn-primary" @click="showRequestConsultationDialog = true">+ Request Consultation</button>
        </div>

        <div class="consultations-list">
          <div class="consultation-item">
            <h4>Crop Disease Identification</h4>
            <p>Dr. John Smith</p>
            <span class="status-badge status-pending">Pending Response</span>
          </div>
        </div>
      </div>

      <!-- Sales Reports Tab -->
      <div v-show="activeTab === 'reports'" class="tab-content">
        <div class="section-header">
          <h2>Sales Reports</h2>
          <div class="period-selector">
            <button 
              v-for="period in [7, 30, 90]"
              :key="period"
              :class="['period-btn', { active: selectedPeriod === period }]"
              @click="selectedPeriod = period"
            >
              {{ period }} Days
            </button>
          </div>
        </div>

        <div class="reports-grid">
          <div class="report-card">
            <h3>Total Sales</h3>
            <p class="big-number">${{ formatNumber(dashboard?.summary.total_sales) }}</p>
          </div>
          <div class="report-card">
            <h3>Orders Completed</h3>
            <p class="big-number">{{ dashboard?.summary.completed_orders }}</p>
          </div>
          <div class="report-card">
            <h3>Top Product</h3>
            <p class="big-number">Organic Maize</p>
          </div>
          <div class="report-card">
            <h3>Avg Order Value</h3>
            <p class="big-number">${{ formatNumber(dashboard?.summary.average_order_value) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-section">
      <div class="recent-card">
        <h2>Recent Harvests</h2>
        <div class="list-items">
          <div v-for="harvest in dashboard?.recent_harvests || []" :key="harvest.id" class="list-item">
            <span class="item-name">{{ harvest.crop?.name }}</span>
            <span class="item-quantity">{{ harvest.quantity }} units</span>
            <span class="item-date">{{ formatDate(harvest.harvest_date) }}</span>
          </div>
        </div>
      </div>

      <div class="recent-card">
        <h2>Pending Loans</h2>
        <div class="list-items">
          <div class="list-item">
            <span class="item-name">Seasonal Funding</span>
            <span class="item-amount">$2,000</span>
            <span class="status-badge status-pending">Pending</span>
          </div>
        </div>
      </div>
    </div>
    </div>

    <!-- Plant Crop Modal -->
    <div v-if="showAddCropDialog" class="modal-overlay" @click="closeCropDialog">
      <div class="modal-dialog" @click.stop>
        <div class="modal-header">
          <h2>Plant New Crop</h2>
          <button class="close-btn" @click="closeCropDialog">&times;</button>
        </div>

        <div class="modal-content">
          <form @submit.prevent="submitCropForm">
            <!-- Farm Selection -->
            <div class="form-group">
              <label for="crop-farm">Select Farm *</label>
              <select id="crop-farm" v-model="cropForm.farm_id" required>
                <option value="">Select a farm</option>
                <option v-for="farm in dashboard?.farms || []" :key="farm.id" :value="farm.id">
                  {{ farm.name }} ({{ farm.size_hectares }} ha)
                </option>
              </select>
              <span v-if="cropErrors.farm_id" class="error-text">{{ cropErrors.farm_id }}</span>
            </div>

            <!-- Crop Type -->
            <div class="form-group">
              <label for="crop-type">Crop Type *</label>
              <input
                id="crop-type"
                v-model="cropForm.crop_type"
                type="text"
                placeholder="e.g., Maize, Wheat, Tomato"
                required
              />
              <span v-if="cropErrors.crop_type" class="error-text">{{ cropErrors.crop_type }}</span>
            </div>

            <!-- Variety -->
            <div class="form-group">
              <label for="crop-variety">Variety *</label>
              <input
                id="crop-variety"
                v-model="cropForm.variety"
                type="text"
                placeholder="e.g., DK777, PAN12, Roma"
                required
              />
              <span v-if="cropErrors.variety" class="error-text">{{ cropErrors.variety }}</span>
            </div>

            <!-- Planting Date -->
            <div class="form-group">
              <label for="crop-planting">Planting Date *</label>
              <input
                id="crop-planting"
                v-model="cropForm.planting_date"
                type="date"
                required
              />
              <span v-if="cropErrors.planting_date" class="error-text">{{ cropErrors.planting_date }}</span>
            </div>

            <!-- Expected Harvest Date -->
            <div class="form-group">
              <label for="crop-harvest">Expected Harvest Date *</label>
              <input
                id="crop-harvest"
                v-model="cropForm.expected_harvest_date"
                type="date"
                required
              />
              <span v-if="cropErrors.expected_harvest_date" class="error-text">{{ cropErrors.expected_harvest_date }}</span>
            </div>

            <!-- Area in Hectares -->
            <div class="form-group">
              <label for="crop-area">Area (Hectares) *</label>
              <input
                id="crop-area"
                v-model.number="cropForm.area_hectares"
                type="number"
                placeholder="e.g., 2.5"
                step="0.1"
                min="0.1"
                required
              />
              <span v-if="cropErrors.area_hectares" class="error-text">{{ cropErrors.area_hectares }}</span>
            </div>

            <!-- Expected Yield -->
            <div class="form-group">
              <label for="crop-yield">Expected Yield (kg)</label>
              <input
                id="crop-yield"
                v-model.number="cropForm.expected_yield_kg"
                type="number"
                placeholder="e.g., 5000"
                step="0.01"
                min="0"
              />
              <span v-if="cropErrors.expected_yield_kg" class="error-text">{{ cropErrors.expected_yield_kg }}</span>
            </div>

            <!-- Notes -->
            <div class="form-group">
              <label for="crop-notes">Notes</label>
              <textarea
                id="crop-notes"
                v-model="cropForm.notes"
                placeholder="Additional notes about this crop"
                rows="3"
              ></textarea>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="closeCropDialog">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="submittingCrop">
                {{ submittingCrop ? 'Planting...' : 'Plant Crop' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const auth = useAuthStore()
const router = useRouter()
const activeTab = ref('farms')
const tabs = ['farms', 'crops', 'products', 'orders', 'weather', 'consultations', 'reports']

const dashboard = ref(null)
const orderFilter = ref('')
const selectedPeriod = ref(30)

const showAddCropDialog = ref(false)
const showAddProductDialog = ref(false)
const showRequestConsultationDialog = ref(false)

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  try {
    const res = await fetch('/api/farmer/dashboard', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    dashboard.value = await res.json()
  } catch (error) {
    console.error('Error fetching dashboard:', error)
  }
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatTabName = (tab) => {
  const names = {
    farms: 'Farm Management',
    crops: 'Crop Management',
    products: 'Product Management',
    orders: 'Customer Orders',
    weather: 'Weather & Market',
    consultations: 'Consultations',
    reports: 'Sales Reports'
  }
  return names[tab] || tab
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

// Crop Form State
const submittingCrop = ref(false)
const cropErrors = ref({})
const cropForm = ref({
  farm_id: '',
  crop_type: '',
  variety: '',
  planting_date: '',
  expected_harvest_date: '',
  area_hectares: '',
  expected_yield_kg: '',
  notes: '',
})

// Close crop dialog
const closeCropDialog = () => {
  showAddCropDialog.value = false
  resetCropForm()
}

// Reset crop form
const resetCropForm = () => {
  cropForm.value = {
    farm_id: '',
    crop_type: '',
    variety: '',
    planting_date: '',
    expected_harvest_date: '',
    area_hectares: '',
    expected_yield_kg: '',
    notes: '',
  }
  cropErrors.value = {}
}

// Submit crop form
const submitCropForm = async () => {
  try {
    submittingCrop.value = true
    cropErrors.value = {}

    // Validate dates
    if (cropForm.value.planting_date && cropForm.value.expected_harvest_date) {
      const plantingDate = new Date(cropForm.value.planting_date)
      const harvestDate = new Date(cropForm.value.expected_harvest_date)
      if (harvestDate <= plantingDate) {
        cropErrors.value.expected_harvest_date = 'Harvest date must be after planting date'
        submittingCrop.value = false
        return
      }
    }

    const payload = {
      ...cropForm.value,
      farm_id: parseInt(cropForm.value.farm_id),
      area_hectares: parseFloat(cropForm.value.area_hectares),
      expected_yield_kg: cropForm.value.expected_yield_kg ? parseFloat(cropForm.value.expected_yield_kg) : null,
    }

    const response = await fetch('/api/farmer/crops', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        cropErrors.value = data.errors
      } else {
        cropErrors.value = { general: data.message || 'An error occurred' }
      }
      return
    }

    // Success
    showAddCropDialog.value = false
    resetCropForm()
    await fetchDashboardData()

    console.log('Crop planted successfully!')
  } catch (error) {
    console.error('Error submitting crop form:', error)
    cropErrors.value = { general: 'An error occurred while planting the crop' }
  } finally {
    submittingCrop.value = false
  }
}
</script>

<style scoped>
.farmer-layout {
  display: flex;
  height: 100vh;
}

.farmer-dashboard {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f5f5;
  min-height: 100vh;
  padding: 20px;
}

.dashboard-header {
  margin-bottom: 30px;
}

.dashboard-header h1 {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.dashboard-header p {
  color: #666;
}

/* Summary Grid */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.summary-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.card-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.card-icon.farm {
  background-color: #8b5cf6;
}

.card-icon.crops {
  background-color: #10b981;
}

.card-icon.products {
  background-color: #f59e0b;
}

.card-icon.orders {
  background-color: #3b82f6;
}

.card-icon.revenue {
  background-color: #ef4444;
}

.card-icon.consultations {
  background-color: #ec4899;
}

.card-content h3 {
  font-size: 12px;
  color: #666;
  margin-bottom: 5px;
  text-transform: uppercase;
  font-weight: 600;
}

.card-value {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.card-sub {
  font-size: 12px;
  color: #999;
}

/* Tabs */
.dashboard-tabs {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 30px;
}

.tab-buttons {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
  overflow-x: auto;
}

.tab-btn {
  flex: 1;
  padding: 15px 20px;
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
  font-weight: 500;
  transition: all 0.3s;
  border-bottom: 3px solid transparent;
  white-space: nowrap;
}

.tab-btn:hover {
  color: #10b981;
}

.tab-btn.active {
  color: #10b981;
  border-bottom-color: #10b981;
}

.tab-content {
  padding: 25px;
}

/* Grids */
.farms-grid,
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.farm-card,
.product-card {
  background-color: #f9fafb;
  border-radius: 8px;
  padding: 15px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s;
}

.farm-card:hover,
.product-card:hover {
  border-color: #10b981;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);
}

.farm-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.farm-header h3 {
  margin: 0;
  color: #333;
}

.farm-details p {
  margin: 5px 0;
  font-size: 14px;
  color: #666;
}

.farm-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

/* Products */
.product-image {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 4px;
  margin-bottom: 10px;
}

.product-image-placeholder {
  width: 100%;
  height: 150px;
  background-color: #e5e7eb;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  color: #d1d5db;
  margin-bottom: 10px;
}

.product-info h4 {
  margin: 0 0 5px 0;
  color: #333;
}

.product-info .price {
  color: #10b981;
  font-weight: bold;
  font-size: 16px;
}

.product-info .stock {
  font-size: 12px;
  color: #666;
}

/* Tables */
.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background-color: #f9fafb;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

/* Buttons */
.btn-primary {
  background-color: #10b981;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover {
  background-color: #059669;
}

.btn-small {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-edit {
  background-color: #3b82f6;
  color: white;
}

.btn-edit:hover {
  background-color: #2563eb;
}

.btn-view {
  background-color: #8b5cf6;
  color: white;
}

.btn-view:hover {
  background-color: #7c3aed;
}

.btn-accept {
  background-color: #10b981;
  color: white;
}

.btn-accept:hover {
  background-color: #059669;
}

.btn-delete {
  background-color: #ef4444;
  color: white;
}

.btn-delete:hover {
  background-color: #dc2626;
}

/* Status Badges */
.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  background-color: #dbeafe;
  color: #1e40af;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.status-completed {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-cancelled {
  background-color: #fee2e2;
  color: #991b1b;
}

/* Weather & Market */
.weather-market-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.weather-card,
.market-card {
  background-color: #f9fafb;
  border-radius: 8px;
  padding: 20px;
  border: 1px solid #e5e7eb;
}

.weather-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.weather-icon {
  font-size: 48px;
  color: #f59e0b;
}

.weather-details p {
  margin: 5px 0;
  color: #666;
}

.temperature {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.prices-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.price-item {
  display: flex;
  justify-content: space-between;
  padding: 10px;
  background-color: white;
  border-radius: 4px;
}

.crop-name {
  font-weight: 600;
  color: #333;
}

.price-value {
  color: #10b981;
  font-weight: bold;
}

/* Reports */
.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
}

.report-card {
  background-color: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.report-card h3 {
  color: #666;
  margin-bottom: 10px;
  font-size: 14px;
}

.big-number {
  font-size: 28px;
  font-weight: bold;
  color: #333;
}

/* Recent Sections */
.recent-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 30px;
}

.recent-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recent-card h2 {
  font-size: 18px;
  margin-bottom: 15px;
  color: #333;
}

.list-items {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background-color: #f9fafb;
  border-radius: 4px;
}

.item-name {
  font-weight: 600;
  color: #333;
}

.item-quantity,
.item-amount,
.item-date {
  font-size: 14px;
  color: #666;
}

/* Responsive */
@media (max-width: 768px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .tab-buttons {
    flex-wrap: wrap;
  }

  .tab-btn {
    flex: 0 1 auto;
    padding: 12px 15px;
  }

  .farms-grid,
  .products-grid {
    grid-template-columns: 1fr;
  }

  .weather-market-grid {
    grid-template-columns: 1fr;
  }

  .recent-section {
    grid-template-columns: 1fr;
  }
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-dialog {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.modal-header h2 {
  margin: 0;
  color: #333;
  font-size: 20px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: #333;
}

.modal-content {
  padding: 25px;
}

/* Form Styles */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-group textarea {
  resize: vertical;
}

.error-text {
  display: block;
  color: #ef4444;
  font-size: 12px;
  margin-top: 5px;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 25px;
  padding-top: 15px;
  border-top: 1px solid #e5e7eb;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background-color: #4b5563;
}

.btn-primary:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}
</style>
