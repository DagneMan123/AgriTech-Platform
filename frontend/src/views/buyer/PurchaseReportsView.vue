<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', darkMode ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-100 text-slate-800']">
    <!-- Responsive Sidebar Container -->
    <div :class="['fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
      <BuyerSidebar @logout="handleLogout" />
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <!-- Main Content Area with proper 64-unit left margin -->
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
                  Financial &amp; Procurement Intelligence
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Reports &gt; Purchase Reports
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <FileSpreadsheet class="w-7 h-7 text-blue-500" />
                Purchase Reports &amp; Expense Analysis
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Audit procurement expenditure, produce volume, commodity breakdowns, and supplier fulfillment across your Ethiopian agricultural purchases
              </p>
            </div>
          </div>

          <!-- Header Right Actions -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <button
              @click="fetchReports"
              :disabled="loading"
              :class="['inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold text-xs border transition-all cursor-pointer', darkMode ? 'bg-slate-800/80 hover:bg-slate-800 text-slate-200 border-slate-700' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200 shadow-sm']"
              title="Refresh report data"
            >
              <RefreshCw :class="['w-4 h-4', loading ? 'animate-spin text-blue-500' : 'text-slate-400']" />
              <span>Refresh</span>
            </button>

            <button
              @click="exportCSV"
              :disabled="loading || orders.length === 0"
              :class="['inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold text-xs border transition-all cursor-pointer disabled:opacity-50', darkMode ? 'bg-slate-800/80 hover:bg-slate-800 text-slate-200 border-slate-700' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200 shadow-sm']"
              title="Download filtered report as CSV"
            >
              <Download class="w-4 h-4 text-blue-500" />
              <span>Export CSV</span>
            </button>

            <button
              @click="triggerPrint"
              :disabled="loading || orders.length === 0"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all transform active:scale-95 cursor-pointer disabled:opacity-50"
            >
              <Printer class="w-4 h-4" />
              <span>Print Audit</span>
            </button>
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
          @click="fetchReports"
          class="px-3 py-1 rounded-lg text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition"
        >
          Retry
        </button>
      </div>

      <!-- ── SUMMARY KPI STATS CARDS (LIVE DATABASE METRICS) ── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Spend -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Spend</span>
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <DollarSign class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-blue-500">
            ETB {{ formatCurrency(summary.total_spend) }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Net settled procurement</span>
          </div>
        </div>

        <!-- Total Orders -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Orders</span>
            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
              <ShoppingBag class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight">
            {{ summary.total_orders || 0 }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span class="text-emerald-500 font-semibold">{{ summary.completed_orders || 0 }} fulfilled</span>
            <span v-if="summary.pending_orders" class="text-amber-500">({{ summary.pending_orders }} in transit)</span>
          </div>
        </div>

        <!-- Average Order Value (AOV) -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Avg Order Value</span>
            <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
              <TrendingUp class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-purple-400">
            ETB {{ formatCurrency(summary.average_order_value) }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Mean purchase value</span>
          </div>
        </div>

        <!-- Produce Volume (Units / Quintals) -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Volume</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <Scale class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-emerald-500">
            {{ formatNumber(summary.total_produce_units) }} <span class="text-xs font-normal text-slate-400">units</span>
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Across all agricultural commodities</span>
          </div>
        </div>
      </div>

      <!-- ── FILTERS & AUDIT TOOLBAR ── -->
      <div :class="['p-4 lg:p-5 rounded-2xl border mb-6 transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <!-- Keyword Search -->
          <div class="relative flex-1 min-w-[260px]">
            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="searchQuery"
              @input="onFilterChange"
              type="text"
              placeholder="Search by order #, produce name, seller, or warehouse location..."
              :class="['w-full pl-10 pr-4 py-2.5 rounded-xl border text-xs lg:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-100 placeholder:text-slate-500' : 'bg-slate-50 border-slate-300 text-slate-900 placeholder:text-slate-400']"
            />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''; onFilterChange()"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Secondary Filters Row -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <!-- Order Status Filter -->
            <select
              v-model="selectedStatus"
              @change="onFilterChange"
              :class="['px-3 py-2.5 rounded-xl border text-xs font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="all">All Order Statuses</option>
              <option value="delivered">Delivered / Completed</option>
              <option value="processing">Processing</option>
              <option value="pending">Pending</option>
              <option value="cancelled">Cancelled</option>
            </select>

            <!-- Payment Status Filter -->
            <select
              v-model="selectedPaymentStatus"
              @change="onFilterChange"
              :class="['px-3 py-2.5 rounded-xl border text-xs font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="all">All Payment Statuses</option>
              <option value="paid">Paid &amp; Settled</option>
              <option value="pending">Escrow Pending</option>
            </select>

            <!-- Sort By -->
            <select
              v-model="sortBy"
              :class="['px-3 py-2.5 rounded-xl border text-xs font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="newest">Most Recent Orders</option>
              <option value="amount_high">Highest Amount (ETB)</option>
              <option value="amount_low">Lowest Amount (ETB)</option>
            </select>
          </div>
        </div>

        <!-- Period Range Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 mt-4 border-t border-slate-200 dark:border-slate-800">
          <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
            <button
              v-for="p in periodOptions"
              :key="p.value"
              @click="setPeriod(p.value)"
              :class="[
                'px-3.5 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all cursor-pointer',
                selectedPeriod === p.value
                  ? 'bg-blue-600 text-white shadow-sm font-semibold'
                  : darkMode
                    ? 'bg-slate-800/80 hover:bg-slate-800 text-slate-300 border border-slate-700'
                    : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200'
              ]"
            >
              {{ p.label }}
            </button>
          </div>

          <!-- Custom Date Range Picker (shown when custom is chosen) -->
          <div v-if="selectedPeriod === 'custom'" class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-1 text-xs">
              <span class="text-slate-400">From:</span>
              <input
                v-model="customFromDate"
                type="date"
                @change="onFilterChange"
                :class="['px-2.5 py-1 rounded-lg border text-xs', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
              />
            </div>
            <div class="flex items-center gap-1 text-xs">
              <span class="text-slate-400">To:</span>
              <input
                v-model="customToDate"
                type="date"
                @change="onFilterChange"
                :class="['px-2.5 py-1 rounded-lg border text-xs', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- ── VISUAL ANALYTICS BREAKDOWNS ── -->
      <div v-if="orders.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
        <!-- Monthly Spend Trend Visualizer -->
        <div :class="['lg:col-span-2 p-5 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 :class="['text-sm font-bold m-0 flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <BarChart3 class="w-4 h-4 text-blue-500" />
                Monthly Procurement Timeline (Last 6 Months)
              </h3>
              <p :class="['text-[11px] m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Monthly gross spend in ETB and order fulfillment frequency
              </p>
            </div>
            <span class="text-xs font-mono font-bold text-blue-500">
              ETB {{ formatCurrency(summary.total_spend) }}
            </span>
          </div>

          <!-- Timeline Bars -->
          <div class="grid grid-cols-6 gap-2 pt-6 pb-2 items-end h-44 border-b border-slate-200 dark:border-slate-800">
            <div
              v-for="item in monthlyTrend"
              :key="item.month"
              class="flex flex-col items-center h-full justify-end group"
            >
              <div class="text-[10px] font-mono text-slate-400 mb-1 opacity-0 group-hover:opacity-100 transition-opacity">
                {{ item.spend > 0 ? (item.spend / 1000).toFixed(0) + 'k' : '0' }}
              </div>
              <div class="w-full max-w-[42px] bg-slate-100 dark:bg-slate-800/80 rounded-t-lg h-full flex items-end overflow-hidden p-0.5">
                <div
                  class="w-full bg-blue-600 rounded-t-md transition-all duration-500 group-hover:bg-blue-500"
                  :style="{ height: getBarHeight(item.spend) }"
                ></div>
              </div>
              <div class="text-[10px] font-medium text-slate-400 mt-2 truncate w-full text-center">
                {{ item.month.split(' ')[0] }}
              </div>
              <div class="text-[9px] text-slate-500 font-mono">
                {{ item.orders_count }} ord
              </div>
            </div>
          </div>
        </div>

        <!-- Commodity Categories Breakdown -->
        <div :class="['p-5 rounded-2xl border transition-all flex flex-col justify-between', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div>
            <div class="flex items-center justify-between mb-4">
              <h3 :class="['text-sm font-bold m-0 flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <PieChart class="w-4 h-4 text-blue-500" />
                Category Distribution
              </h3>
              <span class="text-[11px] text-slate-400">{{ categories.length }} categories</span>
            </div>

            <div v-if="categories.length > 0" class="space-y-3">
              <div v-for="cat in categories.slice(0, 5)" :key="cat.category">
                <div class="flex items-center justify-between text-xs mb-1">
                  <span class="font-medium truncate max-w-[140px]">{{ cat.category }}</span>
                  <div class="flex items-center gap-2 font-mono">
                    <span class="font-bold">ETB {{ formatCurrency(cat.amount) }}</span>
                    <span class="text-[10px] text-slate-400">({{ cat.percentage }}%)</span>
                  </div>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                  <div
                    class="h-full bg-blue-600 rounded-full"
                    :style="{ width: cat.percentage + '%' }"
                  ></div>
                </div>
              </div>
            </div>
            <div v-else class="text-xs text-slate-400 italic py-6 text-center">
              No category breakdown available for current filter
            </div>
          </div>

          <!-- Payment Channel Breakdown Footer -->
          <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Payment Channels</div>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="pm in paymentMethods"
                :key="pm.method"
                class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex items-center gap-1.5"
              >
                <ShieldCheck class="w-3 h-3 text-emerald-500" />
                <span>{{ pm.method }}: {{ pm.percentage }}%</span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- ── LOADING SKELETON ── -->
      <div v-if="loading" :class="['rounded-2xl border p-6 animate-pulse space-y-4 mb-8', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="h-6 w-48 rounded bg-slate-300 dark:bg-slate-700 mb-4"></div>
        <div v-for="i in 5" :key="i" class="h-10 rounded bg-slate-200 dark:bg-slate-800"></div>
      </div>

      <!-- ── EMPTY STATE (FETCHED DIRECTLY FROM DATABASE) ── -->
      <div
        v-else-if="filteredOrders.length === 0"
        :class="['p-12 text-center rounded-2xl border my-8 max-w-xl mx-auto', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']"
      >
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
          <FileSpreadsheet class="w-8 h-8" />
        </div>
        <h3 :class="['text-lg font-bold mb-2', darkMode ? 'text-slate-100' : 'text-slate-800']">
          No Purchase Records in Database
        </h3>
        <p :class="['text-xs lg:text-sm mb-6 max-w-md mx-auto', darkMode ? 'text-slate-400' : 'text-slate-500']">
          {{ searchQuery || selectedStatus !== 'all' || selectedPeriod !== 'all'
            ? 'No purchase transactions matched your active search query or date filters.'
            : 'You have not completed any produce purchases yet. Once you order crops from the marketplace or contract farming, your purchase invoices and analytical reports will be generated here.' }}
        </p>
        <div class="flex items-center justify-center gap-3">
          <button
            v-if="searchQuery || selectedStatus !== 'all' || selectedPeriod !== 'all'"
            @click="resetFilters"
            class="px-5 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm cursor-pointer"
          >
            Reset Filters
          </button>
          <router-link
            to="/buyer/marketplace"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm"
          >
            <Store class="w-4 h-4" />
            <span>Browse Marketplace</span>
          </router-link>
        </div>
      </div>

      <!-- ── DETAILED PURCHASE RECORDS TABLE ── -->
      <div
        v-else
        :class="['rounded-2xl border overflow-hidden mb-8 transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']"
      >
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
          <div>
            <h3 :class="['text-sm font-bold m-0', darkMode ? 'text-slate-100' : 'text-slate-900']">
              Itemized Purchase Ledger
            </h3>
            <p :class="['text-[11px] m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
              Showing {{ filteredOrders.length }} registered transactions from database
            </p>
          </div>

          <span class="text-xs font-mono font-bold text-blue-500">
            Total: ETB {{ formatCurrency(filteredTotalAmount) }}
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr :class="['border-b text-[11px] font-bold uppercase tracking-wider', darkMode ? 'bg-slate-900/60 border-slate-800 text-slate-400' : 'bg-slate-50 border-slate-200 text-slate-500']">
                <th class="py-3.5 px-4">Order # &amp; Date</th>
                <th class="py-3.5 px-4">Seller / Producer</th>
                <th class="py-3.5 px-4">Items / Commodities</th>
                <th class="py-3.5 px-4">Quantity</th>
                <th class="py-3.5 px-4">Payment Method</th>
                <th class="py-3.5 px-4">Status</th>
                <th class="py-3.5 px-4 text-right">Grand Total (ETB)</th>
                <th class="py-3.5 px-4 text-center">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-xs">
              <tr
                v-for="order in filteredOrders"
                :key="order.id"
                :class="['hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors', darkMode ? 'text-slate-200' : 'text-slate-800']"
              >
                <!-- Order # and Date -->
                <td class="py-3.5 px-4">
                  <div class="font-mono font-bold text-blue-500">{{ order.order_number }}</div>
                  <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                    <Calendar class="w-3 h-3" />
                    <span>{{ order.date_formatted }}</span>
                  </div>
                </td>

                <!-- Seller / Cooperative -->
                <td class="py-3.5 px-4">
                  <div class="font-semibold">{{ order.seller_name }}</div>
                  <div v-if="order.seller_phone" class="font-mono text-[10px] text-slate-400">{{ order.seller_phone }}</div>
                </td>

                <!-- Produce Items Summary -->
                <td class="py-3.5 px-4">
                  <div class="font-medium max-w-[200px] truncate" :title="order.items_summary">
                    {{ order.items_summary }}
                  </div>
                  <div class="text-[10px] text-slate-400">
                    {{ order.items ? order.items.length : 0 }} commodity line items
                  </div>
                </td>

                <!-- Quantity -->
                <td class="py-3.5 px-4 font-mono font-medium">
                  {{ order.total_quantity }} {{ order.quantity_unit }}
                </td>

                <!-- Payment Channel & Status -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-1.5 font-medium">
                    <span>{{ order.payment_method }}</span>
                  </div>
                  <span
                    :class="[
                      'inline-block px-2 py-0.2 rounded-full text-[9px] font-bold uppercase mt-0.5',
                      order.payment_status === 'paid'
                        ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                        : 'bg-amber-500/10 text-amber-500 border border-amber-500/20'
                    ]"
                  >
                    {{ order.payment_status }}
                  </span>
                </td>

                <!-- Status -->
                <td class="py-3.5 px-4">
                  <span
                    :class="[
                      'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider',
                      getStatusBadgeClass(order.status)
                    ]"
                  >
                    {{ order.status_label || order.status }}
                  </span>
                </td>

                <!-- Grand Total -->
                <td class="py-3.5 px-4 text-right font-mono font-bold text-sm text-blue-500">
                  ETB {{ formatCurrency(order.grand_total) }}
                </td>

                <!-- Action: View Modal -->
                <td class="py-3.5 px-4 text-center">
                  <button
                    @click="openDetailsModal(order)"
                    class="px-2.5 py-1.5 rounded-lg border text-xs font-semibold text-slate-700 dark:text-slate-200 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                    title="View Itemized Breakdown"
                  >
                    <Eye class="w-3.5 h-3.5 inline mr-1 text-blue-500" />
                    <span>Invoice</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- ── INVOICE DETAILS MODAL ── -->
    <div
      v-if="selectedOrder && detailsModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="detailsModalOpen = false"
    >
      <div :class="['w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl border shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
        <!-- Modal Header -->
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-start justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-500 border border-blue-500/20">
                Purchase Order Receipt
              </span>
              <span :class="['text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full', getStatusBadgeClass(selectedOrder.status)]">
                {{ selectedOrder.status }}
              </span>
            </div>
            <h2 class="text-xl font-mono font-bold m-0 tracking-tight">{{ selectedOrder.order_number }}</h2>
            <div class="text-xs text-slate-400 mt-0.5">Purchased on {{ selectedOrder.date_formatted }}</div>
          </div>
          <button @click="detailsModalOpen = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body Content -->
        <div class="p-6 space-y-6 text-xs">
          <!-- Seller & Delivery Info Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div :class="['p-4 rounded-xl border', darkMode ? 'bg-slate-900/60 border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Seller / Farm Cooperative</div>
              <div class="font-bold text-sm">{{ selectedOrder.seller_name }}</div>
              <div v-if="selectedOrder.seller_phone" class="font-mono text-slate-400 mt-1">
                Phone: {{ selectedOrder.seller_phone }}
              </div>
            </div>

            <div :class="['p-4 rounded-xl border', darkMode ? 'bg-slate-900/60 border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Delivery Destination</div>
              <div class="font-medium">{{ selectedOrder.delivery_address }}</div>
              <div class="text-slate-400 mt-1">Payment: <strong class="text-blue-500">{{ selectedOrder.payment_method }}</strong></div>
            </div>
          </div>

          <!-- Itemized Commodities Table -->
          <div>
            <h4 :class="['text-xs font-bold uppercase tracking-wider mb-2', darkMode ? 'text-slate-400' : 'text-slate-500']">
              Purchased Commodity Items
            </h4>
            <div :class="['rounded-xl border overflow-hidden', darkMode ? 'border-slate-800' : 'border-slate-200']">
              <table class="w-full text-left">
                <thead>
                  <tr :class="['border-b text-[10px] font-bold uppercase tracking-wider', darkMode ? 'bg-slate-900/60 border-slate-800 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-600']">
                    <th class="py-2.5 px-3">Crop / Produce</th>
                    <th class="py-2.5 px-3">Category</th>
                    <th class="py-2.5 px-3 text-right">Unit Price</th>
                    <th class="py-2.5 px-3 text-right">Quantity</th>
                    <th class="py-2.5 px-3 text-right">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                  <tr v-for="item in selectedOrder.items" :key="item.id">
                    <td class="py-2.5 px-3 font-semibold">{{ item.product_name }}</td>
                    <td class="py-2.5 px-3 text-slate-400">{{ item.category }}</td>
                    <td class="py-2.5 px-3 text-right font-mono">ETB {{ formatCurrency(item.price) }}</td>
                    <td class="py-2.5 px-3 text-right font-mono">{{ item.quantity }} {{ item.unit }}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-blue-500">ETB {{ formatCurrency(item.subtotal) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Financial Calculation Breakdown -->
          <div :class="['p-4 rounded-xl border space-y-1.5 font-mono', darkMode ? 'bg-slate-900/40 border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div class="flex items-center justify-between">
              <span class="text-slate-400 font-sans">Produce Subtotal:</span>
              <span>ETB {{ formatCurrency(selectedOrder.subtotal) }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-slate-400 font-sans">Transport / Delivery:</span>
              <span>ETB {{ formatCurrency(selectedOrder.delivery_fee) }}</span>
            </div>
            <div v-if="selectedOrder.tax" class="flex items-center justify-between">
              <span class="text-slate-400 font-sans">VAT / Service Tax:</span>
              <span>ETB {{ formatCurrency(selectedOrder.tax) }}</span>
            </div>
            <div v-if="selectedOrder.discount" class="flex items-center justify-between text-emerald-500">
              <span class="font-sans">Cooperative Discount:</span>
              <span>- ETB {{ formatCurrency(selectedOrder.discount) }}</span>
            </div>
            <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between font-bold text-sm text-blue-500">
              <span class="font-sans">Grand Total:</span>
              <span>ETB {{ formatCurrency(selectedOrder.grand_total) }}</span>
            </div>
          </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="p-6 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
          <button
            @click="detailsModalOpen = false"
            class="px-4 py-2 rounded-xl border text-xs font-bold text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
          >
            Close
          </button>
          <button
            @click="triggerPrint"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm"
          >
            <Printer class="w-4 h-4" />
            <span>Print Receipt</span>
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
import { useThemeStore } from '@/stores/themeStore'
import { buyerAPI } from '@/api/buyer'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import {
  Menu, FileSpreadsheet, Download, Printer, RefreshCw, AlertCircle,
  X, DollarSign, ShoppingBag, TrendingUp, Scale, Search, Calendar,
  BarChart3, PieChart, ShieldCheck, Eye, Store
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const loading = ref(true)
const errorMessage = ref('')

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

// Report Data State
const orders = ref<any[]>([])
const summary = ref({
  total_spend: 0,
  total_orders: 0,
  completed_orders: 0,
  pending_orders: 0,
  cancelled_orders: 0,
  average_order_value: 0,
  total_produce_units: 0,
  currency: 'ETB'
})
const categories = ref<any[]>([])
const paymentMethods = ref<any[]>([])
const monthlyTrend = ref<any[]>([])

// Filters & Controls
const searchQuery = ref('')
const selectedPeriod = ref('all')
const customFromDate = ref('')
const customToDate = ref('')
const selectedStatus = ref('all')
const selectedPaymentStatus = ref('all')
const sortBy = ref('newest')

const periodOptions = [
  { label: 'All Time', value: 'all' },
  { label: 'Last 7 Days', value: '7d' },
  { label: 'Last 30 Days', value: '30d' },
  { label: 'Last 90 Days', value: '90d' },
  { label: 'This Year', value: 'year' },
  { label: 'Custom Range', value: 'custom' },
]

// Modal
const detailsModalOpen = ref(false)
const selectedOrder = ref<any>(null)

// Fetch purchase reports directly from database API
const fetchReports = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: any = {
      period: selectedPeriod.value,
      status: selectedStatus.value,
      payment_status: selectedPaymentStatus.value,
      search: searchQuery.value || undefined
    }

    if (selectedPeriod.value === 'custom' && customFromDate.value && customToDate.value) {
      params.from_date = customFromDate.value
      params.to_date = customToDate.value
    }

    const res = await buyerAPI.getPurchaseReports(params)
    if (res.data && res.data.success) {
      const data = res.data.data
      orders.value = data.orders || []
      summary.value = data.summary || {
        total_spend: 0,
        total_orders: 0,
        completed_orders: 0,
        pending_orders: 0,
        cancelled_orders: 0,
        average_order_value: 0,
        total_produce_units: 0,
        currency: 'ETB'
      }
      categories.value = data.categories || []
      paymentMethods.value = data.payment_methods || []
      monthlyTrend.value = data.monthly_trend || []
    } else {
      orders.value = []
    }
  } catch (err: any) {
    console.error('Failed to load purchase reports:', err)
    errorMessage.value = err.response?.data?.message || 'Failed to fetch purchase records from the database.'
    orders.value = []
  } finally {
    loading.value = false
  }
}

const onFilterChange = () => {
  fetchReports()
}

const setPeriod = (p: string) => {
  selectedPeriod.value = p
  fetchReports()
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedPeriod.value = 'all'
  selectedStatus.value = 'all'
  selectedPaymentStatus.value = 'all'
  customFromDate.value = ''
  customToDate.value = ''
  sortBy.value = 'newest'
  fetchReports()
}

// Client-side sorting on retrieved orders
const filteredOrders = computed(() => {
  let list = [...orders.value]
  if (sortBy.value === 'amount_high') {
    list.sort((a, b) => (Number(b.grand_total) || 0) - (Number(a.grand_total) || 0))
  } else if (sortBy.value === 'amount_low') {
    list.sort((a, b) => (Number(a.grand_total) || 0) - (Number(b.grand_total) || 0))
  } else {
    // Newest
    list.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
  }
  return list
})

const filteredTotalAmount = computed(() => {
  return filteredOrders.value.reduce((acc, curr) => acc + (Number(curr.grand_total) || 0), 0)
})

// Timeline chart height calculator
const getBarHeight = (spend: number) => {
  const max = Math.max(...monthlyTrend.value.map(i => i.spend), 1)
  if (spend <= 0) return '4px'
  const pct = Math.max(8, Math.round((spend / max) * 100))
  return pct + '%'
}

// Formatters
const formatCurrency = (val: number | string | undefined) => {
  if (val === undefined || val === null) return '0.00'
  return Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatNumber = (val: number | string | undefined) => {
  if (val === undefined || val === null) return '0'
  return Number(val).toLocaleString('en-US')
}

const getStatusBadgeClass = (status: string) => {
  switch (status) {
    case 'delivered':
      return 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
    case 'processing':
    case 'shipped':
      return 'bg-blue-500/10 text-blue-500 border border-blue-500/20'
    case 'pending':
      return 'bg-amber-500/10 text-amber-500 border border-amber-500/20'
    case 'cancelled':
      return 'bg-rose-500/10 text-rose-500 border border-rose-500/20'
    default:
      return 'bg-slate-500/10 text-slate-500 border border-slate-500/20'
  }
}

// Modal
const openDetailsModal = (order: any) => {
  selectedOrder.value = order
  detailsModalOpen.value = true
}

// Export CSV
const exportCSV = () => {
  if (orders.value.length === 0) return

  const headers = ['Order Number', 'Date', 'Seller', 'Items', 'Quantity', 'Unit', 'Payment Method', 'Payment Status', 'Order Status', 'Grand Total (ETB)']
  const rows = filteredOrders.value.map(o => [
    `"${o.order_number}"`,
    `"${o.date_formatted}"`,
    `"${o.seller_name}"`,
    `"${(o.items_summary || '').replace(/"/g, '""')}"`,
    o.total_quantity,
    `"${o.quantity_unit}"`,
    `"${o.payment_method}"`,
    `"${o.payment_status}"`,
    `"${o.status}"`,
    o.grand_total
  ])

  const csvContent = [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  link.setAttribute('download', `agri_purchase_report_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

// Print trigger
const triggerPrint = () => {
  window.print()
}

onMounted(() => {
  fetchReports()
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

@media print {
  /* Hide sidebars and controls when printing */
  .fixed, button, select, input {
    display: none !important;
  }
  main {
    margin-left: 0 !important;
    padding: 0 !important;
  }
}
</style>
