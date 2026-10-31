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
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-500/10 text-blue-500 border border-blue-500/20">
                  <Send class="w-3.5 h-3.5" />
                  Freight Dispatch &amp; Fleet Logistics
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Logistics &amp; Delivery &gt; Transport Coordination
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Transport Coordination
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Book dedicated haulage, manage farm-to-warehouse produce freight, and coordinate transporter fleet dispatches
              </p>
            </div>
          </div>

          <!-- Header Right Actions -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <button
              @click="loadData"
              :disabled="loading"
              :class="['inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-medium border transition-all cursor-pointer', darkMode ? 'bg-slate-800/80 border-slate-700 text-slate-300 hover:bg-slate-700' : 'bg-slate-100 border-slate-300 text-slate-700 hover:bg-slate-200']"
              title="Refresh from Database"
            >
              <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin text-blue-500' : '']" />
              <span class="hidden md:inline">{{ loading ? 'Syncing...' : 'Refresh DB' }}</span>
            </button>
            <button
              @click="openBookingModal"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all transform active:scale-95 cursor-pointer"
            >
              <Plus class="w-4 h-4" />
              <span>Book Freight Transport</span>
            </button>
            <BuyerHeaderNav />
          </div>
        </div>
      </div>

      <!-- ── Summary Stats Cards (Live Database Metrics) ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Dispatches -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Freight Dispatches</h3>
            <p class="text-2xl font-black">{{ stats.total }}</p>
            <span class="text-[10px] text-blue-500 font-medium">{{ stats.total_quantity }} Units Hauled</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <Truck class="w-6 h-6" />
          </div>
        </article>

        <!-- Awaiting Assignment / Pending -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-amber-500' : 'bg-white border-slate-200 text-slate-900 !border-l-amber-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Pending Dispatch</h3>
            <p class="text-2xl font-black text-amber-500">{{ stats.pending }}</p>
            <span class="text-[10px] text-amber-500 font-medium">Awaiting driver assignment</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <Clock class="w-6 h-6" />
          </div>
        </article>

        <!-- In Transit -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-purple-500' : 'bg-white border-slate-200 text-slate-900 !border-l-purple-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">In Transit</h3>
            <p class="text-2xl font-black text-purple-500">{{ stats.in_transit }}</p>
            <span class="text-[10px] text-purple-400 font-medium">Active satellite telemetry</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-purple-500/10 text-purple-400' : 'bg-purple-50 text-purple-600']">
            <Navigation class="w-6 h-6" />
          </div>
        </article>

        <!-- Completed / Delivered -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-emerald-500' : 'bg-white border-slate-200 text-slate-900 !border-l-emerald-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Completed Hauls</h3>
            <p class="text-2xl font-black text-emerald-500">{{ stats.completed }}</p>
            <span class="text-[10px] text-emerald-500 font-medium">Delivered to warehouse</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <CheckCircle2 class="w-6 h-6" />
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
              placeholder="Search by tracking #, product name, origin farm, destination, or transporter..."
              :class="['w-full pl-10 pr-4 py-2.5 rounded-xl text-xs border transition-all outline-none', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-blue-500 placeholder-slate-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-blue-500 placeholder-slate-400']"
            />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
            >
              &times;
            </button>
          </div>

          <!-- Filter Controls -->
          <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <!-- Status Filter -->
            <select
              v-model="statusFilter"
              :class="['px-3 py-2.5 rounded-xl text-xs border font-medium outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending Dispatch</option>
              <option value="assigned">Transporter Assigned</option>
              <option value="in_transit">In Transit</option>
              <option value="completed">Completed / Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>

            <!-- Vehicle Filter -->
            <select
              v-model="vehicleFilter"
              :class="['px-3 py-2.5 rounded-xl text-xs border font-medium outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="">All Vehicles</option>
              <option value="refrigerated">Refrigerated Reefer</option>
              <option value="semi_trailer">Heavy 40ft Semi-Trailer</option>
              <option value="truck">10-Tonne Box Truck</option>
              <option value="isuzu">Medium Cargo Isuzu</option>
              <option value="pickup">Light Commercial Pickup</option>
            </select>

            <!-- View Toggle -->
            <div :class="['flex items-center rounded-xl border p-1', darkMode ? 'bg-[#0f172a] border-slate-700' : 'bg-slate-50 border-slate-300']">
              <button
                @click="viewMode = 'grid'"
                :class="['p-1.5 rounded-lg text-xs transition-all cursor-pointer', viewMode === 'grid' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200']"
                title="Grid View"
              >
                <LayoutGrid class="w-4 h-4" />
              </button>
              <button
                @click="viewMode = 'list'"
                :class="['p-1.5 rounded-lg text-xs transition-all cursor-pointer', viewMode === 'list' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200']"
                title="List View"
              >
                <List class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Loading State ── -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <RefreshCw class="w-8 h-8 text-blue-500 animate-spin mb-3" />
        <p :class="['text-xs font-semibold', darkMode ? 'text-slate-400' : 'text-slate-600']">
          Loading transport bookings and freight fleet telemetry from database...
        </p>
      </div>

      <!-- ── Empty State ── -->
      <div
        v-else-if="filteredRequests.length === 0"
        :class="['rounded-2xl border p-12 text-center shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
      >
        <div class="w-16 h-16 rounded-full bg-blue-500/10 text-blue-500 flex items-center justify-center mx-auto mb-4">
          <Send class="w-8 h-8" />
        </div>
        <h3 :class="['text-lg font-bold mb-1', darkMode ? 'text-slate-100' : 'text-slate-900']">
          {{ transportRequests.length === 0 ? 'No transport bookings in database' : 'No matching transport records found' }}
        </h3>
        <p :class="['text-xs max-w-md mx-auto mb-6', darkMode ? 'text-slate-400' : 'text-slate-500']">
          <span v-if="searchQuery || statusFilter || vehicleFilter">
            No freight coordination requests match your active filters. Try clearing search criteria.
          </span>
          <span v-else>
            You have not booked any transport haulage yet. Book freight transportation to move agricultural produce directly from farms to your registered warehouses.
          </span>
        </p>
        <div class="flex items-center justify-center gap-3">
          <button
            v-if="searchQuery || statusFilter || vehicleFilter"
            @click="clearFilters"
            :class="['px-4 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700']"
          >
            Clear Filters
          </button>
          <button
            @click="openBookingModal"
            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm cursor-pointer"
          >
            + Book Freight Transport
          </button>
        </div>
      </div>

      <!-- ── GRID VIEW ── -->
      <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <div
          v-for="item in filteredRequests"
          :key="item.id"
          :class="['rounded-2xl border p-5 shadow-2xs transition-all relative flex flex-col justify-between group hover:shadow-md',
            item.status === 'in_transit' ? (darkMode ? 'border-purple-500/60 bg-[#161a2d]' : 'border-purple-400 bg-purple-50/20') :
            (darkMode ? 'bg-[#131b2e] border-slate-800 hover:border-slate-700' : 'bg-white border-slate-200 hover:border-slate-300')
          ]"
        >
          <div>
            <!-- Top Badges & Tracking ID -->
            <div class="flex items-start justify-between gap-2 mb-3">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-xs font-bold text-blue-500 bg-blue-500/10 px-2.5 py-1 rounded-lg border border-blue-500/20">
                  {{ item.tracking_number }}
                </span>
                <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border uppercase tracking-wider', getStatusBadgeClass(item.status)]">
                  <span v-if="item.status === 'in_transit'" class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-ping mr-0.5"></span>
                  {{ formatStatus(item.status) }}
                </span>
              </div>

              <!-- Waybill Quick View -->
              <button
                @click="openDetailsModal(item)"
                class="p-1.5 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-500/10 transition-all cursor-pointer"
                title="View Waybill & Route"
              >
                <FileText class="w-4 h-4" />
              </button>
            </div>

            <!-- Cargo / Product Title -->
            <h2 :class="['text-base font-bold tracking-tight mb-1 flex items-center justify-between', darkMode ? 'text-slate-100' : 'text-slate-900']">
              <span>{{ item.product_name }}</span>
              <span class="text-xs font-black text-blue-500">{{ item.quantity }} {{ item.unit }}</span>
            </h2>

            <!-- Special Handling Tag if applicable -->
            <div v-if="item.special_handling" class="mb-3">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                <Snowflake class="w-3 h-3" /> Cold-Chain Handling Required
              </span>
            </div>

            <!-- Route Origin to Destination -->
            <div :class="['rounded-xl p-3 mb-4 border transition-all text-xs space-y-2', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <!-- Origin -->
              <div class="flex items-start gap-2">
                <MapPin class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" />
                <div class="truncate">
                  <span class="text-[10px] uppercase font-bold text-slate-400 block">Origin Farm Hub</span>
                  <span :class="['font-medium truncate block', darkMode ? 'text-slate-200' : 'text-slate-800']">
                    {{ item.pickup_location }}
                  </span>
                </div>
              </div>

              <!-- Connecting Line -->
              <div class="border-l-2 border-dashed border-slate-300 dark:border-slate-700 h-3 ml-1.5"></div>

              <!-- Destination -->
              <div class="flex items-start gap-2">
                <Navigation class="w-3.5 h-3.5 text-blue-500 shrink-0 mt-0.5" />
                <div class="truncate">
                  <span class="text-[10px] uppercase font-bold text-slate-400 block">Delivery Destination</span>
                  <span :class="['font-medium truncate block', darkMode ? 'text-slate-200' : 'text-slate-800']">
                    {{ item.delivery_location }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Carrier & Vehicle Fleet Details -->
            <div class="space-y-1.5 mb-4 text-xs">
              <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1.5 font-medium">
                  <Truck class="w-3.5 h-3.5 text-blue-500" />
                  {{ item.transporter_name }}
                </span>
                <span class="font-mono text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                  {{ item.vehicle_plate }}
                </span>
              </div>
              <div class="flex items-center justify-between text-[11px] text-slate-400">
                <span class="flex items-center gap-1">
                  <Calendar class="w-3 h-3 text-slate-400" />
                  Pickup: {{ item.pickup_date || 'ASAP' }}
                </span>
                <span v-if="item.contact_phone" class="font-mono text-blue-500">
                  <a :href="`tel:${item.contact_phone}`" class="hover:underline flex items-center gap-1">
                    <Phone class="w-3 h-3" /> {{ item.contact_phone }}
                  </a>
                </span>
              </div>
            </div>

            <!-- Freight Cost / Escrow Status -->
            <div v-if="item.estimated_cost" :class="['rounded-xl p-2.5 text-[11px] mb-4 border flex items-center justify-between', darkMode ? 'bg-blue-950/20 border-blue-900/50 text-blue-300' : 'bg-blue-50/60 border-blue-200 text-blue-900']">
              <span class="font-semibold flex items-center gap-1">
                <ShieldCheck class="w-3.5 h-3.5 text-emerald-500" /> Escrow Freight Fee:
              </span>
              <span class="font-black text-xs font-mono">
                ETB {{ formatNumber(item.estimated_cost) }}
              </span>
            </div>
          </div>

          <!-- Bottom Actions Bar -->
          <div :class="['pt-3 border-t flex items-center justify-between gap-2 mt-auto', darkMode ? 'border-slate-800' : 'border-slate-200']">
            <div class="flex items-center gap-2">
              <button
                @click="openDetailsModal(item)"
                :class="['inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700']"
              >
                <FileText class="w-3.5 h-3.5 text-blue-500" />
                <span>Waybill Details</span>
              </button>

              <router-link
                to="/buyer/delivery/tracking"
                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-purple-500/10 text-purple-400 hover:bg-purple-500/20 transition-all"
                title="View live GPS telemetry"
              >
                <Navigation class="w-3.5 h-3.5" />
                <span>Track Live</span>
              </router-link>
            </div>

            <!-- Cancel Button for pending requests -->
            <button
              v-if="item.status === 'pending'"
              @click="cancelBooking(item.id)"
              class="px-2.5 py-1.5 rounded-lg text-xs font-medium text-red-500 hover:bg-red-500/10 transition-all cursor-pointer"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>

      <!-- ── LIST VIEW ── -->
      <div
        v-else
        :class="['rounded-2xl border overflow-hidden shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
      >
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr :class="['border-b uppercase font-bold text-[10px] tracking-wider', darkMode ? 'border-slate-800 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-500']">
                <th class="py-3.5 px-4">Tracking #</th>
                <th class="py-3.5 px-4">Produce Cargo</th>
                <th class="py-3.5 px-4">Origin Hub</th>
                <th class="py-3.5 px-4">Destination</th>
                <th class="py-3.5 px-4">Transporter &amp; Vehicle</th>
                <th class="py-3.5 px-4">Freight Fee</th>
                <th class="py-3.5 px-4">Status</th>
                <th class="py-3.5 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
              <tr
                v-for="item in filteredRequests"
                :key="item.id"
                :class="['transition-colors', darkMode ? 'hover:bg-slate-800/40' : 'hover:bg-slate-50/80']"
              >
                <!-- Tracking # -->
                <td class="py-4 px-4 font-mono font-bold text-blue-500">
                  {{ item.tracking_number }}
                </td>

                <!-- Product & Quantity -->
                <td class="py-4 px-4 font-bold">
                  <span :class="[darkMode ? 'text-slate-100' : 'text-slate-900']">{{ item.product_name }}</span>
                  <span class="text-[11px] font-normal text-slate-400 block">{{ item.quantity }} {{ item.unit }}</span>
                </td>

                <!-- Origin -->
                <td class="py-4 px-4 max-w-[150px] truncate">
                  {{ item.pickup_location }}
                </td>

                <!-- Destination -->
                <td class="py-4 px-4 max-w-[150px] truncate">
                  {{ item.delivery_location }}
                </td>

                <!-- Transporter -->
                <td class="py-4 px-4">
                  <p class="m-0 font-semibold text-slate-700 dark:text-slate-200">{{ item.transporter_name }}</p>
                  <span class="text-[11px] font-mono text-slate-400 block">{{ item.vehicle_plate }}</span>
                </td>

                <!-- Fee -->
                <td class="py-4 px-4 font-mono font-bold">
                  ETB {{ formatNumber(item.estimated_cost) }}
                </td>

                <!-- Status -->
                <td class="py-4 px-4">
                  <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border uppercase', getStatusBadgeClass(item.status)]">
                    {{ formatStatus(item.status) }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="py-4 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      @click="openDetailsModal(item)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-500/10 transition-all cursor-pointer"
                      title="View Waybill Details"
                    >
                      <FileText class="w-4 h-4" />
                    </button>
                    <router-link
                      to="/buyer/delivery/tracking"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-purple-500 hover:bg-purple-500/10 transition-all"
                      title="Track Live"
                    >
                      <Navigation class="w-4 h-4" />
                    </router-link>
                    <button
                      v-if="item.status === 'pending'"
                      @click="cancelBooking(item.id)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-500/10 transition-all cursor-pointer"
                      title="Cancel Booking"
                    >
                      <X class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>

    <!-- ── MODAL: BOOK FREIGHT TRANSPORT ── -->
    <div
      v-if="bookingModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
      @click.self="bookingModalOpen = false"
    >
      <div
        :class="['rounded-2xl border w-full max-w-2xl my-8 overflow-hidden shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div :class="['px-6 py-4 border-b flex items-center justify-between', darkMode ? 'border-slate-800 bg-[#0f172a]' : 'border-slate-200 bg-slate-50']">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <Truck class="w-4 h-4" />
            </div>
            <div>
              <h2 class="text-base font-bold m-0">Book Freight Transport</h2>
              <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Schedule transport haulage directly from origin farm to your registered delivery hub
              </p>
            </div>
          </div>
          <button
            @click="bookingModalOpen = false"
            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="submitBooking" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
          <!-- Row 1: Commodity / Product & Quantity -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Commodity / Produce Name *
              </label>
              <input
                v-model="bookingForm.product_name"
                type="text"
                required
                placeholder="e.g. Teff Grain, Tomatoes, Avocado, Onions"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Quantity &amp; Unit *
              </label>
              <div class="flex items-center gap-1">
                <input
                  v-model.number="bookingForm.quantity"
                  type="number"
                  step="any"
                  required
                  placeholder="e.g. 50"
                  :class="['w-1/2 px-2.5 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
                />
                <select
                  v-model="bookingForm.unit"
                  :class="['w-1/2 px-2 py-2 rounded-xl text-xs border outline-none cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
                >
                  <option value="Quintal">Quintal</option>
                  <option value="Tonnes">Tonnes</option>
                  <option value="Kg">Kg</option>
                  <option value="Crates">Crates</option>
                  <option value="Bags">Bags</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Row 2: Pickup Origin & Destination Delivery Address -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Pickup Origin (Farm / Cooperative Hub) *
              </label>
              <input
                v-model="bookingForm.pickup_location"
                type="text"
                required
                placeholder="e.g. Central Oromia Agro Hub / Arsi Cooperative"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>

            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Delivery Destination *
              </label>
              <!-- Select from saved delivery addresses or input -->
              <select
                v-if="savedAddresses.length > 0"
                v-model="bookingForm.delivery_location"
                required
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all cursor-pointer mb-1.5', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              >
                <option value="" disabled>Select from your registered delivery addresses</option>
                <option
                  v-for="addr in savedAddresses"
                  :key="addr.id"
                  :value="`${addr.title} (${addr.street_address}, ${addr.city})`"
                >
                  {{ addr.title }} — {{ addr.city }} {{ addr.is_default ? '★ (Default)' : '' }}
                </option>
                <option value="custom">-- Enter Custom Destination --</option>
              </select>
              <input
                v-if="bookingForm.delivery_location === 'custom' || savedAddresses.length === 0"
                v-model="customDestination"
                type="text"
                required
                placeholder="e.g. Addis Ababa Central Commercial Warehouse"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
          </div>

          <!-- Row 3: Pickup & Delivery Dates -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Pickup Date *
              </label>
              <input
                v-model="bookingForm.pickup_date"
                type="date"
                required
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Required Delivery Date *
              </label>
              <input
                v-model="bookingForm.delivery_date"
                type="date"
                required
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
          </div>

          <!-- Row 4: Vehicle Type & Estimated Budget -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Required Cargo Vehicle Type *
              </label>
              <select
                v-model="bookingForm.vehicle_type"
                required
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              >
                <option value="Refrigerated Reefer Truck (Cold-Chain)">Refrigerated Reefer Truck (Cold-Chain)</option>
                <option value="Heavy 40ft Semi-Trailer (Bulk Freight)">Heavy 40ft Semi-Trailer (Bulk Freight)</option>
                <option value="10-Tonne Box Truck">10-Tonne Commercial Box Truck</option>
                <option value="Medium Cargo Isuzu NPR">Medium Cargo Isuzu NPR</option>
                <option value="Light Commercial Pickup">Light Commercial Pickup</option>
              </select>
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Estimated Freight Budget (ETB)
              </label>
              <input
                v-model.number="bookingForm.estimated_cost"
                type="number"
                step="any"
                placeholder="e.g. 15000"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all font-mono', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
          </div>

          <!-- Row 5: Destination Contact Person & Phone -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                On-site Destination Contact Person *
              </label>
              <input
                v-model="bookingForm.contact_person"
                type="text"
                required
                placeholder="e.g. Dawit Haile"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Destination Contact Phone *
              </label>
              <input
                v-model="bookingForm.contact_phone"
                type="tel"
                required
                placeholder="+251 91 000 0000"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all font-mono', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
          </div>

          <!-- Special Handling Checkbox -->
          <div :class="['p-3 rounded-xl border flex items-center justify-between', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div class="flex items-center gap-2.5">
              <input
                id="special_handling"
                v-model="bookingForm.special_handling"
                type="checkbox"
                class="w-4 h-4 rounded text-blue-600 focus:ring-0 cursor-pointer"
              />
              <div>
                <label for="special_handling" :class="['text-xs font-bold cursor-pointer block', darkMode ? 'text-slate-200' : 'text-slate-800']">
                  Requires Cold-Chain or Specialized Cargo Handling
                </label>
                <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  For perishable horticultural produce, dairy, or delicate crops requiring temperature control.
                </p>
              </div>
            </div>
            <Snowflake v-if="bookingForm.special_handling" class="w-5 h-5 text-cyan-400" />
          </div>

          <!-- Instructions -->
          <div>
            <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
              Handling &amp; Farm Access Instructions
            </label>
            <textarea
              v-model="bookingForm.handling_instructions"
              rows="2"
              placeholder="e.g. Driver must arrive with tarpaulin covers. Loading assistance available at farm entrance."
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
            ></textarea>
          </div>

          <!-- Footer Actions -->
          <div :class="['pt-4 border-t flex items-center justify-end gap-3', darkMode ? 'border-slate-800' : 'border-slate-200']">
            <button
              type="button"
              @click="bookingModalOpen = false"
              :class="['px-4 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700']"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-5 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all cursor-pointer flex items-center gap-1.5"
            >
              <RefreshCw v-if="saving" class="w-3.5 h-3.5 animate-spin" />
              <span>{{ saving ? 'Booking...' : 'Confirm Transport Booking' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── MODAL: WAYBILL & DISPATCH DETAILS ── -->
    <div
      v-if="detailsModalOpen && selectedItem"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      @click.self="detailsModalOpen = false"
    >
      <div
        :class="['rounded-2xl border w-full max-w-xl overflow-hidden shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <div :class="['px-6 py-4 border-b flex items-center justify-between', darkMode ? 'border-slate-800 bg-[#0f172a]' : 'border-slate-200 bg-slate-50']">
          <div class="flex items-center gap-2">
            <FileText class="w-5 h-5 text-blue-500" />
            <div>
              <h2 class="text-sm font-bold m-0">Freight Waybill: {{ selectedItem.tracking_number }}</h2>
              <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Official produce consignment &amp; transporter bill of lading
              </p>
            </div>
          </div>
          <button
            @click="detailsModalOpen = false"
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="p-6 space-y-4 text-xs">
          <!-- Status Banner -->
          <div :class="['p-3 rounded-xl border flex items-center justify-between', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 block">Dispatch Status</span>
              <span class="font-bold text-sm">{{ formatStatus(selectedItem.status) }}</span>
            </div>
            <span :class="['px-3 py-1 rounded-full text-[11px] font-extrabold uppercase', getStatusBadgeClass(selectedItem.status)]">
              {{ selectedItem.status }}
            </span>
          </div>

          <!-- Cargo & Route -->
          <div class="grid grid-cols-2 gap-3">
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] uppercase font-bold text-slate-400 block">Produce Cargo</span>
              <p class="font-bold m-0 mt-0.5">{{ selectedItem.product_name }}</p>
              <p class="text-[11px] text-blue-500 font-mono m-0">{{ selectedItem.quantity }} {{ selectedItem.unit }}</p>
            </div>
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] uppercase font-bold text-slate-400 block">Assigned Fleet</span>
              <p class="font-bold m-0 mt-0.5">{{ selectedItem.transporter_name }}</p>
              <p class="text-[11px] text-slate-400 font-mono m-0">{{ selectedItem.vehicle_plate }}</p>
            </div>
          </div>

          <!-- Pickup & Destination -->
          <div :class="['p-3 rounded-xl border space-y-2', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 block">Origin Farm Checkpoint</span>
              <p class="font-medium m-0">{{ selectedItem.pickup_location }}</p>
            </div>
            <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
              <span class="text-[10px] uppercase font-bold text-slate-400 block">Destination Delivery Hub</span>
              <p class="font-medium m-0">{{ selectedItem.delivery_location }}</p>
            </div>
          </div>

          <!-- Contact & Handling -->
          <div v-if="selectedItem.handling_instructions" :class="['p-3 rounded-xl border text-[11px]', darkMode ? 'bg-blue-950/20 border-blue-900/50 text-blue-300' : 'bg-blue-50/60 border-blue-200 text-blue-900']">
            <span class="font-bold block mb-0.5">Handling Instructions:</span>
            {{ selectedItem.handling_instructions }}
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-2">
            <router-link
              to="/buyer/delivery/tracking"
              class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white flex items-center gap-1.5 shadow-sm transition-all"
            >
              <Navigation class="w-3.5 h-3.5" />
              <span>Track Live Fleet</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- ── TOAST NOTIFICATION ── -->
    <div
      v-if="toastMessage"
      class="fixed bottom-6 right-6 z-50 flex items-center gap-2 px-4 py-3 rounded-xl bg-slate-900 text-white border border-slate-700 shadow-xl text-xs font-semibold animate-slide-up"
    >
      <CheckCircle2 class="w-4 h-4 text-emerald-400" />
      <span>{{ toastMessage }}</span>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { buyerAPI } from '@/api/buyer'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import BuyerHeaderNav from '@/components/layout/BuyerHeaderNav.vue'
import {
  Menu, Send, Plus, Truck, Navigation, Clock, CheckCircle2, Search,
  LayoutGrid, List, FileText, MapPin, Snowflake, Calendar, Phone,
  ShieldCheck, X, RefreshCw
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)
const sidebarOpen = ref(false)
const loading = ref(false)
const saving = ref(false)

const searchQuery = ref('')
const statusFilter = ref('')
const vehicleFilter = ref('')
const viewMode = ref<'grid' | 'list'>('grid')

// PURE LIVE DATABASE RECORDS
const transportRequests = ref<any[]>([])
const savedAddresses = ref<any[]>([])
const customDestination = ref('')

const stats = ref({
  total: 0,
  pending: 0,
  confirmed: 0,
  in_transit: 0,
  completed: 0,
  total_freight_cost: 0,
  total_quantity: 0
})

const bookingModalOpen = ref(false)
const detailsModalOpen = ref(false)
const selectedItem = ref<any | null>(null)

const toastMessage = ref('')
let toastTimer: any = null

const showToast = (msg: string) => {
  toastMessage.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toastMessage.value = ''
  }, 3500)
}

const getTodayDate = () => new Date().toISOString().split('T')[0]
const getTomorrowDate = () => {
  const d = new Date()
  d.setDate(d.getDate() + 2)
  return d.toISOString().split('T')[0]
}

const bookingForm = ref({
  product_name: '',
  quantity: 50,
  unit: 'Quintal',
  pickup_location: '',
  delivery_location: '',
  pickup_date: getTodayDate(),
  delivery_date: getTomorrowDate(),
  vehicle_type: 'Refrigerated Reefer Truck (Cold-Chain)',
  special_handling: false,
  handling_instructions: '',
  estimated_cost: 0,
  contact_person: '',
  contact_phone: ''
})

const filteredRequests = computed(() => {
  let list = [...transportRequests.value]

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(item =>
      (item.tracking_number && item.tracking_number.toLowerCase().includes(q)) ||
      (item.product_name && item.product_name.toLowerCase().includes(q)) ||
      (item.pickup_location && item.pickup_location.toLowerCase().includes(q)) ||
      (item.delivery_location && item.delivery_location.toLowerCase().includes(q)) ||
      (item.transporter_name && item.transporter_name.toLowerCase().includes(q)) ||
      (item.vehicle_plate && item.vehicle_plate.toLowerCase().includes(q))
    )
  }

  if (statusFilter.value) {
    list = list.filter(item => item.status === statusFilter.value)
  }

  if (vehicleFilter.value) {
    const v = vehicleFilter.value.toLowerCase()
    list = list.filter(item => (item.vehicle_type || '').toLowerCase().includes(v))
  }

  return list
})

onMounted(async () => {
  themeStore.initializeTheme()
  await Promise.all([
    loadData(),
    loadSavedAddresses()
  ])
})

const loadData = async () => {
  loading.value = true
  try {
    const res = await buyerAPI.getTransportCoordination()
    if (res && res.data) {
      transportRequests.value = res.data.data || []
      if (res.data.stats) {
        stats.value = res.data.stats
      }
    }
  } catch (err: any) {
    console.error('Error fetching transport coordination from database:', err)
  } finally {
    loading.value = false
  }
}

const loadSavedAddresses = async () => {
  try {
    const res = await buyerAPI.getDeliveryAddresses().catch(() => null)
    if (res && res.data) {
      savedAddresses.value = res.data.data || res.data || []
    }
  } catch (err) {
    console.error('Error loading saved addresses:', err)
  }
}

const openBookingModal = () => {
  // Pre-fill default delivery location from saved database addresses if available
  const defaultAddr = savedAddresses.value.find(a => a.is_default) || savedAddresses.value[0]

  bookingForm.value = {
    product_name: '',
    quantity: 50,
    unit: 'Quintal',
    pickup_location: '',
    delivery_location: defaultAddr ? `${defaultAddr.title} (${defaultAddr.street_address}, ${defaultAddr.city})` : '',
    pickup_date: getTodayDate(),
    delivery_date: getTomorrowDate(),
    vehicle_type: 'Refrigerated Reefer Truck (Cold-Chain)',
    special_handling: false,
    handling_instructions: '',
    estimated_cost: 12000,
    contact_person: defaultAddr ? defaultAddr.contact_name : (authStore.user?.name || ''),
    contact_phone: defaultAddr ? defaultAddr.contact_phone : (authStore.user?.phone || '+251 91 000 0000')
  }
  bookingModalOpen.value = true
}

const submitBooking = async () => {
  const finalDest = bookingForm.value.delivery_location === 'custom'
    ? customDestination.value
    : bookingForm.value.delivery_location

  if (!bookingForm.value.product_name.trim() || !bookingForm.value.pickup_location.trim() || !finalDest.trim()) {
    alert('Please fill in all required fields.')
    return
  }

  saving.value = true
  try {
    const payload = {
      ...bookingForm.value,
      delivery_location: finalDest
    }
    await buyerAPI.createTransportCoordination(payload)
    showToast('Transport coordination booking saved to database!')
    bookingModalOpen.value = false
    await loadData()
  } catch (err: any) {
    console.error('Failed to create transport booking in database:', err)
    alert(err?.response?.data?.message || 'Failed to book transport.')
  } finally {
    saving.value = false
  }
}

const cancelBooking = async (id: number | string) => {
  if (confirm('Are you sure you want to cancel this transport coordination request?')) {
    try {
      await buyerAPI.cancelTransportCoordination(id)
      showToast('Transport booking cancelled.')
      await loadData()
    } catch (err: any) {
      console.error('Error cancelling transport:', err)
      alert(err?.response?.data?.message || 'Failed to cancel transport.')
    }
  }
}

const openDetailsModal = (item: any) => {
  selectedItem.value = item
  detailsModalOpen.value = true
}

const clearFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  vehicleFilter.value = ''
}

const formatStatus = (status: string) => {
  switch (status) {
    case 'pending': return 'Pending Dispatch'
    case 'assigned': return 'Transporter Assigned'
    case 'in_transit': return 'In Transit'
    case 'completed': return 'Delivered'
    case 'cancelled': return 'Cancelled'
    default: return status
  }
}

const getStatusBadgeClass = (status: string) => {
  switch (status) {
    case 'pending':
      return 'bg-amber-500/10 text-amber-500 border-amber-500/30'
    case 'assigned':
      return 'bg-blue-500/10 text-blue-500 border-blue-500/30'
    case 'in_transit':
      return 'bg-purple-500/10 text-purple-400 border-purple-500/30'
    case 'completed':
      return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30'
    case 'cancelled':
      return 'bg-red-500/10 text-red-500 border-red-500/30'
    default:
      return 'bg-slate-500/10 text-slate-500 border-slate-500/30'
  }
}

const formatNumber = (num: number | string | undefined | null) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(Number(num || 0))
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slide-up {
  animation: slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
