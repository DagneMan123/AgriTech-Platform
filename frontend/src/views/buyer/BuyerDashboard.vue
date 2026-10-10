<template>
  <!-- ዋናው ገጽ Background (በምስሉ መሰረት Soft Gray) -->
  <div class="flex min-h-screen bg-slate-100 text-slate-800 font-sans antialiased">
    <BuyerSidebar @logout="handleLogout" />

    <!-- Dashboard Content Area -->
    <main class="ml-0 lg:ml-64 flex-1 p-6 lg:p-8 overflow-y-auto min-h-screen" role="main">
      
      <!-- Header -->
      <header class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
          <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight text-slate-900">Buyer Dashboard</h1>
          <p class="text-slate-500 text-sm mt-1">Manage your orders, deliveries, and purchases</p>
        </div>
        <div class="flex items-center gap-3">
          <button 
            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs lg:text-sm font-semibold text-slate-700 hover:text-emerald-600 hover:border-emerald-500 transition-all shadow-2xs cursor-pointer"
            :disabled="loading" 
            @click="fetchDashboardData"
          >
            <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-rotate-right'"></i>
            <span>Refresh</span>
          </button>
        </div>
      </header>

      <!-- Summary Stats Cards (በምስሉ አሰራር መሰረት በግራ በኩል አረንጓዴ መስመር ያላቸው) -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <article
          v-for="card in summaryCards"
          :key="card.key"
          class="bg-white rounded-xl p-4 border-l-4 border-l-emerald-500 border-y border-r border-slate-200/60 shadow-2xs hover:shadow-md transition-all flex justify-between items-start"
        >
          <div class="min-w-0 flex-1">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1 truncate">{{ card.label }}</h3>
            <p class="text-xl lg:text-2xl font-black text-slate-900 tracking-tight">{{ card.value }}</p>
            <p class="text-[11px] text-slate-400 font-medium truncate mt-1">{{ card.sub }}</p>
          </div>
          <!-- በቀኝ በኩል የሚቀመጥ አዶ -->
          <div class="text-slate-400 text-lg shrink-0 pt-0.5">
            <i :class="card.icon"></i>
          </div>
        </article>
      </section>

      <!-- Main Section Container (በምስሉ መሰረት ነጭ ቦክስ) -->
      <section class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs mb-6 overflow-hidden">
        <!-- Tabs Bar -->
        <nav class="flex border-b border-slate-200 overflow-x-auto px-4 gap-2 scrollbar-none" role="tablist">
          <button
            v-for="tab in tabs"
            :key="tab"
            :class="[
              'px-4 py-3.5 text-sm font-bold whitespace-nowrap border-b-2 transition-all cursor-pointer',
              activeTab === tab 
                ? 'border-emerald-500 text-emerald-600 bg-emerald-50/30' 
                : 'border-transparent text-slate-500 hover:text-slate-800'
            ]"
            @click="activeTab = tab"
          >
            {{ formatTabName(tab) }}
          </button>
        </nav>

        <!-- Orders Content -->
        <div v-show="activeTab === 'orders'" class="p-6">
          <div class="flex justify-between items-center gap-4 mb-5 flex-wrap">
            <h2 class="text-base font-bold text-slate-900">My Orders</h2>
            <select v-model="orderFilter" class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:border-emerald-500 cursor-pointer">
              <option value="">All Orders</option>
              <option value="pending">Pending</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-100">
            <table class="w-full text-left border-collapse min-w-[650px]">
              <thead>
                <tr class="bg-slate-50 text-slate-400 text-[11px] font-bold uppercase tracking-wider border-b border-slate-100">
                  <th class="p-3">Order ID</th>
                  <th class="p-3">Supplier</th>
                  <th class="p-3">Items</th>
                  <th class="p-3">Status</th>
                  <th class="p-3">Total</th>
                  <th class="p-3">Date</th>
                  <th class="p-3">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-600">
                <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-slate-50/50">
                  <td class="p-3 font-bold text-slate-900">#{{ order.id }}</td>
                  <td class="p-3">{{ order.supplier?.name || 'N/A' }}</td>
                  <td class="p-3">{{ order.items?.length || 0 }}</td>
                  <td class="p-3">
                    <span :class="['inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold capitalize', getStatusClass(order.status)]">
                      {{ order.status }}
                    </span>
                  </td>
                  <td class="p-3 font-bold text-slate-900">ETB {{ formatNumber(order.total_amount) }}</td>
                  <td class="p-3">{{ formatDate(order.created_at) }}</td>
                  <td class="p-3">
                    <button class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md font-bold transition-colors" @click="viewOrder(order)">
                      View
                    </button>
                  </td>
                </tr>
                <tr v-if="!filteredOrders.length">
                  <td colspan="7" class="text-center py-10 text-slate-400 font-medium">
                    No orders found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Deliveries Tab -->
        <div v-show="activeTab === 'deliveries'" class="p-6">
          <h2 class="text-base font-bold text-slate-900 mb-4">Active Deliveries</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <article
              v-for="delivery in dashboard?.active_deliveries || []"
              :key="delivery.id"
              class="bg-white border border-slate-200/80 rounded-xl p-4 hover:shadow-sm"
            >
              <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-slate-900 text-sm">Order #{{ delivery.order_id }}</h3>
                <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-600 rounded-full text-[11px] font-bold capitalize">{{ delivery.status }}</span>
              </div>
              <div class="space-y-1.5 text-xs text-slate-600 mb-4">
                <p><strong class="text-slate-800">From:</strong> {{ delivery.from_location }}</p>
                <p><strong class="text-slate-800">To:</strong> {{ delivery.to_location }}</p>
                <p><strong class="text-slate-800">Driver:</strong> {{ delivery.driver_name }}</p>
              </div>
              <button class="w-full py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-lg transition-colors" @click="trackDelivery(delivery)">Track Order</button>
            </article>

            <div v-if="!(dashboard?.active_deliveries || []).length" class="col-span-full text-center py-10 text-slate-400 font-medium">
              No active deliveries at the moment
            </div>
          </div>
        </div>

        <!-- Cart, Reviews, Wishlist, Analytics... -->
      </section>

      <!-- Bottom Recent Cards Grid -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs">
          <h2 class="font-bold text-slate-900 mb-4 text-sm">Recent Transactions</h2>
          <div class="flex flex-col gap-2">
            <div
              v-for="order in (dashboard?.recent_orders || []).slice(0, 5)"
              :key="order.id"
              class="flex items-center justify-between p-3 bg-slate-50/60 rounded-lg text-xs"
            >
              <span class="font-bold text-slate-900">Order #{{ order.id }}</span>
              <span class="font-bold text-slate-900">ETB {{ formatNumber(order.total_amount) }}</span>
              <span :class="['px-2.5 py-0.5 rounded-full text-[11px] font-bold capitalize', getStatusClass(order.status)]">
                {{ order.status }}
              </span>
            </div>
            <div v-if="!(dashboard?.recent_orders || []).length" class="text-center py-6 text-slate-400 text-xs">
              No recent transactions
            </div>
          </div>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs">
          <h2 class="font-bold text-slate-900 mb-4 text-sm">Payment Methods</h2>
          <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between p-3 bg-slate-50/60 rounded-lg text-xs font-semibold">
              <span class="text-slate-800 flex items-center gap-2"><i class="fas fa-mobile-alt text-emerald-600"></i> Telebirr</span>
              <span class="text-[11px] font-bold bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-md">Primary</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50/60 rounded-lg text-xs font-semibold">
              <span class="text-slate-800 flex items-center gap-2"><i class="fas fa-university text-emerald-600"></i> CBE Birr / Bank Transfer</span>
              <span class="text-[11px] font-bold bg-slate-200 text-slate-600 px-2 py-0.5 rounded-md">Active</span>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

const auth = useAuthStore()
const router = useRouter()

const activeTab = ref('orders')
const tabs = ['orders', 'deliveries', 'cart', 'reviews', 'wishlist', 'analytics']

const dashboard = ref(null)
const orderFilter = ref('')
const loading = ref(false)
const error = ref('')

/* -------- Computed -------- */

const filteredOrders = computed(() => {
  const orders = dashboard.value?.recent_orders || []
  if (!orderFilter.value) return orders
  return orders.filter(o => o.status === orderFilter.value)
})

const summaryCards = computed(() => {
  const d = dashboard.value
  return [
    {
      key: 'total',
      label: 'Total Orders',
      value: d?.order_statistics?.total || 0,
      sub: 'All purchases',
      icon: 'fas fa-home'
    },
    {
      key: 'pending',
      label: 'Pending Orders',
      value: d?.order_statistics?.pending || 0,
      sub: 'Awaiting shipment',
      icon: 'fas fa-clock'
    },
    {
      key: 'completed',
      label: 'Completed Orders',
      value: d?.order_statistics?.completed || 0,
      sub: 'Delivered',
      icon: 'fas fa-check-circle'
    },
    {
      key: 'spent',
      label: 'Total Spent',
      value: `ETB ${formatNumber(d?.total_spent)}`,
      sub: `Avg: ETB ${formatNumber(d?.average_order_value)}`,
      icon: 'fas fa-wallet'
    },
    {
      key: 'cart',
      label: 'Cart Items',
      value: d?.cart_items_count || 0,
      sub: 'Ready to checkout',
      icon: 'fas fa-shopping-bag'
    },
    {
      key: 'wishlist',
      label: 'Wishlist Items',
      value: d?.wishlist_items_count || 0,
      sub: 'Saved items',
      icon: 'fas fa-heart'
    }
  ]
})

const getStatusClass = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-50 text-emerald-700'
    case 'pending': return 'bg-amber-50 text-amber-700'
    case 'cancelled': return 'bg-red-50 text-red-700'
    default: return 'bg-slate-100 text-slate-700'
  }
}

onMounted(fetchDashboardData)

async function fetchDashboardData() {
  loading.value = true
  error.value = ''
  try {
    const res = await fetch('/api/buyer/dashboard', {
      headers: { Authorization: `Bearer ${auth.token}` }
    })
    if (!res.ok) throw new Error(`Request failed (${res.status})`)
    const data = await res.json()
    dashboard.value = data.data || data
  } catch (err) {
    console.error('Error fetching dashboard:', err)
    error.value = 'Unable to load dashboard data. Please try again.'
  } finally {
    loading.value = false
  }
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num || 0)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatTabName = (tab) => {
  const names = {
    orders: 'My Orders',
    deliveries: 'Deliveries',
    cart: 'Shopping Cart',
    reviews: 'Reviews',
    wishlist: 'Wishlist',
    analytics: 'Analytics'
  }
  return names[tab] || tab
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

const viewOrder = (order) => {
  router.push(`/buyer/orders/${order.id}`)
}

const trackDelivery = (delivery) => {
  router.push(`/buyer/deliveries/${delivery.id}/track`)
}
</script>