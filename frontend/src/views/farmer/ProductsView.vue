<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Product Listing</h1>
          <p>Manage and sell your agricultural products</p>
        </div>
        <button class="btn-primary btn-large" @click="openAddProductDialog">
          <i class="fas fa-plus"></i> Add Product
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon total">
            <i class="fas fa-box"></i>
          </div>
          <div class="stat-content">
            <h3>Total Products</h3>
            <p class="stat-value">{{ products.length }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon active">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-content">
            <h3>Active Products</h3>
            <p class="stat-value">{{ activeProductsCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon inventory">
            <i class="fas fa-warehouse"></i>
          </div>
          <div class="stat-content">
            <h3>Total Inventory</h3>
            <p class="stat-value">{{ totalInventory }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon revenue">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="stat-content">
            <h3>Total Value</h3>
            <p class="stat-value">${{ totalValue }}</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="controls-section">
        <div class="filter-group">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search products..."
            class="search-input"
          />
          <select v-model="categoryFilter" class="category-select">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </select>
          <select v-model="statusFilter" class="status-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="sold_out">Sold Out</option>
          </select>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="products-section">
        <div v-if="loading" class="loading-state">
          <p><i class="fas fa-spinner fa-spin"></i> Loading products...</p>
        </div>

        <div v-else-if="filteredProducts.length === 0" class="empty-state">
          <i class="fas fa-box-open"></i>
          <h3>No products found</h3>
          <p>{{ products.length === 0 ? 'Start by adding your first product' : 'No products match your filters' }}</p>
        </div>

        <div v-else class="products-grid">
          <div v-for="product in filteredProducts" :key="product.id" class="product-card">
            <!-- Product Image -->
            <div class="product-image-container">
              <img v-if="product.image" :src="product.image" :alt="product.name" class="product-image">
              <div v-else class="product-image-placeholder">
                <i class="fas fa-image"></i>
              </div>
              <span class="status-badge" :class="`status-${product.status}`">
                {{ formatStatus(product.status) }}
              </span>
            </div>

            <!-- Product Info -->
            <div class="product-info">
              <h3>{{ product.name }}</h3>
              <p class="description">{{ truncateText(product.description, 80) }}</p>
              
              <div class="product-details">
                <div class="detail-item">
                  <span class="label">Price:</span>
                  <span class="value">${{ formatNumber(product.price) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Quantity:</span>
                  <span class="value">{{ product.quantity_available }} {{ product.unit }}</span>
                </div>
                <div v-if="product.category" class="detail-item">
                  <span class="label">Category:</span>
                  <span class="value">{{ product.category }}</span>
                </div>
              </div>

              <!-- Inventory Bar -->
              <div class="inventory-bar-container">
                <div class="inventory-label">
                  <span>Stock Level</span>
                  <span>{{ product.quantity_available }} available</span>
                </div>
                <div class="inventory-bar">
                  <div class="inventory-fill" :style="{ width: getInventoryPercentage(product) + '%' }"></div>
                </div>
              </div>

              <!-- Actions -->
              <div class="product-actions">
                <button class="btn-small btn-edit" @click="editProduct(product)" title="Edit">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn-small btn-delete" @click="deleteProduct(product.id)" title="Delete">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Add/Edit Product Modal -->
      <div v-if="showAddProductDialog" class="modal-overlay" @click="closeAddProductDialog">
        <div class="modal-dialog" @click.stop>
          <div class="modal-header">
            <h2>{{ editingProduct ? 'Edit Product' : 'Add New Product' }}</h2>
            <button class="close-btn" @click="closeAddProductDialog">&times;</button>
          </div>

          <div class="modal-content">
            <form @submit.prevent="submitProductForm">
              <!-- Harvest Selection -->
              <div class="form-group">
                <label for="product-harvest">Select Harvest *</label>
                <select id="product-harvest" v-model="productForm.harvest_id" required>
                  <option value="">Choose a harvest</option>
                  <option v-for="harvest in harvests" :key="harvest.id" :value="harvest.id">
                    {{ harvest.crop?.crop_type }} ({{ harvest.quantity }} {{ harvest.unit }})
                  </option>
                </select>
                <span v-if="formErrors.harvest_id" class="error-text">{{ formErrors.harvest_id }}</span>
              </div>

              <!-- Product Name -->
              <div class="form-group">
                <label for="product-name">Product Name *</label>
                <input
                  id="product-name"
                  v-model="productForm.name"
                  type="text"
                  placeholder="e.g., Organic Maize"
                  required
                />
                <span v-if="formErrors.name" class="error-text">{{ formErrors.name }}</span>
              </div>

              <!-- Description -->
              <div class="form-group">
                <label for="product-description">Description *</label>
                <textarea
                  id="product-description"
                  v-model="productForm.description"
                  placeholder="Describe your product..."
                  rows="3"
                  required
                ></textarea>
                <span v-if="formErrors.description" class="error-text">{{ formErrors.description }}</span>
              </div>

              <!-- Category -->
              <div class="form-group">
                <label for="product-category">Category *</label>
                <input
                  id="product-category"
                  v-model="productForm.category"
                  type="text"
                  placeholder="e.g., Grains, Vegetables, Fruits"
                  required
                />
                <span v-if="formErrors.category" class="error-text">{{ formErrors.category }}</span>
              </div>

              <!-- Price and Quantity Row -->
              <div class="form-row">
                <div class="form-group">
                  <label for="product-price">Unit Price *</label>
                  <div class="input-with-currency">
                    <span class="currency">$</span>
                    <input
                      id="product-price"
                      v-model.number="productForm.price"
                      type="number"
                      placeholder="0.00"
                      step="0.01"
                      min="0"
                      required
                    />
                  </div>
                  <span v-if="formErrors.price" class="error-text">{{ formErrors.price }}</span>
                </div>

                <div class="form-group">
                  <label for="product-quantity">Quantity Available *</label>
                  <div class="input-with-unit">
                    <input
                      id="product-quantity"
                      v-model.number="productForm.quantity_available"
                      type="number"
                      placeholder="100"
                      step="0.1"
                      min="0"
                      required
                    />
                    <select v-model="productForm.unit" class="unit-select">
                      <option value="kg">kg</option>
                      <option value="tonnes">tonnes</option>
                      <option value="bags">bags</option>
                      <option value="bundles">bundles</option>
                      <option value="pieces">pieces</option>
                    </select>
                  </div>
                  <span v-if="formErrors.quantity_available" class="error-text">{{ formErrors.quantity_available }}</span>
                </div>
              </div>

              <!-- Image Upload -->
              <div class="form-group">
                <label for="product-image">Product Image</label>
                <div class="image-upload">
                  <input
                    id="product-image"
                    type="file"
                    accept="image/*"
                    @change="onImageSelected"
                    class="image-input"
                  />
                  <label for="product-image" class="upload-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Click to upload image</span>
                  </label>
                  <div v-if="imagePreview" class="image-preview">
                    <img :src="imagePreview" :alt="productForm.name">
                  </div>
                </div>
                <span v-if="formErrors.image" class="error-text">{{ formErrors.image }}</span>
              </div>

              <!-- Quality Grade -->
              <div class="form-group">
                <label for="product-quality">Quality Grade</label>
                <select id="product-quality" v-model="productForm.quality_grade">
                  <option value="">Select quality grade</option>
                  <option value="A">Grade A (Premium)</option>
                  <option value="B">Grade B (Good)</option>
                  <option value="C">Grade C (Standard)</option>
                  <option value="D">Grade D (Below Standard)</option>
                </select>
              </div>

              <!-- Status -->
              <div class="form-group">
                <label for="product-status">Status</label>
                <select id="product-status" v-model="productForm.status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="sold_out">Sold Out</option>
                </select>
              </div>

              <!-- Form Actions -->
              <div class="form-actions">
                <button type="button" class="btn-secondary" @click="closeAddProductDialog">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="submittingProduct">
                  {{ submittingProduct ? 'Saving...' : (editingProduct ? 'Update Product' : 'Add Product') }}
                </button>
              </div>
            </form>
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
const products = ref([])
const harvests = ref([])
const loading = ref(true)
const searchQuery = ref('')
const categoryFilter = ref('')
const statusFilter = ref('')
const showAddProductDialog = ref(false)
const editingProduct = ref(null)
const submittingProduct = ref(false)
const formErrors = ref({})
const imagePreview = ref(null)
const imageFile = ref(null)

// Product Form
const productForm = ref({
  harvest_id: '',
  name: '',
  description: '',
  category: '',
  price: '',
  quantity_available: '',
  unit: 'kg',
  quality_grade: '',
  status: 'active',
})

// Computed Properties
const filteredProducts = computed(() => {
  return products.value.filter(product => {
    const matchesSearch = 
      product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      product.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesCategory = !categoryFilter.value || product.category === categoryFilter.value
    const matchesStatus = !statusFilter.value || product.status === statusFilter.value
    
    return matchesSearch && matchesCategory && matchesStatus
  })
})

const categories = computed(() => {
  const cats = new Set()
  products.value.forEach(p => {
    if (p.category) cats.add(p.category)
  })
  return Array.from(cats).sort()
})

const activeProductsCount = computed(() => {
  return products.value.filter(p => p.status === 'active').length
})

const totalInventory = computed(() => {
  return products.value.reduce((sum, p) => sum + p.quantity_available, 0).toFixed(1)
})

const totalValue = computed(() => {
  return products.value.reduce((sum, p) => sum + (p.price * p.quantity_available), 0).toFixed(2)
})

// Lifecycle
onMounted(async () => {
  await fetchProducts()
  await fetchHarvests()
})

// API Functions
const fetchProducts = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/farmer/products', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    
    if (!response.ok) throw new Error('Failed to fetch products')
    
    const data = await response.json()
    products.value = data.data?.data || data.data || []
  } catch (error) {
    console.error('Error fetching products:', error)
  } finally {
    loading.value = false
  }
}

const fetchHarvests = async () => {
  try {
    const response = await fetch('/api/farmer/harvests', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    
    if (!response.ok) throw new Error('Failed to fetch harvests')
    
    const data = await response.json()
    harvests.value = data.data?.data || data.data || []
  } catch (error) {
    console.error('Error fetching harvests:', error)
  }
}

// Modal Functions
const openAddProductDialog = () => {
  resetProductForm()
  editingProduct.value = null
  imagePreview.value = null
  imageFile.value = null
  showAddProductDialog.value = true
}

const closeAddProductDialog = () => {
  showAddProductDialog.value = false
  resetProductForm()
  imagePreview.value = null
  imageFile.value = null
}

const resetProductForm = () => {
  productForm.value = {
    harvest_id: '',
    name: '',
    description: '',
    category: '',
    price: '',
    quantity_available: '',
    unit: 'kg',
    quality_grade: '',
    status: 'active',
  }
  formErrors.value = {}
}

const editProduct = (product) => {
  editingProduct.value = product
  productForm.value = {
    harvest_id: product.harvest_id || '',
    name: product.name,
    description: product.description,
    category: product.category || '',
    price: product.price,
    quantity_available: product.quantity_available,
    unit: product.unit || 'kg',
    quality_grade: product.quality_grade || '',
    status: product.status,
  }
  if (product.image) {
    imagePreview.value = product.image
  }
  showAddProductDialog.value = true
}

const onImageSelected = (event) => {
  const file = event.target.files?.[0]
  if (file) {
    imageFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target?.result
    }
    reader.readAsDataURL(file)
  }
}

// Form Submission
const submitProductForm = async () => {
  try {
    submittingProduct.value = true
    formErrors.value = {}

    // Validate required fields
    if (!productForm.value.name) {
      formErrors.value.name = 'Product name is required'
      submittingProduct.value = false
      return
    }

    if (!productForm.value.description) {
      formErrors.value.description = 'Description is required'
      submittingProduct.value = false
      return
    }

    if (!productForm.value.price || productForm.value.price <= 0) {
      formErrors.value.price = 'Please enter a valid price'
      submittingProduct.value = false
      return
    }

    if (!productForm.value.quantity_available || productForm.value.quantity_available < 0) {
      formErrors.value.quantity_available = 'Please enter a valid quantity'
      submittingProduct.value = false
      return
    }

    const formData = new FormData()
    formData.append('harvest_id', productForm.value.harvest_id)
    formData.append('name', productForm.value.name)
    formData.append('description', productForm.value.description)
    formData.append('category', productForm.value.category)
    formData.append('price', productForm.value.price)
    formData.append('quantity_available', productForm.value.quantity_available)
    formData.append('unit', productForm.value.unit)
    formData.append('quality_grade', productForm.value.quality_grade)
    formData.append('status', productForm.value.status)
    
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }

    const url = editingProduct.value 
      ? `/api/farmer/products/${editingProduct.value.id}`
      : '/api/farmer/products'
    
    const method = editingProduct.value ? 'POST' : 'POST'

    const response = await fetch(url, {
      method,
      headers: {
        'Authorization': `Bearer ${auth.token}`,
      },
      body: formData,
    })

    const data = await response.json()

    if (!response.ok) {
      if (data.errors) {
        formErrors.value = data.errors
      } else {
        formErrors.value = { general: data.message || 'An error occurred' }
      }
      return
    }

    showAddProductDialog.value = false
    resetProductForm()
    await fetchProducts()
  } catch (error) {
    console.error('Error submitting product form:', error)
    formErrors.value = { general: 'An error occurred while saving the product' }
  } finally {
    submittingProduct.value = false
  }
}

const deleteProduct = async (productId) => {
  if (!confirm('Are you sure you want to delete this product?')) return

  try {
    const response = await fetch(`/api/farmer/products/${productId}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })

    if (!response.ok) throw new Error('Failed to delete product')

    await fetchProducts()
  } catch (error) {
    console.error('Error deleting product:', error)
    alert('Failed to delete product')
  }
}

// Utility Functions
const formatStatus = (status) => {
  const statuses = {
    active: 'Active',
    inactive: 'Inactive',
    sold_out: 'Sold Out'
  }
  return statuses[status] || status
}

const formatNumber = (num) => {
  return Number(num).toLocaleString()
}

const truncateText = (text, length) => {
  return text.length > length ? text.substring(0, length) + '...' : text
}

const getInventoryPercentage = (product) => {
  const maxQuantity = 1000
  const percentage = (product.quantity_available / maxQuantity) * 100
  return Math.min(percentage, 100)
}

// Logout
const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout {
  display: flex;
  height: 100vh;
}

.farmer-page {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f5f5;
  padding: 20px;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  gap: 20px;
}

.header-content h1 {
  font-size: 32px;
  font-weight: bold;
  color: #333;
  margin: 0 0 5px 0;
}

.header-content p {
  color: #666;
  margin: 0;
}

.btn-large {
  padding: 12px 24px;
  font-size: 16px;
  white-space: nowrap;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  gap: 15px;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.stat-icon.total {
  background-color: #10b981;
}

.stat-icon.active {
  background-color: #3b82f6;
}

.stat-icon.inventory {
  background-color: #f59e0b;
}

.stat-icon.revenue {
  background-color: #8b5cf6;
}

.stat-content h3 {
  margin: 0;
  font-size: 12px;
  color: #666;
  text-transform: uppercase;
  font-weight: 600;
}

.stat-value {
  margin: 5px 0 0 0;
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

/* Controls */
.controls-section {
  background: white;
  border-radius: 8px;
  padding: 15px 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-group {
  display: flex;
  gap: 15px;
  align-items: center;
}

.search-input,
.category-select,
.status-select {
  padding: 10px 15px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.search-input:focus,
.category-select:focus,
.status-select:focus {
  outline: none;
  border-color: #10b981;
}

.search-input {
  flex: 1;
  min-width: 250px;
}

/* Products Section */
.products-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.empty-state i {
  font-size: 48px;
  color: #d1d5db;
  margin-bottom: 15px;
  display: block;
}

.empty-state h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.product-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
}

.product-card:hover {
  border-color: #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
  transform: translateY(-4px);
}

.product-image-container {
  position: relative;
  width: 100%;
  height: 200px;
  background: white;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #e5e7eb 0%, #f3f4f6 100%);
  font-size: 48px;
  color: #d1d5db;
}

.status-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-badge.status-active {
  background: #dcfce7;
  color: #166534;
}

.status-badge.status-inactive {
  background: #fee2e2;
  color: #991b1b;
}

.status-badge.status-sold_out {
  background: #f3e8ff;
  color: #6b21a8;
}

.product-info {
  padding: 15px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.product-info h3 {
  margin: 0 0 5px 0;
  font-size: 16px;
  color: #333;
  font-weight: 600;
}

.description {
  color: #666;
  font-size: 13px;
  margin: 0 0 12px 0;
  line-height: 1.4;
}

.product-details {
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  margin-bottom: 6px;
}

.detail-item .label {
  color: #666;
  font-weight: 500;
}

.detail-item .value {
  color: #333;
  font-weight: 600;
}

.inventory-bar-container {
  margin-bottom: 12px;
}

.inventory-label {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #666;
  margin-bottom: 4px;
}

.inventory-bar {
  width: 100%;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.inventory-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #059669);
  transition: width 0.3s ease;
}

.product-actions {
  display: flex;
  gap: 8px;
}

.btn-small {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}

.btn-edit {
  background-color: #e0e7ff;
  color: #3b82f6;
}

.btn-edit:hover {
  background-color: #3b82f6;
  color: white;
}

.btn-delete {
  background-color: #fee2e2;
  color: #ef4444;
}

.btn-delete:hover {
  background-color: #ef4444;
  color: white;
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
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover:not(:disabled) {
  background-color: #059669;
}

.btn-primary:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
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

/* Modal */
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
  max-width: 700px;
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

/* Form */
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

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.input-with-currency,
.input-with-unit {
  display: flex;
  align-items: center;
}

.currency {
  padding: 10px 12px;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-right: none;
  border-radius: 4px 0 0 4px;
  font-weight: 600;
  color: #333;
}

.input-with-currency input {
  border-radius: 0 4px 4px 0;
  margin: 0;
}

.unit-select {
  width: 80px;
  margin-left: 0;
}

.image-upload {
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  background: #f9fafb;
}

.image-upload:hover {
  border-color: #10b981;
  background: #f0fdf4;
}

.image-input {
  display: none;
}

.upload-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  color: #666;
  font-size: 14px;
  font-weight: 500;
}

.upload-label i {
  font-size: 32px;
  color: #10b981;
}

.image-preview {
  margin-top: 15px;
  border-radius: 4px;
  overflow: hidden;
  max-width: 200px;
}

.image-preview img {
  width: 100%;
  height: auto;
  display: block;
  border-radius: 4px;
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

/* Responsive */
@media (max-width: 768px) {
  .farmer-page {
    margin-left: 0;
    padding: 15px;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-large {
    width: 100%;
    text-align: center;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }

  .filter-group {
    flex-direction: column;
  }

  .search-input {
    width: 100%;
  }

  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .modal-dialog {
    width: 95%;
    max-height: 95vh;
  }
}
</style>
