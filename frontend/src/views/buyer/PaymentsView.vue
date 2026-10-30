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
                  <CreditCard class="w-3.5 h-3.5" />
                  Payments &amp; Digital Finance
                </span>
                <span :class="['text-xs font-medium', darkMode ? 'text-slate-400' : 'text-slate-500']">
                  Buyer Portal &gt; Payments &amp; Finance &gt; {{ currentTabTitle }}
                </span>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                {{ currentTabTitle }}
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Manage your digital wallets, verified bank accounts, commercial escrow methods, and transaction receipts
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
            <span class="text-[10px] text-purple-400 font-medium">All completed transactions</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-purple-500/10 text-purple-400' : 'bg-purple-50 text-purple-600']">
            <Receipt class="w-6 h-6" />
          </div>
        </article>

        <!-- Escrow Security Guarantee -->
        <article :class="['rounded-xl p-4 border-l-4 border-y border-r shadow-2xs flex justify-between items-center transition-all', darkMode ? 'bg-[#131b2e] border-slate-800 text-slate-100 !border-l-blue-500' : 'bg-white border-slate-200 text-slate-900 !border-l-blue-600']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', darkMode ? 'text-slate-400' : 'text-slate-500']">Buyer Escrow</h3>
            <p class="text-2xl font-black text-blue-500">100% Protected</p>
            <span class="text-[10px] text-blue-500 font-medium">Safe produce custody</span>
          </div>
          <div :class="['text-xl p-2.5 rounded-xl', darkMode ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
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
            Add your preferred payment account (Telebirr, CBE Birr, Commercial Bank of Ethiopia, or Debit/Credit Card) to seamlessly checkout agricultural produce and fund contract escrows.
          </p>
          <button
            @click="openAddModal"
            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm cursor-pointer"
          >
            + Add Payment Method
          </button>
        </div>

        <!-- Payment Methods Cards Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="method in paymentMethods"
            :key="method.id"
            :class="['rounded-2xl border p-5 shadow-2xs transition-all relative flex flex-col justify-between group hover:shadow-md',
              method.is_primary ? (darkMode ? 'border-blue-500/60 bg-[#14203a]' : 'border-blue-500/80 bg-blue-50/20') :
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
              <h2 :class="['text-base font-bold tracking-tight mb-2 flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                {{ method.bank_name }}
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
                  <span class="text-[11px] text-slate-400">Database Status:</span>
                  <span class="text-emerald-500 font-semibold text-[11px] flex items-center gap-1">
                    <CheckCircle2 class="w-3 h-3" /> Verified &amp; Active
                  </span>
                </div>
              </div>
            </div>

            <!-- Card Bottom Actions -->
            <div :class="['pt-3 border-t flex items-center justify-between gap-2 mt-auto', darkMode ? 'border-slate-800' : 'border-slate-200']">
              <span class="text-[11px] text-slate-400">
                Added {{ formatDate(method.created_at) }}
              </span>

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
              <h2 class="text-sm font-bold m-0">Settlement Transactions</h2>
              <p :class="['text-xs m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Complete audit trail of all produce payments and escrow settlements
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
                  <th class="py-3.5 px-4">Origin Farm</th>
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
                  <td class="py-4 px-4 text-slate-500 dark:text-slate-400">
                    {{ order.farm?.name || 'Local Farm' }}
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
              <h2 class="text-base font-bold m-0 mb-1">Commercial Agricultural Escrow Guarantee</h2>
              <p :class="['text-xs leading-relaxed max-w-3xl m-0', darkMode ? 'text-slate-300' : 'text-slate-600']">
                All buyer payments made through AgriConnect are held in regulated third-party escrow. Funds are only disbursed to farmers or suppliers after you confirm delivery arrival, quantity verification, and produce quality acceptance at your registered delivery address.
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
            Download stamped commercial invoices compliant with Ethiopian Ministry of Revenues regulations.
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
                  <span class="font-bold block">Invoice #INV-2026-{{ order.id }}</span>
                  <span class="text-[11px] text-slate-400">{{ formatDate(order.created_at) }} &bull; Order #{{ order.id }}</span>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <span class="font-mono font-bold">ETB {{ formatNumber(order.grand_total || order.total_amount) }}</span>
                <button
                  @click="showToast('Invoice downloaded successfully!')"
                  class="px-2.5 py-1.5 rounded-lg border text-[11px] font-semibold text-blue-500 hover:bg-blue-500/10 cursor-pointer"
                >
                  Download PDF
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </main>

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
            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <CreditCard class="w-4 h-4" />
            </div>
            <div>
              <h2 class="text-base font-bold m-0">Add Payment Method</h2>
              <p :class="['text-[11px] m-0', darkMode ? 'text-slate-400' : 'text-slate-500']">
                Connect your Telebirr, CBE Birr, bank account, or card
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
                    ? 'border-blue-600 bg-blue-500/10 text-blue-600 dark:text-blue-400'
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
                    ? 'border-blue-600 bg-blue-500/10 text-blue-600 dark:text-blue-400'
                    : (darkMode ? 'border-slate-700 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-600')
                ]"
              >
                <Building2 class="w-4 h-4" />
                <span>Bank Account</span>
              </button>

              <button
                type="button"
                @click="form.type = 'card'; form.bank_name = 'Visa / Mastercard'"
                :class="['p-2.5 rounded-xl border text-center text-xs font-semibold cursor-pointer transition-all flex flex-col items-center gap-1',
                  form.type === 'card'
                    ? 'border-blue-600 bg-blue-500/10 text-blue-600 dark:text-blue-400'
                    : (darkMode ? 'border-slate-700 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-600')
                ]"
              >
                <CreditCard class="w-4 h-4" />
                <span>Debit / Credit</span>
              </button>
            </div>
          </div>

          <!-- Institution / Provider Name -->
          <div>
            <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
              Provider / Institution *
            </label>
            <select
              v-if="form.type === 'mobile_money'"
              v-model="form.bank_name"
              required
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
            >
              <option value="Telebirr (Ethio Telecom)">Telebirr (Ethio Telecom)</option>
              <option value="CBE Birr">CBE Birr</option>
              <option value="M-Pesa (Safaricom Ethiopia)">M-Pesa (Safaricom Ethiopia)</option>
            </select>

            <select
              v-else-if="form.type === 'bank_transfer'"
              v-model="form.bank_name"
              required
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all cursor-pointer', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
            >
              <option value="Commercial Bank of Ethiopia (CBE)">Commercial Bank of Ethiopia (CBE)</option>
              <option value="Awash Bank">Awash Bank</option>
              <option value="Dashen Bank">Dashen Bank</option>
              <option value="Bank of Abyssinia (BOA)">Bank of Abyssinia (BOA)</option>
              <option value="Hibret Bank">Hibret Bank</option>
              <option value="Nib International Bank">Nib International Bank</option>
              <option value="Cooperative Bank of Oromia (Coop)">Cooperative Bank of Oromia (Coop)</option>
            </select>

            <input
              v-else
              v-model="form.bank_name"
              type="text"
              required
              placeholder="e.g. Visa Debit / Mastercard"
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
            />
          </div>

          <!-- Account / Phone Number -->
          <div>
            <label :class="['block text-xs font-bold mb-1', darkMode ? 'text-slate-300' : 'text-slate-700']">
              {{ form.type === 'mobile_money' ? 'Registered Mobile Phone *' : (form.type === 'card' ? '16-Digit Card Number *' : 'Bank Account Number *') }}
            </label>
            <input
              v-model="form.account_number"
              type="text"
              required
              :placeholder="form.type === 'mobile_money' ? '+251 91 123 4567' : (form.type === 'card' ? '4111 2222 3333 4444' : '1000123456789')"
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all font-mono', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
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
              :class="['w-full px-3 py-2 rounded-xl text-xs border outline-none transition-all', darkMode ? 'bg-[#0f172a] border-slate-700 focus:border-blue-500 text-slate-100' : 'bg-slate-50 border-slate-300 focus:border-blue-500 text-slate-900']"
            />
          </div>

          <!-- Set as Primary Checkbox -->
          <div :class="['p-3 rounded-xl border flex items-center justify-between', darkMode ? 'bg-[#0f172a] border-slate-800' : 'bg-slate-50 border-slate-200']">
            <div class="flex items-center gap-2.5">
              <input
                id="is_primary"
                v-model="form.is_primary"
                type="checkbox"
                class="w-4 h-4 rounded text-blue-600 focus:ring-0 cursor-pointer"
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
              class="px-5 py-2.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all cursor-pointer flex items-center gap-1.5"
            >
              <RefreshCw v-if="saving" class="w-3.5 h-3.5 animate-spin" />
              <span>{{ saving ? 'Saving...' : 'Save Payment Method' }}</span>
            </button>
          </div>
        </form>
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
  Smartphone, Building2, Trash2, Copy, Check, X, RefreshCw, FileText
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

const form = ref({
  type: 'mobile_money',
  bank_name: 'Telebirr (Ethio Telecom)',
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

const openAddModal = () => {
  form.value = {
    type: 'mobile_money',
    bank_name: 'Telebirr (Ethio Telecom)',
    account_number: authStore.user?.phone || '',
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
    case 'mobile_money': return 'Mobile Money'
    case 'bank_transfer': return 'Bank Account'
    case 'card': return 'Debit / Credit'
    default: return 'Digital Wallet'
  }
}

const getTypeBadgeClass = (type: string) => {
  switch (type) {
    case 'mobile_money': return 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30'
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
