<template>
  <div class="buyer-layout">
    <!-- Sidebar -->
    <BuyerSidebar @logout="handleLogout" />
    
    <!-- Main Content -->
    <div class="wishlist-container">
    <!-- Header Section -->
    <div class="wishlist-header">
      <div>
        <h1 class="page-title">My Wishlist</h1>
        <p class="page-subtitle">Items saved for later</p>
      </div>
      <div class="header-actions" v-if="buyerStore.wishlist.length > 0">
        <button @click="clearAllWishlist" class="btn-outline">Clear All</button>
      </div>
    </div>

    <!-- Stats Section -->
    <div v-if="buyerStore.wishlist.length > 0" class="stats-section">
      <div class="stat-card">
        <div class="stat-value">{{ buyerStore.wishlist.length }}</div>
        <div class="stat-label">Items in Wishlist</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">Ksh {{ totalPrice }}</div>
        <div class="stat-label">Total Value</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ inStockCount }}</div>
        <div class="stat-label">In Stock</div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="buyerStore.wishlist.length === 0" class="empty-state">
      <div class="empty-icon">
        <i class="fas fa-heart-broken"></i>
      </div>
      <h2>Your wishlist is empty</h2>
      <p>Start adding items to save them for later</p>
      <router-link to="/buyer/marketplace" class="btn-primary">
        <i class="fas fa-shopping-bag"></i> Continue Shopping
      </router-link>
    </div>

    <!-- Wishlist Items -->
    <div v-else class="wishlist-items">
      <!-- Filter/Sort Bar -->
      <div class="filter-bar">
        <div class="filter-group">
          <label>Sort by:</label>
          <select v-model="sortBy" class="filter-select">
            <option value="newest">Newest First</option>
            <option value="price-low">Price: Low to High</option>
            <option value="price-high">Price: High to Low</option>
            <option value="name">Name (A-Z)</option>
          </select>
        </div>
        <div class="filter-group">
          <label>
            <input type="checkbox" v-model="showOnlyInStock" />
            In Stock Only
          </label>
        </div>
      </div>

      <!-- Items Grid -->
      <div class="items-grid">
        <div 
          v-for="item in filteredAndSortedWishlist" 
          :key="item.id" 
          class="wishlist-card"
          :class="{ 'out-of-stock': !item.in_stock }"
        >
          <!-- Product Image -->
          <div class="product-image">
            <img 
              v-if="item.image_url" 
              :src="item.image_url" 
              :alt="item.product_name"
              class="image"
            />
            <div v-else class="no-image">
              <i class="fas fa-image"></i>
            </div>
            
            <!-- Stock Badge -->
            <div class="stock-badge" :class="item.in_stock ? 'in-stock' : 'out-of-stock'">
              {{ item.in_stock ? 'In Stock' : 'Out of Stock' }}
            </div>

            <!-- Remove Button -->
            <button 
              @click="removeFromWishlist(item.id)" 
              class="btn-remove"
              title="Remove from wishlist"
            >
              <i class="fas fa-trash"></i>
            </button>
          </div>

          <!-- Product Info -->
          <div class="product-info">
            <h3 class="product-name">{{ item.product_name }}</h3>
            
            <p v-if="item.seller_name" class="seller-name">
              <i class="fas fa-store"></i> {{ item.seller_name }}
            </p>

            <div class="product-meta">
              <span v-if="item.quantity_available" class="quantity">
                {{ item.quantity_available }} available
              </span>
              <span v-if="item.rating" class="rating">
                <i class="fas fa-star"></i> {{ item.rating }}
              </span>
            </div>

            <!-- Price Section -->
            <div class="price-section">
              <div class="price-display">
                <span class="current-price">Ksh {{ item.price }}</span>
                <span v-if="item.original_price && item.original_price > item.price" class="original-price">
                  Ksh {{ item.original_price }}
                </span>
              </div>
              <div v-if="item.original_price && item.original_price > item.price" class="discount">
                -{{ calculateDiscount(item.price, item.original_price) }}%
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
              <button 
                @click="addToCart(item)" 
                class="btn-add-to-cart"
                :disabled="!item.in_stock"
              >
                <i class="fas fa-shopping-cart"></i>
                {{ item.in_stock ? 'Add to Cart' : 'Unavailable' }}
              </button>
              <button 
                @click="viewProduct(item.id)" 
                class="btn-view-product"
              >
                <i class="fas fa-eye"></i> View
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="buyerStore.wishlist.length > 0" class="bulk-actions">
        <button @click="addAllToCart" class="btn-primary btn-large">
          <i class="fas fa-shopping-bag"></i>
          Add All to Cart
        </button>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useBuyerStore } from '@/stores/buyerStore'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { onMounted, computed, ref } from 'vue'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

const buyerStore = useBuyerStore()
const authStore = useAuthStore()
const router = useRouter()
const sortBy = ref('newest')
const showOnlyInStock = ref(false)

onMounted(async () => {
  try {
    await buyerStore.fetchWishlist()
  } catch (error) {
    console.error('Failed to load wishlist:', error)
  }
})

// Computed properties
const filteredAndSortedWishlist = computed(() => {
  let items = [...buyerStore.wishlist]

  // Filter
  if (showOnlyInStock.value) {
    items = items.filter(item => item.in_stock !== false)
  }

  // Sort
  switch (sortBy.value) {
    case 'price-low':
      items.sort((a, b) => (a.price || 0) - (b.price || 0))
      break
    case 'price-high':
      items.sort((a, b) => (b.price || 0) - (a.price || 0))
      break
    case 'name':
      items.sort((a, b) => a.product_name.localeCompare(b.product_name))
      break
    case 'newest':
    default:
      // Keep original order (newest first)
      break
  }

  return items
})

const totalPrice = computed(() => {
  return buyerStore.wishlist.reduce((sum, item) => sum + (item.price || 0), 0).toLocaleString()
})

const inStockCount = computed(() => {
  return buyerStore.wishlist.filter(item => item.in_stock !== false).length
})

// Methods
const removeFromWishlist = async (id: number) => {
  try {
    await buyerStore.removeFromWishlist(id)
  } catch (error) {
    console.error('Failed to remove from wishlist:', error)
  }
}

const clearAllWishlist = async () => {
  if (confirm('Are you sure you want to clear your entire wishlist?')) {
    try {
      for (const item of buyerStore.wishlist) {
        await buyerStore.removeFromWishlist(item.id)
      }
    } catch (error) {
      console.error('Failed to clear wishlist:', error)
    }
  }
}

const addToCart = async (item: any) => {
  if (item.in_stock !== false) {
    try {
      await buyerStore.addToCart({
        product_id: item.product_id || item.id,
        quantity: 1,
        price: item.price
      })
      // Optionally show a toast notification
      console.log('Added to cart:', item.product_name)
    } catch (error) {
      console.error('Failed to add to cart:', error)
    }
  }
}

const addAllToCart = async () => {
  try {
    const inStockItems = buyerStore.wishlist.filter(item => item.in_stock !== false)
    for (const item of inStockItems) {
      await addToCart(item)
    }
  } catch (error) {
    console.error('Failed to add items to cart:', error)
  }
}

const viewProduct = (id: number) => {
  router.push(`/buyer/products/${id}`)
}

const calculateDiscount = (currentPrice: number, originalPrice: number): number => {
  return Math.round(((originalPrice - currentPrice) / originalPrice) * 100)
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
/* Layout */
.buyer-layout {
  display: flex;
  height: 100vh;
}

.wishlist-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f5f5;
  min-height: 100vh;
  padding: 40px 20px;
  max-width: 1200px;
  width: 100%;
}

/* Header */
.wishlist-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 40px;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.page-subtitle {
  font-size: 16px;
  color: #6b7280;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

/* Stats Section */
.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 24px;
  border-radius: 12px;
  color: white;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  opacity: 0.9;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  background: #f9fafb;
  border-radius: 12px;
  border: 2px dashed #d1d5db;
}

.empty-icon {
  font-size: 64px;
  color: #d1d5db;
  margin-bottom: 20px;
}

.empty-state h2 {
  font-size: 24px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.empty-state p {
  font-size: 16px;
  color: #6b7280;
  margin: 0 0 24px 0;
}

/* Filter Bar */
.filter-bar {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
  align-items: center;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-group label {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: all 0.3s;
}

.filter-select:hover,
.filter-select:focus {
  border-color: #3b82f6;
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-group input[type="checkbox"] {
  cursor: pointer;
  width: 18px;
  height: 18px;
}

/* Items Grid */
.items-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 40px;
}

/* Wishlist Card */
.wishlist-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
}

.wishlist-card:hover {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  transform: translateY(-4px);
}

.wishlist-card.out-of-stock {
  opacity: 0.6;
}

/* Product Image */
.product-image {
  position: relative;
  width: 100%;
  padding-bottom: 100%;
  background: #f3f4f6;
  overflow: hidden;
}

.image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: #d1d5db;
}

/* Stock Badge */
.stock-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.stock-badge.in-stock {
  background: #d1fae5;
  color: #065f46;
}

.stock-badge.out-of-stock {
  background: #fee2e2;
  color: #991b1b;
}

/* Remove Button */
.btn-remove {
  position: absolute;
  bottom: 12px;
  right: 12px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 0.3s;
  opacity: 0;
}

.wishlist-card:hover .btn-remove {
  opacity: 1;
}

.btn-remove:hover {
  background: #dc2626;
}

/* Product Info */
.product-info {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}

.product-name {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.seller-name {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.product-meta {
  display: flex;
  gap: 12px;
  font-size: 12px;
  color: #6b7280;
}

.quantity {
  display: flex;
  align-items: center;
  gap: 4px;
}

.rating {
  display: flex;
  align-items: center;
  gap: 4px;
  color: #f59e0b;
}

/* Price Section */
.price-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.price-display {
  display: flex;
  align-items: center;
  gap: 8px;
}

.current-price {
  font-size: 20px;
  font-weight: 700;
  color: #10b981;
}

.original-price {
  font-size: 14px;
  color: #9ca3af;
  text-decoration: line-through;
}

.discount {
  background: #fef3c7;
  color: #92400e;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

/* Action Buttons */
.action-buttons {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
}

.btn-add-to-cart,
.btn-view-product {
  padding: 10px 12px;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  white-space: nowrap;
}

.btn-add-to-cart {
  background: #3b82f6;
  color: white;
}

.btn-add-to-cart:hover:not(:disabled) {
  background: #2563eb;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-add-to-cart:disabled {
  background: #d1d5db;
  cursor: not-allowed;
  color: #6b7280;
}

.btn-view-product {
  background: #f0f0f0;
  color: #1f2937;
  border: 1px solid #e5e7eb;
}

.btn-view-product:hover {
  background: #e5e7eb;
  border-color: #d1d5db;
}

/* Bulk Actions */
.bulk-actions {
  display: flex;
  justify-content: center;
  padding: 20px 0;
}

.btn-large {
  padding: 12px 48px;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Button Styles */
.btn-primary {
  background: #3b82f6;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover {
  background: #2563eb;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-outline {
  background: white;
  color: #1f2937;
  padding: 10px 20px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

/* Responsive */
@media (max-width: 768px) {
  .wishlist-container {
    padding: 20px 16px;
  }

  .wishlist-header {
    flex-direction: column;
    gap: 16px;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions .btn-outline {
    width: 100%;
  }

  .page-title {
    font-size: 24px;
  }

  .stats-section {
    grid-template-columns: 1fr;
  }

  .items-grid {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
  }

  .filter-bar {
    flex-direction: column;
  }

  .filter-group {
    width: 100%;
  }

  .filter-select {
    width: 100%;
  }

  .action-buttons {
    grid-template-columns: 1fr;
  }

  .btn-view-product {
    display: none;
  }

  .btn-add-to-cart {
    grid-column: 1 / -1;
  }

  .empty-state {
    padding: 60px 20px;
  }

  .empty-icon {
    font-size: 48px;
  }

  .empty-state h2 {
    font-size: 20px;
  }
}

@media (max-width: 480px) {
  .items-grid {
    grid-template-columns: 1fr;
  }

  .page-title {
    font-size: 20px;
  }

  .stats-section {
    grid-template-columns: 1fr;
  }

  .bulk-actions {
    padding: 16px 0;
  }

  .btn-large {
    width: 100%;
    justify-content: center;
  }
}
</style>
