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
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                  <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                  Live Fulfillment
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Orders &gt; Active
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Active Orders
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Real-time tracking and delivery updates for orders currently in progress
              </p>
            </div>
          </div>

          <!-- Header Right Actions -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">

            
            
          </div>
        </div>
      </div>

      <!-- ── Summary Stats Cards ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Active -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Active In-Progress</h3>
            <p class="text-2xl font-black">{{ stats.totalActive }}</p>
            <span class="text-[10px] text-blue-500 font-medium">Orders being fulfilled</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <Clock class="w-6 h-6" />
          </div>
        </article>

        <!-- In Transit -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-purple-500' : 'bg-white border-slate-200 text-slate-900 !border-l-purple-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">In Transit</h3>
            <p class="text-2xl font-black">{{ stats.inTransit }}</p>
            <span class="text-[10px] text-purple-500 font-medium">Vehicles on the road</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-purple-500/10 text-purple-400' : 'bg-purple-50 text-purple-600']">
            <Truck class="w-6 h-6" />
          </div>
        </article>

        <!-- Confirmed & Processing -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-amber-500' : 'bg-white border-slate-200 text-slate-900 !border-l-amber-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Packing & Prep</h3>
            <p class="text-2xl font-black">{{ stats.processing }}</p>
            <span class="text-[10px] text-amber-500 font-medium">Confirmed by farmers</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <PackageCheck class="w-6 h-6" />
          </div>
        </article>

        <!-- Total In-Flight Value -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-emerald-500' : 'bg-white border-slate-200 text-slate-900 !border-l-emerald-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">In-Flight Value</h3>
            <p class="text-xl lg:text-2xl font-black text-emerald-500">ETB {{ formatNumber(stats.totalValue) }}</p>
            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
              <ShieldCheck class="w-3 h-3 inline" /> Escrow secured
            </span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <BadgeDollarSign class="w-6 h-6" />
          </div>
        </article>
      </section>

      <!-- ── Search & Filter Controls ── -->
      <div :class="['rounded-2xl border p-4 lg:p-5 mb-6 shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
          <!-- Search Box -->
          <div class="relative flex-1">
            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by order #, product name, or seller..."
              :class="['w-full pl-10 pr-4 py-2.5 rounded-xl text-xs border transition-all outline-none', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500 placeholder-slate-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-blue-500 placeholder-slate-400']"
            />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-bold"
            >
              ✕
            </button>
          </div>

          <!-- Filter Pills -->
          <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
            <button
              v-for="filter in statusFilters"
              :key="filter.value"
              @click="activeStatusFilter = filter.value"
              :class="[
                'px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5',
                activeStatusFilter === filter.value
                  ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30'
                  : darkMode
                    ? 'bg-[#0f172a] text-slate-300 hover:bg-slate-800 border border-slate-700'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200'
              ]"
            >
              <span>{{ filter.label }}</span>
              <span
                :class="[
                  'px-1.5 py-0.2 rounded-full text-[10px] font-extrabold',
                  activeStatusFilter === filter.value
                    ? 'bg-white/20 text-white'
                    : darkMode ? 'bg-slate-800 text-slate-400' : 'bg-slate-200 text-slate-600'
                ]"
              >
                {{ filter.count }}
              </span>
            </button>
          </div>

          <!-- Sort Select -->
          <div class="flex items-center gap-2">
            <select
              v-model="sortBy"
              :class="['px-3 py-2.5 rounded-xl text-xs border transition-all outline-none cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-blue-500']"
            >
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="delivery-soon">Arriving Soonest</option>
              <option value="highest-amount">Highest Amount</option>
              <option value="lowest-amount">Lowest Amount</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ── Loading State ── -->
      <div v-if="loading" :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="w-10 h-10 border-4 border-slate-300 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
        <p :class="['text-sm font-semibold', darkMode ? 'text-slate-200' : 'text-slate-700']">Loading active orders from database...</p>
        <p :class="['text-xs mt-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Retrieving real-time statuses and tracking coordinates</p>
      </div>

      <!-- ── Empty State ── -->
      <div
        v-else-if="filteredOrders.length === 0"
        :class="['rounded-2xl border p-10 lg:p-14 text-center flex flex-col items-center justify-center shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
      >
        <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-4 border', darkMode ? 'bg-slate-800/60 border-slate-700 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-400']">
          <PackageOpen class="w-8 h-8" />
        </div>
        <h3 :class="['text-lg font-extrabold mb-1', darkMode ? 'text-slate-100' : 'text-slate-800']">
          No Active Orders Found
        </h3>
        <p :class="['text-xs lg:text-sm max-w-md mb-6', darkMode ? 'text-slate-400' : 'text-slate-500']">
          <span v-if="searchQuery || activeStatusFilter">
            No active orders match your search criteria. Try resetting filters or search terms.
          </span>
          <span v-else>
            You currently have no active orders in progress. Completed orders can be found in your Order History.
          </span>
        </p>

        <div class="flex items-center gap-3 flex-wrap justify-center">
          <button
            v-if="searchQuery || activeStatusFilter"
            @click="resetFilters"
            class="px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Clear Filters
          </button>
  
          
        </div>
      </div>

      <!-- ── Active Orders List ── -->
      <div v-else class="flex flex-col gap-6">
        <article
          v-for="order in filteredOrders"
          :key="order.id"
          :class="[
            'rounded-2xl border overflow-hidden shadow-2xs hover:shadow-md transition-all',
            darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200'
          ]"
          :style="{ 'border-left': `5px solid ${getOrderAccentColor(order.status)}` }"
        >
          <!-- Card Header Bar -->
          <div :class="['flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b', darkMode ? 'bg-[#0f1729] border-slate-800/80' : 'bg-slate-50 border-slate-200/80']">
            <div class="flex items-start sm:items-center gap-3 flex-wrap">
              <div class="flex items-center gap-2">
                <span :class="['text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border', darkMode ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-white border-slate-300 text-slate-700']">
                  #{{ order.order_number || ('ORD-' + order.id) }}
                </span>
                <span :class="['text-xs', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Placed {{ formatDateTime(order.created_at) }}
                </span>
              </div>
              <span class="hidden sm:inline text-slate-300 dark:text-slate-700">•</span>
              <!-- Seller info -->
              <div class="flex items-center gap-1.5 text-xs font-medium">
                <User class="w-3.5 h-3.5 text-slate-400" />
                <span :class="darkMode ? 'text-slate-300' : 'text-slate-700'">
                  {{ order.seller?.name || order.seller_name || order.farmer?.name || order.farmer_name || 'Verified Supplier' }}
                </span>
                <span class="px-1.5 py-0.2 rounded text-[10px] bg-emerald-500/10 text-emerald-500 font-semibold border border-emerald-500/20">
                  Seller
                </span>
              </div>
            </div>

            <!-- Status Pill & ETA Badge -->
            <div class="flex items-center gap-2.5">
              <div class="flex items-center gap-1 text-[11px] font-semibold text-blue-500 dark:text-blue-400 bg-blue-500/10 px-2.5 py-1 rounded-full border border-blue-500/20">
                <Calendar class="w-3 h-3" />
                <span>ETA: {{ formatDeliveryEstimate(order.delivery_date || order.estimated_delivery) }}</span>
              </div>
              <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider', getStatusBadgeStyle(order.status)]">
                <span class="w-2 h-2 rounded-full animate-ping" :style="{ backgroundColor: getOrderAccentColor(order.status) }"></span>
                {{ formatStatusLabel(order.status) }}
              </span>
            </div>
          </div>

          <!-- ── Interactive Step-by-Step Progress Tracker ── -->
          <div :class="['px-5 py-5 border-b', darkMode ? 'bg-[#0c1220] border-slate-800/80' : 'bg-slate-50/50 border-slate-100']">
            <div class="relative">
              <!-- Stepper Header -->
              <div class="flex items-center justify-between mb-4">
                <span :class="['text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  <Navigation class="w-3.5 h-3.5 text-emerald-500" />
                  <span>Fulfillment Progress</span>
                </span>
                <span :class="['text-xs font-semibold', getStatusTextColor(order.status)]">
                  {{ getCurrentProgressDescription(order.status) }}
                </span>
              </div>

              <!-- Stepper Nodes Grid -->
              <div class="grid grid-cols-5 gap-2 relative">
                <!-- Connecting Line Background -->
                <div class="absolute top-4 left-[10%] right-[10%] h-1 bg-slate-200 dark:bg-slate-700 -z-0"></div>
                <!-- Connecting Line Active Progress -->
                <div
                  class="absolute top-4 left-[10%] h-1 bg-emerald-500 transition-all duration-500 -z-0"
                  :style="{ width: getProgressPercentage(order.status) }"
                ></div>

                <!-- 5 Steps -->
                <div
                  v-for="(step, idx) in fulfillmentSteps"
                  :key="step.key"
                  class="flex flex-col items-center text-center relative z-10"
                >
                  <div
                    :class="[
                      'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all shadow-sm',
                      isStepCompleted(order.status, step.key)
                        ? 'bg-emerald-500 text-white shadow-emerald-500/30'
                        : isStepCurrent(order.status, step.key)
                          ? 'bg-blue-600 text-white ring-4 ring-blue-500/30 shadow-blue-500/40'
                          : darkMode
                            ? 'bg-slate-800 text-slate-500 border border-slate-700'
                            : 'bg-white text-slate-400 border border-slate-300'
                    ]"
                  >
                    <Check v-if="isStepCompleted(order.status, step.key)" class="w-4 h-4 stroke-[3]" />
                    <component v-else :is="step.icon" class="w-4 h-4" />
                  </div>
                  <span
                    :class="[
                      'text-[11px] font-semibold mt-2 hidden sm:block',
                      isStepCompleted(order.status, step.key) || isStepCurrent(order.status, step.key)
                        ? darkMode ? 'text-slate-200 font-bold' : 'text-slate-800 font-bold'
                        : darkMode ? 'text-slate-500' : 'text-slate-400'
                    ]"
                  >
                    {{ step.label }}
                  </span>
                  <span
                    v-if="isStepCurrent(order.status, step.key)"
                    class="text-[9px] text-blue-500 font-bold uppercase tracking-wider hidden sm:block mt-0.5"
                  >
                    Current Stage
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Content Body -->
          <div class="p-5 grid grid-cols-1 lg:grid-cols-12 gap-5">

            <!-- Left: Items Preview (7 cols) -->
            <div class="lg:col-span-7 flex flex-col justify-between">
              <div>
                <h4 :class="['text-xs font-bold uppercase tracking-wider mb-3 flex items-center gap-1.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  <Package class="w-3.5 h-3.5 text-blue-500" />
                  <span>Ordered Commodities ({{ getOrderItems(order).length }} item{{ getOrderItems(order).length > 1 ? 's' : '' }})</span>
                </h4>

                <div v-if="getOrderItems(order).length > 0" class="space-y-2.5">
                  <div
                    v-for="(item, idx) in getOrderItems(order).slice(0, 3)"
                    :key="idx"
                    :class="['flex items-center justify-between p-2.5 rounded-xl border transition-all', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']"
                  >
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg bg-emerald-500/10 text-emerald-500 shrink-0 border border-emerald-500/20">
                        🌾
                      </div>
                      <div>
                        <p :class="['text-xs font-bold m-0', darkMode ? 'text-slate-200' : 'text-slate-800']">
                          {{ item.product_name || item.product?.title || item.title || 'Agricultural Produce' }}
                        </p>
                        <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                          Qty: <span class="font-bold">{{ item.quantity }} {{ item.unit || 'kg' }}</span>
                          <span class="mx-1">•</span>
                          @ ETB {{ formatNumber(item.price || item.unit_price || 0) }} / {{ item.unit || 'kg' }}
                        </p>
                      </div>
                    </div>
                    <div class="text-right">
                      <span class="text-xs font-black text-emerald-500">
                        ETB {{ formatNumber((item.price || item.unit_price || 0) * (item.quantity || 1)) }}
                      </span>
                    </div>
                  </div>

                  <div
                    v-if="getOrderItems(order).length > 3"
                    :class="['text-center py-1.5 text-xs font-medium rounded-lg', darkMode ? 'text-slate-400 bg-slate-800/40' : 'text-slate-500 bg-slate-100']"
                  >
                    +{{ getOrderItems(order).length - 3 }} additional item(s) in this shipment
                  </div>
                </div>

                <div v-else :class="['p-4 rounded-xl border text-center text-xs text-slate-400', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                  Order commodities details recorded in database.
                </div>
              </div>

              <!-- Destination Address Preview -->
              <div :class="['mt-4 p-3 rounded-xl border text-xs flex items-start gap-2.5', darkMode ? 'bg-[#0f172a]/60 border-slate-800 text-slate-300' : 'bg-slate-50 border-slate-200 text-slate-700']">
                <MapPin class="w-4 h-4 text-red-500 shrink-0 mt-0.5" />
                <div class="min-w-0 flex-1">
                  <p class="font-bold m-0 text-[11px] uppercase tracking-wider text-slate-400">Delivery Address</p>
                  <p class="font-medium m-0 truncate text-xs">{{ order.delivery_address || 'Address registered on file' }}</p>
                  <p v-if="order.delivery_region || order.delivery_zone" class="text-[10px] text-slate-400 m-0">
                    {{ order.delivery_region }} {{ order.delivery_zone ? ', ' + order.delivery_zone : '' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Right: Logistics & Payment Breakdown (5 cols) -->
            <div :class="['lg:col-span-5 flex flex-col justify-between p-4 rounded-xl border', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div>
                <h4 :class="['text-xs font-bold uppercase tracking-wider mb-3 flex items-center justify-between', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  <span class="flex items-center gap-1.5">
                    <Truck class="w-3.5 h-3.5 text-purple-500" />
                    <span>Logistics & Courier</span>
                  </span>
                  <span class="text-[10px] text-purple-400 font-bold uppercase bg-purple-500/10 px-2 py-0.5 rounded-full border border-purple-500/20">
                    {{ order.status === 'shipped' ? 'Assigned' : 'Queued' }}
                  </span>
                </h4>

                <!-- Carrier Info Box -->
                <div class="space-y-2 mb-4 text-xs">
                  <div class="flex justify-between items-center">
                    <span :class="darkMode ? 'text-slate-400' : 'text-slate-500'">Carrier:</span>
                    <span class="font-semibold">{{ order.delivery?.carrier_name || order.carrier_name || 'AgriConnect Courier' }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span :class="darkMode ? 'text-slate-400' : 'text-slate-500'">Driver:</span>
                    <span class="font-semibold">{{ order.delivery?.driver_name || order.driver_name || 'Assigned Driver' }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span :class="darkMode ? 'text-slate-400' : 'text-slate-500'">Vehicle:</span>
                    <span class="font-semibold font-mono">{{ order.delivery?.vehicle_plate || order.vehicle_plate || 'In Transit' }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span :class="darkMode ? 'text-slate-400' : 'text-slate-500'">Payment:</span>
                    <span class="font-semibold text-emerald-500 flex items-center gap-1">
                      <ShieldCheck class="w-3.5 h-3.5" /> Escrow Protected
                    </span>
                  </div>
                </div>

                <!-- Financial Summary -->
                <div :class="['pt-3 border-t space-y-1.5 text-xs', darkMode ? 'border-slate-800' : 'border-slate-200']">
                  <div class="flex justify-between">
                    <span :class="darkMode ? 'text-slate-400' : 'text-slate-500'">Subtotal:</span>
                    <span>ETB {{ formatNumber(order.subtotal || (order.total_amount ? order.total_amount * 0.9 : 0)) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span :class="darkMode ? 'text-slate-400' : 'text-slate-500'">Delivery & Handling:</span>
                    <span>ETB {{ formatNumber(order.delivery_fee || 0) }}</span>
                  </div>
                  <div class="flex justify-between items-center pt-2 border-t font-black text-sm">
                    <span>Total Amount:</span>
                    <span class="text-base text-emerald-500">
                      ETB {{ formatNumber(order.total_amount || order.grand_total || 0) }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Quick Action Buttons -->
              <div class="mt-5 grid grid-cols-2 gap-2 pt-3 border-t border-slate-700/40">
                <button
                  @click="openTrackingModal(order)"
                  class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm shadow-blue-600/30 cursor-pointer"
                >
                  <MapPin class="w-3.5 h-3.5" />
                  <span>Live Tracking</span>
                </button>

                <button
                  @click="openDetailsModal(order)"
                  :class="['px-3 py-2 rounded-xl text-xs font-bold border transition-all flex items-center justify-center gap-1.5 cursor-pointer', darkMode ? 'bg-slate-800/80 border-slate-700 text-slate-200 hover:bg-slate-700' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-100 shadow-2xs']"
                >
                  <FileText class="w-3.5 h-3.5" />
                  <span>Full Details</span>
                </button>

                <button
                  @click="openContactModal(order)"
                  :class="['px-3 py-2 rounded-xl text-xs font-semibold border transition-all flex items-center justify-center gap-1.5 cursor-pointer', darkMode ? 'bg-transparent border-slate-700 text-slate-300 hover:bg-slate-800' : 'bg-transparent border-slate-300 text-slate-600 hover:bg-slate-100']"
                >
                  <Phone class="w-3.5 h-3.5" />
                  <span>Contact Seller</span>
                </button>

                <button
                  v-if="canCancelOrder(order.status)"
                  @click="openCancelModal(order)"
                  class="px-3 py-2 rounded-xl text-xs font-semibold border border-red-500/30 text-red-400 bg-red-500/10 hover:bg-red-500/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                >
                  <XCircle class="w-3.5 h-3.5" />
                  <span>Cancel Order</span>
                </button>
                <div v-else class="flex items-center justify-center text-[10px] text-slate-400 italic">
                  Non-cancellable (in transit)
                </div>
              </div>
            </div>

          </div>
        </article>
      </div>

    </main>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- ── MODAL 1: LIVE SHIPMENT TRACKING MODAL ── -->
    <!-- ═══════════════════════════════════════════════ -->
    <div
      v-if="trackingModalOpen && activeOrderForTracking"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-2xl rounded-2xl border shadow-2xl overflow-hidden flex flex-col max-h-[90vh]', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div :class="['flex items-center justify-between p-5 border-b', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <Truck class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold m-0">Live Shipment Tracking</h3>
              <p :class="['text-xs m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Order #{{ activeOrderForTracking.order_number || activeOrderForTracking.id }}
              </p>
            </div>
          </div>
          <button
            @click="trackingModalOpen = false"
            class="p-2 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800 text-sm font-bold cursor-pointer"
          >
            ✕
          </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-5 overflow-y-auto space-y-6">

          <!-- Simulated Live GPS Route Card -->
          <div :class="['rounded-xl p-4 border relative overflow-hidden', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-emerald-50/50 border-emerald-200']">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
              <span class="text-xs font-bold flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                <span>GPS Telemetry Status</span>
              </span>
              <span class="text-xs font-mono font-bold text-slate-400">
                Updated {{ formatDateTime(activeOrderForTracking.updated_at) }}
              </span>
            </div>

            <!-- Route Nodes visual -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-2 text-xs">
              <div class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5">
                  <Warehouse class="w-3.5 h-3.5" />
                </div>
                <div>
                  <p class="font-bold m-0 text-slate-400 text-[10px] uppercase">Origin Farm / Depot</p>
                  <p class="font-semibold m-0">{{ activeOrderForTracking.seller?.name || activeOrderForTracking.seller_name || 'Seller Hub' }}</p>
                </div>
              </div>
              <div class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-500 flex items-center justify-center shrink-0 mt-0.5">
                  <MapPin class="w-3.5 h-3.5" />
                </div>
                <div>
                  <p class="font-bold m-0 text-slate-400 text-[10px] uppercase">Destination Address</p>
                  <p class="font-semibold m-0">{{ activeOrderForTracking.delivery_address || 'Destination on file' }}</p>
                </div>
              </div>
            </div>

            <!-- Vehicle Driver Info -->
            <div :class="['mt-3 pt-3 border-t flex items-center justify-between flex-wrap gap-2 text-xs', darkMode ? 'border-slate-800' : 'border-emerald-200/60']">
              <div>
                <span class="font-bold">Driver: </span>
                <span>{{ activeOrderForTracking.delivery?.driver_name || activeOrderForTracking.driver_name || 'Driver to be assigned' }}</span>
                <span class="font-mono text-slate-400 ml-2">({{ activeOrderForTracking.delivery?.vehicle_plate || activeOrderForTracking.vehicle_plate || 'Vehicle pending' }})</span>
              </div>
              <a
                v-if="activeOrderForTracking.delivery?.driver_phone || activeOrderForTracking.driver_phone"
                :href="'tel:' + (activeOrderForTracking.delivery?.driver_phone || activeOrderForTracking.driver_phone)"
                class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1"
              >
                <Phone class="w-3 h-3" />
                <span>Call Driver</span>
              </a>
            </div>
          </div>

          <!-- Chronological Checkpoint Logs -->
          <div>
            <h4 class="text-xs font-bold uppercase tracking-wider mb-3 text-slate-400">
              Checkpoint Event Log
            </h4>
            <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-300 dark:before:bg-slate-700">
              <div
                v-for="(log, idx) in getTrackingLogs(activeOrderForTracking)"
                :key="idx"
                class="relative"
              >
                <div
                  :class="[
                    'absolute -left-6 top-1 w-3.5 h-3.5 rounded-full border-2',
                    log.completed
                      ? 'bg-emerald-500 border-emerald-500 ring-2 ring-emerald-500/20'
                      : 'bg-slate-300 dark:bg-slate-700 border-slate-400'
                  ]"
                ></div>
                <div class="flex items-baseline justify-between gap-2">
                  <h5 class="text-xs font-bold m-0" :class="log.completed ? (darkMode ? 'text-slate-100' : 'text-slate-900') : 'text-slate-400'">
                    {{ log.title }}
                  </h5>
                  <span class="text-[10px] font-mono text-slate-400">{{ log.time }}</span>
                </div>
                <p class="text-xs text-slate-400 m-0 mt-0.5">{{ log.desc }}</p>
                <p v-if="log.location" class="text-[10px] font-semibold text-emerald-500 m-0 mt-0.5 flex items-center gap-1">
                  <MapPin class="w-3 h-3" /> {{ log.location }}
                </p>
              </div>
            </div>
          </div>

        </div>

        <!-- Modal Footer -->
        <div :class="['p-4 border-t flex justify-end gap-2', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <button
            @click="trackingModalOpen = false"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Close Tracking
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- ── MODAL 2: FULL ORDER DETAILS MODAL ── -->
    <!-- ═══════════════════════════════════════════════ -->
    <div
      v-if="detailsModalOpen && activeOrderForDetails"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-2xl rounded-2xl border shadow-2xl overflow-hidden flex flex-col max-h-[90vh]', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div :class="['flex items-center justify-between p-5 border-b', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <ReceiptText class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold m-0">Order Specification & Invoice</h3>
              <p :class="['text-xs m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Order Reference: #{{ activeOrderForDetails.order_number || activeOrderForDetails.id }}
              </p>
            </div>
          </div>
          <button
            @click="detailsModalOpen = false"
            class="p-2 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800 text-sm font-bold cursor-pointer"
          >
            ✕
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 overflow-y-auto space-y-5 text-xs">
          <!-- Meta Grid -->
          <div :class="['grid grid-cols-2 sm:grid-cols-4 gap-3 p-3 rounded-xl border', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div>
              <p class="text-slate-400 text-[10px] uppercase font-bold m-0">Status</p>
              <p class="font-bold m-0 text-emerald-500 uppercase">{{ activeOrderForDetails.status }}</p>
            </div>
            <div>
              <p class="text-slate-400 text-[10px] uppercase font-bold m-0">Order Date</p>
              <p class="font-semibold m-0">{{ formatDateTime(activeOrderForDetails.created_at) }}</p>
            </div>
            <div>
              <p class="text-slate-400 text-[10px] uppercase font-bold m-0">Payment Type</p>
              <p class="font-semibold m-0">{{ activeOrderForDetails.payment_method || 'Escrow Transfer' }}</p>
            </div>
            <div>
              <p class="text-slate-400 text-[10px] uppercase font-bold m-0">Est. Arrival</p>
              <p class="font-semibold m-0">{{ formatDeliveryEstimate(activeOrderForDetails.delivery_date) }}</p>
            </div>
          </div>

          <!-- Items Table -->
          <div>
            <h4 class="font-bold uppercase tracking-wider text-[11px] mb-2 text-slate-400">
              Line Items Breakdown
            </h4>
            <div :class="['rounded-xl border overflow-hidden', darkMode ? 'border-slate-800' : 'border-slate-200']">
              <table class="w-full text-left border-collapse">
                <thead :class="['text-[10px] font-bold uppercase tracking-wider border-b', darkMode ? 'bg-slate-800/80 border-slate-700 text-slate-300' : 'bg-slate-100 border-slate-200 text-slate-600']">
                  <tr>
                    <th class="p-3">Product Name</th>
                    <th class="p-3 text-center">Unit Price</th>
                    <th class="p-3 text-center">Quantity</th>
                    <th class="p-3 text-right">Subtotal</th>
                  </tr>
                </thead>
                <tbody :class="['divide-y', darkMode ? 'divide-slate-800' : 'divide-slate-200']">
                  <tr v-for="(item, i) in getOrderItems(activeOrderForDetails)" :key="i">
                    <td class="p-3 font-semibold">{{ item.product_name || item.product?.title || 'Crop Item' }}</td>
                    <td class="p-3 text-center">ETB {{ formatNumber(item.price || item.unit_price || 0) }}</td>
                    <td class="p-3 text-center font-bold">{{ item.quantity }} {{ item.unit || 'kg' }}</td>
                    <td class="p-3 text-right font-black text-emerald-500">
                      ETB {{ formatNumber((item.price || item.unit_price || 0) * (item.quantity || 1)) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Total Calculation Summary -->
          <div :class="['p-4 rounded-xl border space-y-2', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div class="flex justify-between">
              <span class="text-slate-400">Produce Subtotal:</span>
              <span class="font-semibold">ETB {{ formatNumber(activeOrderForDetails.subtotal || (activeOrderForDetails.total_amount ? activeOrderForDetails.total_amount * 0.9 : 0)) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-400">Logistics / Delivery:</span>
              <span class="font-semibold">ETB {{ formatNumber(activeOrderForDetails.delivery_fee || 0) }}</span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t font-black text-sm">
              <span>Grand Total:</span>
              <span class="text-base text-emerald-500">
                ETB {{ formatNumber(activeOrderForDetails.total_amount || activeOrderForDetails.grand_total || 0) }}
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
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Done
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- ── MODAL 3: CANCEL ORDER MODAL ── -->
    <!-- ═══════════════════════════════════════════════ -->
    <div
      v-if="cancelModalOpen && activeOrderForCancel"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-md rounded-2xl border shadow-2xl overflow-hidden p-6 space-y-4', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <div class="flex items-center gap-3 text-red-500">
          <div class="w-10 h-10 rounded-xl bg-red-500/10 flex items-center justify-center">
            <AlertTriangle class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-base font-extrabold m-0">Cancel Active Order</h3>
            <p :class="['text-xs m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
              Order #{{ activeOrderForCancel.order_number || activeOrderForCancel.id }}
            </p>
          </div>
        </div>

        <p :class="['text-xs leading-relaxed', darkMode ? 'text-slate-300' : 'text-slate-600']">
          Are you sure you want to cancel this order? Any funds held in escrow will be refunded to your buyer balance.
        </p>

        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
            Cancellation Reason
          </label>
          <select
            v-model="cancelReason"
            :class="['w-full p-2.5 rounded-xl text-xs border outline-none', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-800']"
          >
            <option value="Ordered by mistake">Ordered by mistake</option>
            <option value="Estimated delivery time too long">Estimated delivery time too long</option>
            <option value="Found alternative supplier">Found alternative supplier</option>
            <option value="Product specification change">Product specification change</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="flex justify-end gap-2 pt-3">
          <button
            @click="cancelModalOpen = false"
            :class="['px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
          >
            Keep Order
          </button>
          <button
            @click="executeCancelOrder"
            :disabled="cancelling"
            class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
          >
            <span v-if="cancelling" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span>Confirm Cancellation</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- ── MODAL 4: CONTACT SELLER MODAL ── -->
    <!-- ═══════════════════════════════════════════════ -->
    <div
      v-if="contactModalOpen && activeOrderForContact"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-md rounded-2xl border shadow-2xl overflow-hidden p-6 space-y-4', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <MessageSquare class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold m-0">Contact Seller</h3>
              <p :class="['text-xs m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                {{ activeOrderForContact.seller?.name || activeOrderForContact.seller_name || 'Seller' }}
              </p>
            </div>
          </div>
          <button
            @click="contactModalOpen = false"
            class="text-slate-400 hover:text-slate-200 font-bold cursor-pointer"
          >
            ✕
          </button>
        </div>

        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
            Send Inquiry
          </label>
          <textarea
            v-model="contactMessage"
            rows="3"
            placeholder="Write your message regarding shipment or delivery..."
            :class="['w-full p-3 rounded-xl text-xs border outline-none resize-none', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-200 focus:border-blue-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-blue-500']"
          ></textarea>
        </div>

        <div class="flex justify-end gap-2">
          <button
            @click="contactModalOpen = false"
            :class="['px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
          >
            Cancel
          </button>
          <button
            @click="sendMessageToSeller"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
          >
            <Send class="w-3.5 h-3.5" />
            <span>Send Message</span>
          </button>
        </div>
      </div>
    </div>

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
  Menu, RefreshCw, ListOrdered, Store, Clock, Truck, PackageCheck,
  BadgeDollarSign, ShieldCheck, Search, PackageOpen,
  ShoppingBag, Check, Package, MapPin, User, Calendar, Navigation,
  FileText, Phone, XCircle, AlertTriangle, ReceiptText, Printer,
  Warehouse, MessageSquare, Send
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const buyerStore = useBuyerStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const loading = ref(false)
const refreshing = ref(false)

// Search & Filter State
const searchQuery = ref('')
const activeStatusFilter = ref('')
const sortBy = ref('newest')

// Stepper Step Definitions
const fulfillmentSteps = [
  { key: 'placed', label: 'Order Placed', icon: Clock },
  { key: 'confirmed', label: 'Confirmed', icon: PackageCheck },
  { key: 'packed', label: 'Packed & Ready', icon: Package },
  { key: 'shipped', label: 'In Transit', icon: Truck },
  { key: 'delivered', label: 'Delivered', icon: MapPin }
]

// Modal state
const trackingModalOpen = ref(false)
const activeOrderForTracking = ref<any>(null)

const detailsModalOpen = ref(false)
const activeOrderForDetails = ref<any>(null)

const cancelModalOpen = ref(false)
const activeOrderForCancel = ref<any>(null)
const cancelReason = ref('Ordered by mistake')
const cancelling = ref(false)

const contactModalOpen = ref(false)
const activeOrderForContact = ref<any>(null)
const contactMessage = ref('')

onMounted(async () => {
  themeStore.initializeTheme()
  await loadOrdersData()
})

const loadOrdersData = async () => {
  loading.value = true
  try {
    await buyerStore.fetchOrders()
  } catch (err) {
    console.warn('Could not fetch orders from API:', err)
  } finally {
    loading.value = false
  }
}

const refreshOrders = async () => {
  refreshing.value = true
  try {
    await buyerStore.fetchOrders()
  } catch (err) {
    console.warn('Refresh error:', err)
  } finally {
    setTimeout(() => {
      refreshing.value = false
    }, 500)
  }
}

// Master Raw Orders fetched exclusively from Database / Store
const rawOrders = computed(() => {
  const storeOrders = buyerStore.orders
  let list: any[] = []

  if (Array.isArray(storeOrders)) {
    list = storeOrders
  } else if (storeOrders && Array.isArray((storeOrders as any).data)) {
    list = (storeOrders as any).data
  }

  // Filter for active status: anything that is not delivered or cancelled
  const activeStatuses = ['pending', 'confirmed', 'processing', 'preparing', 'shipped', 'in_transit', 'out_for_delivery']
  return list.filter(o => o && activeStatuses.includes((o.status || '').toLowerCase()))
})

// Statistics computation
const stats = computed(() => {
  const list = rawOrders.value
  const totalActive = list.length
  const inTransit = list.filter(o => ['shipped', 'in_transit', 'out_for_delivery'].includes((o.status || '').toLowerCase())).length
  const processing = list.filter(o => ['confirmed', 'processing', 'preparing'].includes((o.status || '').toLowerCase())).length
  const totalValue = list.reduce((sum, o) => sum + Number(o.total_amount || o.grand_total || 0), 0)

  return { totalActive, inTransit, processing, totalValue }
})

// Filter pills metadata with dynamic counts
const statusFilters = computed(() => [
  { label: 'All Active', value: '', count: rawOrders.value.length },
  {
    label: 'In Transit',
    value: 'shipped',
    count: rawOrders.value.filter(o => ['shipped', 'in_transit', 'out_for_delivery'].includes((o.status || '').toLowerCase())).length
  },
  {
    label: 'Confirmed',
    value: 'confirmed',
    count: rawOrders.value.filter(o => ['confirmed', 'processing', 'preparing'].includes((o.status || '').toLowerCase())).length
  },
  {
    label: 'Pending',
    value: 'pending',
    count: rawOrders.value.filter(o => (o.status || '').toLowerCase() === 'pending').length
  }
])

// Filtered and Sorted Orders
const filteredOrders = computed(() => {
  let list = [...rawOrders.value]

  // Status Filter
  if (activeStatusFilter.value) {
    if (activeStatusFilter.value === 'shipped') {
      list = list.filter(o => ['shipped', 'in_transit', 'out_for_delivery'].includes((o.status || '').toLowerCase()))
    } else if (activeStatusFilter.value === 'confirmed') {
      list = list.filter(o => ['confirmed', 'processing', 'preparing'].includes((o.status || '').toLowerCase()))
    } else {
      list = list.filter(o => (o.status || '').toLowerCase() === activeStatusFilter.value)
    }
  }

  // Search Filter
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(o => {
      const matchOrderNum = (o.order_number || ('ORD-' + o.id)).toLowerCase().includes(q)
      const matchSeller = (o.seller?.name || o.seller_name || o.farmer?.name || o.farmer_name || '').toLowerCase().includes(q)
      const matchItems = getOrderItems(o).some(item =>
        (item.product_name || item.product?.title || '').toLowerCase().includes(q)
      )
      return matchOrderNum || matchSeller || matchItems
    })
  }

  // Sorting
  switch (sortBy.value) {
    case 'oldest':
      list.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
      break
    case 'delivery-soon':
      list.sort((a, b) => new Date(a.delivery_date || 0).getTime() - new Date(b.delivery_date || 0).getTime())
      break
    case 'highest-amount':
      list.sort((a, b) => (b.total_amount || 0) - (a.total_amount || 0))
      break
    case 'lowest-amount':
      list.sort((a, b) => (a.total_amount || 0) - (b.total_amount || 0))
      break
    case 'newest':
    default:
      list.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      break
  }

  return list
})

// Stepper Progress Helpers
const isStepCompleted = (status: string, stepKey: string) => {
  const norm = (status || '').toLowerCase()
  const order: Record<string, number> = {
    pending: 1,
    confirmed: 2,
    processing: 2,
    preparing: 2,
    shipped: 4,
    in_transit: 4,
    out_for_delivery: 4,
    delivered: 5
  }
  const stepIndices: Record<string, number> = {
    placed: 1,
    confirmed: 2,
    packed: 3,
    shipped: 4,
    delivered: 5
  }
  return (order[norm] || 1) > stepIndices[stepKey]
}

const isStepCurrent = (status: string, stepKey: string) => {
  const norm = (status || '').toLowerCase()
  if (stepKey === 'placed' && norm === 'pending') return true
  if (stepKey === 'confirmed' && (norm === 'confirmed')) return true
  if (stepKey === 'packed' && (norm === 'processing' || norm === 'preparing')) return true
  if (stepKey === 'shipped' && (norm === 'shipped' || norm === 'in_transit' || norm === 'out_for_delivery')) return true
  if (stepKey === 'delivered' && norm === 'delivered') return true
  return false
}

const getProgressPercentage = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'pending') return '0%'
  if (norm === 'confirmed') return '25%'
  if (norm === 'processing' || norm === 'preparing') return '50%'
  if (norm === 'shipped' || norm === 'in_transit') return '75%'
  if (norm === 'out_for_delivery') return '88%'
  if (norm === 'delivered') return '100%'
  return '25%'
}

const getCurrentProgressDescription = (status: string) => {
  const norm = (status || '').toLowerCase()
  switch (norm) {
    case 'pending': return 'Awaiting Farmer Confirmation'
    case 'confirmed': return 'Order Accepted – Preparation Underway'
    case 'processing':
    case 'preparing': return 'Packed and awaiting courier pickup'
    case 'shipped':
    case 'in_transit': return 'In Transit – Driver dispatched to destination'
    case 'out_for_delivery': return 'Out for delivery to your address'
    default: return 'Fulfillment in progress'
  }
}

// Helpers
const getOrderItems = (order: any): any[] => {
  if (Array.isArray(order?.items)) return order.items
  if (Array.isArray(order?.order_items)) return order.order_items
  if (Array.isArray(order?.orderItems)) return order.orderItems
  return []
}

const canCancelOrder = (status: string) => {
  const norm = (status || '').toLowerCase()
  return norm === 'pending' || norm === 'confirmed'
}

const getOrderAccentColor = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'pending') return '#f59e0b' // Amber
  if (norm === 'confirmed') return '#3b82f6' // Blue
  if (norm === 'shipped' || norm === 'in_transit') return '#8b5cf6' // Purple
  if (norm === 'out_for_delivery') return '#10b981' // Emerald
  return '#3b82f6'
}

const getStatusBadgeStyle = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'pending') return 'bg-amber-500/15 text-amber-500 border border-amber-500/30'
  if (norm === 'confirmed') return 'bg-blue-500/15 text-blue-500 border border-blue-500/30'
  if (norm === 'shipped' || norm === 'in_transit') return 'bg-purple-500/15 text-purple-400 border border-purple-500/30'
  if (norm === 'out_for_delivery') return 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30'
  return 'bg-slate-500/15 text-slate-400 border border-slate-500/30'
}

const getStatusTextColor = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'pending') return 'text-amber-500'
  if (norm === 'confirmed') return 'text-blue-500'
  if (norm === 'shipped' || norm === 'in_transit') return 'text-purple-400'
  if (norm === 'out_for_delivery') return 'text-emerald-500'
  return 'text-blue-500'
}

const formatStatusLabel = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'pending') return 'Pending Acceptance'
  if (norm === 'confirmed') return 'Confirmed / Packing'
  if (norm === 'shipped' || norm === 'in_transit') return 'In Transit'
  if (norm === 'out_for_delivery') return 'Out for Delivery'
  return status || 'Active'
}

const formatDateTime = (dateStr: string | null) => {
  if (!dateStr) return 'N/A'
  try {
    return new Date(dateStr).toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateStr
  }
}

const formatDeliveryEstimate = (dateStr: string | null) => {
  if (!dateStr) return 'Pending Schedule'
  try {
    const d = new Date(dateStr)
    const today = new Date()
    const tomorrow = new Date(today)
    tomorrow.setDate(tomorrow.getDate() + 1)

    if (d.toDateString() === today.toDateString()) {
      return `Today by ${d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}`
    }
    if (d.toDateString() === tomorrow.toDateString()) {
      return `Tomorrow by ${d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}`
    }
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
  } catch {
    return dateStr
  }
}

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num || 0)
}

const resetFilters = () => {
  searchQuery.value = ''
  activeStatusFilter.value = ''
  sortBy.value = 'newest'
}

// Modal Handlers
const openTrackingModal = (order: any) => {
  activeOrderForTracking.value = order
  trackingModalOpen.value = true
}

const openDetailsModal = (order: any) => {
  activeOrderForDetails.value = order
  detailsModalOpen.value = true
}

const openCancelModal = (order: any) => {
  activeOrderForCancel.value = order
  cancelModalOpen.value = true
}

const openContactModal = (order: any) => {
  activeOrderForContact.value = order
  contactMessage.value = ''
  contactModalOpen.value = true
}

const executeCancelOrder = async () => {
  if (!activeOrderForCancel.value) return
  cancelling.value = true
  try {
    const id = activeOrderForCancel.value.id
    await buyerStore.cancelOrder(id)
    await buyerStore.fetchOrders()
    cancelModalOpen.value = false
    alert(`Order #${id} has been cancelled. Funds held in escrow have been refunded.`)
  } catch (err: any) {
    console.error('Failed to cancel order:', err)
    alert(err?.response?.data?.message || 'Failed to cancel order.')
  } finally {
    cancelling.value = false
  }
}

const sendMessageToSeller = () => {
  alert(`Inquiry sent to seller.`)
  contactModalOpen.value = false
}

const printReceipt = () => {
  window.print()
}

const getTrackingLogs = (order: any) => {
  const norm = (order.status || '').toLowerCase()
  return [
    {
      title: 'Order Placed & Escrow Created',
      time: formatDateTime(order.created_at),
      desc: 'Order logged into system, buyer payment deposited securely into escrow account.',
      location: 'AgriConnect Platform Engine',
      completed: true
    },
    {
      title: 'Farmer Confirmation & Quality Audit',
      time: formatDateTime(order.updated_at || order.created_at),
      desc: 'Produce harvested, inspected for moisture and grade standard, packaged for transport.',
      location: order.seller?.name || order.seller_name || 'Regional Farm Hub',
      completed: norm !== 'pending'
    },
    {
      title: 'Transporter Assigned & Cargo Picked Up',
      time: order.shipped_at ? formatDateTime(order.shipped_at) : 'Logistics Queue',
      desc: `Transporter ${order.delivery?.carrier_name || order.carrier_name || 'AgriConnect Courier'}, vehicle ${order.delivery?.vehicle_plate || order.vehicle_plate || 'Assigned'}, driver ${order.delivery?.driver_name || order.driver_name || 'Assigned'}.`,
      location: order.delivery_region || 'Logistics Hub',
      completed: ['shipped', 'in_transit', 'out_for_delivery', 'delivered'].includes(norm)
    },
    {
      title: 'In Transit along Main Logistics Corridor',
      time: ['shipped', 'in_transit', 'out_for_delivery'].includes(norm) ? 'In Progress' : 'Pending',
      desc: 'En route to recipient delivery address.',
      location: order.delivery_zone || 'En Route',
      completed: ['shipped', 'in_transit', 'out_for_delivery', 'delivered'].includes(norm)
    },
    {
      title: 'Final Delivery & Acceptance Sign-off',
      time: order.delivered_at ? formatDateTime(order.delivered_at) : 'Pending Delivery',
      desc: 'Buyer signature on delivery receipt will release escrow funds to farmer.',
      location: order.delivery_address || 'Destination Address',
      completed: norm === 'delivered'
    }
  ]
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
/* Responsive scrollbar hide */
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
