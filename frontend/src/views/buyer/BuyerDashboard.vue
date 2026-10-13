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
          <!-- 💡 Circular Dark/Light Mode Icon Button (በምስሉ መሰረት የተስተካከለ) -->
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
            <table class="w-full text-left border-collapse min-w-[650px]">
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
                <tr v-if="!filteredOrders.length">
                  <td colspan="7" class="text-center py-10 text-slate-400 font-medium">
                    No orders match your filter
                  </td>
                </tr>
              </tbody>
            </table>
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
          <div class="flex justify-between items-center mb-6">
            <div>
              <h2 :class="['text-base font-bold', darkMode ? 'text-slate-100' : 'text-slate-900']">Purchase Analytics</h2>
              <p :class="['text-xs mt-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Visual insights into your spending patterns and order history
              </p>
            </div>
          </div>
          
          <!-- Charts Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- 1. Monthly Spending Trend (Line Chart) -->
            <div :class="['border rounded-xl p-5', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-white border-slate-200/80']">
              <h3 :class="['font-bold text-sm mb-4 flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                📈 Monthly Spending Trend
                <span :class="['text-[10px] font-normal px-2 py-0.5 rounded-full', darkMode ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-500']">
                  Last 12 months
                </span>
              </h3>
              <div style="position: relative; height: 300px;">
                <canvas ref="spendingTrendChart"></canvas>
              </div>
            </div>

            <!-- 2. Order Status Distribution (Doughnut Chart) -->
            <div :class="['border rounded-xl p-5', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-white border-slate-200/80']">
              <h3 :class="['font-bold text-sm mb-4 flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                🍩 Order Status Distribution
                <span :class="['text-[10px] font-normal px-2 py-0.5 rounded-full', darkMode ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-500']">
                  All time
                </span>
              </h3>
              <div style="position: relative; height: 300px;">
                <canvas ref="statusChart"></canvas>
              </div>
            </div>
          </div>

          <!-- 3. Purchases by Category (Bar Chart) - Full Width -->
          <div :class="['border rounded-xl p-5 mb-6', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-white border-slate-200/80']">
            <h3 :class="['font-bold text-sm mb-4 flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
              📊 Purchases by Category
              <span :class="['text-[10px] font-normal px-2 py-0.5 rounded-full', darkMode ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-500']">
                Completed orders
              </span>
            </h3>
            
            <!-- No Data Message -->
            <div v-if="!dashboard?.purchases_by_category || dashboard.purchases_by_category.length === 0" class="text-center py-12">
              <i class="fas fa-chart-bar text-4xl mb-3" :class="darkMode ? 'text-slate-700' : 'text-slate-300'"></i>
              <p :class="['text-sm font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                No purchase data available yet
              </p>
              <p :class="['text-xs mt-1', darkMode ? 'text-slate-500' : 'text-slate-400']">
                Start ordering to see your category breakdown
              </p>
            </div>
            
            <div v-else style="position: relative; height: 350px;">
              <canvas ref="categoryChart"></canvas>
            </div>
            
            <!-- Category Stats Summary -->
            <div v-if="dashboard?.purchases_by_category && dashboard.purchases_by_category.length > 0" class="mt-5 pt-4 border-t" :class="darkMode ? 'border-slate-800' : 'border-slate-200'">
              <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <div
                  v-for="(cat, idx) in dashboard.purchases_by_category.slice(0, 4)"
                  :key="idx"
                  :class="['rounded-lg p-3 text-center', darkMode ? 'bg-[#131b2e]' : 'bg-slate-50']"
                >
                  <p :class="['text-xs font-semibold mb-1 truncate', darkMode ? 'text-slate-400' : 'text-slate-500']">
                    {{ cat.category }}
                  </p>
                  <p :class="['text-lg font-black', darkMode ? 'text-slate-100' : 'text-slate-900']">
                    {{ cat.count }}
                  </p>
                  <p :class="['text-[10px] font-medium', darkMode ? 'text-slate-500' : 'text-slate-400']">
                    ETB {{ formatNumber(cat.spent) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Spending Overview Stats -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div :class="['border rounded-xl p-4', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-white border-slate-200/80']">
              <h3 :class="['font-bold text-sm mb-3', darkMode ? 'text-slate-100' : 'text-slate-900']">💰 Spending Overview</h3>
              <div :class="['divide-y text-xs', darkMode ? 'divide-slate-800 text-slate-300' : 'divide-slate-100 text-slate-600']">
                <p class="flex justify-between py-2.5">
                  <span>Total Spent</span>
                  <strong :class="darkMode ? 'text-slate-100' : 'text-slate-900'">ETB {{ formatNumber(dashboard?.summary?.total_spent) }}</strong>
                </p>
                <p class="flex justify-between py-2.5">
                  <span>Average Order Value</span>
                  <strong :class="darkMode ? 'text-slate-100' : 'text-slate-900'">ETB {{ formatNumber(dashboard?.summary?.average_order_value) }}</strong>
                </p>
                <p class="flex justify-between py-2.5">
                  <span>Total Orders</span>
                  <strong :class="darkMode ? 'text-slate-100' : 'text-slate-900'">{{ dashboard?.summary?.total_orders || 0 }}</strong>
                </p>
              </div>
            </div>

            <div :class="['border rounded-xl p-4', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-white border-slate-200/80']">
              <h3 :class="['font-bold text-sm mb-3', darkMode ? 'text-slate-100' : 'text-slate-900']">📦 Order Breakdown</h3>
              <div :class="['divide-y text-xs', darkMode ? 'divide-slate-800 text-slate-300' : 'divide-slate-100 text-slate-600']">
                <p class="flex justify-between py-2.5">
                  <span>🟢 Completed</span>
                  <strong class="text-emerald-600">{{ dashboard?.summary?.completed_orders || 0 }}</strong>
                </p>
                <p class="flex justify-between py-2.5">
                  <span>🟡 Pending</span>
                  <strong class="text-amber-600">{{ dashboard?.summary?.pending_orders || 0 }}</strong>
                </p>
                <p class="flex justify-between py-2.5">
                  <span>🔴 Cancelled</span>
                  <strong class="text-red-600">{{ dashboard?.summary?.cancelled_orders || 0 }}</strong>
                </p>
              </div>
            </div>

            <div :class="['border rounded-xl p-4', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-white border-slate-200/80']">
              <h3 :class="['font-bold text-sm mb-3', darkMode ? 'text-slate-100' : 'text-slate-900']">📈 Insights</h3>
              <div :class="['space-y-2 text-xs', darkMode ? 'text-slate-300' : 'text-slate-600']">
                <p class="flex items-start gap-2">
                  <span class="text-blue-500">•</span>
                  <span>Most orders placed in last quarter</span>
                </p>
                <p class="flex items-start gap-2">
                  <span class="text-green-500">•</span>
                  <span>{{ dashboard?.summary?.completed_orders || 0 }} successful deliveries</span>
                </p>
                <p class="flex items-start gap-2">
                  <span class="text-purple-500">•</span>
                  <span>{{ dashboard?.purchases_by_category?.length || 0 }} categories purchased</span>
                </p>
              </div>
            </div>
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

// Use themeStore for dark mode (synchronized across all pages)
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
  
  if (activeTab.value === 'analytics') {
    createSpendingTrendChart()
    createOrderStatusChart()
    createCategoryChart()
  }
}

// 1. Monthly Spending Trend Chart (Line Chart)
const createSpendingTrendChart = () => {
  if (!spendingTrendChart.value) {
    console.warn('Spending chart ref not found')
    return
  }
  
  try {
    // Destroy existing chart
    if (spendingChart) {
      spendingChart.destroy()
    }
    
    const ctx = spendingTrendChart.value.getContext('2d')
    
    // Get data from API or use empty arrays
    const spendingData = dashboard.value?.spending_by_month || []
    
    // Fill in missing months for last 12 months
    const allMonths = []
    const allSpending = []
    const now = new Date()
    
    for (let i = 11; i >= 0; i--) {
      const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
      const monthLabel = d.toLocaleDateString('en-US', { month: 'short' })
      const yearMonth = d.toISOString().slice(0, 7)
      
      allMonths.push(monthLabel)
      
      const found = spendingData.find(item => item.year_month === yearMonth)
      allSpending.push(found ? parseFloat(found.spent) : 0)
    }
    
    spendingChart = new window.Chart(ctx, {
      type: 'line',
      data: {
        labels: allMonths,
        datasets: [{
          label: 'Monthly Spending (ETB)',
          data: allSpending,
          borderColor: darkMode.value ? '#60a5fa' : '#3b82f6',
          backgroundColor: darkMode.value ? 'rgba(96, 165, 250, 0.1)' : 'rgba(59, 130, 246, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: darkMode.value ? '#60a5fa' : '#3b82f6',
          pointBorderColor: darkMode.value ? '#1e293b' : '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              color: darkMode.value ? '#cbd5e1' : '#475569',
              font: { size: 12, weight: 'bold' }
            }
          },
          tooltip: {
            backgroundColor: darkMode.value ? '#1e293b' : '#ffffff',
            titleColor: darkMode.value ? '#f1f5f9' : '#0f172a',
            bodyColor: darkMode.value ? '#cbd5e1' : '#475569',
            borderColor: darkMode.value ? '#334155' : '#e2e8f0',
            borderWidth: 1,
            padding: 12,
            displayColors: false,
            callbacks: {
              label: (context) => `ETB ${context.parsed.y.toLocaleString()}`
            }
          }
        },
        scales: {
          x: {
            grid: {
              color: darkMode.value ? '#1e293b' : '#f1f5f9',
              drawBorder: false
            },
            ticks: {
              color: darkMode.value ? '#94a3b8' : '#64748b',
              font: { size: 11, weight: 'bold' }
            }
          },
          y: {
            beginAtZero: true,
            grid: {
              color: darkMode.value ? '#1e293b' : '#f1f5f9',
              drawBorder: false
            },
            ticks: {
              color: darkMode.value ? '#94a3b8' : '#64748b',
              font: { size: 11, weight: 'bold' },
              callback: (value) => {
                if (value >= 1000) return `${(value / 1000).toFixed(0)}k`
                return value
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

// 2. Order Status Distribution Chart (Doughnut Chart)
const createOrderStatusChart = () => {
  if (!statusChart.value) {
    console.warn('Status chart ref not found')
    return
  }
  
  try {
    // Destroy existing chart
    if (orderStatusChart) {
      orderStatusChart.destroy()
    }
    
    const ctx = statusChart.value.getContext('2d')
    
    // Get data from dashboard or use sample data
    const completed = dashboard.value?.summary?.completed_orders || 0
    const pending = dashboard.value?.summary?.pending_orders || 0
    const cancelled = dashboard.value?.summary?.cancelled_orders || 0
    
    orderStatusChart = new window.Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['🟢 Completed', '🟡 Pending', '🔴 Cancelled'],
        datasets: [{
          data: [completed, pending, cancelled],
          backgroundColor: [
            '#10b981', // Green for completed
            '#f59e0b', // Yellow for pending
            '#ef4444'  // Red for cancelled
          ],
          borderColor: darkMode.value ? '#0b0f19' : '#ffffff',
          borderWidth: 3,
          hoverOffset: 10
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            labels: {
              color: darkMode.value ? '#cbd5e1' : '#475569',
              font: { size: 12, weight: 'bold' },
              padding: 15,
              usePointStyle: true,
              pointStyle: 'circle'
            }
          },
          tooltip: {
            backgroundColor: darkMode.value ? '#1e293b' : '#ffffff',
            titleColor: darkMode.value ? '#f1f5f9' : '#0f172a',
            bodyColor: darkMode.value ? '#cbd5e1' : '#475569',
            borderColor: darkMode.value ? '#334155' : '#e2e8f0',
            borderWidth: 1,
            padding: 12,
            callbacks: {
              label: (context) => {
                const label = context.label || ''
                const value = context.parsed || 0
                const total = context.dataset.data.reduce((a, b) => a + b, 0)
                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0
                return `${label}: ${value} orders (${percentage}%)`
              }
            }
          }
        }
      }
    })
  } catch (err) {
    console.error('Error creating status chart:', err)
  }
}

// 3. Purchases by Category Chart (Bar Chart)
const createCategoryChart = () => {
  if (!categoryChart.value) {
    console.warn('Category chart ref not found')
    return
  }
  
  try {
    // Destroy existing chart
    if (purchaseCategoryChart) {
      purchaseCategoryChart.destroy()
    }
    
    const ctx = categoryChart.value.getContext('2d')
    
    // Get data from API
    const categoryData = dashboard.value?.purchases_by_category || []
    
    // Extract categories and purchase counts
    const categories = categoryData.map(item => item.category || 'Uncategorized')
    const purchaseCounts = categoryData.map(item => parseInt(item.count) || 0)
    
    // If no data, show placeholder
    const hasData = categories.length > 0
    const displayCategories = hasData ? categories : ['No Data']
    const displayData = hasData ? purchaseCounts : [0]
    
    // Color palette for categories
    const colors = [
      '#f59e0b', // Amber
      '#8b5cf6', // Purple
      '#10b981', // Green
      '#ef4444', // Red
      '#3b82f6', // Blue
      '#ec4899', // Pink
      '#06b6d4', // Cyan
      '#f97316', // Orange
      '#14b8a6', // Teal
      '#a855f7', // Violet
    ]
    
    const backgroundColors = displayData.map((_, index) => colors[index % colors.length])
    
    purchaseCategoryChart = new window.Chart(ctx, {
      type: 'bar',
      data: {
        labels: displayCategories,
        datasets: [{
          label: 'Number of Purchases',
          data: displayData,
          backgroundColor: backgroundColors,
          borderColor: darkMode.value ? '#0b0f19' : '#ffffff',
          borderWidth: 2,
          borderRadius: 8,
          barThickness: 'flex',
          maxBarThickness: 60
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: darkMode.value ? '#1e293b' : '#ffffff',
            titleColor: darkMode.value ? '#f1f5f9' : '#0f172a',
            bodyColor: darkMode.value ? '#cbd5e1' : '#475569',
            borderColor: darkMode.value ? '#334155' : '#e2e8f0',
            borderWidth: 1,
            padding: 12,
            displayColors: false,
            callbacks: {
              label: (context) => `${context.parsed.y} purchase${context.parsed.y !== 1 ? 's' : ''}`
            }
          }
        },
        scales: {
          x: {
            grid: {
              display: false,
              drawBorder: false
            },
            ticks: {
              color: darkMode.value ? '#94a3b8' : '#64748b',
              font: { size: 11, weight: 'bold' },
              maxRotation: 45,
              minRotation: 0
            }
          },
          y: {
            beginAtZero: true,
            grid: {
              color: darkMode.value ? '#1e293b' : '#f1f5f9',
              drawBorder: false
            },
            ticks: {
              color: darkMode.value ? '#94a3b8' : '#64748b',
              font: { size: 11, weight: 'bold' },
              stepSize: 1,
              callback: (value) => Math.floor(value) === value ? value : ''
            }
          }
        }
      }
    })
  } catch (err) {
    console.error('Error creating category chart:', err)
  }
}

// Watch for tab changes to initialize charts
watch(activeTab, async (newTab) => {
  if (newTab === 'analytics') {
    await nextTick()
    setTimeout(() => {
      initializeCharts()
    }, 100)
  }
})

// Watch for theme changes to update charts
watch(darkMode, async () => {
  if (activeTab.value === 'analytics') {
    await nextTick()
    setTimeout(() => {
      initializeCharts()
    }, 100)
  }
})

onMounted(async () => {
  themeStore.initializeTheme()
  await fetchDashboardData()
})

/* -------- Computed Properties -------- */

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

/* -------- Helpers -------- */

const getStatusClass = (status) => {
  switch (status) {
    case 'completed':
    case 'delivered': 
      return 'bg-emerald-50 text-emerald-600'
    case 'pending': 
      return 'bg-amber-50 text-amber-600'
    case 'cancelled': 
      return 'bg-red-50 text-red-600'
    default: 
      return 'bg-slate-100 text-slate-600'
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
    
    // Refresh charts if on analytics tab
    if (activeTab.value === 'analytics') {
      await nextTick()
      setTimeout(() => {
        initializeCharts()
      }, 150)
    }
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