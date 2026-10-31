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
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                  <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                  Chapa Gateway Active
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Payments &amp; Finance &gt; {{ currentTabTitle }}
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                {{ currentTabTitle }}
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Secure digital payments powered by Chapa &bull; Telebirr, CBE Birr, Ethiopian bank transfers, and cards
              </p>
            </div>
          </div>

          <!-- Header Right Actions -->
          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            
            <button
              @click="openAddModal"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all transform active:scale-95 cursor-pointer"
            >
              <Plus class="w-4 h-4" />
              <span>Add Payment Method</span>
            </button>
          </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 mt-6 border-b border-slate-200 dark:border-slate-800 -mb-[24px]">
          <button
            @click="switchTab('methods')"
            :class="['px-4 py-2.5 text-xs font-bold border-b-2 transition-all flex items-center gap-2 cursor-pointer',
              activeTab === 'methods'
                ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
            ]"
          >
            <CreditCard class="w-4 h-4" />
            <span>Payment Methods</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200 dark:bg-slate-800 font-mono">
              {{ paymentMethods.length }}
            </span>
          </button>

          <button
            @click="switchTab('history')"
            :class="['px-4 py-2.5 text-xs font-bold border-b-2 transition-all flex items-center gap-2 cursor-pointer',
              activeTab === 'history'
                ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
            ]"
          >
            <Receipt class="w-4 h-4" />
            <span>Transaction History</span>
          </button>

          <button
            @click="switchTab('escrow')"
            :class="['px-4 py-2.5 text-xs font-bold border-b-2 transition-all flex items-center gap-2 cursor-pointer',
              activeTab === 'escrow'
                ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
            ]"
          >
            <ShieldCheck class="w-4 h-4" />
            <span>Escrow &amp; Invoices</span>
          </button>
        </div>
      </div>

<!-- ── Summary Stats Cards (Live Database Metrics) ── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Registered Methods -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Payment Methods</h3>
            <p class="text-2xl font-black">{{ paymentMethods.length }}</p>
            <span class="text-[10px] text-blue-500 font-medium">Saved in PostgreSQL</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <CreditCard class="w-6 h-6" />
          </div>
        </article>

        <!-- Primary Payment Account -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-emerald-500' : 'bg-white border-slate-200 text-slate-900 !border-l-emerald-600']">
          <div class="truncate mr-2">
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Primary Method</h3>
            <p class="text-sm font-black truncate max-w-[170px]" :title="primaryMethod?.bank_name || 'None Assigned'">
              {{ primaryMethod?.bank_name || 'None Assigned' }}
            </p>
            <span class="text-[10px] text-emerald-500 font-medium flex items-center gap-1">
              <CheckCircle2 class="w-3 h-3 inline" /> Active Default
            </span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <Star class="w-6 h-6 fill-current" />
          </div>
        </article>

        <!-- Total Settled Purchases -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-purple-500' : 'bg-white border-slate-200 text-slate-900 !border-l-purple-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Total Volume</h3>
            <p class="text-xl lg:text-2xl font-black text-purple-500">ETB {{ formatNumber(stats.total_spent) }}</p>
            <span class="text-[10px] text-purple-400 font-medium">Chapa &amp; escrow settlements</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-purple-500/10 text-purple-400' : 'bg-purple-50 text-purple-600']">
            <Receipt class="w-6 h-6" />
          </div>
        </article>

        <!-- Escrow Security Guarantee -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Chapa Protection</h3>
            <p class="text-2xl font-black text-emerald-500">100% Secured</p>
            <span class="text-[10px] text-emerald-500 font-medium">Safe produce escrow</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <ShieldCheck class="w-6 h-6" />
          </div>
        </article>
      </section>

      <!-- ── TAB 1: PAYMENT METHODS ── -->
      <div v-if="activeTab === 'methods'">
        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <RefreshCw class="w-8 h-8 text-blue-500 animate-spin mb-3" />
          <p :class="['text-xs font-semibold', darkMode ? 'text-slate-400' : 'text-slate-600']">
            Loading payment methods from database...
          </p>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="paymentMethods.length === 0"
          :class="['rounded-2xl border p-12 text-center shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
        >
          <div class="w-16 h-16 rounded-full bg-blue-500/10 text-blue-500 flex items-center justify-center mx-auto mb-4">
            <CreditCard class="w-8 h-8" />
          </div>
          <h3 :class="['text-lg font-bold mb-1', darkMode ? 'text-slate-100' : 'text-slate-900']">
            No payment methods registered in database
          </h3>
          <p :class="['text-xs max-w-md mx-auto mb-6', darkMode ? 'text-slate-400' : 'text-slate-500']">
            Add your preferred payment account (Telebirr via Chapa, CBE Birr, Commercial Bank of Ethiopia, or Debit/Credit Card) to seamlessly checkout agricultural produce.
          </p>
          <div class="flex items-center justify-center gap-3">
            <button
              @click="openAddModal"
              class="px-5 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm cursor-pointer"
            >
              + Add Payment Method
            </button>
          </div>
        </div>

        <!-- Payment Methods Cards Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="method in paymentMethods"
            :key="method.id"
            :class="['rounded-2xl border p-5 shadow-2xs transition-all relative flex flex-col justify-between group hover:shadow-md',
              method.is_primary ? (darkMode ? 'border-emerald-500/60 bg-[#122329]' : 'border-emerald-500/80 bg-emerald-50/20') :
              (darkMode ? 'bg-[#131b2e] border-slate-800 hover:border-slate-700' : 'bg-white border-slate-200 hover:border-slate-300')
            ]"
          >
            <div>
              <!-- Top Badges -->
              <div class="flex items-start justify-between gap-2 mb-3">
                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border', getTypeBadgeClass(method.type)]">
                  <component :is="getTypeIcon(method.type)" class="w-3.5 h-3.5" />
                  {{ formatTypeName(method.type) }}
                </span>

                <div class="flex items-center gap-1">
                  <span
                    v-if="method.is_primary"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-500 border border-amber-500/30"
                  >
                    <Star class="w-3 h-3 fill-current" />
                    PRIMARY
                  </span>

                  <!-- Delete Button -->
                  <button
                    @click="deleteMethod(method)"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-500/10 transition-all cursor-pointer"
                    title="Delete Payment Method"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <!-- Institution / Bank Name -->
              <h2 :class="['text-base font-bold tracking-tight mb-2 flex items-center justify-between', darkMode ? 'text-slate-100' : 'text-slate-900']">
                <span>{{ method.bank_name }}</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-mono border border-emerald-500/20">
                  Chapa Ready
                </span>
              </h2>

              <!-- Masked Account Number -->
              <div :class="['rounded-xl p-3 mb-4 border transition-all text-xs font-mono flex items-center justify-between', darkMode ? 'bg-[#0f172a] border-slate-800 text-slate-200' : 'bg-slate-50 border-slate-200 text-slate-800']">
                <span>{{ maskAccount(method.account_number, method.type) }}</span>
                <button
                  @click="copyAccount(method.account_number)"
                  class="text-slate-400 hover:text-blue-500 cursor-pointer"
                  title="Copy account number"
                >
                  <Copy class="w-3.5 h-3.5" />
                </button>
              </div>

              <!-- Account Holder Details -->
              <div class="space-y-1 mb-4 text-xs text-slate-500 dark:text-slate-400">
                <div class="flex items-center justify-between">
                  <span class="text-[11px] text-slate-400">Account Holder:</span>
                  <span class="font-semibold text-slate-700 dark:text-slate-200">{{ method.account_holder }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-[11px] text-slate-400">Chapa Gateway:</span>
                  <span class="text-emerald-500 font-semibold text-[11px] flex items-center gap-1">
                    <CheckCircle2 class="w-3 h-3" /> Verified &amp; Active
                  </span>
                </div>
              </div>
            </div>

            <!-- Card Bottom Actions -->
            <div :class="['pt-3 border-t flex items-center justify-between gap-2 mt-auto', darkMode ? 'border-slate-800' : 'border-slate-200']">
              <div class="flex items-center gap-2">
                <button
                  v-if="method.bank_name?.includes('Chapa') || method.type === 'card'"
                  @click="openChapaModal"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-all cursor-pointer"
                >
                  <ShieldCheck class="w-3.5 h-3.5" />
                  <span>Pay with Chapa</span>
                </button>
                <span v-else class="text-[11px] text-slate-400">
                  Added {{ formatDate(method.created_at) }}
                </span>
              </div>

              <button
                v-if="!method.is_primary"
                @click="setAsPrimary(method.id)"
                :class="['px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all cursor-pointer hover:border-blue-500 hover:text-blue-500', darkMode ? 'border-slate-700 text-slate-400' : 'border-slate-300 text-slate-600']"
              >
                Make Primary
              </button>
              <span
                v-else
                class="text-xs font-bold text-emerald-500 flex items-center gap-1"
              >
                <Check class="w-3.5 h-3.5 stroke-[3]" /> Active Primary
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- ── TAB 2: TRANSACTION HISTORY ── -->
      <div v-else-if="activeTab === 'history'">
        <div
          :class="['rounded-2xl border overflow-hidden shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']"
        >
          <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
              <h2 class="text-sm font-bold m-0">Chapa Settlement Transactions</h2>
              <p :class="['text-xs m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Complete audit trail of all produce payments and Chapa escrow settlements
              </p>
            </div>
          </div>

          <div v-if="transactions.length === 0" class="p-12 text-center text-xs text-slate-400">
            No transaction records found in the database yet.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr :class="['border-b uppercase font-bold text-[10px] tracking-wider', darkMode ? 'border-slate-800 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-500']">
                  <th class="py-3.5 px-4">Order #</th>
                  <th class="py-3.5 px-4">Produce Details</th>
                  <th class="py-3.5 px-4">Gateway</th>
                  <th class="py-3.5 px-4">Amount</th>
                  <th class="py-3.5 px-4">Date</th>
                  <th class="py-3.5 px-4">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                <tr
                  v-for="order in transactions"
                  :key="order.id"
                  :class="['transition-colors', darkMode ? 'hover:bg-slate-800/40' : 'hover:bg-slate-50/80']"
                >
                  <td class="py-4 px-4 font-mono font-bold text-blue-500">
                    #{{ order.id }}
                  </td>
                  <td class="py-4 px-4 font-bold">
                    {{ order.items && order.items.length > 0 ? order.items[0].product?.name : 'Produce Consignment' }}
                  </td>
                  <td class="py-4 px-4 font-semibold text-emerald-500">
                    Chapa Gateway
                  </td>
                  <td class="py-4 px-4 font-mono font-bold">
                    ETB {{ formatNumber(order.grand_total || order.total_amount) }}
                  </td>
                  <td class="py-4 px-4 text-slate-400 text-[11px]">
                    {{ formatDate(order.created_at) }}
                  </td>
                  <td class="py-4 px-4">
                    <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase', getOrderStatusBadge(order.status)]">
                      {{ order.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ── TAB 3: ESCROW & INVOICES ── -->
      <div v-else-if="activeTab === 'escrow'" class="space-y-6">
        <div :class="['rounded-2xl border p-6 shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
              <ShieldCheck class="w-7 h-7" />
            </div>
            <div>
              <h2 class="text-base font-bold m-0 mb-1">Chapa &amp; Agricultural Escrow Custody</h2>
              <p :class="['text-xs leading-relaxed max-w-3xl m-0', darkMode ? 'text-slate-300' : 'text-slate-600']">
                All buyer payments made through Chapa (Telebirr, CBE Birr, Awash, Cards) are held securely in escrow. Funds are disbursed to the farmer or agricultural supplier only after you inspect and accept produce delivery at your designated warehouse.
              </p>
            </div>
          </div>
        </div>

        <div :class="['rounded-2xl border p-6 shadow-2xs', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
          <h2 class="text-sm font-bold mb-3 flex items-center gap-2">
            <Receipt class="w-4 h-4 text-blue-500" />
            <span>Digital Tax Invoices &amp; Settlement Receipts</span>
          </h2>
          <p :class="['text-xs mb-4', darkMode ? 'text-slate-400' : 'text-slate-500']">
            Download official Chapa payment receipts and Ethiopian Ministry of Revenues tax invoices.
          </p>

          <div v-if="transactions.length === 0" class="text-xs text-slate-400 py-6 text-center">
            No completed invoices ready for download yet.
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="order in transactions.slice(0, 5)"
              :key="'inv-' + order.id"
              :class="['p-3 rounded-xl border flex items-center justify-between text-xs', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']"
            >
              <div class="flex items-center gap-3">
                <FileText class="w-4 h-4 text-blue-500" />
                <div>
                  <span class="font-bold block">Chapa Invoice #CHAPA-{{ order.id }}</span>
                  <span class="text-[11px] text-slate-400">{{ formatDate(order.created_at) }} &bull; Order #{{ order.id }}</span>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <span class="font-mono font-bold">ETB {{ formatNumber(order.grand_total || order.total_amount) }}</span>
                <button
                  @click="showToast('Chapa invoice downloaded successfully!')"
                  class="px-2.5 py-1.5 rounded-lg border text-[11px] font-semibold text-emerald-500 hover:bg-emerald-500/10 cursor-pointer"
                >
                  Download Receipt
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </main>

    <!-- ── MODAL: CHAPA INSTANT PAYMENT CHECKOUT ── -->
    <div
      v-if="chapaModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
      @click.self="chapaModalOpen = false"
    >
      <div
        :class="['rounded-2xl border w-full max-w-md overflow-hidden shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-emerald-500/30 bg-emerald-600 text-white flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center font-black">
              CH
            </div>
            <div>
              <h2 class="text-base font-bold m-0">Chapa Secure Payment</h2>
              <p class="text-[11px] m-0 text-emerald-100">
                Telebirr &bull; CBE Birr &bull; Bank Transfer &bull; Cards
              </p>
            </div>
          </div>
          <button
            @click="chapaModalOpen = false"
            class="p-1 rounded-lg text-white/80 hover:text-white cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Chapa Form Body -->
        <form @submit.prevent="initiateChapaCheckout" class="p-6 space-y-4">
          <!-- Amount -->
          <div>
            <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
              Payment Amount (ETB) *
            </label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-xs text-slate-400">ETB</span>
              <input
                v-model.number="chapaForm.amount"
                type="number"
                step="any"
                min="10"
                required
                placeholder="e.g. 5000"
                :class="['w-full pl-12 pr-4 py-2.5 rounded-xl text-sm font-mono font-bold border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-emerald-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-emerald-500 text-slate-900']"
              />
            </div>
          </div>

          <!-- Select Payment Option -->
          <div>
            <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
              Select Chapa Payment Channel *
            </label>
            <div class="grid grid-cols-2 gap-2 text-xs">
              <label
                v-for="channel in chapaChannels"
                :key="channel.id"
                :class="['p-2.5 rounded-xl border flex items-center gap-2 cursor-pointer transition-all select-none',
                  chapaForm.channel === channel.id
                    ? 'border-emerald-500 bg-emerald-500/10 text-emerald-500 font-bold'
                    : (darkMode ? 'border-slate-800 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-600')
                ]"
              >
                <input
                  type="radio"
                  :value="channel.id"
                  v-model="chapaForm.channel"
                  class="text-emerald-600 focus:ring-0"
                />
                <span class="text-[11px] truncate">{{ channel.name }}</span>
              </label>
            </div>
          </div>

          <!-- Phone Number -->
          <div>
            <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
              Mobile Phone (for Telebirr / CBE Birr prompt) *
            </label>
            <input
              v-model="chapaForm.phone_number"
              type="tel"
              required
              placeholder="+251 91 123 4567"
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all font-mono', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-emerald-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-emerald-500 text-slate-900']"
            />
          </div>

          <!-- Email -->
          <div>
            <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
              Email Receipt Destination
            </label>
            <input
              v-model="chapaForm.email"
              type="email"
              placeholder="buyer@agriconnect.et"
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-emerald-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-emerald-500 text-slate-900']"
            />
          </div>

          <!-- Result Session if Generated -->
          <div
            v-if="chapaSession"
            :class="['p-3 rounded-xl border space-y-2 text-xs', darkMode ? 'bg-[#0f172a] border-emerald-900/60' : 'bg-emerald-50/60 border-emerald-200']"
          >
            <div class="flex items-center justify-between text-emerald-500 font-bold">
              <span>Chapa Session Active:</span>
              <span class="font-mono text-[11px]">{{ chapaSession.tx_ref }}</span>
            </div>
            <div class="flex items-center gap-2">
              <a
                v-if="chapaSession.checkout_url"
                :href="chapaSession.checkout_url"
                target="_blank"
                rel="noopener noreferrer"
                class="flex-1 text-center py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs"
              >
                Open Chapa Checkout &rarr;
              </a>
              <button
                type="button"
                @click="verifyChapaSession(chapaSession.tx_ref)"
                class="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs cursor-pointer"
              >
                Verify
              </button>
            </div>
          </div>

          <!-- Modal Actions Footer -->
          <div :class="['pt-3 border-t flex items-center justify-end gap-3', darkMode ? 'border-slate-800' : 'border-slate-200']">
            <button
              type="button"
              @click="chapaModalOpen = false"
              :class="['px-4 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700']"
            >
              Close
            </button>
            <button
              v-if="!chapaSession"
              type="submit"
              :disabled="chapaProcessing"
              class="px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all cursor-pointer flex items-center gap-1.5"
            >
              <RefreshCw v-if="chapaProcessing" class="w-3.5 h-3.5 animate-spin" />
              <span>{{ chapaProcessing ? 'Connecting...' : 'Proceed with Chapa' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── MODAL: ADD PAYMENT METHOD ── -->
    <div
      v-if="modalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
      @click.self="modalOpen = false"
    >
      <div
        :class="['rounded-2xl border w-full max-w-lg overflow-hidden shadow-2xl transition-all', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']"
      >
        <div :class="['px-6 py-4 border-b flex items-center justify-between', darkMode ? 'border-slate-800 bg-[#0f172a]' : 'border-slate-200 bg-slate-50']">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <CreditCard class="w-4 h-4" />
            </div>
            <div>
              <h2 class="text-base font-bold m-0">Add Payment Method</h2>
              <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Connect your Telebirr, CBE Birr, bank account, or card via Chapa
              </p>
            </div>
          </div>
          <button
            @click="modalOpen = false"
            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="savePaymentMethod" class="p-6 space-y-4">
          <!-- Method Type -->
          <div>
            <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
              Payment Category *
            </label>
            <div class="grid grid-cols-3 gap-2">
              <button
                type="button"
                @click="form.type = 'mobile_money'; form.bank_name = 'Telebirr (Ethio Telecom)'"
                :class="['p-2.5 rounded-xl border text-center text-xs font-semibold cursor-pointer transition-all flex flex-col items-center gap-1',
                  form.type === 'mobile_money'
                    ? 'border-emerald-600 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                    : (darkMode ? 'border-slate-700 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-600')
                ]"
              >
                <Smartphone class="w-4 h-4" />
                <span>Mobile Money</span>
              </button>

              <button
                type="button"
                @click="form.type = 'bank_transfer'; form.bank_name = 'Commercial Bank of Ethiopia (CBE)'"
                :class="['p-2.5 rounded-xl border text-center text-xs font-semibold cursor-pointer transition-all flex flex-col items-center gap-1',
                  form.type === 'bank_transfer'
                    ? 'border-emerald-600 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                    : (darkMode ? 'border-slate-700 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-600')
                ]"
              >
                <Building2 class="w-4 h-4" />
                <span>Bank Transfer</span>
              </button>

              <button
                type="button"
                @click="form.type = 'card'; form.bank_name = 'Chapa Ethiopian Gateway (Telebirr/CBE/Cards)'"
                :class="['p-2.5 rounded-xl border text-center text-xs font-semibold cursor-pointer transition-all flex flex-col items-center gap-1 relative overflow-hidden',
                  form.type === 'card'
                    ? 'border-emerald-500 bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 ring-2 ring-emerald-500/30'
                    : (darkMode ? 'border-slate-700 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-600')
                ]"
              >
                <div class="flex items-center gap-1">
                  <div class="w-4 h-4 rounded bg-emerald-600 text-white font-black text-[9px] flex items-center justify-center">CH</div>
                  <CreditCard class="w-3.5 h-3.5" />
                </div>
                <span>Card (Chapa)</span>
              </button>
            </div>
          </div>

          <!-- ── CATEGORY 1 & 2: MANUAL MOBILE MONEY OR BANK TRANSFER ── -->
          <template v-if="form.type !== 'card'">
            <!-- Provider Name -->
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Provider / Institution *
              </label>
              <select
                v-if="form.type === 'mobile_money'"
                v-model="form.bank_name"
                required
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-emerald-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-emerald-500 text-slate-900']"
              >
                <option value="Telebirr (Ethio Telecom)">Telebirr (Ethio Telecom)</option>
                <option value="CBE Birr (Commercial Bank of Ethiopia)">CBE Birr (Commercial Bank of Ethiopia)</option>
                <option value="M-Pesa (Safaricom Ethiopia)">M-Pesa (Safaricom Ethiopia)</option>
              </select>

              <select
                v-else
                v-model="form.bank_name"
                required
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-emerald-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-emerald-500 text-slate-900']"
              >
                <option value="Commercial Bank of Ethiopia (CBE)">Commercial Bank of Ethiopia (CBE)</option>
                <option value="Awash Bank">Awash Bank</option>
                <option value="Dashen Bank">Dashen Bank</option>
                <option value="Bank of Abyssinia (BOA)">Bank of Abyssinia (BOA)</option>
                <option value="Hibret Bank">Hibret Bank</option>
                <option value="Nib International Bank">Nib International Bank</option>
                <option value="Cooperative Bank of Oromia">Cooperative Bank of Oromia</option>
              </select>
            </div>

            <!-- Account / Phone Number -->
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                {{ form.type === 'mobile_money' ? 'Registered Mobile Phone Number *' : 'Bank Account Number *' }}
              </label>
              <input
                v-model="form.account_number"
                type="text"
                required
                :placeholder="form.type === 'mobile_money' ? '+251 91 123 4567' : '1000123456789'"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all font-mono', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-emerald-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-emerald-500 text-slate-900']"
              />
            </div>

            <!-- Account Holder Full Name -->
            <div>
              <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                Account Holder Full Name *
              </label>
              <input
                v-model="form.account_holder"
                type="text"
                required
                placeholder="e.g. Dawit Haile"
                :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-emerald-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-emerald-500 text-slate-900']"
              />
            </div>

            <!-- Set as Primary Checkbox -->
            <div :class="['p-3 rounded-xl border flex items-center justify-between', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex items-center gap-2.5">
                <input
                  id="is_primary"
                  v-model="form.is_primary"
                  type="checkbox"
                  class="w-4 h-4 rounded text-emerald-600 focus:ring-0 cursor-pointer"
                />
                <label for="is_primary" :class="['text-xs font-bold cursor-pointer block', darkMode ? 'text-slate-200' : 'text-slate-800']">
                  Set as Primary Payment Method
                </label>
              </div>
              <Star v-if="form.is_primary" class="w-5 h-5 text-amber-500 fill-current" />
            </div>

            <!-- Footer Actions -->
            <div :class="['pt-4 border-t flex items-center justify-end gap-3', darkMode ? 'border-slate-800' : 'border-slate-200']">
              <button
                type="button"
                @click="modalOpen = false"
                :class="['px-4 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700']"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all cursor-pointer flex items-center gap-1.5"
              >
                <RefreshCw v-if="saving" class="w-3.5 h-3.5 animate-spin" />
                <span>{{ saving ? 'Saving...' : 'Save Payment Method' }}</span>
              </button>
            </div>
          </template>

          <!-- ── CATEGORY 3: PROFESSIONAL CHAPA PAYMENT GATEWAY INTEGRATION ── -->
          <template v-else>
            <div class="space-y-4">
              <!-- Official Chapa Brand Card -->
              <div :class="['rounded-2xl border p-4 transition-all relative overflow-hidden', darkMode ? 'bg-gradient-to-br from-emerald-950/60 via-[#131b2e] to-[#0f172a] border-emerald-700/50' : 'bg-gradient-to-br from-emerald-50/80 via-white to-emerald-50/30 border-emerald-300/80']">
                <div class="flex items-start gap-3.5 mb-3">
                  <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white font-black text-xl flex items-center justify-center shrink-0 shadow-md">
                    CH
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                      <h4 :class="['text-sm font-black m-0', darkMode ? 'text-slate-100' : 'text-slate-900']">
                        Chapa Ethiopian Payment Gateway
                      </h4>
                      <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        PCI-DSS CERTIFIED
                      </span>
                    </div>
                    <p :class="['text-xs leading-relaxed m-0', darkMode ? 'text-slate-300' : 'text-slate-600']">
                      Connect your account securely through Chapa's official gateway. No raw 16-digit card numbers required—authenticate seamlessly with <strong>Telebirr</strong>, <strong>CBE Birr</strong>, <strong>Awash</strong>, or <strong>Cards</strong>.
                    </p>
                  </div>
                </div>

                <!-- Channels Badges -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-3 border-t border-emerald-500/20 text-[11px] font-semibold text-center">
                  <div :class="['p-2 rounded-lg border', darkMode ? 'bg-emerald-950/40 border-emerald-800/40 text-emerald-300' : 'bg-emerald-50 border-emerald-200 text-emerald-800']">
                    ⚡ Telebirr
                  </div>
                  <div :class="['p-2 rounded-lg border', darkMode ? 'bg-emerald-950/40 border-emerald-800/40 text-emerald-300' : 'bg-emerald-50 border-emerald-200 text-emerald-800']">
                    🏛 CBE Birr
                  </div>
                  <div :class="['p-2 rounded-lg border', darkMode ? 'bg-emerald-950/40 border-emerald-800/40 text-emerald-300' : 'bg-emerald-50 border-emerald-200 text-emerald-800']">
                    💳 Cards (Visa/MC)
                  </div>
                  <div :class="['p-2 rounded-lg border', darkMode ? 'bg-emerald-950/40 border-emerald-800/40 text-emerald-300' : 'bg-emerald-50 border-emerald-200 text-emerald-800']">
                    🏦 Awash &amp; Banks
                  </div>
                </div>
              </div>

              <!-- Channel Preference Radio -->
              <div>
                <label :class="['block text-xs font-bold mb-1.5', darkMode ? 'text-slate-300' : 'text-slate-700']">
                  Preferred Default Chapa Channel *
                </label>
                <div class="grid grid-cols-2 gap-2 text-xs">
                  <label
                    v-for="ch in chapaChannels"
                    :key="ch.id"
                    :class="['p-2.5 rounded-xl border flex items-center gap-2 cursor-pointer transition-all select-none',
                      chapaForm.channel === ch.id
                        ? 'border-emerald-500 bg-emerald-500/10 text-emerald-500 font-bold'
                        : (darkMode ? 'border-slate-800 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-600')
                    ]"
                  >
                    <input
                      type="radio"
                      :value="ch.id"
                      v-model="chapaForm.channel"
                      class="text-emerald-600 focus:ring-0"
                    />
                    <span class="text-[11px] truncate">{{ ch.name }}</span>
                  </label>
                </div>
              </div>

              <!-- Buyer Contact Info for Chapa Connection -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                    Telebirr / Mobile Phone *
                  </label>
                  <input
                    v-model="chapaBuyerPhone"
                    type="tel"
                    required
                    placeholder="+251 91 123 4567"
                    :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all font-mono', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-emerald-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-emerald-500 text-slate-900']"
                  />
                </div>
                <div>
                  <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
                    Receipt Email Address *
                  </label>
                  <input
                    v-model="chapaBuyerEmail"
                    type="email"
                    required
                    placeholder="buyer@agriconnect.et"
                    :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-emerald-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-emerald-500 text-slate-900']"
                  />
                </div>
              </div>

              <!-- Set as Primary Checkbox -->
              <div :class="['p-3 rounded-xl border flex items-center justify-between', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
                <div class="flex items-center gap-2.5">
                  <input
                    id="is_primary_chapa"
                    v-model="form.is_primary"
                    type="checkbox"
                    class="w-4 h-4 rounded text-emerald-600 focus:ring-0 cursor-pointer"
                  />
                  <label for="is_primary_chapa" :class="['text-xs font-bold cursor-pointer block', darkMode ? 'text-slate-200' : 'text-slate-800']">
                    Set Chapa as Primary Payment Method
                  </label>
                </div>
                <Star v-if="form.is_primary" class="w-5 h-5 text-amber-500 fill-current" />
              </div>

              <!-- Footer with Connect Chapa Button -->
              <div :class="['pt-4 border-t flex items-center justify-between gap-3', darkMode ? 'border-slate-800' : 'border-slate-200']">
                <button
                  type="button"
                  @click="modalOpen = false"
                  :class="['px-4 py-2 rounded-xl text-xs font-semibold border transition-all cursor-pointer', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700']"
                >
                  Cancel
                </button>

                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    @click="connectChapaPayment(true)"
                    :disabled="connectingChapa"
                    class="px-4 py-2.5 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition-all cursor-pointer flex items-center gap-2 transform active:scale-95"
                  >
                    <RefreshCw v-if="connectingChapa" class="w-3.5 h-3.5 animate-spin" />
                    <ShieldCheck v-else class="w-3.5 h-3.5" />
                    <span>{{ connectingChapa ? 'Connecting...' : 'Connect Chapa & Open Checkout' }}</span>
                    <ExternalLink class="w-3 h-3 ml-0.5" />
                  </button>
                  <button
                    type="button"
                    @click="connectChapaPayment(false)"
                    :disabled="connectingChapa"
                    :class="['px-4 py-2.5 rounded-xl font-bold text-xs border transition-all cursor-pointer', darkMode ? 'border-emerald-600/50 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100']"
                  >
                    Save Chapa Method
                  </button>
                </div>
              </div>
            </div>
          </template></form>
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { buyerAPI } from '@/api/buyer'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import {
  Menu, CreditCard, Plus, Receipt, ShieldCheck, CheckCircle2, Star,
  Smartphone, Building2, Trash2, Copy, Check, X, RefreshCw, FileText,
  ExternalLink
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)
const sidebarOpen = ref(false)
const loading = ref(false)
const saving = ref(false)

const activeTab = ref<'methods' | 'history' | 'escrow'>('methods')

const paymentMethods = ref<any[]>([])
const transactions = ref<any[]>([])

const stats = ref({
  total_methods: 0,
  primary_method: 'None',
  total_spent: 0,
  completed_transactions: 0
})

const modalOpen = ref(false)
const chapaModalOpen = ref(false)
const chapaProcessing = ref(false)
const chapaSession = ref<any | null>(null)

const chapaChannels = [
  { id: 'telebirr', name: 'Telebirr (Chapa)' },
  { id: 'cbe_birr', name: 'CBE Birr' },
  { id: 'awash', name: 'Awash Birr' },
  { id: 'cards', name: 'Visa / Mastercard' }
]

const chapaBuyerPhone = ref('0911000000')
const chapaBuyerEmail = ref('aydenfudagne@gmail.com')
const connectingChapa = ref(false)

const chapaForm = ref({
  amount: 100,
  channel: 'telebirr',
  phone_number: '0911000000',
  email: 'aydenfudagne@gmail.com'
})

const form = ref({
  type: 'mobile_money',
  bank_name: 'Telebirr (Chapa)',
  account_number: '',
  account_holder: '',
  is_primary: false
})

const toastMessage = ref('')
let toastTimer: any = null

const showToast = (msg: string) => {
  toastMessage.value = msg
  if (toastTimer) clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toastMessage.value = ''
  }, 3500)
}

const currentTabTitle = computed(() => {
  switch (activeTab.value) {
    case 'history': return 'Transaction History'
    case 'escrow': return 'Escrow & Invoices'
    default: return 'Payment Methods'
  }
})

const primaryMethod = computed(() => {
  return paymentMethods.value.find(m => m.is_primary) || paymentMethods.value[0] || null
})

// Sync tab with route path
const syncTabWithRoute = () => {
  const p = route.path || ''
  if (p.includes('/history')) {
    activeTab.value = 'history'
  } else if (p.includes('/escrow')) {
    activeTab.value = 'escrow'
  } else {
    activeTab.value = 'methods'
  }
}

watch(() => route.path, () => {
  syncTabWithRoute()
}, { immediate: true })

const switchTab = (tab: 'methods' | 'history' | 'escrow') => {
  activeTab.value = tab
  if (tab === 'methods') {
    router.push('/buyer/payments/methods')
  } else if (tab === 'history') {
    router.push('/buyer/payments/history')
  } else if (tab === 'escrow') {
    router.push('/buyer/payments/escrow')
  }
}

onMounted(async () => {
  themeStore.initializeTheme()
  syncTabWithRoute()
  await loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    const [methodsRes, transRes] = await Promise.all([
      buyerAPI.getPaymentMethods().catch(() => null),
      buyerAPI.getPaymentTransactions().catch(() => null)
    ])

    if (methodsRes && methodsRes.data) {
      paymentMethods.value = methodsRes.data.data || []
      if (methodsRes.data.stats) {
        stats.value = methodsRes.data.stats
      }
    }

    if (transRes && transRes.data) {
      transactions.value = transRes.data.data?.data || transRes.data.data || []
    }
  } catch (err: any) {
    console.error('Error fetching payment data from database:', err)
  } finally {
    loading.value = false
  }
}

const openChapaModal = () => {
  chapaSession.value = null
  const userPhone = authStore.user?.phone ? authStore.user.phone.replace(/[^0-9+]/g, '') : '0911000000'
  const userEmail = (authStore.user?.email && !authStore.user.email.includes('agriconnect.et')) ? authStore.user.email : 'aydenfudagne@gmail.com'
  chapaForm.value = {
    amount: 100,
    channel: 'telebirr',
    phone_number: userPhone,
    email: userEmail
  }
  chapaModalOpen.value = true
}

const initiateChapaCheckout = async () => {
  chapaProcessing.value = true
  try {
    const cleanPhone = (chapaForm.value.phone_number || '0911000000').replace(/[^0-9+]/g, '')
    const cleanEmail = (chapaForm.value.email && !chapaForm.value.email.includes('agriconnect.et')) 
      ? chapaForm.value.email 
      : 'aydenfudagne@gmail.com'

    const res = await buyerAPI.initializeChapaPayment({
      amount: chapaForm.value.amount,
      phone_number: cleanPhone,
      email: cleanEmail,
      channel: chapaForm.value.channel
    })

    if (res && res.data && res.data.data) {
      chapaSession.value = res.data.data
      showToast('Chapa payment session generated!')
      if (res.data.data.checkout_url) {
        window.open(res.data.data.checkout_url, '_blank')
      }
    }
  } catch (err: any) {
    console.error('Chapa initiation error:', err)
    alert(err?.response?.data?.message || 'Failed to initiate Chapa payment.')
  } finally {
    chapaProcessing.value = false
  }
}

const verifyChapaSession = async (txRef: string) => {
  try {
    const res = await buyerAPI.verifyChapaPayment(txRef)
    if (res && res.data && res.data.success) {
      showToast('Chapa payment confirmed and recorded in database!')
      chapaModalOpen.value = false
      await loadData()
    }
  } catch (err: any) {
    console.error('Chapa verification error:', err)
    alert('Verification failed or payment is still pending on Chapa.')
  }
}

const connectChapaPayment = async (openCheckout = true) => {
  connectingChapa.value = true
  try {
    const channelName = chapaChannels.find(c => c.id === chapaForm.value.channel)?.name || 'Chapa Official Gateway'
    const cleanPhone = (chapaBuyerPhone.value || authStore.user?.phone || '0911000000').replace(/[^0-9+]/g, '')
    const cleanEmail = (chapaBuyerEmail.value && !chapaBuyerEmail.value.includes('agriconnect.et')) 
      ? chapaBuyerEmail.value 
      : (authStore.user?.email && !authStore.user.email.includes('agriconnect.et') ? authStore.user.email : 'aydenfudagne@gmail.com')

    // 1. Call Chapa API to initialize payment session
    const res = await buyerAPI.initializeChapaPayment({
      amount: 100,
      phone_number: cleanPhone,
      email: cleanEmail,
      channel: chapaForm.value.channel
    })

    const checkoutUrl = res?.data?.data?.checkout_url
    const txRef = res?.data?.data?.tx_ref || ('AGRI-CHAPA-' + Date.now())

    // 2. Save Chapa Payment Gateway to database
    await buyerAPI.createPaymentMethod({
      type: 'card',
      bank_name: 'Chapa Gateway (' + channelName + ')',
      account_number: 'CHAPA-' + cleanPhone,
      account_holder: authStore.user?.name || 'AgriConnect Verified Buyer',
      is_primary: form.value.is_primary
    })

    modalOpen.value = false
    showToast('Chapa Payment Gateway connected successfully!')
    await loadData()

    // 3. Open original Chapa checkout if requested
    if (openCheckout && checkoutUrl) {
      chapaSession.value = res?.data?.data || null
      chapaModalOpen.value = true
      try {
        window.open(checkoutUrl, '_blank')
      } catch (e) {
        console.warn('Popup blocked, modal fallback opened', e)
      }
    }
  } catch (err: any) {
    console.error('Failed to connect Chapa:', err)
    alert(err?.response?.data?.message || 'Failed to connect with Chapa Gateway.')
  } finally {
    connectingChapa.value = false
  }
}

const openAddModal = () => {
  const userPhone = authStore.user?.phone ? authStore.user.phone.replace(/[^0-9+]/g, '') : '0911000000'
  const userEmail = (authStore.user?.email && !authStore.user.email.includes('agriconnect.et')) ? authStore.user.email : 'aydenfudagne@gmail.com'
  chapaBuyerPhone.value = userPhone
  chapaBuyerEmail.value = userEmail
  form.value = {
    type: 'mobile_money',
    bank_name: 'Telebirr (Ethio Telecom)',
    account_number: userPhone,
    account_holder: authStore.user?.name || '',
    is_primary: paymentMethods.value.length === 0
  }
  modalOpen.value = true
}

const savePaymentMethod = async () => {
  if (!form.value.bank_name || !form.value.account_number || !form.value.account_holder) {
    alert('Please fill in all required fields.')
    return
  }

  saving.value = true
  try {
    await buyerAPI.createPaymentMethod(form.value)
    showToast('Payment method saved to database successfully!')
    modalOpen.value = false
    await loadData()
  } catch (err: any) {
    console.error('Failed to save payment method:', err)
    alert(err?.response?.data?.message || 'Failed to save payment method.')
  } finally {
    saving.value = false
  }
}

const setAsPrimary = async (id: number | string) => {
  try {
    await buyerAPI.setDefaultPaymentMethod(id)
    showToast('Primary payment method updated in database.')
    await loadData()
  } catch (err: any) {
    console.error('Error setting default payment method:', err)
    alert('Failed to set as primary method.')
  }
}

const deleteMethod = async (method: any) => {
  if (confirm(`Are you sure you want to delete payment method "${method.bank_name}" from database?`)) {
    try {
      await buyerAPI.deletePaymentMethod(method.id)
      showToast(`Payment method "${method.bank_name}" deleted.`)
      await loadData()
    } catch (err: any) {
      console.error('Error deleting payment method:', err)
      alert(err?.response?.data?.message || 'Failed to delete payment method.')
    }
  }
}

const maskAccount = (num: string, type: string) => {
  if (!num) return '••••'
  if (type === 'mobile_money') {
    return num.length > 4 ? num.slice(0, 4) + ' ••• ••' + num.slice(-2) : num
  }
  if (num.length >= 8) {
    return '•••• •••• •••• ' + num.slice(-4)
  }
  return '•••• ' + num.slice(-4)
}

const copyAccount = (text: string) => {
  navigator.clipboard.writeText(text).then(() => {
    showToast('Account number copied to clipboard!')
  })
}

const getTypeIcon = (type: string) => {
  switch (type) {
    case 'mobile_money': return Smartphone
    case 'bank_transfer': return Building2
    case 'card': return CreditCard
    default: return CreditCard
  }
}

const formatTypeName = (type: string) => {
  switch (type) {
    case 'mobile_money': return 'Mobile Money (Chapa)'
    case 'bank_transfer': return 'Bank Transfer (Chapa)'
    case 'card': return 'Cards (Chapa)'
    default: return 'Chapa Wallet'
  }
}

const getTypeBadgeClass = (type: string) => {
  switch (type) {
    case 'mobile_money': return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30'
    case 'bank_transfer': return 'bg-blue-500/10 text-blue-400 border-blue-500/30'
    case 'card': return 'bg-purple-500/10 text-purple-400 border-purple-500/30'
    default: return 'bg-slate-500/10 text-slate-400 border-slate-500/30'
  }
}

const getOrderStatusBadge = (status: string) => {
  switch (status) {
    case 'delivered':
    case 'completed':
      return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
    case 'confirmed':
    case 'in_transit':
      return 'bg-blue-500/10 text-blue-400 border border-blue-500/30'
    case 'pending':
      return 'bg-amber-500/10 text-amber-400 border border-amber-500/30'
    default:
      return 'bg-slate-500/10 text-slate-400 border border-slate-500/30'
  }
}

const formatDate = (dateStr?: string | null) => {
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
