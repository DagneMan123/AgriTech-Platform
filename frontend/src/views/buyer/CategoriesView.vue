<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar @logout="handleLogout" />
    </div>
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen lg:ml-64 transition-all" role="main">
      
      <div :class="['flex justify-between items-start gap-4 px-6 py-[30px] mb-6 border-b transition-all -mx-4 lg:-mx-8 -mt-4 lg:-mt-8', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex-1">
          <h1 :class="['text-[28px] font-[800] m-0 mb-2 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">Product Categories</h1>
          <p :class="['text-sm m-0', darkMode ? 'text-slate-400' : 'text-slate-600']">Browse and explore agricultural products by specialized category</p>
        </div>
        <div class="flex items-center gap-3 flex-shrink-0 flex-wrap">
          <BuyerHeaderNav />
        </div>
      </div>

      <!-- Quick Summary Stats Grid -->
      <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-blue-500' : 'bg-white border-blue-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Categories</h3>
            <p class="text-xl font-black">{{ categories.length }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <i class="fas fa-layer-group"></i>
          </div>
        </article>

        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-emerald-500' : 'bg-white border-blue-200 text-slate-900 !border-l-emerald-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Products</h3>
            <p class="text-xl font-black">{{ totalProductsCount }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <i class="fas fa-boxes-stacked"></i>
          </div>
        </article>

        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-amber-500' : 'bg-white border-blue-200 text-slate-900 !border-l-amber-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Top Category</h3>
            <p class="text-base font-black truncate max-w-[150px]">{{ topCategoryName }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <i class="fas fa-fire"></i>
          </div>
        </article>
      </section>

      <!-- Main Content Section -->
      <section>
        <!-- Loading State -->
        <div v-if="loading" :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-blue-500/40' : 'bg-white border-blue-200']">
          <div class="w-10 h-10 border-4 border-slate-300 border-t-blue-500 rounded-full animate-spin mb-4"></div>
          <p :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">Loading categories...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredCategories.length === 0" :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-blue-500/40' : 'bg-white border-blue-200']">
          <div :class="['h-16 w-16 rounded-2xl flex items-center justify-center text-2xl mb-4 border shadow-inner', darkMode ? 'bg-slate-800/50 border-blue-500/40 text-blue-400' : 'bg-blue-50 border-blue-100 text-blue-600']">
            <i class="fas fa-inbox"></i>
          </div>
          <h3 :class="['text-base font-bold mb-1', darkMode ? 'text-slate-200' : 'text-slate-800']">No categories found</h3>
          <p :class="['text-xs max-w-sm mb-4', darkMode ? 'text-slate-400' : 'text-slate-500']">Try adjusting your search criteria or filter options.</p>
          <button @click="searchQuery = ''" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer">
            Reset Filter
          </button>
        </div>

        <!-- Categories Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-5">
          <article
            v-for="category in filteredCategories"
            :key="category.id"
            :class="[
              'rounded-2xl p-5 border shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group',
              darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 hover:border-blue-400' : 'bg-white border-blue-200 text-slate-900 hover:border-blue-400'
            ]"
          >
            <div>
              <div class="flex justify-between items-start mb-4">
                <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-xl transition-transform group-hover:scale-110', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
                  <i :class="getCategoryIcon(category.name)"></i>
                </div>
                <span :class="['text-[11px] font-bold px-2.5 py-1 rounded-full border', darkMode ? 'bg-[#0b0f19] border-blue-500/30 text-blue-400' : 'bg-slate-100 border-slate-200 text-slate-600']">
                  {{ category.product_count || 0 }} products
                </span>
              </div>

              <h3 class="text-base font-bold mb-1.5 group-hover:text-blue-500 transition-colors">{{ category.name }}</h3>
              <p :class="['text-xs line-clamp-2 mb-4', darkMode ? 'text-slate-400' : 'text-slate-500']">
                {{ category.description || 'Explore top quality ' + category.name + ' directly from local farms.' }}
              </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 pt-3 border-t border-slate-700/30">
              <button 
                @click="openCategoryDetails(category)"
                :class="['flex-1 py-2 border rounded-xl text-xs font-bold transition-all cursor-pointer', darkMode ? 'bg-[#0b0f19] border-blue-500/40 text-slate-300 hover:bg-slate-800' : 'bg-white border-blue-200 text-slate-700 hover:bg-slate-50']"
              >
                Details
              </button>
              <button 
                @click="selectCategory(category)"
                class="flex-1 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-xs flex items-center justify-center gap-1.5"
              >
                Explore <i class="fas fa-arrow-right text-[10px]"></i>
              </button>
            </div>
          </article>
        </div>
      </section>

      <!-- CATEGORY DETAILS MODAL -->
      <div v-if="selectedModalCategory" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div :class="['w-full max-w-md rounded-2xl border p-6 shadow-xl relative transition-all', darkMode ? 'bg-[#131b2e] border-blue-500/50 text-slate-100' : 'bg-white border-blue-200 text-slate-900']">
          <button @click="selectedModalCategory = null" class="absolute top-4 right-4 text-slate-400 hover:text-slate-200 text-lg cursor-pointer">
            <i class="fas fa-times"></i>
          </button>

          <div class="flex items-center gap-3 mb-4">
            <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
              <i :class="getCategoryIcon(selectedModalCategory.name)"></i>
            </div>
            <div>
              <h2 class="text-lg font-extrabold">{{ selectedModalCategory.name }}</h2>
              <p class="text-xs text-blue-500 font-bold">{{ selectedModalCategory.product_count || 0 }} Available Products</p>
            </div>
          </div>

          <p :class="['text-xs mb-6 leading-relaxed', darkMode ? 'text-slate-300' : 'text-slate-600']">
            {{ selectedModalCategory.description || 'This category contains fresh agricultural products supplied directly by registered regional farmers and suppliers.' }}
          </p>

          <div class="flex gap-3">
            <button @click="selectedModalCategory = null" :class="['flex-1 py-2.5 border rounded-xl text-xs font-bold cursor-pointer', darkMode ? 'bg-[#0b0f19] border-blue-500/40 text-slate-300 hover:bg-slate-800' : 'bg-white border-blue-200 text-slate-700 hover:bg-slate-50']">
              Close
            </button>
            <button @click="selectCategory(selectedModalCategory)" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold cursor-pointer flex items-center justify-center gap-2">
              Browse Category <i class="fas fa-shopping-bag"></i>
            </button>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import BuyerHeaderNav from '@/components/layout/BuyerHeaderNav.vue'

const router = useRouter()
const auth = useAuthStore()
const themeStore = useThemeStore()

const categories = ref([])
const loading = ref(true)
const sidebarOpen = ref(false)
const searchQuery = ref('')
const sortBy = ref('name')
const selectedModalCategory = ref(null)

const darkMode = computed(() => themeStore.isDark)
const toggleDarkMode = () => themeStore.toggleTheme()

const loadCategories = async () => {
  loading.value = true
  try {
    const response = await fetch('http://localhost:8000/api/marketplace/categories', {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      categories.value = data.data || []
    }
  } catch (err) {
    console.error('Error loading categories:', err)
  } finally {
    loading.value = false
  }
}

const totalProductsCount = computed(() => {
  return categories.value.reduce((acc, curr) => acc + (Number(curr.product_count) || 0), 0)
})

const topCategoryName = computed(() => {
  if (!categories.value.length) return 'N/A'
  const sorted = [...categories.value].sort((a, b) => (b.product_count || 0) - (a.product_count || 0))
  return sorted[0]?.name || 'N/A'
})

const filteredCategories = computed(() => {
  let list = [...categories.value]
  if (searchQuery.value) {
    list = list.filter(c => c.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
  }
  if (sortBy.value === 'products') {
    list.sort((a, b) => (b.product_count || 0) - (a.product_count || 0))
  } else {
    list.sort((a, b) => a.name.localeCompare(b.name))
  }
  return list
})

const selectCategory = (category) => {
  router.push({
    name: 'buyer-marketplace',
    query: { category: category.id }
  })
}

const openCategoryDetails = (category) => {
  selectedModalCategory.value = category
}

const getCategoryIcon = (name = '') => {
  const n = name.toLowerCase()
  if (n.includes('grain') || n.includes('cereal')) return 'fas fa-wheat-awn'
  if (n.includes('veg')) return 'fas fa-carrot'
  if (n.includes('fruit')) return 'fas fa-apple-whole'
  if (n.includes('dairy') || n.includes('milk')) return 'fas fa-cow'
  if (n.includes('meat') || n.includes('livestock')) return 'fas fa-drumstick-bite'
  if (n.includes('coffee') || n.includes('tea')) return 'fas fa-mug-hot'
  return 'fas fa-tag'
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  themeStore.initializeTheme()
  loadCategories()
})
</script>