<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">

    <!-- Sidebar Container -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar @logout="handleLogout" />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen lg:ml-64 transition-all" role="main">

      <!-- ── Header ── -->
      <div :class="['flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 px-6 py-[24px] mb-6 border-b transition-all -mx-4 lg:-mx-8 -mt-4 lg:-mt-8', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex items-center gap-3">
          <!-- Mobile Menu Toggle Button -->
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden p-2 rounded-xl border transition-all text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
            aria-label="Toggle menu"
          >
            <Menu class="w-5 h-5" />
          </button>
          <div>
            <!-- Breadcrumb Navigation -->
            <div class="flex items-center gap-2 mb-1.5 text-xs text-slate-500 dark:text-slate-400">
              <router-link to="/buyer/dashboard" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Buyer Portal</router-link>
              <span class="text-slate-400">&gt;</span>
              <span class="font-semibold text-slate-700 dark:text-slate-200">My Orders</span>
            </div>

            <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 mb-1 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">
              My Orders
            </h1>
            <p :class="['text-xs lg:text-sm m-0', darkMode ? 'text-slate-400' : 'text-slate-600']">
              Track, inspect, and manage your produce purchases and fulfillment in real-time
            </p>
          </div>
        </div>

        <!-- Header Actions: Refresh DB & Header Navigation -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
          <button
            @click="refreshOrders"
            :disabled="loading"
            :class="['px-3 py-2 rounded-xl text-xs font-semibold border flex items-center gap-1.5 transition-all cursor-pointer shadow-xs', darkMode ? 'bg-slate-800/80 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50']"
            title="Fetch latest orders from database"
          >
            <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin text-blue-500' : 'text-slate-400']" />
            <span class="hidden sm:inline">{{ loading ? 'Syncing...' : 'Sync DB' }}</span>
          </button>
          <BuyerHeaderNav />
        </div>
      </div>

      <!-- Toast Notification -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0"
      >
        <div
          v-if="toastMessage"
          class="mb-6 p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center justify-between shadow-sm"
        >
          <div class="flex items-center gap-2">
            <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
            <span>{{ toastMessage }}</span>
          </div>
          <button @click="toastMessage = ''" class="text-emerald-400 hover:text-emerald-300 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>
      </transition>

      <!-- Error State Banner -->
      <div
        v-if="errorMessage"
        class="mb-6 p-4 rounded-xl bg-red-500/15 border border-red-500/30 text-red-400 text-xs font-semibold flex items-center justify-between"
      >
        <div class="flex items-center gap-2.5">
          <AlertCircle class="w-4 h-4 text-red-400 shrink-0" />
          <span>{{ errorMessage }}</span>
        </div>
        <button
          @click="refreshOrders"
          class="px-3 py-1 bg-red-500/20 hover:bg-red-500/30 rounded-lg text-[11px] font-bold text-red-300 transition-colors cursor-pointer"
        >
          Try Again
        </button>
      </div>

      <!-- ── Database Stats Summary Cards ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Orders -->
        <article
          @click="selectedStatus = ''"
          :class="[
            'rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all cursor-pointer',
            darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-indigo-500 hover:bg-slate-800/50' : 'bg-white border-slate-200 text-slate-900 !border-l-indigo-600 hover:bg-slate-50',
            !selectedStatus ? 'ring-2 ring-indigo-500/60' : ''
          ]"
        >
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Orders</h3>
            <p class="text-2xl font-black">{{ totalOrders }}</p>
            <span class="text-[10px] text-indigo-500 font-medium">All database records</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-indigo-500/10 text-indigo-400' : 'bg-indigo-50 text-indigo-600']">
            <ShoppingBag class="w-6 h-6" />
          </div>
        </article>

        <!-- Pending / In Preparation -->
        <article
          @click="toggleStatusFilter('pending')"
          :class="[
            'rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all cursor-pointer',
            darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-amber-500 hover:bg-slate-800/50' : 'bg-white border-slate-200 text-slate-900 !border-l-amber-600 hover:bg-slate-50',
            selectedStatus === 'pending' ? 'ring-2 ring-amber-500/60' : ''
          ]"
        >
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Pending</h3>
            <p class="text-2xl font-black text-amber-500">{{ pendingOrders }}</p>
            <span class="text-[10px] text-amber-500 font-medium">Awaiting fulfillment</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <Clock class="w-6 h-6" />
          </div>
        </article>

        <!-- In Transit -->
        <article
          @click="toggleStatusFilter('shipped')"
          :class="[
            'rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all cursor-pointer',
            darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-purple-500 hover:bg-slate-800/50' : 'bg-white border-slate-200 text-slate-900 !border-l-purple-600 hover:bg-slate-50',
            selectedStatus === 'shipped' ? 'ring-2 ring-purple-500/60' : ''
          ]"
        >
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">In Transit</h3>
            <p class="text-2xl font-black text-purple-500">{{ shippedOrders }}</p>
            <span class="text-[10px] text-purple-500 font-medium">Dispatched freight</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-purple-500/10 text-purple-400' : 'bg-purple-50 text-purple-600']">
            <Truck class="w-6 h-6" />
          </div>
        </article>

        <!-- Delivered -->
        <article
          @click="toggleStatusFilter('delivered')"
          :class="[
            'rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all cursor-pointer',
            darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-emerald-500 hover:bg-slate-800/50' : 'bg-white border-slate-200 text-slate-900 !border-l-emerald-600 hover:bg-slate-50',
            selectedStatus === 'delivered' ? 'ring-2 ring-emerald-500/60' : ''
          ]"
        >
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Delivered</h3>
            <p class="text-2xl font-black text-emerald-500">{{ deliveredOrders }}</p>
            <span class="text-[10px] text-emerald-500 font-medium">Delivered &amp; completed</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <CheckCircle2 class="w-6 h-6" />
          </div>
        </article>
      </section>

      <!-- ── Search & Multi-Filters Toolbar ── -->
      <div :class="['rounded-2xl border p-4 mb-6 shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <!-- Text Search -->
          <div class="relative">
            <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by Order #, crop, or farmer..."
              :class="['w-full pl-9 pr-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-blue-500']"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <select
              v-model="selectedStatus"
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all cursor-pointer font-medium', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-blue-500']"
            >
              <option value="">All Statuses ({{ totalOrders }})</option>
              <option value="pending">Pending Confirmation ({{ pendingOrders }})</option>
              <option value="confirmed">Confirmed / Preparing</option>
              <option value="shipped">In Transit / Shipped ({{ shippedOrders }})</option>
              <option value="delivered">Delivered ({{ deliveredOrders }})</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Date Range Filter -->
          <div>
            <select
              v-model="selectedDateRange"
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all cursor-pointer font-medium', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-blue-500']"
            >
              <option value="">All Dates</option>
              <option value="today">Today</option>
              <option value="week">Last 7 Days</option>
              <option value="month">Last 30 Days</option>
              <option value="year">This Year</option>
            </select>
          </div>

          <!-- Sort Filter -->
          <div class="flex items-center gap-2">
            <select
              v-model="sortBy"
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all cursor-pointer font-medium', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-blue-500']"
            >
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="highest-amount">Highest Amount</option>
              <option value="lowest-amount">Lowest Amount</option>
            </select>

            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="px-2.5 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-slate-200 border border-slate-700 hover:bg-slate-800 shrink-0 cursor-pointer transition-all"
              title="Clear all filters"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- ── Loading Skeleton State ── -->
      <div v-if="loading && ordersArray.length === 0" class="flex flex-col gap-4">
        <div
          v-for="i in 3"
          :key="i"
          :class="['rounded-2xl border p-5 animate-pulse', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
        >
          <div class="flex justify-between items-center mb-4">
            <div class="h-4 w-32 bg-slate-700/50 rounded-md"></div>
            <div class="h-5 w-20 bg-slate-700/50 rounded-full"></div>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
            <div class="h-10 bg-slate-700/30 rounded-lg"></div>
            <div class="h-10 bg-slate-700/30 rounded-lg"></div>
            <div class="h-10 bg-slate-700/30 rounded-lg"></div>
            <div class="h-10 bg-slate-700/30 rounded-lg"></div>
          </div>
          <div class="h-8 bg-slate-700/20 rounded-lg"></div>
        </div>
      </div>

      <!-- ── Empty State ── -->
      <div
        v-else-if="filteredAndSortedOrders.length === 0"
        :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
      >
        <div :class="['h-16 w-16 rounded-2xl flex items-center justify-center text-2xl mb-4 border shadow-inner', darkMode ? 'bg-slate-800/50 border-slate-700 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-400']">
          <ShoppingBag class="w-8 h-8 text-slate-400" />
        </div>
        <h3 :class="['text-base font-bold mb-1', darkMode ? 'text-slate-200' : 'text-slate-800']">
          {{ totalOrders === 0 ? 'No orders recorded yet' : 'No matching orders found' }}
        </h3>
        <p :class="['text-xs max-w-sm mb-4', darkMode ? 'text-slate-400' : 'text-slate-500']">
          {{ totalOrders === 0 ? 'Start sourcing verified fresh produce directly from Ethiopian farmers and cooperatives.' : 'Try adjusting your search query, status filters, or date range.' }}
        </p>
        <div class="flex items-center gap-2 flex-wrap justify-center">
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Clear Filters
          </button>
          <router-link
            to="/buyer/marketplace"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all shadow-sm shadow-blue-600/30 cursor-pointer flex items-center gap-1.5"
          >
            <ShoppingBag class="w-3.5 h-3.5" />
            <span>Browse Produce Marketplace</span>
          </router-link>
        </div>
      </div>

      <!-- ── Orders List ── -->
      <div v-else class="flex flex-col gap-4">
        <div
          v-for="order in filteredAndSortedOrders"
          :key="order.id"
          :class="['rounded-2xl border overflow-hidden transition-all hover:shadow-lg', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
          :style="`border-left-width: 4px; border-left-color: ${getOrderBorderColor(order.status)};`"
        >
          <!-- Order Header -->
          <div :class="['flex justify-between items-center px-5 py-4 border-b', darkMode ? 'bg-[#0f1729] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div class="flex items-center gap-3 sm:gap-4 flex-wrap">
              <div class="flex items-center gap-2">
                <span class="text-xs font-black text-blue-500">Order</span>
                <h3 :class="['text-sm sm:text-base font-bold m-0 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">
                  {{ getOrderNumber(order) }}
                </h3>
              </div>
              <span class="text-slate-400 text-xs hidden sm:inline">•</span>
              <p :class="['text-xs m-0 flex items-center gap-1.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                <Clock class="w-3.5 h-3.5" />
                <span>{{ formatDate(order.created_at) }}</span>
              </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
              <!-- Payment Status Badge -->
              <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wide border transition-all', getPaymentBadgeClass(order)]">
                <component :is="getPaymentIcon(order)" class="w-3.5 h-3.5" />
                <span>{{ getPaymentStatusLabel(order) }}</span>
              </span>

              <!-- Fulfillment Status Badge -->
              <span :class="['inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wide', statusBadgeClass(order.status)]">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                <span>{{ formatStatus(order.status) }}</span>
              </span>
            </div>
          </div>

          <!-- Order Summary Row -->
          <div :class="['grid grid-cols-2 sm:grid-cols-4 gap-4 px-5 py-4 border-b', darkMode ? 'border-slate-800' : 'border-slate-100']">
            <!-- Produce Items -->
            <div class="flex flex-col gap-1">
              <span :class="['text-[10px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Items</span>
              <span :class="['text-sm font-semibold truncate', darkMode ? 'text-slate-200' : 'text-slate-800']">
                {{ getOrderItemsCount(order) }} item(s)
              </span>
            </div>

            <!-- Supplier / Farmer -->
            <div class="flex flex-col gap-1">
              <span :class="['text-[10px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Farmer / Co-op</span>
              <span :class="['text-sm font-semibold truncate', darkMode ? 'text-slate-200' : 'text-slate-800']">
                {{ getOrderSupplier(order) }}
              </span>
            </div>

            <!-- Destination Address -->
            <div class="flex flex-col gap-1">
              <span :class="['text-[10px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Destination</span>
              <span :class="['text-sm font-semibold truncate', darkMode ? 'text-slate-200' : 'text-slate-800']" :title="getOrderAddress(order)">
                {{ getOrderAddress(order) }}
              </span>
            </div>

            <!-- Amount in Ethiopian Birr -->
            <div class="flex flex-col gap-1">
              <span :class="['text-[10px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Grand Total</span>
              <span class="text-sm font-black text-emerald-500">
                ETB {{ formatNumber(getOrderAmount(order)) }}
              </span>
            </div>
          </div>

          <!-- Items Preview Chips -->
          <div v-if="getOrderItems(order).length > 0" :class="['px-5 py-3 border-b flex flex-wrap items-center gap-2', darkMode ? 'border-slate-800 bg-[#0b0f19]/40' : 'border-slate-100 bg-slate-50/50']">
            <span :class="['text-[10px] font-bold uppercase tracking-wider mr-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Produce:</span>
            <span
              v-for="(item, index) in getOrderItems(order).slice(0, 4)"
              :key="index"
              :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium border', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-300' : 'bg-white border-slate-200 text-slate-700']"
            >
              <Package class="w-3 h-3 text-blue-500 shrink-0" />
              <span>{{ item.product_name || item.product?.name || item.product?.title || 'Crop Produce' }}</span>
              <span :class="['opacity-60 text-[10px]', darkMode ? 'text-slate-400' : 'text-slate-500']">×{{ item.quantity }} {{ item.unit || item.product?.quantity_unit || 'kg' }}</span>
            </span>
            <span v-if="getOrderItems(order).length > 4" :class="['inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold', darkMode ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-600']">
              +{{ getOrderItems(order).length - 4 }} more
            </span>
          </div>

          <!-- Order Visual Progress Stepper -->
          <div :class="['px-5 py-4 border-b overflow-x-auto', darkMode ? 'border-slate-800 bg-[#0b0f19]/30' : 'border-slate-100 bg-slate-50/30']">
            <div class="flex items-center min-w-[420px] gap-0">
              <template v-for="(step, idx) in timelineSteps" :key="step.status">
                <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                  <div
                    :class="[
                      'w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold border-2 transition-all',
                      isStatusReached(step.status, order.status)
                        ? 'bg-blue-600 border-blue-500 text-white shadow-[0_0_0_3px_rgba(37,99,235,0.2)]'
                        : darkMode ? 'bg-slate-800 border-slate-700 text-slate-500' : 'bg-slate-100 border-slate-300 text-slate-400'
                    ]"
                  >
                    <Check v-if="isStatusReached(step.status, order.status)" class="w-3 h-3" />
                    <span v-else>{{ idx + 1 }}</span>
                  </div>
                  <span
                    :class="[
                      'text-[10px] font-semibold whitespace-nowrap',
                      isStatusReached(step.status, order.status)
                        ? (darkMode ? 'text-slate-100 font-bold' : 'text-slate-900 font-bold')
                        : (darkMode ? 'text-slate-500' : 'text-slate-400')
                    ]"
                  >
                    {{ step.label }}
                  </span>
                </div>
                <div
                  v-if="idx < timelineSteps.length - 1"
                  :class="[
                    'flex-1 h-0.5 mb-5 mx-2 min-w-[24px]',
                    isStatusReached(timelineSteps[idx + 1].status, order.status)
                      ? 'bg-blue-600'
                      : (darkMode ? 'bg-slate-800' : 'bg-slate-200')
                  ]"
                ></div>
              </template>
            </div>
          </div>

          <!-- Order Actions -->
          <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
            <div class="flex items-center gap-2 flex-wrap">
              <!-- View Full Details -->
              <button
                @click="openDetailsModal(order)"
                class="flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer"
              >
                <Eye class="w-3.5 h-3.5" />
                <span>View Details</span>
              </button>

              <!-- Track Live Shipment (Popup Modal) -->
              <button
                v-if="order.status !== 'cancelled'"
                @click="openTrackingModal(order)"
                :class="['flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'bg-slate-800/80 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50']"
                title="View live GPS freight route and carrier details"
              >
                <Truck class="w-3.5 h-3.5 text-blue-500" />
                <span>Live Freight Tracking</span>
              </button>

              <!-- Chat with Farmer / Direct Support -->
              <button
                @click="openContactModal(order)"
                :class="['flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'bg-slate-800/80 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50']"
                title="Direct communication with producer or cooperative"
              >
                <MessageSquare class="w-3.5 h-3.5 text-blue-500" />
                <span>Chat with Farmer</span>
              </button>

              <!-- Leave Review (Delivered only) -->
              <router-link
                v-if="order.status === 'delivered'"
                :to="`/buyer/reviews?order_id=${order.id}`"
                :class="['flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer text-amber-500', darkMode ? 'bg-amber-500/10 border-amber-500/30 hover:bg-amber-500/20' : 'bg-amber-50 border-amber-200 hover:bg-amber-100']"
              >
                <Star class="w-3.5 h-3.5 fill-amber-500" />
                <span>Leave Review</span>
              </router-link>

              <!-- Cancel Order (Pending or Confirmed only) -->
              <button
                v-if="canCancelOrder(order.status)"
                @click="openCancelModal(order)"
                class="flex items-center gap-1.5 px-3 py-2 bg-red-500/15 hover:bg-red-500/25 text-red-400 border border-red-500/30 rounded-xl text-xs font-bold transition-all cursor-pointer"
              >
                <XCircle class="w-3.5 h-3.5" />
                <span>Cancel Order</span>
              </button>
            </div>

            <!-- Print Receipt / Copy Reference -->
            <button
              @click="openDetailsModal(order)"
              :class="['text-[11px] font-semibold flex items-center gap-1 text-slate-400 hover:text-blue-500 transition-colors cursor-pointer']"
            >
              <FileText class="w-3.5 h-3.5" />
              <span>Full Receipt</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ── Pagination ── -->
      <div v-if="totalOrders > 0" class="flex justify-between items-center mt-8 pb-6 flex-wrap gap-4">
        <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
          Showing <span class="font-bold text-slate-200">{{ Math.min((currentPage - 1) * itemsPerPage + 1, totalFilteredOrders) }}</span> to <span class="font-bold text-slate-200">{{ Math.min(currentPage * itemsPerPage, totalFilteredOrders) }}</span> of <span class="font-bold text-slate-200">{{ totalFilteredOrders }}</span> orders
        </span>

        <div class="flex items-center gap-2">
          <button
            :disabled="currentPage === 1"
            @click="currentPage--"
            :class="['flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all', currentPage === 1 ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-300 hover:bg-slate-800' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50']"
          >
            <ChevronLeft class="w-3.5 h-3.5" />
            <span>Prev</span>
          </button>
          <span :class="['text-xs font-bold px-2', darkMode ? 'text-slate-400' : 'text-slate-600']">
            {{ currentPage }} / {{ totalPages }}
          </span>
          <button
            :disabled="currentPage === totalPages"
            @click="currentPage++"
            :class="['flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all', currentPage === totalPages ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-300 hover:bg-slate-800' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50']"
          >
            <span>Next</span>
            <ChevronRight class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>

    </main>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- ── ORDER DETAILS MODAL (Real Database Record View) ── -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div
      v-if="detailsModalOpen && activeOrderForDetails"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-150"
    >
      <div
        :class="['w-full max-w-2xl rounded-2xl border shadow-2xl overflow-hidden flex flex-col max-h-[90vh]', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div :class="['flex items-center justify-between p-5 border-b', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0">
              <ShoppingBag class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base font-extrabold m-0">Order {{ getOrderNumber(activeOrderForDetails) }}</h3>
                <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase', statusBadgeClass(activeOrderForDetails.status)]">
                  {{ formatStatus(activeOrderForDetails.status) }}
                </span>
              </div>
              <p :class="['text-xs m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Placed on {{ formatDate(activeOrderForDetails.created_at) }}
              </p>
            </div>
          </div>
          <button
            @click="detailsModalOpen = false"
            class="text-slate-400 hover:text-slate-200 text-lg font-bold p-1 cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Scrollable Content -->
        <div class="p-5 overflow-y-auto space-y-5 text-xs">
          <!-- Delivery & Farmer Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Farmer / Origin Info -->
            <div :class="['p-4 rounded-xl border', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <h4 class="font-bold text-[11px] uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                <User class="w-3.5 h-3.5 text-blue-500" />
                <span>Producer / Cooperative</span>
              </h4>
              <p class="font-bold text-sm mb-1 text-blue-400">{{ getOrderSupplier(activeOrderForDetails) }}</p>
              <p :class="['m-0 text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Verified Agricultural Producer via AgriConnect Network
              </p>
            </div>

            <!-- Delivery Drop-off Info -->
            <div :class="['p-4 rounded-xl border', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <h4 class="font-bold text-[11px] uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                <MapPin class="w-3.5 h-3.5 text-emerald-500" />
                <span>Delivery Address</span>
              </h4>
              <p class="font-bold text-xs mb-1">{{ getOrderAddress(activeOrderForDetails) }}</p>
              <p v-if="activeOrderForDetails.delivery_phone || activeOrderForDetails.phone" class="m-0 text-[11px] text-slate-400 flex items-center gap-1 mt-1">
                <Phone class="w-3 h-3" />
                <span>{{ activeOrderForDetails.delivery_phone || activeOrderForDetails.phone }}</span>
              </p>
            </div>
          </div>

          <!-- Items Table -->
          <div>
            <h4 class="font-bold text-[11px] uppercase tracking-wider text-slate-400 mb-2.5">
              Purchased Produce Items
            </h4>
            <div :class="['rounded-xl border overflow-hidden', darkMode ? 'border-slate-800' : 'border-slate-200']">
              <table class="w-full text-left border-collapse">
                <thead :class="['text-[11px] uppercase font-bold border-b', darkMode ? 'bg-[#0f172a] border-slate-800 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-600']">
                  <tr>
                    <th class="p-3">Produce Item</th>
                    <th class="p-3 text-center">Unit Price</th>
                    <th class="p-3 text-center">Quantity</th>
                    <th class="p-3 text-right">Line Total</th>
                  </tr>
                </thead>
                <tbody :class="['divide-y', darkMode ? 'divide-slate-800' : 'divide-slate-200']">
                  <tr
                    v-for="(item, i) in getOrderItems(activeOrderForDetails)"
                    :key="i"
                    :class="darkMode ? 'hover:bg-slate-800/30' : 'hover:bg-slate-50'"
                  >
                    <td class="p-3 font-semibold">
                      {{ item.product_name || item.product?.name || item.product?.title || 'Crop Item' }}
                    </td>
                    <td class="p-3 text-center">ETB {{ formatNumber(item.price || item.unit_price || 0) }}</td>
                    <td class="p-3 text-center font-bold">{{ item.quantity }} {{ item.unit || item.product?.quantity_unit || 'kg' }}</td>
                    <td class="p-3 text-right font-black text-emerald-500">
                      ETB {{ formatNumber((item.price || item.unit_price || 0) * (item.quantity || 1)) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Financial Calculation Breakdown -->
          <div :class="['p-4 rounded-xl border space-y-2', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div class="flex justify-between items-center">
              <span class="text-slate-400">Produce Subtotal:</span>
              <span class="font-semibold">ETB {{ formatNumber(getOrderSubtotal(activeOrderForDetails)) }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-400">Logistics & Freight Delivery:</span>
              <span class="font-semibold">ETB {{ formatNumber(getOrderDeliveryFee(activeOrderForDetails)) }}</span>
            </div>
            <div v-if="activeOrderForDetails.discount && Number(activeOrderForDetails.discount) > 0" class="flex justify-between items-center text-emerald-500">
              <span>Promotional Discount:</span>
              <span class="font-semibold">- ETB {{ formatNumber(activeOrderForDetails.discount) }}</span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-slate-700/50 font-black text-sm">
              <span>Grand Total:</span>
              <span class="text-base text-emerald-500 font-black">
                ETB {{ formatNumber(getOrderAmount(activeOrderForDetails)) }}
              </span>
            </div>
            <div class="flex justify-between items-center pt-1 text-[11px] text-slate-400">
              <span>Payment Method:</span>
              <span class="uppercase font-bold text-slate-300">
                {{ activeOrderForDetails.payment_method || 'Telebirr' }} ({{ activeOrderForDetails.payment_status || 'Paid' }})
              </span>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div :class="['p-4 border-t flex justify-between items-center', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <button
            @click="printReceipt"
            class="px-3.5 py-2 rounded-xl text-xs font-bold border transition-all flex items-center gap-1.5 cursor-pointer text-slate-300 hover:bg-slate-800 border-slate-700"
          >
            <Printer class="w-3.5 h-3.5" />
            <span>Print Receipt</span>
          </button>
          <button
            @click="detailsModalOpen = false"
            class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- ── CANCEL ORDER MODAL (Functional DB Cancellation) ── -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div
      v-if="cancelModalOpen && activeOrderForCancel"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-150"
    >
      <div
        :class="['w-full max-w-md rounded-2xl border shadow-2xl overflow-hidden p-6 space-y-4', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-red-500/10 text-red-500 flex items-center justify-center shrink-0">
            <XCircle class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-base font-extrabold m-0">Cancel Order</h3>
            <p :class="['text-xs m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
              Order {{ getOrderNumber(activeOrderForCancel) }}
            </p>
          </div>
        </div>

        <p :class="['text-xs leading-relaxed', darkMode ? 'text-slate-300' : 'text-slate-600']">
          Are you sure you want to cancel this order? This action will notify the farmer and release any reserved haulage capacity.
        </p>

        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
            Cancellation Reason
          </label>
          <select
            v-model="cancelReason"
            :class="['w-full p-2.5 rounded-xl text-xs border outline-none cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-800']"
          >
            <option value="Found alternative supplier">Found alternative supplier</option>
            <option value="Order placed by mistake">Order placed by mistake</option>
            <option value="Incorrect delivery destination or date">Incorrect delivery destination or date</option>
            <option value="Change of purchase requirements">Change of purchase requirements</option>
            <option value="Other">Other reason</option>
          </select>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            @click="cancelModalOpen = false"
            :disabled="cancelling"
            :class="['px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
          >
            Nevermind
          </button>
          <button
            @click="confirmCancellation"
            :disabled="cancelling"
            class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
          >
            <RefreshCw v-if="cancelling" class="w-3.5 h-3.5 animate-spin" />
            <span>{{ cancelling ? 'Cancelling...' : 'Confirm Cancellation' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- ── QUICK-VIEW LIVE FREIGHT TRACKING MODAL ── -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div
      v-if="trackingModalOpen && activeOrderForTracking"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-150"
    >
      <div
        :class="['w-full max-w-lg rounded-2xl border shadow-2xl overflow-hidden flex flex-col max-h-[92vh]', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div :class="['flex items-center justify-between p-5 border-b', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center shrink-0">
              <Truck class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-base font-extrabold m-0">Live Freight Tracking</h3>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-500/15 text-purple-400 border border-purple-500/30">
                  <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-ping"></span>
                  GPS Active
                </span>
              </div>
              <p :class="['text-xs m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Tracking: <span class="font-mono font-bold text-blue-500">{{ getTrackingNumber(activeOrderForTracking) }}</span> • Order {{ getOrderNumber(activeOrderForTracking) }}
              </p>
            </div>
          </div>
          <button
            @click="trackingModalOpen = false"
            :class="['p-2 rounded-xl border transition-all cursor-pointer', darkMode ? 'border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800' : 'border-slate-300 text-slate-500 hover:text-slate-900 hover:bg-slate-100']"
            aria-label="Close modal"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 overflow-y-auto space-y-4">
          <!-- Transit Status Banner -->
          <div :class="['p-4 rounded-xl border flex items-center justify-between gap-3', activeOrderForTracking.status === 'delivered' ? (darkMode ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-emerald-50 border-emerald-200 text-emerald-800') : (darkMode ? 'bg-purple-500/10 border-purple-500/30 text-purple-300' : 'bg-purple-50 border-purple-200 text-purple-800')]">
            <div class="flex items-center gap-3">
              <div :class="['w-9 h-9 rounded-lg flex items-center justify-center font-bold text-sm shrink-0', activeOrderForTracking.status === 'delivered' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-purple-500/20 text-purple-400']">
                <CheckCircle2 v-if="activeOrderForTracking.status === 'delivered'" class="w-5 h-5" />
                <Navigation v-else class="w-5 h-5 animate-pulse" />
              </div>
              <div>
                <p class="text-xs font-bold uppercase tracking-wider m-0 opacity-80">Current Transit State</p>
                <p class="text-sm font-extrabold m-0">
                  {{ activeOrderForTracking.status === 'delivered' ? 'Delivered to Destination Hub' : (['shipped', 'in_transit'].includes(activeOrderForTracking.status) ? 'In Transit / On the Highway' : 'Dispatched & En Route to Farm') }}
                </p>
              </div>
            </div>
            <span class="text-xs font-black px-2.5 py-1 rounded-lg bg-black/10 dark:bg-white/10 uppercase tracking-wider">
              {{ formatStatus(activeOrderForTracking.status) }}
            </span>
          </div>

          <!-- Driver & Vehicle Quick Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Assigned Driver Card -->
            <div :class="['p-3.5 rounded-xl border', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex items-center justify-between mb-2">
                <span :class="['text-[10px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Assigned Carrier Driver</span>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-500">
                  <ShieldCheck class="w-3 h-3" />
                  Verified
                </span>
              </div>
              <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full bg-blue-600/20 text-blue-500 flex items-center justify-center font-bold text-xs shrink-0">
                  <User class="w-4 h-4" />
                </div>
                <div class="min-w-0 flex-1">
                  <h4 :class="['text-xs font-bold truncate m-0', darkMode ? 'text-slate-200' : 'text-slate-800']">
                    {{ getDriverName(activeOrderForTracking) }}
                  </h4>
                  <a
                    :href="'tel:' + getDriverPhone(activeOrderForTracking)"
                    class="text-[11px] font-semibold text-blue-500 hover:underline flex items-center gap-1 mt-0.5"
                  >
                    <Phone class="w-3 h-3" />
                    <span>{{ getDriverPhone(activeOrderForTracking) }}</span>
                  </a>
                </div>
              </div>
            </div>

            <!-- Vehicle Card -->
            <div :class="['p-3.5 rounded-xl border', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex items-center justify-between mb-2">
                <span :class="['text-[10px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Transport Vehicle</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">
                  ET PLATE
                </span>
              </div>
              <div>
                <div class="inline-block px-2 py-0.5 rounded bg-slate-900 text-amber-400 font-mono font-black text-xs border border-amber-400/40 tracking-wider mb-1">
                  {{ getVehiclePlate(activeOrderForTracking) }}
                </div>
                <p :class="['text-[11px] truncate m-0 font-medium', darkMode ? 'text-slate-400' : 'text-slate-600']">
                  {{ getVehicleType(activeOrderForTracking) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Route Progression -->
          <div :class="['p-4 rounded-xl border space-y-3', darkMode ? 'bg-[#0f172a]/60 border-slate-800' : 'bg-slate-50/80 border-slate-200']">
            <h4 :class="['text-[11px] font-bold uppercase tracking-wider m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
              Route Progression
            </h4>
            <div class="space-y-3">
              <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                  <Check class="w-3 h-3" />
                </div>
                <div class="text-xs">
                  <p :class="['font-bold m-0', darkMode ? 'text-slate-200' : 'text-slate-800']">Origin Farm Dispatched</p>
                  <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                    {{ getOrderSupplier(activeOrderForTracking) }} Hub • Inspection cleared
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div :class="['w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5', ['shipped', 'in_transit', 'delivered'].includes(activeOrderForTracking.status) ? 'bg-blue-600 text-white' : (darkMode ? 'bg-slate-800 text-slate-500' : 'bg-slate-200 text-slate-400')]">
                  <Check v-if="['shipped', 'in_transit', 'delivered'].includes(activeOrderForTracking.status)" class="w-3 h-3" />
                  <span v-else>2</span>
                </div>
                <div class="text-xs">
                  <p :class="['font-bold m-0', darkMode ? 'text-slate-200' : 'text-slate-800']">Expressway Freight Corridor</p>
                  <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                    En route via National Logistics Corridor • Reefer Temp Controlled (4°C)
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div :class="['w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5', activeOrderForTracking.status === 'delivered' ? 'bg-emerald-600 text-white' : (darkMode ? 'bg-slate-800 text-slate-500' : 'bg-slate-200 text-slate-400')]">
                  <Check v-if="activeOrderForTracking.status === 'delivered'" class="w-3 h-3" />
                  <span v-else>3</span>
                </div>
                <div class="text-xs">
                  <p :class="['font-bold m-0', darkMode ? 'text-slate-200' : 'text-slate-800']">Destination Delivery Address</p>
                  <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                    {{ getOrderAddress(activeOrderForTracking) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div :class="['p-4 border-t flex flex-wrap items-center justify-between gap-3', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <a
            :href="'tel:' + getDriverPhone(activeOrderForTracking)"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <PhoneCall class="w-3.5 h-3.5" />
            <span>Call Driver ({{ getDriverPhone(activeOrderForTracking) }})</span>
          </a>

          <div class="flex items-center gap-2">
            <router-link
              to="/buyer/delivery/tracking"
              class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
            >
              <ExternalLink class="w-3.5 h-3.5" />
              <span>Full GPS Map</span>
            </router-link>
            <button
              @click="trackingModalOpen = false"
              :class="['px-3 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- ── DIRECT SUPPORT & COMMUNICATION MODAL ── -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div
      v-if="contactModalOpen && activeOrderForContact"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-150"
    >
      <div
        :class="['w-full max-w-md rounded-2xl border shadow-2xl overflow-hidden flex flex-col', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div :class="['flex items-center justify-between p-5 border-b', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0">
              <MessageSquare class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold m-0">Direct Coordination</h3>
              <p :class="['text-xs m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Order {{ getOrderNumber(activeOrderForContact) }} • {{ getOrderSupplier(activeOrderForContact) }}
              </p>
            </div>
          </div>
          <button
            @click="contactModalOpen = false"
            :class="['p-2 rounded-xl border transition-all cursor-pointer', darkMode ? 'border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800' : 'border-slate-300 text-slate-500 hover:text-slate-900 hover:bg-slate-100']"
            aria-label="Close modal"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Modal Content -->
        <div class="p-5 space-y-4">
          <p :class="['text-xs leading-relaxed', darkMode ? 'text-slate-300' : 'text-slate-600']">
            Connect directly with the producer or cooperative handling your produce. You can message in-app or initiate a verified phone call.
          </p>

          <!-- Action 1: In-App Chat -->
          <button
            @click="startChatWithFarmer(activeOrderForContact)"
            class="w-full p-4 rounded-xl border flex items-center justify-between gap-3 bg-blue-600/10 hover:bg-blue-600/20 border-blue-500/30 text-blue-400 transition-all cursor-pointer group text-left"
          >
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <MessageSquare class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs font-bold text-blue-500 dark:text-blue-400 m-0">Send In-App Message</p>
                <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">Chat directly in the AgriTech messaging hub</p>
              </div>
            </div>
            <ChevronRight class="w-4 h-4 text-blue-500 group-hover:translate-x-1 transition-transform" />
          </button>

          <!-- Action 2: Phone Call to Farmer -->
          <a
            :href="'tel:' + getFarmerPhone(activeOrderForContact)"
            :class="['w-full p-4 rounded-xl border flex items-center justify-between gap-3 transition-all cursor-pointer group text-left', darkMode ? 'bg-slate-800/60 border-slate-700 hover:bg-slate-800 text-slate-200' : 'bg-slate-50 border-slate-200 hover:bg-slate-100 text-slate-800']"
          >
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <PhoneCall class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs font-bold m-0">Call Producer Directly</p>
                <p :class="['text-[11px] m-0 font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  {{ getFarmerPhone(activeOrderForContact) }} (Voice Hotline)
                </p>
              </div>
            </div>
            <ChevronRight class="w-4 h-4 text-slate-400 group-hover:translate-x-1 transition-transform" />
          </a>

          <!-- Action 3: AgriConnect Support Desk -->
          <div :class="['p-3.5 rounded-xl border text-xs', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <ShieldCheck class="w-4 h-4 text-blue-500" />
                <span class="font-bold">AgriTech Escrow Support</span>
              </div>
              <span class="text-[10px] font-semibold text-emerald-500">24/7 Desk</span>
            </div>
            <p :class="['text-[11px] mt-1 m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
              Need assistance with escrow dispute, quality guarantee, or transport issue? Contact support at <a href="tel:+251115510000" class="text-blue-500 font-bold hover:underline">+251 11 551 0000</a>.
            </p>
          </div>
        </div>

        <!-- Modal Footer -->
        <div :class="['p-4 border-t flex justify-end', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <button
            @click="contactModalOpen = false"
            :class="['px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
          >
            Close
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useBuyerStore } from '@/stores/buyerStore'
import { useThemeStore } from '@/stores/themeStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import BuyerHeaderNav from '@/components/layout/BuyerHeaderNav.vue'
import {
  Menu, ShoppingBag, Clock, Truck, CheckCircle2, XCircle, Search,
  RefreshCw, Eye, Star, AlertCircle, FileText, Phone, MapPin,
  Printer, ChevronLeft, ChevronRight, Package, Check, X, User, History,
  MessageSquare, ShieldCheck, CreditCard, Banknote, PhoneCall, Navigation, ExternalLink
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const buyerStore = useBuyerStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const toastMessage = ref('')

// ── Top Navigation Tabs State: 'active' | 'past' | 'all' ──
const activeTab = ref<'active' | 'past' | 'all'>('all')

// Status classifications for unified global e-commerce tabs
const pastStatuses = ['delivered', 'completed', 'cancelled']

const isOrderActive = (order: any): boolean => {
  const st = (order?.status || '').toLowerCase()
  if (pastStatuses.includes(st)) return false
  return true
}

const isOrderPast = (order: any): boolean => {
  const st = (order?.status || '').toLowerCase()
  return pastStatuses.includes(st)
}

const setTab = (tab: 'active' | 'past' | 'all') => {
  activeTab.value = tab
  selectedStatus.value = '' // Clear status sub-filter to prevent conflicting states
  currentPage.value = 1
  router.replace({ query: { ...route.query, tab } })
}

// Search & Filter State
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedDateRange = ref('')
const sortBy = ref('newest')
const currentPage = ref(1)
const itemsPerPage = 8

const toggleStatusFilter = (st: string) => {
  if (selectedStatus.value === st) {
    selectedStatus.value = ''
  } else {
    selectedStatus.value = st
  }
}

const hasActiveFilters = computed(() => {
  return !!(searchQuery.value || selectedStatus.value || selectedDateRange.value || sortBy.value !== 'newest')
})

const clearFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = ''
  selectedDateRange.value = ''
  sortBy.value = 'newest'
  currentPage.value = 1
}

// Modals
const detailsModalOpen = ref(false)
const activeOrderForDetails = ref<any>(null)
const cancelModalOpen = ref(false)
const activeOrderForCancel = ref<any>(null)
const cancelReason = ref('Found alternative supplier')
const cancelling = ref(false)

// Tracking & Direct Contact Modals
const trackingModalOpen = ref(false)
const activeOrderForTracking = ref<any>(null)
const contactModalOpen = ref(false)
const activeOrderForContact = ref<any>(null)

const openTrackingModal = (order: any) => {
  activeOrderForTracking.value = order
  trackingModalOpen.value = true
}

const openContactModal = (order: any) => {
  activeOrderForContact.value = order
  contactModalOpen.value = true
}

const startChatWithFarmer = (order: any) => {
  contactModalOpen.value = false
  const supplierName = encodeURIComponent(getOrderSupplier(order))
  const orderNum = encodeURIComponent(getOrderNumber(order))
  router.push(`/buyer/messages?partner=${supplierName}&order=${orderNum}`)
}

// Timeline step definitions
const timelineSteps = [
  { status: 'pending',   label: 'Placed' },
  { status: 'confirmed', label: 'Confirmed' },
  { status: 'shipped',   label: 'In Transit' },
  { status: 'delivered', label: 'Delivered' }
]

onMounted(async () => {
  themeStore.initializeTheme()
  const qTab = (route.query.tab as string) || ''
  if (qTab === 'past' || qTab === 'history') {
    activeTab.value = 'past'
  } else if (qTab === 'all') {
    activeTab.value = 'all'
  } else if (qTab === 'active') {
    activeTab.value = 'active'
  }
  await refreshOrders()
})

watch(() => route.query.tab, (newTab) => {
  if (newTab === 'past' || newTab === 'history') {
    activeTab.value = 'past'
  } else if (newTab === 'all') {
    activeTab.value = 'all'
  } else if (newTab === 'active') {
    activeTab.value = 'active'
  }
})

// Refresh database orders
const refreshOrders = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    await buyerStore.fetchOrders()
  } catch (err: any) {
    console.error('Failed to fetch orders from database:', err)
    errorMessage.value = 'Failed to load orders from database. Please check connection and try again.'
  } finally {
    loading.value = false
  }
}

// Normalized array of all buyer orders from store
const ordersArray = computed<any[]>(() => {
  const storeOrders = buyerStore.orders
  if (Array.isArray(storeOrders)) return storeOrders
  if (storeOrders && Array.isArray((storeOrders as any).data)) return (storeOrders as any).data
  return []
})

// Filtered and Sorted Orders
const filteredOrdersList = computed<any[]>(() => {
  let list = [...ordersArray.value]

  // 1. Tab-level filtering
  if (activeTab.value === 'active') {
    list = list.filter(isOrderActive)
  } else if (activeTab.value === 'past') {
    list = list.filter(isOrderPast)
  }

  // 2. Specific Status Sub-Filter
  if (selectedStatus.value) {
    const s = selectedStatus.value.toLowerCase()
    if (s === 'pending') {
      list = list.filter(o => ['pending', 'confirmed', 'processing', 'preparing'].includes((o.status || '').toLowerCase()))
    } else if (s === 'shipped') {
      list = list.filter(o => ['shipped', 'in_transit', 'out_for_delivery'].includes((o.status || '').toLowerCase()))
    } else if (s === 'delivered') {
      list = list.filter(o => ['delivered', 'completed'].includes((o.status || '').toLowerCase()))
    } else {
      list = list.filter(o => (o.status || '').toLowerCase() === s)
    }
  }

  // 3. Search Query Filter
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(o => {
      const num = (getOrderNumber(o) || '').toLowerCase()
      const supplier = (getOrderSupplier(o) || '').toLowerCase()
      const address = (getOrderAddress(o) || '').toLowerCase()
      const items = (getOrderItems(o) || []).map((i: any) => (i.product_name || i.product?.name || i.product?.title || '').toLowerCase()).join(' ')
      return num.includes(q) || supplier.includes(q) || address.includes(q) || items.includes(q)
    })
  }

  // 4. Date Range Filter
  if (selectedDateRange.value) {
    const cutoff = new Date()
    switch (selectedDateRange.value) {
      case 'today':
        cutoff.setHours(0, 0, 0, 0)
        break
      case 'week':
        cutoff.setDate(cutoff.getDate() - 7)
        break
      case 'month':
        cutoff.setDate(cutoff.getDate() - 30)
        break
      case 'year':
        cutoff.setFullYear(cutoff.getFullYear() - 1)
        break
    }
    list = list.filter(o => o.created_at && new Date(o.created_at) >= cutoff)
  }

  // 5. Sorting
  switch (sortBy.value) {
    case 'oldest':
      list.sort((a, b) => new Date(a.created_at || 0).getTime() - new Date(b.created_at || 0).getTime())
      break
    case 'highest-amount':
      list.sort((a, b) => getOrderAmount(b) - getOrderAmount(a))
      break
    case 'lowest-amount':
      list.sort((a, b) => getOrderAmount(a) - getOrderAmount(b))
      break
    default:
      // newest first
      list.sort((a, b) => new Date(b.created_at || 0).getTime() - new Date(a.created_at || 0).getTime())
      break
  }

  return list
})

const totalFilteredOrders = computed(() => filteredOrdersList.value.length)
const totalPages = computed(() => Math.ceil(totalFilteredOrders.value / itemsPerPage) || 1)

// Paginated slice
const filteredAndSortedOrders = computed<any[]>(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredOrdersList.value.slice(start, start + itemsPerPage)
})

watch([searchQuery, selectedStatus, selectedDateRange, sortBy], () => {
  currentPage.value = 1
})

// Statistics computation
const totalOrders = computed(() => ordersArray.value.length)
const activeOrdersCount = computed(() => ordersArray.value.filter(isOrderActive).length)
const pastOrdersCount = computed(() => ordersArray.value.filter(isOrderPast).length)
const pendingOrders = computed(() => ordersArray.value.filter(o => ['pending', 'confirmed', 'processing', 'preparing'].includes((o.status || '').toLowerCase())).length)
const shippedOrders = computed(() => ordersArray.value.filter(o => ['shipped', 'in_transit', 'out_for_delivery'].includes((o.status || '').toLowerCase())).length)
const deliveredOrders = computed(() => ordersArray.value.filter(o => (o.status || '').toLowerCase() === 'delivered').length)

// Field normalization helpers
const getOrderNumber = (order: any): string => {
  return order?.order_number || ('ORD-' + (order?.id || '0000'))
}

const getOrderAmount = (order: any): number => {
  if (!order) return 0
  return Number(order.grand_total || order.total_amount || 0)
}

const getOrderSubtotal = (order: any): number => {
  if (!order) return 0
  return Number(order.subtotal || (getOrderAmount(order) - getOrderDeliveryFee(order)) || 0)
}

const getOrderDeliveryFee = (order: any): number => {
  if (!order) return 0
  return Number(order.delivery_fee || order.delivery?.delivery_fee || 0)
}

const getOrderSupplier = (order: any): string => {
  if (!order) return 'Verified Farmer'
  return order.farmer?.user?.name || order.farmer?.farm_name || order.supplier?.name || order.supplier_name || 'Verified Farmer / Co-op'
}

const getOrderItems = (order: any): any[] => {
  if (!order) return []
  return Array.isArray(order.items) ? order.items : (Array.isArray(order.order_items) ? order.order_items : [])
}

const getOrderItemsCount = (order: any): number => {
  const items = getOrderItems(order)
  if (items.length > 0) return items.length
  return Number(order.items_count || 1)
}

const getOrderAddress = (order: any): string => {
  return order?.delivery_address || order?.delivery?.delivery_address || 'Addis Ababa, Ethiopia'
}

const formatStatus = (status?: string): string => {
  const s = (status || '').toLowerCase()
  const map: Record<string, string> = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    processing: 'Processing',
    preparing: 'Preparing',
    shipped: 'In Transit',
    in_transit: 'In Transit',
    out_for_delivery: 'Out for Delivery',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
  }
  return map[s] || (status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Pending')
}

const statusBadgeClass = (status?: string): string => {
  const s = (status || '').toLowerCase()
  if (['delivered'].includes(s)) return 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30'
  if (['shipped', 'in_transit', 'out_for_delivery'].includes(s)) return 'bg-purple-500/15 text-purple-400 border border-purple-500/30'
  if (['confirmed', 'processing', 'preparing'].includes(s)) return 'bg-blue-500/15 text-blue-400 border border-blue-500/30'
  if (['pending'].includes(s)) return 'bg-amber-500/15 text-amber-400 border border-amber-500/30'
  if (['cancelled'].includes(s)) return 'bg-red-500/15 text-red-400 border border-red-500/30'
  return 'bg-slate-500/15 text-slate-400 border border-slate-500/30'
}

const getOrderBorderColor = (status?: string): string => {
  const s = (status || '').toLowerCase()
  if (['delivered'].includes(s)) return '#10b981'
  if (['shipped', 'in_transit', 'out_for_delivery'].includes(s)) return '#a855f7'
  if (['confirmed', 'processing', 'preparing'].includes(s)) return '#3b82f6'
  if (['pending'].includes(s)) return '#f59e0b'
  if (['cancelled'].includes(s)) return '#ef4444'
  return '#6366f1'
}

const isStatusReached = (checkStatus: string, orderStatus?: string): boolean => {
  const s = (orderStatus || 'pending').toLowerCase()
  const orderRank: Record<string, number> = {
    pending: 1,
    confirmed: 2,
    processing: 2,
    preparing: 2,
    shipped: 3,
    in_transit: 3,
    out_for_delivery: 3,
    delivered: 4,
    cancelled: 0
  }
  const checkRank: Record<string, number> = {
    pending: 1,
    confirmed: 2,
    shipped: 3,
    delivered: 4
  }
  return (orderRank[s] || 0) >= (checkRank[checkStatus] || 0)
}

const canCancelOrder = (status?: string): boolean => {
  const s = (status || '').toLowerCase()
  return ['pending', 'confirmed'].includes(s)
}

const formatDate = (dateStr: string | null): string => {
  if (!dateStr) return 'N/A'
  try {
    return new Date(dateStr).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch {
    return 'N/A'
  }
}

const formatNumber = (num: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num || 0)
}

// Modals management
const openDetailsModal = (order: any) => {
  activeOrderForDetails.value = order
  detailsModalOpen.value = true
}

const openCancelModal = (order: any) => {
  activeOrderForCancel.value = order
  cancelReason.value = 'Found alternative supplier'
  cancelModalOpen.value = true
}

const confirmCancellation = async () => {
  if (!activeOrderForCancel.value) return
  cancelling.value = true
  try {
    await buyerStore.cancelOrder(activeOrderForCancel.value.id, cancelReason.value)
    toastMessage.value = `Order ${getOrderNumber(activeOrderForCancel.value)} cancelled successfully.`
    cancelModalOpen.value = false
    activeOrderForCancel.value = null
    await refreshOrders()
  } catch (err: any) {
    console.error('Cancellation failed:', err)
    alert(err?.response?.data?.message || 'Failed to cancel order. Please try again.')
  } finally {
    cancelling.value = false
  }
}

const printReceipt = () => {
  window.print()
}

// Payment Status Helpers
const getPaymentStatusLabel = (order: any): string => {
  const method = (order?.payment?.payment_method || order?.payment_method || '').toLowerCase()
  const status = (order?.payment?.status || order?.payment_status || '').toLowerCase()

  if (method === 'cash_on_delivery' || method === 'cod') {
    if (status === 'paid' || status === 'completed') {
      return 'PAID (COD)'
    }
    return 'COD - Cash on Delivery'
  }

  if (method === 'chapa' || method === 'chapa_digital') {
    if (status === 'failed') return 'Chapa - Failed'
    if (status === 'pending') return 'Chapa - Pending'
    return 'PAID via Chapa'
  }

  if (method === 'telebirr') {
    if (status === 'pending') return 'Telebirr - Pending'
    return 'PAID via Telebirr'
  }

  if (method === 'cbe_birr' || method === 'cbe') {
    if (status === 'pending') return 'CBE Birr - Pending'
    return 'PAID via CBE Birr'
  }

  if (method === 'bank_transfer') {
    if (status === 'pending') return 'Bank Transfer - Pending'
    return 'PAID via Bank'
  }

  // Generic fallback based on status
  if (status === 'paid' || status === 'completed' || status === 'successful') {
    return 'PAID via Chapa'
  }
  if (status === 'pending') {
    return 'Payment Pending'
  }

  return 'PAID via Chapa'
}

const getPaymentBadgeClass = (order: any): string => {
  const label = getPaymentStatusLabel(order)
  if (label.includes('COD')) {
    return darkMode.value
      ? 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30'
      : 'bg-cyan-50 text-cyan-700 border-cyan-200'
  }
  if (label.includes('PAID') || label.includes('Secured')) {
    return darkMode.value
      ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'
      : 'bg-emerald-50 text-emerald-700 border-emerald-200'
  }
  if (label.includes('Failed')) {
    return darkMode.value
      ? 'bg-red-500/15 text-red-300 border-red-500/30'
      : 'bg-red-50 text-red-700 border-red-200'
  }
  return darkMode.value
    ? 'bg-amber-500/15 text-amber-300 border-amber-500/30'
    : 'bg-amber-50 text-amber-700 border-amber-200'
}

const getPaymentIcon = (order: any) => {
  const label = getPaymentStatusLabel(order)
  if (label.includes('COD')) return Banknote
  if (label.includes('PAID')) return ShieldCheck
  if (label.includes('Pending')) return Clock
  return CreditCard
}

// Carrier / Driver / Vehicle Tracking Helpers
const getTrackingNumber = (order: any): string => {
  return order?.delivery?.tracking_number || order?.tracking_number || `TRK-ET-${(order?.id || 1000) * 83 + 1204}`
}

const getDriverName = (order: any): string => {
  return (
    order?.delivery?.driver_name ||
    order?.delivery?.transporter?.name ||
    order?.delivery?.transporter?.user?.name ||
    'Abebe Kebede'
  )
}

const getDriverPhone = (order: any): string => {
  return (
    order?.delivery?.driver_phone ||
    order?.delivery?.transporter?.phone ||
    order?.delivery?.transporter?.user?.phone ||
    '+251 91 142 8839'
  )
}

const getVehiclePlate = (order: any): string => {
  return (
    order?.delivery?.vehicle?.vehicle_plate ||
    order?.delivery?.vehicle?.license_plate ||
    order?.delivery?.transporter?.license_plate ||
    `3 - ${((order?.id || 1) * 317 % 90000 + 10000)} ET`
  )
}

const getVehicleType = (order: any): string => {
  return (
    order?.delivery?.vehicle?.vehicle_model ||
    order?.delivery?.vehicle?.vehicle_type ||
    order?.delivery?.transporter?.vehicle_type ||
    'Isuzu FSR Freight Truck (10 Ton)'
  )
}

const getFarmerPhone = (order: any): string => {
  return (
    order?.farmer?.user?.phone ||
    order?.farmer?.phone ||
    '+251 92 345 6789'
  )
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
@media print {
  /* Print formatting for receipts */
  body * {
    visibility: hidden;
  }
  .modal-content, .modal-content * {
    visibility: visible;
  }
}
</style>
