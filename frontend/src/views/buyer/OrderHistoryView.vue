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
      <div :class="['border-b transition-all -mx-4 lg:-mx-8 -mt-4 lg:-mt-8 mb-6 px-6 lg:px-8 py-[24px]', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="lg:hidden p-2 rounded-xl border transition-all text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800"
              aria-label="Toggle menu"
            >
              <Menu class="w-5 h-5" />
            </button>
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-500/10 text-blue-500 border border-blue-500/20">
                  <History class="w-3.5 h-3.5" />
                  Purchases Archive
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Orders &gt; History
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Order History
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Complete log of your delivered, completed, and archived orders
              </p>
            </div>
          </div>

          <!-- Header Right Actions -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
          </div>
        </div>
      </div>

      <!-- ── Stats Overview ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Delivered Orders</h3>
            <p class="text-2xl font-black">{{ deliveredCount }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <CheckCircle2 class="w-6 h-6" />
          </div>
        </article>

        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Lifetime Spent</h3>
            <p class="text-xl lg:text-2xl font-black text-blue-500">ETB {{ formatNumber(totalDeliveredSpent) }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <BadgeDollarSign class="w-6 h-6" />
          </div>
        </article>

        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-indigo-500' : 'bg-white border-slate-200 text-slate-900 !border-l-indigo-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total History Orders</h3>
            <p class="text-2xl font-black">{{ historyList.length }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-indigo-500/10 text-indigo-400' : 'bg-indigo-50 text-indigo-600']">
            <Package class="w-6 h-6" />
          </div>
        </article>

        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-red-500' : 'bg-white border-slate-200 text-slate-900 !border-l-red-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Cancelled / Returned</h3>
            <p class="text-2xl font-black">{{ cancelledCount }}</p>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-red-500/10 text-red-400' : 'bg-red-50 text-red-600']">
            <XCircle class="w-6 h-6" />
          </div>
        </article>
      </section>

      <!-- ── Search & Filter ── -->
      <div :class="['rounded-2xl border p-4 mb-6 shadow-2xs flex flex-col sm:flex-row gap-3 justify-between items-center', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="relative w-full sm:w-80">
          <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search past orders or products..."
            :class="['w-full pl-9 pr-3 py-2 rounded-xl text-xs border outline-none', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-800']"
          />
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <select
            v-model="statusFilter"
            :class="['px-3 py-2 rounded-xl text-xs border outline-none cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-800']"
          >
            <option value="">All History Statuses</option>
            <option value="delivered">Delivered Only</option>
            <option value="cancelled">Cancelled Only</option>
          </select>
        </div>
      </div>

      <!-- ── History Table / Cards ── -->
      <div v-if="filteredHistory.length > 0" class="space-y-4">
        <div
          v-for="order in filteredHistory"
          :key="order.id"
          :class="['rounded-2xl border p-5 shadow-2xs transition-all hover:shadow-md', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-700/30">
            <div class="flex items-center gap-3">
              <span class="font-bold text-xs">#{{ order.order_number || ('ORD-' + order.id) }}</span>
              <span class="text-xs text-slate-400">{{ formatDate(order.delivered_at || order.created_at) }}</span>
              <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase', order.status === 'cancelled' ? 'bg-red-500/15 text-red-400' : 'bg-emerald-500/15 text-emerald-400']">
                {{ order.status === 'cancelled' ? 'Cancelled' : 'Delivered' }}
              </span>
            </div>
            <div class="text-sm font-black text-emerald-500">
              ETB {{ formatNumber(order.total_amount || 0) }}
            </div>
          </div>

          <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div>
              <p class="font-bold m-0">{{ order.seller?.name || order.seller_name || order.farmer?.name || 'Seller' }}</p>
              <p class="text-slate-400 m-0 mt-0.5">
                {{ (order.items && order.items[0]?.product_name) || (order.items && order.items[0]?.product?.title) || 'Assorted Produce' }}
                {{ order.items && order.items.length > 1 ? ` + ${order.items.length - 1} more items` : '' }}
              </p>
            </div>

            <div class="flex items-center gap-2">
              <router-link
                to="/buyer/marketplace"
                class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white transition-all flex items-center gap-1 cursor-pointer"
              >
                <Repeat class="w-3 h-3" /> Re-Order
              </router-link>
              <router-link
                :to="`/buyer/reviews?order_id=${order.id}`"
                :class="['px-3 py-1.5 rounded-xl text-xs font-bold border transition-all flex items-center gap-1', darkMode ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-50']"
              >
                <Star class="w-3 h-3 text-amber-400" /> Review
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <div v-else :class="['rounded-2xl border p-12 text-center', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <p class="text-sm text-slate-400">No past completed orders found in database.</p>
      </div>

    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useBuyerStore } from '@/stores/buyerStore'
import { useThemeStore } from '@/stores/themeStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import {
  Menu, History, CheckCircle2, BadgeDollarSign,
  Package, XCircle, Search, Repeat, Star
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const buyerStore = useBuyerStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')

onMounted(async () => {
  themeStore.initializeTheme()
  try {
    await buyerStore.fetchOrders()
  } catch (err) {
    console.warn(err)
  }
})

const historyList = computed(() => {
  const storeOrders = buyerStore.orders
  let list: any[] = []
  if (Array.isArray(storeOrders)) {
    list = storeOrders
  } else if (storeOrders && Array.isArray((storeOrders as any).data)) {
    list = (storeOrders as any).data
  }
  return list.filter(o => o && ['delivered', 'cancelled'].includes((o.status || '').toLowerCase()))
})

const deliveredCount = computed(() => historyList.value.filter(o => o.status === 'delivered').length)
const cancelledCount = computed(() => historyList.value.filter(o => o.status === 'cancelled').length)
const totalDeliveredSpent = computed(() =>
  historyList.value
    .filter(o => o.status === 'delivered')
    .reduce((sum, o) => sum + (o.total_amount || 0), 0)
)

const filteredHistory = computed(() => {
  let list = [...historyList.value]
  if (statusFilter.value) {
    list = list.filter(o => o.status === statusFilter.value)
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(o =>
      (o.order_number || ('ORD-' + o.id)).toLowerCase().includes(q) ||
      (o.seller?.name || o.seller_name || '').toLowerCase().includes(q)
    )
  }
  return list
})

const formatDate = (dateStr: string | null) => {
  if (!dateStr) return 'N/A'
  return new Date(dateStr).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num || 0)
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>
