<template>
  <!-- ዋናው ገጽ Background (በምስሉ መሰረት Soft Light Gray) -->
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
            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs lg:text-sm font-semibold text-slate-700 hover:text-blue-600 hover:border-blue-500 transition-all shadow-2xs cursor-pointer"
            :disabled="loading" 
            @click="fetchDashboardData"
          >
            <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-rotate-right'"></i>
            <span>Refresh</span>
          </button>
        </div>
      </header>

      <!-- Error State -->
      <div v-if="error" class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl mb-6 text-sm font-medium" role="alert">
        <i class="fas fa-exclamation-circle text-lg shrink-0"></i>
        <span>{{ error }}</span>
      </div>

      <!-- Summary Stats Cards (በግራ በኩል ሰማያዊ መስመር ያላቸው - BLUE BORDER) -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6" aria-label="Summary statistics">
        <article
          v-for="card in summaryCards"
          :key="card.key"
          class="bg-white rounded-xl p-4 border-l-4 border-l-blue-600 border-y border-r border-slate-200/60 shadow-2xs hover:shadow-md transition-all flex justify-between items-start"
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
        <!-- Tabs Bar (Active Tab ሰማያዊ) -->
        <nav class="flex border-b border-slate-200 overflow-x-auto px-4 gap-2 scrollbar-none" role="tablist">
          <button
            v-for="tab in tabs"
            :key="tab"
            :class="[
              'px-4 py-3.5 text-sm font-bold whitespace-nowrap border-b-2 transition-all cursor-pointer',
              activeTab === tab 
                ? 'border-blue-600 text-blue-600 bg-blue-50/50' 
                : 'border-transparent text-slate-500 hover:text-slate-800'
            ]"
            role="tab"
            :aria-selected="activeTab === tab"
            @click="activeTab = tab"
          >
            {{ formatTabName(tab) }}
          </button>
        </nav>

        <!-- Orders Content -->
        <div v-show="activeTab === 'orders'" class="p-6" role="tabpanel">
          <div class="flex justify-between items-center gap-4 mb-5 flex-wrap">
            <h2 class="text-base font-bold text-slate-900">My Orders</h2>
            <select v-model="orderFilter" class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:border-blue-600 cursor-pointer">
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

        <!-- Deliveries Tab -->
        <div v-show="activeTab === 'deliveries'" class="p-6" role="tabpanel">
          <h2 class="text-base font-bold text-slate-900 mb-4">Active Deliveries</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <article
              v-for="delivery in dashboard?.active_deliveries || []"
              :key="delivery.id"
              class="bg-white border border-slate-200/80 rounded-xl p-4 hover:shadow-sm"
            >
              <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-slate-900 text-sm">Order #{{ delivery.order_id }}</h3>
                <span class="px-2.5 py-0.5 bg-blue-50 text-blue-600 rounded-full text-[11px] font-bold capitalize">{{ delivery.status }}</span>
              </div>
              <div class="space-y-1.5 text-xs text-slate-600 mb-4">
                <p><strong class="text-slate-800">From:</strong> {{ delivery.from_location }}</p>
                <p><strong class="text-slate-800">To:</strong> {{ delivery.to_location }}</p>
                <p><strong class="text-slate-800">Driver:</strong> {{ delivery.driver_name }}</p>
                <p><strong class="text-slate-800">Est. Date:</strong> {{ formatDate(delivery.estimated_delivery_date) }}</p>
              </div>
              <div class="flex gap-2">
                <button class="flex-1 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer" @click="trackDelivery(delivery)">Track Order</button>
                <button class="flex-1 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-xs font-bold rounded-lg transition-colors cursor-pointer" @click="contactDriver(delivery)">Contact</button>
              </div>
            </article>

            <div v-if="!(dashboard?.active_deliveries || []).length" class="col-span-full text-center py-10 text-slate-400 font-medium">
              No active deliveries at the moment
            </div>
          </div>
        </div>

        <!-- Cart Tab -->
        <div v-show="activeTab === 'cart'" class="p-6" role="tabpanel">
          <h2 class="text-base font-bold text-slate-900 mb-4">Shopping Cart</h2>
          <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start">
            <div class="flex flex-col gap-3">
              <div v-if="!dashboard?.cart_items_count" class="text-center py-10 text-slate-400 font-medium">
                Your cart is empty
              </div>

              <div
                v-for="i in (dashboard?.cart_items_count || 0)"
                :key="i"
                class="bg-white border border-slate-200/80 rounded-xl p-4 flex justify-between items-center gap-4"
              >
                <div>
                  <h4 class="font-bold text-slate-900 text-sm mb-0.5">Product {{ i }}</h4>
                  <p class="text-xs text-slate-500">Quantity: 5 units</p>
                </div>
                <div class="font-bold text-blue-600 text-sm">ETB 150.00</div>
                <button class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold rounded-lg transition-colors cursor-pointer" @click="removeFromCart(i)">Remove</button>
              </div>
            </div>

            <aside class="bg-white border border-slate-200/80 rounded-xl p-5 sticky top-5 shadow-2xs">
              <div class="flex justify-between py-2 border-b border-slate-100 text-xs text-slate-600 font-medium">
                <span>Subtotal</span>
                <span>ETB {{ formatNumber(cartSubtotal) }}</span>
              </div>
              <div class="flex justify-between py-2 border-b border-slate-100 text-xs text-slate-600 font-medium">
                <span>Tax (15%)</span>
                <span>ETB {{ formatNumber(cartTax) }}</span>
              </div>
              <div class="flex justify-between py-3 text-sm font-bold text-slate-900 mb-4 border-t border-slate-200 mt-2">
                <span>Total</span>
                <span>ETB {{ formatNumber(cartTotal) }}</span>
              </div>
              <button class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs transition-colors shadow-2xs cursor-pointer" @click="proceedToCheckout">
                Proceed to Checkout
              </button>
            </aside>
          </div>
        </div>

        <!-- Reviews Tab -->
        <div v-show="activeTab === 'reviews'" class="p-6" role="tabpanel">
          <h2 class="text-base font-bold text-slate-900 mb-4">My Reviews</h2>
          <div class="flex flex-col gap-3">
            <article v-for="i in 3" :key="i" class="bg-white border border-slate-200/80 rounded-xl p-4">
              <div class="flex justify-between items-center mb-1">
                <h4 class="font-bold text-slate-900 text-sm">Product Review</h4>
                <div class="flex gap-1 text-amber-400 text-xs">
                  <i v-for="j in 5" :key="j" class="fas fa-star"></i>
                </div>
              </div>
              <p class="text-xs text-slate-600 leading-relaxed my-1.5">
                Great quality products! Fast delivery and excellent customer service.
              </p>
              <p class="text-[11px] text-slate-400">{{ formatDate(new Date()) }}</p>
            </article>
          </div>
        </div>

        <!-- Wishlist Tab -->
        <div v-show="activeTab === 'wishlist'" class="p-6" role="tabpanel">
          <h2 class="text-base font-bold text-slate-900 mb-4">My Wishlist</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <article
              v-for="i in (dashboard?.wishlist_items_count || 0)"
              :key="i"
              class="bg-white border border-slate-200/80 rounded-xl p-4 hover:shadow-sm"
            >
              <div class="w-full h-32 bg-slate-50 rounded-lg flex items-center justify-center text-slate-300 text-xl mb-3">
                <i class="fas fa-image"></i>
              </div>
              <div>
                <h4 class="font-bold text-slate-900 text-sm mb-0.5">Product {{ i }}</h4>
                <p class="text-red-500 font-bold text-sm my-1">ETB {{ (100 + i * 50).toFixed(2) }}</p>
                <p class="text-[11px] text-slate-400">Supplier {{ i }}</p>
              </div>
              <div class="flex gap-2 mt-3">
                <button class="flex-1 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer" @click="addToCart(i)">Add to Cart</button>
                <button class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-bold rounded-lg transition-colors cursor-pointer" @click="removeFromWishlist(i)">Remove</button>
              </div>
            </article>

            <div v-if="!dashboard?.wishlist_items_count" class="col-span-full text-center py-10 text-slate-400 font-medium">
              Your wishlist is empty
            </div>
          </div>
        </div>

        <!-- Analytics Tab -->
        <div v-show="activeTab === 'analytics'" class="p-6" role="tabpanel">
          <h2 class="text-base font-bold text-slate-900 mb-4">Purchase Analytics</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white border border-slate-200/80 rounded-xl p-4">
              <h3 class="font-bold text-slate-900 text-sm mb-3">Spending Overview</h3>
              <div class="divide-y divide-slate-100 text-xs text-slate-600">
                <p class="flex justify-between py-2.5">
                  <span>Total Spent</span>
                  <strong class="text-slate-900">ETB {{ formatNumber(dashboard?.total_spent) }}</strong>
                </p>
                <p class="flex justify-between py-2.5">
                  <span>Avg Order Value</span>
                  <strong class="text-slate-900">ETB {{ formatNumber(dashboard?.average_order_value) }}</strong>
                </p>
                <p class="flex justify-between py-2.5">
                  <span>Total Orders</span>
                  <strong class="text-slate-900">{{ dashboard?.order_statistics?.total || 0 }}</strong>
                </p>
              </div>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-xl p-4">
              <h3 class="font-bold text-slate-900 text-sm mb-3">Top Purchases</h3>
              <div class="divide-y divide-slate-100 text-xs text-slate-600">
                <p v-for="i in 3" :key="i" class="flex justify-between py-2.5">
                  <span>{{ i }}. Product {{ i }}</span>
                  <strong class="text-slate-900">3 purchases</strong>
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Bottom Recent Section -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transactions -->
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

        <!-- Local Payment Methods -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs">
          <h2 class="font-bold text-slate-900 mb-4 text-sm">Payment Methods</h2>
          <div class="flex flex-col gap-2">
            <div class="flex items-center justify-between p-3 bg-slate-50/60 rounded-lg text-xs font-semibold">
              <span class="text-slate-800 flex items-center gap-2"><i class="fas fa-mobile-alt text-blue-600"></i> Telebirr</span>
              <span class="text-[11px] font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded-md">Primary</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-50/60 rounded-lg text-xs font-semibold">
              <span class="text-slate-800 flex items-center gap-2"><i class="fas fa-university text-blue-600"></i> CBE Birr / Bank Transfer</span>
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

const cartSubtotal = computed(() => dashboard.value?.total_spent || 0)
const cartTax = computed(() => cartSubtotal.value * 0.15)
const cartTotal = computed(() => cartSubtotal.value + cartTax.value)

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

/* -------- Helpers -------- */

const getStatusClass = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-50 text-emerald-700'
    case 'pending': return 'bg-amber-50 text-amber-700'
    case 'cancelled': return 'bg-red-50 text-red-700'
    default: return 'bg-slate-100 text-slate-700'
  }
}

/* -------- Lifecycle -------- */

onMounted(fetchDashboardData)

/* -------- API Fetch -------- */

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

/* -------- Formatters -------- */

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

/* -------- Actions -------- */

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

const contactDriver = (delivery) => {
  console.log('Contact driver for delivery', delivery.id)
}

const removeFromCart = (id) => {
  console.log('Remove from cart', id)
}

const addToCart = (id) => {
  console.log('Add to cart', id)
}

const removeFromWishlist = (id) => {
  console.log('Remove from wishlist', id)
}

const proceedToCheckout = () => {
  router.push('/buyer/checkout')
}
</script>