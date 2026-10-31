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
                  Cost &amp; Spend Intelligence
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Reports &gt; Expense Analysis
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <BarChart2 class="w-7 h-7 text-blue-500" />
                Procurement Expense Analysis
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Detailed audit of raw produce costs, logistics freight expenditures, regulatory escrow taxes, and cooperative volume discounts
              </p>
            </div>
          </div>

          <!-- Header Right Actions -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <button
              @click="exportCSV"
              :disabled="loading || transactions.length === 0"
              :class="['inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold text-xs border transition-all cursor-pointer disabled:opacity-50', darkMode ? 'bg-slate-800/80 hover:bg-slate-800 text-slate-200 border-slate-700' : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-200 shadow-sm']"
              title="Download expense analysis as CSV"
            >
              <Download class="w-4 h-4 text-blue-500" />
              <span>Export CSV</span>
            </button>

            <button
              @click="triggerPrint"
              :disabled="loading || transactions.length === 0"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all transform active:scale-95 cursor-pointer disabled:opacity-50"
            >
              <Printer class="w-4 h-4" />
              <span>Print Audit</span>
            </button>

            <BuyerHeaderNav />
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
          @click="fetchExpenses"
          class="px-3 py-1 rounded-lg text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700 transition"
        >
          Retry
        </button>
      </div>

      <!-- ── EXPENSE KPI SUMMARY CARDS (LIVE DATABASE METRICS) ── -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Outlay -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Expenses</span>
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <DollarSign class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-blue-500">
            ETB {{ formatCurrency(summary.total_expense) }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>Gross procurement budget</span>
          </div>
        </div>

        <!-- Raw Produce Subtotal -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Produce Cost</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <ShoppingBag class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-emerald-500">
            ETB {{ formatCurrency(summary.produce_subtotal) }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>{{ calculatePct(summary.produce_subtotal, summary.total_expense) }}% of total outlay</span>
          </div>
        </div>

        <!-- Logistics & Freight -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Logistics Freight</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
              <Truck class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-amber-500">
            ETB {{ formatCurrency(summary.logistics_total) }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span>{{ calculatePct(summary.logistics_total, summary.total_expense) }}% transportation cost</span>
          </div>
        </div>

        <!-- Discounts & Net Savings -->
        <div :class="['p-4 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-2">
            <span :class="['text-xs font-semibold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">Discounts Captured</span>
            <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
              <PiggyBank class="w-4 h-4" />
            </div>
          </div>
          <div class="text-2xl font-bold tracking-tight text-purple-400">
            ETB {{ formatCurrency(summary.discounts_saved) }}
          </div>
          <div :class="['text-[11px] mt-1 flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
            <span class="text-emerald-500 font-semibold">{{ summary.savings_rate || 0 }}% effective savings</span>
          </div>
        </div>
      </div>

      <!-- ── FILTERS & DATE SELECTOR ── -->
      <div :class="['p-4 lg:p-5 rounded-2xl border mb-6 transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <!-- Keyword Search -->
          <div class="relative flex-1 min-w-[260px]">
            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by reference ID, payee, produce item, or category..."
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

          <!-- Secondary Filter Controls -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <!-- Expense Type Filter -->
            <select
              v-model="selectedType"
              :class="['px-3 py-2.5 rounded-xl border text-xs font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="all">All Expense Types</option>
              <option value="Produce Purchase">Produce Purchases</option>
              <option value="Logistics Freight">Logistics Freight</option>
            </select>

            <!-- Sort By -->
            <select
              v-model="sortBy"
              :class="['px-3 py-2.5 rounded-xl border text-xs font-medium cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="newest">Most Recent</option>
              <option value="amount_high">Highest Expense (ETB)</option>
              <option value="amount_low">Lowest Expense (ETB)</option>
            </select>
          </div>
        </div>

        <!-- Period Tabs -->
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

          <!-- Custom Date Range -->
          <div v-if="selectedPeriod === 'custom'" class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-1 text-xs">
              <span class="text-slate-400">From:</span>
              <input
                v-model="customFromDate"
                type="date"
                @change="fetchExpenses"
                :class="['px-2.5 py-1 rounded-lg border text-xs', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
              />
            </div>
            <div class="flex items-center gap-1 text-xs">
              <span class="text-slate-400">To:</span>
              <input
                v-model="customToDate"
                type="date"
                @change="fetchExpenses"
                :class="['px-2.5 py-1 rounded-lg border text-xs', darkMode ? 'bg-slate-900 border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- ── VISUAL COST STRUCTURE & VELOCITY BREAKDOWNS ── -->
      <div v-if="transactions.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
        <!-- Monthly Expense Velocity Chart -->
        <div :class="['lg:col-span-2 p-5 rounded-2xl border transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 :class="['text-sm font-bold m-0 flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <TrendingUp class="w-4 h-4 text-blue-500" />
                Monthly Expenditure Velocity (Last 6 Months)
              </h3>
              <p :class="['text-[11px] m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Gross procurement and freight spending run-rate in ETB
              </p>
            </div>
            <span class="text-xs font-mono font-bold text-blue-500">
              Total: ETB {{ formatCurrency(summary.total_expense) }}
            </span>
          </div>

          <!-- Trend Bar Graph -->
          <div class="grid grid-cols-6 gap-2 pt-6 pb-2 items-end h-44 border-b border-slate-200 dark:border-slate-800">
            <div
              v-for="item in monthlyExpenses"
              :key="item.month"
              class="flex flex-col items-center h-full justify-end group"
            >
              <div class="text-[10px] font-mono text-slate-400 mb-1 opacity-0 group-hover:opacity-100 transition-opacity">
                {{ item.total_expense > 0 ? (item.total_expense / 1000).toFixed(0) + 'k' : '0' }}
              </div>
              <div class="w-full max-w-[42px] bg-slate-100 dark:bg-slate-800/80 rounded-t-lg h-full flex items-end overflow-hidden p-0.5">
                <div
                  class="w-full bg-blue-600 rounded-t-md transition-all duration-500 group-hover:bg-blue-500"
                  :style="{ height: getBarHeight(item.total_expense) }"
                ></div>
              </div>
              <div class="text-[10px] font-medium text-slate-400 mt-2 truncate w-full text-center">
                {{ item.month.split(' ')[0] }}
              </div>
              <div class="text-[9px] text-slate-500 font-mono">
                {{ item.orders_count }} tx
              </div>
            </div>
          </div>
        </div>

        <!-- Cost Component Structure Stack -->
        <div :class="['p-5 rounded-2xl border transition-all flex flex-col justify-between', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']">
          <div>
            <div class="flex items-center justify-between mb-4">
              <h3 :class="['text-sm font-bold m-0 flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <PieChart class="w-4 h-4 text-blue-500" />
                Cost Component Structure
              </h3>
              <span class="text-[11px] text-slate-400">Ratio (%)</span>
            </div>

            <!-- Proportional Multi-bar Stack -->
            <div class="space-y-4">
              <div v-for="cost in costStructure" :key="cost.name">
                <div class="flex items-center justify-between text-xs mb-1">
                  <span class="font-medium truncate max-w-[150px]">{{ cost.name }}</span>
                  <div class="flex items-center gap-2 font-mono">
                    <span class="font-bold">ETB {{ formatCurrency(cost.amount) }}</span>
                    <span class="text-[10px] text-slate-400">({{ cost.percentage }}%)</span>
                  </div>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all"
                    :style="{ width: cost.percentage + '%', backgroundColor: cost.color }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Supplier Outlay -->
          <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Top Producer Outlay</div>
            <div v-if="suppliers.length > 0" class="space-y-1.5">
              <div
                v-for="s in suppliers.slice(0, 3)"
                :key="s.name"
                class="flex items-center justify-between text-[11px]"
              >
                <span class="truncate max-w-[140px] text-slate-300 font-medium">{{ s.name }}</span>
                <span class="font-mono text-blue-500 font-bold">ETB {{ formatCurrency(s.amount) }}</span>
              </div>
            </div>
            <div v-else class="text-[11px] text-slate-400 italic">No supplier outlay data yet</div>
          </div>
        </div>
      </div>

      <!-- ── LOADING SKELETON ── -->
      <div v-if="loading" :class="['rounded-2xl border p-6 animate-pulse space-y-4 mb-8', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="h-6 w-48 rounded bg-slate-300 dark:bg-slate-700 mb-4"></div>
        <div v-for="i in 5" :key="i" class="h-10 rounded bg-slate-200 dark:bg-slate-800"></div>
      </div>

      <!-- ── EMPTY STATE (FETCHED FROM DATABASE) ── -->
      <div
        v-else-if="filteredTransactions.length === 0"
        :class="['p-12 text-center rounded-2xl border my-8 max-w-xl mx-auto', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']"
      >
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
          <BarChart2 class="w-8 h-8" />
        </div>
        <h3 :class="['text-lg font-bold mb-2', darkMode ? 'text-slate-100' : 'text-slate-800']">
          No Expense Records in Database
        </h3>
        <p :class="['text-xs lg:text-sm mb-6 max-w-md mx-auto', darkMode ? 'text-slate-400' : 'text-slate-500']">
          {{ searchQuery || selectedPeriod !== 'all' || selectedType !== 'all'
            ? 'No procurement or logistics expense records match your active query or selected time filters.'
            : 'You have not incurred any procurement or freight logistics expenses yet. Once you make commodity purchases or coordinate transport, your cost analytics and budget ratios will appear here.' }}
        </p>
        <div class="flex items-center justify-center gap-3">
          <button
            v-if="searchQuery || selectedPeriod !== 'all' || selectedType !== 'all'"
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
            <span>Go to Marketplace</span>
          </router-link>
        </div>
      </div>

      <!-- ── ITEMIZED EXPENSE TRANSACTIONS LEDGER ── -->
      <div
        v-else
        :class="['rounded-2xl border overflow-hidden mb-8 transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-sm']"
      >
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
          <div>
            <h3 :class="['text-sm font-bold m-0', darkMode ? 'text-slate-100' : 'text-slate-900']">
              Itemized Cost &amp; Expense Ledger
            </h3>
            <p :class="['text-[11px] m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
              Showing {{ filteredTransactions.length }} database expense items across produce &amp; logistics
            </p>
          </div>

          <span class="text-xs font-mono font-bold text-blue-500">
            Total: ETB {{ formatCurrency(filteredTotalOutlay) }}
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr :class="['border-b text-[11px] font-bold uppercase tracking-wider', darkMode ? 'bg-slate-900/60 border-slate-800 text-slate-400' : 'bg-slate-50 border-slate-200 text-slate-500']">
                <th class="py-3.5 px-4">Ref # &amp; Date</th>
                <th class="py-3.5 px-4">Expense Type</th>
                <th class="py-3.5 px-4">Payee / Carrier</th>
                <th class="py-3.5 px-4">Commodity / Scope</th>
                <th class="py-3.5 px-4 text-right">Produce (ETB)</th>
                <th class="py-3.5 px-4 text-right">Logistics (ETB)</th>
                <th class="py-3.5 px-4 text-right">Total Outlay (ETB)</th>
                <th class="py-3.5 px-4">Payment Channel</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-xs">
              <tr
                v-for="tx in filteredTransactions"
                :key="tx.id"
                :class="['hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors', darkMode ? 'text-slate-200' : 'text-slate-800']"
              >
                <!-- Reference and Date -->
                <td class="py-3.5 px-4">
                  <div class="font-mono font-bold text-blue-500">{{ tx.reference }}</div>
                  <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                    <Calendar class="w-3 h-3" />
                    <span>{{ tx.date }}</span>
                  </div>
                </td>

                <!-- Type Badge -->
                <td class="py-3.5 px-4">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                      tx.type === 'Produce Purchase'
                        ? 'bg-blue-500/10 text-blue-500 border border-blue-500/20'
                        : 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20'
                    ]"
                  >
                    {{ tx.type }}
                  </span>
                </td>

                <!-- Payee -->
                <td class="py-3.5 px-4 font-semibold">
                  {{ tx.payee }}
                </td>

                <!-- Scope / Description -->
                <td class="py-3.5 px-4">
                  <div class="font-medium max-w-[200px] truncate" :title="tx.description">
                    {{ tx.description }}
                  </div>
                  <div class="text-[10px] text-slate-400">{{ tx.category }}</div>
                </td>

                <!-- Produce Cost -->
                <td class="py-3.5 px-4 text-right font-mono">
                  {{ tx.produce_cost > 0 ? 'ETB ' + formatCurrency(tx.produce_cost) : '—' }}
                </td>

                <!-- Logistics Freight -->
                <td class="py-3.5 px-4 text-right font-mono">
                  {{ tx.logistics_cost > 0 ? 'ETB ' + formatCurrency(tx.logistics_cost) : '—' }}
                </td>

                <!-- Total Outlay -->
                <td class="py-3.5 px-4 text-right font-mono font-bold text-sm text-blue-500">
                  ETB {{ formatCurrency(tx.total) }}
                </td>

                <!-- Channel -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-1 font-medium">
                    <ShieldCheck class="w-3.5 h-3.5 text-emerald-500" />
                    <span>{{ tx.payment_method }}</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
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
  Menu, BarChart2, Download, Printer, AlertCircle, X,
  DollarSign, ShoppingBag, Truck, PiggyBank, Search, Calendar,
  TrendingUp, PieChart, ShieldCheck, Store
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

// Expense Data State
const summary = ref({
  total_expense: 0,
  produce_subtotal: 0,
  logistics_total: 0,
  taxes_and_fees: 0,
  discounts_saved: 0,
  savings_rate: 0,
  total_transactions: 0,
  currency: 'ETB'
})
const costStructure = ref<any[]>([])
const categories = ref<any[]>([])
const suppliers = ref<any[]>([])
const monthlyExpenses = ref<any[]>([])
const transactions = ref<any[]>([])

// Filters
const searchQuery = ref('')
const selectedPeriod = ref('all')
const customFromDate = ref('')
const customToDate = ref('')
const selectedType = ref('all')
const sortBy = ref('newest')

const periodOptions = [
  { label: 'All Time', value: 'all' },
  { label: 'Last 7 Days', value: '7d' },
  { label: 'Last 30 Days', value: '30d' },
  { label: 'Last 90 Days', value: '90d' },
  { label: 'This Year', value: 'year' },
  { label: 'Custom Range', value: 'custom' },
]

// Fetch expense analysis directly from backend API
const fetchExpenses = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: any = {
      period: selectedPeriod.value
    }

    if (selectedPeriod.value === 'custom' && customFromDate.value && customToDate.value) {
      params.from_date = customFromDate.value
      params.to_date = customToDate.value
    }

    const res = await buyerAPI.getExpenseAnalysis(params)
    if (res.data && res.data.success) {
      const data = res.data.data
      summary.value = data.summary || {
        total_expense: 0,
        produce_subtotal: 0,
        logistics_total: 0,
        taxes_and_fees: 0,
        discounts_saved: 0,
        savings_rate: 0,
        total_transactions: 0,
        currency: 'ETB'
      }
      costStructure.value = data.cost_structure || []
      categories.value = data.categories || []
      suppliers.value = data.suppliers || []
      monthlyExpenses.value = data.monthly_expenses || []
      transactions.value = data.transactions || []
    } else {
      transactions.value = []
    }
  } catch (err: any) {
    console.error('Failed to load expense analysis:', err)
    errorMessage.value = err.response?.data?.message || 'Failed to fetch expense records from the database.'
    transactions.value = []
  } finally {
    loading.value = false
  }
}

const setPeriod = (p: string) => {
  selectedPeriod.value = p
  fetchExpenses()
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedPeriod.value = 'all'
  selectedType.value = 'all'
  customFromDate.value = ''
  customToDate.value = ''
  sortBy.value = 'newest'
  fetchExpenses()
}

// Client filtering on transactions
const filteredTransactions = computed(() => {
  let list = [...transactions.value]

  // Type filter
  if (selectedType.value !== 'all') {
    list = list.filter(t => t.type === selectedType.value)
  }

  // Keyword search
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    list = list.filter(t => {
      const ref = (t.reference || '').toLowerCase()
      const payee = (t.payee || '').toLowerCase()
      const desc = (t.description || '').toLowerCase()
      const cat = (t.category || '').toLowerCase()
      return ref.includes(q) || payee.includes(q) || desc.includes(q) || cat.includes(q)
    })
  }

  // Sort
  if (sortBy.value === 'amount_high') {
    list.sort((a, b) => (Number(b.total) || 0) - (Number(a.total) || 0))
  } else if (sortBy.value === 'amount_low') {
    list.sort((a, b) => (Number(a.total) || 0) - (Number(b.total) || 0))
  }

  return list
})

const filteredTotalOutlay = computed(() => {
  return filteredTransactions.value.reduce((acc, curr) => acc + (Number(curr.total) || 0), 0)
})

// Bar height calculator for velocity chart
const getBarHeight = (amt: number) => {
  const max = Math.max(...monthlyExpenses.value.map(i => i.total_expense), 1)
  if (amt <= 0) return '4px'
  const pct = Math.max(8, Math.round((amt / max) * 100))
  return pct + '%'
}

// Helpers
const formatCurrency = (val: number | string | undefined) => {
  if (val === undefined || val === null) return '0.00'
  return Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const calculatePct = (part: number, whole: number) => {
  if (!whole || whole <= 0) return '0'
  return Math.round((part / whole) * 100)
}

// Export CSV
const exportCSV = () => {
  if (transactions.value.length === 0) return

  const headers = ['Reference', 'Date', 'Expense Type', 'Payee', 'Description', 'Category', 'Produce Cost (ETB)', 'Logistics Cost (ETB)', 'Total Outlay (ETB)', 'Payment Method']
  const rows = filteredTransactions.value.map(t => [
    `"${t.reference}"`,
    `"${t.date}"`,
    `"${t.type}"`,
    `"${t.payee}"`,
    `"${(t.description || '').replace(/"/g, '""')}"`,
    `"${t.category}"`,
    t.produce_cost,
    t.logistics_cost,
    t.total,
    `"${t.payment_method}"`
  ])

  const csvContent = [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  link.setAttribute('download', `procurement_expense_analysis_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const triggerPrint = () => {
  window.print()
}

onMounted(() => {
  fetchExpenses()
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
  .fixed, button, select, input {
    display: none !important;
  }
  main {
    margin-left: 0 !important;
    padding: 0 !important;
  }
}
</style>
