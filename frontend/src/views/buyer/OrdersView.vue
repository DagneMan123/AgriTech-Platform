<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">
    
    <!-- Sidebar Container -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar @logout="handleLogout" />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto min-h-screen lg:ml-64 transition-all" role="main">
      
      <!-- Header Section (Matching Farmer Dashboard) -->
      <div :class="['flex justify-between items-start gap-4 px-6 py-[30px] border-b transition-all -mx-4 lg:-mx-8 -mt-4 lg:-mt-8', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex-1">
          <h1 :class="['text-[28px] font-[800] m-0 mb-2 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">My Orders</h1>
          <p :class="['text-sm m-0', darkMode ? 'text-slate-400' : 'text-slate-600']">Track and manage your purchases</p>
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
          <!-- Filters Button -->
          <button @click="showFilters = !showFilters" :class="['px-4 py-2 rounded-lg border text-xs font-bold transition-all cursor-pointer flex items-center gap-2', darkMode ? 'bg-slate-800 border-slate-700 text-slate-100 hover:bg-slate-700' : 'bg-slate-100 border-slate-300 text-slate-700 hover:bg-slate-200']">
            <i class="fas fa-sliders-h"></i> Filters
          </button>
          <!-- Refresh Button -->
          <button @click="refreshOrders" :class="['px-4 py-2 rounded-lg border text-xs font-bold transition-all cursor-pointer flex items-center gap-2', darkMode ? 'bg-slate-800 border-slate-700 text-slate-100 hover:bg-slate-700' : 'bg-slate-100 border-slate-300 text-slate-700 hover:bg-slate-200']">
            <i class="fas fa-sync-alt"></i> Refresh
          </button>
        </div>
      </div>

      <!-- Content with padding -->
      <div class="p-4 lg:p-8">
        <!-- Summary Statistics Grid -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" aria-label="Order summary statistics">
          <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs hover:shadow-md transition-all flex justify-between items-start', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200/60 text-slate-900 !border-l-blue-600']">
            <div class="min-w-0 flex-1">
              <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1 truncate', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Orders</h3>
              <p class="text-2xl font-black tracking-tight">{{ totalOrders }}</p>
              <p :class="['text-[11px] font-medium truncate mt-1', darkMode ? 'text-slate-500' : 'text-slate-400']">All purchases</p>
            </div>
            <div :class="['text-lg shrink-0 pt-0.5', darkMode ? 'text-blue-400' : 'text-slate-400']">
              <i class="fas fa-shopping-bag"></i>
            </div>
          </article>

          <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs hover:shadow-md transition-all flex justify-between items-start', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100 !border-l-amber-500' : 'bg-white border-slate-200/60 text-slate-900 !border-l-amber-600']">
            <div class="min-w-0 flex-1">
              <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1 truncate', darkMode ? 'text-slate-400' : 'text-slate-500']">Pending</h3>
              <p class="text-2xl font-black tracking-tight">{{ pendingOrders }}</p>
              <p :class="['text-[11px] font-medium truncate mt-1', darkMode ? 'text-slate-500' : 'text-slate-400']">Awaiting confirmation</p>
            </div>
            <div :class="['text-lg shrink-0 pt-0.5', darkMode ? 'text-amber-400' : 'text-slate-400']">
              <i class="fas fa-clock"></i>
            </div>
          </article>

          <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs hover:shadow-md transition-all flex justify-between items-start', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100 !border-l-orange-500' : 'bg-white border-slate-200/60 text-slate-900 !border-l-orange-600']">
            <div class="min-w-0 flex-1">
              <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1 truncate', darkMode ? 'text-slate-400' : 'text-slate-500']">In Transit</h3>
              <p class="text-2xl font-black tracking-tight">{{ shippedOrders }}</p>
              <p :class="['text-[11px] font-medium truncate mt-1', darkMode ? 'text-slate-500' : 'text-slate-400']">On the way</p>
            </div>
            <div :class="['text-lg shrink-0 pt-0.5', darkMode ? 'text-orange-400' : 'text-slate-400']">
              <i class="fas fa-box"></i>
            </div>
          </article>

          <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs hover:shadow-md transition-all flex justify-between items-start', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100 !border-l-emerald-500' : 'bg-white border-slate-200/60 text-slate-900 !border-l-emerald-600']">
            <div class="min-w-0 flex-1">
              <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1 truncate', darkMode ? 'text-slate-400' : 'text-slate-500']">Delivered</h3>
              <p class="text-2xl font-black tracking-tight">{{ deliveredOrders }}</p>
              <p :class="['text-[11px] font-medium truncate mt-1', darkMode ? 'text-slate-500' : 'text-slate-400']">Completed</p>
            </div>
            <div :class="['text-lg shrink-0 pt-0.5', darkMode ? 'text-emerald-400' : 'text-slate-400']">
              <i class="fas fa-check-circle"></i>
            </div>
          </article>
        </section>

        <!-- Filters Section -->
        <section v-if="showFilters" :class="['rounded-2xl border shadow-2xs mb-6 p-6 transition-all', darkMode ? 'bg-[#131b2e] border-slate-800/80' : 'bg-white border-slate-200/80']">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div class="flex flex-col gap-1.5">
              <label :class="['text-xs font-bold', darkMode ? 'text-slate-300' : 'text-slate-700']">Status:</label>
              <select v-model="selectedStatus" :class="['px-3 py-2 border rounded-lg text-xs focus:outline-none focus:border-blue-500 cursor-pointer', darkMode ? 'bg-[#0b0f19] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-800']">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="flex flex-col gap-1.5">
              <label :class="['text-xs font-bold', darkMode ? 'text-slate-300' : 'text-slate-700']">Date Range:</label>
              <select v-model="selectedDateRange" :class="['px-3 py-2 border rounded-lg text-xs focus:outline-none focus:border-blue-500 cursor-pointer', darkMode ? 'bg-[#0b0f19] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-800']">
                <option value="">All Time</option>
                <option value="today">Today</option>
                <option value="week">Last 7 Days</option>
                <option value="month">Last 30 Days</option>
                <option value="year">This Year</option>
              </select>
            </div>
            <div class="flex flex-col gap-1.5">
              <label :class="['text-xs font-bold', darkMode ? 'text-slate-300' : 'text-slate-700']">Sort by:</label>
              <select v-model="sortBy" :class="['px-3 py-2 border rounded-lg text-xs focus:outline-none focus:border-blue-500 cursor-pointer', darkMode ? 'bg-[#0b0f19] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-800']">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="highest-amount">Highest Amount</option>
                <option value="lowest-amount">Lowest Amount</option>
              </select>
            </div>
            <div class="flex flex-col gap-1.5">
              <label :class="['text-xs font-bold', darkMode ? 'text-slate-300' : 'text-slate-700']">&nbsp;</label>
              <div class="flex gap-2">
                <button @click="applyFilters" :class="['px-3 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer flex-1', darkMode ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white']">
                  Apply
                </button>
                <button @click="clearFilters" :class="['px-3 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer', darkMode ? 'bg-slate-700 hover:bg-slate-600 text-slate-100' : 'bg-slate-200 hover:bg-slate-300 text-slate-800']">
                  Clear
                </button>
              </div>
            </div>
          </div>
        </section>

        <!-- Empty State -->
        <div v-if="totalOrders === 0" :class="['rounded-2xl border shadow-2xs p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-slate-800/80' : 'bg-white border-slate-200/80']">
          <div :class="['h-16 w-16 rounded-2xl flex items-center justify-center text-3xl mb-4', darkMode ? 'bg-slate-800/50 border-slate-700/80 text-blue-400' : 'bg-blue-50 border-blue-100 text-blue-600']">
            <i class="fas fa-inbox"></i>
          </div>
          <h3 :class="['text-base font-bold mb-1', darkMode ? 'text-slate-200' : 'text-slate-800']">
            No orders yet
          </h3>
          <p :class="['text-xs max-w-sm mb-5', darkMode ? 'text-slate-400' : 'text-slate-500']">
            Start shopping to see your orders here
          </p>
          <router-link to="/buyer/marketplace" :class="['inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all', darkMode ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white']">
            <i class="fas fa-shopping-bag"></i> Continue Shopping
          </router-link>
        </div>

        <!-- Orders List -->
        <div v-else class="space-y-4">
          <div v-for="order in filteredAndSortedOrders" :key="order.id" :class="['rounded-2xl border shadow-2xs overflow-hidden transition-all', darkMode ? 'bg-[#131b2e] border-slate-800/80 hover:shadow-lg' : 'bg-white border-slate-200/80 hover:shadow-lg']">
            <!-- Order Header -->
            <div :class="['flex flex-wrap justify-between items-center gap-4 p-5 border-b', darkMode ? 'border-slate-800' : 'border-slate-200']">
              <div class="flex-1">
                <h3 :class="['text-base font-bold', darkMode ? 'text-slate-100' : 'text-slate-900']">Order #{{ order.id }}</h3>
                <p :class="['text-xs mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">{{ formatDate(order.created_at) }}</p>
              </div>
              <span :class="['inline-block px-3 py-1 rounded-full text-xs font-bold', getStatusClass(order.status)]">
                {{ formatStatus(order.status) }}
              </span>
            </div>

            <!-- Order Details -->
            <div class="p-5 space-y-3">
              <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                <div>
                  <p :class="['font-bold mb-1', darkMode ? 'text-slate-400' : 'text-slate-600']">Items</p>
                  <p :class="['font-semibold', darkMode ? 'text-slate-100' : 'text-slate-900']">{{ order.items_count || 0 }}</p>
                </div>
                <div>
                  <p :class="['font-bold mb-1', darkMode ? 'text-slate-400' : 'text-slate-600']">From</p>
                  <p :class="['font-semibold', darkMode ? 'text-slate-100' : 'text-slate-900']">{{ order.supplier_name || 'Supplier' }}</p>
                </div>
                <div>
                  <p :class="['font-bold mb-1', darkMode ? 'text-slate-400' : 'text-slate-600']">Est. Delivery</p>
                  <p :class="['font-semibold', darkMode ? 'text-slate-100' : 'text-slate-900']">{{ formatDate(order.estimated_delivery) || 'Pending' }}</p>
                </div>
                <div>
                  <p :class="['font-bold mb-1', darkMode ? 'text-slate-400' : 'text-slate-600']">Amount</p>
                  <p :class="['font-semibold', darkMode ? 'text-slate-100' : 'text-slate-900']">ETB {{ formatNumber(order.total_amount) }}</p>
                </div>
              </div>

              <!-- Order Timeline -->
              <div class="flex items-center justify-between pt-3 text-xs">
                <div :class="['flex flex-col items-center', isStatusReached('pending', order.status) ? 'text-blue-500' : darkMode ? 'text-slate-500' : 'text-slate-400']">
                  <div :class="['w-2 h-2 rounded-full mb-1', isStatusReached('pending', order.status) ? 'bg-blue-500' : darkMode ? 'bg-slate-500' : 'bg-slate-400']"></div>
                  <span class="font-medium">Placed</span>
                </div>
                <div :class="['flex-1 h-0.5 mx-2', isStatusReached('confirmed', order.status) ? 'bg-blue-500' : darkMode ? 'bg-slate-700' : 'bg-slate-200']"></div>
                <div :class="['flex flex-col items-center', isStatusReached('confirmed', order.status) ? 'text-blue-500' : darkMode ? 'text-slate-500' : 'text-slate-400']">
                  <div :class="['w-2 h-2 rounded-full mb-1', isStatusReached('confirmed', order.status) ? 'bg-blue-500' : darkMode ? 'bg-slate-500' : 'bg-slate-400']"></div>
                  <span class="font-medium">Confirmed</span>
                </div>
                <div :class="['flex-1 h-0.5 mx-2', isStatusReached('shipped', order.status) ? 'bg-blue-500' : darkMode ? 'bg-slate-700' : 'bg-slate-200']"></div>
                <div :class="['flex flex-col items-center', isStatusReached('shipped', order.status) ? 'text-blue-500' : darkMode ? 'text-slate-500' : 'text-slate-400']">
                  <div :class="['w-2 h-2 rounded-full mb-1', isStatusReached('shipped', order.status) ? 'bg-blue-500' : darkMode ? 'bg-slate-500' : 'bg-slate-400']"></div>
                  <span class="font-medium">Shipped</span>
                </div>
                <div :class="['flex-1 h-0.5 mx-2', isStatusReached('delivered', order.status) ? 'bg-blue-500' : darkMode ? 'bg-slate-700' : 'bg-slate-200']"></div>
                <div :class="['flex flex-col items-center', isStatusReached('delivered', order.status) ? 'text-emerald-500' : darkMode ? 'text-slate-500' : 'text-slate-400']">
                  <div :class="['w-2 h-2 rounded-full mb-1', isStatusReached('delivered', order.status) ? 'bg-emerald-500' : darkMode ? 'bg-slate-500' : 'bg-slate-400']"></div>
                  <span class="font-medium">Delivered</span>
                </div>
              </div>
            </div>

            <!-- Order Actions -->
            <div :class="['flex flex-wrap gap-2 p-5 border-t', darkMode ? 'border-slate-800 bg-[#0b0f19]/50' : 'border-slate-200 bg-slate-50/50']">
              <button @click="viewOrderDetails(order.id)" :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-2', darkMode ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white']">
                <i class="fas fa-eye"></i> Details
              </button>
              <button v-if="order.status === 'delivered'" @click="leaveReview(order.id)" :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-2', darkMode ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-amber-600 hover:bg-amber-700 text-white']">
                <i class="fas fa-star"></i> Review
              </button>
              <button v-if="order.status === 'pending'" @click="cancelOrder(order.id)" :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-2', darkMode ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white']">
                <i class="fas fa-times"></i> Cancel
              </button>
              <button @click="contactSupport(order.id)" :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-2', darkMode ? 'bg-slate-700 hover:bg-slate-600 text-slate-100' : 'bg-slate-200 hover:bg-slate-300 text-slate-800']">
                <i class="fas fa-headset"></i> Support
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalOrders > 0" :class="['flex items-center justify-center gap-4 mt-8 pt-4 border-t', darkMode ? 'border-slate-800' : 'border-slate-200']">
          <button @click="previousPage" :disabled="currentPage === 1" :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-2', currentPage === 1 ? (darkMode ? 'bg-slate-700/50 text-slate-500 cursor-not-allowed' : 'bg-slate-200/50 text-slate-400 cursor-not-allowed') : (darkMode ? 'bg-slate-800 hover:bg-slate-700 text-slate-100' : 'bg-slate-200 hover:bg-slate-300 text-slate-800')]">
            <i class="fas fa-chevron-left"></i> Previous
          </button>
          <span :class="['text-xs font-bold', darkMode ? 'text-slate-400' : 'text-slate-600']">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <button @click="nextPage" :disabled="currentPage === totalPages" :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-2', currentPage === totalPages ? (darkMode ? 'bg-slate-700/50 text-slate-500 cursor-not-allowed' : 'bg-slate-200/50 text-slate-400 cursor-not-allowed') : (darkMode ? 'bg-slate-800 hover:bg-slate-700 text-slate-100' : 'bg-slate-200 hover:bg-slate-300 text-slate-800')]">
            Next <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useBuyerStore } from '@/stores/buyerStore'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { onMounted, computed, ref } from 'vue'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

const buyerStore = useBuyerStore()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()

// State
const showFilters = ref(false)
const selectedStatus = ref('')
const selectedDateRange = ref('')
const sortBy = ref('newest')
const currentPage = ref(1)
const sidebarOpen = ref(false)
const itemsPerPage = 10

const darkMode = computed(() => themeStore.isDark)

onMounted(async () => {
  themeStore.initializeTheme()
  try {
    await buyerStore.fetchOrders()
  } catch (error) {
    console.error('Failed to load orders:', error)
  }
})

// Helper to ensure orders is always an array
const getOrdersList = () => {
  if (Array.isArray(buyerStore.orders)) {
    return buyerStore.orders
  }
  return []
}

const ordersList = computed(() => getOrdersList())

// Computed properties
const filteredAndSortedOrders = computed(() => {
  let orders = [...ordersList.value]

  // Filter by status
  if (selectedStatus.value) {
    orders = orders.filter(o => o.status === selectedStatus.value)
  }

  // Filter by date range
  if (selectedDateRange.value) {
    const createdDate = new Date()
    switch (selectedDateRange.value) {
      case 'today':
        createdDate.setHours(0, 0, 0, 0)
        orders = orders.filter(o => new Date(o.created_at) >= createdDate)
        break
      case 'week':
        createdDate.setDate(createdDate.getDate() - 7)
        orders = orders.filter(o => new Date(o.created_at) >= createdDate)
        break
      case 'month':
        createdDate.setDate(createdDate.getDate() - 30)
        orders = orders.filter(o => new Date(o.created_at) >= createdDate)
        break
      case 'year':
        createdDate.setFullYear(createdDate.getFullYear() - 1)
        orders = orders.filter(o => new Date(o.created_at) >= createdDate)
        break
    }
  }

  // Sort
  switch (sortBy.value) {
    case 'oldest':
      orders.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
      break
    case 'highest-amount':
      orders.sort((a, b) => (b.total_amount || 0) - (a.total_amount || 0))
      break
    case 'lowest-amount':
      orders.sort((a, b) => (a.total_amount || 0) - (b.total_amount || 0))
      break
    case 'newest':
    default:
      orders.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      break
  }

  return orders.slice((currentPage.value - 1) * itemsPerPage, currentPage.value * itemsPerPage)
})

const totalOrders = computed(() => ordersList.value.length)
const pendingOrders = computed(() => ordersList.value.filter(o => o.status === 'pending').length)
const shippedOrders = computed(() => ordersList.value.filter(o => o.status === 'shipped').length)
const deliveredOrders = computed(() => ordersList.value.filter(o => o.status === 'delivered').length)
const totalPages = computed(() => Math.ceil(ordersList.value.length / itemsPerPage))

// Methods
const formatDate = (date: string | null) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  }).format(num || 0)
}

const formatStatus = (status: string) => {
  const map: Record<string, string> = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    shipped: 'In Transit',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
  }
  return map[status] || status
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'delivered':
      return 'bg-emerald-50 text-emerald-600'
    case 'shipped':
      return 'bg-blue-50 text-blue-600'
    case 'pending':
      return 'bg-amber-50 text-amber-600'
    case 'cancelled':
      return 'bg-red-50 text-red-600'
    default:
      return 'bg-slate-100 text-slate-600'
  }
}

const isStatusReached = (checkStatus: string, orderStatus: string) => {
  const statusOrder = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled']
  return statusOrder.indexOf(orderStatus) >= statusOrder.indexOf(checkStatus)
}

const applyFilters = () => {
  currentPage.value = 1
}

const clearFilters = () => {
  selectedStatus.value = ''
  selectedDateRange.value = ''
  sortBy.value = 'newest'
  currentPage.value = 1
  showFilters.value = false
}

const refreshOrders = async () => {
  try {
    await buyerStore.fetchOrders()
  } catch (error) {
    console.error('Failed to refresh orders:', error)
  }
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const viewOrderDetails = (orderId: number) => {
  router.push(`/buyer/orders/${orderId}`)
}

const leaveReview = (orderId: number) => {
  router.push(`/buyer/reviews?order_id=${orderId}`)
}

const cancelOrder = async (orderId: number) => {
  if (confirm('Are you sure you want to cancel this order?')) {
    try {
      console.log('Cancelling order:', orderId)
      await buyerStore.fetchOrders()
    } catch (error) {
      console.error('Failed to cancel order:', error)
    }
  }
}

const contactSupport = (orderId: number) => {
  console.log('Contacting support for order:', orderId)
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>
