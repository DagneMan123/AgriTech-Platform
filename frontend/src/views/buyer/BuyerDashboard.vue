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
      
      <!-- Header Section with Emoji Theme Toggle -->
      <div :class="['border-b border-t-0 border-x-0 transition-all -mx-4 lg:-mx-8 -mt-4 lg:-mt-8 mb-6 px-6 lg:px-8 py-[24px]', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <!-- Mobile Menu Toggle Button -->
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="lg:hidden p-2 rounded-xl border transition-all text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
              aria-label="Toggle menu"
            >
              <i class="fas fa-bars text-lg"></i>
            </button>
            <div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 mb-1 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">Buyer Dashboard</h1>
              <p :class="['text-xs lg:text-sm m-0', darkMode ? 'text-slate-400' : 'text-slate-600']">Manage your orders, deliveries, and purchases</p>
            </div>
          </div>

          <!-- Header Right Actions (Notification Bell & Profile Avatar) -->
          <div class="flex items-center gap-3">
            <BuyerHeaderNav />
          </div>
        </div>
      </div>

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
              <option value="refunded">Refunded</option>
            </select>
          </div>

          <div :class="['overflow-x-auto rounded-lg border', darkMode ? 'border-slate-800' : 'border-slate-100']">
            <table class="w-full text-left border-collapse min-w-[650px]" v-if="filteredOrders.length">
              <thead>
                <tr :class="['text-[11px] font-bold uppercase tracking-wider border-b', darkMode ? 'bg-[#0b0f19]/80 text-slate-400 border-slate-800' : 'bg-slate-50 text-slate-400 border-slate-100']">
                  <th class="p-3">Order ID</th>
                  <th class="p-3">Farmers</th>
                  <th class="p-3">Items</th>
                  <th class="p-3">Status</th>
                  <th class="p-3">Payment</th>
                  <th class="p-3">Total</th>
                  <th class="p-3">Date</th>
                  <th class="p-3">Actions</th>
                </tr>
              </thead>
              <tbody :class="['divide-y text-xs font-semibold', darkMode ? 'divide-slate-800/80 text-slate-300' : 'divide-slate-100 text-slate-600']">
                <tr v-for="order in filteredOrders" :key="order.id" :class="[darkMode ? 'hover:bg-[#0b0f19]/50' : 'hover:bg-slate-50/50']">
                  <td :class="['p-3 font-bold font-mono text-[11px] whitespace-nowrap', darkMode ? 'text-slate-100' : 'text-slate-900']">{{ getOrderNumber(order) }}</td>
                  <td class="p-3">{{ getOrderFarmer(order) }}</td>
                  <td class="p-3">{{ order.items?.length || 0 }}</td>
                  <td class="p-3">
                    <span :class="['inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold capitalize', getStatusClass(order.status, order)]">
                      {{ getStatusDisplay(order) }}
                    </span>
                  </td>
                  <td class="p-3">
                    <span
                      class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                      :class="getOrderPaymentBadge(order).class"
                    >
                      {{ getOrderPaymentBadge(order).label }}
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

            <!-- Empty State -->
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
              <router-link to="/buyer/marketplace" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all shadow-sm shadow-blue-600/30">
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
          <div :class="['border rounded-2xl p-5 shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800/80' : 'bg-white border-slate-200/80']">
            <h3 :class="['font-bold text-sm mb-4', darkMode ? 'text-slate-100' : 'text-slate-900']">📈 Monthly Spending Trend</h3>
            <div class="relative h-[280px] w-full">
              <canvas ref="spendingTrendChart"></canvas>
            </div>
          </div>

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

      <!-- ORDER DETAILS MODAL -->
      <div
        v-if="showOrderModal && selectedOrder"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
        @click.self="closeOrderModal"
      >
        <div
          :class="[
            'w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border shadow-2xl flex flex-col',
            darkMode ? 'bg-[#0f172a] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-800'
          ]"
        >
          <!-- Modal Header -->
          <div :class="['px-6 py-4 border-b flex items-center justify-between sticky top-0 z-10 backdrop-blur-md', darkMode ? 'bg-[#0f172a]/90 border-slate-800' : 'bg-white/90 border-slate-100']">
            <div class="flex items-center gap-3">
              <div :class="['w-10 h-10 rounded-xl flex items-center justify-center text-lg', selectedOrder.status === 'delivered' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-blue-500/10 text-blue-500']">
                <i :class="selectedOrder.status === 'delivered' ? 'fas fa-check-circle' : 'fas fa-receipt'"></i>
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="text-base font-bold m-0 font-mono">{{ getOrderNumber(selectedOrder) }}</h3>
                  <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider', getStatusClass(selectedOrder.status, selectedOrder)]">
                    {{ getStatusDisplay(selectedOrder) }}
                  </span>
                  <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider', getOrderPaymentBadge(selectedOrder).class]">
                    {{ getOrderPaymentBadge(selectedOrder).label }}
                  </span>
                </div>
                <p :class="['text-xs m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Placed on {{ formatDate(selectedOrder.created_at) }}
                  <span v-if="selectedOrder.order_number" class="ml-2 font-mono text-[11px] opacity-75">• {{ selectedOrder.order_number }}</span>
                </p>
              </div>
            </div>
            <button
              class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors cursor-pointer"
              @click="closeOrderModal"
              title="Close"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 space-y-6">
            <!-- Active Refund Claim Banner -->
            <div
              v-if="hasActiveRefundClaim(selectedOrder)"
              :class="['p-3.5 rounded-xl border flex items-center justify-between gap-3', darkMode ? 'bg-amber-950/25 border-amber-900/50 text-amber-300' : 'bg-amber-50 border-amber-200 text-amber-800']"
            >
              <div class="flex items-center gap-2.5 text-xs font-semibold">
                <i class="fas fa-history text-amber-500 text-sm"></i>
                <span><strong>Refund Under Review:</strong> A refund request ({{ selectedOrder.refund?.refund_number || selectedOrder.refund?.id || 'Active Claim' }}) is currently being reviewed.</span>
              </div>
              <router-link
                :to="`/buyer/returns?claim_id=${selectedOrder.refund?.id || ''}&order_id=${selectedOrder.id}`"
                class="text-xs font-bold underline shrink-0 hover:opacity-80"
                @click="closeOrderModal"
              >
                Track Claim Status
              </router-link>
            </div>

            <!-- Refund Notice Banner (if settled) -->
            <div
              v-else-if="isOrderRefundSettled(selectedOrder)"
              :class="['p-3.5 rounded-xl border flex items-center justify-between gap-3', darkMode ? 'bg-rose-950/25 border-rose-900/50 text-rose-300' : 'bg-rose-50 border-rose-200 text-rose-800']"
            >
              <div class="flex items-center gap-2.5 text-xs font-semibold">
                <i class="fas fa-undo-alt text-rose-500 text-sm"></i>
                <span><strong>Escrow Refund Settled:</strong> Funds have been refunded to the buyer's account.</span>
              </div>
              <router-link
                to="/buyer/returns"
                class="text-xs font-bold underline shrink-0 hover:opacity-80"
                @click="closeOrderModal"
              >
                Returns &amp; Refunds
              </router-link>
            </div>

            <!-- Logistics & Farmer Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Farmer / Supplier Info -->
              <div :class="['p-4 rounded-xl border', darkMode ? 'bg-slate-900/50 border-slate-800' : 'bg-slate-50 border-slate-200/80']">
                <div class="flex items-center gap-2 mb-2">
                  <i class="fas fa-user-tie text-emerald-500 text-xs"></i>
                  <span :class="['text-[11px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Farmers / Producers</span>
                </div>
                <p class="font-bold text-sm m-0 text-emerald-500">{{ getOrderFarmer(selectedOrder) }}</p>
                <p v-if="selectedOrder.farmer?.farm_name && selectedOrder.farmer?.farm_name !== getOrderFarmer(selectedOrder)" :class="['text-xs mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                  {{ selectedOrder.farmer.farm_name }}
                </p>
                <div v-if="selectedOrder.farmer?.phone_number || selectedOrder.farmer?.user?.phone" class="flex items-center gap-1.5 text-xs text-slate-400 mt-2">
                  <i class="fas fa-phone text-[10px]"></i>
                  <span>{{ selectedOrder.farmer?.phone_number || selectedOrder.farmer?.user?.phone }}</span>
                </div>
              </div>

              <!-- Delivery Destination -->
              <div :class="['p-4 rounded-xl border', darkMode ? 'bg-slate-900/50 border-slate-800' : 'bg-slate-50 border-slate-200/80']">
                <div class="flex items-center gap-2 mb-2">
                  <i class="fas fa-map-marker-alt text-blue-500 text-xs"></i>
                  <span :class="['text-[11px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Delivery Destination</span>
                </div>
                <p class="font-semibold text-xs leading-relaxed m-0 break-words whitespace-normal">{{ getOrderDeliveryAddress(selectedOrder) }}</p>
                <div v-if="selectedOrder.delivery_phone || selectedOrder.phone" class="flex items-center gap-1.5 text-xs text-slate-400 mt-2">
                  <i class="fas fa-phone text-[10px]"></i>
                  <span>Recipient: {{ selectedOrder.delivery_phone || selectedOrder.phone }}</span>
                </div>
              </div>
            </div>

            <!-- Items Ordered List -->
            <div>
              <h4 :class="['text-xs font-bold uppercase tracking-wider mb-3', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Items In This Order ({{ getOrderItems(selectedOrder).length }})
              </h4>
              <div :class="['divide-y rounded-xl border overflow-hidden', darkMode ? 'divide-slate-800 border-slate-800 bg-slate-900/30' : 'divide-slate-100 border-slate-200 bg-white']">
                <div
                  v-for="(item, idx) in getOrderItems(selectedOrder)"
                  :key="item.id || idx"
                  class="p-3.5 flex items-center justify-between gap-4"
                >
                  <div class="flex items-center gap-3 min-w-0">
                    <div :class="['w-10 h-10 rounded-lg flex items-center justify-center font-bold text-xs shrink-0', darkMode ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-700']">
                      {{ idx + 1 }}
                    </div>
                    <div class="min-w-0">
                      <p class="font-bold text-xs m-0 capitalize truncate">{{ item.product?.name || item.name || 'Agricultural Item' }}</p>
                      <p :class="['text-[11px] m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                        {{ item.quantity || 1 }} {{ item.product?.quantity_unit || item.product?.unit || 'kg' }} @ ETB {{ formatNumber(item.price || item.unit_price) }}
                      </p>
                    </div>
                  </div>
                  <div class="text-right shrink-0">
                    <span class="font-bold text-xs">ETB {{ formatNumber(item.subtotal || item.total_price || ((item.quantity || 1) * (item.price || 0))) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Price & Settlement Summary -->
            <div :class="['p-4 rounded-xl border space-y-2', darkMode ? 'bg-slate-900/50 border-slate-800' : 'bg-slate-50 border-slate-200/80']">
              <div class="flex justify-between items-center text-xs">
                <span :class="darkMode ? 'text-slate-400' : 'text-slate-600'">Items Subtotal</span>
                <span class="font-semibold">ETB {{ formatNumber(selectedOrder.subtotal || (selectedOrder.total_amount || selectedOrder.grand_total) - (selectedOrder.delivery_fee || 0)) }}</span>
              </div>
              <div class="flex justify-between items-center text-xs">
                <span :class="darkMode ? 'text-slate-400' : 'text-slate-600'">Delivery & Transport Fee</span>
                <span class="font-semibold">ETB {{ formatNumber(selectedOrder.delivery_fee || 0) }}</span>
              </div>
              <div v-if="selectedOrder.discount && Number(selectedOrder.discount) > 0" class="flex justify-between items-center text-xs text-emerald-500">
                <span>Promotional Discount</span>
                <span class="font-semibold">- ETB {{ formatNumber(selectedOrder.discount) }}</span>
              </div>
              <div :class="['pt-2 border-t flex justify-between items-center text-sm font-bold', darkMode ? 'border-slate-800 text-slate-100' : 'border-slate-200 text-slate-900']">
                <span>Total Paid / Payable</span>
                <span class="text-blue-500">ETB {{ formatNumber(selectedOrder.grand_total || selectedOrder.total_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div :class="['px-6 py-4 border-t flex items-center justify-between gap-3', darkMode ? 'bg-slate-900/60 border-slate-800' : 'bg-slate-50 border-slate-100']">
            <button
              class="px-4 py-2 text-xs font-bold rounded-xl border transition-colors cursor-pointer flex items-center gap-2"
              :class="darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700'"
              @click="printOrderReceipt"
            >
              <i class="fas fa-print"></i> Print Receipt
            </button>
            <div class="flex items-center gap-2">
              <button
                class="px-4 py-2 text-xs font-bold rounded-xl bg-blue-600 hover:bg-blue-500 text-white transition-all shadow-sm shadow-blue-600/30 cursor-pointer flex items-center gap-2"
                @click="goToFullOrders(selectedOrder)"
              >
                <span>Full Orders Page</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
              </button>
              <button
                class="px-4 py-2 text-xs font-bold rounded-xl border transition-colors cursor-pointer"
                :class="darkMode ? 'border-slate-800 hover:bg-slate-800 text-slate-400' : 'border-slate-200 hover:bg-slate-100 text-slate-600'"
                @click="closeOrderModal"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { useRouter } from 'vue-router'
import { buyerAPI } from '@/api/buyer'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import BuyerHeaderNav from '@/components/layout/BuyerHeaderNav.vue'

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

const spendingTrendChart = ref(null)
const statusChart = ref(null)
const categoryChart = ref(null)

let spendingChart = null
let orderStatusChart = null
let purchaseCategoryChart = null

const darkMode = computed(() => themeStore.isDark)

const toggleDarkMode = () => {
  themeStore.toggleTheme()
}

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

const initializeCharts = async () => {
  await loadChartJS()
  await nextTick()
  
  createSpendingTrendChart()
  createOrderStatusChart()
  createCategoryChart()
}

const createSpendingTrendChart = () => {
  if (!spendingTrendChart.value) return
  try {
    if (spendingChart) spendingChart.destroy()
    const ctx = spendingTrendChart.value.getContext('2d')
    
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
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
            beginAtZero: true,
            grid: { color: darkMode.value ? '#1e293b' : '#f1f5f9' },
            ticks: {
              color: darkMode.value ? '#94a3b8' : '#64748b',
              callback: function(value) {
                if (value >= 1000) {
                  return (value / 1000) + 'k'
                }
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

const hasActiveRefundClaim = (order) => {
  if (!order) return false
  const refundStatus = String(order.refund?.status || '').toLowerCase()
  return ['requested', 'pending', 'under_review', 'in_review', 'dispute', 'disputed'].includes(refundStatus)
}

const isOrderRefundSettled = (order) => {
  if (!order) return false
  const status = String(order.status || '').toLowerCase()
  if (status === 'refunded') return true
  const payStatus = String(order.payment_status || order.payment?.status || '').toLowerCase()
  if (payStatus === 'refunded') return true
  const escrowStatus = String(order.escrow_status || order.payment?.escrow_status || '').toLowerCase()
  if (escrowStatus === 'refunded') return true
  const refundStatus = String(order.refund?.status || '').toLowerCase()
  if (['approved', 'completed', 'refunded'].includes(refundStatus)) return true
  return false
}

const isOrderRefunded = (order) => {
  return isOrderRefundSettled(order) || hasActiveRefundClaim(order)
}

const filteredOrders = computed(() => {
  const orders = dashboard.value?.recent_orders || []
  if (!orderFilter.value) return orders
  if (orderFilter.value === 'refunded') {
    return orders.filter(o => isOrderRefunded(o))
  }
  if (orderFilter.value === 'pending') {
    return orders.filter(o => (o.status === 'pending' || o.status === 'placed') && !isOrderRefunded(o))
  }
  return orders.filter(o => o.status === orderFilter.value && !isOrderRefunded(o))
})

const summaryCards = computed(() => {
  const s = dashboard.value?.summary
  const refAmt = Number(s?.total_refunded_amount || 0)
  return [
    { key: 'total', label: 'Total Orders', value: s?.total_orders || 0, sub: 'All purchases', icon: 'fas fa-shopping-cart' },
    { key: 'pending', label: 'Pending Orders', value: s?.pending_orders || 0, sub: 'Awaiting shipment', icon: 'fas fa-clock' },
    { key: 'completed', label: 'Completed Orders', value: s?.completed_orders || 0, sub: 'Delivered', icon: 'fas fa-check-circle' },
    { key: 'spent', label: 'Total Spent', value: `ETB ${formatNumber(s?.total_spent)}`, sub: refAmt > 0 ? `Refunds: -ETB ${formatNumber(refAmt)}` : `Avg: ETB ${formatNumber(s?.average_order_value)}`, icon: 'fas fa-wallet' },
    { key: 'cart', label: 'Cart Items', value: s?.cart_items_count || 0, sub: 'Ready to checkout', icon: 'fas fa-shopping-bag' },
    { key: 'wishlist', label: 'Wishlist Items', value: s?.wishlist_items_count || 0, sub: 'Saved items', icon: 'fas fa-heart' }
  ]
})

const getStatusClass = (status, order) => {
  if (order && hasActiveRefundClaim(order)) {
    return 'bg-amber-500/15 text-amber-500 dark:text-amber-400 border border-amber-500/30'
  }
  if (order && isOrderRefundSettled(order)) {
    return 'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400'
  }
  switch (status) {
    case 'completed':
    case 'delivered': return 'bg-emerald-50 text-emerald-600'
    case 'pending': return 'bg-amber-50 text-amber-600'
    case 'cancelled': return 'bg-red-50 text-red-600'
    case 'refunded': return 'bg-rose-50 text-rose-600'
    default: return 'bg-slate-100 text-slate-600'
  }
}

const getStatusDisplay = (order) => {
  if (!order) return ''
  if (hasActiveRefundClaim(order)) return 'Under Review'
  if (isOrderRefundSettled(order)) return 'Refunded'
  return order.status || ''
}

async function fetchDashboardData() {
  loading.value = true
  error.value = ''
  try {
    const res = await buyerAPI.getDashboard()
    const raw = res.data
    dashboard.value = (raw && raw.summary) ? raw : (raw?.data || raw || {})
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

const getOrderNumber = (order) => {
  if (!order) return ''
  return order.order_number || order.tracking_id || order.tracking_number || (order.id ? `ORD-${order.id}` : '')
}

const getOrderFarmer = (order) => {
  if (!order) return 'N/A'
  if (order.farmer?.user?.name) return order.farmer.user.name
  if (order.farmer?.name) return order.farmer.name
  if (order.farmer?.farm_name) return order.farmer.farm_name
  if (order.supplier?.user?.name) return order.supplier.user.name
  if (order.supplier?.name) return order.supplier.name
  if (order.items && order.items.length > 0) {
    const names = order.items
      .map((it) => it.product?.farmer?.user?.name || it.product?.farmer?.name || it.product?.farmer_name)
      .filter(Boolean)
    if (names.length > 0) {
      return Array.from(new Set(names)).join(', ')
    }
  }
  return 'N/A'
}

const selectedOrder = ref(null)
const showOrderModal = ref(false)

const viewOrder = (order) => {
  if (!order) return
  selectedOrder.value = order
  showOrderModal.value = true
}

const closeOrderModal = () => {
  showOrderModal.value = false
  selectedOrder.value = null
}

const goToFullOrders = (order) => {
  const orderId = order?.id
  closeOrderModal()
  if (orderId) {
    router.push(`/buyer/orders/${orderId}`)
  } else {
    router.push('/buyer/orders')
  }
}

const printOrderReceipt = () => {
  window.print()
}

const getOrderItems = (order) => {
  if (!order) return []
  return order.items || order.order_items || []
}

const getOrderDeliveryAddress = (order) => {
  if (!order) return 'N/A'
  return order.delivery_address || order.destination || order.delivery?.to_location || 'N/A'
}

const getOrderPaymentBadge = (order) => {
  if (!order) return { label: 'PENDING', class: 'bg-amber-500/10 text-amber-500 border border-amber-500/20' }
  if (hasActiveRefundClaim(order)) {
    return { label: 'REFUND REQUESTED', class: 'bg-amber-500/15 text-amber-500 dark:text-amber-400 border border-amber-500/30 font-bold' }
  }
  if (isOrderRefundSettled(order)) {
    return { label: 'REFUNDED', class: 'bg-rose-500/15 text-rose-500 dark:text-rose-400 border border-rose-500/30 font-bold' }
  }
  const isPaid = order.payment_status === 'paid' || order.payment?.status === 'completed' || order.payment?.status === 'paid'
  if (isPaid) {
    const escrow = String(order.escrow_status || order.payment?.escrow_status || '').toLowerCase()
    if (escrow === 'disbursed') {
      return { label: 'PAID (SETTLED)', class: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }
    }
    return { label: 'PAID (ESCROW)', class: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }
  }
  return { label: 'PENDING', class: 'bg-amber-500/10 text-amber-500 border border-amber-500/20' }
}

const handleLogout = async () => { await auth.logout(); router.push('/login'); }
const trackDelivery = (delivery) => router.push(`/buyer/deliveries/${delivery.id}/track`)
</script>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>