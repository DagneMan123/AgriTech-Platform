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
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20">
                  <RotateCcw class="w-3.5 h-3.5" />
                  Buyer Protection
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Orders &gt; Returns &amp; Refunds
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Returns &amp; Refunds
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Submit claims for damaged, missing, or subpar agricultural goods and track escrow refund statuses
              </p>
            </div>
          </div>

          <!-- Header Right Actions -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <button
              @click="openRequestModal"
              class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition-all flex items-center gap-1.5 shadow-sm shadow-emerald-600/30 cursor-pointer"
            >
              <Plus class="w-4 h-4" />
              <span>File Return Claim</span>
            </button>

            <button
              @click="refreshData"
              :disabled="refreshing"
              :class="['px-3 py-2 rounded-xl text-xs font-semibold border flex items-center gap-2 transition-all cursor-pointer', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-200 hover:bg-slate-800' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50 shadow-2xs']"
              title="Refresh Returns"
            >
              <RefreshCw :class="['w-3.5 h-3.5', refreshing ? 'animate-spin text-emerald-500' : '']" />
              <span>Refresh</span>
            </button>

            <router-link
              to="/buyer/orders"
              :class="['px-3 py-2 rounded-xl text-xs font-semibold border flex items-center gap-1.5 transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-200 hover:bg-slate-800' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50 shadow-2xs']"
            >
              <ShoppingBag class="w-3.5 h-3.5 text-blue-500" />
              <span>My Orders</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- ── Summary Stats Cards ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Claims -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Claims</h3>
            <p class="text-2xl font-black">{{ stats.total }}</p>
            <span class="text-[10px] text-blue-500 font-medium">All submitted returns</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <RotateCcw class="w-6 h-6" />
          </div>
        </article>

        <!-- Under Review -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-amber-500' : 'bg-white border-slate-200 text-slate-900 !border-l-amber-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Under Review</h3>
            <p class="text-2xl font-black">{{ stats.underReview }}</p>
            <span class="text-[10px] text-amber-500 font-medium">Auditing evidence</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <Clock class="w-6 h-6" />
          </div>
        </article>

        <!-- Approved -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-purple-500' : 'bg-white border-slate-200 text-slate-900 !border-l-purple-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Approved Claims</h3>
            <p class="text-2xl font-black">{{ stats.approved }}</p>
            <span class="text-[10px] text-purple-500 font-medium">Processing refund / return</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-purple-500/10 text-purple-400' : 'bg-purple-50 text-purple-600']">
            <CheckCircle2 class="w-6 h-6" />
          </div>
        </article>

        <!-- Refunded Value -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-emerald-500' : 'bg-white border-slate-200 text-slate-900 !border-l-emerald-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Refunded Value</h3>
            <p class="text-xl lg:text-2xl font-black text-emerald-500">ETB {{ formatNumber(stats.refundedAmount) }}</p>
            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
              <ShieldCheck class="w-3 h-3 inline" /> Returned to wallet
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
              placeholder="Search by return #, order #, or reason..."
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
              <option value="highest-amount">Highest Amount</option>
              <option value="lowest-amount">Lowest Amount</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ── Loading State ── -->
      <div v-if="loading" :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="w-10 h-10 border-4 border-slate-300 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
        <p :class="['text-sm font-semibold', darkMode ? 'text-slate-200' : 'text-slate-700']">Loading returns &amp; refund claims...</p>
        <p :class="['text-xs mt-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Fetching records from database</p>
      </div>

      <!-- ── Empty State ── -->
      <div
        v-else-if="filteredReturns.length === 0"
        :class="['rounded-2xl border p-10 lg:p-14 text-center flex flex-col items-center justify-center shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
      >
        <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-4 border', darkMode ? 'bg-slate-800/60 border-slate-700 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-400']">
          <RotateCcw class="w-8 h-8 text-slate-400" />
        </div>
        <h3 :class="['text-lg font-extrabold mb-1', darkMode ? 'text-slate-100' : 'text-slate-800']">
          No Returns or Refund Claims Found
        </h3>
        <p :class="['text-xs lg:text-sm max-w-md mb-6', darkMode ? 'text-slate-400' : 'text-slate-500']">
          <span v-if="searchQuery || activeStatusFilter">
            No return claims match your search filters. Try clearing your search parameters.
          </span>
          <span v-else>
            You have no recorded return or refund requests. If you ever encounter damaged produce, weight discrepancies, or delivery delays, you can file a protected dispute here.
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
          <button
            @click="openRequestModal"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm cursor-pointer"
          >
            <Plus class="w-4 h-4" />
            <span>File Return Claim</span>
          </button>
          <router-link
            to="/buyer/orders"
            :class="['px-4 py-2 rounded-xl text-xs font-bold border transition-all', darkMode ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
          >
            View Delivered Orders
          </router-link>
        </div>
      </div>

      <!-- ── Returns Cards List ── -->
      <div v-else class="flex flex-col gap-5">
        <article
          v-for="item in filteredReturns"
          :key="item.id"
          :class="[
            'rounded-2xl border overflow-hidden shadow-2xs hover:shadow-md transition-all',
            darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200'
          ]"
          :style="{ 'border-left': `5px solid ${getStatusColor(item.status)}` }"
        >
          <!-- Card Header Bar -->
          <div :class="['flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b', darkMode ? 'bg-[#0f1729] border-slate-800/80' : 'bg-slate-50 border-slate-200/80']">
            <div class="flex items-start sm:items-center gap-3 flex-wrap">
              <div class="flex items-center gap-2">
                <span :class="['text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border', darkMode ? 'bg-slate-800 border-slate-700 text-slate-300' : 'bg-white border-slate-300 text-slate-700']">
                  #{{ item.refund_number || ('RET-' + item.id) }}
                </span>
                <span :class="['text-xs', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Filed {{ formatDateTime(item.created_at) }}
                </span>
              </div>
              <span class="hidden sm:inline text-slate-300 dark:text-slate-700">•</span>
              <!-- Order link -->
              <router-link
                to="/buyer/orders"
                class="text-xs font-semibold text-blue-500 hover:underline flex items-center gap-1"
              >
                <span>Order #{{ item.order?.order_number || item.order_id }}</span>
              </router-link>
            </div>

            <!-- Status Pill & Amount -->
            <div class="flex items-center gap-3">
              <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider', getStatusBadgeStyle(item.status)]">
                <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: getStatusColor(item.status) }"></span>
                {{ formatStatusLabel(item.status) }}
              </span>
              <span class="text-sm font-black text-emerald-500">
                ETB {{ formatNumber(item.amount) }}
              </span>
            </div>
          </div>

          <!-- ── Stepper Resolution Timeline ── -->
          <div :class="['px-5 py-4 border-b', darkMode ? 'bg-[#0c1220] border-slate-800/80' : 'bg-slate-50/50 border-slate-100']">
            <div class="grid grid-cols-4 gap-2 relative">
              <div class="absolute top-4 left-[12%] right-[12%] h-1 bg-slate-200 dark:bg-slate-700 -z-0"></div>
              <div
                class="absolute top-4 left-[12%] h-1 bg-emerald-500 transition-all duration-500 -z-0"
                :style="{ width: getResolutionPercentage(item.status) }"
              ></div>

              <div
                v-for="step in resolutionSteps"
                :key="step.key"
                class="flex flex-col items-center text-center relative z-10"
              >
                <div
                  :class="[
                    'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all shadow-sm',
                    isResolutionCompleted(item.status, step.key)
                      ? 'bg-emerald-500 text-white'
                      : isResolutionCurrent(item.status, step.key)
                        ? 'bg-blue-600 text-white ring-4 ring-blue-500/30'
                        : darkMode
                          ? 'bg-slate-800 text-slate-500 border border-slate-700'
                          : 'bg-white text-slate-400 border border-slate-300'
                  ]"
                >
                  <Check v-if="isResolutionCompleted(item.status, step.key)" class="w-4 h-4 stroke-[3]" />
                  <component v-else :is="step.icon" class="w-4 h-4" />
                </div>
                <span
                  :class="[
                    'text-[10px] font-semibold mt-1.5 hidden sm:block',
                    isResolutionCompleted(item.status, step.key) || isResolutionCurrent(item.status, step.key)
                      ? darkMode ? 'text-slate-200 font-bold' : 'text-slate-800 font-bold'
                      : darkMode ? 'text-slate-500' : 'text-slate-400'
                  ]"
                >
                  {{ step.label }}
                </span>
              </div>
            </div>
          </div>

          <!-- Body Content -->
          <div class="p-5 grid grid-cols-1 md:grid-cols-12 gap-5">
            <!-- Left Info (8 cols) -->
            <div class="md:col-span-8 space-y-3">
              <div>
                <span class="text-[10px] uppercase font-bold text-slate-400">Claim Reason</span>
                <p :class="['text-sm font-bold m-0 mt-0.5', darkMode ? 'text-slate-100' : 'text-slate-800']">
                  {{ item.reason }}
                </p>
              </div>

              <div v-if="item.admin_notes" :class="['p-3 rounded-xl border text-xs', darkMode ? 'bg-[#0f172a] border-slate-800 text-slate-300' : 'bg-slate-50 border-slate-200 text-slate-700']">
                <span class="font-bold text-[10px] uppercase text-blue-500 block mb-0.5">Dispute Review Note</span>
                <p class="m-0">{{ item.admin_notes }}</p>
              </div>

              <!-- Associated Order Items -->
              <div v-if="item.order?.items && item.order.items.length > 0" class="flex items-center gap-2 flex-wrap text-xs">
                <span class="text-slate-400 font-medium">Involved Produce:</span>
                <span
                  v-for="(orderItem, idx) in item.order.items.slice(0, 3)"
                  :key="idx"
                  :class="['px-2.5 py-1 rounded-lg border font-semibold text-[11px]', darkMode ? 'bg-slate-800/80 border-slate-700 text-slate-200' : 'bg-slate-100 border-slate-200 text-slate-700']"
                >
                  {{ orderItem.product?.title || orderItem.product_name || 'Crop item' }} ({{ orderItem.quantity }}kg)
                </span>
              </div>
            </div>

            <!-- Right Actions (4 cols) -->
            <div :class="['md:col-span-4 flex flex-col justify-between p-4 rounded-xl border', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div>
                <p class="text-[10px] uppercase font-bold text-slate-400 m-0">Claim Amount</p>
                <p class="text-xl font-black text-emerald-500 m-0 mb-2">ETB {{ formatNumber(item.amount) }}</p>
                <p class="text-[11px] text-slate-400 m-0">
                  Method: <span class="font-semibold text-slate-200">Escrow Reversal</span>
                </p>
              </div>

              <div class="mt-4 flex flex-col gap-2">
                <button
                  @click="openDetailsModal(item)"
                  :class="['w-full py-2 px-3 rounded-xl text-xs font-bold border transition-all flex items-center justify-center gap-1.5 cursor-pointer', darkMode ? 'bg-slate-800 border-slate-700 text-slate-200 hover:bg-slate-700' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-100 shadow-2xs']"
                >
                  <FileText class="w-3.5 h-3.5" />
                  <span>Claim Details</span>
                </button>

                <button
                  v-if="item.status === 'requested'"
                  @click="cancelReturnRequest(item.id)"
                  class="w-full py-2 px-3 rounded-xl text-xs font-semibold border border-red-500/30 text-red-400 bg-red-500/10 hover:bg-red-500/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                >
                  <XCircle class="w-3.5 h-3.5" />
                  <span>Cancel Claim</span>
                </button>
              </div>
            </div>
          </div>
        </article>
      </div>

    </main>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- ── MODAL 1: FILE RETURN CLAIM MODAL ── -->
    <!-- ═══════════════════════════════════════════════ -->
    <div
      v-if="requestModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-lg rounded-2xl border shadow-2xl overflow-hidden flex flex-col max-h-[90vh]', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div :class="['flex items-center justify-between p-5 border-b', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
              <RotateCcw class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold m-0">Submit Return / Refund Claim</h3>
              <p :class="['text-xs m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                File a claim for damaged or non-conforming agricultural shipments
              </p>
            </div>
          </div>
          <button
            @click="requestModalOpen = false"
            class="p-2 rounded-xl text-slate-400 hover:text-slate-200 text-sm font-bold cursor-pointer"
          >
            ✕
          </button>
        </div>

        <!-- Modal Form Body -->
        <form @submit.prevent="submitReturnClaim" class="p-5 overflow-y-auto space-y-4 text-xs">
          <!-- Order Selector -->
          <div>
            <label class="block font-bold uppercase tracking-wider text-[11px] text-slate-400 mb-1.5">
              Select Affected Order *
            </label>
            <select
              v-model="claimForm.order_id"
              required
              :class="['w-full p-2.5 rounded-xl border outline-none cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-800']"
              @change="onOrderSelected"
            >
              <option value="" disabled>Choose an order to return...</option>
              <option
                v-for="order in eligibleOrders"
                :key="order.id"
                :value="order.id"
              >
                #{{ order.order_number || ('ORD-' + order.id) }} — ETB {{ formatNumber(order.total_amount || 0) }} ({{ order.status }})
              </option>
            </select>
          </div>

          <!-- Reason Category -->
          <div>
            <label class="block font-bold uppercase tracking-wider text-[11px] text-slate-400 mb-1.5">
              Return / Refund Reason *
            </label>
            <select
              v-model="claimForm.reason"
              required
              :class="['w-full p-2.5 rounded-xl border outline-none cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-800']"
            >
              <option value="" disabled>Select primary reason...</option>
              <option value="Damaged or spoiled produce on arrival">Damaged or spoiled produce on arrival</option>
              <option value="Weight / quantity discrepancy from invoice">Weight / quantity discrepancy from invoice</option>
              <option value="Produce grade below contracted standard">Produce grade below contracted standard</option>
              <option value="Incorrect agricultural variety delivered">Incorrect agricultural variety delivered</option>
              <option value="Excessive delivery delay causing degradation">Excessive delivery delay causing degradation</option>
              <option value="Other non-compliance">Other non-compliance</option>
            </select>
          </div>

          <!-- Refund Amount -->
          <div>
            <label class="block font-bold uppercase tracking-wider text-[11px] text-slate-400 mb-1.5">
              Requested Refund Amount (ETB) *
            </label>
            <input
              v-model.number="claimForm.amount"
              type="number"
              step="0.01"
              required
              placeholder="e.g. 15000"
              :class="['w-full p-2.5 rounded-xl border outline-none font-bold', darkMode ? 'bg-[#0f172a] border-slate-700 text-emerald-400' : 'bg-slate-50 border-slate-300 text-emerald-600']"
            />
          </div>

          <!-- Description / Notes -->
          <div>
            <label class="block font-bold uppercase tracking-wider text-[11px] text-slate-400 mb-1.5">
              Detailed Description &amp; Evidence Summary
            </label>
            <textarea
              v-model="claimForm.notes"
              rows="3"
              placeholder="Describe the condition of goods upon inspection at delivery, packaging condition, or discrepancy notes..."
              :class="['w-full p-2.5 rounded-xl border outline-none resize-none', darkMode ? 'bg-[#0f172a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-800']"
            ></textarea>
          </div>

          <!-- Evidence upload simulation -->
          <div :class="['p-3 rounded-xl border border-dashed text-center', darkMode ? 'border-slate-700 bg-slate-900/40 text-slate-400' : 'border-slate-300 bg-slate-50 text-slate-500']">
            <UploadCloud class="w-6 h-6 mx-auto mb-1 text-slate-400" />
            <p class="font-bold text-[11px] m-0">Upload inspection photos or delivery receipt</p>
            <p class="text-[10px] text-slate-400 m-0">Supported formats: JPG, PNG, PDF (Max 10MB)</p>
          </div>

          <!-- Modal Action Footer -->
          <div :class="['pt-3 border-t flex justify-end gap-2', darkMode ? 'border-slate-800' : 'border-slate-200']">
            <button
              type="button"
              @click="requestModalOpen = false"
              :class="['px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
            >
              <span v-if="submitting" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <span>Submit Return Claim</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- ── MODAL 2: CLAIM DETAILS MODAL ── -->
    <!-- ═══════════════════════════════════════════════ -->
    <div
      v-if="detailsModalOpen && activeClaimForDetails"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-md rounded-2xl border shadow-2xl overflow-hidden p-6 space-y-4', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <div class="flex items-center justify-between pb-3 border-b border-slate-700/40">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <FileCheck class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-sm font-extrabold m-0">Claim Specification</h3>
              <p class="text-[10px] text-slate-400 m-0">#{{ activeClaimForDetails.refund_number || activeClaimForDetails.id }}</p>
            </div>
          </div>
          <button @click="detailsModalOpen = false" class="text-slate-400 hover:text-slate-200 font-bold cursor-pointer">✕</button>
        </div>

        <div class="space-y-3 text-xs">
          <div class="flex justify-between">
            <span class="text-slate-400">Status:</span>
            <span :class="['font-bold uppercase', getStatusBadgeStyle(activeClaimForDetails.status)]">
              {{ formatStatusLabel(activeClaimForDetails.status) }}
            </span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Order Reference:</span>
            <span class="font-semibold">Order #{{ activeClaimForDetails.order?.order_number || activeClaimForDetails.order_id }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Claim Amount:</span>
            <span class="font-black text-emerald-500">ETB {{ formatNumber(activeClaimForDetails.amount) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Filed On:</span>
            <span>{{ formatDateTime(activeClaimForDetails.created_at) }}</span>
          </div>
          <div>
            <span class="text-slate-400 block mb-0.5">Primary Reason:</span>
            <p class="font-semibold m-0">{{ activeClaimForDetails.reason }}</p>
          </div>
          <div v-if="activeClaimForDetails.admin_notes">
            <span class="text-slate-400 block mb-0.5">Reviewer Notes:</span>
            <p :class="['p-2 rounded-lg border text-xs m-0', darkMode ? 'bg-slate-900 border-slate-800' : 'bg-slate-50 border-slate-200']">
              {{ activeClaimForDetails.admin_notes }}
            </p>
          </div>
        </div>

        <div class="pt-3 border-t flex justify-end">
          <button
            @click="detailsModalOpen = false"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Close
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
  Menu, RotateCcw, Plus, RefreshCw, ShoppingBag, Clock, CheckCircle2,
  BadgeDollarSign, ShieldCheck, Search, Check, FileText, XCircle,
  UploadCloud, FileCheck
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const buyerStore = useBuyerStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)

const sidebarOpen = ref(false)
const loading = ref(false)
const refreshing = ref(false)
const submitting = ref(false)

const searchQuery = ref('')
const activeStatusFilter = ref('')
const sortBy = ref('newest')

const requestModalOpen = ref(false)
const detailsModalOpen = ref(false)
const activeClaimForDetails = ref<any>(null)

const claimForm = ref({
  order_id: '',
  reason: '',
  amount: 0,
  notes: ''
})

const resolutionSteps = [
  { key: 'requested', label: 'Claim Submitted', icon: Clock },
  { key: 'review', label: 'Under Review', icon: FileCheck },
  { key: 'approved', label: 'Claim Approved', icon: CheckCircle2 },
  { key: 'completed', label: 'Refund Disbursed', icon: BadgeDollarSign }
]

onMounted(async () => {
  themeStore.initializeTheme()
  await loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    await Promise.all([
      buyerStore.fetchReturns().catch(() => []),
      buyerStore.fetchOrders().catch(() => [])
    ])
  } finally {
    loading.value = false
  }
}

const refreshData = async () => {
  refreshing.value = true
  try {
    await Promise.all([
      buyerStore.fetchReturns().catch(() => []),
      buyerStore.fetchOrders().catch(() => [])
    ])
  } finally {
    setTimeout(() => {
      refreshing.value = false
    }, 500)
  }
}

// Master returns raw list from Database / Store
const rawReturns = computed(() => {
  const r = buyerStore.returns
  if (Array.isArray(r)) return r
  if (r && Array.isArray((r as any).data)) return (r as any).data
  return []
})

// Eligible orders available for filing returns
const eligibleOrders = computed(() => {
  const o = buyerStore.orders
  let list: any[] = []
  if (Array.isArray(o)) list = o
  else if (o && Array.isArray((o as any).data)) list = (o as any).data
  return list
})

// Metrics
const stats = computed(() => {
  const list = rawReturns.value
  const total = list.length
  const underReview = list.filter(r => (r.status || '').toLowerCase() === 'requested').length
  const approved = list.filter(r => (r.status || '').toLowerCase() === 'approved').length
  const refundedAmount = list
    .filter(r => (r.status || '').toLowerCase() === 'completed')
    .reduce((sum, r) => sum + Number(r.amount || 0), 0)

  return { total, underReview, approved, refundedAmount }
})

// Filter Tabs
const statusFilters = computed(() => [
  { label: 'All Requests', value: '', count: rawReturns.value.length },
  {
    label: 'Under Review',
    value: 'requested',
    count: rawReturns.value.filter(r => (r.status || '').toLowerCase() === 'requested').length
  },
  {
    label: 'Approved',
    value: 'approved',
    count: rawReturns.value.filter(r => (r.status || '').toLowerCase() === 'approved').length
  },
  {
    label: 'Completed',
    value: 'completed',
    count: rawReturns.value.filter(r => (r.status || '').toLowerCase() === 'completed').length
  },
  {
    label: 'Rejected',
    value: 'rejected',
    count: rawReturns.value.filter(r => (r.status || '').toLowerCase() === 'rejected').length
  }
])

// Filtered and Sorted
const filteredReturns = computed(() => {
  let list = [...rawReturns.value]

  if (activeStatusFilter.value) {
    list = list.filter(r => (r.status || '').toLowerCase() === activeStatusFilter.value)
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(r =>
      (r.refund_number || ('RET-' + r.id)).toLowerCase().includes(q) ||
      (r.reason || '').toLowerCase().includes(q) ||
      (r.order?.order_number || ('ORD-' + r.order_id)).toLowerCase().includes(q)
    )
  }

  switch (sortBy.value) {
    case 'oldest':
      list.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
      break
    case 'highest-amount':
      list.sort((a, b) => Number(b.amount || 0) - Number(a.amount || 0))
      break
    case 'lowest-amount':
      list.sort((a, b) => Number(a.amount || 0) - Number(b.amount || 0))
      break
    case 'newest':
    default:
      list.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      break
  }

  return list
})

// Form Actions
const openRequestModal = () => {
  claimForm.value = {
    order_id: '',
    reason: '',
    amount: 0,
    notes: ''
  }
  requestModalOpen.value = true
}

const onOrderSelected = () => {
  const selected = eligibleOrders.value.find(o => String(o.id) === String(claimForm.value.order_id))
  if (selected) {
    claimForm.value.amount = Number(selected.total_amount || selected.grand_total || 0)
  }
}

const submitReturnClaim = async () => {
  if (!claimForm.value.order_id || !claimForm.value.reason) return
  submitting.value = true
  try {
    await buyerStore.createReturn({
      order_id: claimForm.value.order_id,
      reason: claimForm.value.reason,
      amount: claimForm.value.amount,
      notes: claimForm.value.notes
    })
    requestModalOpen.value = false
    alert('Return claim submitted successfully! You can track the inspection and escrow refund status here.')
    await buyerStore.fetchReturns()
  } catch (err: any) {
    console.error('Failed to submit claim:', err)
    alert(err?.response?.data?.message || 'Failed to submit return request.')
  } finally {
    submitting.value = false
  }
}

const cancelReturnRequest = async (id: number) => {
  if (confirm('Are you sure you want to cancel this return claim?')) {
    try {
      await buyerStore.cancelReturn(id)
      await buyerStore.fetchReturns()
      alert('Return request cancelled.')
    } catch (err: any) {
      alert(err?.response?.data?.message || 'Failed to cancel return request.')
    }
  }
}

const openDetailsModal = (item: any) => {
  activeClaimForDetails.value = item
  detailsModalOpen.value = true
}

const resetFilters = () => {
  searchQuery.value = ''
  activeStatusFilter.value = ''
  sortBy.value = 'newest'
}

// Helpers
const getStatusColor = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'requested') return '#f59e0b'
  if (norm === 'approved') return '#3b82f6'
  if (norm === 'completed') return '#10b981'
  if (norm === 'rejected') return '#ef4444'
  return '#64748b'
}

const getStatusBadgeStyle = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'requested') return 'bg-amber-500/15 text-amber-500 border border-amber-500/30'
  if (norm === 'approved') return 'bg-blue-500/15 text-blue-500 border border-blue-500/30'
  if (norm === 'completed') return 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30'
  if (norm === 'rejected') return 'bg-red-500/15 text-red-400 border border-red-500/30'
  return 'bg-slate-500/15 text-slate-400 border border-slate-500/30'
}

const formatStatusLabel = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'requested') return 'Under Review'
  if (norm === 'approved') return 'Approved'
  if (norm === 'completed') return 'Refunded'
  if (norm === 'rejected') return 'Rejected'
  return status || 'Pending'
}

const isResolutionCompleted = (status: string, stepKey: string) => {
  const norm = (status || '').toLowerCase()
  const map: Record<string, number> = {
    requested: 1,
    approved: 3,
    completed: 4,
    rejected: 2
  }
  const stepMap: Record<string, number> = {
    requested: 1,
    review: 2,
    approved: 3,
    completed: 4
  }
  return (map[norm] || 1) > stepMap[stepKey]
}

const isResolutionCurrent = (status: string, stepKey: string) => {
  const norm = (status || '').toLowerCase()
  if (stepKey === 'requested' && norm === 'requested') return true
  if (stepKey === 'approved' && norm === 'approved') return true
  if (stepKey === 'completed' && norm === 'completed') return true
  return false
}

const getResolutionPercentage = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'requested') return '15%'
  if (norm === 'approved') return '60%'
  if (norm === 'completed') return '100%'
  return '0%'
}

const formatDateTime = (dateStr: string | null) => {
  if (!dateStr) return 'N/A'
  try {
    return new Date(dateStr).toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    })
  } catch {
    return dateStr
  }
}

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num || 0)
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
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
