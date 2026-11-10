<template>
  <div class="sales-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="sales-container">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h1>My Sales & Analytics</h1>
          <p>Track verified Chapa Escrow revenue, completed sales, and produce performance</p>
        </div>
        <div class="header-actions">
          <button @click="exportData" class="btn-export" title="Export verified sales to CSV">
            <Download size="16" />
            <span>Export CSV</span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading verified sales and escrow analytics...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchSalesData" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="sales-content p-6 max-w-7xl mx-auto w-full">
        
        <!-- Top Summary KPI Cards (Tailwind CSS) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
          <!-- TOTAL REVENUE -->
          <div :class="['rounded-2xl p-5 border transition-all duration-200 hover:shadow-md relative overflow-hidden group', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200/80 shadow-xs']">
            <div class="flex items-center justify-between mb-3">
              <span :class="['text-xs font-bold uppercase tracking-wider', isDark ? 'text-slate-400' : 'text-slate-500']">Total Revenue</span>
              <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                <Banknote class="w-5 h-5" />
              </div>
            </div>
            <div class="flex items-baseline gap-1.5 mb-1">
              <span class="text-xs font-bold text-emerald-500">ETB</span>
              <h2 :class="['text-2xl font-black tracking-tight leading-none', isDark ? 'text-white' : 'text-slate-900']">
                {{ formatNumber(summaryData.totalRevenue) }}
              </h2>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400 mt-2">
              <ShieldCheck class="w-3.5 h-3.5 shrink-0" />
              <span>Delivered & Paid (Chapa Escrow)</span>
            </div>
          </div>

          <!-- TOTAL SALES -->
          <div :class="['rounded-2xl p-5 border transition-all duration-200 hover:shadow-md relative overflow-hidden group', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200/80 shadow-xs']">
            <div class="flex items-center justify-between mb-3">
              <span :class="['text-xs font-bold uppercase tracking-wider', isDark ? 'text-slate-400' : 'text-slate-500']">Total Sales</span>
              <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0">
                <ShoppingCart class="w-5 h-5" />
              </div>
            </div>
            <div class="flex items-baseline gap-1.5 mb-1">
              <h2 :class="['text-2xl font-black tracking-tight leading-none', isDark ? 'text-white' : 'text-slate-900']">
                {{ summaryData.totalSales }}
              </h2>
              <span :class="['text-xs font-medium', isDark ? 'text-slate-400' : 'text-slate-500']">orders</span>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] font-medium text-blue-600 dark:text-blue-400 mt-2">
              <CheckCircle2 class="w-3.5 h-3.5 shrink-0" />
              <span>Completed & Fulfilled Orders</span>
            </div>
          </div>

          <!-- AVERAGE ORDER -->
          <div :class="['rounded-2xl p-5 border transition-all duration-200 hover:shadow-md relative overflow-hidden group', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200/80 shadow-xs']">
            <div class="flex items-center justify-between mb-3">
              <span :class="['text-xs font-bold uppercase tracking-wider', isDark ? 'text-slate-400' : 'text-slate-500']">Average Order</span>
              <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center shrink-0">
                <BarChart3 class="w-5 h-5" />
              </div>
            </div>
            <div class="flex items-baseline gap-1.5 mb-1">
              <span class="text-xs font-bold text-purple-500">ETB</span>
              <h2 :class="['text-2xl font-black tracking-tight leading-none', isDark ? 'text-white' : 'text-slate-900']">
                {{ formatNumber(summaryData.averageOrder) }}
              </h2>
            </div>
            <div :class="['flex items-center gap-1.5 text-[11px] font-medium mt-2', isDark ? 'text-slate-400' : 'text-slate-500']">
              <TrendingUp class="w-3.5 h-3.5 text-purple-500 shrink-0" />
              <span>Per delivered transaction</span>
            </div>
          </div>

          <!-- PENDING ORDERS -->
          <div :class="['rounded-2xl p-5 border transition-all duration-200 hover:shadow-md relative overflow-hidden group', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200/80 shadow-xs']">
            <div class="flex items-center justify-between mb-3">
              <span :class="['text-xs font-bold uppercase tracking-wider', isDark ? 'text-slate-400' : 'text-slate-500']">Pending Orders</span>
              <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                <Clock class="w-5 h-5" />
              </div>
            </div>
            <div class="flex items-baseline gap-1.5 mb-1">
              <h2 :class="['text-2xl font-black tracking-tight leading-none', isDark ? 'text-white' : 'text-slate-900']">
                {{ summaryData.pendingOrders }}
              </h2>
              <span :class="['text-xs font-medium', isDark ? 'text-slate-400' : 'text-slate-500']">in transit / review</span>
            </div>
            <div class="flex items-center justify-between text-[11px] font-semibold mt-2">
              <span class="text-amber-600 dark:text-amber-400">Awaiting fulfillment</span>
              <router-link to="/farmer/customer-orders" class="text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 flex items-center gap-0.5 hover:underline">
                Manage &rarr;
              </router-link>
            </div>
          </div>
        </div>

        <!-- Charts Section (Monthly Revenue Trend & Sales by Product) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Monthly Revenue Trend (Strictly Synchronized with Delivered & Paid Orders) -->
          <div :class="['rounded-2xl p-6 border shadow-xs flex flex-col justify-between transition-all', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 :class="['text-base font-black tracking-tight m-0', isDark ? 'text-slate-100' : 'text-slate-900']">
                  Monthly Revenue Trend
                </h3>
                <p :class="['text-xs m-0 mt-0.5', isDark ? 'text-slate-400' : 'text-slate-500']">
                  Strictly synchronized with completed & paid Chapa Escrow payouts
                </p>
              </div>
              <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                Last 6 Months
              </span>
            </div>
            <div class="h-64 sm:h-72 w-full relative">
              <Line v-if="verifiedSales.length > 0" :data="lineChartData" :options="lineChartOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center text-slate-400 text-xs">
                <Banknote class="w-8 h-8 text-slate-400 mb-2 opacity-40" />
                <span>No verified payouts recorded yet</span>
              </div>
            </div>
          </div>

          <!-- Sales by Product (Donut Chart) -->
          <div :class="['rounded-2xl p-6 border shadow-xs flex flex-col justify-between transition-all', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 :class="['text-base font-black tracking-tight m-0', isDark ? 'text-slate-100' : 'text-slate-900']">
                  Sales by Produce
                </h3>
                <p :class="['text-xs m-0 mt-0.5', isDark ? 'text-slate-400' : 'text-slate-500']">
                  Produce volume distribution & percentage shares
                </p>
              </div>
              <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-500/10 text-blue-500 border border-blue-500/20">
                Volume Share
              </span>
            </div>
            <div class="h-64 sm:h-72 w-full relative flex items-center justify-center">
              <Doughnut v-if="verifiedSales.length > 0" :data="donutChartData" :options="donutChartOptions" />
              <div v-else class="h-full flex flex-col items-center justify-center text-slate-400 text-xs">
                <Package class="w-8 h-8 text-slate-400 mb-2 opacity-40" />
                <span>No product distribution data</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Professional Inline Filters (Between Charts and Recent Sales) -->
        <div :class="['rounded-2xl border p-5 mb-8 transition-all shadow-xs', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
          <!-- Top Row: Title, Record Count & Clear Action -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
              <div :class="['w-8 h-8 rounded-lg flex items-center justify-center shrink-0', isDark ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
                <SlidersHorizontal class="w-4 h-4" />
              </div>
              <div>
                <h3 :class="['text-sm font-bold tracking-tight m-0', isDark ? 'text-slate-100' : 'text-slate-900']">
                  Filter Sales Transactions
                </h3>
                <span class="text-[11px] text-slate-400 font-medium">
                  Filter verified delivered payouts, commodities, and date ranges
                </span>
              </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
              <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                {{ filteredSales.length }} {{ filteredSales.length === 1 ? 'Transaction' : 'Transactions' }} Found
              </span>
              <button
                v-if="hasActiveFilters"
                @click="clearFilters"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all cursor-pointer"
              >
                <RotateCcw class="w-3.5 h-3.5" />
                <span>Reset</span>
              </button>
            </div>
          </div>

          <!-- Controls Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            <!-- Search Input -->
            <div class="relative">
              <label :class="['block text-[11px] font-bold uppercase tracking-wider mb-1.5', isDark ? 'text-slate-400' : 'text-slate-500']">
                Search
              </label>
              <div class="relative">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Order ID, customer, produce..."
                  :class="['w-full pl-9 pr-3.5 py-2.5 rounded-xl text-xs border font-medium transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100 placeholder:text-slate-500' : 'bg-slate-50 border-slate-200 text-slate-900 placeholder:text-slate-400']"
                />
              </div>
            </div>

            <!-- Date Range Selector -->
            <div>
              <label :class="['block text-[11px] font-bold uppercase tracking-wider mb-1.5', isDark ? 'text-slate-400' : 'text-slate-500']">
                Date Timeline
              </label>
              <div class="relative">
                <Calendar class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                <select
                  v-model="filters.dateRange"
                  :class="['w-full pl-9 pr-8 py-2.5 rounded-xl text-xs border font-medium transition-all appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-200 text-slate-900']"
                >
                  <option value="all">All Time</option>
                  <option value="today">Today</option>
                  <option value="week">Past 7 Days</option>
                  <option value="month">This Month</option>
                  <option value="quarter">This Quarter (90d)</option>
                  <option value="year">Past 12 Months</option>
                </select>
                <ChevronDown class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
              </div>
            </div>

            <!-- Produce / Product Selector -->
            <div>
              <label :class="['block text-[11px] font-bold uppercase tracking-wider mb-1.5', isDark ? 'text-slate-400' : 'text-slate-500']">
                Produce / Commodity
              </label>
              <div class="relative">
                <Package class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                <select
                  v-model="filters.product"
                  :class="['w-full pl-9 pr-8 py-2.5 rounded-xl text-xs border font-medium transition-all appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-200 text-slate-900']"
                >
                  <option value="">All Commodities</option>
                  <option v-for="prod in availableProducts" :key="prod.id" :value="prod.name">
                    {{ prod.name }}
                  </option>
                </select>
                <ChevronDown class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
              </div>
            </div>

            <!-- Escrow Release Status Selector -->
            <div>
              <label :class="['block text-[11px] font-bold uppercase tracking-wider mb-1.5', isDark ? 'text-slate-400' : 'text-slate-500']">
                Payout / Settlement Status
              </label>
              <div class="relative">
                <CheckCircle2 class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                <select
                  v-model="filters.payoutStatus"
                  :class="['w-full pl-9 pr-8 py-2.5 rounded-xl text-xs border font-medium transition-all appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-200 text-slate-900']"
                >
                  <option value="">All Verified Transactions</option>
                  <option value="disbursed">Chapa Escrow Disbursed</option>
                  <option value="delivered">Delivered & Verified</option>
                </select>
                <ChevronDown class="w-4 h-4 text-slate-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Sales & Transactions Table (Strict Data Separation: Delivered & Paid Only) -->
        <div :class="['rounded-2xl border shadow-xs overflow-hidden transition-all mb-8', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
          <!-- Table Header -->
          <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <div class="flex items-center gap-2">
                <h3 :class="['text-base font-black tracking-tight m-0', isDark ? 'text-slate-100' : 'text-slate-900']">
                  Recent Sales & Transactions
                </h3>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                  100% Synced With Revenue
                </span>
              </div>
              <p :class="['text-xs m-0 mt-0.5', isDark ? 'text-slate-400' : 'text-slate-500']">
                Verified sales ledger reflecting successful deliveries and completed Chapa Escrow disbursements
              </p>
            </div>

            <div class="flex items-center gap-3">
              <label :class="['text-xs font-semibold', isDark ? 'text-slate-400' : 'text-slate-500']">Rows:</label>
              <select
                v-model="itemsPerPage"
                :class="['px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-200 text-slate-700']"
              >
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
              </select>
            </div>
          </div>

          <!-- Table Content -->
          <div v-if="paginatedSales.length > 0" class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr :class="['border-b text-[11px] font-bold uppercase tracking-wider', isDark ? 'bg-[#0b101b] border-slate-800 text-slate-400' : 'bg-slate-50/80 border-slate-200 text-slate-500']">
                  <th class="py-3.5 px-4 font-bold">Order Ref</th>
                  <th class="py-3.5 px-4 font-bold">Produce</th>
                  <th class="py-3.5 px-4 font-bold">Quantity</th>
                  <th class="py-3.5 px-4 font-bold">Price / Unit</th>
                  <th class="py-3.5 px-4 font-bold">Total Payout</th>
                  <th class="py-3.5 px-4 font-bold">Customer</th>
                  <th class="py-3.5 px-4 font-bold">Escrow & Delivery</th>
                  <th class="py-3.5 px-4 font-bold">Date</th>
                  <th class="py-3.5 px-4 font-bold text-right">Actions</th>
                </tr>
              </thead>
              <tbody :class="isDark ? 'divide-y divide-slate-800/60' : 'divide-y divide-slate-100'">
                <tr
                  v-for="sale in paginatedSales"
                  :key="sale.uniqueKey"
                  :class="['transition-colors', isDark ? 'hover:bg-slate-800/40 text-slate-200' : 'hover:bg-slate-50/80 text-slate-800']"
                >
                  <!-- Order Ref -->
                  <td class="py-3.5 px-4 font-mono font-bold text-emerald-500">
                    {{ sale.id }}
                  </td>

                  <!-- Produce -->
                  <td class="py-3.5 px-4 font-semibold">
                    {{ sale.productName }}
                  </td>

                  <!-- Quantity -->
                  <td class="py-3.5 px-4">
                    <span class="font-medium">{{ sale.quantity }}</span>
                    <span :class="['ml-1 text-[11px]', isDark ? 'text-slate-400' : 'text-slate-500']">{{ sale.unit }}</span>
                  </td>

                  <!-- Price / Unit -->
                  <td class="py-3.5 px-4 font-mono">
                    ETB {{ formatNumber(sale.pricePerUnit) }}
                  </td>

                  <!-- Total Amount -->
                  <td class="py-3.5 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono font-bold text-xs bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                      ETB {{ formatNumber(sale.totalAmount) }}
                    </span>
                  </td>

                  <!-- Customer -->
                  <td class="py-3.5 px-4">
                    <div class="font-semibold leading-tight">{{ sale.buyerName }}</div>
                    <div v-if="sale.buyerPhone" :class="['text-[11px] font-mono mt-0.5', isDark ? 'text-slate-400' : 'text-slate-500']">
                      {{ sale.buyerPhone }}
                    </div>
                  </td>

                  <!-- Escrow & Delivery Badges -->
                  <td class="py-3.5 px-4">
                    <div class="flex flex-wrap items-center gap-1.5">
                      <!-- Dynamic Delivery Status Badge -->
                      <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border transition-all', getDeliveryBadgeClass(sale)]">
                        <component :is="getDeliveryBadgeIcon(sale)" class="w-3 h-3" />
                        <span>{{ getDeliveryBadgeText(sale) }}</span>
                      </span>
                      <!-- Dynamic Escrow / Settlement Status Badge -->
                      <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border transition-all', getEscrowBadgeClass(sale)]">
                        <component :is="getEscrowBadgeIcon(sale)" class="w-3 h-3" />
                        <span>{{ getEscrowBadgeText(sale) }}</span>
                      </span>
                    </div>
                  </td>

                  <!-- Date -->
                  <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                    {{ formatDate(sale.saleDate) }}
                  </td>

                  <!-- Actions -->
                  <td class="py-3.5 px-4 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end gap-1.5">
                      <button
                        @click="viewDetails(sale)"
                        :class="['p-1.5 rounded-lg border transition-all cursor-pointer', isDark ? 'border-slate-700 bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700' : 'border-slate-200 bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-100']"
                        title="View Transaction Details"
                      >
                        <Eye class="w-4 h-4" />
                      </button>
                      <button
                        @click="downloadInvoice(sale)"
                        :class="['p-1.5 rounded-lg border transition-all cursor-pointer', isDark ? 'border-emerald-800/40 bg-emerald-950/30 text-emerald-400 hover:bg-emerald-900/50' : 'border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-100']"
                        title="Download / Print Official Tax Invoice"
                      >
                        <Printer class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-else class="p-12 text-center flex flex-col items-center justify-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
              <ShoppingCart class="w-8 h-8 opacity-50" />
            </div>
            <h4 :class="['text-sm font-bold mb-1', isDark ? 'text-slate-200' : 'text-slate-800']">
              {{ hasActiveFilters ? 'No matching sales found' : 'No verified sales recorded yet' }}
            </h4>
            <p :class="['text-xs max-w-sm mb-4', isDark ? 'text-slate-400' : 'text-slate-500']">
              {{ hasActiveFilters ? 'Try adjusting your search criteria or resetting filters.' : 'Completed orders marked delivered and paid via Chapa Escrow will be logged here.' }}
            </p>
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-500 text-white hover:bg-emerald-600 transition-all cursor-pointer"
            >
              Reset Filters
            </button>
            <router-link
              v-else-if="summaryData.pendingOrders > 0"
              to="/farmer/customer-orders"
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-500 text-white hover:bg-emerald-600 transition-all cursor-pointer"
            >
              <span>Manage Pending Orders in Customer Orders</span>
              <span>&rarr;</span>
            </router-link>
          </div>

          <!-- Pagination -->
          <div v-if="filteredSales.length > 0" class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              :class="['inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border font-semibold transition-all', currentPage === 1 ? 'opacity-40 cursor-not-allowed border-transparent' : (isDark ? 'border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 cursor-pointer' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 cursor-pointer')]"
            >
              <ChevronLeft class="w-4 h-4" />
              Previous
            </button>

            <span :class="['font-medium', isDark ? 'text-slate-400' : 'text-slate-500']">
              Page <strong :class="isDark ? 'text-slate-200' : 'text-slate-800'">{{ currentPage }}</strong> of <strong :class="isDark ? 'text-slate-200' : 'text-slate-800'">{{ totalPages }}</strong>
            </span>

            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              :class="['inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border font-semibold transition-all', currentPage === totalPages ? 'opacity-40 cursor-not-allowed border-transparent' : (isDark ? 'border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700 cursor-pointer' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 cursor-pointer')]"
            >
              Next
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Quick View Modal (Actionable Controls) -->
        <div v-if="selectedSale" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" @click="selectedSale = null">
          <div :class="['w-full max-w-xl rounded-2xl border shadow-2xl overflow-hidden transition-all', isDark ? 'bg-[#131b2e] border-slate-700' : 'bg-white border-slate-200']" @click.stop>
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                  <FileText class="w-5 h-5" />
                </div>
                <div>
                  <h3 :class="['text-base font-black tracking-tight m-0', isDark ? 'text-slate-100' : 'text-slate-900']">
                    Sale Transaction & Escrow Details
                  </h3>
                  <span class="text-xs font-mono font-bold text-emerald-500">
                    {{ selectedSale.id }}
                  </span>
                </div>
              </div>
              <button @click="selectedSale = null" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition-all cursor-pointer">
                <X class="w-5 h-5" />
              </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5 text-xs max-h-[75vh] overflow-y-auto">
              <!-- Escrow Status Card -->
              <div :class="['p-4 rounded-xl border flex items-center justify-between', selectedSale.escrowStatus === 'disbursed' ? (isDark ? 'bg-emerald-950/20 border-emerald-800/40 text-emerald-300' : 'bg-emerald-50/80 border-emerald-200 text-emerald-800') : (isDark ? 'bg-blue-950/20 border-blue-800/40 text-blue-300' : 'bg-blue-50/80 border-blue-200 text-blue-800')]">
                <div class="flex items-center gap-3">
                  <component :is="selectedSale.escrowStatus === 'disbursed' ? CheckCircle2 : ShieldCheck" class="w-6 h-6 text-emerald-500 shrink-0" />
                  <div>
                    <div class="font-bold text-sm leading-tight">
                      {{ selectedSale.escrowStatus === 'disbursed' ? 'Chapa Escrow Disbursed' : (selectedSale.escrowStatus === 'held' ? 'Chapa Escrow Held' : 'Payment Status: ' + (selectedSale.paymentStatus ? selectedSale.paymentStatus.toUpperCase() : 'PENDING')) }}
                    </div>
                    <div class="text-[11px] opacity-80 mt-0.5">
                      {{ selectedSale.escrowStatus === 'disbursed' ? 'Funds released directly to farmer digital wallet' : 'Funds safely secured in neutral escrow custody' }}
                    </div>
                  </div>
                </div>
                <span :class="['px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider', selectedSale.escrowStatus === 'disbursed' ? 'bg-emerald-500 text-white' : 'bg-blue-500 text-white']">
                  {{ selectedSale.escrowStatus === 'disbursed' ? 'DISBURSED' : (selectedSale.paymentStatus === 'paid' ? 'PAID' : (selectedSale.paymentStatus || 'PENDING').toUpperCase()) }}
                </span>
              </div>

              <!-- Transaction Information Grid -->
              <div class="grid grid-cols-2 gap-4">
                <div :class="['p-3.5 rounded-xl border', isDark ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                  <span :class="['block text-[10px] uppercase font-bold tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">Buyer / Customer</span>
                  <div :class="['font-bold text-sm', isDark ? 'text-white' : 'text-slate-900']">{{ selectedSale.buyerName }}</div>
                  <div v-if="selectedSale.buyerPhone" class="text-slate-400 font-mono text-[11px] mt-0.5">{{ selectedSale.buyerPhone }}</div>
                </div>

                <div :class="['p-3.5 rounded-xl border', isDark ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                  <span :class="['block text-[10px] uppercase font-bold tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">Settlement Date</span>
                  <div :class="['font-bold text-sm', isDark ? 'text-white' : 'text-slate-900']">{{ formatDate(selectedSale.saleDate) }}</div>
                  <div class="text-slate-400 text-[11px] mt-0.5">Quality verified & delivered</div>
                </div>

                <div :class="['p-3.5 rounded-xl border', isDark ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                  <span :class="['block text-[10px] uppercase font-bold tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">Warehouse / Destination</span>
                  <div :class="['font-bold text-xs', isDark ? 'text-white' : 'text-slate-900']">{{ selectedSale.deliveryAddress || 'Kality Central Grain Warehouse' }}</div>
                  <div class="text-slate-400 text-[11px] mt-0.5">Inspected & Fulfilled</div>
                </div>

                <div :class="['p-3.5 rounded-xl border', isDark ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                  <span :class="['block text-[10px] uppercase font-bold tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">Escrow Payout Ref</span>
                  <div class="font-mono font-bold text-xs text-blue-500 truncate">{{ selectedSale.payoutReference }}</div>
                  <div class="text-slate-400 text-[11px] mt-0.5">Chapa Settlement ID</div>
                </div>
              </div>

              <!-- Produce & Items Details Table -->
              <div :class="['rounded-xl border overflow-hidden', isDark ? 'border-slate-800' : 'border-slate-200']">
                <div :class="['p-3 border-b text-[11px] font-bold uppercase tracking-wider', isDark ? 'bg-[#0b101b] border-slate-800 text-slate-400' : 'bg-slate-50 border-slate-200 text-slate-600']">
                  Produce Item Breakdown
                </div>
                <div class="p-4 flex items-center justify-between">
                  <div>
                    <div :class="['font-bold text-sm', isDark ? 'text-white' : 'text-slate-900']">
                      {{ selectedSale.productName }}
                    </div>
                    <div class="text-slate-400 text-xs mt-0.5">
                      Quantity: {{ selectedSale.quantity }} {{ selectedSale.unit }} &times; ETB {{ formatNumber(selectedSale.pricePerUnit) }}
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="font-mono font-black text-base text-emerald-500">
                      ETB {{ formatNumber(selectedSale.totalAmount) }}
                    </div>
                    <span class="text-[10px] font-bold text-slate-400">Net Payout</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
              <button
                @click="selectedSale = null"
                :class="['px-4 py-2.5 rounded-xl font-semibold border transition-all cursor-pointer', isDark ? 'border-slate-700 bg-slate-800 text-slate-300 hover:bg-slate-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50']"
              >
                Close
              </button>
              <button
                @click="downloadInvoice(selectedSale)"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold bg-emerald-500 hover:bg-emerald-600 text-white shadow-sm transition-all cursor-pointer"
              >
                <Printer class="w-4 h-4" />
                <span>Print / Download Tax Invoice</span>
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useTheme } from '@/composables/useTheme'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import { Line, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
  ArcElement
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
  ArcElement
)
import { farmerAPI } from '@/api/farmer'
import {
  TrendingUp, ShoppingCart, BarChart3, Clock, Banknote,
  AlertCircle, RotateCcw, Download, X, Eye, ChevronLeft,
  ChevronRight, SlidersHorizontal, Search, Calendar, Package, CheckCircle2, ChevronDown,
  ShieldCheck, Printer, FileText, Truck, Check, Lock
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()
const { isDark, isLight } = useTheme()

// State
const loading = ref(false)
const error = ref(null)
const selectedSale = ref(null)
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Data
const allRawOrders = ref([])
const verifiedSales = ref([])
const availableProducts = ref([])
const summaryData = ref({
  totalRevenue: 0,
  totalSales: 0,
  averageOrder: 0,
  pendingOrders: 0
})

// Filters
const filters = ref({
  search: '',
  dateRange: 'all',
  product: '',
  payoutStatus: ''
})

const hasActiveFilters = computed(() => {
  return Boolean(
    filters.value.search ||
    filters.value.product ||
    filters.value.payoutStatus ||
    (filters.value.dateRange && filters.value.dateRange !== 'all')
  )
})

// Charts Computed Data (Strictly synchronized with verified sales payouts)
const revenueTrendData = computed(() => {
  const months = []
  const now = new Date()
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    const label = d.toLocaleDateString('en-US', { month: 'short' })
    months.push({ key, label, revenue: 0, count: 0 })
  }

  // Iterate strictly through verified sales
  verifiedSales.value.forEach(s => {
    if (!s.saleDate) return
    const d = new Date(s.saleDate)
    const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    const m = months.find(item => item.key === key)
    if (m) {
      m.revenue += Number(s.totalAmount || 0)
      m.count += 1
    }
  })

  return months
})

const productSalesData = computed(() => {
  const map = new Map()
  let grandTotal = 0

  verifiedSales.value.forEach(s => {
    const name = s.productName || 'Agricultural Produce'
    const amt = Number(s.totalAmount || 0)
    const qty = Number(s.quantity || 0)
    grandTotal += amt

    if (!map.has(name)) {
      map.set(name, { name, revenue: 0, quantity: 0, unit: s.unit || 'kg' })
    }
    const cur = map.get(name)
    cur.revenue += amt
    cur.quantity += qty
  })

  return Array.from(map.values())
    .map(p => ({
      ...p,
      percentage: grandTotal > 0 ? Math.round((p.revenue / grandTotal) * 100) : 0
    }))
    .sort((a, b) => b.revenue - a.revenue)
    .slice(0, 6)
})

// Line Chart Config (Monthly Revenue Trend)
const lineChartData = computed(() => {
  const labels = revenueTrendData.value.map(m => m.label)
  const data = revenueTrendData.value.map(m => m.revenue)

  return {
    labels,
    datasets: [
      {
        label: 'Verified Revenue (ETB)',
        data,
        borderColor: '#10b981',
        backgroundColor: (context) => {
          const chart = context.chart
          const { ctx, chartArea } = chart
          if (!chartArea) return 'rgba(16, 185, 129, 0.15)'
          const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom)
          gradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)')
          gradient.addColorStop(1, 'rgba(16, 185, 129, 0.01)')
          return gradient
        },
        fill: true,
        tension: 0.35,
        borderWidth: 2.5,
        pointBackgroundColor: '#10b981',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4.5,
        pointHoverRadius: 7,
        pointHoverBackgroundColor: '#059669',
        pointHoverBorderColor: '#ffffff',
        pointHoverBorderWidth: 2.5,
      }
    ]
  }
})

const lineChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
  },
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: isDark.value ? '#1e293b' : '#0f172a',
      titleColor: '#f8fafc',
      bodyColor: '#34d399',
      borderColor: isDark.value ? '#334155' : '#475569',
      borderWidth: 1,
      padding: 12,
      cornerRadius: 10,
      displayColors: false,
      callbacks: {
        title: (items) => items && items[0] ? `${items[0].label} Revenue` : '',
        label: (item) => `ETB ${formatNumber(item.parsed.y)}`
      }
    }
  },
  scales: {
    x: {
      grid: {
        display: false
      },
      ticks: {
        color: isDark.value ? '#94a3b8' : '#64748b',
        font: { family: 'inherit', size: 11, weight: '600' }
      }
    },
    y: {
      beginAtZero: true,
      grid: {
        color: isDark.value ? 'rgba(148, 163, 184, 0.08)' : 'rgba(226, 232, 240, 0.7)',
      },
      ticks: {
        color: isDark.value ? '#94a3b8' : '#64748b',
        font: { family: 'inherit', size: 11 },
        callback: (value) => {
          if (value >= 1000000) return `ETB ${(value / 1000000).toFixed(1)}M`
          if (value >= 1000) return `ETB ${(value / 1000).toFixed(0)}k`
          return `ETB ${value}`
        }
      }
    }
  }
}))

// Donut Chart Config (Sales by Product)
const donutPalette = [
  '#10b981', // emerald
  '#3b82f6', // blue
  '#f59e0b', // amber
  '#8b5cf6', // purple
  '#ec4899', // pink
  '#06b6d4', // cyan
]

const donutChartData = computed(() => {
  const items = productSalesData.value
  if (!items || items.length === 0) {
    return {
      labels: ['No verified sales'],
      datasets: [
        {
          data: [1],
          backgroundColor: [isDark.value ? '#1e293b' : '#e2e8f0'],
          borderWidth: 0
        }
      ]
    }
  }

  const labels = items.map(p => p.name)
  const data = items.map(p => p.revenue)
  const bgColors = items.map((_, idx) => donutPalette[idx % donutPalette.length])

  return {
    labels,
    datasets: [
      {
        data,
        backgroundColor: bgColors,
        borderColor: isDark.value ? '#131b2e' : '#ffffff',
        borderWidth: 3,
        hoverOffset: 6
      }
    ]
  }
})

const donutChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  cutout: '68%',
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        color: isDark.value ? '#cbd5e1' : '#334155',
        font: { family: 'inherit', size: 11, weight: '600' },
        padding: 14,
        usePointStyle: true,
        pointStyle: 'circle'
      }
    },
    tooltip: {
      backgroundColor: isDark.value ? '#1e293b' : '#0f172a',
      titleColor: '#f8fafc',
      bodyColor: '#38bdf8',
      borderColor: isDark.value ? '#334155' : '#475569',
      borderWidth: 1,
      padding: 12,
      cornerRadius: 10,
      callbacks: {
        label: (item) => {
          const val = item.parsed
          const total = item.dataset.data.reduce((a, b) => a + b, 0)
          const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0
          return ` ETB ${formatNumber(val)} (${pct}%)`
        }
      }
    }
  }
}))

// Computed Filtered Sales (Strictly on verifiedSales)
const filteredSales = computed(() => {
  let result = verifiedSales.value

  // Search filter
  if (filters.value.search) {
    const q = filters.value.search.toLowerCase().trim()
    result = result.filter(s =>
      (s.id && s.id.toLowerCase().includes(q)) ||
      (s.buyerName && s.buyerName.toLowerCase().includes(q)) ||
      (s.productName && s.productName.toLowerCase().includes(q)) ||
      (s.payoutReference && s.payoutReference.toLowerCase().includes(q))
    )
  }

  // Produce filter
  if (filters.value.product) {
    result = result.filter(s => s.productName === filters.value.product)
  }

  // Payout / Settlement status filter
  if (filters.value.payoutStatus) {
    if (filters.value.payoutStatus === 'disbursed') {
      result = result.filter(s => s.escrowStatus === 'disbursed')
    } else if (filters.value.payoutStatus === 'delivered') {
      result = result.filter(s => s.status === 'delivered' || s.status === 'completed')
    }
  }

  // Date Range filter
  if (filters.value.dateRange && filters.value.dateRange !== 'all') {
    const now = new Date()
    result = result.filter(s => {
      if (!s.saleDate) return false
      const saleDate = new Date(s.saleDate)
      switch (filters.value.dateRange) {
        case 'today':
          return saleDate.toDateString() === now.toDateString()
        case 'week': {
          const d = new Date(now)
          d.setDate(now.getDate() - 7)
          return saleDate >= d
        }
        case 'month': {
          const d = new Date(now)
          d.setMonth(now.getMonth() - 1)
          return saleDate >= d
        }
        case 'quarter': {
          const d = new Date(now)
          d.setMonth(now.getMonth() - 3)
          return saleDate >= d
        }
        case 'year': {
          const d = new Date(now)
          d.setFullYear(now.getFullYear() - 1)
          return saleDate >= d
        }
        default:
          return true
      }
    })
  }

  return result
})

const totalPages = computed(() => {
  return Math.ceil(filteredSales.value.length / parseInt(itemsPerPage.value)) || 1
})

const paginatedSales = computed(() => {
  const start = (currentPage.value - 1) * parseInt(itemsPerPage.value)
  const end = start + parseInt(itemsPerPage.value)
  return filteredSales.value.slice(start, end)
})

// Methods
// Dynamic Badge Helpers matching actual database fulfillment & escrow states
const getDeliveryBadgeText = (sale) => {
  const status = (sale?.status || '').toLowerCase()
  if (status === 'delivered') return 'Delivered'
  if (status === 'completed') return 'Completed'
  if (status === 'shipped' || status === 'in_transit') return 'In Transit'
  if (status === 'confirmed') return 'Confirmed'
  if (status === 'processing') return 'Processing'
  if (status === 'pending' || status === 'placed') return 'Pending'
  if (status === 'cancelled') return 'Cancelled'
  return status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Delivered'
}

const getDeliveryBadgeClass = (sale) => {
  const status = (sale?.status || '').toLowerCase()
  if (status === 'delivered' || status === 'completed') {
    return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
  }
  if (status === 'shipped' || status === 'in_transit') {
    return 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20'
  }
  if (status === 'confirmed' || status === 'processing') {
    return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'
  }
  if (status === 'pending' || status === 'placed') {
    return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'
  }
  if (status === 'cancelled') {
    return 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20'
  }
  return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
}

const getDeliveryBadgeIcon = (sale) => {
  const status = (sale?.status || '').toLowerCase()
  if (status === 'delivered' || status === 'completed') return CheckCircle2
  if (status === 'shipped' || status === 'in_transit') return Truck
  if (status === 'cancelled') return AlertCircle
  return Clock
}

const getEscrowBadgeText = (sale) => {
  const escrow = (sale?.escrowStatus || '').toLowerCase()
  const payment = (sale?.paymentStatus || '').toLowerCase()

  if (escrow === 'disbursed') return 'Chapa Disbursed'
  if (escrow === 'held') return 'Escrow Held'
  if (payment === 'paid' || payment === 'completed') return 'Chapa Paid'
  if (payment === 'pending') return 'Payment Pending'
  if (payment === 'failed') return 'Payment Failed'
  return payment ? payment.charAt(0).toUpperCase() + payment.slice(1) : 'Chapa Paid'
}

const getEscrowBadgeClass = (sale) => {
  const escrow = (sale?.escrowStatus || '').toLowerCase()
  const payment = (sale?.paymentStatus || '').toLowerCase()

  if (escrow === 'disbursed') {
    return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
  }
  if (escrow === 'held') {
    return 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20'
  }
  if (payment === 'paid' || payment === 'completed') {
    return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'
  }
  if (payment === 'pending') {
    return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'
  }
  if (payment === 'failed') {
    return 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20'
  }
  return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20'
}

const getEscrowBadgeIcon = (sale) => {
  const escrow = (sale?.escrowStatus || '').toLowerCase()
  const payment = (sale?.paymentStatus || '').toLowerCase()

  if (escrow === 'disbursed') return CheckCircle2
  if (escrow === 'held' || payment === 'paid' || payment === 'completed') return ShieldCheck
  if (payment === 'failed') return AlertCircle
  return Clock
}

const fetchSalesData = async () => {
  loading.value = true
  error.value = null
  try {
    const [ordersRes, productsRes] = await Promise.all([
      farmerAPI.getOrders({ limit: 100 }),
      farmerAPI.getProducts()
    ])

    const rawOrders = ordersRes.data?.data || ordersRes.data || []
    const rawProducts = productsRes.data?.data || productsRes.data || []

    allRawOrders.value = Array.isArray(rawOrders) ? rawOrders : []

    // Populate available commodities list
    availableProducts.value = (Array.isArray(rawProducts) ? rawProducts : []).map(p => ({
      id: p.id,
      name: p.name
    }))

    const verifiedList = []
    let pendingCount = 0
    const completedOrderIds = new Set()

    allRawOrders.value.forEach((order, idx) => {
      const status = (order.status || '').toLowerCase()
      const paymentStatus = (order.payment_status || order.payment?.status || '').toLowerCase()
      const escrowStatus = (order.escrow_status || order.payment?.escrow_status || '').toLowerCase()

      // Track pending or active orders awaiting fulfillment, logistics assignment, or in transit
      const isPendingOrActive = [
        'pending',
        'placed',
        'confirmed',
        'processing',
        'awaiting_transport',
        'awaiting_delivery',
        'shipped',
        'in_transit'
      ].includes(status)

      if (isPendingOrActive) {
        pendingCount += 1
      }

      // STRICT STATE-MATCHING RULE FOR SALES LEDGER & REVENUE:
      // The sales ledger and completed revenue MUST strictly reflect verified successful deliveries
      // and completed transactions. Orders currently in Pending, Placed, Confirmed, or Awaiting Transport
      // states must NEVER be prematurely injected into the completed sales history.
      const isDeliveredAndCompleted = (status === 'delivered' || status === 'completed') &&
        status !== 'cancelled' &&
        status !== 'rejected' &&
        paymentStatus !== 'failed' &&
        paymentStatus !== 'refunded'

      if (!isDeliveredAndCompleted) return

      completedOrderIds.add(order.id)
      const buyerName = order.buyer?.name || order.buyer?.phone || 'Customer'
      const buyerPhone = order.buyer?.phone || order.delivery_phone || order.phone || ''
      const orderNum = order.order_number || `ORD-${String(order.id).padStart(4, '0')}`
      const deliveryAddress = order.delivery_address || order.shipping_address || 'Kality Central Grain Warehouse'
      const payoutRef = order.payout_reference || order.payment_reference || order.payment?.transaction_id || `CHAPA-ESC-${order.id}`
      const saleDate = order.delivered_at || order.updated_at || order.created_at

      // True database fulfillment & escrow states (Zero hardcoding)
      const realStatus = (order.status || 'delivered').toLowerCase()
      const realPaymentStatus = (order.payment_status || order.payment?.status || 'paid').toLowerCase()
      const realEscrowStatus = (order.escrow_status || order.payment?.escrow_status || (realPaymentStatus === 'paid' ? 'disbursed' : 'pending')).toLowerCase()

      if (order.items && order.items.length > 0) {
        order.items.forEach((item, itemIdx) => {
          verifiedList.push({
            uniqueKey: `${order.id}-${item.id || itemIdx}`,
            id: orderNum,
            orderId: order.id,
            productName: item.product?.name || item.product_name || 'Agricultural Produce',
            productId: item.product_id,
            quantity: Number(item.quantity || 0),
            unit: item.product?.quantity_unit || item.product?.unit || item.unit || 'kg',
            pricePerUnit: Number(item.price || item.unit_price || 0),
            totalAmount: Number(item.subtotal || (item.quantity * item.price) || 0),
            buyerName,
            buyerPhone,
            deliveryAddress,
            saleDate,
            status: realStatus,
            paymentStatus: realPaymentStatus,
            escrowStatus: realEscrowStatus,
            payoutReference: payoutRef,
            rawOrder: order
          })
        })
      } else {
        const total = Number(order.grand_total || order.subtotal || 0)
        verifiedList.push({
          uniqueKey: `${order.id}-${idx}`,
          id: orderNum,
          orderId: order.id,
          productName: 'Agricultural Produce',
          productId: null,
          quantity: 1,
          unit: 'lot',
          pricePerUnit: total,
          totalAmount: total,
          buyerName,
          buyerPhone,
          deliveryAddress,
          saleDate,
          status: realStatus,
          paymentStatus: realPaymentStatus,
          escrowStatus: realEscrowStatus,
          payoutReference: payoutRef,
          rawOrder: order
        })
      }
    })

    verifiedSales.value = verifiedList

    // Compute KPI cards: Total Revenue strictly matches verified transactions
    const totalRev = verifiedList.reduce((sum, s) => sum + s.totalAmount, 0)
    const completedOrdersCount = completedOrderIds.size

    summaryData.value = {
      totalRevenue: totalRev,
      totalSales: completedOrdersCount,
      averageOrder: completedOrdersCount > 0 ? Math.round(totalRev / completedOrdersCount) : 0,
      pendingOrders: pendingCount
    }
  } catch (err) {
    console.error('Error fetching sales data:', err)
    error.value = 'Failed to load verified sales and escrow records. Please try again.'
  } finally {
    loading.value = false
  }
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const clearFilters = () => {
  filters.value = {
    search: '',
    dateRange: 'all',
    product: '',
    payoutStatus: ''
  }
  currentPage.value = 1
}

const viewDetails = (sale) => {
  selectedSale.value = sale
}

// Official Printable Invoice Generator
const downloadInvoice = (sale) => {
  // 1. Open dedicated print window
  const printWindow = window.open('', '_blank', 'width=800,height=900')
  if (printWindow) {
    const htmlContent = `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Invoice - ${sale.id}</title>
        <style>
          body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px; color: #1e293b; background: #fff; line-height: 1.5; }
          .header { display: flex; justify-content: space-between; border-bottom: 2px solid #10b981; padding-bottom: 20px; margin-bottom: 30px; }
          .title { font-size: 24px; font-weight: 900; color: #065f46; margin: 0; }
          .subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }
          .invoice-meta { text-align: right; }
          .meta-item { font-size: 13px; color: #475569; }
          .meta-item strong { color: #0f172a; }
          .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 30px; }
          .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; }
          .card h4 { margin: 0 0 8px; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
          .card p { margin: 4px 0; font-size: 13px; }
          table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
          th { background: #f1f5f9; padding: 12px 14px; font-size: 12px; text-transform: uppercase; text-align: left; color: #475569; border-bottom: 2px solid #cbd5e1; }
          td { padding: 12px 14px; font-size: 13px; border-bottom: 1px solid #e2e8f0; }
          .total-box { margin-left: auto; width: 300px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 16px; margin-bottom: 30px; }
          .total-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; color: #065f46; }
          .stamp { display: inline-block; padding: 8px 16px; border: 2px solid #10b981; border-radius: 8px; font-weight: 900; color: #047857; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
          .footer { text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        </style>
      </head>
      <body>
        <div class="header">
          <div>
            <h1 class="title">AgriTech Digital Farmer Exchange</h1>
            <div class="subtitle">Official Verified Sales & Escrow Settlement Tax Invoice</div>
          </div>
          <div class="invoice-meta">
            <div class="meta-item">Invoice #: <strong>${sale.id}</strong></div>
            <div class="meta-item">Settled: <strong>${formatDate(sale.saleDate)}</strong></div>
            <div class="meta-item">Chapa Ref: <strong>${sale.payoutReference}</strong></div>
          </div>
        </div>

        <div class="grid">
          <div class="card">
            <h4>Sold By (Farmer / Producer)</h4>
            <p><strong>${auth.user?.name || 'Verified Farmer Member'}</strong></p>
            <p>National Farmer Registry ID: ET-FARM-${auth.user?.id || '001'}</p>
            <p>Digital Wallet: Chapa Escrow Connected</p>
          </div>
          <div class="card">
            <h4>Billed To (Buyer / Customer)</h4>
            <p><strong>${sale.buyerName}</strong></p>
            <p>Phone: ${sale.buyerPhone || 'Registered Account'}</p>
            <p>Fulfillment Hub: ${sale.deliveryAddress}</p>
          </div>
        </div>

        <table>
          <thead>
            <tr>
              <th>Produce Item Description</th>
              <th>Quantity</th>
              <th>Unit Price</th>
              <th style="text-align: right;">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>${sale.productName}</strong></td>
              <td>${sale.quantity} ${sale.unit}</td>
              <td>ETB ${formatNumber(sale.pricePerUnit)}</td>
              <td style="text-align: right; font-weight: bold;">ETB ${formatNumber(sale.totalAmount)}</td>
            </tr>
          </tbody>
        </table>

        <div class="total-box">
          <div class="total-row">
            <span>Net Disbursed Revenue:</span>
            <span>ETB ${formatNumber(sale.totalAmount)}</span>
          </div>
        </div>

        <div style="margin-bottom: 30px;">
          <div class="stamp">✓ Chapa Escrow Verified & Disbursed</div>
        </div>

        <div class="footer">
          This is an electronically verified transaction certificate generated by AgriTech Platform.
        </div>
        <scr' + 'ipt>
          window.onload = function() {
            window.print();
          };
        </scr' + 'ipt>
      </body>
      </html>
    `
    printWindow.document.write(htmlContent)
    printWindow.document.close()
  }

  // 2. Also download text invoice file for offline records
  const invoiceText = [
    '==============================================================',
    '        AGRITECH PLATFORM - VERIFIED SALES INVOICE',
    '==============================================================',
    `Invoice Ref:       ${sale.id}`,
    `Settled Date:      ${formatDate(sale.saleDate)}`,
    `Customer:          ${sale.buyerName} (${sale.buyerPhone || 'N/A'})`,
    `Delivery Hub:      ${sale.deliveryAddress}`,
    `Payment Status:    Paid via Chapa Escrow`,
    `Escrow Payout Ref: ${sale.payoutReference}`,
    '--------------------------------------------------------------',
    `Produce Item:      ${sale.productName}`,
    `Quantity:          ${sale.quantity} ${sale.unit}`,
    `Unit Price:        ETB ${formatNumber(sale.pricePerUnit)}`,
    `Net Disbursed:     ETB ${formatNumber(sale.totalAmount)}`,
    '==============================================================',
    '  Status: VERIFIED & DISBURSED TO FARMER DIGITAL WALLET',
    '=============================================================='
  ].join('\n')

  const blob = new Blob([invoiceText], { type: 'text/plain;charset=utf-8;' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `Invoice-${sale.id}.txt`
  a.click()
}

// Export Verified Sales to CSV
const exportData = () => {
  const headers = ['Order Ref', 'Produce', 'Quantity', 'Unit', 'Price (ETB)', 'Total Amount (ETB)', 'Buyer', 'Phone', 'Date', 'Escrow Status', 'Payout Ref']
  const rows = filteredSales.value.map(sale => [
    `"${sale.id}"`,
    `"${sale.productName}"`,
    `"${sale.quantity}"`,
    `"${sale.unit}"`,
    `"${formatNumber(sale.pricePerUnit)}"`,
    `"${formatNumber(sale.totalAmount)}"`,
    `"${sale.buyerName}"`,
    `"${sale.buyerPhone || ''}"`,
    `"${formatDate(sale.saleDate)}"`,
    '"Delivered & Paid (Chapa Escrow)"',
    `"${sale.payoutReference}"`
  ])

  const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n')
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `verified-sales-report-${new Date().toISOString().split('T')[0]}.csv`
  a.click()
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  fetchSalesData()
})
</script>

<style scoped>
.sales-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.sales-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* Page Header */
.page-header {
  background: white;
  padding: 24px 32px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
}

.header-content h1 {
  font-size: 26px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 6px 0;
  letter-spacing: -0.02em;
}

.header-content p {
  color: #6b7280;
  font-size: 13px;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-export {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 16px;
  border-radius: 10px;
  background: #10b981;
  color: white;
  border: 1px solid #10b981;
  cursor: pointer;
  font-size: 13px;
  font-weight: 700;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

.btn-export:hover {
  background: #059669;
  border-color: #059669;
}

/* Loading & Error */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  gap: 20px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-container {
  background: #fee2e2;
  border: 1px solid #fecaca;
  color: #991b1b;
  padding: 30px;
  border-radius: 16px;
  margin: 30px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  text-align: center;
}

.btn-retry {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #dc2626;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-retry:hover {
  background: #b91c1c;
}

/* Dark Mode Theme Adjustments */
.dark.sales-layout {
  background-color: #0b101b;
}

.dark .page-header {
  background: #131b2e;
  border-bottom-color: #1e293b;
}

.dark .header-content h1 {
  color: #f8fafc;
}

.dark .header-content p {
  color: #94a3b8;
}

.dark .spinner {
  border-color: #1e293b;
  border-top-color: #10b981;
}

.dark .loading-container p {
  color: #94a3b8;
}

@media (max-width: 1024px) {
  .sales-container {
    margin-left: 0;
  }
}
</style>
