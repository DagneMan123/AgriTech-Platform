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
                  <BookUser class="w-3.5 h-3.5" />
                  Live Database Records
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Logistics &amp; Delivery &gt; Delivery Addresses
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Delivery Addresses
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Manage your registered delivery locations, warehouses, retail depots, and shipping drop-off points
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
              @click="openAddModal"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all transform active:scale-95 cursor-pointer"
            >
              <Plus class="w-4 h-4" />
              <span>Add New Address</span>
            </button>
            <BuyerHeaderNav />
          </div>
        </div>
      </div>

      <!-- ── Summary Stats Cards ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Saved Locations -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Database Hubs</h3>
            <p class="text-2xl font-black">{{ addresses.length }}</p>
            <span class="text-[10px] text-blue-500 font-medium">{{ warehouseCount }} Warehouses &bull; {{ retailCount }} Retail</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <Warehouse class="w-6 h-6" />
          </div>
        </article>

        <!-- Default Shipping Destination -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-emerald-500' : 'bg-white border-slate-200 text-slate-900 !border-l-emerald-600']">
          <div class="truncate mr-2">
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Primary Hub</h3>
            <p class="text-sm font-black truncate max-w-[170px]" :title="defaultAddress?.title || 'None registered'">
              {{ defaultAddress?.title || 'None Assigned' }}
            </p>
            <span class="text-[10px] text-emerald-500 font-medium flex items-center gap-1">
              <CheckCircle2 class="w-3 h-3 inline" /> Default Destination
            </span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <Star class="w-6 h-6 fill-current" />
          </div>
        </article>

        <!-- Active Inbound Deliveries -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-purple-500' : 'bg-white border-slate-200 text-slate-900 !border-l-purple-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Active Inbound</h3>
            <p class="text-2xl font-black text-purple-500">{{ totalActiveInbound }}</p>
            <span class="text-[10px] text-purple-400 font-medium">From database orders &amp; fleet</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-purple-500/10 text-purple-400' : 'bg-purple-50 text-purple-600']">
            <Truck class="w-6 h-6" />
          </div>
        </article>

        <!-- Regional Coverage -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Coverage Zones</h3>
            <p class="text-2xl font-black text-blue-500">{{ distinctRegionsCount }}</p>
            <span class="text-[10px] text-blue-500 font-medium">Active logistical zones</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <Navigation class="w-6 h-6" />
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
              placeholder="Search by address title, street, city, contact person, or phone..."
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

          <!-- Filters & View Toggle -->
          <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <select
              v-model="typeFilter"
              :class="['px-3 py-2.5 rounded-xl text-xs border font-medium outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="">All Facility Types</option>
              <option value="warehouse">Warehouses &amp; Hubs</option>
              <option value="cold_storage">Cold Storage Terminals</option>
              <option value="retail">Retail / Wholesale Outlets</option>
              <option value="factory">Processing Plants</option>
              <option value="office">Commercial Offices</option>
              <option value="farm_hub">Farm Drop-off Hubs</option>
            </select>

            <!-- Region Filter -->
            <select
              v-if="availableRegions.length > 0"
              v-model="regionFilter"
              :class="['px-3 py-2.5 rounded-xl text-xs border font-medium outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="">All Regions</option>
              <option v-for="reg in availableRegions" :key="reg" :value="reg">{{ reg }}</option>
            </select>

            <!-- Sort By -->
            <select
              v-model="sortBy"
              :class="['px-3 py-2.5 rounded-xl text-xs border font-medium outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="default_first">Default First</option>
              <option value="name_asc">Name (A-Z)</option>
              <option value="recent">Most Recent</option>
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
          Fetching delivery addresses from database...
        </p>
      </div>

      <!-- ── Empty State ── -->
      <div
        v-else-if="filteredAddresses.length === 0"
        :class="['rounded-2xl border p-12 text-center shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
      >
        <div class="w-16 h-16 rounded-full bg-blue-500/10 text-blue-500 flex items-center justify-center mx-auto mb-4">
          <BookUser class="w-8 h-8" />
        </div>
        <h3 :class="['text-lg font-bold mb-1', darkMode ? 'text-slate-100' : 'text-slate-900']">
          {{ addresses.length === 0 ? 'No delivery addresses in database' : 'No matching addresses found' }}
        </h3>
        <p :class="['text-xs max-w-md mx-auto mb-6', darkMode ? 'text-slate-400' : 'text-slate-500']">
          <span v-if="searchQuery || typeFilter || regionFilter">
            No saved locations match your active filter criteria. Try clearing search filters.
          </span>
          <span v-else>
            Your account does not have any delivery addresses saved in the database yet. Click below to add your primary drop-off hub.
          </span>
        </p>
        <div class="flex items-center justify-center gap-3">
          <button
            v-if="searchQuery || typeFilter || regionFilter"
            @click="clearFilters"
            :class="['px-4 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700']"
          >
            Clear Filters
          </button>
          <button
            @click="openAddModal"
            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm cursor-pointer"
          >
            + Add New Address
          </button>
        </div>
      </div>

      <!-- ── GRID VIEW ── -->
      <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <div
          v-for="addr in filteredAddresses"
          :key="addr.id"
          :class="['rounded-2xl border p-5 shadow-2xs transition-all relative flex flex-col justify-between group hover:shadow-md',
            addr.is_default ? (darkMode ? 'border-blue-500/60 bg-[#14203a]' : 'border-blue-500/80 bg-blue-50/20') :
            (darkMode ? 'bg-[#131b2e] border-slate-800 hover:border-slate-700' : 'bg-white border-slate-200 hover:border-slate-300')
          ]"
        >
          <div>
            <!-- Top Badges & Options -->
            <div class="flex items-start justify-between gap-2 mb-3">
              <div class="flex items-center gap-2 flex-wrap">
                <!-- Type Badge -->
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border', getTypeBadgeClass(addr.type)]">
                  <component :is="getTypeIcon(addr.type)" class="w-3.5 h-3.5" />
                  {{ formatTypeName(addr.type) }}
                </span>

                <!-- Default Badge -->
                <span
                  v-if="addr.is_default"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-500 border border-amber-500/30"
                >
                  <Star class="w-3 h-3 fill-current" />
                  DEFAULT DESTINATION
                </span>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center gap-1">
                <button
                  @click="copyAddressToClipboard(addr)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-500/10 transition-all cursor-pointer"
                  title="Copy full address"
                >
                  <Copy class="w-4 h-4" />
                </button>
                <button
                  @click="openEditModal(addr)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-500/10 transition-all cursor-pointer"
                  title="Edit address"
                >
                  <Edit3 class="w-4 h-4" />
                </button>
                <button
                  @click="confirmDeleteAddress(addr)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-500/10 transition-all cursor-pointer"
                  title="Delete address"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Title & Destination Name -->
            <h2 :class="['text-base font-bold tracking-tight mb-1 flex items-center gap-1.5', darkMode ? 'text-slate-100' : 'text-slate-900']">
              {{ addr.title }}
            </h2>

            <!-- Street Address & Regional Meta -->
            <div class="space-y-1.5 mb-4 text-xs">
              <p :class="['m-0 font-medium leading-relaxed', darkMode ? 'text-slate-300' : 'text-slate-700']">
                {{ addr.street_address }}
              </p>
              <p :class="['m-0 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
                <MapPin class="w-3.5 h-3.5 text-blue-500 shrink-0" />
                <span>{{ addr.sub_city ? `${addr.sub_city}, ` : '' }}{{ addr.city }}, {{ addr.region }}</span>
              </p>
              <p v-if="addr.landmark" :class="['m-0 text-[11px] italic', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Landmark: {{ addr.landmark }}
              </p>
            </div>

            <!-- Contact Person Info -->
            <div :class="['rounded-xl p-3 mb-4 border transition-all text-xs', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex items-center justify-between mb-1">
                <span class="flex items-center gap-1.5 font-semibold text-slate-700 dark:text-slate-200">
                  <User class="w-3.5 h-3.5 text-blue-500" />
                  {{ addr.contact_name }}
                </span>
                <span v-if="addr.contact_role" class="text-[10px] text-slate-400 font-medium">
                  {{ addr.contact_role }}
                </span>
              </div>
              <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                <a :href="`tel:${addr.contact_phone}`" class="flex items-center gap-1 text-blue-500 hover:underline font-mono">
                  <Phone class="w-3 h-3" />
                  {{ addr.contact_phone }}
                </a>
                <span v-if="addr.receiving_hours" class="flex items-center gap-1">
                  <Clock class="w-3 h-3 text-amber-500" />
                  {{ addr.receiving_hours }}
                </span>
              </div>
            </div>

            <!-- Logistics Capabilities Tags -->
            <div v-if="addr.capabilities && addr.capabilities.length > 0" class="flex flex-wrap gap-1.5 mb-4">
              <span
                v-for="cap in addr.capabilities"
                :key="cap"
                :class="['px-2 py-0.5 rounded-md text-[10px] font-medium border', darkMode ? 'bg-slate-800/80 border-slate-700 text-slate-300' : 'bg-slate-100 border-slate-200 text-slate-600']"
              >
                {{ cap }}
              </span>
            </div>

            <!-- Driver Gate Instructions -->
            <div
              v-if="addr.instructions"
              :class="['rounded-xl p-2.5 text-[11px] mb-4 border border-dashed', darkMode ? 'bg-blue-950/20 border-blue-900/50 text-blue-300' : 'bg-blue-50/60 border-blue-200 text-blue-900']"
            >
              <div class="font-bold uppercase tracking-wider text-[9px] text-blue-500 mb-0.5 flex items-center gap-1">
                <ShieldCheck class="w-3 h-3" /> Gate &amp; Offloading Instructions:
              </div>
              <p class="m-0 leading-normal">{{ addr.instructions }}</p>
            </div>
          </div>

          <!-- Bottom Actions Bar -->
          <div :class="['pt-3 border-t flex items-center justify-between gap-2 mt-auto', darkMode ? 'border-slate-800' : 'border-slate-200']">
            <div class="flex items-center gap-2">
              <button
                @click="openMapModal(addr)"
                :class="['inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium border transition-all cursor-pointer', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700']"
              >
                <Compass class="w-3.5 h-3.5 text-blue-500" />
                <span>Map Pin</span>
              </button>

              <button
                v-if="getInboundCountForAddress(addr) > 0"
                @click="openShipmentsModal(addr)"
                class="inline-flex items-center gap-1 px-2 py-1.5 rounded-lg text-xs font-semibold bg-purple-500/10 text-purple-400 hover:bg-purple-500/20 transition-all cursor-pointer"
              >
                <Truck class="w-3.5 h-3.5" />
                <span>{{ getInboundCountForAddress(addr) }} Inbound</span>
              </button>
            </div>

            <!-- Set as Default Button -->
            <button
              v-if="!addr.is_default"
              @click="setDefaultAddress(addr.id)"
              :class="['px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer hover:border-blue-500 hover:text-blue-500', darkMode ? 'border-slate-700 text-slate-400' : 'border-slate-300 text-slate-600']"
            >
              Make Default
            </button>
            <span
              v-else
              class="text-xs font-bold text-emerald-500 flex items-center gap-1"
            >
              <Check class="w-3.5 h-3.5 stroke-[3]" /> Active Default
            </span>
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
                <th class="py-3.5 px-4">Destination Hub</th>
                <th class="py-3.5 px-4">Facility Type</th>
                <th class="py-3.5 px-4">Street &amp; Region</th>
                <th class="py-3.5 px-4">Contact Person</th>
                <th class="py-3.5 px-4">Receiving Hours</th>
                <th class="py-3.5 px-4">Inbound Shipments</th>
                <th class="py-3.5 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
              <tr
                v-for="addr in filteredAddresses"
                :key="addr.id"
                :class="['transition-colors', addr.is_default ? (darkMode ? 'bg-blue-950/20' : 'bg-blue-50/30') : '', darkMode ? 'hover:bg-slate-800/40' : 'hover:bg-slate-50/80']"
              >
                <!-- Title & Default -->
                <td class="py-4 px-4 font-bold">
                  <div class="flex items-center gap-2">
                    <span :class="[darkMode ? 'text-slate-100' : 'text-slate-900']">{{ addr.title }}</span>
                    <span
                      v-if="addr.is_default"
                      class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-500/10 text-amber-500 border border-amber-500/30"
                    >
                      DEFAULT
                    </span>
                  </div>
                  <span class="text-[11px] font-normal text-slate-400 block">{{ addr.landmark || 'No landmark specified' }}</span>
                </td>

                <!-- Type -->
                <td class="py-4 px-4">
                  <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold border', getTypeBadgeClass(addr.type)]">
                    <component :is="getTypeIcon(addr.type)" class="w-3 h-3" />
                    {{ formatTypeName(addr.type) }}
                  </span>
                </td>

                <!-- Address -->
                <td class="py-4 px-4">
                  <p class="m-0 font-medium">{{ addr.street_address }}</p>
                  <p class="m-0 text-[11px] text-slate-400">{{ addr.sub_city ? `${addr.sub_city}, ` : '' }}{{ addr.city }}, {{ addr.region }}</p>
                </td>

                <!-- Contact -->
                <td class="py-4 px-4">
                  <p class="m-0 font-semibold text-slate-700 dark:text-slate-200">{{ addr.contact_name }}</p>
                  <a :href="`tel:${addr.contact_phone}`" class="text-blue-500 hover:underline text-[11px] font-mono">{{ addr.contact_phone }}</a>
                </td>

                <!-- Hours -->
                <td class="py-4 px-4 text-slate-500 dark:text-slate-400 text-[11px]">
                  {{ addr.receiving_hours || 'Standard Business Hours' }}
                </td>

                <!-- Inbound Shipments -->
                <td class="py-4 px-4">
                  <span
                    v-if="getInboundCountForAddress(addr) > 0"
                    @click="openShipmentsModal(addr)"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-400 cursor-pointer hover:underline"
                  >
                    <Truck class="w-3 h-3" />
                    {{ getInboundCountForAddress(addr) }} in transit
                  </span>
                  <span v-else class="text-slate-400 text-[11px]">0 active</span>
                </td>

                <!-- Actions -->
                <td class="py-4 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      v-if="!addr.is_default"
                      @click="setDefaultAddress(addr.id)"
                      class="px-2 py-1 rounded text-[11px] font-medium text-slate-400 hover:text-blue-500 hover:bg-blue-500/10 transition-all cursor-pointer"
                      title="Set as Default"
                    >
                      Make Default
                    </button>
                    <button
                      @click="openMapModal(addr)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-500/10 transition-all cursor-pointer"
                      title="View on Map"
                    >
                      <Compass class="w-4 h-4" />
                    </button>
                    <button
                      @click="copyAddressToClipboard(addr)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-500/10 transition-all cursor-pointer"
                      title="Copy Address"
                    >
                      <Copy class="w-4 h-4" />
                    </button>
                    <button
                      @click="openEditModal(addr)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-500/10 transition-all cursor-pointer"
                      title="Edit Address"
                    >
                      <Edit3 class="w-4 h-4" />
                    </button>
                    <button
                      @click="confirmDeleteAddress(addr)"
                      class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-500/10 transition-all cursor-pointer"
                      title="Delete Address"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>

    <!-- ── MODAL: ADD / EDIT DELIVERY ADDRESS ── -->
    <div
      v-if="addressModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
      @click.self="addressModalOpen = false"
    >
      <div
        :class="['rounded-2xl border w-full max-w-2xl my-8 overflow-hidden shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div :class="['px-6 py-4 border-b flex items-center justify-between', darkMode ? 'border-slate-800 bg-[#0f172a]' : 'border-slate-200 bg-slate-50']">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <BookUser class="w-4 h-4" />
            </div>
            <div>
              <h2 class="text-base font-bold m-0">
                {{ isEditing ? 'Edit Delivery Address' : 'Add New Delivery Address' }}
              </h2>
              <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Saves directly into your database delivery address registry
              </p>
            </div>
          </div>
          <button
            @click="addressModalOpen = false"
            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body Form -->
        <form @submit.prevent="saveAddress" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
          <!-- Row 1: Title & Type -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Address / Hub Nickname *
              </label>
              <input
                v-model="form.title"
                type="text"
                required
                placeholder="e.g. Central Warehouse / Branch Depot"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>

            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Facility / Address Type *
              </label>
              <select
                v-model="form.type"
                required
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              >
                <option value="warehouse">Central Warehouse &amp; Hub</option>
                <option value="cold_storage">Cold-Chain Storage Terminal</option>
                <option value="retail">Retail / Wholesale Outlet</option>
                <option value="factory">Agro-Processing Plant</option>
                <option value="office">Commercial Office / HQ</option>
                <option value="farm_hub">Farm Drop-off Collection Point</option>
              </select>
            </div>
          </div>

          <!-- Row 2: Region, City, Sub-City -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Region / State *
              </label>
              <input
                v-model="form.region"
                type="text"
                required
                placeholder="e.g. Addis Ababa"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                City / Town *
              </label>
              <input
                v-model="form.city"
                type="text"
                required
                placeholder="e.g. Addis Ababa"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Sub-City / Zone
              </label>
              <input
                v-model="form.sub_city"
                type="text"
                placeholder="e.g. Bole"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
          </div>

          <!-- Row 3: Street Address & Landmark -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Detailed Street Address &amp; Gate *
              </label>
              <input
                v-model="form.street_address"
                type="text"
                required
                placeholder="e.g. Ring Road, Commercial Sector #4"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Prominent Landmark
              </label>
              <input
                v-model="form.landmark"
                type="text"
                placeholder="e.g. Near Commercial Bank"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
          </div>

          <!-- Row 4: Contact Person & Phone -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Contact Person Name *
              </label>
              <input
                v-model="form.contact_name"
                type="text"
                required
                placeholder="e.g. Operations Manager"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Designation / Role
              </label>
              <input
                v-model="form.contact_role"
                type="text"
                placeholder="e.g. Warehouse Supervisor"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Contact Phone *
              </label>
              <input
                v-model="form.contact_phone"
                type="tel"
                required
                placeholder="+251 91 000 0000"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all font-mono', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
          </div>

          <!-- Row 5: Receiving Hours & GPS Coordinates -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Receiving Hours
              </label>
              <input
                v-model="form.receiving_hours"
                type="text"
                placeholder="e.g. Mon - Sat: 08:00 - 18:00"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                GPS Latitude
              </label>
              <input
                v-model.number="form.latitude"
                type="number"
                step="any"
                placeholder="e.g. 8.9806"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all font-mono', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                GPS Longitude
              </label>
              <input
                v-model.number="form.longitude"
                type="number"
                step="any"
                placeholder="e.g. 38.7578"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all font-mono', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
              />
            </div>
          </div>

          <!-- Capabilities Checkboxes -->
          <div>
            <label :class="['block text-xs font-bold mb-1.5', darkMode ? 'text-slate-300' : 'text-slate-700']">
              Facility Equipment &amp; Logistics Capabilities
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
              <label
                v-for="cap in allCapabilityOptions"
                :key="cap"
                :class="['flex items-center gap-2 p-2 rounded-xl border cursor-pointer select-none transition-all',
                  form.capabilities.includes(cap)
                    ? 'border-blue-500 bg-blue-500/10 text-blue-500 font-semibold'
                    : (darkMode ? 'border-slate-800 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-600')
                ]"
              >
                <input
                  type="checkbox"
                  :value="cap"
                  v-model="form.capabilities"
                  class="rounded text-blue-600 focus:ring-0"
                />
                <span class="text-[11px]">{{ cap }}</span>
              </label>
            </div>
          </div>

          <!-- Gate / Driver Instructions -->
          <div>
            <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
              Driver Gate Instructions &amp; Offloading Procedures
            </label>
            <textarea
              v-model="form.instructions"
              rows="3"
              placeholder="e.g. Check in at security gate with consignment note."
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
            ></textarea>
          </div>

          <!-- Default Address Checkbox -->
          <div :class="['p-3 rounded-xl border flex items-center justify-between', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div class="flex items-center gap-2.5">
              <input
                id="is_default"
                v-model="form.is_default"
                type="checkbox"
                class="w-4 h-4 rounded text-blue-600 focus:ring-0 cursor-pointer"
              />
              <div>
                <label for="is_default" :class="['text-xs font-bold cursor-pointer block', darkMode ? 'text-slate-200' : 'text-slate-800']">
                  Set as Default Delivery Address
                </label>
                <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  This location will be designated by default for all new marketplace orders and farm contract deliveries.
                </p>
              </div>
            </div>
            <Star v-if="form.is_default" class="w-5 h-5 text-amber-500 fill-current" />
          </div>

          <!-- Modal Actions Footer -->
          <div :class="['pt-4 border-t flex items-center justify-end gap-3', darkMode ? 'border-slate-800' : 'border-slate-200']">
            <button
              type="button"
              @click="addressModalOpen = false"
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
              <span>{{ saving ? 'Saving...' : (isEditing ? 'Save Changes' : 'Save to Database') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── MODAL: MAP PIN VIEW ── -->
    <div
      v-if="mapModalOpen && activeMapAddress"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      @click.self="mapModalOpen = false"
    >
      <div
        :class="['rounded-2xl border w-full max-w-xl overflow-hidden shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <div :class="['px-6 py-4 border-b flex items-center justify-between', darkMode ? 'border-slate-800 bg-[#0f172a]' : 'border-slate-200 bg-slate-50']">
          <div class="flex items-center gap-2">
            <Compass class="w-5 h-5 text-blue-500" />
            <div>
              <h2 class="text-sm font-bold m-0">{{ activeMapAddress.title }}</h2>
              <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                {{ activeMapAddress.city }}, {{ activeMapAddress.region }}
              </p>
            </div>
          </div>
          <button
            @click="mapModalOpen = false"
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="p-6 space-y-4">
          <div
            class="w-full h-64 rounded-xl border overflow-hidden relative bg-slate-900 flex items-center justify-center"
          >
            <div class="text-center p-6 text-white space-y-2">
              <div class="w-12 h-12 rounded-full bg-blue-600/30 border border-blue-500 flex items-center justify-center mx-auto animate-bounce">
                <MapPin class="w-6 h-6 text-blue-400" />
              </div>
              <p class="font-bold text-sm">{{ activeMapAddress.title }}</p>
              <p class="text-xs text-slate-300 font-mono">
                GPS: {{ activeMapAddress.latitude || 8.9806 }}° N, {{ activeMapAddress.longitude || 38.7578 }}° E
              </p>
              <p class="text-[11px] text-slate-400 max-w-sm mx-auto">
                {{ activeMapAddress.street_address }}
              </p>
            </div>
          </div>

          <div :class="['p-3.5 rounded-xl border text-xs', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div class="flex items-center justify-between mb-2">
              <span class="font-bold flex items-center gap-1.5">
                <ShieldCheck class="w-4 h-4 text-emerald-500" />
                Receiving Checkpoint:
              </span>
              <span class="font-mono text-[11px] text-blue-500">
                Lat: {{ activeMapAddress.latitude || 8.9806 }}, Lng: {{ activeMapAddress.longitude || 38.7578 }}
              </span>
            </div>
            <p class="m-0 text-slate-600 dark:text-slate-400 leading-relaxed mb-2">
              {{ activeMapAddress.instructions || 'Standard gate clearance and paperwork inspection applies.' }}
            </p>
            <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-slate-800 text-[11px]">
              <span class="text-slate-400">On-site Contact:</span>
              <span class="font-semibold text-slate-700 dark:text-slate-200">
                {{ activeMapAddress.contact_name }} &bull; {{ activeMapAddress.contact_phone }}
              </span>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-2">
            <button
              @click="copyCoordinates(activeMapAddress)"
              :class="['px-3 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700']"
            >
              <Copy class="w-3.5 h-3.5 inline mr-1" />
              Copy Coordinates
            </button>
            <a
              :href="`https://www.google.com/maps/search/?api=1&query=${activeMapAddress.latitude || 8.9806},${activeMapAddress.longitude || 38.7578}`"
              target="_blank"
              rel="noopener noreferrer"
              class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white flex items-center gap-1.5 shadow-sm transition-all"
            >
              <span>Open in Google Maps</span>
              <ExternalLink class="w-3.5 h-3.5" />
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ── MODAL: ACTIVE SHIPMENTS FOR ADDRESS ── -->
    <div
      v-if="shipmentsModalOpen && selectedAddressForShipments"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
      @click.self="shipmentsModalOpen = false"
    >
      <div
        :class="['rounded-2xl border w-full max-w-xl overflow-hidden shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <div :class="['px-6 py-4 border-b flex items-center justify-between', darkMode ? 'border-slate-800 bg-[#0f172a]' : 'border-slate-200 bg-slate-50']">
          <div class="flex items-center gap-2">
            <Truck class="w-5 h-5 text-purple-500" />
            <div>
              <h2 class="text-sm font-bold m-0">Inbound Database Shipments</h2>
              <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Live freight deliveries destined for {{ selectedAddressForShipments.title }}
              </p>
            </div>
          </div>
          <button
            @click="shipmentsModalOpen = false"
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="p-6 space-y-3">
          <div
            v-if="getInboundShipmentsForAddress(selectedAddressForShipments).length === 0"
            class="text-center py-6 text-slate-400 text-xs"
          >
            No active inbound freight shipments currently recorded in database for this location.
          </div>
          <div
            v-else
            v-for="shipment in getInboundShipmentsForAddress(selectedAddressForShipments)"
            :key="shipment.id"
            :class="['rounded-xl p-3 border transition-all text-xs', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']"
          >
            <div class="flex items-center justify-between mb-1.5">
              <span class="font-bold text-blue-500">
                #{{ shipment.tracking_number || shipment.id }}
              </span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                {{ shipment.status || 'In Transit' }}
              </span>
            </div>
            <p class="m-0 font-medium text-slate-700 dark:text-slate-200 mb-1">
              Driver: {{ shipment.driver_name || 'Assigned Transporter' }} ({{ shipment.vehicle_plate || 'Freight Fleet' }})
            </p>
            <div class="flex items-center justify-between text-[11px] text-slate-400">
              <span>ETA: {{ formatDateTime(shipment.estimated_delivery) }}</span>
              <router-link to="/buyer/delivery/tracking" class="text-blue-500 hover:underline font-semibold flex items-center gap-0.5">
                Track Live Telemetry &rarr;
              </router-link>
            </div>
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
  Menu, BookUser, Plus, Warehouse, Store, Factory, Snowflake, Building2,
  Tractor, Star, Truck, Navigation, Search, LayoutGrid, List, Copy, Edit3,
  Trash2, MapPin, User, Phone, Clock, ShieldCheck, Compass, Check, CheckCircle2,
  ExternalLink, X, RefreshCw
} from 'lucide-vue-next'

export interface DeliveryAddress {
  id: string | number
  title: string
  type: 'warehouse' | 'cold_storage' | 'retail' | 'factory' | 'office' | 'farm_hub'
  contact_name: string
  contact_role?: string
  contact_phone: string
  secondary_phone?: string
  region: string
  city: string
  sub_city?: string
  woreda_kebele?: string
  street_address: string
  landmark?: string
  postal_code?: string
  is_default: boolean
  instructions?: string
  receiving_hours?: string
  capabilities: string[]
  latitude?: number
  longitude?: number
  total_deliveries?: number
  last_delivery_date?: string
  created_at: string
}

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)
const sidebarOpen = ref(false)
const loading = ref(false)
const saving = ref(false)

const searchQuery = ref('')
const typeFilter = ref('')
const regionFilter = ref('')
const sortBy = ref('default_first')
const viewMode = ref<'grid' | 'list'>('grid')

// PURE DATABASE RECORDS ONLY — NO STATIC HARDCODED SAMPLES
const addresses = ref<DeliveryAddress[]>([])
const activeShipments = ref<any[]>([])

const addressModalOpen = ref(false)
const isEditing = ref(false)
const editingAddressId = ref<string | number | null>(null)

const mapModalOpen = ref(false)
const activeMapAddress = ref<DeliveryAddress | null>(null)

const shipmentsModalOpen = ref(false)
const selectedAddressForShipments = ref<DeliveryAddress | null>(null)

const toastMessage = ref('')
let toastTimer: any = null

const showToast = (msg: string) => {
  toastMessage.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toastMessage.value = ''
  }, 3500)
}

const allCapabilityOptions = [
  '40ft Trailer Bay',
  'Forklift Available',
  'Cold Chain Dock',
  'Refrigerated Receiving',
  'Pallet Jack',
  'Weighbridge on-site',
  '24/7 Security Guard',
  'Ramp Access',
  'Sack Offloading Team'
]

const form = ref({
  title: '',
  type: 'warehouse' as DeliveryAddress['type'],
  region: 'Addis Ababa',
  city: 'Addis Ababa',
  sub_city: '',
  woreda_kebele: '',
  street_address: '',
  landmark: '',
  contact_name: '',
  contact_role: '',
  contact_phone: '',
  receiving_hours: 'Mon - Sat: 08:00 - 18:00',
  instructions: '',
  capabilities: [] as string[],
  latitude: 8.9806,
  longitude: 38.7578,
  is_default: false
})

const defaultAddress = computed(() => {
  return addresses.value.find(a => a.is_default) || addresses.value[0] || null
})

const warehouseCount = computed(() => {
  return addresses.value.filter(a => a.type === 'warehouse' || a.type === 'cold_storage').length
})

const retailCount = computed(() => {
  return addresses.value.filter(a => a.type === 'retail' || a.type === 'factory' || a.type === 'office').length
})

const distinctRegionsCount = computed(() => {
  return new Set(addresses.value.map(a => a.region).filter(Boolean)).size
})

const availableRegions = computed(() => {
  return Array.from(new Set(addresses.value.map(a => a.region).filter(Boolean)))
})

const totalActiveInbound = computed(() => {
  return activeShipments.value.length
})

const filteredAddresses = computed(() => {
  let list = [...addresses.value]

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(a =>
      (a.title && a.title.toLowerCase().includes(q)) ||
      (a.street_address && a.street_address.toLowerCase().includes(q)) ||
      (a.city && a.city.toLowerCase().includes(q)) ||
      (a.region && a.region.toLowerCase().includes(q)) ||
      (a.contact_name && a.contact_name.toLowerCase().includes(q)) ||
      (a.contact_phone && a.contact_phone.includes(q)) ||
      (a.landmark && a.landmark.toLowerCase().includes(q))
    )
  }

  if (typeFilter.value) {
    list = list.filter(a => a.type === typeFilter.value)
  }

  if (regionFilter.value) {
    list = list.filter(a => a.region === regionFilter.value)
  }

  if (sortBy.value === 'default_first') {
    list.sort((a, b) => (b.is_default ? 1 : 0) - (a.is_default ? 1 : 0))
  } else if (sortBy.value === 'name_asc') {
    list.sort((a, b) => (a.title || '').localeCompare(b.title || ''))
  } else if (sortBy.value === 'recent') {
    list.sort((a, b) => new Date(b.created_at || '').getTime() - new Date(a.created_at || '').getTime())
  }

  return list
})

onMounted(async () => {
  themeStore.initializeTheme()
  await loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    await Promise.all([
      fetchAddressesFromDatabase(),
      fetchInboundShipmentsFromDatabase()
    ])
  } finally {
    loading.value = false
  }
}

/**
 * Fetch exclusively from the database API
 */
const fetchAddressesFromDatabase = async () => {
  try {
    const res = await buyerAPI.getDeliveryAddresses()
    if (res && res.data) {
      const records = Array.isArray(res.data.data)
        ? res.data.data
        : (Array.isArray(res.data) ? res.data : [])

      addresses.value = records.map((item: any) => ({
        id: item.id,
        title: item.title,
        type: item.type || 'warehouse',
        contact_name: item.contact_name,
        contact_role: item.contact_role,
        contact_phone: item.contact_phone,
        secondary_phone: item.secondary_phone,
        region: item.region || 'Addis Ababa',
        city: item.city || 'Addis Ababa',
        sub_city: item.sub_city || '',
        woreda_kebele: item.woreda_kebele || '',
        street_address: item.street_address,
        landmark: item.landmark || '',
        postal_code: item.postal_code || '',
        is_default: Boolean(item.is_default),
        instructions: item.instructions || '',
        receiving_hours: item.receiving_hours || '',
        capabilities: Array.isArray(item.capabilities) ? item.capabilities : (typeof item.capabilities === 'string' ? JSON.parse(item.capabilities) : []),
        latitude: item.latitude ? Number(item.latitude) : 8.9806,
        longitude: item.longitude ? Number(item.longitude) : 38.7578,
        total_deliveries: item.total_deliveries || 0,
        last_delivery_date: item.last_delivery_date,
        created_at: item.created_at || new Date().toISOString()
      }))
    }
  } catch (err: any) {
    console.error('Database fetch error:', err)
  }
}

/**
 * Fetch live shipments from database orders & deliveries
 */
const fetchInboundShipmentsFromDatabase = async () => {
  try {
    const res = await buyerAPI.getDeliveryTracking().catch(() => null)
    if (res && res.data) {
      const items = Array.isArray(res.data) ? res.data : (res.data.data || [])
      activeShipments.value = items.filter((i: any) => i.status !== 'delivered')
    }
  } catch (err) {
    console.error('Error fetching live deliveries from database:', err)
    activeShipments.value = []
  }
}

const getInboundCountForAddress = (addr: DeliveryAddress): number => {
  return activeShipments.value.filter(s =>
    (s.destination && s.destination.toLowerCase().includes((addr.city || '').toLowerCase())) ||
    (s.destination && s.destination.toLowerCase().includes((addr.title || '').toLowerCase())) ||
    (addr.is_default && !s.destination)
  ).length
}

const getInboundShipmentsForAddress = (addr: DeliveryAddress) => {
  return activeShipments.value.filter(s =>
    (s.destination && s.destination.toLowerCase().includes((addr.city || '').toLowerCase())) ||
    (s.destination && s.destination.toLowerCase().includes((addr.title || '').toLowerCase())) ||
    (addr.is_default && !s.destination)
  )
}

const openAddModal = () => {
  isEditing.value = false
  editingAddressId.value = null
  form.value = {
    title: '',
    type: 'warehouse',
    region: authStore.user?.region || 'Addis Ababa',
    city: authStore.user?.location || 'Addis Ababa',
    sub_city: authStore.user?.zone || '',
    woreda_kebele: authStore.user?.woreda || '',
    street_address: authStore.user?.address || '',
    landmark: '',
    contact_name: authStore.user?.name || '',
    contact_role: 'Operations Logistics Contact',
    contact_phone: authStore.user?.phone || '+251 91 000 0000',
    receiving_hours: 'Mon - Sat: 08:00 - 18:00',
    instructions: '',
    capabilities: ['Forklift Available', 'Pallet Jack'],
    latitude: 8.9806,
    longitude: 38.7578,
    is_default: addresses.value.length === 0
  }
  addressModalOpen.value = true
}

const openEditModal = (addr: DeliveryAddress) => {
  isEditing.value = true
  editingAddressId.value = addr.id
  form.value = {
    title: addr.title,
    type: addr.type,
    region: addr.region,
    city: addr.city,
    sub_city: addr.sub_city || '',
    woreda_kebele: addr.woreda_kebele || '',
    street_address: addr.street_address,
    landmark: addr.landmark || '',
    contact_name: addr.contact_name,
    contact_role: addr.contact_role || '',
    contact_phone: addr.contact_phone,
    receiving_hours: addr.receiving_hours || 'Mon - Sat: 08:00 - 18:00',
    instructions: addr.instructions || '',
    capabilities: [...(addr.capabilities || [])],
    latitude: addr.latitude || 8.9806,
    longitude: addr.longitude || 38.7578,
    is_default: addr.is_default
  }
  addressModalOpen.value = true
}

/**
 * Save new or updated record into PostgreSQL database
 */
const saveAddress = async () => {
  if (!form.value.title.trim() || !form.value.street_address.trim() || !form.value.contact_name.trim() || !form.value.contact_phone.trim()) {
    alert('Please fill in all required fields.')
    return
  }

  saving.value = true
  try {
    const payload = {
      title: form.value.title,
      type: form.value.type,
      region: form.value.region,
      city: form.value.city,
      sub_city: form.value.sub_city,
      woreda_kebele: form.value.woreda_kebele,
      street_address: form.value.street_address,
      landmark: form.value.landmark,
      contact_name: form.value.contact_name,
      contact_role: form.value.contact_role,
      contact_phone: form.value.contact_phone,
      receiving_hours: form.value.receiving_hours,
      instructions: form.value.instructions,
      capabilities: form.value.capabilities,
      latitude: form.value.latitude,
      longitude: form.value.longitude,
      is_default: form.value.is_default
    }

    if (isEditing.value && editingAddressId.value !== null) {
      await buyerAPI.updateDeliveryAddress(editingAddressId.value, payload)
      showToast('Delivery address updated in database successfully!')
    } else {
      await buyerAPI.createDeliveryAddress(payload)
      showToast('New delivery address saved to database successfully!')
    }

    addressModalOpen.value = false
    await fetchAddressesFromDatabase()
  } catch (err: any) {
    console.error('Failed to save address to database:', err)
    alert(err?.response?.data?.message || 'Failed to save address to database.')
  } finally {
    saving.value = false
  }
}

/**
 * Update default address directly in database
 */
const setDefaultAddress = async (id: string | number) => {
  try {
    await buyerAPI.setDefaultDeliveryAddress(id)
    await fetchAddressesFromDatabase()
    const target = addresses.value.find(a => a.id === id)
    showToast(`"${target?.title}" set as default in database.`)
  } catch (err: any) {
    console.error('Error setting default address:', err)
    alert('Failed to update default address in database.')
  }
}

/**
 * Delete address directly from database
 */
const confirmDeleteAddress = async (addr: DeliveryAddress) => {
  if (addr.is_default && addresses.value.length > 1) {
    alert('This is currently your default delivery destination. Please designate another address as default before deleting this one.')
    return
  }

  if (confirm(`Are you sure you want to delete delivery address "${addr.title}" from database?`)) {
    try {
      await buyerAPI.deleteDeliveryAddress(addr.id)
      await fetchAddressesFromDatabase()
      showToast(`Address "${addr.title}" deleted from database.`)
    } catch (err: any) {
      console.error('Error deleting address from database:', err)
      alert(err?.response?.data?.message || 'Failed to delete address.')
    }
  }
}

const copyAddressToClipboard = (addr: DeliveryAddress) => {
  const fullText = `${addr.title}\n${addr.street_address}\n${addr.sub_city ? addr.sub_city + ', ' : ''}${addr.city}, ${addr.region}\nLandmark: ${addr.landmark || 'N/A'}\nContact: ${addr.contact_name} (${addr.contact_phone})\nHours: ${addr.receiving_hours || 'Standard'}\nInstructions: ${addr.instructions || 'Standard'}`

  navigator.clipboard.writeText(fullText).then(() => {
    showToast('Address details copied to clipboard!')
  }).catch(() => {
    showToast('Address details copied!')
  })
}

const copyCoordinates = (addr: DeliveryAddress) => {
  const coords = `${addr.latitude || 8.9806}, ${addr.longitude || 38.7578}`
  navigator.clipboard.writeText(coords).then(() => {
    showToast(`Coordinates ${coords} copied!`)
  })
}

const openMapModal = (addr: DeliveryAddress) => {
  activeMapAddress.value = addr
  mapModalOpen.value = true
}

const openShipmentsModal = (addr: DeliveryAddress) => {
  selectedAddressForShipments.value = addr
  shipmentsModalOpen.value = true
}

const clearFilters = () => {
  searchQuery.value = ''
  typeFilter.value = ''
  regionFilter.value = ''
}

const getTypeIcon = (type: DeliveryAddress['type']) => {
  switch (type) {
    case 'warehouse': return Warehouse
    case 'cold_storage': return Snowflake
    case 'retail': return Store
    case 'factory': return Factory
    case 'office': return Building2
    case 'farm_hub': return Tractor
    default: return MapPin
  }
}

const formatTypeName = (type: DeliveryAddress['type']) => {
  switch (type) {
    case 'warehouse': return 'Central Warehouse'
    case 'cold_storage': return 'Cold Chain Terminal'
    case 'retail': return 'Retail / Storefront'
    case 'factory': return 'Processing Plant'
    case 'office': return 'Commercial Office'
    case 'farm_hub': return 'Farm Drop-off'
    default: return 'Delivery Hub'
  }
}

const getTypeBadgeClass = (type: DeliveryAddress['type']) => {
  switch (type) {
    case 'warehouse':
      return 'bg-blue-500/10 text-blue-500 border-blue-500/30'
    case 'cold_storage':
      return 'bg-cyan-500/10 text-cyan-500 border-cyan-500/30'
    case 'retail':
      return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30'
    case 'factory':
      return 'bg-purple-500/10 text-purple-500 border-purple-500/30'
    case 'office':
      return 'bg-amber-500/10 text-amber-500 border-amber-500/30'
    case 'farm_hub':
      return 'bg-lime-500/10 text-lime-500 border-lime-500/30'
    default:
      return 'bg-slate-500/10 text-slate-500 border-slate-500/30'
  }
}

const formatDateTime = (dateStr?: string | null) => {
  if (!dateStr) return 'Pending'
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
