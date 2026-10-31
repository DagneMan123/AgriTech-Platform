<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">

    <!-- Sidebar -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar @logout="handleLogout" />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen lg:ml-64 transition-all" role="main">

      <!-- ── Header ── -->
      <div :class="['flex justify-between items-start gap-4 px-6 py-[30px] mb-6 border-b transition-all -mx-4 lg:-mx-8 -mt-4 lg:-mt-8', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex-1">
          <h1 :class="['text-[28px] font-[800] m-0 mb-2 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">My Orders</h1>
          <p :class="['text-sm m-0', darkMode ? 'text-slate-400' : 'text-slate-600']">Track and manage your purchases seamlessly</p>
        </div>
        <BuyerHeaderNav />
      </div>

      <!-- ── Stats Cards ── -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-indigo-500' : 'bg-white border-blue-200 text-slate-900 !border-l-indigo-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Orders</h3>
            <p class="text-xl font-black">{{ totalOrders }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-indigo-500/10 text-indigo-400' : 'bg-indigo-50 text-indigo-600']">
            <i class="fas fa-shopping-bag"></i>
          </div>
        </article>

        <article :class="['rounded-xl p-4 border-l-4 border-y border-r flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-amber-500' : 'bg-white border-blue-200 text-slate-900 !border-l-amber-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Pending</h3>
            <p class="text-xl font-black">{{ pendingOrders }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <i class="fas fa-clock"></i>
          </div>
        </article>

        <article :class="['rounded-xl p-4 border-l-4 border-y border-r flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-sky-500' : 'bg-white border-blue-200 text-slate-900 !border-l-sky-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">In Transit</h3>
            <p class="text-xl font-black">{{ shippedOrders }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-sky-500/10 text-sky-400' : 'bg-sky-50 text-sky-600']">
            <i class="fas fa-box"></i>
          </div>
        </article>

        <article :class="['rounded-xl p-4 border-l-4 border-y border-r flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100 !border-l-blue-500' : 'bg-white border-blue-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Delivered</h3>
            <p class="text-xl font-black text-blue-500">{{ deliveredOrders }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <i class="fas fa-check-circle"></i>
          </div>
        </article>
      </section>

      <!-- ── Filters (Matched size with Marketplace screenshot) ── -->
      <div :class="['rounded-xl border p-5 mb-6', darkMode ? 'bg-[#131b2e] border-blue-500/40' : 'bg-white border-blue-200']">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="flex flex-col gap-2">
            <label :class="['text-[11px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Status</label>
            <select v-model="selectedStatus" :class="['w-full px-3 py-2 rounded-xl text-xs border transition-all outline-none cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500' : 'bg-white border-slate-300 text-slate-800 focus:border-blue-500']">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="flex flex-col gap-2">
            <label :class="['text-[11px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Date Range</label>
            <select v-model="selectedDateRange" :class="['w-full px-3 py-2 rounded-xl text-xs border transition-all outline-none cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500' : 'bg-white border-slate-300 text-slate-800 focus:border-blue-500']">
              <option value="">All Time</option>
              <option value="today">Today</option>
              <option value="week">Last 7 Days</option>
              <option value="month">Last 30 Days</option>
              <option value="year">This Year</option>
            </select>
          </div>
          <div class="flex flex-col gap-2">
            <label :class="['text-[11px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Sort By</label>
            <select v-model="sortBy" :class="['w-full px-3 py-2 rounded-xl text-xs border transition-all outline-none cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500' : 'bg-white border-slate-300 text-slate-800 focus:border-blue-500']">
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="highest-amount">Highest Amount</option>
              <option value="lowest-amount">Lowest Amount</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ── Empty State ── -->
      <div v-if="totalOrders === 0" :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-blue-500/40' : 'bg-white border-blue-200']">
        <div :class="['h-16 w-16 rounded-2xl flex items-center justify-center text-2xl mb-4 border shadow-inner', darkMode ? 'bg-slate-800/50 border-blue-500/40 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-400']">
          <i class="fas fa-inbox"></i>
        </div>
        <h3 :class="['text-base font-bold mb-1', darkMode ? 'text-slate-200' : 'text-slate-800']">No orders yet</h3>
        <p :class="['text-xs max-w-sm mb-4', darkMode ? 'text-slate-400' : 'text-slate-500']">Start shopping to see your orders here</p>
        <router-link to="/buyer/marketplace" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all">
          <i class="fas fa-shopping-bag mr-1"></i> Continue Shopping
        </router-link>
      </div>

      <!-- ── Orders List ── -->
      <div v-if="filteredAndSortedOrders.length > 0" class="flex flex-col gap-4">
        <div
          v-for="order in filteredAndSortedOrders"
          :key="order.id"
          :class="['rounded-2xl border overflow-hidden transition-all hover:shadow-lg', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100' : 'bg-white border-blue-200 text-slate-900']"
          style="border-left-width: 4px; border-left-color: #6366f1;"
        >
          <!-- Order Header -->
          <div :class="['flex justify-between items-center px-5 py-4 border-b', darkMode ? 'bg-[#0f1729] border-blue-500/30' : 'bg-slate-50 border-blue-100']">
            <div class="flex items-center gap-4">
              <h3 :class="['text-base font-bold m-0', darkMode ? 'text-slate-100' : 'text-slate-900']">Order #{{ order.id }}</h3>
              <p :class="['text-xs m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">{{ formatDate(order.created_at) }}</p>
            </div>
            <div class="flex items-center gap-2">
              <span :class="['inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide', statusBadgeClass(order.status)]">
                {{ formatStatus(order.status) }}
              </span>
            </div>
          </div>

          <!-- Order Summary Row -->
          <div :class="['grid grid-cols-2 sm:grid-cols-4 gap-4 px-5 py-4 border-b', darkMode ? 'border-blue-500/20' : 'border-blue-100']">
            <div class="flex flex-col gap-1">
              <span :class="['text-[10px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Items</span>
              <span :class="['text-sm font-semibold', darkMode ? 'text-slate-200' : 'text-slate-800']">{{ order.items_count || 0 }} item(s)</span>
            </div>
            <div class="flex flex-col gap-1">
              <span :class="['text-[10px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">From</span>
              <span :class="['text-sm font-semibold', darkMode ? 'text-slate-200' : 'text-slate-800']">{{ order.supplier_name || 'Supplier' }}</span>
            </div>
            <div class="flex flex-col gap-1">
              <span :class="['text-[10px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Est. Delivery</span>
              <span :class="['text-sm font-semibold', darkMode ? 'text-slate-200' : 'text-slate-800']">{{ formatDate(order.estimated_delivery) || 'Pending' }}</span>
            </div>
            <div class="flex flex-col gap-1">
              <span :class="['text-[10px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Amount</span>
              <span class="text-sm font-black text-emerald-500">Ksh {{ formatNumber(order.total_amount) }}</span>
            </div>
          </div>

          <!-- Items Preview -->
          <div v-if="order.items && order.items.length > 0" :class="['px-5 py-3 border-b', darkMode ? 'border-blue-500/20 bg-[#0b0f19]/40' : 'border-blue-100 bg-slate-50']">
            <span :class="['text-[10px] font-bold uppercase tracking-wider mr-3', darkMode ? 'text-slate-400' : 'text-slate-500']">Items:</span>
            <div class="flex flex-wrap gap-2 mt-2">
              <span
                v-for="(item, index) in order.items.slice(0, 3)"
                :key="index"
                :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium border', darkMode ? 'bg-[#131b2e] border-blue-500/30 text-slate-300' : 'bg-white border-blue-200 text-slate-700']"
              >
                {{ item.product_name }} <span :class="['opacity-60', darkMode ? 'text-slate-400' : 'text-slate-500']">×{{ item.quantity }}</span>
              </span>
              <span v-if="order.items.length > 3" :class="['inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium border', darkMode ? 'bg-[#131b2e] border-blue-500/30 text-slate-400' : 'bg-white border-blue-200 text-slate-500']">
                +{{ order.items.length - 3 }} more
              </span>
            </div>
          </div>

          <!-- Order Timeline -->
          <div :class="['px-5 py-4 border-b', darkMode ? 'border-blue-500/20 bg-[#0b0f19]/40' : 'border-blue-100 bg-slate-50']">
            <div class="flex items-center gap-0">
              <template v-for="(step, idx) in timelineSteps" :key="step.status">
                <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                  <div :class="['w-5 h-5 rounded-full border-2 transition-all', isStatusReached(step.status, order.status) ? 'bg-indigo-500 border-indigo-500 shadow-[0_0_0_3px_rgba(99,102,241,0.2)]' : darkMode ? 'bg-slate-700 border-slate-600' : 'bg-slate-200 border-slate-300']"></div>
                  <span :class="['text-[10px] font-semibold whitespace-nowrap', isStatusReached(step.status, order.status) ? (darkMode ? 'text-slate-200' : 'text-slate-700') : (darkMode ? 'text-slate-500' : 'text-slate-400')]">{{ step.label }}</span>
                </div>
                <div v-if="idx < timelineSteps.length - 1" :class="['flex-1 h-0.5 mb-4 min-w-[16px]', darkMode ? 'bg-slate-700' : 'bg-slate-200']"></div>
              </template>
            </div>
          </div>

          <!-- Order Actions -->
          <div class="flex flex-wrap gap-2 px-5 py-4">
            <button @click="viewOrderDetails(order.id)" class="flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer">
              <i class="fas fa-eye text-[10px]"></i> View Details
            </button>
            <button v-if="order.status === 'delivered'" @click="leaveReview(order.id)" :class="['flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'bg-[#0b0f19] border-blue-500/40 text-slate-300 hover:bg-slate-800' : 'bg-white border-blue-200 text-slate-700 hover:bg-slate-50']">
              <i class="fas fa-star text-[10px]"></i> Leave Review
            </button>
            <button v-if="order.status === 'pending'" @click="cancelOrder(order.id)" class="flex items-center gap-1.5 px-3 py-2 bg-red-500/15 hover:bg-red-500/25 text-red-400 border border-red-500/30 rounded-xl text-xs font-bold transition-all cursor-pointer">
              <i class="fas fa-times text-[10px]"></i> Cancel
            </button>
            <button @click="contactSupport(order.id)" :class="['flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'bg-transparent border-blue-500/40 text-slate-400 hover:bg-slate-800 hover:text-slate-200' : 'bg-transparent border-blue-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700']">
              <i class="fas fa-headset text-[10px]"></i> Support
            </button>
          </div>
        </div>
      </div>

      <!-- ── Pagination ── -->
      <div v-if="totalOrders > 0" class="flex justify-center items-center gap-4 mt-8 pb-4">
        <button
          :disabled="currentPage === 1"
          @click="currentPage--"
          :class="['flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold border transition-all', currentPage === 1 ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-300 hover:bg-slate-800' : 'bg-white border-blue-200 text-slate-700 hover:bg-slate-50']"
        >
          <i class="fas fa-chevron-left"></i> Previous
        </button>
        <span :class="['text-xs font-bold', darkMode ? 'text-slate-400' : 'text-slate-500']">Page {{ currentPage }} of {{ totalPages }}</span>
        <button
          :disabled="currentPage === totalPages"
          @click="currentPage++"
          :class="['flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold border transition-all', currentPage === totalPages ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-300 hover:bg-slate-800' : 'bg-white border-blue-200 text-slate-700 hover:bg-slate-50']"
        >
          Next <i class="fas fa-chevron-right"></i>
        </button>
      </div>

    </main>
  </div>
</template>

<script setup lang="ts">
import { useBuyerStore } from '@/stores/buyerStore'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { onMounted, computed, ref, watch } from 'vue'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import BuyerHeaderNav from '@/components/layout/BuyerHeaderNav.vue'

const buyerStore = useBuyerStore()
const authStore = useAuthStore()
const router = useRouter()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const selectedStatus = ref('')
const selectedDateRange = ref('')
const sortBy = ref('newest')
const currentPage = ref(1)
const itemsPerPage = 10

const timelineSteps = [
  { status: 'pending',   label: 'Order Placed' },
  { status: 'confirmed', label: 'Confirmed'    },
  { status: 'shipped',   label: 'Shipped'      },
  { status: 'delivered', label: 'Delivered'    },
]

onMounted(async () => {
  themeStore.initializeTheme()
  try {
    await buyerStore.fetchOrders()
  } catch (error) {
    console.error('Failed to load orders:', error)
  }
})

watch([selectedStatus, selectedDateRange, sortBy], () => {
  currentPage.value = 1
})

const ordersArray = computed(() => {
  return Array.isArray(buyerStore.orders) ? buyerStore.orders : []
})

const filteredAndSortedOrders = computed(() => {
  let orders = [...ordersArray.value]

  if (selectedStatus.value) {
    orders = orders.filter(o => o && o.status === selectedStatus.value)
  }

  if (selectedDateRange.value) {
    const cutoff = new Date()
    switch (selectedDateRange.value) {
      case 'today':  cutoff.setHours(0, 0, 0, 0); break
      case 'week':   cutoff.setDate(cutoff.getDate() - 7); break
      case 'month':  cutoff.setDate(cutoff.getDate() - 30); break
      case 'year':   cutoff.setFullYear(cutoff.getFullYear() - 1); break
    }
    orders = orders.filter(o => o && new Date(o.created_at) >= cutoff)
  }

  switch (sortBy.value) {
    case 'oldest':         orders.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime()); break
    case 'highest-amount': orders.sort((a, b) => (b.total_amount || 0) - (a.total_amount || 0)); break
    case 'lowest-amount':  orders.sort((a, b) => (a.total_amount || 0) - (b.total_amount || 0)); break
    default:               orders.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()); break
  }

  return orders.slice((currentPage.value - 1) * itemsPerPage, currentPage.value * itemsPerPage)
})

const totalOrders    = computed(() => ordersArray.value.length)
const pendingOrders  = computed(() => ordersArray.value.filter(o => o?.status === 'pending').length)
const shippedOrders  = computed(() => ordersArray.value.filter(o => o?.status === 'shipped').length)
const deliveredOrders= computed(() => ordersArray.value.filter(o => o?.status === 'delivered').length)
const totalPages     = computed(() => Math.ceil(totalOrders.value / itemsPerPage) || 1)

const statusBadgeClass = (status: string) => {
  const map: Record<string, string> = {
    pending:   'bg-amber-500/15 text-amber-400',
    confirmed: 'bg-blue-500/15 text-blue-400',
    shipped:   'bg-purple-500/15 text-purple-400',
    delivered: 'bg-emerald-500/15 text-emerald-400',
    cancelled: 'bg-red-500/15 text-red-400',
  }
  return map[status] || 'bg-slate-500/15 text-slate-400'
}

const formatDate = (date: string | null) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num || 0)
}

const formatStatus = (status: string) => {
  const map: Record<string, string> = {
    pending: 'Pending', confirmed: 'Confirmed', shipped: 'In Transit',
    delivered: 'Delivered', cancelled: 'Cancelled',
  }
  return map[status] || status
}

const isStatusReached = (checkStatus: string, orderStatus: string) => {
  const order = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled']
  return order.indexOf(orderStatus) >= order.indexOf(checkStatus)
}

const viewOrderDetails = (orderId: number) => router.push(`/buyer/orders/${orderId}`)
const leaveReview      = (orderId: number) => router.push(`/buyer/reviews?order_id=${orderId}`)
const contactSupport   = (orderId: number) => console.log('Contacting support for order:', orderId)

const cancelOrder = async (orderId: number) => {
  if (confirm('Are you sure you want to cancel this order?')) {
    try {
      await buyerStore.fetchOrders()
    } catch (error) {
      console.error('Failed to cancel order:', error)
    }
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>