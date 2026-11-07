<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', isDark ? 'bg-[#0b0f19] text-slate-100 dark' : 'bg-slate-50 text-slate-800']">
    <!-- Farmer Sidebar -->
    <FarmerSidebar @logout="handleLogout" />

    <!-- Main Content Area -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen transition-all lg:ml-[260px]" role="main">
      
      <!-- Page Header matching Crop Activities design -->
      <header class="page-header" :class="{ 'dark': isDark }">
        <div class="header-titles">
          <h1>Contracts &amp; Agreements</h1>
          <p>Track, monitor, and manage all forward supply agreements and milestone escrows.</p>
        </div>
        <button class="btn-primary" @click="openAddContractDialog">
          <PlusCircle class="icon" />
          <span>Add Agreement</span>
        </button>
      </header>

      <!-- Toast Feedback Banner -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0"
      >
        <div
          v-if="toastMessage"
          class="mb-6 p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center justify-between shadow-sm"
        >
          <div class="flex items-center gap-2">
            <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
            <span>{{ toastMessage }}</span>
          </div>
          <button @click="toastMessage = ''" class="text-emerald-400 hover:text-emerald-300 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>
      </transition>

      <!-- Live Summary Stats Cards -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Contracts -->
        <article :class="['rounded-2xl p-4 border shadow-2xs flex justify-between items-center transition-all', isDark ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">
              Total Contracts
            </h3>
            <p class="text-xl lg:text-2xl font-black text-blue-500">
              {{ stats.total_contracts }}
            </p>
            <span class="text-[10px] text-blue-400 font-medium">Bilateral agreements</span>
          </div>
          <div :class="['p-3 rounded-xl', isDark ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <FileText class="w-6 h-6" />
          </div>
        </article>

        <!-- Pending Proposals -->
        <article :class="['rounded-2xl p-4 border shadow-2xs flex justify-between items-center transition-all', isDark ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">
              Pending Review
            </h3>
            <p class="text-xl lg:text-2xl font-black text-amber-500">
              {{ stats.pending_contracts }}
            </p>
            <span class="text-[10px] text-amber-400 font-medium">Awaiting your signature</span>
          </div>
          <div :class="['p-3 rounded-xl', isDark ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <Clock class="w-6 h-6" />
          </div>
        </article>

        <!-- Active Production -->
        <article :class="['rounded-2xl p-4 border shadow-2xs flex justify-between items-center transition-all', isDark ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">
              Active Supply
            </h3>
            <p class="text-xl lg:text-2xl font-black text-emerald-500">
              {{ stats.active_contracts }}
            </p>
            <span class="text-[10px] text-emerald-400 font-medium">In growing / harvesting</span>
          </div>
          <div :class="['p-3 rounded-xl', isDark ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <Sprout class="w-6 h-6" />
          </div>
        </article>

        <!-- Committed Escrow Value -->
        <article :class="['rounded-2xl p-4 border shadow-2xs flex justify-between items-center transition-all', isDark ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">
              Committed Escrow
            </h3>
            <p class="text-lg lg:text-xl font-black text-purple-500">
              ETB {{ formatNumber(stats.total_contracted_value) }}
            </p>
            <span class="text-[10px] text-purple-400 font-medium">Secured production funds</span>
          </div>
          <div :class="['p-3 rounded-xl', isDark ? 'bg-purple-500/10 text-purple-400' : 'bg-purple-50 text-purple-600']">
            <BadgeDollarSign class="w-6 h-6" />
          </div>
        </article>
      </section>

      <!-- Search & Status Filter Controls -->
      <div :class="['rounded-2xl border p-4 mb-6 shadow-2xs transition-all', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
          <!-- Search Input -->
          <div class="relative flex-1">
            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by contract #, title, crop, or buyer union..."
              :class="['w-full pl-10 pr-4 py-2.5 rounded-xl text-xs border transition-all outline-none', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100 focus:border-emerald-500 placeholder-slate-500' : 'bg-slate-50 border-slate-300 text-slate-800 focus:border-emerald-500 placeholder-slate-400']"
            />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200 text-xs"
            >
              ✕
            </button>
          </div>

          <!-- Sort Select -->
          <div class="flex items-center gap-2">
            <select
              v-model="sortBy"
              :class="['py-2.5 px-3 rounded-xl text-xs border transition-all outline-none font-semibold cursor-pointer', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-300 focus:border-emerald-500' : 'bg-slate-50 border-slate-300 text-slate-700 focus:border-emerald-500']"
            >
              <option value="newest">Newest Proposals</option>
              <option value="highest_value">Highest Escrow Value</option>
              <option value="lowest_value">Lowest Escrow Value</option>
              <option value="delivery_date">Earliest Delivery Date</option>
            </select>
          </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-700/30 overflow-x-auto pb-1 scrollbar-none">
          <button
            v-for="tab in filterTabs"
            :key="tab.value"
            @click="activeStatusFilter = tab.value"
            :class="['px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer flex items-center gap-1.5', activeStatusFilter === tab.value ? (isDark ? 'bg-emerald-500 text-white' : 'bg-emerald-600 text-white shadow-xs') : (isDark ? 'text-slate-400 hover:bg-slate-800' : 'text-slate-600 hover:bg-slate-100')]"
          >
            <span>{{ tab.label }}</span>
            <span
              v-if="tab.count > 0"
              :class="['px-1.5 py-0.2 rounded-full text-[10px] font-extrabold', activeStatusFilter === tab.value ? 'bg-white/20 text-white' : (isDark ? 'bg-slate-800 text-slate-300' : 'bg-slate-200 text-slate-700')]"
            >
              {{ tab.count }}
            </span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && filteredContracts.length === 0" class="text-center py-16">
        <div class="w-8 h-8 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
        <p class="text-xs text-slate-400 font-medium">Loading forward agreements and escrow details...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredContracts.length === 0" :class="['rounded-2xl border p-12 text-center transition-all', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <FileText class="w-12 h-12 text-slate-400 mx-auto mb-3 opacity-60" />
        <h3 :class="['text-base font-bold m-0 mb-1', isDark ? 'text-slate-200' : 'text-slate-800']">
          No Contracts Found
        </h3>
        <p class="text-xs text-slate-400 max-w-sm mx-auto mb-4">
          No contract agreements matching your current filter criteria were found.
        </p>
        <button
          @click="resetFilters"
          class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
        >
          Reset Filters
        </button>
      </div>

      <!-- Contracts List -->
      <div v-else class="space-y-4">
        <article
          v-for="contract in filteredContracts"
          :key="contract.id"
          :class="['rounded-2xl border p-5 sm:p-6 transition-all hover:border-emerald-500/50', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200 shadow-xs']"
        >
          <!-- Card Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-700/30">
            <div class="flex items-center gap-3">
              <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', contract.crop_type.toLowerCase().includes('teff') ? 'bg-amber-500/10 text-amber-500' : 'bg-emerald-500/10 text-emerald-500']">
                <Sprout class="w-5 h-5" />
              </div>
              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-xs font-bold text-slate-400">#{{ contract.contract_number }}</span>
                  <span :class="['px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide', getStatusBadgeStyle(contract.status)]">
                    {{ formatStatusLabel(contract.status) }}
                  </span>
                  <span v-if="contract.advance_payment > 0" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                    ETB {{ formatNumber(contract.advance_payment) }} Advance Escrow
                  </span>
                </div>
                <h2 :class="['text-base lg:text-lg font-black tracking-tight m-0 mt-1', isDark ? 'text-slate-100' : 'text-slate-900']">
                  {{ contract.title }}
                </h2>
              </div>
            </div>

            <!-- Buyer / Union Info -->
            <div :class="['p-2.5 rounded-xl border text-xs sm:text-right shrink-0', isDark ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <span class="text-[10px] uppercase font-bold text-slate-400 block">Buyer / Cooperative Union:</span>
              <span class="font-bold text-emerald-500">{{ contract.buyer?.name || 'Lake Tana Agro Producers Union' }}</span>
              <span class="text-[10px] text-slate-400 block">{{ contract.delivery_location || 'Bahir Dar Agro Depot' }}</span>
            </div>
          </div>

          <!-- Commodity & Escrow Economics Grid -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 py-4 text-xs">
            <div>
              <span class="text-slate-400 text-[10px] uppercase font-bold block mb-0.5">Commodity &amp; Variety</span>
              <span class="font-bold text-slate-200">{{ contract.crop_type }}</span>
              <span class="text-[10px] text-slate-400 block">{{ contract.variety || 'Standard Grade' }}</span>
            </div>
            <div>
              <span class="text-slate-400 text-[10px] uppercase font-bold block mb-0.5">Contract Volume</span>
              <span class="font-bold text-slate-200">{{ contract.quantity }} {{ contract.unit }}</span>
              <span class="text-[10px] text-slate-400 block">@ ETB {{ formatNumber(contract.unit_price) }} / {{ contract.unit }}</span>
            </div>
            <div>
              <span class="text-slate-400 text-[10px] uppercase font-bold block mb-0.5">Total Escrow Value</span>
              <span class="font-black text-sm text-emerald-400">ETB {{ formatNumber(contract.total_amount) }}</span>
              <span class="text-[10px] text-emerald-500/80 block font-medium">100% Locked in Custody</span>
            </div>
            <div>
              <span class="text-slate-400 text-[10px] uppercase font-bold block mb-0.5">Delivery Timeline</span>
              <span class="font-bold text-slate-200 flex items-center gap-1">
                <Calendar class="w-3.5 h-3.5 text-slate-400" />
                {{ formatDate(contract.delivery_date) }}
              </span>
              <span class="text-[10px] text-slate-400 block">Starts: {{ formatDate(contract.start_date) }}</span>
            </div>
          </div>

          <!-- Escrow Milestone Strip -->
          <div :class="['p-3 rounded-xl border mb-4', isDark ? 'bg-[#0f172a]/70 border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div class="flex items-center justify-between mb-2">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <ShieldCheck class="w-3.5 h-3.5 text-emerald-500" />
                Production &amp; Escrow Milestone Breakdown (4 Stages)
              </span>
              <button
                @click="openMilestoneModal(contract)"
                class="text-[11px] font-bold text-emerald-400 hover:underline cursor-pointer"
              >
                Inspect Milestones &rarr;
              </button>
            </div>

            <!-- Milestone Progress Track -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 text-xs">
              <div
                v-for="(m, idx) in contract.milestones"
                :key="m.id || idx"
                :class="['p-2 rounded-lg border flex flex-col justify-between transition-all', getMilestoneBoxStyle(m, contract.status)]"
              >
                <div class="flex items-center justify-between mb-1">
                  <span class="text-[10px] font-extrabold text-slate-400">STAGE {{ idx + 1 }}</span>
                  <span :class="['text-[9px] font-bold px-1.5 py-0.5 rounded-full', getMilestoneStatusBadge(m.status, m.payout_status)]">
                    {{ formatMilestoneStatus(m) }}
                  </span>
                </div>
                <div class="font-bold text-[11px] truncate mb-0.5" :title="m.title">
                  {{ m.title }}
                </div>
                <div class="text-[10px] flex items-center justify-between text-slate-400">
                  <span>{{ m.payout_percentage }}% Escrow</span>
                  <span v-if="m.payout_amount > 0" class="font-bold text-emerald-400">
                    ETB {{ formatNumber(m.payout_amount) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Action Buttons -->
          <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <div class="flex items-center gap-2">
              <button
                @click="openLegalTermsModal(contract)"
                :class="['px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer flex items-center gap-1.5', isDark ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
              >
                <FileText class="w-3.5 h-3.5" />
                <span>Agreement Terms &amp; Specs</span>
              </button>
              <button
                @click="openMilestoneModal(contract)"
                :class="['px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer flex items-center gap-1.5', isDark ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
              >
                <Clock class="w-3.5 h-3.5 text-blue-400" />
                <span>Escrow Milestones</span>
              </button>
            </div>

            <!-- Primary Signing / Review Action Buttons -->
            <div class="flex items-center gap-2">
              <template v-if="contract.status === 'proposed' || contract.status === 'draft'">
                <button
                  @click="openSignModal(contract)"
                  class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/30 flex items-center gap-2 transition-all transform active:scale-95 cursor-pointer"
                >
                  <FileSignature class="w-4 h-4" />
                  <span>Review &amp; Sign Proposal</span>
                </button>
                <button
                  @click="rejectContractProposal(contract.id)"
                  :disabled="actionLoading"
                  class="px-3 py-2 border border-red-500/30 text-red-400 hover:bg-red-500/10 rounded-xl text-xs font-bold transition-all cursor-pointer"
                >
                  <span>Decline</span>
                </button>
              </template>
              
              <template v-else-if="contract.status === 'active' || contract.status === 'in_progress'">
                <div class="flex items-center gap-2">
                  <span class="text-xs text-emerald-400 font-bold flex items-center gap-1.5 bg-emerald-500/10 px-3 py-1.5 rounded-xl border border-emerald-500/20">
                    <CheckCircle2 class="w-4 h-4" />
                    Legally Executed &amp; Active
                  </span>
                  <button
                    @click="openMilestoneModal(contract)"
                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
                  >
                    <span>Update Milestone</span>
                  </button>
                </div>
              </template>

              <template v-else-if="contract.status === 'completed'">
                <span class="text-xs text-emerald-400 font-bold flex items-center gap-1.5 bg-emerald-500/10 px-3 py-1.5 rounded-xl border border-emerald-500/20">
                  <CheckCircle2 class="w-4 h-4" />
                  Fully Delivered &amp; Disbursed
                </span>
              </template>

              <template v-else-if="contract.status === 'cancelled'">
                <span class="text-xs text-red-400 font-bold flex items-center gap-1.5 bg-red-500/10 px-3 py-1.5 rounded-xl border border-red-500/20">
                  <X class="w-4 h-4" />
                  Declined / Terminated
                </span>
              </template>
            </div>
          </div>
        </article>
      </div>

    </main>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ── MODAL 1: REVIEW AGREEMENT & DIGITAL SIGNATURE MODAL ── -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div
      v-if="signModalOpen && selectedContract"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-2xl rounded-2xl border shadow-2xl overflow-hidden max-h-[90vh] flex flex-col', isDark ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-5 border-b border-slate-700/40 shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <FileSignature class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold m-0">Review &amp; Digitally Sign Supply Agreement</h3>
              <p class="text-xs text-slate-400 m-0">#{{ selectedContract.contract_number }} • Forward Escrow Covenant</p>
            </div>
          </div>
          <button @click="signModalOpen = false" class="text-slate-400 hover:text-slate-200 text-lg font-bold cursor-pointer">✕</button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto space-y-5 text-xs">
          <!-- Parties Section -->
          <div :class="['p-4 rounded-xl border grid grid-cols-2 gap-4', isDark ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Off-taker / Institutional Buyer:</span>
              <p class="font-black text-sm m-0 text-emerald-500">{{ selectedContract.buyer?.name || 'Lake Tana Agro Producers Union' }}</p>
              <p class="text-[11px] text-slate-400 m-0">Commercial Buyer Account Verified</p>
            </div>
            <div>
              <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Designated Producer:</span>
              <p class="font-black text-sm m-0 text-blue-400">{{ farmerName || 'Primary Farm Enterprise' }}</p>
              <p class="text-[11px] text-slate-400 m-0">Certified Farmer Registration ID</p>
            </div>
          </div>

          <!-- Commodity & Escrow Economics -->
          <div :class="['p-4 rounded-xl border space-y-3', isDark ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-300 m-0">Commodity &amp; Financial Settlement Schedule</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
              <div>
                <span class="text-slate-400 text-[10px] block">Crop Type:</span>
                <span class="font-bold text-slate-200">{{ selectedContract.crop_type }}</span>
              </div>
              <div>
                <span class="text-slate-400 text-[10px] block">Volume:</span>
                <span class="font-bold text-slate-200">{{ selectedContract.quantity }} {{ selectedContract.unit }}</span>
              </div>
              <div>
                <span class="text-slate-400 text-[10px] block">Unit Price:</span>
                <span class="font-bold text-slate-200">ETB {{ formatNumber(selectedContract.unit_price) }}</span>
              </div>
              <div>
                <span class="text-slate-400 text-[10px] block">Total Contract Value:</span>
                <span class="font-black text-emerald-400">ETB {{ formatNumber(selectedContract.total_amount) }}</span>
              </div>
            </div>

            <!-- Advance Escrow Highlight -->
            <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-between">
              <div>
                <span class="font-bold text-emerald-400 block">Mobilization Advance (30% in Escrow):</span>
                <span class="text-[11px] text-slate-300">Will be unlocked to your registered payout account for land preparation and inputs.</span>
              </div>
              <span class="text-base font-black text-emerald-400 shrink-0">
                ETB {{ formatNumber(selectedContract.advance_payment || (selectedContract.total_amount * 0.3)) }}
              </span>
            </div>
          </div>

          <!-- Quality Standards -->
          <div :class="['p-4 rounded-xl border space-y-2', isDark ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-300 m-0">Mandatory Quality &amp; Packaging Specifications</h4>
            <p class="text-slate-300 leading-relaxed m-0">
              {{ selectedContract.quality_specifications || 'Grade 1 Export White Magna Teff. Moisture content max 11.5%, purity min 99.0%, free of foreign stones and weed seeds, unadulterated, standard 100kg new polypropylene food bags.' }}
            </p>
            <div class="pt-2 flex items-center gap-1.5 text-slate-400">
              <MapPin class="w-3.5 h-3.5 text-emerald-500" />
              <span>Final Delivery Point: <strong>{{ selectedContract.delivery_location || 'Bahir Dar Agro Industrial Depot' }}</strong> on or before <strong>{{ formatDate(selectedContract.delivery_date) }}</strong></span>
            </div>
          </div>

          <!-- Legal Covenants & Escrow Binding Terms -->
          <div :class="['p-4 rounded-xl border space-y-2', isDark ? 'bg-slate-900 border-slate-800' : 'bg-slate-100 border-slate-300']">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-300 m-0">Bilateral Escrow Covenants</h4>
            <ul class="list-disc pl-4 space-y-1 text-slate-400">
              <li>100% of the forward funds are held under neutral Agricultural Custody.</li>
              <li>Producer agrees to follow Good Agricultural Practices (GAP) and accommodate scheduled mid-season agronomist inspection.</li>
              <li>Upon delivery, laboratory purity sampling and weighing determines final settlement release within 24 hours.</li>
            </ul>
          </div>

          <!-- Farmer Notes Input (Optional) -->
          <div>
            <label class="block font-bold uppercase tracking-wider text-[11px] text-slate-400 mb-1">
              Producer Acceptance Notes (Optional):
            </label>
            <input
              v-model="signNotes"
              type="text"
              placeholder="e.g. Sowing scheduled on 10 hectares with certified Quncho seed stock..."
              :class="['w-full p-2.5 rounded-xl border text-xs outline-none', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-800']"
            />
          </div>

          <!-- Digital Signature Acceptance Checkbox -->
          <div class="p-3 rounded-xl border border-emerald-500/40 bg-emerald-500/5">
            <label class="flex items-start gap-2.5 cursor-pointer">
              <input
                type="checkbox"
                v-model="agreeLegally"
                class="w-4 h-4 text-emerald-600 rounded mt-0.5 cursor-pointer"
              />
              <span class="text-xs leading-relaxed text-slate-300">
                I, as the authorized producer/farmer, digitally execute this forward supply agreement and confirm readiness to fulfill <strong>{{ selectedContract.quantity }} {{ selectedContract.unit }} of {{ selectedContract.crop_type }}</strong> according to the quality specifications and escrow milestones.
              </span>
            </label>
          </div>
        </div>

        <!-- Modal Footer -->
        <div :class="['p-4 border-t flex items-center justify-between gap-3 shrink-0', isDark ? 'border-slate-800' : 'border-slate-200']">
          <button
            type="button"
            @click="signModalOpen = false"
            :class="['px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer', isDark ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
          >
            Cancel
          </button>

          <button
            type="button"
            @click="submitDigitalSignature"
            :disabled="!agreeLegally || actionLoading"
            class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/30 flex items-center gap-2 transition-all cursor-pointer"
          >
            <span v-if="actionLoading" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <FileSignature v-else class="w-4 h-4" />
            <span>Digitally Sign &amp; Accept Proposal</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ── MODAL 2: ESCROW MILESTONE BREAKDOWN MODAL ── -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div
      v-if="milestoneModalOpen && selectedContract"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-2xl rounded-2xl border shadow-2xl overflow-hidden max-h-[90vh] flex flex-col', isDark ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <div class="flex items-center justify-between p-5 border-b border-slate-700/40 shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <Clock class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold m-0">Escrow Milestone Breakdown</h3>
              <p class="text-xs text-slate-400 m-0">#{{ selectedContract.contract_number }} • 4-Stage Agricultural Clearance</p>
            </div>
          </div>
          <button @click="milestoneModalOpen = false" class="text-slate-400 hover:text-slate-200 text-lg font-bold cursor-pointer">✕</button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs">
          <div
            v-for="(m, idx) in selectedContract.milestones"
            :key="m.id || idx"
            :class="['p-4 rounded-xl border space-y-2 transition-all', getMilestoneBoxStyle(m, selectedContract.status)]"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 font-extrabold text-[11px] flex items-center justify-center">
                  {{ idx + 1 }}
                </span>
                <span class="font-extrabold text-sm text-slate-200">{{ m.title }}</span>
              </div>
              <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold uppercase', getMilestoneStatusBadge(m.status, m.payout_status)]">
                {{ formatMilestoneStatus(m) }}
              </span>
            </div>

            <p class="text-slate-300 leading-relaxed m-0">{{ m.description }}</p>

            <div class="pt-2 flex flex-wrap items-center justify-between gap-2 border-t border-slate-700/30 text-[11px]">
              <span class="text-slate-400">
                Target Due Date: <strong>{{ formatDate(m.due_date) }}</strong>
              </span>
              <div class="flex items-center gap-3">
                <span class="text-slate-400">{{ m.payout_percentage }}% Allocation</span>
                <span v-if="m.payout_amount > 0" class="font-black text-emerald-400 text-xs">
                  ETB {{ formatNumber(m.payout_amount) }}
                </span>
                <button
                  v-if="selectedContract.status === 'active' && m.status !== 'completed'"
                  @click="triggerMilestoneCompletion(selectedContract.id, m.id)"
                  :disabled="actionLoading"
                  class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-bold text-[10px] transition-all cursor-pointer"
                >
                  Mark Completed
                </button>
              </div>
            </div>
          </div>
        </div>

        <div :class="['p-4 border-t flex justify-end shrink-0', isDark ? 'border-slate-800' : 'border-slate-200']">
          <button
            @click="milestoneModalOpen = false"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ── MODAL 3: LEGAL TERMS & SPECIFICATIONS MODAL ── -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div
      v-if="legalTermsModalOpen && selectedContract"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-xl rounded-2xl border shadow-2xl overflow-hidden max-h-[90vh] flex flex-col', isDark ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <div class="flex items-center justify-between p-5 border-b border-slate-700/40 shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
              <FileText class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold m-0">Forward Supply Specifications</h3>
              <p class="text-xs text-slate-400 m-0">#{{ selectedContract.contract_number }}</p>
            </div>
          </div>
          <button @click="legalTermsModalOpen = false" class="text-slate-400 hover:text-slate-200 text-lg font-bold cursor-pointer">✕</button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs leading-relaxed">
          <div>
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400 mb-1">Quality Standard Tolerances:</h4>
            <p class="p-3 rounded-xl border bg-slate-900/40 border-slate-800 text-slate-200 m-0">
              {{ selectedContract.quality_specifications || 'Grade 1 Export White Magna Teff. Moisture content max 11.5%, purity min 99.0%, free of foreign stones and weed seeds, unadulterated, standard 100kg new polypropylene food bags.' }}
            </p>
          </div>

          <div>
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400 mb-1">Escrow Custody &amp; Payment Rules:</h4>
            <p class="p-3 rounded-xl border bg-slate-900/40 border-slate-800 text-slate-200 m-0">
              {{ selectedContract.payment_terms || '30% Advance Escrow Deposit upon digital signature, 40% upon harvesting and threshing inspection, 30% final balance upon warehouse physical inspection.' }}
            </p>
          </div>

          <div>
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400 mb-1">Bilateral Contract Terms:</h4>
            <p class="p-3 rounded-xl border bg-slate-900/40 border-slate-800 text-slate-200 m-0">
              {{ selectedContract.terms_and_conditions || '1. All milestone funds are locked under neutral Chapa & Agricultural Escrow custody. 2. Producer agrees to follow GAP (Good Agricultural Practices) protocol and allow mid-season inspection. 3. Final settlement disbursed immediately upon warehouse digital acceptance voucher.' }}
            </p>
          </div>
        </div>

        <div :class="['p-4 border-t flex justify-end shrink-0', isDark ? 'border-slate-800' : 'border-slate-200']">
          <button
            @click="legalTermsModalOpen = false"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ── MODAL 4: ADD / PROPOSE FORWARD AGREEMENT ── -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div
      v-if="addContractModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-2xl rounded-2xl border shadow-2xl overflow-hidden max-h-[90vh] flex flex-col', isDark ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div :class="['p-5 sm:p-6 border-b flex items-center justify-between shrink-0', isDark ? 'border-slate-800 bg-[#0f172a]' : 'border-slate-200 bg-slate-50']">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <PlusCircle class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base sm:text-lg font-black tracking-tight m-0">Propose Forward Supply Agreement</h2>
              <p class="text-xs text-slate-400 m-0 mt-0.5">Setup commodity terms, escrow milestones, and forward delivery guarantees</p>
            </div>
          </div>
          <button @click="addContractModalOpen = false" class="text-slate-400 hover:text-slate-200 p-1 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <form @submit.prevent="submitNewContractProposal" class="p-6 space-y-4 overflow-y-auto flex-1">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Agreement Title *</label>
            <input
              v-model="newContractForm.title"
              type="text"
              required
              placeholder="e.g. Teff Supply Agreement with Lake Tana Union"
              :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Commodity / Crop Type *</label>
              <select
                v-model="newContractForm.crop_type"
                required
                :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              >
                <option value="Teff">Teff</option>
                <option value="Wheat">Wheat</option>
                <option value="Maize">Maize (Corn)</option>
                <option value="Coffee">Arabica Coffee</option>
                <option value="Soybeans">Soybeans</option>
                <option value="Barley">Malting Barley</option>
                <option value="Sesame">White Humera Sesame</option>
                <option value="Sorghum">Sorghum</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Variety / Grade</label>
              <input
                v-model="newContractForm.variety"
                type="text"
                placeholder="e.g. Quncho DZ-Cr-387 Magna"
                :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Quantity *</label>
              <input
                v-model.number="newContractForm.quantity"
                type="number"
                min="1"
                step="any"
                required
                placeholder="e.g. 200"
                :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Unit</label>
              <select
                v-model="newContractForm.unit"
                :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              >
                <option value="Quintal">Quintal (100 kg)</option>
                <option value="Kg">Kilogram (kg)</option>
                <option value="Metric Ton">Metric Ton</option>
                <option value="Bags">Standard Bags</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Unit Price (ETB) *</label>
              <input
                v-model.number="newContractForm.unit_price"
                type="number"
                min="1"
                step="any"
                required
                placeholder="e.g. 9200"
                :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Advance Escrow Mobilization (%)</label>
              <input
                v-model.number="newContractForm.advance_percentage"
                type="number"
                min="0"
                max="100"
                placeholder="30"
                :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Delivery Target Date *</label>
              <input
                v-model="newContractForm.delivery_date"
                type="date"
                required
                :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Buyer / Union Organization</label>
              <input
                v-model="newContractForm.buyer_name"
                type="text"
                placeholder="Lake Tana Agro Producers Union"
                :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              />
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Delivery Location Depot</label>
              <input
                v-model="newContractForm.delivery_location"
                type="text"
                placeholder="Lake Tana Agro Central Depot, Bahir Dar"
                :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Quality Specifications &amp; Standards</label>
            <textarea
              v-model="newContractForm.quality_specifications"
              rows="2"
              placeholder="Moisture content max 11.5%, purity min 99.0%, standard food bags..."
              :class="['w-full px-3.5 py-2.5 rounded-xl text-xs border focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium resize-none', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
            ></textarea>
          </div>

          <!-- Escrow Calculation Preview Strip -->
          <div :class="['p-3.5 rounded-xl border flex items-center justify-between text-xs', isDark ? 'bg-emerald-950/20 border-emerald-800/40' : 'bg-emerald-50 border-emerald-200']">
            <div>
              <span class="text-slate-400 text-[10px] uppercase font-bold block">Estimated Contract Value</span>
              <span class="font-black text-sm text-emerald-500">
                ETB {{ formatNumber((newContractForm.quantity || 0) * (newContractForm.unit_price || 0)) }}
              </span>
            </div>
            <div class="text-right">
              <span class="text-slate-400 text-[10px] uppercase font-bold block">Advance Escrow ({{ newContractForm.advance_percentage || 30 }}%)</span>
              <span class="font-black text-sm text-purple-400">
                ETB {{ formatNumber(((newContractForm.quantity || 0) * (newContractForm.unit_price || 0)) * ((newContractForm.advance_percentage || 30) / 100)) }}
              </span>
            </div>
          </div>

          <!-- Modal Action Buttons -->
          <div :class="['pt-3 border-t flex items-center justify-end gap-3', isDark ? 'border-slate-800' : 'border-slate-200']">
            <button
              type="button"
              @click="addContractModalOpen = false"
              class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="actionLoading"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2"
            >
              <PlusCircle class="w-4 h-4" />
              <span>{{ actionLoading ? 'Creating Agreement...' : 'Create & Propose Agreement' }}</span>
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
import { useFarmerStore } from '@/stores/farmerStore'
import { useTheme } from '@/composables/useTheme'
import { farmerAPI } from '@/api/farmer'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import {
  FileText, CheckCircle2, Clock, ShieldCheck, BadgeDollarSign,
  Calendar, MapPin, Search, FileSignature, X, Sprout, PlusCircle
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const farmerStore = useFarmerStore()
const { isDark } = useTheme()

const loading = ref(false)
const actionLoading = ref(false)
const toastMessage = ref('')

const searchQuery = ref('')
const activeStatusFilter = ref('')
const sortBy = ref('newest')

// Modals
const signModalOpen = ref(false)
const milestoneModalOpen = ref(false)
const legalTermsModalOpen = ref(false)
const addContractModalOpen = ref(false)
const selectedContract = ref<any>(null)

// Add Agreement Form state
const newContractForm = ref({
  title: '',
  crop_type: 'Teff',
  variety: 'Quncho (DZ-Cr-387)',
  quantity: 200,
  unit: 'Quintal',
  unit_price: 9200,
  advance_percentage: 30,
  delivery_date: '',
  delivery_location: 'Lake Tana Agro Central Depot, Bahir Dar',
  quality_specifications: 'Grade 1 Export Standards, moisture max 11.5%, purity min 99.0%, standard 100kg new food bags.',
  buyer_name: 'Lake Tana Agro Producers Union',
})

// Signing state
const agreeLegally = ref(false)
const signNotes = ref('')

const farmerName = computed(() => authStore.user?.name || '')

const rawContracts = computed(() => {
  const c = farmerStore.contracts
  if (Array.isArray(c)) return c
  if (c && Array.isArray((c as any).data)) return (c as any).data
  return []
})

const stats = computed(() => {
  return farmerStore.contractStats || {
    total_contracts: rawContracts.value.length,
    active_contracts: rawContracts.value.filter(c => ['active', 'in_progress'].includes((c.status || '').toLowerCase())).length,
    pending_contracts: rawContracts.value.filter(c => ['proposed', 'draft'].includes((c.status || '').toLowerCase())).length,
    total_contracted_value: rawContracts.value.reduce((sum, c) => sum + Number(c.total_amount || 0), 0)
  }
})

const filterTabs = computed(() => [
  { label: 'All Contracts', value: '', count: rawContracts.value.length },
  {
    label: 'Pending Review',
    value: 'proposed',
    count: rawContracts.value.filter(c => ['proposed', 'draft', 'pending'].includes((c.status || '').toLowerCase())).length
  },
  {
    label: 'Active Supply',
    value: 'active',
    count: rawContracts.value.filter(c => ['active', 'in_progress'].includes((c.status || '').toLowerCase())).length
  },
  {
    label: 'Completed',
    value: 'completed',
    count: rawContracts.value.filter(c => (c.status || '').toLowerCase() === 'completed').length
  },
  {
    label: 'Declined',
    value: 'cancelled',
    count: rawContracts.value.filter(c => (c.status || '').toLowerCase() === 'cancelled').length
  }
])

const filteredContracts = computed(() => {
  let list = [...rawContracts.value]

  if (activeStatusFilter.value) {
    const f = activeStatusFilter.value.toLowerCase()
    if (f === 'proposed') {
      list = list.filter(c => ['proposed', 'draft', 'pending'].includes((c.status || '').toLowerCase()))
    } else if (f === 'active') {
      list = list.filter(c => ['active', 'in_progress'].includes((c.status || '').toLowerCase()))
    } else {
      list = list.filter(c => (c.status || '').toLowerCase() === f)
    }
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(c =>
      (c.contract_number || '').toLowerCase().includes(q) ||
      (c.title || '').toLowerCase().includes(q) ||
      (c.crop_type || '').toLowerCase().includes(q) ||
      (c.buyer?.name || '').toLowerCase().includes(q)
    )
  }

  switch (sortBy.value) {
    case 'highest_value':
      list.sort((a, b) => Number(b.total_amount || 0) - Number(a.total_amount || 0))
      break
    case 'lowest_value':
      list.sort((a, b) => Number(a.total_amount || 0) - Number(b.total_amount || 0))
      break
    case 'delivery_date':
      list.sort((a, b) => new Date(a.delivery_date).getTime() - new Date(b.delivery_date).getTime())
      break
    case 'newest':
    default:
      list.sort((a, b) => new Date(b.created_at || b.start_date).getTime() - new Date(a.created_at || a.start_date).getTime())
      break
  }

  return list
})

const loadContracts = async () => {
  loading.value = true
  try {
    await farmerStore.fetchContracts()
  } catch (err: any) {
    console.error('Failed to load contracts:', err)
  } finally {
    loading.value = false
  }
}

const openSignModal = (contract: any) => {
  selectedContract.value = contract
  agreeLegally.value = false
  signNotes.value = ''
  signModalOpen.value = true
}

const openMilestoneModal = (contract: any) => {
  selectedContract.value = contract
  milestoneModalOpen.value = true
}

const openLegalTermsModal = (contract: any) => {
  selectedContract.value = contract
  legalTermsModalOpen.value = true
}

const openAddContractDialog = () => {
  const targetDate = new Date()
  targetDate.setDate(targetDate.getDate() + 90)
  newContractForm.value = {
    title: 'Teff Supply Agreement with Lake Tana Union',
    crop_type: 'Teff',
    variety: 'Quncho (DZ-Cr-387)',
    quantity: 200,
    unit: 'Quintal',
    unit_price: 9200,
    advance_percentage: 30,
    delivery_date: targetDate.toISOString().slice(0, 10),
    delivery_location: 'Lake Tana Agro Central Depot, Bahir Dar',
    quality_specifications: 'Grade 1 Export Standards, moisture max 11.5%, purity min 99.0%, standard 100kg new food bags.',
    buyer_name: 'Lake Tana Agro Producers Union',
  }
  addContractModalOpen.value = true
}

const submitNewContractProposal = async () => {
  actionLoading.value = true
  try {
    await farmerStore.createContract(newContractForm.value)
    addContractModalOpen.value = false
    toastMessage.value = `Agreement "${newContractForm.value.title}" successfully proposed! Escrow milestones initialized.`
    await loadContracts()
  } catch (err: any) {
    alert(err?.response?.data?.message || 'Failed to create agreement proposal.')
  } finally {
    actionLoading.value = false
  }
}

const submitDigitalSignature = async () => {
  if (!selectedContract.value || !agreeLegally.value) return
  actionLoading.value = true
  try {
    await farmerStore.acceptContract(selectedContract.value.id, { notes: signNotes.value })
    signModalOpen.value = false
    toastMessage.value = `Contract #${selectedContract.value.contract_number} has been legally signed! ETB ${formatNumber(selectedContract.value.advance_payment || (selectedContract.value.total_amount * 0.3))} mobilization advance is locked in Escrow.`
    await loadContracts()
  } catch (err: any) {
    alert(err?.response?.data?.message || 'Failed to sign agreement.')
  } finally {
    actionLoading.value = false
  }
}

const rejectContractProposal = async (id: number) => {
  const reason = window.prompt('Please provide a reason for declining this forward agreement proposal:')
  if (reason === null) return
  actionLoading.value = true
  try {
    await farmerStore.rejectContract(id, { reason })
    toastMessage.value = 'Forward supply proposal declined.'
    await loadContracts()
  } catch (err: any) {
    alert(err?.response?.data?.message || 'Failed to decline proposal.')
  } finally {
    actionLoading.value = false
  }
}

const triggerMilestoneCompletion = async (contractId: number, milestoneId: number) => {
  if (!window.confirm('Mark this milestone as completed and request escrow verification?')) return
  actionLoading.value = true
  try {
    await farmerAPI.completeMilestone(contractId, milestoneId)
    toastMessage.value = 'Milestone status updated and registered for escrow inspection.'
    await loadContracts()
    if (selectedContract.value) {
      const updated = rawContracts.value.find(c => c.id === contractId)
      if (updated) selectedContract.value = updated
    }
  } catch (err: any) {
    alert(err?.response?.data?.message || 'Failed to update milestone.')
  } finally {
    actionLoading.value = false
  }
}

const resetFilters = () => {
  searchQuery.value = ''
  activeStatusFilter.value = ''
  sortBy.value = 'newest'
}

// Styling helpers
const getStatusBadgeStyle = (status: string) => {
  const s = (status || '').toLowerCase()
  if (['proposed', 'draft', 'pending'].includes(s)) {
    return 'bg-amber-500/15 text-amber-500 border border-amber-500/30'
  }
  if (['active', 'in_progress'].includes(s)) {
    return 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30'
  }
  if (s === 'completed') {
    return 'bg-blue-500/15 text-blue-400 border border-blue-500/30'
  }
  if (s === 'cancelled') {
    return 'bg-red-500/15 text-red-400 border border-red-500/30'
  }
  return 'bg-slate-500/15 text-slate-400 border border-slate-500/30'
}

const formatStatusLabel = (status: string) => {
  const s = (status || '').toLowerCase()
  if (['proposed', 'draft', 'pending'].includes(s)) return 'Pending Proposal Review'
  if (['active', 'in_progress'].includes(s)) return 'Active Production'
  if (s === 'completed') return 'Completed & Settled'
  if (s === 'cancelled') return 'Declined'
  return status || 'Proposed'
}

const getMilestoneBoxStyle = (m: any, contractStatus: string) => {
  if (m.status === 'completed') {
    return isDark.value ? 'bg-emerald-950/20 border-emerald-800/60' : 'bg-emerald-50 border-emerald-300'
  }
  if (m.payout_status === 'in_escrow' || contractStatus === 'active') {
    return isDark.value ? 'bg-blue-950/20 border-blue-800/50' : 'bg-blue-50 border-blue-200'
  }
  return isDark.value ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200'
}

const getMilestoneStatusBadge = (status: string, payoutStatus: string) => {
  if (status === 'completed') return 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
  if (payoutStatus === 'in_escrow') return 'bg-purple-500/20 text-purple-400 border border-purple-500/30'
  if (status === 'in_progress') return 'bg-blue-500/20 text-blue-400 border border-blue-500/30'
  return 'bg-slate-700/50 text-slate-400'
}

const formatMilestoneStatus = (m: any) => {
  if (m.status === 'completed') return 'Cleared'
  if (m.payout_status === 'in_escrow') return 'Funds in Escrow'
  if (m.status === 'in_progress') return 'In Progress'
  return 'Pending'
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

const formatNumber = (num: number | string | undefined | null) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(Number(num || 0))
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  loadContracts()
})
</script>

<style scoped>
/* Page Header matching Crop Activities design */
.page-header {
  background: white;
  padding: 24px;
  margin: -16px -16px 24px -16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: background-color 0.2s, border-color 0.2s;
}

@media (min-width: 640px) {
  .page-header {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

@media (min-width: 1024px) {
  .page-header {
    margin: -32px -32px 28px -32px;
    padding: 30px;
  }
}

.page-header h1 {
  font-size: 28px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 8px 0;
  letter-spacing: -0.02em;
}

.page-header p {
  color: #6b7280;
  font-size: 14px;
  margin: 0;
}

.header-titles {
  flex: 1;
}

.page-header .btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: #10b981;
  color: white;
  padding: 10px 18px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: background-color 0.2s;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  white-space: nowrap;
  align-self: flex-start;
}

@media (min-width: 640px) {
  .page-header .btn-primary {
    align-self: center;
  }
}

.page-header .btn-primary:hover {
  background-color: #059669;
}

.page-header .icon {
  width: 18px;
  height: 18px;
}

/* Page Header - Dark Mode */
.page-header.dark,
:deep(.dark) .page-header {
  background: #131b2e;
  border-bottom-color: #1f293d;
}

.page-header.dark h1,
:deep(.dark) .page-header h1 {
  color: #f1f5f9;
}

.page-header.dark p,
:deep(.dark) .page-header p {
  color: #94a3b8;
}

.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
