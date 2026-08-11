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
    description: 'Imp