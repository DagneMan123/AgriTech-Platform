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
      <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div class="flex items-center gap-3">
          <!-- Mobile Sidebar Hamburger Toggle Button -->
          <button 
            @click="sidebarOpen = !sidebarOpen" 
            :class="['lg:hidden p-2.5 rounded-xl border transition-colors cursor-pointer', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-200 hover:bg-slate-800' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50']"
            aria-label="Toggle Navigation Menu"
          >
            <i class="fas" :class="sidebarOpen ? 'fa-xmark' : 'fa-bars'"></i>
          </button>

          <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">Buyer Dashboard</h1>
            <p :class="['text-sm mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">Manage your orders, deliveries, and purchases</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <!-- Circular Dark/Light Mode Toggle Button -->
          <button 
            @click="toggleDarkMode" 
            type="button"
            :class="[
              'flex items-center justify-center h-10 w-10 rounded-full border transition-all duration-300 cursor-pointer shadow-sm focus:outline-none',
              darkMode 
                ? 'bg-[#131b2e] border-slate-700/80 text-amber-400 hover:bg-slate-800' 
                : 'bg-white border-slate-200 text-amber-500 hover:bg-slate-50'
            ]"
            :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
          >
            <span class="text-base select-none">{{ darkMode ? '🌙' : '☀️' }}</span>
          </button>

          <!-- Refresh Data Button -->
          <button 
            :class="['inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs lg:text-sm font-semibold border transition-all cursor-pointer shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-200 hover:border-blue-500' : 'bg-white border-slate-200 text-slate-700 hover:text-blue-600 hover:border-blue-500']"
            :disabled="loading" 
            @click="fetchDashboardData"
          >
            <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-rotate-right'"></i>
            <span class="hidden sm:inline">Refresh</span>
          </button>
        </div>
      </header>

      <!-- Error Message Banner -->
      <div v-if="error" class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl mb-6 text-sm font-medium" role="alert">
        <i class="fas fa-exclamation-circle text-lg shrink-0"></i>
        <span>{{ error }}</span>
      </div>

      <!-- Summary Statistics Grid -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6" aria-label="Summary statistics">
        <article
          v-for="card in summaryCards"
          :key="card.key"
          :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs hover:shadow-md transition-all flex justify-between items-start', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200/60 text-slate-900 !border-l-blue-600']"
        >
          <div class="min-w-0 flex-1">
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1 truncate', darkMode ? 'text-slate-400' : 'text-slate-500']">{{ card.label }}</h3>
            <p class="text-xl lg:text-2xl font-black tracking-tight">{{ card.value }}</p>
            <p :class="['text-[11px] font-medium truncate mt-1', darkMode ? 'text-slate-500' : 'text-slate-400']">{{ card.sub }}</p>
          </div>
          <div :class="['text-lg shrink-0 pt-0.5', darkMode ? 'text-blue-400' : 'text-slate-400']">
            <i :class="card.icon"></i>
          </div>
        </article>
      </section>

      <!-- Main Section Tabs Container -->
      <section :class="['rounded-2xl border shadow-2xs mb-6 overflow-hidden', darkMode ? 'bg-[#131b2e] border-slate-800/80' : 'bg-white border-slate-200/80']">
        <!-- Navigation Tabs Bar -->
        <nav :class="['flex border-b overflow-x-auto px-4 gap-2 scrollbar-none', darkMode ? 'border-slate-800' : 'border-slate-200']" role="tablist">
          <button
            v-for="tab in tabs"
            :key="tab"
            :class="[
              'px-4 py-3.5 text-sm font-bold whitespace-nowrap border-b-2 transition-all cursor-pointer',
              activeTab === tab 
                ? 'border-blue-600 text-blue-600 bg-blue-50/50' 
                : darkMode ? 'border-transparent text-slate-400 hover:text-slate-200' : 'border-transparent text-slate-500 hover:text-slate-800'
            ]"
            role="tab"
            :aria-selected="activeTab === tab"
            @click="activeTab = tab"
          >
            {{ formatTabName(tab) }}
          </button>
        </nav>

        <!-- Orders Tab Content -->
        <div v-show="activeTab === 'orders'" class="p-6" role="tabpanel">
          <div class="flex justify-between items-center gap-4 mb-5 flex-wrap">
            <h2 :class="['text-base font-bold', darkMode ? 'text-slate-100' : 'text-slate-900']">My Orders</h2>
            <select v-model="orderFilter" :class="['px-3 py-1.5 border rounded-lg text-xs font-semibold focus:outline-none focus:border-blue-600 cursor-pointer', darkMode ? 'bg-[#0b0f19] border-slate-800 text-slate-200' : 'bg-white border-slate-200 text-slate-700']">
              <option value="">All Orders</option>
              <option value="pending">Pending</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <div :class="['overflow-x-auto rounded-lg border', darkMode ? 'border-slate-800' : 'border-slate-100']">
            <table class="w-full text-left border-collapse min-w-[650px]" v-if="filteredOrders.length">
              <thead>
                <tr :class="['text-[11px] font-bold uppercase tracking-wider border-b', darkMode ? 'bg-[#0b0f19]/80 text-slate-400 border-slate-800' : 'bg-slate-50 text-slate-400 border-slate-100']">
                  <th class="p-3">Order ID</th>
                  <th class="p-3">Supplier</th>
                  <th class="p-3">Items</th>
                  <th class="p-3">Status</th>
                  <th class="p-3">Total</th>
                  <th class="p-3">Date</th>
                  <th class="p-3">Actions</th>
                </tr>
              </thead>
              <tbody :class="['divide-y text-xs font-semibold', darkMode ? 'divide-slate-800/80 text-slate-300' : 'divide-slate-100 text-slate-600']">
                <tr v-for="order in filteredOrders" :key="order.id" :class="[darkMode ? 'hover:bg-[#0b0f19]/50' : 'hover:bg-slate-50/50']">
                  <td :class="['p-3 font-bold', darkMode ? 'text-slate-100' : 'text-slate-900']">#{{ order.id }}</td>
                  <td class="p-3">{{ order.supplier?.name || 'N/A' }}</td>
                  <td class="p-3">{{ order.items?.length || 0 }}</td>
                  <td class="p-3">
                    <span :class="['inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold capitalize', getStatusClass(order.status)]">
                      {{ order.status }}
                    </span>
                  </td>
                  <td :class="['p-3 font-bold', darkMode ? 'text-slate-100' : 'text-slate-900']">ETB {{ formatNumber(order.total_amount || order.grand_total) }}</td>
                  <td class="p-3">{{ formatDate(order.created_at) }}</td>
                  <td class="p-3">
                    <button class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-md font-bold transition-colors cursor-pointer" @click="viewOrder(order)">
                      View
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- English Professional Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-12 px-4 text-center">
              <div :class="['h-16 w-16 rounded-2xl flex items-center justify-center text-2xl mb-4 border shadow-inner', darkMode ? 'bg-slate-800/50 border-slate-700/80 text-blue-400' : 'bg-blue-50 border-blue-100 text-blue-600']">
                <i class="fas fa-box-open"></i>
              </div>
              <h3 :class="['text-base font-bold mb-1', darkMode ? 'text-slate-200' : 'text-slate-800']">
                No Orders Found
              </h3>
              <p :class="['text-xs max-w-sm mb-5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                You haven't placed any agricultural orders yet, or no orders match your selected filter.
              </p>
              <router-link to="/marketplace" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                <i class="fas fa-shopping-cart"></i> Browse Marketplace
              </router-link>
            </div>
          </div>
        </div>

        <!-- Deliveries Tab Content -->
        <div v-show="activeTab === 'deliveries'" class="p-6" role="tabpanel">
          <h2 :class="['text-base font-bold mb-4', darkMode ? 'text-slate-100' : 'text-slate-900']">Active Deliveries</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <article
              v-for="delivery in dashboard?.active_deliveries || []"
              :key="delivery.id"
              :class="['border rounded-xl p-4 hover:shadow-sm', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-white border-slate-200/80']"
            >
              <div class="flex justify-between items-center mb-3">
                <h3 :class="['font-bold text-sm', darkMode ? 'text-slate-100' : 'text-slate-900']">Order #{{ delivery.order_id }}</h3>
                <span class="px-2.5 py-0.5 bg-blue-50 text-blue-600 rounded-full text-[11px] font-bold capitalize">{{ delivery.status }}</span>
              </div>
              <div :class="['space-y-1.5 text-xs mb-4', darkMode ? 'text-slate-300' : 'text-slate-600']">
                <p><strong :class="darkMode ? 'text-slate-100' : 'text-slate-800'">From:</strong> {{ delivery.from_location || 'Farm Location' }}</p>
                <p><strong :class="darkMode ? 'text-slate-100' : 'text-slate-800'">To:</strong> {{ delivery.to_location || 'Destination' }}</p>
              </div>
              <button class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer" @click="trackDelivery(delivery)">Track Order</button>
            </article>

            <div v-if="!(dashboard?.active_deliveries || []).length" class="col-span-full text-center py-10 text-slate-400 font-medium">
              No active deliveries at the moment
            </div>
          </div>
        </div>

        <!-- Cart Tab Content -->
        <div v-show="activeTab === 'cart'" class="p-6" role="tabpanel">
          <h2 :class="['text-base font-bold mb-4', darkMode ? 'text-slate-100' : 'text-slate-900']">Shopping Cart Summary</h2>
          <div class="text-center py-10 text-slate-400 font-medium">
            You have {{ dashboard?.summary?.cart_items_count || 0 }} items in your shopping cart.
          </div>
        </div>

        <!-- Reviews Tab Content -->
        <div v-show="activeTab === 'reviews'" class="p-6" role="tabpanel">
          <h2 :class="['text-base font-bold mb-4', darkMode ? 'text-slate-100' : 'text-slate-900']">My Written Reviews</h2>
          <div class="text-center py-10 text-slate-400 font-medium">
            No reviews written yet.
          </div>
        </div>

        <!-- Wishlist Tab Content -->
        <div v-show="activeTab === 'wishlist'" class="p-6" role="tabpanel">
          <h2 :class="['text-base font-bold mb-4', darkMode ? 'text-slate-100' : 'text-slate-900']">My Wishlist</h2>
          <div class="text-center py-10 text-slate-400 font-medium">
            You have {{ dashboard?.summary?.wishlist_items_count || 0 }} items saved in your wishlist.
          </div>
        </div>

        <!-- Analytics Tab Content -->
        <div v-show="activeTab === 'analytics'" class="p-6" role="tabpanel">
          <h2 :class="['text-base font-bold mb-2', darkMode ? 'text-slate-100' : 'text-slate-900']">Purchase Analytics</h2>
          <p :class="['text-xs mb-6', darkMode ? 'text-slate-400' : 'text-slate-500']">Insights into your spending trends and order statistics.</p>
        </div>
      </section>

      <!-- PERSISTENT BOTTOM ANALYTICS & CHARTS SECTION -->
      <section class="mt-8 space-y-6">
        <h2 :class="['text-lg font-bold flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
          <i class="fas fa-chart-line text-blue-500"></i> Analytics Overview
        </h2>

        <!-- Top Row Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- 1. Monthly Spending Trend -->
          <div :class="['border rounded-2xl p-5 shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800/80' : 'bg-white border-slate-200/80']">
            <h3 :class="['font-bold text-sm mb-4', darkMode ? 'text-slate-100' : 'text-slate-900']">📈 Monthly Spending Trend</h3>
            <div class="relative h-[280px] w-full">
              <canvas ref="spendingTrendChart"></canvas>
            </div>
          </div>

          <!-- 2. Order Status Distribution -->
          <div :class="['border rounded-2xl p-5 shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800/80' : 'bg-white border-slate-200/80']">
            <h3 :class="['font-bold text-sm mb-4', darkMode ? 'text-slate-100' : 'text-slate-900']">🍩 Order Status Distribution</h3>
            <div class="relative h-[280px] w-full">
              <canvas ref="statusChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Bottom Full-Width Bar Chart -->
        <div :class="['border rounded-2xl p-5 shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800/80' : 'bg-white border-slate-200/80']">
          <h3 :class="['font-bold text-sm mb-4', darkMode ? 'text-slate-100' : 'text-slate-900']">📊 Purchases by Category</h3>
          <div class="relative h-[320px] w-full">
            <canvas ref="categoryChart"></canvas>
          </div>
        </div>
      </section>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { useRouter } from 'vue-router'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

const auth = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()

const activeTab = ref('orders')
const tabs = ['orders', 'deliveries', 'cart', 'reviews', 'wishlist', 'analytics']

const dashboard = ref(null)
const orderFilter = ref('')
const loading = ref(false)
const error = ref('')
const sidebarOpen = ref(false)

// Chart refs
const spendingTrendChart = ref(null)
const statusChart = ref(null)
const categoryChart = ref(null)

// Chart instances
let spendingChart = null
let orderStatusChart = null
let purchaseCategoryChart = null

const darkMode = computed(() => themeStore.isDark)

const toggleDarkMode = () => {
  themeStore.toggleTheme()
}

// Load Chart.js from CDN
const loadChartJS = () => {
  return new Promise((resolve, reject) => {
    if (window.Chart) {
      resolve()
      return
    }
    const script = document.createElement('script')
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js'
    script.onload = resolve
    script.onerror = reject
    document.head.appendChild(script)
  })
}

// Initialize all charts
const initializeCharts = async () => {
  await loadChartJS()
  await nextTick()
  
  createSpendingTrendChart()
  createOrderStatusChart()
  createCategoryChart()
}

// 1. Monthly Spending Trend Chart (የተስተካከለው Y-axis ሎጂክ)
const createSpendingTrendChart = () => {
  if (!spendingTrendChart.value) return
  try {
    if (spendingChart) spendingChart.destroy()
    const ctx = spendingTrendChart.value.getContext('2d')
    
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    
    // ከ Backend የሚመጣውን Real Data መጠቀም ወይም አነስተኛ ወጪዎችንም (ለምሳሌ 500) ማካተት
    const rawData = dashboard.value?.spending_by_month || []
    const spendingData = rawData.length > 0 
      ? rawData.map(item => Number(item.spent) || 0)
      : [500, 1500, 18000, 22000, 19000, 25000, 28000, 30000, 27000, 32000, 35000, 38000]
    
    spendingChart = new window.Chart(ctx, {
      type: 'line',
      data: {
        labels: months,
        datasets: [{
          label: 'Monthly Spending (ETB)',
          data: spendingData,
          borderColor: darkMode.value ? '#60a5fa' : '#3b82f6',
          backgroundColor: darkMode.value ? 'rgba(96, 165, 250, 0.1)' : 'rgba(59, 130, 246, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          tooltip: {
            callbacks: {
              label: (context) => `ETB ${context.parsed.y.toLocaleString()}`
            }
          }
        },
        scales: {
          x: {
            grid: { color: darkMode.value ? '#1e293b' : '#f1f5f9' },
            ticks: { color: darkMode.value ? '#94a3b8' : '#64748b' }
          },
          y: {
            beginAtZero: true, // 💡 Y-axis ከ 0 ETB እንዲጀምር ያደርጋል
            grid: { color: darkMode.value ? '#1e293b' : '#f1f5f9' },
            ticks: {
              color: darkMode.value ? '#94a3b8' : '#64748b',
              callback: function(value) {
                if (value >= 1000) {
                  return (value / 1000) + 'k' // 1,000 እና ከዚያ በላይ ሲሆን 1k, 2k... ያደርጋል
                }
                return value // ከ 1,000 በታች ከሆነ (ለምሳሌ 500) ቁጥሩን ቀጥታ ያሳያል
              }
            }
          }
        }
      }
    })
  } catch (err) {
    console.error('Error creating spending chart:', err)
  }
}

// 2. Order Status Distribution Chart
const createOrderStatusChart = () => {
  if (!statusChart.value) return
  try {
    if (orderStatusChart) orderStatusChart.destroy()
    const ctx = statusChart.value.getContext('2d')
    const completed = dashboard.value?.summary?.completed_orders || 45
    const pending = dashboard.value?.summary?.pending_orders || 12
    const cancelled = dashboard.value?.summary?.cancelled_orders || 3
    
    orderStatusChart = new window.Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Completed', 'Pending', 'Cancelled'],
        datasets: [{
          data: [completed, pending, cancelled],
          backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
          borderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    })
  } catch (err) {
    console.error('Error creating status chart:', err)
  }
}

// 3. Purchases by Category Chart
const createCategoryChart = () => {
  if (!categoryChart.value) return
  try {
    if (purchaseCategoryChart) purchaseCategoryChart.destroy()
    const ctx = categoryChart.value.getContext('2d')
    const categories = ['Cereals & Grains', 'Coffee & Teas', 'Vegetables', 'Fruits', 'Dairy', 'Livestock']
    const purchaseData = [28, 45, 32, 38, 22, 15]
    
    purchaseCategoryChart = new window.Chart(ctx, {
      type: 'bar',
      data: {
        labels: categories,
        datasets: [{
          label: 'Number of Purchases',
          data: purchaseData,
          backgroundColor: ['#f59e0b', '#8b5cf6', '#10b981', '#ef4444', '#3b82f6', '#ec4899'],
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    })
  } catch (err) {
    console.error('Error creating category chart:', err)
  }
}

watch(darkMode, async () => {
  await nextTick()
  initializeCharts()
})

onMounted(async () => {
  themeStore.initializeTheme()
  await fetchDashboardData()
  initializeCharts()
})

const filteredOrders = computed(() => {
  const orders = dashboard.value?.recent_orders || []
  if (!orderFilter.value) return orders
  return orders.filter(o => o.status === orderFilter.value)
})

const summaryCards = computed(() => {
  const s = dashboard.value?.summary
  return [
    { key: 'total', label: 'Total Orders', value: s?.total_orders || 0, sub: 'All purchases', icon: 'fas fa-shopping-cart' },
    { key: 'pending', label: 'Pending Orders', value: s?.pending_orders || 0, sub: 'Awaiting shipment', icon: 'fas fa-clock' },
    { key: 'completed', label: 'Completed Orders', value: s?.completed_orders || 0, sub: 'Delivered', icon: 'fas fa-check-circle' },
    { key: 'spent', label: 'Total Spent', value: `ETB ${formatNumber(s?.total_spent)}`, sub: `Avg: ETB ${formatNumber(s?.average_order_value)}`, icon: 'fas fa-wallet' },
    { key: 'cart', label: 'Cart Items', value: s?.cart_items_count || 0, sub: 'Ready to checkout', icon: 'fas fa-shopping-bag' },
    { key: 'wishlist', label: 'Wishlist Items', value: s?.wishlist_items_count || 0, sub: 'Saved items', icon: 'fas fa-heart' }
  ]
})

const getStatusClass = (status) => {
  switch (status) {
    case 'completed':
    case 'delivered': return 'bg-emerald-50 text-emerald-600'
    case 'pending': return 'bg-amber-50 text-amber-600'
    case 'cancelled': return 'bg-red-50 text-red-600'
    default: return 'bg-slate-100 text-slate-600'
  }
}

async function fetchDashboardData() {
  loading.value = true
  error.value = ''
  try {
    const res = await fetch('/api/buyer/dashboard', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    if (!res.ok) throw new Error(`Request failed (${res.status})`)
    const data = await res.json()
    dashboard.value = data
  } catch (err) {
    console.error('Error fetching dashboard:', err)
    error.value = 'Unable to load dashboard data. Please try again.'
  } finally {
    loading.value = false
  }
}

const formatNumber = (num) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'
const formatTabName = (tab) => ({ orders: 'My Orders', deliveries: 'Deliveries', cart: 'Shopping Cart', reviews: 'Reviews', wishlist: 'Wishlist', analytics: 'Analytics' }[tab] || tab)

const handleLogout = async () => { await auth.logout(); router.push('/login'); }
const viewOrder = (order) => router.push(`/buyer/orders/${order.id}`)
const trackDelivery = (delivery) => router.push(`/buyer/deliveries/${delivery.id}/track`)
</script>