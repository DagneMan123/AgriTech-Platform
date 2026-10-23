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
          <h1 :class="['text-[28px] font-[800] m-0 mb-2 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">My Wishlist</h1>
          <p :class="['text-sm m-0', darkMode ? 'text-slate-400' : 'text-slate-600']">Manage and review your saved agricultural products</p>
        </div>
      </div>

      <!-- 2. Summary Stats Cards -->
      <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-red-500' : 'bg-white border-blue-200 text-slate-900 !border-l-red-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Saved Items</h3>
            <p class="text-xl font-black">{{ wishlistItems.length }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-red-500/10 text-red-400' : 'bg-red-50 text-red-600']">
            <i class="fas fa-heart"></i>
          </div>
        </article>

        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-emerald-500' : 'bg-white border-blue-200 text-slate-900 !border-l-emerald-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Estimated Value</h3>
            <p class="text-xl font-black">ETB {{ formatNumber(totalWishlistValue) }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <i class="fas fa-wallet"></i>
          </div>
        </article>

        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-blue-500' : 'bg-white border-blue-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">In Stock Products</h3>
            <p class="text-xl font-black">{{ inStockCount }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <i class="fas fa-check-circle"></i>
          </div>
        </article>
      </section>

      <!-- 3. Wishlist Items Grid -->
      <section>
        <!-- Loading State -->
        <div v-if="loading" :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-blue-500/40' : 'bg-white border-blue-200']">
          <div class="w-10 h-10 border-4 border-slate-300 border-t-blue-500 rounded-full animate-spin mb-4"></div>
          <p :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">Loading wishlist...</p>
        </div>

        <!-- Wishlist Grid -->
        <div v-else-if="filteredWishlist.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
          <article
            v-for="item in filteredWishlist"
            :key="item.id"
            :class="[
              'rounded-2xl border overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group',
              darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100' : 'bg-white border-blue-200 text-slate-900'
            ]"
          >
            <!-- Product Image Section -->
            <div :class="['relative w-full pt-[80%] overflow-hidden', darkMode ? 'bg-[#0b0f19]' : 'bg-slate-100']">
              <img
                v-if="item.image"
                :src="item.image"
                :alt="item.name"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
              />
              <div v-else class="absolute inset-0 flex items-center justify-center text-slate-400 text-3xl">
                <i class="fas fa-image"></i>
              </div>

              <!-- Delete/Remove Button -->
              <button
                @click="removeFromWishlist(item.id)"
                title="Remove from wishlist"
                class="absolute top-3 right-3 w-8 h-8 rounded-full bg-red-500/80 hover:bg-red-600 text-white flex items-center justify-center text-xs transition-all shadow-md cursor-pointer"
              >
                <i class="fas fa-times"></i>
              </button>

              <!-- Stock Badge -->
              <span 
                :class="[
                  'absolute bottom-3 left-3 px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider',
                  item.in_stock ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'
                ]"
              >
                {{ item.in_stock ? 'In Stock' : 'Out of Stock' }}
              </span>
            </div>

            <!-- Product Details -->
            <div class="p-4 flex-1 flex flex-col justify-between">
              <div>
                <h3 class="text-sm font-bold mb-1 line-clamp-1 group-hover:text-blue-500 transition-colors">{{ item.name }}</h3>
                <p :class="['text-xs mb-2 flex items-center gap-1.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  <i class="fas fa-store text-xs"></i> {{ item.farmer_name || 'Regional Supplier' }}
                </p>
                <p class="text-sm font-black text-emerald-500 mb-4">ETB {{ formatNumber(item.price) }}</p>
              </div>

              <!-- Actions -->
              <div class="flex gap-2 pt-3 border-t border-slate-700/30">
                <button 
                  @click="viewProduct(item.product_id)"
                  :class="['flex-1 py-2 border rounded-xl text-xs font-bold transition-all cursor-pointer', darkMode ? 'bg-[#0b0f19] border-blue-500/40 text-slate-300 hover:bg-slate-800' : 'bg-white border-blue-200 text-slate-700 hover:bg-slate-50']"
                >
                  View
                </button>
                <button 
                  @click="addToCart(item)"
                  :disabled="!item.in_stock"
                  :class="[
                    'flex-1 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5',
                    item.in_stock 
                      ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs' 
                      : 'bg-slate-600 text-slate-300 cursor-not-allowed opacity-60'
                  ]"
                >
                  <i class="fas fa-shopping-cart text-[10px]"></i> Add
                </button>
              </div>
            </div>
          </article>
        </div>

        <!-- Empty Wishlist State -->
        <div v-else :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-blue-500/40' : 'bg-white border-blue-200']">
          <div :class="['h-16 w-16 rounded-2xl flex items-center justify-center text-2xl mb-4 border shadow-inner', darkMode ? 'bg-slate-800/50 border-blue-500/40 text-red-400' : 'bg-red-50 border-red-100 text-red-500']">
            <i class="fas fa-heart-broken"></i>
          </div>
          <h3 :class="['text-base font-bold mb-1', darkMode ? 'text-slate-200' : 'text-slate-800']">Your Wishlist is Empty</h3>
          <p :class="['text-xs max-w-sm mb-4', darkMode ? 'text-slate-400' : 'text-slate-500']">You haven't saved any agricultural items yet. Browse marketplace to add products!</p>
          <router-link to="/marketplace" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer">
            Explore Marketplace
          </router-link>
        </div>
      </section>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()
const themeStore = useThemeStore()

const loading = ref(false)
const sidebarOpen = ref(false)
const searchQuery = ref('')

// ሳምፕል ዳታው ሙሉ በሙሉ ተወግዷል፤ አሁን የሚጀምረው በባዶ አርሬ (Empty Array) ነው
const wishlistItems = ref([])

const darkMode = computed(() => themeStore.isDark)
const toggleDarkMode = () => themeStore.toggleTheme()

const fetchWishlist = async () => {
  loading.value = true
  try {
    const response = await fetch('http://localhost:8000/api/buyer/wishlist', {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })
    if (response.ok) {
      const data = await response.json()
      const resData = Array.isArray(data) ? data : (data.data || [])
      wishlistItems.value = resData
    }
  } catch (err) {
    console.error('Error fetching wishlist:', err)
  } finally {
    loading.value = false
  }
}

const totalWishlistValue = computed(() => {
  return wishlistItems.value.reduce((acc, item) => acc + (Number(item.price) || 0), 0)
})

const inStockCount = computed(() => {
  return wishlistItems.value.filter(item => item.in_stock).length
})

const filteredWishlist = computed(() => {
  if (!searchQuery.value) return wishlistItems.value
  return wishlistItems.value.filter(item => 
    item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    (item.farmer_name && item.farmer_name.toLowerCase().includes(searchQuery.value.toLowerCase()))
  )
})

const removeFromWishlist = async (id) => {
  try {
    const response = await fetch(`http://localhost:8000/api/buyer/wishlist/${id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })
    if (response.ok) {
      wishlistItems.value = wishlistItems.value.filter(item => item.id !== id)
    }
  } catch (err) {
    console.error('Error removing from wishlist:', err)
  }
}

const viewProduct = (productId) => {
  router.push(`/app/buyer/products/${productId}`)
}

const addToCart = (item) => {
  alert(`Added ${item.name} to shopping cart!`)
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num || 0)
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  themeStore.initializeTheme()
  fetchWishlist()
})
</script>