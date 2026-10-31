<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">
    <!-- Responsive Sidebar Container -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar @logout="handleLogout" />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area with proper 64-unit left margin -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen lg:ml-64 transition-all" role="main">
      <!-- ── PAGE HEADER ── -->
      <div :class="['border-b transition-all -mx-4 lg:-mx-8 -mt-4 lg:-mt-8 mb-6 px-6 lg:px-8 py-[24px]', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <!-- Mobile Menu Toggle Button -->
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
                  <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                  Activity Alerts Feed
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Account &gt; Notifications
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <Bell class="w-7 h-7 text-blue-500" />
                Notifications &amp; Activity Alerts
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Real-time operational alerts for marketplace orders, transport delivery dispatches, Chapa escrow settlements, and supplier quotes
              </p>
            </div>
          </div>

          <!-- Header Right Actions -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              :disabled="markingAllRead"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all transform active:scale-95 cursor-pointer disabled:opacity-50"
            >
              <CheckCheck class="w-4 h-4" />
              <span>Mark All as Read</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ── DATABASE ERROR BANNER ── -->
      <div
        v-if="errorMessage"
        :class="['p-4 rounded-2xl border mb-6 flex items-start justify-between gap-3', darkMode ? 'bg-rose-500/10 border-rose-500/20 text-rose-200' : 'bg-rose-50 border-rose-200 text-rose-800']"
      >
        <div class="flex items-center gap-3">
          <AlertCircle class="w-5 h-5 text-rose-500 shrink-0" />
          <div>
            <div class="text-xs font-bold">Database Connection Notice</div>
            <div class="text-xs opacity-90">{{ errorMessage }}</div>
          </div>
        </div>
        <button
          @click="fetchNotifications"
          class="px-3 py-1 rounded-lg text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition"
        >
          Retry
        </button>
      </div>

      <!-- ── SUMMARY KPI STATS CARDS (LIVE DATABASE METRICS) ── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Notifications -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Alerts</span>
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <Bell class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight">
            {{ notifications.length }}
          </div>
          <div :class="['text-[11px] mt-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Historical system notifications</span>
          </div>
        </div>

        <!-- Unread Alerts -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Unread Alerts</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
              <Mail class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-amber-500">
            {{ unreadCount }}
          </div>
          <div :class="['text-[11px] mt-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Requires your attention</span>
          </div>
        </div>

        <!-- Order & Delivery Alerts -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Order Notices</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <ShoppingBag class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-emerald-500">
            {{ orderNoticesCount }}
          </div>
          <div :class="['text-[11px] mt-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Fulfillment &amp; logistics updates</span>
          </div>
        </div>

        <!-- Escrow & Payments -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Payment Notices</span>
            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
              <ShieldCheck class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-indigo-400">
            {{ paymentNoticesCount }}
          </div>
          <div :class="['text-[11px] mt-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Chapa receipts &amp; escrow</span>
          </div>
        </div>
      </div>

      <!-- ── FILTERS & SEARCH TOOLBAR ── -->
      <div :class="['p-4 lg:p-5 rounded-2xl border mb-6 transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <!-- Search input -->
          <div class="relative flex-1 min-w-[240px]">
            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search notifications by title, order #, or keywords..."
              :class="['w-full pl-10 pr-4 py-2.5 rounded-xl border text-xs lg:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100 placeholder:text-slate-500' : 'bg-slate-50 border-slate-300 text-slate-900 placeholder:text-slate-400']"
            />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Status / Type Filter Select -->
          <div class="flex items-center gap-2">
            <select
              v-model="selectedFilter"
              :class="['px-3 py-2.5 rounded-xl border text-xs font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="all">All Alerts</option>
              <option value="unread">Unread Only</option>
              <option value="orders">Orders &amp; Deliveries</option>
              <option value="payments">Payments &amp; Escrow</option>
              <option value="system">System &amp; General</option>
            </select>
          </div>
        </div>

        <!-- Filter Quick Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 no-scrollbar">
          <button
            v-for="tab in filterTabs"
            :key="tab.value"
            @click="selectedFilter = tab.value"
            :class="[
              'px-3.5 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5',
              selectedFilter === tab.value
                ? 'bg-blue-600 text-white shadow-sm font-semibold'
                : darkMode
                  ? 'bg-slate-800/80 hover:bg-slate-800 text-slate-300 border border-slate-700'
                  : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200'
            ]"
          >
            <span>{{ tab.label }}</span>
            <span
              v-if="tab.count !== undefined"
              :class="[
                'px-1.5 py-0.2 rounded-full text-[10px]',
                selectedFilter === tab.value ? 'bg-blue-700 text-blue-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400'
              ]"
            >
              {{ tab.count }}
            </span>
          </button>
        </div>
      </div>

      <!-- ── LOADING SKELETON ── -->
      <div v-if="loading" class="space-y-3 mb-8">
        <div
          v-for="i in 4"
          :key="i"
          :class="['p-4 rounded-2xl border animate-pulse flex items-start gap-4', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
        >
          <div class="w-10 h-10 rounded-xl bg-slate-300 dark:bg-slate-700 shrink-0"></div>
          <div class="flex-1 space-y-2">
            <div class="w-1/3 h-4 rounded bg-slate-300 dark:bg-slate-700"></div>
            <div class="w-3/4 h-3 rounded bg-slate-200 dark:bg-slate-800"></div>
          </div>
        </div>
      </div>

      <!-- ── EMPTY STATE ── -->
      <div
        v-else-if="filteredNotifications.length === 0"
        :class="['p-12 text-center rounded-2xl border my-8 max-w-xl mx-auto', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']"
      >
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
          <Bell class="w-8 h-8" />
        </div>
        <h3 :class="['text-lg font-bold mb-2', darkMode ? 'text-slate-100' : 'text-slate-800']">
          No Notifications
        </h3>
        <p :class="['text-xs lg:text-sm mb-6 max-w-md mx-auto', darkMode ? 'text-slate-400' : 'text-slate-500']">
          {{ selectedFilter !== 'all' || searchQuery
            ? 'No alerts match your active filter selections.'
            : 'You are completely caught up! New order fulfillment notices, Chapa escrow confirmations, and message replies will appear here in real time.' }}
        </p>
        <button
          v-if="selectedFilter !== 'all' || searchQuery"
          @click="selectedFilter = 'all'; searchQuery = ''"
          class="px-5 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm cursor-pointer"
        >
          Reset Filters
        </button>
      </div>

      <!-- ── NOTIFICATIONS FEED LIST ── -->
      <div v-else class="space-y-3 mb-8">
        <div
          v-for="item in filteredNotifications"
          :key="item.id"
          :class="[
            'p-4 rounded-2xl border transition-all flex items-start justify-between gap-4 group',
            !item.is_read
              ? darkMode
                ? 'bg-[#16223b] border-blue-500/40 shadow-sm'
                : 'bg-blue-50/60 border-blue-200 shadow-sm'
              : darkMode
                ? 'bg-[#131b2e] border-slate-800 hover:border-slate-700'
                : 'bg-white border-slate-200 hover:border-slate-300'
          ]"
        >
          <div class="flex items-start gap-3.5">
            <!-- Type Icon Container -->
            <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border', getIconBg(item.type)]">
              <component :is="getIconComponent(item.type)" class="w-5 h-5" />
            </div>

            <!-- Content -->
            <div>
              <div class="flex items-center gap-2 mb-1">
                <h3 :class="['font-bold text-sm m-0', darkMode ? 'text-slate-100' : 'text-slate-900']">
                  {{ item.title }}
                </h3>
                <span
                  v-if="!item.is_read"
                  class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"
                  title="Unread alert"
                ></span>
              </div>

              <p :class="['text-xs leading-relaxed m-0', darkMode ? 'text-slate-300' : 'text-slate-600']">
                {{ item.message }}
              </p>

              <div class="flex items-center gap-3 mt-2 text-[10px] text-slate-400">
                <span class="flex items-center gap-1 font-medium">
                  <Clock class="w-3 h-3" />
                  {{ formatTime(item.created_at) }}
                </span>
                <span v-if="item.type" class="uppercase tracking-wider font-semibold">
                  &bull; {{ item.type.replace('_', ' ') }}
                </span>
              </div>
            </div>
          </div>

          <!-- Item Action Button -->
          <div class="flex items-center gap-2 shrink-0">
            <button
              v-if="!item.is_read"
              @click="markAsRead(item.id)"
              class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-blue-500 hover:bg-blue-500/10 transition"
              title="Mark as read"
            >
              Mark Read
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { buyerAPI } from '@/api/buyer'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import {
  Menu, Bell, CheckCheck, AlertCircle, X, Search,
  ShoppingBag, ShieldCheck, Mail, Clock, Truck,
  CreditCard, CheckCircle2
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const loading = ref(true)
const markingAllRead = ref(false)
const errorMessage = ref('')

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

// Data State
const notifications = ref<any[]>([])
const searchQuery = ref('')
const selectedFilter = ref('all')

const fetchNotifications = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await buyerAPI.getNotifications()
    if (res.data) {
      if (Array.isArray(res.data)) {
        notifications.value = res.data
      } else if (res.data.data && Array.isArray(res.data.data)) {
        notifications.value = res.data.data
      } else {
        notifications.value = []
      }
    }
  } catch (err: any) {
    console.error('Failed to load notifications:', err)
    errorMessage.value = err.response?.data?.message || 'Unable to retrieve notifications feed from database.'
    notifications.value = []
  } finally {
    loading.value = false
  }
}

const markAsRead = async (id: number | string) => {
  try {
    await buyerAPI.markNotificationRead(id)
    const target = notifications.value.find(n => n.id === id)
    if (target) {
      target.is_read = true
    }
  } catch (err) {
    console.error('Error marking notification as read:', err)
  }
}

const markAllAsRead = async () => {
  markingAllRead.value = true
  try {
    await buyerAPI.markAllNotificationsRead()
    notifications.value.forEach(n => { n.is_read = true })
  } catch (err) {
    console.error('Error marking all notifications as read:', err)
  } finally {
    markingAllRead.value = false
  }
}

// Metrics
const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.is_read).length
})

const orderNoticesCount = computed(() => {
  return notifications.value.filter(n => {
    const t = (n.type || '').toLowerCase()
    return t.includes('order') || t.includes('delivery') || t.includes('transport')
  }).length
})

const paymentNoticesCount = computed(() => {
  return notifications.value.filter(n => {
    const t = (n.type || '').toLowerCase()
    return t.includes('payment') || t.includes('chapa') || t.includes('escrow')
  }).length
})

const filterTabs = computed(() => [
  { label: 'All Alerts', value: 'all', count: notifications.value.length },
  { label: 'Unread Only', value: 'unread', count: unreadCount.value },
  { label: 'Orders & Transport', value: 'orders', count: orderNoticesCount.value },
  { label: 'Payments & Escrow', value: 'payments', count: paymentNoticesCount.value },
  { label: 'System & Quotes', value: 'system' }
])

const filteredNotifications = computed(() => {
  let list = [...notifications.value]

  // Type filter
  if (selectedFilter.value === 'unread') {
    list = list.filter(n => !n.is_read)
  } else if (selectedFilter.value === 'orders') {
    list = list.filter(n => {
      const t = (n.type || '').toLowerCase()
      return t.includes('order') || t.includes('delivery') || t.includes('transport')
    })
  } else if (selectedFilter.value === 'payments') {
    list = list.filter(n => {
      const t = (n.type || '').toLowerCase()
      return t.includes('payment') || t.includes('chapa') || t.includes('escrow')
    })
  } else if (selectedFilter.value === 'system') {
    list = list.filter(n => {
      const t = (n.type || '').toLowerCase()
      return !t.includes('order') && !t.includes('delivery') && !t.includes('payment') && !t.includes('chapa')
    })
  }

  // Keyword search
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    list = list.filter(n => {
      const title = (n.title || '').toLowerCase()
      const msg = (n.message || '').toLowerCase()
      return title.includes(q) || msg.includes(q)
    })
  }

  return list
})

// Helpers
const getIconComponent = (type: string = '') => {
  const t = type.toLowerCase()
  if (t.includes('order')) return ShoppingBag
  if (t.includes('delivery') || t.includes('transport')) return Truck
  if (t.includes('payment') || t.includes('chapa') || t.includes('escrow')) return CreditCard
  if (t.includes('message') || t.includes('inquiry')) return Mail
  return Bell
}

const getIconBg = (type: string = '') => {
  const t = type.toLowerCase()
  if (t.includes('order')) return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20'
  if (t.includes('delivery') || t.includes('transport')) return 'bg-blue-500/10 text-blue-500 border-blue-500/20'
  if (t.includes('payment') || t.includes('chapa') || t.includes('escrow')) return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'
  return 'bg-amber-500/10 text-amber-500 border-amber-500/20'
}

const formatTime = (ts: string) => {
  if (!ts) return 'Just now'
  const date = new Date(ts)
  const now = new Date()
  const diffHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60))
  if (diffHours < 1) return 'Just now'
  if (diffHours < 24) return `${diffHours}h ago`
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchNotifications()
})
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
