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
                  <Handshake class="w-3.5 h-3.5" />
                  Contract Farming
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Contract Farming &gt; Direct Contracts
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Direct Contracts
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Initiate forward supply agreements with verified farmers &amp; cooperatives with milestone escrow security
              </p>
            </div>
          </div>

          <!-- Header Right Actions (All Buttons Blue) -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <button
              @click="openCreateModal"
              class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all flex items-center gap-1.5 shadow-sm shadow-blue-600/30 cursor-pointer"
            >
              <Plus class="w-4 h-4" />
              <span>New Direct Contract</span>
            </button>

            <button
              @click="refreshData"
              :disabled="refreshing"
              :class="['px-3 py-2 rounded-xl text-xs font-semibold border flex items-center gap-2 transition-all cursor-pointer', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-200 hover:bg-slate-800' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50 shadow-2xs']"
              title="Refresh Contracts"
            >
              <RefreshCw :class="['w-3.5 h-3.5', refreshing ? 'animate-spin text-blue-500' : '']" />
              <span>Refresh</span>
            </button>

            <router-link
              to="/buyer/marketplace"
              :class="['px-3 py-2 rounded-xl text-xs font-semibold border flex items-center gap-1.5 transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-200 hover:bg-slate-800' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50 shadow-2xs']"
            >
              <ShoppingBag class="w-3.5 h-3.5 text-blue-500" />
              <span>Marketplace</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- ── Summary Stats Cards (Live Database Metrics) ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Contracts -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <p :class="['text-xs font-medium uppercase tracking-wider m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Contracts</p>
            <h3 class="text-2xl font-black mt-1 mb-0">{{ stats.total }}</h3>
            <p class="text-[11px] text-blue-500 font-semibold mt-0.5 mb-0">Agreements created</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
            <FileText class="w-5 h-5" />
          </div>
        </article>

        <!-- Active Production -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <p :class="['text-xs font-medium uppercase tracking-wider m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">Active Production</p>
            <h3 class="text-2xl font-black mt-1 mb-0">{{ stats.active }}</h3>
            <p class="text-[11px] text-blue-500 font-semibold mt-0.5 mb-0">Growing &amp; in-progress</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
            <Sprout class="w-5 h-5" />
          </div>
        </article>

        <!-- Pending Signature -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-amber-500' : 'bg-white border-slate-200 text-slate-900 !border-l-amber-600']">
          <div>
            <p :class="['text-xs font-medium uppercase tracking-wider m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">Pending Signature</p>
            <h3 class="text-2xl font-black mt-1 mb-0">{{ stats.pending }}</h3>
            <p class="text-[11px] text-amber-500 font-semibold mt-0.5 mb-0">Awaiting farmer sign</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
            <Clock class="w-5 h-5" />
          </div>
        </article>

        <!-- Total Contracted Value -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-purple-500' : 'bg-white border-slate-200 text-slate-900 !border-l-purple-600']">
          <div>
            <p :class="['text-xs font-medium uppercase tracking-wider m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">Committed Value</p>
            <h3 class="text-xl font-black mt-1 mb-0 tracking-tight">ETB {{ formatNumber(stats.totalValue) }}</h3>
            <p class="text-[11px] text-purple-500 font-semibold mt-0.5 mb-0">Forward supply volume</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
            <BadgeDollarSign class="w-5 h-5" />
          </div>
        </article>
      </section>

      <!-- ── Search & Filter Controls ── -->
      <section :class="['p-4 rounded-xl border mb-6 transition-all shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-stretch md:items-center">
          
          <!-- Search Input -->
          <div class="relative flex-1 max-w-md">
            <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by contract number, crop, or farmer..."
              :class="['w-full pl-9 pr-4 py-2 text-xs rounded-xl border focus:outline-hidden focus:ring-2 focus:ring-blue-500/30 transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100 placeholder-slate-500 focus:border-blue-500' : 'bg-slate-50 border-slate-300 text-slate-900 placeholder-slate-400 focus:border-blue-600']"
            />
          </div>

          <!-- Status Filters & Sorting -->
          <div class="flex flex-wrap items-center gap-2">
            <!-- Filter Tabs -->
            <div :class="['flex items-center p-1 rounded-xl border text-xs overflow-x-auto scrollbar-none', darkMode ? 'bg-[#1a233a] border-slate-700' : 'bg-slate-50 border-slate-200']">
              <button
                v-for="filter in statusFilters"
                :key="filter.value"
                @click="activeStatusFilter = filter.value"
                :class="['px-3 py-1.5 rounded-lg font-semibold transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5', activeStatusFilter === filter.value ? 'bg-blue-600 text-white shadow-xs' : darkMode ? 'text-slate-400 hover:text-slate-200' : 'text-slate-600 hover:text-slate-900']"
              >
                <span>{{ filter.label }}</span>
                <span :class="['text-[10px] px-1.5 py-0.2 rounded-full', activeStatusFilter === filter.value ? 'bg-blue-800 text-white' : darkMode ? 'bg-slate-800 text-slate-300' : 'bg-slate-200 text-slate-700']">
                  {{ filter.count }}
                </span>
              </button>
            </div>

            <!-- Sort Dropdown -->
            <select
              v-model="sortBy"
              :class="['px-3 py-2 text-xs rounded-xl border cursor-pointer focus:outline-hidden transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-700']"
            >
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="highest_value">Highest Value</option>
              <option value="lowest_value">Lowest Value</option>
              <option value="delivery_date">Nearest Delivery</option>
            </select>
          </div>

        </div>
      </section>

      <!-- ── Contracts Content Area ── -->

      <!-- Loading State -->
      <div v-if="loading" class="py-20 flex flex-col items-center justify-center space-y-3">
        <div class="w-8 h-8 border-3 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        <p :class="['text-xs font-semibold', darkMode ? 'text-slate-400' : 'text-slate-500']">
          Fetching contracts from database...
        </p>
      </div>

      <!-- Empty State (No contracts found) -->
      <div
        v-else-if="filteredContracts.length === 0"
        :class="['p-12 text-center rounded-2xl border transition-all my-4', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
      >
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
          <Handshake class="w-8 h-8" />
        </div>
        <h3 :class="['text-base font-extrabold m-0', darkMode ? 'text-slate-100' : 'text-slate-900']">
          {{ searchQuery || activeStatusFilter ? 'No matching contracts found' : 'No direct contracts initiated yet' }}
        </h3>
        <p :class="['text-xs max-w-md mx-auto mt-1 mb-5', darkMode ? 'text-slate-400' : 'text-slate-500']">
          {{ searchQuery || activeStatusFilter
            ? 'Try adjusting your search query or status filter to locate existing agreements.'
            : 'Enter into forward supply agreements directly with verified farmers to secure bulk grain, coffee, or pulse harvests at guaranteed prices.'
          }}
        </p>
        <div class="flex items-center justify-center gap-3">
          <button
            v-if="searchQuery || activeStatusFilter"
            @click="resetFilters"
            :class="['px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
          >
            Clear Filters
          </button>
          <button
            @click="openCreateModal"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-sm shadow-blue-600/30"
          >
            <Plus class="w-4 h-4" />
            <span>Create First Direct Contract</span>
          </button>
        </div>
      </div>

      <!-- Contracts Grid / Cards -->
      <div v-else class="space-y-4">
        <article
          v-for="contract in filteredContracts"
          :key="contract.id"
          :class="['rounded-2xl border p-5 transition-all shadow-2xs hover:shadow-md', darkMode ? 'bg-[#131b2e] border-slate-800 hover:border-slate-700' : 'bg-white border-slate-200 hover:border-slate-300']"
        >
          <!-- Card Top Bar: Contract #, Title, and Status -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-700/30">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0">
                <FileText class="w-5 h-5" />
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-mono text-xs font-extrabold text-blue-500">
                    {{ contract.contract_number }}
                  </span>
                  <span :class="['text-[11px] px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider', getStatusBadgeStyle(contract.status)]">
                    {{ formatStatusLabel(contract.status) }}
                  </span>
                </div>
                <h2 :class="['text-sm lg:text-base font-extrabold m-0 mt-0.5', darkMode ? 'text-slate-100' : 'text-slate-900']">
                  {{ contract.title }}
                </h2>
              </div>
            </div>

            <!-- Header Amount and Action Buttons -->
            <div class="flex items-center gap-3 sm:text-right">
              <div>
                <span :class="['text-[11px] block', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Contract Value</span>
                <span class="text-base lg:text-lg font-black text-blue-500">
                  ETB {{ formatNumber(contract.total_amount) }}
                </span>
              </div>
              <button
                @click="viewContractDetails(contract)"
                class="px-3 py-1.5 bg-blue-600/10 hover:bg-blue-600 hover:text-white text-blue-500 rounded-xl text-xs font-bold transition-all flex items-center gap-1 cursor-pointer shrink-0"
              >
                <span>Agreement</span>
                <ChevronRight class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <!-- Card Body: Grid of Farmer, Produce, Financials, Timeline -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 py-4 text-xs">
            
            <!-- 1. Farmer / Cooperative -->
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex items-center gap-1.5 text-slate-400 mb-1">
                <User class="w-3.5 h-3.5 text-blue-500" />
                <span class="font-bold uppercase text-[10px]">Contracted Farmer</span>
              </div>
              <p :class="['font-extrabold text-xs m-0 truncate', darkMode ? 'text-slate-100' : 'text-slate-900']">
                {{ contract.farmer?.farm_name || contract.farmer?.user?.name || 'Local Farm Enterprise' }}
              </p>
              <p :class="['text-[11px] m-0 mt-0.5 truncate flex items-center gap-1', darkMode ? 'text-slate-400' : 'text-slate-500']">
                <MapPin class="w-3 h-3 text-slate-400 shrink-0" />
                <span>{{ [contract.farmer?.woreda, contract.farmer?.region].filter(Boolean).join(', ') || 'Ethiopia' }}</span>
              </p>
              <p v-if="contract.farmer?.user?.phone" class="text-[10px] text-slate-400 m-0 mt-0.5">
                📞 {{ contract.farmer.user.phone }}
              </p>
            </div>

            <!-- 2. Commodity & Target Volume -->
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex items-center gap-1.5 text-slate-400 mb-1">
                <Sprout class="w-3.5 h-3.5 text-amber-500" />
                <span class="font-bold uppercase text-[10px]">Commodity &amp; Volume</span>
              </div>
              <p :class="['font-extrabold text-xs m-0', darkMode ? 'text-slate-100' : 'text-slate-900']">
                {{ contract.crop_type }} {{ contract.variety ? `(${contract.variety})` : '' }}
              </p>
              <p class="text-[11px] font-semibold m-0 mt-0.5 text-amber-500 flex items-center gap-1">
                <Scale class="w-3 h-3" />
                <span>{{ formatNumber(contract.quantity) }} {{ contract.unit || 'Quintals' }}</span>
              </p>
              <p :class="['text-[10px] m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-500']">
                At ETB {{ formatNumber(contract.unit_price) }} / {{ contract.unit || 'Quintal' }}
              </p>
            </div>

            <!-- 3. Financial & Escrow Terms -->
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex items-center gap-1.5 text-slate-400 mb-1">
                <BadgeDollarSign class="w-3.5 h-3.5 text-purple-500" />
                <span class="font-bold uppercase text-[10px]">Payment &amp; Escrow</span>
              </div>
              <p :class="['font-extrabold text-xs m-0', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Advance: ETB {{ formatNumber(contract.advance_payment || 0) }}
              </p>
              <p :class="['text-[11px] m-0 mt-0.5 truncate', darkMode ? 'text-slate-400' : 'text-slate-500']" :title="contract.payment_terms">
                {{ contract.payment_terms || 'Escrow milestone disbursement' }}
              </p>
              <span class="inline-flex items-center gap-1 text-[10px] text-blue-500 font-semibold mt-1">
                <ShieldCheck class="w-3 h-3" /> Escrow Protected
              </span>
            </div>

            <!-- 4. Schedule & Delivery -->
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex items-center gap-1.5 text-slate-400 mb-1">
                <Calendar class="w-3.5 h-3.5 text-blue-500" />
                <span class="font-bold uppercase text-[10px]">Fulfillment Window</span>
              </div>
              <p :class="['font-extrabold text-xs m-0 flex items-center justify-between', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <span>Start: {{ formatDate(contract.start_date) }}</span>
              </p>
              <p class="text-[11px] font-bold text-blue-500 m-0 mt-0.5">
                Due: {{ formatDate(contract.delivery_date) }}
              </p>
              <p :class="['text-[10px] m-0 mt-0.5 truncate', darkMode ? 'text-slate-400' : 'text-slate-500']" :title="contract.delivery_location">
                📍 {{ contract.delivery_location || 'Designated buyer warehouse' }}
              </p>
            </div>

          </div>

          <!-- Milestones Visual Stepper (4 standard stages) -->
          <div :class="['mt-2 pt-3 border-t', darkMode ? 'border-slate-800' : 'border-slate-200']">
            <div class="flex items-center justify-between mb-2">
              <span :class="['text-[11px] font-bold uppercase tracking-wider', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Production &amp; Escrow Milestones
              </span>
              <span class="text-[11px] font-semibold text-blue-500">
                {{ getCompletedMilestonesCount(contract) }} of 4 Milestones Settled
              </span>
            </div>

            <div class="grid grid-cols-4 gap-2 relative">
              <div
                v-for="(stage, idx) in defaultStages"
                :key="stage.key"
                :class="['p-2 rounded-xl text-center border transition-all', getStageStyle(contract, stage.key, idx)]"
              >
                <div class="w-5 h-5 mx-auto mb-1 rounded-full flex items-center justify-center text-[10px] font-bold"
                  :class="getStageIconBadge(contract, stage.key, idx)">
                  <Check v-if="isStageCompleted(contract, stage.key, idx)" class="w-3 h-3" />
                  <span v-else>{{ idx + 1 }}</span>
                </div>
                <p class="font-bold text-[10px] m-0 truncate leading-tight">{{ stage.label }}</p>
                <p class="text-[9px] opacity-75 m-0 mt-0.5">{{ stage.sub }}</p>
              </div>
            </div>
          </div>

          <!-- Card Footer Actions (All Blue) -->
          <div :class="['mt-4 pt-3 border-t flex flex-wrap items-center justify-between gap-2', darkMode ? 'border-slate-800' : 'border-slate-200']">
            <div class="flex items-center gap-2 text-slate-400 text-xs">
              <Clock class="w-3.5 h-3.5" />
              <span>Created on {{ formatDateTime(contract.created_at) }}</span>
            </div>

            <div class="flex items-center gap-2">
              <button
                v-if="['proposed', 'draft'].includes(contract.status)"
                @click="cancelContract(contract.id)"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold text-red-500 border border-red-500/30 hover:bg-red-500/10 transition-all cursor-pointer"
              >
                Cancel Proposal
              </button>

              <button
                @click="viewContractDetails(contract)"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-all flex items-center gap-1.5 cursor-pointer shadow-xs shadow-blue-600/30"
              >
                <FileCheck class="w-3.5 h-3.5" />
                <span>Full Agreement</span>
              </button>
            </div>
          </div>

        </article>
      </div>

    </main>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ── MODAL 1: CREATE DIRECT CONTRACT ── -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div
      v-if="createModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-2xl max-h-[92vh] flex flex-col rounded-2xl border shadow-2xl overflow-hidden', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-700/40 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <Handshake class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-extrabold m-0">Initiate Direct Farming Contract</h2>
              <p class="text-xs text-slate-400 m-0">Forward agreement with guaranteed volume, price, and escrow milestones</p>
            </div>
          </div>
          <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-200 font-bold text-sm cursor-pointer p-1">✕</button>
        </div>

        <!-- Modal Form (Scrollable) -->
        <form @submit.prevent="submitCreateContract" class="flex-1 overflow-y-auto p-5 space-y-4 text-xs">
          
          <!-- 1. Contract Title -->
          <div>
            <label class="block font-bold text-xs mb-1">
              Contract Agreement Title <span class="text-red-500">*</span>
            </label>
            <input
              v-model="contractForm.title"
              type="text"
              required
              placeholder="e.g. 2026/27 First Grade Teff Supply Agreement"
              :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden focus:ring-2 focus:ring-blue-500/30 transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            />
          </div>

          <!-- 2. Farmer Selection (Live Database Farmers) -->
          <div>
            <label class="block font-bold text-xs mb-1">
              Select Farmer or Cooperative <span class="text-red-500">*</span>
            </label>
            <select
              v-model="contractForm.farmer_id"
              required
              :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all cursor-pointer', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            >
              <option value="" disabled>Choose a verified farmer or agricultural enterprise...</option>
              <option v-for="farmer in availableFarmers" :key="farmer.id" :value="farmer.id">
                {{ farmer.farm_name || farmer.user?.name || `Farmer #${farmer.id}` }} — {{ farmer.region || 'Ethiopia' }} ({{ farmer.farm_type || 'Mixed' }})
              </option>
            </select>
            <p class="text-[11px] text-slate-400 mt-1">
              Farmers are pulled directly from registered database accounts.
            </p>
          </div>

          <!-- 3. Produce / Commodity & Variety -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-xs mb-1">
                Crop Commodity <span class="text-red-500">*</span>
              </label>
              <select
                v-model="contractForm.crop_type"
                required
                :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              >
                <option value="" disabled>Select commodity...</option>
                <option value="Teff">Teff (ጤፍ)</option>
                <option value="Coffee">Coffee (ቡና)</option>
                <option value="Wheat">Wheat (ስንዴ)</option>
                <option value="Maize">Maize (በቆሎ)</option>
                <option value="Sesame">Sesame (ሰሊጥ)</option>
                <option value="Soybean">Soybean (አኩሪ አተር)</option>
                <option value="Barley">Barley (ገብስ)</option>
                <option value="Red Kidney Beans">Red Kidney Beans (ቦሎቄ)</option>
                <option value="Avocado">Avocado (አቮካዶ)</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-xs mb-1">
                Variety / Cultivar
              </label>
              <input
                v-model="contractForm.variety"
                type="text"
                placeholder="e.g. Magna, Quncho, Sidama G1"
                :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              />
            </div>
          </div>

          <!-- 4. Quantity, Unit & Unit Price -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block font-bold text-xs mb-1">
                Quantity <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="contractForm.quantity"
                type="number"
                min="0.01"
                step="any"
                required
                placeholder="e.g. 500"
                :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              />
            </div>

            <div>
              <label class="block font-bold text-xs mb-1">
                Unit <span class="text-red-500">*</span>
              </label>
              <select
                v-model="contractForm.unit"
                required
                :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              >
                <option value="Quintal">Quintal (100 kg)</option>
                <option value="Kg">Kilogram (kg)</option>
                <option value="Metric Ton">Metric Ton</option>
                <option value="Bag">Standard 50kg Bag</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-xs mb-1">
                Agreed Unit Price (ETB) <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="contractForm.unit_price"
                type="number"
                min="0.01"
                step="any"
                required
                placeholder="e.g. 4500"
                :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              />
            </div>
          </div>

          <!-- Calculated Total Amount Banner -->
          <div :class="['p-3 rounded-xl border flex items-center justify-between', darkMode ? 'bg-blue-950/30 border-blue-800/50' : 'bg-blue-50 border-blue-200']">
            <div>
              <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 block">Computed Total Contract Value:</span>
              <p class="text-base font-black text-blue-600 dark:text-blue-400 m-0">
                ETB {{ formatNumber(computedTotal) }}
              </p>
            </div>
            <div class="text-right">
              <span class="text-[11px] font-semibold text-slate-500 block">Escrow Advance Deposit (30%):</span>
              <p class="text-xs font-bold text-slate-700 dark:text-slate-300 m-0">
                ETB {{ formatNumber(computedAdvance) }}
              </p>
            </div>
          </div>

          <!-- 5. Timeline: Start Date & Delivery Date -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-xs mb-1">
                Production Start Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="contractForm.start_date"
                type="date"
                required
                :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              />
            </div>

            <div>
              <label class="block font-bold text-xs mb-1">
                Target Harvest &amp; Delivery Date <span class="text-red-500">*</span>
              </label>
              <input
                v-model="contractForm.delivery_date"
                type="date"
                required
                :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
              />
            </div>
          </div>

          <!-- 6. Delivery Location -->
          <div>
            <label class="block font-bold text-xs mb-1">
              Designated Delivery Depot / Warehouse Location
            </label>
            <input
              v-model="contractForm.delivery_location"
              type="text"
              placeholder="e.g. Kality Central Grain Warehouse, Addis Ababa"
              :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            />
          </div>

          <!-- 7. Quality Standards & Specifications -->
          <div>
            <label class="block font-bold text-xs mb-1">
              Quality Benchmark Specifications
            </label>
            <textarea
              v-model="contractForm.quality_specifications"
              rows="2"
              placeholder="e.g. Moisture content <= 12%, foreign matter < 1%, certified Grade-1 seed, cleaned and bagged in standard 100kg jute sacks."
              :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all resize-none', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            ></textarea>
          </div>

          <!-- 8. Payment Terms -->
          <div>
            <label class="block font-bold text-xs mb-1">
              Payment Terms &amp; Escrow Schedule
            </label>
            <input
              v-model="contractForm.payment_terms"
              type="text"
              placeholder="30% Advance on signing, 40% on mid-harvest inspection, 30% final warehouse acceptance"
              :class="['w-full px-3 py-2 rounded-xl border focus:outline-hidden transition-all', darkMode ? 'bg-[#1a233a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            />
          </div>

          <!-- Modal Action Footer (All Blue) -->
          <div :class="['pt-4 border-t flex justify-end gap-2 shrink-0', darkMode ? 'border-slate-800' : 'border-slate-200']">
            <button
              type="button"
              @click="createModalOpen = false"
              :class="['px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', darkMode ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-sm shadow-blue-600/30"
            >
              <span v-if="submitting" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              <span>Send Contract Proposal</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════ -->
    <!-- ── MODAL 2: CONTRACT AGREEMENT DETAILS MODAL ── -->
    <!-- ═══════════════════════════════════════════════════ -->
    <div
      v-if="detailsModalOpen && activeContract"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-2xl max-h-[92vh] flex flex-col rounded-2xl border shadow-2xl overflow-hidden', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-700/40 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <FileCheck class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="font-mono text-xs font-black text-blue-500">{{ activeContract.contract_number }}</span>
                <span :class="['text-[10px] px-2 py-0.5 rounded-full font-bold uppercase', getStatusBadgeStyle(activeContract.status)]">
                  {{ formatStatusLabel(activeContract.status) }}
                </span>
              </div>
              <h2 class="text-sm lg:text-base font-extrabold m-0 mt-0.5">{{ activeContract.title }}</h2>
            </div>
          </div>
          <button @click="detailsModalOpen = false" class="text-slate-400 hover:text-slate-200 font-bold text-sm cursor-pointer p-1">✕</button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-5 space-y-4 text-xs">
          
          <!-- Key Metrics Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Total Value</span>
              <span class="text-sm font-black text-blue-500">ETB {{ formatNumber(activeContract.total_amount) }}</span>
            </div>
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Quantity</span>
              <span class="text-sm font-black">{{ formatNumber(activeContract.quantity) }} {{ activeContract.unit }}</span>
            </div>
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Unit Price</span>
              <span class="text-sm font-black">ETB {{ formatNumber(activeContract.unit_price) }}</span>
            </div>
            <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] text-slate-400 block">Advance Escrow</span>
              <span class="text-sm font-black text-purple-400">ETB {{ formatNumber(activeContract.advance_payment || 0) }}</span>
            </div>
          </div>

          <!-- Parties Involved -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div :class="['p-3.5 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="font-bold text-[11px] text-slate-400 uppercase tracking-wider block mb-1">Contractor / Farmer</span>
              <p class="font-extrabold text-sm m-0">{{ activeContract.farmer?.farm_name || activeContract.farmer?.user?.name || 'Local Farm Enterprise' }}</p>
              <p class="text-slate-400 m-0 mt-0.5">Contact: {{ activeContract.farmer?.user?.phone || 'On file' }}</p>
              <p class="text-slate-400 m-0">Location: {{ [activeContract.farmer?.woreda, activeContract.farmer?.zone, activeContract.farmer?.region].filter(Boolean).join(', ') }}</p>
            </div>

            <div :class="['p-3.5 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="font-bold text-[11px] text-slate-400 uppercase tracking-wider block mb-1">Timeline &amp; Delivery</span>
              <p class="m-0"><span class="text-slate-400">Agreement Start:</span> <span class="font-bold">{{ formatDate(activeContract.start_date) }}</span></p>
              <p class="m-0 mt-0.5"><span class="text-slate-400">Target Delivery:</span> <span class="font-bold text-blue-500">{{ formatDate(activeContract.delivery_date) }}</span></p>
              <p class="m-0 mt-0.5"><span class="text-slate-400">Destination:</span> {{ activeContract.delivery_location || 'Designated buyer warehouse' }}</p>
            </div>
          </div>

          <!-- Quality Benchmarks -->
          <div v-if="activeContract.quality_specifications" :class="['p-3.5 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <span class="font-bold text-[11px] text-slate-400 uppercase tracking-wider block mb-1">Quality Benchmarks</span>
            <p class="m-0 leading-relaxed">{{ activeContract.quality_specifications }}</p>
          </div>

          <!-- Production Milestones List -->
          <div>
            <span class="font-bold text-xs uppercase tracking-wider block mb-2">Production &amp; Escrow Milestones</span>
            <div class="space-y-2">
              <div
                v-for="(milestone, idx) in (activeContract.milestones || [])"
                :key="milestone.id || idx"
                :class="['p-3 rounded-xl border flex items-center justify-between gap-3', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']"
              >
                <div class="flex items-center gap-3">
                  <div
                    :class="['w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs', milestone.status === 'completed' ? 'bg-blue-600 text-white' : 'bg-slate-700 text-slate-300']"
                  >
                    <Check v-if="milestone.status === 'completed'" class="w-4 h-4" />
                    <span v-else>{{ idx + 1 }}</span>
                  </div>
                  <div>
                    <h4 class="font-bold text-xs m-0">{{ milestone.title }}</h4>
                    <p class="text-[11px] text-slate-400 m-0">{{ milestone.description }}</p>
                  </div>
                </div>

                <div class="text-right shrink-0">
                  <span class="font-extrabold text-xs block text-blue-500">
                    ETB {{ formatNumber(milestone.payout_amount) }}
                  </span>
                  <span :class="['text-[10px] uppercase font-bold px-2 py-0.5 rounded-full', milestone.status === 'completed' ? 'bg-blue-500/20 text-blue-400' : 'bg-slate-500/20 text-slate-400']">
                    {{ milestone.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Cancellation Reason if any -->
          <div v-if="activeContract.cancellation_reason" class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400">
            <span class="font-bold text-xs block mb-0.5">Cancellation Reason:</span>
            <p class="m-0 text-xs">{{ activeContract.cancellation_reason }}</p>
          </div>

        </div>

        <!-- Modal Footer -->
        <div :class="['p-4 border-t flex justify-end gap-2 shrink-0', darkMode ? 'border-slate-800' : 'border-slate-200']">
          <button
            @click="detailsModalOpen = false"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Close Agreement
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
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import {
  Menu, Handshake, FileText, Plus, RefreshCw, ShoppingBag, Clock,
  BadgeDollarSign, ShieldCheck, Search, Check, ChevronRight,
  Calendar, MapPin, User, Scale, FileCheck, Sprout
} from 'lucide-vue-next'

interface ContractFarmerUser {
  name?: string
  phone?: string
  email?: string
}

interface ContractFarmer {
  id: number
  user_id: number
  farm_name?: string
  region?: string
  zone?: string
  woreda?: string
  farm_type?: string
  average_rating?: number
  user?: ContractFarmerUser
}

interface ContractMilestoneItem {
  id?: number
  contract_id?: number
  title: string
  description?: string
  due_date?: string
  status: string
  payout_percentage: number
  payout_amount: number
  payout_status?: string
}

interface ContractItem {
  id: number
  contract_number: string
  buyer_id: number
  farmer_id: number
  title: string
  crop_type: string
  variety?: string
  quantity: number
  unit: string
  unit_price: number
  total_amount: number
  advance_payment?: number
  currency?: string
  start_date: string
  delivery_date: string
  delivery_location?: string
  quality_specifications?: string
  status: string
  payment_terms?: string
  terms_and_conditions?: string
  farmer_notes?: string
  created_at: string
  cancellation_reason?: string
  farmer?: ContractFarmer
  milestones?: ContractMilestoneItem[]
}

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

const createModalOpen = ref(false)
const detailsModalOpen = ref(false)
const activeContract = ref<ContractItem | null>(null)

// Form state
const contractForm = ref({
  title: '',
  farmer_id: '',
  crop_type: '',
  variety: '',
  quantity: 100,
  unit: 'Quintal',
  unit_price: 4500,
  start_date: new Date().toISOString().substring(0, 10),
  delivery_date: '',
  delivery_location: '',
  quality_specifications: '',
  payment_terms: '30% Advance on signing, 40% on mid-harvest inspection, 30% final warehouse acceptance'
})

// Default standard milestones
const defaultStages = [
  { key: 'sowing', label: 'Land & Sowing', sub: '30% Advance' },
  { key: 'inspection', label: 'Mid Inspection', sub: 'Health Audit' },
  { key: 'harvest', label: 'Harvesting', sub: '40% Payout' },
  { key: 'delivery', label: 'Final Settlement', sub: '30% Release' },
]

onMounted(async () => {
  themeStore.initializeTheme()
  // Set default delivery date 90 days ahead
  const d = new Date()
  d.setDate(d.getDate() + 90)
  contractForm.value.delivery_date = d.toISOString().substring(0, 10)

  await loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    await Promise.all([
      buyerStore.fetchContracts().catch(() => []),
      buyerStore.fetchContractFarmers().catch(() => [])
    ])
  } finally {
    loading.value = false
  }
}

const refreshData = async () => {
  refreshing.value = true
  try {
    await Promise.all([
      buyerStore.fetchContracts().catch(() => []),
      buyerStore.fetchContractFarmers().catch(() => [])
    ])
  } finally {
    setTimeout(() => {
      refreshing.value = false
    }, 500)
  }
}

// Master Contracts List (Strictly Live Database)
const rawContracts = computed<ContractItem[]>(() => {
  const c = buyerStore.contracts
  if (Array.isArray(c)) return c as ContractItem[]
  if (c && Array.isArray((c as any).data)) return (c as any).data as ContractItem[]
  return []
})

// Available Farmers from Database
const availableFarmers = computed<ContractFarmer[]>(() => {
  const f = buyerStore.contractFarmers
  if (Array.isArray(f)) return f as ContractFarmer[]
  if (f && Array.isArray((f as any).data)) return (f as any).data as ContractFarmer[]
  return []
})

// Summary Stats
const stats = computed(() => {
  const storeStats = buyerStore.contractStats
  if (storeStats) {
    return {
      total: storeStats.total_contracts ?? 0,
      active: storeStats.active_contracts ?? 0,
      pending: storeStats.pending_contracts ?? 0,
      totalValue: storeStats.total_contracted_value ?? 0
    }
  }

  const list: ContractItem[] = rawContracts.value
  const total = list.length
  const active = list.filter((c: ContractItem) => ['active', 'in_progress'].includes((c.status || '').toLowerCase())).length
  const pending = list.filter((c: ContractItem) => ['proposed', 'draft'].includes((c.status || '').toLowerCase())).length
  const totalValue = list
    .filter((c: ContractItem) => (c.status || '').toLowerCase() !== 'cancelled')
    .reduce((sum: number, c: ContractItem) => sum + Number(c.total_amount || 0), 0)

  return { total, active, pending, totalValue }
})

// Filter Tabs
const statusFilters = computed(() => [
  { label: 'All Contracts', value: '', count: rawContracts.value.length },
  {
    label: 'Active Production',
    value: 'active',
    count: rawContracts.value.filter((c: ContractItem) => ['active', 'in_progress'].includes((c.status || '').toLowerCase())).length
  },
  {
    label: 'Pending Signature',
    value: 'proposed',
    count: rawContracts.value.filter((c: ContractItem) => ['proposed', 'draft'].includes((c.status || '').toLowerCase())).length
  },
  {
    label: 'Completed',
    value: 'completed',
    count: rawContracts.value.filter((c: ContractItem) => (c.status || '').toLowerCase() === 'completed').length
  },
  {
    label: 'Cancelled',
    value: 'cancelled',
    count: rawContracts.value.filter((c: ContractItem) => (c.status || '').toLowerCase() === 'cancelled').length
  }
])

// Filtered and Sorted Contracts
const filteredContracts = computed<ContractItem[]>(() => {
  let list: ContractItem[] = [...rawContracts.value]

  // Status Filter
  if (activeStatusFilter.value) {
    if (activeStatusFilter.value === 'active') {
      list = list.filter((c: ContractItem) => ['active', 'in_progress'].includes((c.status || '').toLowerCase()))
    } else if (activeStatusFilter.value === 'proposed') {
      list = list.filter((c: ContractItem) => ['proposed', 'draft'].includes((c.status || '').toLowerCase()))
    } else {
      list = list.filter((c: ContractItem) => (c.status || '').toLowerCase() === activeStatusFilter.value)
    }
  }

  // Search Filter
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter((c: ContractItem) =>
      (c.contract_number || '').toLowerCase().includes(q) ||
      (c.title || '').toLowerCase().includes(q) ||
      (c.crop_type || '').toLowerCase().includes(q) ||
      (c.farmer?.farm_name || '').toLowerCase().includes(q) ||
      (c.farmer?.user?.name || '').toLowerCase().includes(q)
    )
  }

  // Sort
  switch (sortBy.value) {
    case 'oldest':
      list.sort((a: ContractItem, b: ContractItem) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
      break
    case 'highest_value':
      list.sort((a: ContractItem, b: ContractItem) => Number(b.total_amount || 0) - Number(a.total_amount || 0))
      break
    case 'lowest_value':
      list.sort((a: ContractItem, b: ContractItem) => Number(a.total_amount || 0) - Number(b.total_amount || 0))
      break
    case 'delivery_date':
      list.sort((a: ContractItem, b: ContractItem) => new Date(a.delivery_date || 0).getTime() - new Date(b.delivery_date || 0).getTime())
      break
    case 'newest':
    default:
      list.sort((a: ContractItem, b: ContractItem) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      break
  }

  return list
})

// Auto-computed financials for modal
const computedTotal = computed(() => {
  const q = Number(contractForm.value.quantity) || 0
  const p = Number(contractForm.value.unit_price) || 0
  return q * p
})

const computedAdvance = computed(() => {
  return computedTotal.value * 0.30
})

// Actions
const openCreateModal = () => {
  contractForm.value = {
    title: '',
    farmer_id: availableFarmers.value[0]?.id ? String(availableFarmers.value[0].id) : '',
    crop_type: 'Teff',
    variety: 'Magna',
    quantity: 100,
    unit: 'Quintal',
    unit_price: 4500,
    start_date: new Date().toISOString().substring(0, 10),
    delivery_date: '',
    delivery_location: 'Kality Central Grain Warehouse, Addis Ababa',
    quality_specifications: 'Grade 1 grain, moisture content <= 12.5%, purity >= 98%, standard 100kg jute packaging.',
    payment_terms: '30% Advance on signing, 40% on mid-harvest inspection, 30% final warehouse acceptance'
  }
  const d = new Date()
  d.setDate(d.getDate() + 90)
  contractForm.value.delivery_date = d.toISOString().substring(0, 10)

  createModalOpen.value = true
}

const submitCreateContract = async () => {
  if (!contractForm.value.title || !contractForm.value.farmer_id || !contractForm.value.crop_type) {
    alert('Please fill out all required contract fields.')
    return
  }

  submitting.value = true
  try {
    await buyerStore.createContract({
      farmer_id: contractForm.value.farmer_id,
      title: contractForm.value.title,
      crop_type: contractForm.value.crop_type,
      variety: contractForm.value.variety,
      quantity: contractForm.value.quantity,
      unit: contractForm.value.unit,
      unit_price: contractForm.value.unit_price,
      advance_payment: computedAdvance.value,
      start_date: contractForm.value.start_date,
      delivery_date: contractForm.value.delivery_date,
      delivery_location: contractForm.value.delivery_location,
      quality_specifications: contractForm.value.quality_specifications,
      payment_terms: contractForm.value.payment_terms
    })

    createModalOpen.value = false
    alert('Direct farming contract proposal created and submitted to farmer successfully!')
    await buyerStore.fetchContracts()
  } catch (err: any) {
    console.error('Failed to create contract:', err)
    alert(err?.response?.data?.message || 'Failed to create direct contract.')
  } finally {
    submitting.value = false
  }
}

const cancelContract = async (id: number) => {
  const reason = prompt('Please enter a cancellation reason:')
  if (reason !== null) {
    try {
      await buyerStore.cancelContract(id, reason)
      alert('Contract cancelled.')
      await buyerStore.fetchContracts()
    } catch (err: any) {
      alert(err?.response?.data?.message || 'Failed to cancel contract.')
    }
  }
}

const viewContractDetails = (contract: ContractItem) => {
  activeContract.value = contract
  detailsModalOpen.value = true
}

const resetFilters = () => {
  searchQuery.value = ''
  activeStatusFilter.value = ''
  sortBy.value = 'newest'
}

// Helpers
const getStatusBadgeStyle = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (['active', 'in_progress'].includes(norm)) return 'bg-blue-500/15 text-blue-400 border border-blue-500/30'
  if (['proposed', 'draft'].includes(norm)) return 'bg-amber-500/15 text-amber-500 border border-amber-500/30'
  if (norm === 'completed') return 'bg-blue-600/20 text-blue-300 border border-blue-500/30'
  if (norm === 'cancelled') return 'bg-red-500/15 text-red-400 border border-red-500/30'
  return 'bg-slate-500/15 text-slate-400 border border-slate-500/30'
}

const formatStatusLabel = (status: string) => {
  const norm = (status || '').toLowerCase()
  if (norm === 'proposed') return 'Pending Signature'
  if (norm === 'active' || norm === 'in_progress') return 'In Production'
  if (norm === 'completed') return 'Fulfilled'
  if (norm === 'cancelled') return 'Cancelled'
  return status || 'Pending'
}

// Milestones Helper
const getCompletedMilestonesCount = (contract: ContractItem) => {
  if (!contract.milestones || contract.milestones.length === 0) {
    const norm = (contract.status || '').toLowerCase()
    if (norm === 'completed') return 4
    if (['active', 'in_progress'].includes(norm)) return 1
    return 0
  }
  return contract.milestones.filter((m: ContractMilestoneItem) => m.status === 'completed').length
}

const isStageCompleted = (contract: ContractItem, _stageKey: string, stageIdx: number) => {
  const completedCount = getCompletedMilestonesCount(contract)
  return completedCount > stageIdx
}

const getStageStyle = (contract: ContractItem, _stageKey: string, stageIdx: number) => {
  const completedCount = getCompletedMilestonesCount(contract)
  if (completedCount > stageIdx) {
    return darkMode.value ? 'bg-blue-950/20 border-blue-800 text-blue-400' : 'bg-blue-50 border-blue-300 text-blue-800'
  }
  if (completedCount === stageIdx && ['active', 'in_progress'].includes((contract.status || '').toLowerCase())) {
    return darkMode.value ? 'bg-amber-950/20 border-amber-800 text-amber-400' : 'bg-amber-50 border-amber-300 text-amber-800'
  }
  return darkMode.value ? 'bg-[#1a233a] border-slate-800 text-slate-400' : 'bg-slate-50 border-slate-200 text-slate-500'
}

const getStageIconBadge = (contract: ContractItem, _stageKey: string, stageIdx: number) => {
  const completedCount = getCompletedMilestonesCount(contract)
  if (completedCount > stageIdx) {
    return 'bg-blue-600 text-white'
  }
  if (completedCount === stageIdx && ['active', 'in_progress'].includes((contract.status || '').toLowerCase())) {
    return 'bg-amber-500 text-white animate-pulse'
  }
  return darkMode.value ? 'bg-slate-800 text-slate-400' : 'bg-slate-200 text-slate-600'
}

const formatDate = (dateStr: string | null) => {
  if (!dateStr) return 'N/A'
  try {
    return new Date(dateStr).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    })
  } catch {
    return dateStr
  }
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
