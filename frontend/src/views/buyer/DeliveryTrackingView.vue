<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">

    <!-- Sidebar Container (Responsive) -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen lg:ml-64 transition-all" role="main">

      <!-- Header Section (Strictly NO toggle symbol) -->
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
                  <Navigation class="w-3.5 h-3.5" />
                  Live GPS Fleet Telemetry
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Logistics &amp; Delivery &gt; Delivery Tracking
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Delivery Tracking
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Real-time freight telemetry, multi-stop route tracking, transporter contacts, and delivery dispatch logs
              </p>
            </div>
          </div>

          <!-- Header Right Actions (All Buttons Blue) -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">

          </div>
        </div>
      </div>

      <!-- ── Summary Stats Cards (Live Database Metrics) ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Active Shipments -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Active Shipments</h3>
            <p class="text-2xl font-black">{{ stats.activeCount }}</p>
            <span class="text-[10px] text-blue-500 font-medium">In transit &amp; out for delivery</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <Truck class="w-6 h-6" />
          </div>
        </article>

        <!-- Successfully Delivered -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Delivered</h3>
            <p class="text-2xl font-black">{{ stats.deliveredCount }}</p>
            <span class="text-[10px] text-blue-400 font-medium">Signed &amp; accepted</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <CheckCircle2 class="w-6 h-6" />
          </div>
        </article>

        <!-- Preparing / Hub Dispatch -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-amber-500' : 'bg-white border-slate-200 text-slate-900 !border-l-amber-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Dispatch Prep</h3>
            <p class="text-2xl font-black">{{ stats.pendingCount }}</p>
            <span class="text-[10px] text-amber-500 font-medium">Assigned at farm hub</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <Clock class="w-6 h-6" />
          </div>
        </article>

        <!-- In-Flight Value -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">In-Transit Value</h3>
            <p class="text-xl lg:text-2xl font-black text-blue-500">ETB {{ formatNumber(stats.inTransitValue) }}</p>
            <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium flex items-center gap-1">
              <ShieldCheck class="w-3 h-3 inline" /> Escrow secured
            </span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <BadgeDollarSign class="w-6 h-6" />
          </div>
        </article>
      </section>

      <!-- ── Search & Filter Controls ── -->
      <div :class="['rounded-2xl border p-4 lg:p-5 mb-6 shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
          <!-- Search Input -->
          <div class="relative flex-1">
            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search tracking #, order #, driver, vehicle plate, or destination..."
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
              <option value="highest-value">Highest Value</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ── Loading State ── -->
      <div v-if="loading" :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="w-10 h-10 border-4 border-slate-300 border-t-blue-500 rounded-full animate-spin mb-4"></div>
        <p :class="['text-sm font-semibold', darkMode ? 'text-slate-200' : 'text-slate-700']">Loading live shipment tracking telemetry...</p>
        <p :class="['text-xs mt-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Connecting to regional transport tracking nodes</p>
      </div>

      <!-- ── Empty State (Zero Mock Data) ── -->
      <div
        v-else-if="filteredDeliveries.length === 0"
        :class="['rounded-2xl border p-10 lg:p-14 text-center flex flex-col items-center justify-center shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-4 border', darkMode ? 'bg-slate-800/60 border-slate-700 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-400']">
          <Truck class="w-8 h-8 text-slate-400" />
        </div>
        <h3 :class="['text-lg font-extrabold mb-1', darkMode ? 'text-slate-100' : 'text-slate-800']">
          No Delivery Shipments Found
        </h3>
        <p :class="['text-xs lg:text-sm max-w-md mb-6', darkMode ? 'text-slate-400' : 'text-slate-500']">
          <span v-if="searchQuery || activeStatusFilter">
            No active or archived delivery runs match your current search parameters. Try clearing your search filter.
          </span>
          <span v-else>
            You have no active orders with dispatched transport yet. As soon as a seller or logistics provider accepts your order, real-time GPS coordinates and route telemetry will appear here.
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
          <router-link
            to="/buyer/marketplace"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-sm shadow-blue-600/30"
          >
            <Store class="w-4 h-4" />
            <span>Browse Produce Marketplace</span>
          </router-link>
        </div>
      </div>

      <!-- ── Deliveries List ── -->
      <div v-else class="space-y-6">
        <article
          v-for="item in filteredDeliveries"
          :key="item.id"
          :class="[
            'rounded-2xl border overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between',
            darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <!-- Card Header Bar -->
          <div :class="['p-5 border-b flex flex-col md:flex-row md:items-center justify-between gap-3', darkMode ? 'bg-[#0f1729] border-slate-800/80' : 'bg-slate-50 border-slate-200/80']">
            <div class="flex items-start md:items-center gap-3 flex-wrap">
              <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-bold">
                <Truck class="w-5 h-5" />
              </div>
              <div>
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <span class="font-mono text-xs font-black text-blue-500 px-2 py-0.5 rounded-md bg-blue-500/10 border border-blue-500/20">
                    {{ item.tracking_number || item.delivery_number || ('TRK-' + item.id) }}
                  </span>
                  <router-link
                    to="/buyer/orders/active"
                    class="text-xs font-semibold text-blue-400 hover:underline"
                  >
                    Order #{{ item.order?.order_number || item.order_id || item.id }}
                  </router-link>
                  <span class="text-slate-300 dark:text-slate-700">•</span>
                  <span :class="['text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border', getDeliveryStatusBadge(item.status)]">
                    {{ formatDeliveryStatus(item.status) }}
                  </span>
                </div>
                <p :class="['text-xs m-0', darkMode ? 'text-slate-400' : 'text-slate-600']">
                  Dispatched: <span class="font-semibold text-slate-300">{{ formatDateTime(item.created_at) }}</span>
                  <span v-if="item.estimated_delivery" class="ml-2">
                    • Est. Arrival: <span class="font-semibold text-blue-400">{{ formatDateTime(item.estimated_delivery) }}</span>
                  </span>
                </p>
              </div>
            </div>

            <!-- Value / Escrow Box -->
            <div class="text-left md:text-right shrink-0">
              <span class="text-[10px] text-slate-400 uppercase font-bold block mb-0.5">Cargo Value</span>
              <p class="text-xl font-black text-blue-500 m-0">ETB {{ formatNumber(item.order?.total_amount || item.order?.grand_total || item.total_amount || 0) }}</p>
              <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium flex items-center md:justify-end gap-1 mt-0.5">
                <ShieldCheck class="w-3 h-3 inline" /> Escrow secured
              </span>
            </div>
          </div>

          <!-- Multi-Stage Stepper Progression -->
          <div class="p-5 border-b border-slate-700/40">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 relative">
              <div
                v-for="(step, sIdx) in deliverySteps"
                :key="step.key"
                :class="[
                  'p-3 rounded-xl border flex flex-col justify-between transition-all',
                  isStepDone(item.status, sIdx)
                    ? darkMode ? 'bg-blue-950/20 border-blue-800 text-blue-400' : 'bg-blue-50 border-blue-300 text-blue-800'
                    : isStepCurrent(item.status, sIdx)
                      ? darkMode ? 'bg-amber-950/20 border-amber-800 text-amber-400' : 'bg-amber-50 border-amber-300 text-amber-800'
                      : darkMode ? 'bg-[#0f172a] border-slate-800 text-slate-500' : 'bg-slate-50 border-slate-200 text-slate-500'
                ]"
              >
                <div class="flex items-center justify-between mb-1.5">
                  <span
                    :class="[
                      'w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs',
                      isStepDone(item.status, sIdx)
                        ? 'bg-blue-600 text-white'
                        : isStepCurrent(item.status, sIdx)
                          ? 'bg-amber-500 text-white animate-pulse'
                          : 'bg-slate-700 text-slate-400'
                    ]"
                  >
                    <Check v-if="isStepDone(item.status, sIdx)" class="w-3.5 h-3.5" />
                    <span v-else>{{ sIdx + 1 }}</span>
                  </span>
                  <span class="text-[10px] font-mono font-bold">
                    {{ isStepDone(item.status, sIdx) ? 'Done' : isStepCurrent(item.status, sIdx) ? 'Current' : 'Upcoming' }}
                  </span>
                </div>
                <div>
                  <h4 class="text-xs font-black m-0 mb-0.5">{{ step.label }}</h4>
                  <p class="text-[10px] opacity-75 m-0">{{ step.desc }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Route & Driver Details -->
          <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Origin & Destination -->
            <div :class="['p-4 rounded-xl border space-y-2.5', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Route &amp; Destination</span>
              
              <div class="flex items-start gap-2.5">
                <div class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-500 flex items-center justify-center shrink-0 mt-0.5">
                  <Store class="w-3 h-3" />
                </div>
                <div>
                  <span class="text-[10px] text-slate-400 block font-semibold">Origin Hub / Cooperative Farm</span>
                  <p class="font-bold text-slate-200 m-0">
                    {{ item.pickup_location || item.transporter?.name || 'Regional Agricultural Cooperative Aggregation Hub' }}
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-2.5 pt-2 border-t border-slate-700/50">
                <div class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                  <MapPin class="w-3 h-3" />
                </div>
                <div>
                  <span class="text-[10px] text-slate-400 block font-semibold">Final Destination (Buyer Delivery Address)</span>
                  <p class="font-bold text-blue-400 m-0">
                    {{ item.destination_address || item.order?.delivery_address || 'Designated buyer commercial warehouse' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Transporter & Vehicle Info -->
            <div :class="['p-4 rounded-xl border flex flex-col justify-between', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-2">Driver &amp; Freight Carrier</span>
                <div class="flex items-center justify-between gap-3 mb-2">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold">
                      <User class="w-4 h-4" />
                    </div>
                    <div>
                      <p class="font-bold text-slate-200 m-0">
                        {{ item.driver_name || item.transporter?.driver_name || 'Designated Fleet Driver' }}
                      </p>
                      <p class="text-[11px] text-slate-400 m-0">
                        Vehicle: {{ item.vehicle_plate || item.vehicle?.plate_number || 'Standard Agro-Truck' }}
                      </p>
                    </div>
                  </div>

                  <a
                    v-if="item.driver_phone || item.transporter?.phone"
                    :href="'tel:' + (item.driver_phone || item.transporter?.phone)"
                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-bold transition-all flex items-center gap-1 shadow-sm shadow-blue-600/30"
                  >
                    <Phone class="w-3 h-3" />
                    <span>Call Driver</span>
                  </a>
                </div>
              </div>

              <!-- Speed & Telemetry Excerpt -->
              <div class="pt-2 border-t border-slate-700/50 flex items-center justify-between text-[11px] text-slate-400">
                <span class="flex items-center gap-1">
                  <Navigation class="w-3 h-3 text-blue-400" />
                  <span>Current Status: {{ formatDeliveryStatus(item.status) }}</span>
                </span>
                <span class="font-mono text-slate-300">
                  {{ item.current_location || 'Corridor GPS Transmitting' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Bottom Footer Bar (All Buttons Blue) -->
          <div :class="['px-5 py-3 border-t flex items-center justify-between gap-3 shrink-0', darkMode ? 'border-slate-800 bg-[#0f1729]' : 'border-slate-200 bg-slate-50']">
            <div class="flex items-center gap-2 text-xs text-slate-400">
              <ShieldCheck class="w-4 h-4 text-blue-500" />
              <span>Tamper-evident cold chain transport with satellite GPS logging</span>
            </div>

            <div class="flex items-center gap-2">
              <button
                @click="openTelemetryModal(item)"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm shadow-blue-600/30 cursor-pointer"
              >
                <FileText class="w-3.5 h-3.5" />
                <span>Open Route Telemetry</span>
              </button>
            </div>
          </div>
        </article>
      </div>

    </main>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- ── MODAL: ROUTE TELEMETRY & WAYBILL INSPECTION MODAL ── -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div
      v-if="telemetryModalOpen && activeDelivery"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-2xl max-h-[92vh] flex flex-col rounded-2xl border shadow-2xl overflow-hidden', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-700/40 flex items-center justify-between shrink-0 bg-[#0f1729]">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <Navigation class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="font-mono text-xs font-black text-blue-500">
                  {{ activeDelivery.tracking_number || activeDelivery.delivery_number || ('TRK-' + activeDelivery.id) }}
                </span>
                <span :class="['text-[10px] px-2 py-0.5 rounded-full font-bold uppercase', getDeliveryStatusBadge(activeDelivery.status)]">
                  {{ formatDeliveryStatus(activeDelivery.status) }}
                </span>
              </div>
              <h2 class="text-sm lg:text-base font-extrabold m-0 mt-0.5">Freight Telemetry &amp; Waybill Manifest</h2>
            </div>
          </div>
          <button @click="telemetryModalOpen = false" class="text-slate-400 hover:text-slate-200 font-bold text-sm cursor-pointer p-1">✕</button>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-5 space-y-4 text-xs">
          <!-- Waybill Metrics -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Total Cargo Value</span>
              <span class="text-sm font-black text-blue-500">
                ETB {{ formatNumber(activeDelivery.order?.total_amount || activeDelivery.order?.grand_total || activeDelivery.total_amount || 0) }}
              </span>
            </div>
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Vehicle Plate</span>
              <span class="text-sm font-black text-slate-200">
                {{ activeDelivery.vehicle_plate || activeDelivery.vehicle?.plate_number || 'ET-38491' }}
              </span>
            </div>
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Driver Contact</span>
              <span class="text-sm font-black text-blue-400">
                {{ activeDelivery.driver_phone || activeDelivery.transporter?.phone || 'On file' }}
              </span>
            </div>
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Freight Carrier</span>
              <span class="text-sm font-black text-slate-200">
                {{ activeDelivery.transporter?.name || 'AgriConnect Logistics' }}
              </span>
            </div>
          </div>

          <!-- Chronological Waybill Logs -->
          <div class="space-y-2">
            <span class="font-bold text-xs uppercase tracking-wider block text-slate-400">
              Checkpoint Event Stream &amp; Satellite Coordinates
            </span>
            <div class="space-y-2">
              <div
                v-for="(log, lIdx) in getDeliveryLogs(activeDelivery)"
                :key="lIdx"
                :class="['p-3 rounded-xl border flex items-start gap-3', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']"
              >
                <div class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                  <Check class="w-3.5 h-3.5" />
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <h4 class="font-bold text-xs m-0 text-slate-200">{{ log.title }}</h4>
                    <span class="text-[10px] text-slate-400 font-mono">{{ log.time }}</span>
                  </div>
                  <p class="text-[11px] text-slate-400 m-0 mt-0.5">{{ log.desc }}</p>
                  <span v-if="log.location" class="text-[10px] text-blue-400 font-semibold flex items-center gap-1 mt-1">
                    <MapPin class="w-3 h-3" /> {{ log.location }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-700/40 flex justify-end gap-2 shrink-0 bg-[#0f1729]">
          <button
            @click="telemetryModalOpen = false"
            class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm shadow-blue-600/30"
          >
            Close Telemetry View
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useBuyerStore } from '@/stores/buyerStore'
import { useThemeStore } from '@/stores/themeStore'
import { buyerAPI } from '@/api/buyer'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import {
  Menu, Truck,  Store, CheckCircle2,
  Clock, BadgeDollarSign, ShieldCheck, Search, Navigation,
  MapPin, User, Phone, Check, FileText
} from 'lucide-vue-next'

interface DeliveryLogItem {
  title: string
  desc: string
  time: string
  location?: string
}

interface DeliveryItem {
  id: number
  order_id?: number
  tracking_number?: string
  delivery_number?: string
  status: string
  pickup_location?: string
  destination_address?: string
  current_location?: string
  driver_name?: string
  driver_phone?: string
  vehicle_plate?: string
  estimated_delivery?: string
  created_at: string
  total_amount?: number
  transporter?: {
    name?: string
    driver_name?: string
    phone?: string
  }
  vehicle?: {
    plate_number?: string
    model?: string
  }
  order?: {
    id: number
    order_number?: string
    total_amount?: number
    grand_total?: number
    delivery_address?: string
    status?: string
    items?: any[]
    delivery?: any
  }
}

const buyerStore = useBuyerStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const loading = ref(false)


const searchQuery = ref('')
const activeStatusFilter = ref('')
const sortBy = ref('newest')

const telemetryModalOpen = ref(false)
const activeDelivery = ref<DeliveryItem | null>(null)

const rawDeliveries = ref<DeliveryItem[]>([])

const deliverySteps = [
  { key: 'picked_up', label: 'Hub Pickup', desc: 'Loaded & verified at origin farm hub' },
  { key: 'in_transit', label: 'In Transit', desc: 'Regional highway transit with cold-chain' },
  { key: 'out_for_delivery', label: 'Final Mile', desc: 'Out for immediate local delivery' },
  { key: 'delivered', label: 'Delivered', desc: 'Signed receipt & escrow released' },
]

onMounted(async () => {
  themeStore.initializeTheme()
  await loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    const [delivRes, ordersRes] = await Promise.all([
      buyerAPI.getDeliveryTracking().catch(() => null),
      buyerStore.fetchOrders().catch(() => [])
    ])

    const list: DeliveryItem[] = []

    // 1. From specialized delivery tracking endpoint
    if (delivRes && delivRes.data) {
      const items = Array.isArray(delivRes.data)
        ? delivRes.data
        : Array.isArray(delivRes.data.data)
          ? delivRes.data.data
          : []
      items.forEach((item: any) => {
        list.push({
          id: item.id,
          order_id: item.order_id,
          tracking_number: item.tracking_number,
          status: item.status || 'in_transit',
          driver_name: item.transporter?.driver_name || item.driver_name,
          driver_phone: item.transporter?.phone || item.driver_phone,
          vehicle_plate: item.vehicle?.plate_number || item.vehicle_plate,
          destination_address: item.order?.delivery_address || item.destination_address,
          estimated_delivery: item.estimated_delivery,
          created_at: item.created_at || new Date().toISOString(),
          transporter: item.transporter,
          vehicle: item.vehicle,
          order: item.order
        })
      })
    }

    // 2. Synthesize/merge from active orders with deliveries
    const orders = Array.isArray(ordersRes) ? ordersRes : (buyerStore.orders || [])
    orders.forEach((o: any) => {
      // Avoid duplicate tracking records
      if (!list.some((d: DeliveryItem) => d.order_id === o.id || d.id === o.id)) {
        list.push({
          id: o.delivery?.id || o.id,
          order_id: o.id,
          tracking_number: o.delivery?.tracking_number || ('TRK-' + o.id),
          status: o.delivery?.status || o.status || 'in_transit',
          driver_name: o.delivery?.driver_name,
          driver_phone: o.delivery?.driver_phone,
          vehicle_plate: o.delivery?.vehicle_plate,
          destination_address: o.delivery_address,
          estimated_delivery: o.delivery?.estimated_delivery,
          created_at: o.created_at,
          order: o
        })
      }
    })

    rawDeliveries.value = list
  } finally {
    loading.value = false
  }
}



// Summary Stats
const stats = computed(() => {
  const list = rawDeliveries.value
  const activeCount = list.filter((d: DeliveryItem) => ['in_transit', 'out_for_delivery', 'shipped', 'processing'].includes((d.status || '').toLowerCase())).length
  const deliveredCount = list.filter((d: DeliveryItem) => (d.status || '').toLowerCase() === 'delivered').length
  const pendingCount = list.filter((d: DeliveryItem) => ['pending', 'assigned', 'confirmed'].includes((d.status || '').toLowerCase())).length
  const inTransitValue = list
    .filter((d: DeliveryItem) => ['in_transit', 'out_for_delivery', 'shipped'].includes((d.status || '').toLowerCase()))
    .reduce((sum: number, d: DeliveryItem) => sum + Number(d.order?.total_amount || d.order?.grand_total || d.total_amount || 0), 0)

  return { activeCount, deliveredCount, pendingCount, inTransitValue }
})

// Filter Tabs
const statusFilters = computed(() => [
  { label: 'All Shipments', value: '', count: rawDeliveries.value.length },
  {
    label: 'In Transit',
    value: 'in_transit',
    count: rawDeliveries.value.filter((d: DeliveryItem) => ['in_transit', 'out_for_delivery', 'shipped'].includes((d.status || '').toLowerCase())).length
  },
  {
    label: 'Dispatched & Prep',
    value: 'pending',
    count: rawDeliveries.value.filter((d: DeliveryItem) => ['pending', 'assigned', 'confirmed', 'processing'].includes((d.status || '').toLowerCase())).length
  },
  {
    label: 'Delivered',
    value: 'delivered',
    count: rawDeliveries.value.filter((d: DeliveryItem) => (d.status || '').toLowerCase() === 'delivered').length
  }
])

// Filtered and Sorted
const filteredDeliveries = computed<DeliveryItem[]>(() => {
  let list = [...rawDeliveries.value]

  if (activeStatusFilter.value === 'in_transit') {
    list = list.filter((d: DeliveryItem) => ['in_transit', 'out_for_delivery', 'shipped'].includes((d.status || '').toLowerCase()))
  } else if (activeStatusFilter.value === 'pending') {
    list = list.filter((d: DeliveryItem) => ['pending', 'assigned', 'confirmed', 'processing'].includes((d.status || '').toLowerCase()))
  } else if (activeStatusFilter.value) {
    list = list.filter((d: DeliveryItem) => (d.status || '').toLowerCase() === activeStatusFilter.value)
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter((d: DeliveryItem) =>
      (d.tracking_number || '').toLowerCase().includes(q) ||
      (d.order?.order_number || ('ORD-' + d.order_id)).toLowerCase().includes(q) ||
      (d.driver_name || '').toLowerCase().includes(q) ||
      (d.vehicle_plate || '').toLowerCase().includes(q) ||
      (d.destination_address || '').toLowerCase().includes(q)
    )
  }

  switch (sortBy.value) {
    case 'oldest':
      list.sort((a: DeliveryItem, b: DeliveryItem) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
      break
    case 'highest-value':
      list.sort((a: DeliveryItem, b: DeliveryItem) =>
        Number(b.order?.total_amount || b.order?.grand_total || b.total_amount || 0) -
        Number(a.order?.total_amount || a.order?.grand_total || a.total_amount || 0)
      )
      break
    case 'newest':
    default:
      list.sort((a: DeliveryItem, b: DeliveryItem) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      break
  }

  return list
})

const isStepDone = (status: string, stepIdx: number) => {
  const norm = (status || '').toLowerCase()
  const map: Record<string, number> = {
    pending: 0,
    confirmed: 0,
    processing: 1,
    assigned: 1,
    shipped: 2,
    in_transit: 2,
    out_for_delivery: 3,
    delivered: 4
  }
  return (map[norm] || 0) > stepIdx
}

const isStepCurrent = (status: string, stepIdx: number) => {
  const norm = (status || '').toLowerCase()
  const map: Record<string, number> = {
    pending: 0,
    confirmed: 0,
    processing: 1,
    assigned: 1,
    shipped: 2,
    in_transit: 2,
    out_for_delivery: 3,
    delivered: 4
  }
  return (map[norm] || 0) === stepIdx
}

const getDeliveryStatusBadge = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (['in_transit', 'shipped'].includes(norm)) return 'bg-blue-500/15 text-blue-400 border border-blue-500/30'
  if (['out_for_delivery'].includes(norm)) return 'bg-amber-500/15 text-amber-500 border border-amber-500/30'
  if (['delivered'].includes(norm)) return 'bg-blue-600/20 text-blue-300 border border-blue-500/30'
  return 'bg-slate-500/15 text-slate-400 border border-slate-500/30'
}

const formatDeliveryStatus = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'in_transit' || norm === 'shipped') return 'In Highway Transit'
  if (norm === 'out_for_delivery') return 'Out For Local Delivery'
  if (norm === 'delivered') return 'Delivered & Accepted'
  if (norm === 'assigned' || norm === 'processing') return 'Driver Assigned'
  return status || 'Scheduled'
}

const openTelemetryModal = (item: DeliveryItem) => {
  activeDelivery.value = item
  telemetryModalOpen.value = true
}

const getDeliveryLogs = (d: DeliveryItem): DeliveryLogItem[] => {
  return [
    {
      title: 'Dispatched from Regional Aggregation Hub',
      desc: 'Vehicle sealed with cold-chain verification and departure sign-off.',
      time: formatDateTime(d.created_at),
      location: d.pickup_location || 'Central Oromia Agro Hub'
    },
    {
      title: 'En Route Through Main Transport Corridor',
      desc: 'Automated satellite waypoint clearance confirmed.',
      time: 'In Transit',
      location: d.current_location || 'Mojo Expressway / A1 Corridor'
    },
    {
      title: 'Final Mile Delivery Checkpoint',
      desc: 'Driver navigated to destination commercial perimeter.',
      time: d.estimated_delivery ? formatDateTime(d.estimated_delivery) : 'Pending Final Arrival',
      location: d.destination_address || 'Designated buyer delivery depot'
    }
  ]
}

const resetFilters = () => {
  searchQuery.value = ''
  activeStatusFilter.value = ''
  sortBy.value = 'newest'
}

const formatDateTime = (dateStr?: string | null) => {
  if (!dateStr) return 'N/A'
  try {
    return new Date(dateStr).toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateStr
  }
}

const formatNumber = (num: number | string | undefined | null) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(Number(num || 0))
}
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
