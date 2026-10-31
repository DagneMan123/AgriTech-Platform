<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">
    
    <!-- Sidebar Container (Responsive) -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar @logout="handleLogout" />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen lg:ml-64 transition-all" role="main">
      
      <!-- Header Section -->
      <div :class="['flex justify-between items-start gap-4 px-6 py-[30px] mb-6 border-b transition-all -mx-4 lg:-mx-8 -mt-4 lg:-mt-8', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex-1">
          <h1 :class="['text-[28px] font-[800] m-0 mb-2 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">Marketplace</h1>
          <p :class="['text-sm m-0', darkMode ? 'text-slate-400' : 'text-slate-600']">Browse and discover fresh agricultural products</p>
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
          <BuyerHeaderNav />
        </div>
      </div>

      <!-- Summary Statistics Grid -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" aria-label="Marketplace summary statistics">
        <article
          v-for="card in summaryCards"
          :key="card.key"
          :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs hover:shadow-md transition-all flex justify-between items-start', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-blue-500' : 'bg-white border-blue-200 text-slate-900 !border-l-blue-600']"
        >
          <div class="min-w-0 flex-1">
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1 truncate', darkMode ? 'text-slate-400' : 'text-slate-500']">{{ card.label }}</h3>
            <p class="text-xl lg:text-2xl font-black tracking-tight">{{ card.value }}</p>
            <p :class="['text-[11px] font-medium truncate mt-1', darkMode ? 'text-slate-500' : 'text-slate-400']">{{ card.sub }}</p>
          </div>
          <div :class="['text-lg shrink-0 pt-0.5', darkMode ? 'text-blue-400' : 'text-blue-600']">
            <i :class="card.icon"></i>
          </div>
        </article>
      </section>

      <!-- Filters Section Panel (Always Visible) -->
      <section :class="['rounded-2xl border p-5 mb-6 shadow-sm', darkMode ? 'bg-[#131b2e] border-blue-500/40' : 'bg-white border-blue-200']">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
          <div class="flex flex-col gap-1.5">
            <label :class="['text-xs font-bold', darkMode ? 'text-slate-300' : 'text-slate-700']">Search:</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search products..."
              :class="['px-3 py-2 border rounded-xl text-xs focus:outline-none focus:border-blue-500', darkMode ? 'bg-[#0b0f19] border-blue-500/40 text-slate-100' : 'bg-white border-blue-200 text-slate-800']"
              @input="handleSearch"
            />
          </div>
          <div class="flex flex-col gap-1.5">
            <label :class="['text-xs font-bold', darkMode ? 'text-slate-300' : 'text-slate-700']">Category:</label>
            <select v-model="selectedCategory" :class="['px-3 py-2 border rounded-xl text-xs focus:outline-none focus:border-blue-500 cursor-pointer', darkMode ? 'bg-[#0b0f19] border-blue-500/40 text-slate-100' : 'bg-white border-blue-200 text-slate-800']" @change="handleSearch">
              <option value="">All Categories</option>
              <option value="vegetables">Vegetables</option>
              <option value="grains">Grains</option>
              <option value="fruits">Fruits</option>
              <option value="dairy">Dairy</option>
              <option value="meat">Meat</option>
            </select>
          </div>
          <div class="flex flex-col gap-1.5">
            <label :class="['text-xs font-bold', darkMode ? 'text-slate-300' : 'text-slate-700']">Price Range:</label>
            <select v-model="priceRange" :class="['px-3 py-2 border rounded-xl text-xs focus:outline-none focus:border-blue-500 cursor-pointer', darkMode ? 'bg-[#0b0f19] border-blue-500/40 text-slate-100' : 'bg-white border-blue-200 text-slate-800']" @change="handleSearch">
              <option value="">All Prices</option>
              <option value="0-500">Under ETB 500</option>
              <option value="500-1000">ETB 500 - 1,000</option>
              <option value="1000-5000">ETB 1,000 - 5,000</option>
              <option value="5000+">Above ETB 5,000</option>
            </select>
          </div>
          <div class="flex flex-col gap-1.5">
            <label :class="['text-xs font-bold', darkMode ? 'text-slate-300' : 'text-slate-700']">Sort by:</label>
            <select v-model="sortBy" :class="['px-3 py-2 border rounded-xl text-xs focus:outline-none focus:border-blue-500 cursor-pointer', darkMode ? 'bg-[#0b0f19] border-blue-500/40 text-slate-100' : 'bg-white border-blue-200 text-slate-800']" @change="handleSearch">
              <option value="newest">Newest First</option>
              <option value="price-low">Price: Low to High</option>
              <option value="price-high">Price: High to Low</option>
              <option value="popular">Most Popular</option>
            </select>
          </div>
        </div>
      </section>

      <!-- Loading State -->
      <div v-if="loading" :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center mb-6', darkMode ? 'bg-[#131b2e] border-blue-500/40' : 'bg-white border-blue-200']">
        <div class="w-10 h-10 border-4 border-slate-300 border-t-emerald-600 rounded-full animate-spin mb-4"></div>
        <p :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">Loading marketplace products...</p>
      </div>

      <!-- Products Grid Section -->
      <section v-else-if="safeProducts.length > 0" class="mb-8">
        <div class="flex justify-between items-center mb-4">
          <p :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">Showing {{ safeProducts.length }} product(s)</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
          <article
            v-for="product in safeProducts"
            :key="product.id"
            :class="['rounded-2xl border overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col', darkMode ? 'bg-[#131b2e] border-blue-500/40' : 'bg-white border-blue-200']"
          >
            <!-- Product Image -->
            <div :class="['relative w-full pt-[100%] overflow-hidden', darkMode ? 'bg-[#0b0f19]' : 'bg-slate-100']">
              <img
                v-if="product.image"
                :src="product.image"
                :alt="product.name"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 hover:scale-105"
              />
              <div v-else class="absolute inset-0 flex items-center justify-center text-slate-400 text-3xl">
                <i class="fas fa-image"></i>
              </div>
              <button
                @click.stop="toggleWishlist(product)"
                :class="[
                  'absolute top-3 right-3 w-9 h-9 rounded-full flex items-center justify-center transition-all shadow-sm cursor-pointer',
                  isInWishlist(product.id) ? 'bg-red-50 text-red-500' : (darkMode ? 'bg-[#131b2e]/80 text-slate-300 hover:text-red-500' : 'bg-white text-slate-400 hover:text-red-500')
                ]"
              >
                <i class="fas fa-heart"></i>
              </button>
              <div v-if="product.rating" class="absolute bottom-3 right-3 bg-blue-600 text-white px-2 py-0.5 rounded-md text-[11px] font-bold flex items-center gap-1">
                <i class="fas fa-star text-amber-300"></i> {{ product.rating }}
              </div>
            </div>

            <!-- Product Info -->
            <div class="p-4 flex-1 flex flex-col justify-between">
              <div>
                <h3 :class="['text-sm font-bold mb-1 line-clamp-2', darkMode ? 'text-slate-100' : 'text-slate-900']">{{ product.name }}</h3>
                <p :class="['text-xs mb-1 flex items-center gap-1.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  <i class="fas fa-store text-xs"></i> {{ product.farmer_name || 'Supplier' }}
                </p>
                <p :class="['text-[11px] uppercase tracking-wider mb-3', darkMode ? 'text-slate-500' : 'text-slate-400']">{{ product.category || 'Uncategorized' }}</p>
              </div>

              <div>
                <div :class="['flex justify-between items-center mb-3 pt-3 border-t', darkMode ? 'border-blue-500/30' : 'border-slate-100']">
                  <span class="text-sm font-black text-emerald-500">ETB {{ formatNumber(product.price || 0) }}</span>
                  <span v-if="product.original_price" class="text-xs text-slate-400 line-through">ETB {{ formatNumber(product.original_price) }}</span>
                </div>

                <div class="flex gap-2">
                  <button @click.stop="viewProduct(product.id)" :class="['flex-1 py-2 border rounded-xl text-xs font-bold transition-all cursor-pointer', darkMode ? 'bg-[#0b0f19] border-blue-500/40 text-slate-300 hover:bg-slate-800' : 'bg-white border-blue-200 text-slate-700 hover:bg-slate-50']">
                    View
                  </button>
                  <button @click.stop="addToCart(product)" class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-xs">
                    Add to Cart
                  </button>
                </div>
              </div>
            </div>
          </article>
        </div>
      </section>

      <!-- Empty State -->
      <div v-else :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center mb-6', darkMode ? 'bg-[#131b2e] border-blue-500/40' : 'bg-white border-blue-200']">
        <div :class="['h-16 w-16 rounded-2xl flex items-center justify-center text-2xl mb-4 border shadow-inner', darkMode ? 'bg-slate-800/50 border-blue-500/40 text-emerald-400' : 'bg-emerald-50 border-emerald-100 text-emerald-600']">
          <i class="fas fa-box-open"></i>
        </div>
        <h3 :class="['text-base font-bold mb-1', darkMode ? 'text-slate-200' : 'text-slate-800']">
          No products found
        </h3>
        <p :class="['text-xs max-w-sm mb-5', darkMode ? 'text-slate-400' : 'text-slate-500']">
          Try adjusting your filters or search terms to find available agricultural products.
        </p>
        <button @click="clearFilters" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all shadow-sm cursor-pointer">
          <i class="fas fa-redo"></i> Reset Filters
        </button>
      </div>

      <!-- Product Details Modal -->
      <div v-if="selectedProduct" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" @click="selectedProduct = null">
        <div :class="['w-full max-w-lg rounded-2xl p-6 shadow-2xl relative transition-all', darkMode ? 'bg-[#131b2e] text-slate-100 border border-slate-700' : 'bg-white text-slate-800 border border-slate-200']" @click.stop>
          <button @click="selectedProduct = null" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 cursor-pointer text-lg">
            <i class="fas fa-times"></i>
          </button>
          <div class="flex gap-4 mb-4">
            <img v-if="selectedProduct.image" :src="selectedProduct.image" :alt="selectedProduct.name" class="w-24 h-24 object-cover rounded-xl" />
            <div v-else class="w-24 h-24 bg-slate-100 dark:bg-slate-800 flex items-center justify-center rounded-xl text-slate-400 text-2xl">
              <i class="fas fa-image"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold">{{ selectedProduct.name }}</h3>
              <p class="text-xs text-slate-500 mt-1"><i class="fas fa-store"></i> {{ selectedProduct.farmer_name || 'Local Farmer' }}</p>
              <p class="text-xs text-emerald-600 font-bold uppercase mt-1">{{ selectedProduct.category }}</p>
              <p class="text-base font-black text-emerald-500 mt-2">ETB {{ formatNumber(selectedProduct.price || 0) }} / {{ selectedProduct.unit || 'kg' }}</p>
            </div>
          </div>
          <p v-if="selectedProduct.description" :class="['text-xs mb-4 p-3 rounded-xl', darkMode ? 'bg-[#0b0f19] text-slate-300' : 'bg-slate-50 text-slate-600']">{{ selectedProduct.description }}</p>
          <div class="flex justify-between items-center text-xs text-slate-500 mb-6">
            <span>Available: <strong class="text-emerald-500">{{ selectedProduct.quantity_available || selectedProduct.quantity || 0 }} {{ selectedProduct.unit || 'kg' }}</strong></span>
            <span>Grade: <strong>{{ selectedProduct.quality_grade || 'Standard' }}</strong></span>
          </div>
          <div class="flex gap-2">
            <button @click="selectedProduct = null" :class="['flex-1 py-2.5 border rounded-xl text-xs font-bold cursor-pointer transition-all', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-200 hover:bg-slate-50 text-slate-700']">Close</button>
            <button @click="addToCart(selectedProduct); selectedProduct = null" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold cursor-pointer shadow-xs transition-all">Add to Cart</button>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketplaceStore } from '@/stores/marketplaceStore'
import { useBuyerStore } from '@/stores/buyerStore'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import BuyerHeaderNav from '@/components/layout/BuyerHeaderNav.vue'

interface Product {
  id: number
  name: string
  farmer_name?: string
  category?: string
  price?: number
  original_price?: number
  image?: string
  rating?: number
  description?: string
  quantity_available?: number
  quantity?: number
  unit?: string
  quality_grade?: string
}

const router = useRouter()
const marketplaceStore = useMarketplaceStore()
const buyerStore = useBuyerStore()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const loading = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('')
const priceRange = ref('')
const sortBy = ref('newest')
const sidebarOpen = ref(false)
const selectedProduct = ref<Product | null>(null)

const darkMode = computed(() => themeStore.isDark)

onMounted(async () => {
  themeStore.initializeTheme()
  loading.value = true
  try {
    await marketplaceStore.fetchProducts()
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    loading.value = false
  }
})

const safeProducts = computed<Product[]>(() => {
  const raw = marketplaceStore.products
  if (Array.isArray(raw)) return raw as Product[]
  if (raw && typeof raw === 'object' && Array.isArray((raw as any).data)) {
    return (raw as any).data as Product[]
  }
  return []
})

const totalProducts = computed(() => safeProducts.value.length)
const totalSuppliers = computed(() => new Set(safeProducts.value.map(p => p.farmer_name)).size)
const totalCategories = computed(() => new Set(safeProducts.value.map(p => p.category)).size)
const wishlistCount = computed(() => buyerStore.wishlist?.length || 0)

const summaryCards = computed(() => [
  { key: 'total', label: 'Total Products', value: totalProducts.value, sub: 'Available items', icon: 'fas fa-box' },
  { key: 'suppliers', label: 'Suppliers', value: totalSuppliers.value, sub: 'Active vendors', icon: 'fas fa-store' },
  { key: 'categories', label: 'Categories', value: totalCategories.value, sub: 'Product types', icon: 'fas fa-list' },
  { key: 'wishlist', label: 'Wishlist Items', value: wishlistCount.value, sub: 'Saved products', icon: 'fas fa-heart' }
])

const isInWishlist = (productId: number) => {
  return buyerStore.wishlist?.some((item: any) => item.product_id === productId) || false
}

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num || 0)
}

const handleSearch = async () => {
  loading.value = true
  try {
    const filters: any = {}
    if (selectedCategory.value) {
      filters.category = selectedCategory.value
    }
    if (priceRange.value) {
      if (priceRange.value === '0-500') {
        filters.min_price = 0
        filters.max_price = 500
      } else if (priceRange.value === '500-1000') {
        filters.min_price = 500
        filters.max_price = 1000
      } else if (priceRange.value === '1000-5000') {
        filters.min_price = 1000
        filters.max_price = 5000
      } else if (priceRange.value === '5000+') {
        filters.min_price = 5000
      }
    }
    if (sortBy.value) {
      filters.sort = sortBy.value
    }

    if (searchQuery.value) {
      await marketplaceStore.searchProducts(searchQuery.value, filters)
    } else {
      await marketplaceStore.fetchProducts(1, 24, filters)
    }
  } catch (error) {
    console.error('Search failed:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = async () => {
  await handleSearch()
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  priceRange.value = ''
  sortBy.value = 'newest'
  handleSearch()
}

const viewProduct = (id: number) => {
  selectedProduct.value = safeProducts.value.find(p => p.id === id) || null
}

const addToCart = async (product: Product) => {
  try {
    await buyerStore.addToCart({
      product_id: product.id,
      quantity: 1
    })
    alert('Added to cart successfully')
  } catch (error) {
    console.error('Failed to add to cart:', error)
  }
}

const toggleWishlist = async (product: Product) => {
  try {
    await buyerStore.addToWishlist({ product_id: product.id })
  } catch (error) {
    console.error('Failed to add to wishlist:', error)
  }
}

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}
</script>