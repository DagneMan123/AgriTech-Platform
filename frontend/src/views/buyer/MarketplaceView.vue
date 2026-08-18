<template>
  <div class="buyer-layout">
    <!-- Sidebar -->
    <BuyerSidebar @logout="handleLogout" />

    <!-- Main Content -->
    <div class="marketplace-container">
      <!-- Header Section -->
      <div class="marketplace-header">
        <div>
          <h1 class="page-title">Marketplace</h1>
          <p class="page-subtitle">Browse and discover fresh agricultural products</p>
        </div>
        <div class="header-actions">
          <button @click="showFilters = !showFilters" class="btn-secondary">
            <i class="fas fa-sliders-h"></i> Filters
          </button>
          <button @click="refreshProducts" class="btn-secondary">
            <i class="fas fa-sync-alt"></i> Refresh
          </button>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="stats-section">
        <div class="stat-card">
          <div class="stat-icon total">
            <i class="fas fa-box"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ totalProducts }}</div>
            <div class="stat-label">Total Products</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon pending">
            <i class="fas fa-store"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ totalSuppliers }}</div>
            <div class="stat-label">Suppliers</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon shipped">
            <i class="fas fa-list"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ totalCategories }}</div>
            <div class="stat-label">Categories</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon delivered">
            <i class="fas fa-heart"></i>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ wishlistCount }}</div>
            <div class="stat-label">Wishlist Items</div>
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <div v-if="showFilters" class="filter-section">
        <div class="filter-group-row">
          <div class="filter-group">
            <label>Search:</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search products..."
              class="filter-input"
              @input="handleSearch"
            />
          </div>
          <div class="filter-group">
            <label>Category:</label>
            <select v-model="selectedCategory" class="filter-select" @change="handleSearch">
              <option value="">All Categories</option>
              <option value="vegetables">Vegetables</option>
              <option value="grains">Grains</option>
              <option value="fruits">Fruits</option>
              <option value="dairy">Dairy</option>
              <option value="meat">Meat</option>
            </select>
          </div>
          <div class="filter-group">
            <label>Price Range:</label>
            <select v-model="priceRange" class="filter-select" @change="handleSearch">
              <option value="">All Prices</option>
              <option value="0-500">Under Ksh 500</option>
              <option value="500-1000">Ksh 500 - 1,000</option>
              <option value="1000-5000">Ksh 1,000 - 5,000</option>
              <option value="5000+">Above Ksh 5,000</option>
            </select>
          </div>
          <div class="filter-group">
            <label>Sort by:</label>
            <select v-model="sortBy" class="filter-select" @change="handleSearch">
              <option value="newest">Newest First</option>
              <option value="price-low">Price: Low to High</option>
              <option value="price-high">Price: High to Low</option>
              <option value="popular">Most Popular</option>
            </select>
          </div>
        </div>
        <div class="filter-actions">
          <button @click="applyFilters" class="btn-primary">Apply Filters</button>
          <button @click="clearFilters" class="btn-secondary">Clear Filters</button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && marketplaceStore.products.length === 0" class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-inbox"></i>
        </div>
        <h2>No products found</h2>
        <p>Try adjusting your filters or search terms</p>
        <button @click="clearFilters" class="btn-primary">
          <i class="fas fa-redo"></i> Reset Filters
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading products...</p>
      </div>

      <!-- Products Grid -->
      <div v-if="!loading && marketplaceStore.products.length > 0" class="products-section">
        <div class="products-header">
          <p class="product-count">Showing {{ marketplaceStore.products.length }} product(s)</p>
        </div>
        <div class="products-grid">
          <div
            v-for="product in marketplaceStore.products"
            :key="product.id"
            class="product-card"
          >
            <!-- Product Image -->
            <div class="product-image">
              <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="image"
              />
              <div v-else class="no-image">
                <i class="fas fa-image"></i>
              </div>
              <button
                @click.stop="toggleWishlist(product)"
                :class="['wishlist-btn', { active: isInWishlist(product.id) }]"
              >
                <i :class="['fas', isInWishlist(product.id) ? 'fa-heart' : 'fa-heart']"></i>
              </button>
              <div class="product-badge" v-if="product.rating">
                <i class="fas fa-star"></i> {{ product.rating }}
              </div>
            </div>

            <!-- Product Details -->
            <div class="product-details">
              <h3 class="product-name">{{ product.name }}</h3>
              <p class="product-supplier">
                <i class="fas fa-store"></i> {{ product.farmer_name || 'Supplier' }}
              </p>
              <p class="product-category">{{ product.category || 'Uncategorized' }}</p>

              <!-- Product Price & Rating -->
              <div class="product-footer">
                <div class="price-section">
                  <span class="product-price">Ksh {{ formatNumber(product.price || 0) }}</span>
                  <span v-if="product.original_price" class="original-price">
                    Ksh {{ formatNumber(product.original_price) }}
                  </span>
                </div>
                <div class="rating-section" v-if="product.rating">
                  <span class="rating">{{ product.rating }}/5</span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="product-actions">
                <button
                  @click.stop="viewProduct(product.id)"
                  class="btn-secondary btn-block"
                >
                  <i class="fas fa-eye"></i> View Details
                </button>
                <button
                  @click.stop="addToCart(product)"
                  class="btn-primary btn-block"
                >
                  <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recommended Products Section -->
      <div v-if="!loading && recommendedProducts.length > 0" class="recommended-section">
        <h2 class="section-title">
          <i class="fas fa-star"></i> Recommended For You
        </h2>
        <div class="products-grid">
          <div
            v-for="product in recommendedProducts.slice(0, 4)"
            :key="product.id"
            class="product-card"
          >
            <!-- Product Image -->
            <div class="product-image">
              <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="image"
              />
              <div v-else class="no-image">
                <i class="fas fa-image"></i>
              </div>
              <button
                @click.stop="toggleWishlist(product)"
                :class="['wishlist-btn', { active: isInWishlist(product.id) }]"
              >
                <i class="fas fa-heart"></i>
              </button>
            </div>

            <!-- Product Details -->
            <div class="product-details">
              <h3 class="product-name">{{ product.name }}</h3>
              <p class="product-supplier">
                <i class="fas fa-store"></i> {{ product.farmer_name || 'Supplier' }}
              </p>

              <!-- Product Price -->
              <div class="product-footer">
                <span class="product-price">Ksh {{ formatNumber(product.price || 0) }}</span>
              </div>

              <!-- Action Buttons -->
              <div class="product-actions">
                <button
                  @click.stop="addToCart(product)"
                  class="btn-primary btn-block"
                >
                  <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketplaceStore } from '@/stores/marketplaceStore'
import { useBuyerStore } from '@/stores/buyerStore'
import { useAuthStore } from '@/stores/authStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
const router = useRouter()
const marketplaceStore = useMarketplaceStore()
const buyerStore = useBuyerStore()
const authStore = useAuthStore()

const loading = ref(false)
const showFilters = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('')
const priceRange = ref('')
const sortBy = ref('newest')

onMounted(async () => {
  loading.value = true
  try {
    await marketplaceStore.fetchProducts()
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    loading.value = false
  }
})

// Computed properties for stats
const totalProducts = computed(() => marketplaceStore.products.length)
const totalSuppliers = computed(() => {
  const suppliers = new Set(marketplaceStore.products.map(p => p.farmer_name))
  return suppliers.size
})
const totalCategories = computed(() => {
  const categories = new Set(marketplaceStore.products.map(p => p.category))
  return categories.size
})
const wishlistCount = computed(() => buyerStore.wishlist?.length || 0)

// Recommended products (random selection)
const recommendedProducts = computed(() => {
  return marketplaceStore.products.slice(0, 8)
})

// Check if product is in wishlist
const isInWishlist = (productId: number) => {
  return buyerStore.wishlist?.some((item: any) => item.product_id === productId) || false
}

// Format number with comma separator
const formatNumber = (num: number) => {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

// Handle search input
const handleSearch = async () => {
  loading.value = true
  try {
    if (searchQuery.value) {
      await marketplaceStore.searchProducts(searchQuery.value, {
        category: selectedCategory.value
      })
    } else {
      await marketplaceStore.fetchProducts(1, 12, {
        category: selectedCategory.value
      })
    }
  } catch (error) {
    console.error('Search failed:', error)
  } finally {
    loading.value = false
  }
}

// Apply filters
const applyFilters = async () => {
  await handleSearch()
}

// Clear filters
const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  priceRange.value = ''
  sortBy.value = 'newest'
  handleSearch()
}

// Refresh products
const refreshProducts = async () => {
  loading.value = true
  try {
    await marketplaceStore.fetchProducts()
  } catch (error) {
    console.error('Failed to refresh products:', error)
  } finally {
    loading.value = false
  }
}

// View product details
const viewProduct = (id: number) => {
  router.push(`/app/buyer/products/${id}`)
}

// Add to cart
const addToCart = async (product: any) => {
  try {
    await buyerStore.addToCart({
      product_id: product.id,
      quantity: 1
    })
    alert('Added to cart')
  } catch (error) {
    console.error('Failed to add to cart:', error)
  }
}

// Toggle wishlist
const toggleWishlist = async (product: any) => {
  try {
    await buyerStore.addToWishlist({ product_id: product.id })
  } catch (error) {
    console.error('Failed to add to wishlist:', error)
  }
}

// Handle logout
const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}
</script>
<style scoped>
/* Layout */
.buyer-layout {
  display: flex;
  width: 100%;
  height: 100vh;
}

.marketplace-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background: linear-gradient(135deg, #f5f7fa 0%, #f8fafb 100%);
  padding: 30px;
}

/* Header */
.marketplace-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 30px;
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.page-subtitle {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

/* Buttons */
.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-secondary:hover {
  color: #1f2937;
  border-color: #9ca3af;
  background: #f9fafb;
}

.btn-block {
  width: 100%;
}

.btn-sm {
  padding: 8px 12px;
  font-size: 13px;
}

/* Stats Section */
.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s;
}

.stat-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.stat-icon.total {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.pending {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-icon.shipped {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-icon.delivered {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
}

.stat-label {
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
}

/* Filter Section */
.filter-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.filter-group-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-group label {
  font-size: 13px;
  font-weight: 600;
  color: #1f2937;
}

.filter-select,
.filter-input {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  color: #1f2937;
  background: white;
  transition: all 0.3s;
}

.filter-select:hover,
.filter-input:hover {
  border-color: #9ca3af;
}

.filter-select:focus,
.filter-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

/* Empty State */
.empty-state {
  background: white;
  border-radius: 12px;
  padding: 60px 20px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.empty-icon {
  font-size: 64px;
  color: #d1d5db;
  margin-bottom: 20px;
}

.empty-state h2 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.empty-state p {
  color: #6b7280;
  margin-bottom: 20px;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-state p {
  color: #6b7280;
  font-size: 14px;
}

/* Products Section */
.products-section {
  margin-bottom: 40px;
}

.products-header {
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.product-count {
  color: #6b7280;
  font-size: 14px;
  margin: 0;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 20px;
}

/* Product Card */
.product-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
}

.product-card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  transform: translateY(-4px);
}

.product-image {
  position: relative;
  width: 100%;
  padding-top: 100%;
  background: #f3f4f6;
  overflow: hidden;
}

.product-image .image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.product-card:hover .product-image .image {
  transform: scale(1.05);
}

.product-image .no-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 36px;
  color: #d1d5db;
}

.wishlist-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #d1d5db;
  transition: all 0.3s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  font-size: 18px;
}

.wishlist-btn:hover {
  color: #ef4444;
  transform: scale(1.1);
}

.wishlist-btn.active {
  color: #ef4444;
  background: #fee2e2;
}

.product-badge {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background: #3b82f6;
  color: white;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.product-details {
  padding: 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.product-name {
  font-size: 15px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-supplier {
  font-size: 12px;
  color: #6b7280;
  margin: 0 0 4px 0;
  display: flex;
  align-items: center;
  gap: 4px;
}

.product-supplier i {
  font-size: 10px;
}

.product-category {
  font-size: 12px;
  color: #9ca3af;
  margin: 4px 0 8px 0;
  text-transform: capitalize;
}

.product-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 12px 0;
  padding-bottom: 12px;
  border-bottom: 1px solid #f3f4f6;
}

.price-section {
  display: flex;
  align-items: center;
  gap: 8px;
}

.product-price {
  font-size: 16px;
  font-weight: 700;
  color: #10b981;
}

.original-price {
  font-size: 12px;
  color: #d1d5db;
  text-decoration: line-through;
}

.rating-section {
  font-size: 12px;
  color: #f59e0b;
  font-weight: 600;
}

.product-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.product-actions button {
  padding: 10px;
  font-size: 13px;
}

/* Recommended Section */
.recommended-section {
  margin-top: 40px;
}

.section-title {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-title i {
  color: #f59e0b;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .marketplace-container {
    padding: 20px;
  }

  .marketplace-header {
    flex-direction: column;
    gap: 16px;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  }
}

@media (max-width: 768px) {
  .marketplace-container {
    margin-left: 0;
    padding: 16px;
  }

  .stats-section {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }

  .stat-card {
    padding: 16px;
  }

  .stat-value {
    font-size: 20px;
  }

  .filter-group-row {
    grid-template-columns: 1fr;
  }

  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
  }

  .product-card {
    border-radius: 8px;
  }

  .product-details {
    padding: 12px;
  }

  .product-name {
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .marketplace-container {
    padding: 12px;
  }

  .marketplace-header {
    padding: 16px;
  }

  .page-title {
    font-size: 22px;
  }

  .stats-section {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .stat-card {
    padding: 12px;
  }

  .stat-icon {
    width: 40px;
    height: 40px;
    font-size: 18px;
  }

  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }

  .product-details {
    padding: 10px;
  }

  .product-footer {
    margin: 8px 0;
    padding-bottom: 8px;
  }
}
</style>
