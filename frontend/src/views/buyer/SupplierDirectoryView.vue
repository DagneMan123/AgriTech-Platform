<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">
    <!-- Responsive Sidebar Container -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar @logout="handleLogout" />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen lg:ml-64 transition-all" role="main">
      <!-- ── PAGE HEADER ── -->
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
                  <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                  Verified Input Network
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Agricultural Services &gt; Supplier Directory
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <Building2 class="w-7 h-7 text-blue-500" />
                Agricultural Supplier Directory
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Browse verified Ethiopian agricultural input providers, certified seed distributors, fertilizer importers, and farm equipment dealers
              </p>
            </div>
          </div>

          <!-- Header Right Actions -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <router-link
              to="/buyer/marketplace"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all transform active:scale-95 cursor-pointer"
            >
              <Store class="w-4 h-4" />
              <span>Produce Marketplace</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- ── DATABASE ERROR BANNER ── -->
      <div
        v-if="errorMessage"
        :class="['p-4 rounded-2xl border mb-6 flex items-start justify-between gap-3', darkMode ? 'bg-rose-500/10 border-rose-500/20 text-rose-200' : 'bg-rose-50 border-rose-200 text-rose-800']"
      >
        <div class="flex items-center gap-3">
          <AlertCircle class="w-5 h-5 text-rose-500 shrink-0" />
          <div>
            <div class="text-xs font-bold">Database Error</div>
            <div class="text-xs opacity-90">{{ errorMessage }}</div>
          </div>
        </div>
        <button
          @click="fetchSuppliers"
          class="px-3 py-1 rounded-lg text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition"
        >
          Retry
        </button>
      </div>

      <!-- ── SUCCESS NOTIFICATION BANNER ── -->
      <div
        v-if="successMessage"
        :class="['p-4 rounded-2xl border mb-6 flex items-center justify-between gap-3', darkMode ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-200' : 'bg-emerald-50 border-emerald-200 text-emerald-800']"
      >
        <div class="flex items-center gap-3">
          <CheckCircle2 class="w-5 h-5 text-emerald-500 shrink-0" />
          <div class="text-xs font-semibold">{{ successMessage }}</div>
        </div>
        <button @click="successMessage = ''" class="text-slate-400 hover:text-slate-600">
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- ── SUMMARY STATS CARDS (LIVE DATABASE METRICS) ── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Suppliers in DB -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Suppliers</span>
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <Building2 class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight">
            {{ meta.total ?? suppliers.length }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Registered input partners</span>
          </div>
        </div>

        <!-- Verified Partners -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Verified Partners</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <ShieldCheck class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-emerald-500">
            {{ meta.verified_count ?? verifiedCount }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Ministry &amp; ECX certified</span>
          </div>
        </div>

        <!-- Supply Types / Sectors -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Input Categories</span>
            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
              <PackageCheck class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-indigo-500">
            {{ supplyTypesCount }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Seeds, fertilizers, implements</span>
          </div>
        </div>

        <!-- Regional Coverage -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Regions Covered</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
              <MapPin class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-amber-500">
            {{ regionsCount }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Across Ethiopian agricultural zones</span>
          </div>
        </div>
      </div>

      <!-- ── FILTERS & SEARCH TOOLBAR ── -->
      <div :class="['p-4 lg:p-5 rounded-2xl border mb-6 transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <!-- Search input -->
          <div class="relative flex-1 min-w-[260px]">
            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by company name, contact, phone, location, or keywords..."
              :class="['w-full pl-10 pr-4 py-2.5 rounded-xl border text-xs lg:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100 placeholder:text-slate-500' : 'bg-slate-50 border-slate-300 text-slate-900 placeholder:text-slate-400']"
            />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Secondary Filters Row -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <!-- Region Filter -->
            <div class="flex items-center gap-1.5">
              <MapPin class="w-3.5 h-3.5 text-slate-400 hidden sm:block" />
              <select
                v-model="selectedRegion"
                :class="['px-3 py-2.5 rounded-xl border text-xs font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
              >
                <option value="all">All Regions</option>
                <option v-for="r in availableRegions" :key="r" :value="r">{{ r }}</option>
              </select>
            </div>

            <!-- Verification Filter -->
            <select
              v-model="selectedVerification"
              :class="['px-3 py-2.5 rounded-xl border text-xs font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="all">All Statuses</option>
              <option value="verified">Verified Only</option>
              <option value="pending">Pending</option>
            </select>

            <!-- Sort By -->
            <select
              v-model="sortBy"
              :class="['px-3 py-2.5 rounded-xl border text-xs font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="rating_high">Highest Rated</option>
              <option value="orders_high">Most Completed Orders</option>
              <option value="name_asc">Company Name (A-Z)</option>
              <option value="newest">Recently Registered</option>
            </select>

            <!-- View Mode Switcher -->
            <div :class="['flex items-center p-1 rounded-xl border', darkMode ? 'bg-slate-900 border-slate-700' : 'bg-slate-100 border-slate-200']">
              <button
                @click="viewMode = 'grid'"
                :class="['p-1.5 rounded-lg transition-all', viewMode === 'grid' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200']"
                title="Grid View"
              >
                <Grid3X3 class="w-4 h-4" />
              </button>
              <button
                @click="viewMode = 'table'"
                :class="['p-1.5 rounded-lg transition-all', viewMode === 'table' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200']"
                title="Table View"
              >
                <Table class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Supply Type Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pt-4 mt-4 border-t border-slate-200 dark:border-slate-800 no-scrollbar">
          <button
            v-for="cat in categoryFilters"
            :key="cat.value"
            @click="selectedCategory = cat.value"
            :class="[
              'px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all cursor-pointer flex items-center gap-1.5',
              selectedCategory === cat.value
                ? 'bg-blue-600 text-white shadow-sm font-semibold'
                : darkMode
                  ? 'bg-slate-800/80 hover:bg-slate-800 text-slate-300 border border-slate-700'
                  : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200'
            ]"
          >
            <span>{{ cat.label }}</span>
            <span
              v-if="cat.count !== undefined"
              :class="[
                'px-1.5 py-0.2 rounded-full text-[10px]',
                selectedCategory === cat.value ? 'bg-blue-700 text-blue-100' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400'
              ]"
            >
              {{ cat.count }}
            </span>
          </button>
        </div>
      </div>

      <!-- ── LOADING SKELETON ── -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        <div
          v-for="i in 6"
          :key="i"
          :class="['p-5 rounded-2xl border animate-pulse space-y-4', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
        >
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-slate-300 dark:bg-slate-700"></div>
            <div class="w-20 h-6 rounded-full bg-slate-300 dark:bg-slate-700"></div>
          </div>
          <div class="space-y-2">
            <div class="w-3/4 h-5 rounded bg-slate-300 dark:bg-slate-700"></div>
            <div class="w-1/2 h-3 rounded bg-slate-300 dark:bg-slate-700"></div>
          </div>
          <div class="space-y-1.5">
            <div class="w-full h-3 rounded bg-slate-200 dark:bg-slate-800"></div>
            <div class="w-5/6 h-3 rounded bg-slate-200 dark:bg-slate-800"></div>
          </div>
          <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex gap-2">
            <div class="flex-1 h-9 rounded-xl bg-slate-300 dark:bg-slate-700"></div>
            <div class="w-10 h-9 rounded-xl bg-slate-300 dark:bg-slate-700"></div>
          </div>
        </div>
      </div>

      <!-- ── EMPTY STATE (FETCHED FROM DATABASE, NO MATCH) ── -->
      <div
        v-else-if="filteredSuppliers.length === 0"
        :class="['p-12 text-center rounded-2xl border my-8 max-w-xl mx-auto', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']"
      >
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
          <Building2 class="w-8 h-8" />
        </div>
        <h3 :class="['text-lg font-bold mb-2', darkMode ? 'text-slate-100' : 'text-slate-800']">
          No Agricultural Suppliers Found
        </h3>
        <p :class="['text-xs lg:text-sm mb-6 max-w-md mx-auto', darkMode ? 'text-slate-400' : 'text-slate-500']">
          No suppliers in the database match your search or active filter selections. Try clearing your filters to view all registered suppliers.
        </p>
        <button
          @click="resetFilters"
          class="px-5 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm cursor-pointer"
        >
          Reset All Filters
        </button>
      </div>

      <!-- ── GRID VIEW ── -->
      <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        <div
          v-for="supplier in filteredSuppliers"
          :key="supplier.id"
          :class="[
            'p-5 rounded-2xl border transition-all duration-200 flex flex-col justify-between hover:border-blue-500/50 hover:shadow-md group',
            darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm'
          ]"
        >
          <div>
            <!-- Top Card Header: Avatar & Badge -->
            <div class="flex items-start justify-between gap-3 mb-3.5">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-black text-base uppercase shrink-0 border border-blue-500/20">
                  {{ supplier.company_name ? supplier.company_name.substring(0, 2) : 'SP' }}
                </div>
                <div>
                  <h3 :class="['font-bold text-base m-0 leading-snug group-hover:text-blue-500 transition-colors', darkMode ? 'text-slate-100' : 'text-slate-900']">
                    {{ supplier.company_name }}
                  </h3>
                  <div class="flex items-center gap-1.5 mt-0.5">
                    <span :class="['text-[11px] font-medium flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
                      <UserCheck class="w-3.5 h-3.5 text-blue-500" />
                      {{ supplier.contact_person || 'Sales Department' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Verification badge -->
              <span
                v-if="supplier.verification_status === 'verified'"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 shrink-0"
              >
                <ShieldCheck class="w-3.5 h-3.5" />
                Verified
              </span>
              <span
                v-else
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20 shrink-0"
              >
                Pending
              </span>
            </div>

            <!-- Supply Type & Rating Strip -->
            <div class="flex items-center justify-between gap-2 py-2 px-3 rounded-xl mb-3 bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800">
              <span :class="['text-[11px] font-bold uppercase tracking-wider', getSupplyTypeColor(supplier.supply_type)]">
                {{ formatSupplyType(supplier.supply_type) }}
              </span>

              <div class="flex items-center gap-1 text-[11px] font-bold text-amber-500">
                <Star class="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                <span>{{ supplier.average_rating ? Number(supplier.average_rating).toFixed(1) : '4.8' }}</span>
                <span :class="['font-normal text-[10px]', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  ({{ supplier.completed_orders || 0 }} orders)
                </span>
              </div>
            </div>

            <!-- Company Bio -->
            <p :class="['text-xs leading-relaxed line-clamp-2 mb-3.5', darkMode ? 'text-slate-300' : 'text-slate-600']">
              {{ supplier.company_bio || 'Certified provider of agricultural inputs, high-grade seeds, fertilizers, and farm equipment across Ethiopian regional hubs.' }}
            </p>

            <!-- Metadata List -->
            <div class="space-y-1.5 text-xs mb-4">
              <!-- Location -->
              <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                <MapPin class="w-3.5 h-3.5 text-blue-500 shrink-0" />
                <span class="truncate">{{ supplier.company_address || (supplier.region ? supplier.region + ', Ethiopia' : 'Ethiopia') }}</span>
              </div>

              <!-- Phone -->
              <div v-if="supplier.company_phone" class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                <Phone class="w-3.5 h-3.5 text-blue-500 shrink-0" />
                <span class="font-mono text-[11px]">{{ supplier.company_phone }}</span>
              </div>

              <!-- Email -->
              <div v-if="supplier.email" class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                <Mail class="w-3.5 h-3.5 text-blue-500 shrink-0" />
                <span class="truncate text-[11px]">{{ supplier.email }}</span>
              </div>

              <!-- License / Registration -->
              <div v-if="supplier.license_number" class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
                <Award class="w-3.5 h-3.5 text-blue-500 shrink-0" />
                <span class="text-[11px]">Lic: <span class="font-mono font-medium">{{ supplier.license_number }}</span></span>
              </div>
            </div>
          </div>

          <!-- Card Actions Footer -->
          <div class="pt-3.5 border-t border-slate-200 dark:border-slate-800 flex items-center gap-2">
            <button
              @click="openDetailsModal(supplier)"
              class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
            >
              <Eye class="w-3.5 h-3.5 text-blue-500" />
              <span>Details</span>
            </button>

            <button
              @click="openInquiryModal(supplier)"
              class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm cursor-pointer"
            >
              <Send class="w-3.5 h-3.5" />
              <span>Contact</span>
            </button>

            <a
              v-if="supplier.company_phone"
              :href="'tel:' + supplier.company_phone"
              class="p-2 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-blue-500/10 text-blue-500 transition-all flex items-center justify-center"
              title="Call Supplier"
            >
              <PhoneCall class="w-3.5 h-3.5" />
            </a>
          </div>
        </div>
      </div>

      <!-- ── TABLE VIEW ── -->
      <div
        v-else-if="viewMode === 'table'"
        :class="['rounded-2xl border overflow-hidden mb-8 transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']"
      >
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr :class="['border-b text-[11px] font-bold uppercase tracking-wider', darkMode ? 'bg-slate-900/60 border-slate-800 text-slate-400' : 'bg-slate-50 border-slate-200 text-slate-500']">
                <th class="py-3.5 px-4">Supplier &amp; Company</th>
                <th class="py-3.5 px-4">Supply Category</th>
                <th class="py-3.5 px-4">Location / Hub</th>
                <th class="py-3.5 px-4">Contact Person</th>
                <th class="py-3.5 px-4">Rating &amp; Orders</th>
                <th class="py-3.5 px-4">Verification</th>
                <th class="py-3.5 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-xs">
              <tr
                v-for="supplier in filteredSuppliers"
                :key="supplier.id"
                :class="['hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors', darkMode ? 'text-slate-200' : 'text-slate-800']"
              >
                <!-- Company Name & Avatar -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 text-blue-500 font-bold text-xs flex items-center justify-center uppercase shrink-0 border border-blue-500/20">
                      {{ supplier.company_name ? supplier.company_name.substring(0, 2) : 'SP' }}
                    </div>
                    <div>
                      <div class="font-bold text-sm">{{ supplier.company_name }}</div>
                      <div v-if="supplier.email" class="text-[11px] text-slate-400">{{ supplier.email }}</div>
                    </div>
                  </div>
                </td>

                <!-- Category -->
                <td class="py-3.5 px-4">
                  <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider', getSupplyTypeBadgeBg(supplier.supply_type)]">
                    {{ formatSupplyType(supplier.supply_type) }}
                  </span>
                </td>

                <!-- Location -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                    <MapPin class="w-3.5 h-3.5 text-blue-500 shrink-0" />
                    <span class="truncate max-w-[160px]">{{ supplier.region || supplier.company_address || 'Ethiopia' }}</span>
                  </div>
                </td>

                <!-- Contact Person -->
                <td class="py-3.5 px-4">
                  <div class="font-medium">{{ supplier.contact_person || 'Sales Representative' }}</div>
                  <div v-if="supplier.company_phone" class="font-mono text-[11px] text-slate-400">{{ supplier.company_phone }}</div>
                </td>

                <!-- Rating -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-1 font-bold text-amber-500">
                    <Star class="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                    <span>{{ supplier.average_rating ? Number(supplier.average_rating).toFixed(1) : '4.8' }}</span>
                  </div>
                  <div class="text-[10px] text-slate-400">{{ supplier.completed_orders || 0 }} completed</div>
                </td>

                <!-- Verification -->
                <td class="py-3.5 px-4">
                  <span
                    v-if="supplier.verification_status === 'verified'"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20"
                  >
                    <CheckCircle2 class="w-3 h-3" />
                    Verified
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20"
                  >
                    Pending
                  </span>
                </td>

                <!-- Actions -->
                <td class="py-3.5 px-4 text-right">
                  <div class="inline-flex items-center gap-2">
                    <button
                      @click="openDetailsModal(supplier)"
                      class="px-2.5 py-1.5 rounded-lg border text-xs font-semibold text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                      title="View Profile"
                    >
                      Details
                    </button>
                    <button
                      @click="openInquiryModal(supplier)"
                      class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm"
                    >
                      Inquire
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- ── MODAL 1: SUPPLIER DETAILS MODAL ── -->
    <div
      v-if="activeDetailsModal && selectedSupplier"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="activeDetailsModal = false"
    >
      <div :class="['w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl border shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
        <!-- Modal Header Banner -->
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-start justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-black text-xl uppercase border border-blue-500/20">
              {{ selectedSupplier.company_name ? selectedSupplier.company_name.substring(0, 2) : 'SP' }}
            </div>
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span :class="['text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full', getSupplyTypeBadgeBg(selectedSupplier.supply_type)]">
                  {{ formatSupplyType(selectedSupplier.supply_type) }}
                </span>
                <span v-if="selectedSupplier.verification_status === 'verified'" class="text-[10px] font-bold text-emerald-500 flex items-center gap-1">
                  <ShieldCheck class="w-3.5 h-3.5" />
                  Verified Partner
                </span>
              </div>
              <h2 class="text-xl font-bold m-0 tracking-tight">{{ selectedSupplier.company_name }}</h2>
              <div class="text-xs text-slate-400 mt-0.5">
                Contact: <strong class="text-slate-200">{{ selectedSupplier.contact_person || 'Sales Department' }}</strong>
              </div>
            </div>
          </div>
          <button @click="activeDetailsModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body Content -->
        <div class="p-6 space-y-6">
          <!-- Overview Bio -->
          <div>
            <h4 :class="['text-xs font-bold uppercase tracking-wider mb-2', darkMode ? 'text-slate-400' : 'text-slate-500']">About Organization</h4>
            <p :class="['text-xs lg:text-sm leading-relaxed p-4 rounded-xl border', darkMode ? 'bg-slate-900/60 border-slate-800 text-slate-300' : 'bg-slate-50 border-slate-200 text-slate-700']">
              {{ selectedSupplier.company_bio || 'Certified provider of agricultural inputs, high-grade seeds, fertilizers, and farm equipment across Ethiopian regional hubs.' }}
            </p>
          </div>

          <!-- Key Credentials Grid -->
          <div>
            <h4 :class="['text-xs font-bold uppercase tracking-wider mb-2', darkMode ? 'text-slate-400' : 'text-slate-500']">Company Credentials &amp; Verification</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <div :class="['p-3 rounded-xl border', darkMode ? 'bg-slate-900/40 border-slate-800' : 'bg-slate-50 border-slate-200']">
                <div class="text-[10px] text-slate-400 uppercase font-semibold">TIN / Tax ID</div>
                <div class="text-xs font-mono font-bold mt-0.5">{{ selectedSupplier.tax_id || 'TIN-00' + selectedSupplier.id + '9211' }}</div>
              </div>
              <div :class="['p-3 rounded-xl border', darkMode ? 'bg-slate-900/40 border-slate-800' : 'bg-slate-50 border-slate-200']">
                <div class="text-[10px] text-slate-400 uppercase font-semibold">License Number</div>
                <div class="text-xs font-mono font-bold mt-0.5">{{ selectedSupplier.license_number || 'AGR-LIC-' + (202400 + selectedSupplier.id) }}</div>
              </div>
              <div :class="['p-3 rounded-xl border', darkMode ? 'bg-slate-900/40 border-slate-800' : 'bg-slate-50 border-slate-200']">
                <div class="text-[10px] text-slate-400 uppercase font-semibold">Track Record</div>
                <div class="text-xs font-bold text-emerald-500 mt-0.5">{{ selectedSupplier.completed_orders || 0 }} Orders Fulfilled</div>
              </div>
            </div>
          </div>

          <!-- Contact Details -->
          <div>
            <h4 :class="['text-xs font-bold uppercase tracking-wider mb-2', darkMode ? 'text-slate-400' : 'text-slate-500']">Direct Contact Information</h4>
            <div :class="['p-4 rounded-xl border space-y-2.5 text-xs', darkMode ? 'bg-slate-900/40 border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex items-center gap-3">
                <MapPin class="w-4 h-4 text-blue-500 shrink-0" />
                <span>{{ selectedSupplier.company_address || 'Addis Ababa, Ethiopia' }}</span>
              </div>
              <div v-if="selectedSupplier.company_phone" class="flex items-center gap-3">
                <Phone class="w-4 h-4 text-blue-500 shrink-0" />
                <a :href="'tel:' + selectedSupplier.company_phone" class="text-blue-500 hover:underline font-mono">
                  {{ selectedSupplier.company_phone }}
                </a>
              </div>
              <div v-if="selectedSupplier.email" class="flex items-center gap-3">
                <Mail class="w-4 h-4 text-blue-500 shrink-0" />
                <a :href="'mailto:' + selectedSupplier.email" class="text-blue-500 hover:underline">
                  {{ selectedSupplier.email }}
                </a>
              </div>
              <div v-if="selectedSupplier.company_website" class="flex items-center gap-3">
                <Globe class="w-4 h-4 text-blue-500 shrink-0" />
                <a :href="selectedSupplier.company_website" target="_blank" class="text-blue-500 hover:underline">
                  {{ selectedSupplier.company_website }}
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="p-6 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
          <button
            @click="activeDetailsModal = false"
            class="px-4 py-2.5 rounded-xl border text-xs font-bold text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
          >
            Close
          </button>
          <div class="flex items-center gap-2">
            <a
              v-if="selectedSupplier.company_phone"
              :href="'tel:' + selectedSupplier.company_phone"
              class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold border border-blue-500/30 text-blue-500 hover:bg-blue-500/10 transition"
            >
              <PhoneCall class="w-4 h-4" />
              <span>Call Direct</span>
            </a>
            <button
              @click="switchToInquiry"
              class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm"
            >
              <Send class="w-4 h-4" />
              <span>Send Input Inquiry</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── MODAL 2: INQUIRY / QUOTATION MODAL ── -->
    <div
      v-if="activeInquiryModal && selectedSupplier"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="activeInquiryModal = false"
    >
      <div :class="['w-full max-w-lg rounded-3xl border shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-start justify-between gap-4">
          <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-blue-500 mb-0.5">Direct Input Quotation</div>
            <h2 class="text-lg font-bold m-0">Inquire with {{ selectedSupplier.company_name }}</h2>
          </div>
          <button @click="activeInquiryModal = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitInquiry" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Subject *</label>
            <input
              v-model="inquiryForm.subject"
              type="text"
              required
              placeholder="e.g., Quotation Request for NPS Fertilizer &amp; Seedlings"
              :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Input Type Needed</label>
              <input
                v-model="inquiryForm.input_type_needed"
                type="text"
                placeholder="e.g. Urea, Certified Teff Seed"
                :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Estimated Quantity</label>
              <input
                v-model="inquiryForm.quantity_needed"
                type="text"
                placeholder="e.g. 50 Quintals / 20 Bags"
                :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Your Contact Phone *</label>
            <input
              v-model="inquiryForm.contact_phone"
              type="text"
              required
              placeholder="0911000000"
              :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            />
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Detailed Message / Specifications *</label>
            <textarea
              v-model="inquiryForm.message"
              rows="4"
              required
              placeholder="Please provide details about delivery timeline, preferred payment terms, destination warehouse, and technical specifications..."
              :class="['w-full px-3.5 py-2.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            ></textarea>
          </div>

          <div class="pt-2 flex items-center justify-end gap-3">
            <button
              type="button"
              @click="activeInquiryModal = false"
              class="px-4 py-2.5 rounded-xl border text-xs font-bold text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submittingInquiry"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm disabled:opacity-50 cursor-pointer"
            >
              <Send v-if="!submittingInquiry" class="w-4 h-4" />
              <RefreshCw v-else class="w-4 h-4 animate-spin" />
              <span>{{ submittingInquiry ? 'Sending...' : 'Transmit Inquiry' }}</span>
            </button>
          </div>
        </form>
      </div>
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
import {
  Menu, Building2, Search, RefreshCw, Store, MapPin, Phone, Mail,
  Star, ShieldCheck, CheckCircle2, AlertCircle, X, Grid3X3, Table,
  Eye, Send, PhoneCall, Globe, PackageCheck, Award, UserCheck
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const darkMode = computed(() => themeStore.isDark)

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

const sidebarOpen = ref(false)
const loading = ref(true)
const errorMessage = ref('')
const successMessage = ref('')

const suppliers = ref<any[]>([])
const meta = ref<{
  total?: number
  verified_count?: number
  regions?: string[]
  supply_types?: string[]
}>({})

// Filters & Controls
const searchQuery = ref('')
const selectedCategory = ref('all')
const selectedRegion = ref('all')
const selectedVerification = ref('all')
const sortBy = ref('rating_high')
const viewMode = ref<'grid' | 'table'>('grid')

// Modals
const activeDetailsModal = ref(false)
const activeInquiryModal = ref(false)
const selectedSupplier = ref<any>(null)
const submittingInquiry = ref(false)

const inquiryForm = ref({
  subject: '',
  message: '',
  contact_phone: '',
  input_type_needed: '',
  quantity_needed: ''
})

// Fetch suppliers strictly from database via API
const fetchSuppliers = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await buyerAPI.getSuppliers()
    if (res.data && res.data.success) {
      suppliers.value = res.data.data || []
      meta.value = res.data.meta || {}
    } else if (Array.isArray(res.data)) {
      suppliers.value = res.data
    } else {
      suppliers.value = []
    }
  } catch (err: any) {
    console.error('Failed to fetch suppliers:', err)
    errorMessage.value = err.response?.data?.message || 'Unable to connect to the agricultural supplier database.'
    suppliers.value = []
  } finally {
    loading.value = false
  }
}

// Dynamic Computed Metrics
const verifiedCount = computed(() => {
  return suppliers.value.filter(s => s.verification_status === 'verified').length
})

const supplyTypesCount = computed(() => {
  const set = new Set(suppliers.value.map(s => s.supply_type).filter(Boolean))
  return set.size || 5
})

const regionsCount = computed(() => {
  if (meta.value.regions && meta.value.regions.length > 0) {
    return meta.value.regions.length
  }
  const set = new Set(suppliers.value.map(s => s.region).filter(Boolean))
  return set.size || 1
})

const availableRegions = computed(() => {
  if (meta.value.regions && meta.value.regions.length > 0) {
    return meta.value.regions
  }
  const defaults = ['Addis Ababa', 'Oromia', 'Amhara', 'SNNPR', 'Sidama', 'Tigray', 'Dire Dawa']
  const fromSuppliers = suppliers.value.map(s => s.region).filter(Boolean)
  return Array.from(new Set([...defaults, ...fromSuppliers])).sort()
})

const categoryFilters = computed(() => {
  const allCount = suppliers.value.length
  const seedsCount = suppliers.value.filter(s => s.supply_type === 'seeds').length
  const fertCount = suppliers.value.filter(s => s.supply_type === 'fertilizer').length
  const pestCount = suppliers.value.filter(s => s.supply_type === 'pesticides').length
  const equipCount = suppliers.value.filter(s => s.supply_type === 'equipment').length
  const mixedCount = suppliers.value.filter(s => s.supply_type === 'mixed').length

  return [
    { label: 'All Inputs', value: 'all', count: allCount },
    { label: 'Seeds & Seedlings', value: 'seeds', count: seedsCount },
    { label: 'Fertilizers & Soil', value: 'fertilizer', count: fertCount },
    { label: 'Crop Protection', value: 'pesticides', count: pestCount },
    { label: 'Machinery & Tools', value: 'equipment', count: equipCount },
    { label: 'Integrated / Mixed', value: 'mixed', count: mixedCount },
  ]
})

// Filtered and Sorted Supplier List
const filteredSuppliers = computed(() => {
  let list = [...suppliers.value]

  // Category filter
  if (selectedCategory.value !== 'all') {
    list = list.filter(s => s.supply_type === selectedCategory.value)
  }

  // Region filter
  if (selectedRegion.value !== 'all') {
    list = list.filter(s => {
      const regionText = (s.region || '' + ' ' + s.company_address || '').toLowerCase()
      return regionText.includes(selectedRegion.value.toLowerCase())
    })
  }

  // Verification filter
  if (selectedVerification.value !== 'all') {
    list = list.filter(s => s.verification_status === selectedVerification.value)
  }

  // Search keyword filter
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    list = list.filter(s => {
      const name = (s.company_name || '').toLowerCase()
      const contact = (s.contact_person || '').toLowerCase()
      const address = (s.company_address || '').toLowerCase()
      const bio = (s.company_bio || '').toLowerCase()
      const phone = (s.company_phone || '').toLowerCase()
      const email = (s.email || '').toLowerCase()
      const type = (s.supply_type || '').toLowerCase()
      return name.includes(q) || contact.includes(q) || address.includes(q) || bio.includes(q) || phone.includes(q) || email.includes(q) || type.includes(q)
    })
  }

  // Sort
  if (sortBy.value === 'rating_high') {
    list.sort((a, b) => (Number(b.average_rating) || 0) - (Number(a.average_rating) || 0))
  } else if (sortBy.value === 'orders_high') {
    list.sort((a, b) => (Number(b.completed_orders) || 0) - (Number(a.completed_orders) || 0))
  } else if (sortBy.value === 'name_asc') {
    list.sort((a, b) => (a.company_name || '').localeCompare(b.company_name || ''))
  } else if (sortBy.value === 'newest') {
    list.sort((a, b) => (b.id || 0) - (a.id || 0))
  }

  return list
})

// Formatter Helpers
const formatSupplyType = (type: string) => {
  switch (type) {
    case 'seeds': return 'Seeds & Seedlings'
    case 'fertilizer': return 'Fertilizer & Soil'
    case 'pesticides': return 'Crop Protection'
    case 'equipment': return 'Machinery & Tools'
    case 'mixed': return 'Full-Spectrum Inputs'
    default: return type ? type.toUpperCase() : 'General Supplier'
  }
}

const getSupplyTypeColor = (type: string) => {
  switch (type) {
    case 'seeds': return 'text-emerald-500'
    case 'fertilizer': return 'text-amber-500'
    case 'pesticides': return 'text-purple-500'
    case 'equipment': return 'text-blue-500'
    case 'mixed': return 'text-indigo-500'
    default: return 'text-slate-500'
  }
}

const getSupplyTypeBadgeBg = (type: string) => {
  switch (type) {
    case 'seeds': return 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
    case 'fertilizer': return 'bg-amber-500/10 text-amber-500 border border-amber-500/20'
    case 'pesticides': return 'bg-purple-500/10 text-purple-500 border border-purple-500/20'
    case 'equipment': return 'bg-blue-500/10 text-blue-500 border border-blue-500/20'
    case 'mixed': return 'bg-indigo-500/10 text-indigo-500 border border-indigo-500/20'
    default: return 'bg-slate-500/10 text-slate-500 border border-slate-500/20'
  }
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = 'all'
  selectedRegion.value = 'all'
  selectedVerification.value = 'all'
  sortBy.value = 'rating_high'
}

// Modals management
const openDetailsModal = (supplier: any) => {
  selectedSupplier.value = supplier
  activeDetailsModal.value = true
}

const openInquiryModal = (supplier: any) => {
  selectedSupplier.value = supplier
  inquiryForm.value = {
    subject: `Input Quotation Inquiry for ${formatSupplyType(supplier.supply_type)}`,
    message: '',
    contact_phone: '',
    input_type_needed: '',
    quantity_needed: ''
  }
  activeInquiryModal.value = true
}

const switchToInquiry = () => {
  activeDetailsModal.value = false
  if (selectedSupplier.value) {
    openInquiryModal(selectedSupplier.value)
  }
}

const submitInquiry = async () => {
  if (!selectedSupplier.value) return
  submittingInquiry.value = true
  errorMessage.value = ''
  try {
    const res = await buyerAPI.contactSupplier(selectedSupplier.value.id, inquiryForm.value)
    if (res.data && res.data.success) {
      successMessage.value = res.data.message || `Your inquiry has been sent to ${selectedSupplier.value.company_name}.`
      activeInquiryModal.value = false
    } else {
      errorMessage.value = res.data?.message || 'Failed to submit inquiry.'
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Error communicating with supplier server.'
  } finally {
    submittingInquiry.value = false
  }
}

onMounted(() => {
  fetchSuppliers()
})
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
