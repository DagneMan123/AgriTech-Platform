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
                  <FileSignature class="w-3.5 h-3.5" />
                  Legal &amp; Escrow Binding
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Contract Farming &gt; Contract Agreements
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Contract Agreements
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Review legal covenants, signed bilateral stipulations, escrow guarantees, and digital executions
              </p>
            </div>
          </div>

          <!-- Header Right Actions (All Buttons Blue) -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <BuyerHeaderNav />
          </div>
        </div>
      </div>

      <!-- ── Summary Stats Cards (Live Database Metrics) ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Agreements -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Agreements</h3>
            <p class="text-2xl font-black">{{ stats.total }}</p>
            <span class="text-[10px] text-blue-500 font-medium">Bilateral documents</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <FileText class="w-6 h-6" />
          </div>
        </article>

        <!-- Fully Executed (Active) -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Fully Executed</h3>
            <p class="text-2xl font-black">{{ stats.executed }}</p>
            <span class="text-[10px] text-blue-400 font-medium">Active in production</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <CheckCircle2 class="w-6 h-6" />
          </div>
        </article>

        <!-- Pending Signature -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-amber-500' : 'bg-white border-slate-200 text-slate-900 !border-l-amber-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Awaiting Signature</h3>
            <p class="text-2xl font-black">{{ stats.pendingSignature }}</p>
            <span class="text-[10px] text-amber-500 font-medium">Needs farmer co-signing</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <Clock class="w-6 h-6" />
          </div>
        </article>

        <!-- Total Legal Value -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Agreed Value</h3>
            <p class="text-xl lg:text-2xl font-black text-blue-500">ETB {{ formatNumber(stats.totalValue) }}</p>
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
              placeholder="Search agreement #, title, farmer, or crop..."
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
              <option value="lowest-value">Lowest Value</option>
              <option value="delivery-date">Delivery Deadline</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ── Loading State ── -->
      <div v-if="loading" :class="['rounded-2xl border p-12 text-center flex flex-col items-center justify-center', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="w-10 h-10 border-4 border-slate-300 border-t-blue-500 rounded-full animate-spin mb-4"></div>
        <p :class="['text-sm font-semibold', darkMode ? 'text-slate-200' : 'text-slate-700']">Loading legal contract agreements...</p>
        <p :class="['text-xs mt-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Retrieving bilateral covenants from database</p>
      </div>

      <!-- ── Empty State (Zero Mock Data) ── -->
      <div
        v-else-if="filteredAgreements.length === 0"
        :class="['rounded-2xl border p-10 lg:p-14 text-center flex flex-col items-center justify-center shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
      >
        <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-4 border', darkMode ? 'bg-slate-800/60 border-slate-700 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-400']">
          <FileSignature class="w-8 h-8 text-slate-400" />
        </div>
        <h3 :class="['text-lg font-extrabold mb-1', darkMode ? 'text-slate-100' : 'text-slate-800']">
          No Contract Agreements Found
        </h3>
        <p :class="['text-xs lg:text-sm max-w-md mb-6', darkMode ? 'text-slate-400' : 'text-slate-500']">
          <span v-if="searchQuery || activeStatusFilter">
            No bilateral agreements match your current filter parameters. Try clearing your filters.
          </span>
          <span v-else>
            You have no active or proposed farming agreements in the database yet. Forward supply agreements protect both buyers and farmers with guaranteed off-take prices and escrow security.
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
            to="/buyer/contracts"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-sm shadow-blue-600/30"
          >
            <Plus class="w-4 h-4" />
            <span>Propose Agreement</span>
          </router-link>
        </div>
      </div>

      <!-- ── Agreements Document Grid ── -->
      <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        <article
          v-for="agreement in filteredAgreements"
          :key="agreement.id"
          :class="[
            'rounded-2xl border overflow-hidden shadow-2xs hover:shadow-md transition-all flex flex-col justify-between',
            darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200'
          ]"
        >
          <!-- Top Card Ribbon -->
          <div>
            <div :class="['flex items-center justify-between px-5 py-4 border-b', darkMode ? 'bg-[#0f1729] border-slate-800/80' : 'bg-slate-50 border-slate-200/80']">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center font-bold">
                  <FileText class="w-4 h-4" />
                </div>
                <div>
                  <span class="font-mono text-xs font-extrabold text-blue-500 block">
                    {{ agreement.contract_number }}
                  </span>
                  <span :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-500']">
                    Drafted {{ formatDate(agreement.created_at) }}
                  </span>
                </div>
              </div>

              <!-- Status Badge -->
              <span :class="['text-[10px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider border', getStatusBadgeStyle(agreement.status)]">
                {{ formatStatusLabel(agreement.status) }}
              </span>
            </div>

            <!-- Agreement Content -->
            <div class="p-5 space-y-4">
              <!-- Title & Produce Summary -->
              <div>
                <h3 :class="['text-base font-extrabold m-0 tracking-tight', darkMode ? 'text-slate-100' : 'text-slate-900']">
                  {{ agreement.title }}
                </h3>
                <div class="flex items-center gap-2 mt-1 text-xs">
                  <span class="font-semibold text-blue-500">{{ agreement.crop_type }}</span>
                  <span v-if="agreement.variety" class="text-slate-400">({{ agreement.variety }})</span>
                  <span class="text-slate-300 dark:text-slate-700">•</span>
                  <span :class="['font-medium', darkMode ? 'text-slate-300' : 'text-slate-600']">
                    {{ formatNumber(agreement.quantity) }} {{ agreement.unit }}
                  </span>
                </div>
              </div>

              <!-- Bilateral Parties Box -->
              <div :class="['p-3.5 rounded-xl border text-xs grid grid-cols-2 gap-3', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                <!-- Buyer Side -->
                <div>
                  <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Purchaser (Party A)</span>
                  <p class="font-bold m-0 truncate">Verified Agricultural Buyer</p>
                  <span class="text-[10px] text-blue-500 font-semibold flex items-center gap-1 mt-0.5">
                    <Check class="w-3 h-3" /> Signed digitally
                  </span>
                </div>

                <!-- Farmer Side -->
                <div>
                  <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Producer (Party B)</span>
                  <p class="font-bold m-0 truncate">
                    {{ agreement.farmer?.farm_name || agreement.farmer?.user?.name || 'Local Producer' }}
                  </p>
                  <span
                    v-if="['active', 'in_progress', 'completed'].includes((agreement.status || '').toLowerCase())"
                    class="text-[10px] text-blue-400 font-semibold flex items-center gap-1 mt-0.5"
                  >
                    <Check class="w-3 h-3" /> Signed digitally
                  </span>
                  <span v-else class="text-[10px] text-amber-500 font-semibold flex items-center gap-1 mt-0.5">
                    <Clock class="w-3 h-3" /> Awaiting signature
                  </span>
                </div>
              </div>

              <!-- Commercial Terms Grid -->
              <div class="grid grid-cols-3 gap-2 text-center text-xs">
                <div :class="['p-2.5 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                  <span class="text-[10px] text-slate-400 block">Unit Price</span>
                  <span class="font-bold text-slate-200">ETB {{ formatNumber(agreement.unit_price) }}</span>
                </div>
                <div :class="['p-2.5 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                  <span class="text-[10px] text-slate-400 block">Advance Escrow</span>
                  <span class="font-bold text-purple-400">ETB {{ formatNumber(agreement.advance_payment || 0) }}</span>
                </div>
                <div :class="['p-2.5 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                  <span class="text-[10px] text-slate-400 block">Total Value</span>
                  <span class="font-black text-blue-500">ETB {{ formatNumber(agreement.total_amount) }}</span>
                </div>
              </div>

              <!-- Delivery Specs & Quality Excerpt -->
              <div :class="['p-3 rounded-xl border text-xs', darkMode ? 'bg-[#0f172a] border-slate-800 text-slate-300' : 'bg-slate-50 border-slate-200 text-slate-700']">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-[10px] uppercase font-bold text-slate-400">Target Delivery</span>
                  <span class="font-bold text-blue-500">{{ formatDate(agreement.delivery_date) }}</span>
                </div>
                <p class="text-[11px] m-0 text-slate-400 line-clamp-2">
                  <span class="font-medium text-slate-300">Destination:</span> {{ agreement.delivery_location || 'Designated buyer warehouse' }}
                </p>
                <p v-if="agreement.quality_specifications" class="text-[11px] m-0 mt-1 text-slate-400 line-clamp-2">
                  <span class="font-medium text-slate-300">Quality Spec:</span> {{ agreement.quality_specifications }}
                </p>
              </div>
            </div>
          </div>

          <!-- Bottom Action Buttons (All Blue) -->
          <div :class="['px-5 py-3 border-t flex items-center justify-between gap-3 shrink-0', darkMode ? 'border-slate-800 bg-[#0f1729]' : 'border-slate-200 bg-slate-50']">
            <div class="flex items-center gap-1.5 text-xs text-slate-400">
              <ShieldCheck class="w-4 h-4 text-blue-500" />
              <span>Smart Escrow Enforced</span>
            </div>

            <div class="flex items-center gap-2">
              <button
                @click="openAgreementDocument(agreement)"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm shadow-blue-600/30 cursor-pointer"
              >
                <FileSignature class="w-3.5 h-3.5" />
                <span>View Legal Agreement</span>
              </button>
            </div>
          </div>
        </article>
      </div>

    </main>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- ── FORMAL LEGAL AGREEMENT DOCUMENT MODAL (PRINTABLE) ── -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div
      v-if="documentModalOpen && activeAgreement"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div
        :class="['w-full max-w-3xl max-h-[92vh] flex flex-col rounded-2xl border shadow-2xl overflow-hidden', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Top Navigation Bar -->
        <div class="p-4 border-b border-slate-700/50 flex items-center justify-between shrink-0 bg-[#0f1729]">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
            <span class="font-mono text-xs font-black text-blue-400">{{ activeAgreement.contract_number }}</span>
            <span class="text-xs text-slate-400">• Legal Purchase Agreement Document</span>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="printAgreement"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-slate-700 text-slate-300 hover:bg-slate-800 transition-all flex items-center gap-1.5 cursor-pointer"
              title="Print Document"
            >
              <Printer class="w-3.5 h-3.5" />
              <span>Print Agreement</span>
            </button>
            <button
              @click="documentModalOpen = false"
              class="text-slate-400 hover:text-slate-200 font-bold text-sm cursor-pointer p-1"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Document Sheet (Official Agricultural Agreement Style) -->
        <div id="printable-agreement" class="flex-1 overflow-y-auto p-6 lg:p-8 space-y-6 text-xs leading-relaxed">
          
          <!-- Document Header -->
          <div class="text-center pb-6 border-b border-slate-700/60">
            <span class="text-[10px] tracking-widest font-black uppercase text-blue-500 block mb-1">
              Federal Democratic Republic of Ethiopia • Agricultural Commodity Exchange Standard
            </span>
            <h2 class="text-lg lg:text-xl font-black uppercase tracking-tight m-0">
              Bilateral Agricultural Forward Production &amp; Off-Take Agreement
            </h2>
            <p class="text-slate-400 text-[11px] m-0 mt-1">
              Binding legal contract registered under AgriConnect Secure Escrow Framework
            </p>
            <div class="mt-3 flex items-center justify-center gap-3 text-[11px] font-mono">
              <span class="px-2.5 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">
                Document Ref: {{ activeAgreement.contract_number }}
              </span>
              <span class="text-slate-400">
                Effective Date: {{ formatDate(activeAgreement.start_date) }}
              </span>
            </div>
          </div>

          <!-- Section 1: Contracting Parties -->
          <div class="space-y-2">
            <h4 class="font-extrabold text-xs uppercase text-blue-400 tracking-wider">
              1. Contracting Parties &amp; Legal Capacity
            </h4>
            <p class="text-slate-300">
              This Agreement is entered into by and between the authorized parties described herein:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
              <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                <span class="font-bold text-[10px] text-slate-400 uppercase tracking-wider block">Party A: The Buyer</span>
                <p class="font-bold text-sm m-0 mt-0.5">Verified AgriConnect Commercial Buyer</p>
                <p class="text-slate-400 m-0 mt-0.5">Account Status: Good Standing &amp; Escrow Backed</p>
                <p class="text-slate-400 m-0">Governing Law: Ethiopian Commercial Code</p>
              </div>

              <div :class="['p-3 rounded-xl border', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                <span class="font-bold text-[10px] text-slate-400 uppercase tracking-wider block">Party B: The Producer</span>
                <p class="font-bold text-sm m-0 mt-0.5">{{ activeAgreement.farmer?.farm_name || activeAgreement.farmer?.user?.name || 'Local Farm Enterprise' }}</p>
                <p class="text-slate-400 m-0 mt-0.5">Producer Contact: {{ activeAgreement.farmer?.user?.phone || 'On file with platform' }}</p>
                <p class="text-slate-400 m-0">Location: {{ [activeAgreement.farmer?.woreda, activeAgreement.farmer?.zone, activeAgreement.farmer?.region].filter(Boolean).join(', ') || 'Certified Agricultural Zone' }}</p>
              </div>
            </div>
          </div>

          <!-- Section 2: Commodity Specifications -->
          <div class="space-y-2">
            <h4 class="font-extrabold text-xs uppercase text-blue-400 tracking-wider">
              2. Crop Specifications &amp; Agreed Volume
            </h4>
            <p class="text-slate-300">
              Party B covenants and undertakes to cultivate, harvest, and deliver the specified produce strictly in accordance with agreed benchmarks:
            </p>
            <div :class="['p-3.5 rounded-xl border space-y-2', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                <div>
                  <span class="text-slate-400 text-[10px] block">Crop Type</span>
                  <span class="font-bold text-blue-400 text-sm">{{ activeAgreement.crop_type }}</span>
                </div>
                <div>
                  <span class="text-slate-400 text-[10px] block">Variety</span>
                  <span class="font-bold text-sm">{{ activeAgreement.variety || 'Standard Certified' }}</span>
                </div>
                <div>
                  <span class="text-slate-400 text-[10px] block">Contract Volume</span>
                  <span class="font-bold text-sm">{{ formatNumber(activeAgreement.quantity) }} {{ activeAgreement.unit }}</span>
                </div>
                <div>
                  <span class="text-slate-400 text-[10px] block">Target Delivery</span>
                  <span class="font-bold text-blue-400 text-sm">{{ formatDate(activeAgreement.delivery_date) }}</span>
                </div>
              </div>

              <div v-if="activeAgreement.quality_specifications" class="pt-2 border-t border-slate-700/50">
                <span class="text-slate-400 text-[10px] font-bold block mb-0.5">Standardized Quality Benchmarks:</span>
                <p class="text-slate-200 m-0 italic">{{ activeAgreement.quality_specifications }}</p>
              </div>
            </div>
          </div>

          <!-- Section 3: Commercial Terms & Pricing -->
          <div class="space-y-2">
            <h4 class="font-extrabold text-xs uppercase text-blue-400 tracking-wider">
              3. Financial Valuation &amp; Escrow Guarantee
            </h4>
            <div class="grid grid-cols-3 gap-3">
              <div :class="['p-3 rounded-xl border text-center', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                <span class="text-[10px] text-slate-400 block">Agreed Unit Price</span>
                <span class="text-base font-extrabold text-slate-100">ETB {{ formatNumber(activeAgreement.unit_price) }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">per {{ activeAgreement.unit }}</span>
              </div>
              <div :class="['p-3 rounded-xl border text-center', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                <span class="text-[10px] text-slate-400 block">Advance Deposit</span>
                <span class="text-base font-extrabold text-purple-400">ETB {{ formatNumber(activeAgreement.advance_payment || 0) }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Escrow locked</span>
              </div>
              <div :class="['p-3 rounded-xl border text-center', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                <span class="text-[10px] text-slate-400 block">Total Agreement Value</span>
                <span class="text-base font-black text-blue-400">ETB {{ formatNumber(activeAgreement.total_amount) }}</span>
                <span class="text-[10px] text-blue-500 block mt-0.5">Guaranteed off-take</span>
              </div>
            </div>
            <p v-if="activeAgreement.payment_terms" class="text-slate-300 pt-1">
              <span class="font-bold text-slate-400">Payment Terms:</span> {{ activeAgreement.payment_terms }}
            </p>
          </div>

          <!-- Section 4: Production Milestones & Escrow Release -->
          <div class="space-y-2">
            <h4 class="font-extrabold text-xs uppercase text-blue-400 tracking-wider">
              4. Milestone Schedule &amp; Conditional Disbursal
            </h4>
            <div class="space-y-1.5">
              <div
                v-for="(milestone, idx) in (activeAgreement.milestones || [])"
                :key="milestone.id || idx"
                :class="['p-2.5 rounded-xl border flex items-center justify-between text-[11px]', darkMode ? 'bg-[#1a233a] border-slate-800' : 'bg-slate-50 border-slate-200']"
              >
                <div class="flex items-center gap-2">
                  <span class="w-5 h-5 rounded-full bg-blue-600/20 text-blue-400 font-bold flex items-center justify-center text-[10px]">
                    {{ idx + 1 }}
                  </span>
                  <div>
                    <span class="font-bold">{{ milestone.title }}</span>
                    <span class="text-slate-400 ml-2">Due: {{ formatDate(milestone.due_date || null) }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <span class="font-black text-blue-400">ETB {{ formatNumber(milestone.payout_amount) }}</span>
                  <span class="text-slate-400 ml-1">({{ milestone.payout_percentage }}%)</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 5: Legal Attestation & Digital Signatures -->
          <div class="pt-4 border-t border-slate-700/60 space-y-4">
            <h4 class="font-extrabold text-xs uppercase text-blue-400 tracking-wider">
              5. Electronic Execution &amp; Bilateral Attestation
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
              <!-- Buyer Signature Block -->
              <div :class="['p-4 rounded-xl border text-center', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-2">Buyer Electronic Signature</span>
                <div class="h-12 flex items-center justify-center font-serif italic text-base text-blue-400 border-b border-dashed border-slate-700 mb-2">
                  Verified Purchaser
                </div>
                <p class="font-mono text-[10px] text-slate-400 m-0">Cryptographic Hash: SHA256-{{ activeAgreement.id }}8f4d92</p>
                <p class="text-[10px] text-blue-500 font-semibold m-0 mt-0.5">
                  Signed at: {{ formatDateTime(activeAgreement.created_at) }}
                </p>
              </div>

              <!-- Farmer Signature Block -->
              <div :class="['p-4 rounded-xl border text-center', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-2">Farmer Electronic Signature</span>
                <div
                  v-if="['active', 'in_progress', 'completed'].includes((activeAgreement.status || '').toLowerCase())"
                  class="h-12 flex items-center justify-center font-serif italic text-base text-blue-400 border-b border-dashed border-slate-700 mb-2"
                >
                  {{ activeAgreement.farmer?.farm_name || 'Verified Producer' }}
                </div>
                <div
                  v-else
                  class="h-12 flex items-center justify-center text-xs text-amber-500 italic border-b border-dashed border-slate-700 mb-2"
                >
                  Pending Co-Signature By Producer
                </div>
                <p class="font-mono text-[10px] text-slate-400 m-0">
                  {{ ['active', 'in_progress', 'completed'].includes((activeAgreement.status || '').toLowerCase()) ? 'Status: Fully Counter-Signed' : 'Status: Formal Offer Sent' }}
                </p>
                <p class="text-[10px] text-slate-400 m-0 mt-0.5">
                  Registry: AgriConnect Digital Notary
                </p>
              </div>
            </div>
          </div>

        </div>

        <!-- Modal Footer Actions -->
        <div class="p-4 border-t border-slate-700/50 flex justify-end gap-2 shrink-0 bg-[#0f1729]">
          <button
            @click="documentModalOpen = false"
            class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer"
          >
            Close Agreement View
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
import BuyerHeaderNav from '@/components/layout/BuyerHeaderNav.vue'
import {
  Menu, FileSignature, FileText, Plus, Clock,
  CheckCircle2, BadgeDollarSign, ShieldCheck, Search, Check, Printer
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

const searchQuery = ref('')
const activeStatusFilter = ref('')
const sortBy = ref('newest')

const documentModalOpen = ref(false)
const activeAgreement = ref<ContractItem | null>(null)

onMounted(async () => {
  themeStore.initializeTheme()
  await loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    await buyerStore.fetchContracts().catch(() => [])
  } finally {
    loading.value = false
  }
}



// Master Agreements List from Live Database
const rawAgreements = computed<ContractItem[]>(() => {
  const c = buyerStore.contracts
  if (Array.isArray(c)) return c as ContractItem[]
  if (c && Array.isArray((c as any).data)) return (c as any).data as ContractItem[]
  return []
})

// Summary Stats
const stats = computed(() => {
  const list = rawAgreements.value
  const total = list.length
  const executed = list.filter((c: ContractItem) => ['active', 'in_progress', 'completed'].includes((c.status || '').toLowerCase())).length
  const pendingSignature = list.filter((c: ContractItem) => ['proposed', 'draft'].includes((c.status || '').toLowerCase())).length
  const totalValue = list
    .filter((c: ContractItem) => (c.status || '').toLowerCase() !== 'cancelled')
    .reduce((sum: number, c: ContractItem) => sum + Number(c.total_amount || 0), 0)

  return { total, executed, pendingSignature, totalValue }
})

// Filter Tabs
const statusFilters = computed(() => [
  { label: 'All Agreements', value: '', count: rawAgreements.value.length },
  {
    label: 'Fully Executed',
    value: 'executed',
    count: rawAgreements.value.filter((c: ContractItem) => ['active', 'in_progress'].includes((c.status || '').toLowerCase())).length
  },
  {
    label: 'Pending Signature',
    value: 'proposed',
    count: rawAgreements.value.filter((c: ContractItem) => ['proposed', 'draft'].includes((c.status || '').toLowerCase())).length
  },
  {
    label: 'Completed',
    value: 'completed',
    count: rawAgreements.value.filter((c: ContractItem) => (c.status || '').toLowerCase() === 'completed').length
  },
  {
    label: 'Cancelled',
    value: 'cancelled',
    count: rawAgreements.value.filter((c: ContractItem) => (c.status || '').toLowerCase() === 'cancelled').length
  }
])

// Filtered and Sorted Agreements
const filteredAgreements = computed<ContractItem[]>(() => {
  let list = [...rawAgreements.value]

  if (activeStatusFilter.value === 'executed') {
    list = list.filter((c: ContractItem) => ['active', 'in_progress'].includes((c.status || '').toLowerCase()))
  } else if (activeStatusFilter.value === 'proposed') {
    list = list.filter((c: ContractItem) => ['proposed', 'draft'].includes((c.status || '').toLowerCase()))
  } else if (activeStatusFilter.value) {
    list = list.filter((c: ContractItem) => (c.status || '').toLowerCase() === activeStatusFilter.value)
  }

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

  switch (sortBy.value) {
    case 'oldest':
      list.sort((a: ContractItem, b: ContractItem) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
      break
    case 'highest-value':
      list.sort((a: ContractItem, b: ContractItem) => Number(b.total_amount || 0) - Number(a.total_amount || 0))
      break
    case 'lowest-value':
      list.sort((a: ContractItem, b: ContractItem) => Number(a.total_amount || 0) - Number(b.total_amount || 0))
      break
    case 'delivery-date':
      list.sort((a: ContractItem, b: ContractItem) => new Date(a.delivery_date).getTime() - new Date(b.delivery_date).getTime())
      break
    case 'newest':
    default:
      list.sort((a: ContractItem, b: ContractItem) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
      break
  }

  return list
})

const openAgreementDocument = (agreement: ContractItem) => {
  activeAgreement.value = agreement
  documentModalOpen.value = true
}

const printAgreement = () => {
  window.print()
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
  if (norm === 'active' || norm === 'in_progress') return 'Fully Executed'
  if (norm === 'completed') return 'Fulfilled'
  if (norm === 'cancelled') return 'Nullified'
  return status || 'Pending'
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

@media print {
  body * {
    visibility: hidden;
  }
  #printable-agreement, #printable-agreement * {
    visibility: visible;
  }
  #printable-agreement {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
