<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Buy Farm Inputs</h1>
          <p>Purchase agricultural inputs, seeds, fertilizers, and farm supplies</p>
        </div>
        <div class="header-right">
          <div class="cart-badge">
            <i class="fas fa-shopping-cart"></i>
            <span class="badge-count">{{ cartCount }}</span>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon products">
            <i class="fas fa-boxes"></i>
          </div>
          <div class="stat-content">
            <h3>Available Products</h3>
            <p class="stat-value">{{ inputs.length }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon categories">
            <i class="fas fa-layer-group"></i>
          </div>
          <div class="stat-content">
            <h3>Categories</h3>
            <p class="stat-value">{{ categories.length }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon cart">
            <i class="fas fa-shopping-bag"></i>
          </div>
          <div class="stat-content">
            <h3>Cart Items</h3>
            <p class="stat-value">{{ cartCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon total">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="stat-content">
            <h3>Cart Total</h3>
            <p class="stat-value">ETB {{ formatNumber(cartTotal) }}</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="controls-section">
        <div class="filter-group">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search farm inputs..."
            class="search-input"
          />
          <select v-model="categoryFilter" class="category-select">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </select>
          <button v-if="cartCount > 0" class="btn-checkout" @click="showCartModal = true">
            <i class="fas fa-shopping-cart"></i> View Cart ({{ cartCount }})
          </button>
        </div>
      </div>

      <!-- Inputs Grid -->
      <div class="inputs-section">
        <div v-if="loading" class="loading-state">
          <p><i class="fas fa-spinner fa-spin"></i> Loading farm inputs...</p>
        </div>

        <div v-else-if="filteredInputs.length === 0" class="empty-state">
          <i class="fas fa-inbox"></i>
          <h3>No inputs found</h3>
          <p>{{ inputs.length === 0 ? 'No farm inputs available' : 'No inputs match your filters' }}</p>
        </div>

        <div v-else class="inputs-grid">
          <div v-for="input in filteredInputs" :key="input.id" class="input-card">
            <div class="input-image">
              <i class="fas fa-seedling"></i>
            </div>

            <div class="input-header">
              <h3>{{ input.name }}</h3>
              <span class="badge">{{ input.category }}</span>
            </div>

            <p class="description">{{ input.description }}</p>

            <div class="input-details">
              <div class="detail-row">
                <span class="label">Price:</span>
                <span class="price">ETB {{ formatNumber(input.price) }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Stock:</span>
                <span class="stock" :class="{ 'low-stock': input.stock < 10 }">
                  {{ input.stock }} units
                </span>
              </div>
              <div class="detail-row">
                <span class="label">Supplier:</span>
                <span class="supplier">{{ input.supplier }}</span>
              </div>
            </div>

            <div class="quantity-control">
              <button @click="decreaseQuantity(input.id)" class="qty-btn">
                <i class="fas fa-minus"></i>
              </button>
              <input
                v-model.number="quantities[input.id]"
                type="number"
                min="0"
                max="100"
                class="qty-input"
              />
              <button @click="increaseQuantity(input.id)" class="qty-btn">
                <i class="fas fa-plus"></i>
              </button>
            </div>

            <button
              @click="addToCart(input)"
              :disabled="quantities[input.id] === 0 || !quantities[input.id]"
              class="btn-add-cart"
            >
              <i class="fas fa-shopping-cart"></i> Add to Cart
            </button>
          </div>
        </div>
      </div>

      <!-- Cart Modal -->
      <div v-if="showCartModal" class="modal-overlay" @click="closeCartModal">
        <div class="modal-dialog modal-large" @click.stop>
          <div class="modal-header">
            <h2>Shopping Cart</h2>
            <button class="close-btn" @click="closeCartModal">&times;</button>
          </div>

          <div class="modal-content">
            <div v-if="cart.length === 0" class="empty-cart">
              <i class="fas fa-shopping-cart"></i>
              <p>Your cart is empty</p>
            </div>

            <div v-else>
              <div class="cart-items">
                <div v-for="item in cart" :key="item.id" class="cart-item">
                  <div class="item-info">
                    <h4>{{ item.name }}</h4>
                    <p class="item-category">{{ item.category }}</p>
                  </div>

                  <div class="item-quantity">
                    <span class="label">Qty:</span>
                    <button @click="decreaseCartQty(item.id)" class="qty-btn small">-</button>
                    <span class="qty-value">{{ item.quantity }}</span>
                    <button @click="increaseCartQty(item.id)" class="qty-btn small">+</button>
                  </div>

                  <div class="item-price">
                    <span class="price">ETB {{ formatNumber(item.price * item.quantity) }}</span>
                  </div>

                  <button @click="removeFromCart(item.id)" class="btn-remove">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>

              <div class="cart-summary">
                <div class="summary-row">
                  <span>Subtotal:</span>
                  <span>ETB {{ formatNumber(cartSubtotal) }}</span>
                </div>
                <div class="summary-row">
                  <span>Tax (10%):</span>
                  <span>ETB {{ formatNumber(cartTax) }}</span>
                </div>
                <div class="summary-row total">
                  <span>Total:</span>
                  <span>ETB {{ formatNumber(cartTotal) }}</span>
                </div>
              </div>

              <div class="cart-actions">
                <button class="btn-secondary" @click="closeCartModal">Continue Shopping</button>
                <button class="btn-primary" @click="checkout">
                  <i class="fas fa-credit-card"></i> Checkout
                </button>
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
const inputs = ref([])
const cart = ref([])
const loading = ref(true)
const searchQuery = ref('')
const categoryFilter = ref('')
const quantities = ref({})
const showCartModal = ref(false)

// Mock data - Replace with API call
const mockInputs = [
  {
    id: 1,
    name: 'Hybrid Maize Seeds',
    category: 'Seeds',
    description: 'High-yield hybrid maize seeds suitable for various climates',
    price: 1500,
    stock: 50,
    supplier: 'Agricultural Supplies Ltd'
  },
  {
    id: 2,
    name: 'NPK Fertilizer 10-10-10',
    category: 'Fertilizers',
    description: 'Balanced NPK fertilizer for crop growth',
    price: 800,
    stock: 200,
    supplier: 'Fertilizer Distribution Co'
  },
  {
    id: 3,
    name: 'Improved Wheat Seeds',
    category: 'Seeds',
    description: 'Disease-resistant wheat seeds with high yield',
    price: 1200,
    stock: 30,
    supplier: 'Seed Production Center'
  },
  {
    id: 4,
    name: 'Organic Fertilizer',
    category: 'Fertilizers',
    description: 'Eco-friendly organic fertilizer for sustainable farming',
    price: 600,
    stock: 150,
    supplier: 'Green Agriculture Ltd'
  },
  {
    id: 5,
    name: 'Pesticide Spray',
    category: 'Pesticides',
    description: 'Effective pesticide for crop protection',
    price: 500,
    stock: 100,
    supplier: 'Pest Control Solutions'
  },
  {
    id: 6,
    name: 'Farm Shovel',
    category: 'Tools',
    description: 'Durable stainless steel farm shovel',
    price: 300,
    stock: 80,
    supplier: 'Farm Equipment Store'
  },
  {
    id: 7,
    name: 'Tomato Seeds',
    category: 'Seeds',
    description: 'High-quality tomato seeds for vegetable farming',
    price: 1000,
    stock: 40,
    supplier: 'Vegetable Seed Suppliers'
  },
  {
    id: 8,
    name: 'Soil Amendment',
    category: 'Soil Care',
    description: 'Improve soil quality and nutrient content',
    price: 700,
    stock: 120,
    supplier: 'Soil Solutions Inc'
  },
]

// Initialize quantities
onMounted(async () => {
  // For now, use mock data. Replace with API call later
  inputs.value = mockInputs
  mockInputs.forEach(input => {
    quantities.value[input.id] = 1
  })
  loading.value = false
})

// Computed
const categories = computed(() => {
  return [...new Set(inputs.value.map(input => input.category))]
})

const filteredInputs = computed(() => {
  return inputs.value.filter(input => {
    const matchesSearch = input.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         input.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = !categoryFilter.value || input.category === categoryFilter.value
    return matchesSearch && matchesCategory
  })
})

const cartCount = computed(() => {
  return cart.value.reduce((sum, item) => sum + item.quantity, 0)
})

const cartSubtotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const cartTax = computed(() => {
  return cartSubtotal.value * 0.1 // 10% tax
})

const cartTotal = computed(() => {
  return cartSubtotal.value + cartTax.value
})

// Cart Functions
const addToCart = (input) => {
  const quantity = quantities.value[input.id] || 1
  if (quantity <= 0) return

  const existingItem = cart.value.find(item => item.id === input.id)
  if (existingItem) {
    existingItem.quantity += quantity
  } else {
    cart.value.push({
      ...input,
      quantity: quantity
    })
  }

  quantities.value[input.id] = 1
}

const removeFromCart = (inputId) => {
  cart.value = cart.value.filter(item => item.id !== inputId)
}

const increaseCartQty = (inputId) => {
  const item = cart.value.find(item => item.id === inputId)
  if (item) {
    item.quantity++
  }
}

const decreaseCartQty = (inputId) => {
  const item = cart.value.find(item => item.id === inputId)
  if (item && item.quantity > 1) {
    item.quantity--
  }
}

const increaseQuantity = (inputId) => {
  if (!quantities.value[inputId]) quantities.value[inputId] = 1
  quantities.value[inputId]++
}

const decreaseQuantity = (inputId) => {
  if (quantities.value[inputId] > 1) {
    quantities.value[inputId]--
  } else {
    quantities.value[inputId] = 1
  }
}

const checkout = async () => {
  if (cart.value.length === 0) {
    alert('Cart is empty')
    return
  }

  try {
    // TODO: Implement checkout API call
    alert(`Order placed successfully! Total: ETB ${formatNumber(cartTotal.value)}`)
    cart.value = []
    showCartModal.value = false
  } catch (error) {
    console.error('Error during checkout:', error)
    alert('Failed to place order')
  }
}

const closeCartModal = () => {
  showCartModal.value = false
}

// Utility Functions
const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num || 0)
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout { display: flex; height: 100vh; }
.farmer-page { margin-left: 260px; flex: 1; overflow-y: auto; background-color: #f5f5f5; padding: 20px; }

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

.header-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.cart-badge {
  position: relative;
  font-size: 24px;
  color: #10b981;
  cursor: pointer;
}

.badge-count {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #ef4444;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
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

.stat-card:hover { transform: translateY(-2px); }

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

.stat-icon.products { background-color: #10b981; }
.stat-icon.categories { background-color: #f59e0b; }
.stat-icon.cart { background-color: #8b5cf6; }
.stat-icon.total { background-color: #3b82f6; }

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
  flex-wrap: wrap;
}

.search-input,
.category-select {
  padding: 10px 15px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.search-input:focus,
.category-select:focus {
  outline: none;
  border-color: #10b981;
}

.search-input { flex: 1; min-width: 250px; }

.btn-checkout {
  background-color: #10b981;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
}

.btn-checkout:hover {
  background-color: #059669;
}

/* Inputs Section */
.inputs-section {
  margin-bottom: 20px;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  background: white;
  border-radius: 8px;
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

/* Inputs Grid */
.inputs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.input-card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.input-card:hover {
  border-color: #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
  transform: translateY(-2px);
}

.input-image {
  width: 100%;
  height: 120px;
  background: #f3f4f6;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: #10b981;
}

.input-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 10px;
}

.input-header h3 {
  margin: 0;
  color: #333;
  font-size: 16px;
  flex: 1;
}

.badge {
  background: #ecfdf5;
  color: #10b981;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}

.description {
  color: #666;
  font-size: 13px;
  line-height: 1.4;
  margin: 0;
}

.input-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
}

.detail-row .label {
  color: #666;
  font-weight: 500;
}

.detail-row .price {
  color: #10b981;
  font-weight: 700;
  font-size: 14px;
}

.detail-row .stock {
  color: #333;
  font-weight: 600;
}

.detail-row .stock.low-stock {
  color: #f59e0b;
}

.detail-row .supplier {
  color: #666;
  font-size: 12px;
}

.quantity-control {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f9fafb;
  padding: 8px;
  border-radius: 4px;
}

.qty-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #d1d5db;
  background: white;
  cursor: pointer;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.qty-btn:hover {
  background: #10b981;
  color: white;
  border-color: #10b981;
}

.qty-input {
  flex: 1;
  padding: 6px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  text-align: center;
  font-size: 13px;
}

.btn-add-cart {
  background-color: #10b981;
  color: white;
  padding: 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.btn-add-cart:hover:not(:disabled) {
  background-color: #059669;
}

.btn-add-cart:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-dialog.modal-large {
  max-width: 800px;
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

.empty-cart {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.empty-cart i {
  font-size: 48px;
  color: #d1d5db;
  margin-bottom: 15px;
  display: block;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
  gap: 12px;
}

.item-info {
  flex: 1;
}

.item-info h4 {
  margin: 0 0 4px 0;
  color: #333;
  font-size: 14px;
}

.item-category {
  margin: 0;
  color: #666;
  font-size: 12px;
}

.item-quantity {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
}

.qty-btn.small {
  width: 24px;
  height: 24px;
  font-size: 12px;
  padding: 0;
}

.qty-value {
  min-width: 30px;
  text-align: center;
}

.item-price {
  min-width: 100px;
  text-align: right;
}

.item-price .price {
  color: #10b981;
  font-weight: 700;
  font-size: 14px;
}

.btn-remove {
  background: #fee2e2;
  color: #ef4444;
  border: none;
  padding: 6px 8px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-remove:hover {
  background: #ef4444;
  color: white;
}

.cart-summary {
  background: #f9fafb;
  padding: 15px;
  border-radius: 6px;
  margin-bottom: 20px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  font-size: 14px;
  border-bottom: 1px solid #e5e7eb;
}

.summary-row.total {
  border-bottom: none;
  font-weight: 700;
  font-size: 16px;
  color: #10b981;
  margin-top: 8px;
  padding-top: 12px;
  border-top: 2px solid #e5e7eb;
}

.cart-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.btn-primary {
  background-color: #10b981;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover {
  background-color: #059669;
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

/* Responsive */
@media (max-width: 768px) {
  .farmer-page { margin-left: 0; }
  .page-header { flex-direction: column; align-items: stretch; }
  .header-right { justify-content: flex-end; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .filter-group { flex-direction: column; }
  .search-input { width: 100%; }
  .inputs-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
  .modal-dialog { width: 95%; max-height: 95vh; }
  .cart-item { flex-direction: column; align-items: flex-start; }
  .item-price { width: 100%; text-align: left; }
}
</style>
