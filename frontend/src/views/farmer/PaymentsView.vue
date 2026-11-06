<template>
  <div :class="['flex min-h-screen font-sans antialiased transition-colors duration-300', isDark ? 'bg-[#0b0f19] text-slate-100' : 'bg-slate-50 text-slate-800']">
    <!-- Sidebar -->
    <FarmerSidebar @logout="handleLogout" />

    <!-- Main Content Area -->
    <main class="flex-1 p-4 lg:p-8 overflow-y-auto min-h-screen transition-all" role="main">
      <!-- Header -->
      <div :class="['rounded-2xl border p-6 mb-6 shadow-xs transition-all', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                PRODUCER SETTLEMENT
              </span>
              <span class="text-xs text-slate-400 font-medium">Chapa Escrow Standard</span>
            </div>
            <h1 :class="['text-2xl lg:text-3xl font-black tracking-tight m-0', isDark ? 'text-slate-100' : 'text-slate-900']">
              Payout Settings &amp; Escrow Settlements
            </h1>
            <p :class="['text-xs lg:text-sm m-0 mt-1', isDark ? 'text-slate-400' : 'text-slate-600']">
              Register receiving bank accounts and mobile wallets to receive automated Chapa escrow disbursements upon warehouse delivery inspection.
            </p>
          </div>

          <button
            @click="openAddPayoutModal"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white shadow-md shadow-emerald-600/20 transition-all transform active:scale-95 cursor-pointer shrink-0"
          >
            <Plus class="w-4 h-4" />
            <span>Register Payout Account</span>
          </button>
        </div>
      </div>

      <!-- Escrow Custody Guarantee Notice -->
      <div :class="['mb-6 p-4 rounded-2xl border flex items-start gap-3.5 transition-all', isDark ? 'bg-emerald-950/20 border-emerald-900/50 text-emerald-200' : 'bg-emerald-50/70 border-emerald-200 text-emerald-900']">
        <ShieldCheck class="w-6 h-6 text-emerald-500 shrink-0 mt-0.5" />
        <div class="text-xs leading-relaxed">
          <div class="font-bold text-sm text-emerald-600 dark:text-emerald-400 mb-0.5">
            100% Secured Ethiopian Agricultural Produce Escrow
          </div>
          <div>
            Buyer payments for your crops and produce are held safely in neutral <span class="font-bold">Chapa &amp; AgriConnect Escrow Custody</span>. Once cargo departs the regional hub and is inspected at the buyer's destination depot, funds disburse directly to your registered primary receiving account without platform middleman delays.
          </div>
        </div>
      </div>

      <!-- Live Database Summary Metrics -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Held in Escrow -->
        <article :class="['rounded-2xl p-4 border shadow-2xs flex justify-between items-center transition-all', isDark ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">
              Held in Escrow
            </h3>
            <p class="text-xl lg:text-2xl font-black text-amber-500">
              ETB {{ formatNumber(summary.held_in_escrow) }}
            </p>
            <span class="text-[10px] text-amber-400 font-medium">Locked pending inspection</span>
          </div>
          <div :class="['p-3 rounded-xl', isDark ? 'bg-amber-500/10 text-amber-400' : 'bg-amber-50 text-amber-600']">
            <Lock class="w-6 h-6" />
          </div>
        </article>

        <!-- Total Disbursed Payouts -->
        <article :class="['rounded-2xl p-4 border shadow-2xs flex justify-between items-center transition-all', isDark ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">
              Disbursed Payouts
            </h3>
            <p class="text-xl lg:text-2xl font-black text-emerald-500">
              ETB {{ formatNumber(summary.disbursed_total) }}
            </p>
            <span class="text-[10px] text-emerald-400 font-medium">{{ summary.completed_payouts_count }} orders settled</span>
          </div>
          <div :class="['p-3 rounded-xl', isDark ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <CheckCircle2 class="w-6 h-6" />
          </div>
        </article>

        <!-- Pending Inspections -->
        <article :class="['rounded-2xl p-4 border shadow-2xs flex justify-between items-center transition-all', isDark ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
          <div>
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">
              Awaiting Inspection
            </h3>
            <p class="text-xl lg:text-2xl font-black text-blue-500">
              {{ summary.pending_inspection_count }}
            </p>
            <span class="text-[10px] text-blue-400 font-medium">Delivered at warehouse</span>
          </div>
          <div :class="['p-3 rounded-xl', isDark ? 'bg-blue-500/10 text-blue-400' : 'bg-blue-50 text-blue-600']">
            <Clock class="w-6 h-6" />
          </div>
        </article>

        <!-- Active Primary Payout Account -->
        <article :class="['rounded-2xl p-4 border shadow-2xs flex justify-between items-center transition-all', isDark ? 'bg-[#131b2e] border-slate-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
          <div class="truncate mr-2">
            <h3 :class="['text-[11px] font-bold uppercase tracking-wider mb-1', isDark ? 'text-slate-400' : 'text-slate-500']">
              Primary Payout A/C
            </h3>
            <p class="text-sm font-black truncate max-w-[170px]" :title="primaryMethod?.bank_name || 'None Set'">
              {{ primaryMethod?.bank_name || 'None Configured' }}
            </p>
            <span class="text-[10px] text-emerald-500 font-mono font-bold">
              {{ primaryMethod ? maskAccount(primaryMethod.account_number) : 'Click Register to add' }}
            </span>
          </div>
          <div :class="['p-3 rounded-xl', isDark ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600']">
            <Star class="w-6 h-6 fill-current text-amber-500" />
          </div>
        </article>
      </section>

      <!-- Tabs Navigation -->
      <div class="flex items-center gap-2 mb-6 border-b border-slate-200 dark:border-slate-800 pb-3">
        <button
          @click="activeTab = 'accounts'"
          :class="['px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2', activeTab === 'accounts' ? 'bg-emerald-600 text-white shadow-xs' : (isDark ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100')]"
        >
          <Building class="w-4 h-4" />
          <span>Receiving Accounts ({{ payoutMethods.length }})</span>
        </button>

        <button
          @click="activeTab = 'escrow'"
          :class="['px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2', activeTab === 'escrow' ? 'bg-emerald-600 text-white shadow-xs' : (isDark ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100')]"
        >
          <Lock class="w-4 h-4" />
          <span>Active Escrow Holdings ({{ escrowHeldOrders.length }})</span>
        </button>

        <button
          @click="activeTab = 'history'"
          :class="['px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2', activeTab === 'history' ? 'bg-emerald-600 text-white shadow-xs' : (isDark ? 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100')]"
        >
          <Receipt class="w-4 h-4" />
          <span>Settlement History ({{ payoutHistory.length }})</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <RefreshCw class="w-8 h-8 text-emerald-500 animate-spin mb-3" />
        <p :class="['text-xs font-semibold', isDark ? 'text-slate-400' : 'text-slate-600']">
          Synchronizing payout accounts from PostgreSQL database...
        </p>
      </div>

      <!-- TAB 1: RECEIVING PAYOUT ACCOUNTS -->
      <div v-else-if="activeTab === 'accounts'">
        <div v-if="payoutMethods.length === 0" :class="['rounded-2xl border p-12 text-center shadow-xs', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
          <Building class="w-12 h-12 text-slate-400 mx-auto mb-3 opacity-50" />
          <h3 class="text-base font-bold mb-1">No Receiving Accounts Registered Yet</h3>
          <p :class="['text-xs max-w-md mx-auto mb-5', isDark ? 'text-slate-400' : 'text-slate-500']">
            Add your Commercial Bank of Ethiopia (CBE), Awash Bank, Dashen Bank account or Telebirr wallet so that buyer escrow payments can be disbursed immediately upon delivery.
          </p>
          <button
            @click="openAddPayoutModal"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-xs transition"
          >
            + Register First Payout Account
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="pm in payoutMethods"
            :key="pm.id"
            :class="[
              'rounded-2xl border p-5 flex flex-col justify-between shadow-2xs transition-all relative',
              pm.is_primary
                ? (isDark ? 'bg-[#131b2e] border-emerald-500/50 ring-1 ring-emerald-500/30' : 'bg-white border-emerald-400 ring-2 ring-emerald-400/20')
                : (isDark ? 'bg-[#131b2e] border-slate-800 hover:border-slate-700' : 'bg-white border-slate-200 hover:border-slate-300')
            ]"
          >
            <div>
              <div class="flex items-start justify-between gap-2 mb-3">
                <div class="flex items-center gap-2.5">
                  <div :class="['w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs', pm.type === 'mobile_money' ? 'bg-blue-500/10 text-blue-500' : 'bg-emerald-500/10 text-emerald-500']">
                    <Smartphone v-if="pm.type === 'mobile_money'" class="w-4 h-4" />
                    <Building v-else class="w-4 h-4" />
                  </div>
                  <div>
                    <h3 class="text-xs font-bold leading-tight" :title="pm.bank_name">
                      {{ pm.bank_name }}
                    </h3>
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider">
                      {{ pm.type === 'mobile_money' ? 'Mobile Wallet' : 'Bank Transfer' }}
                    </span>
                  </div>
                </div>

                <span
                  v-if="pm.is_primary"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-600 text-white shadow-xs shrink-0"
                >
                  <Star class="w-2.5 h-2.5 fill-current text-amber-300" /> PRIMARY PAYOUT
                </span>
              </div>

              <!-- Account Details -->
              <div :class="['p-3 rounded-xl mb-4 font-mono text-xs', isDark ? 'bg-[#0b0f19] border border-slate-800' : 'bg-slate-50 border border-slate-100']">
                <div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Account / Phone Number</div>
                <div class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-wide">
                  {{ pm.account_number }}
                </div>
                <div class="text-[10px] text-slate-400 mt-1 truncate">
                  Beneficiary: <span class="font-sans font-semibold text-slate-700 dark:text-slate-300">{{ pm.account_holder }}</span>
                </div>
              </div>
            </div>

            <!-- Card Bottom Actions -->
            <div :class="['pt-3 border-t flex items-center justify-between gap-2', isDark ? 'border-slate-800' : 'border-slate-100']">
              <span class="text-[10px] text-slate-400">
                Registered {{ formatDate(pm.created_at) }}
              </span>

              <div class="flex items-center gap-2">
                <button
                  v-if="!pm.is_primary"
                  @click="makePrimary(pm.id)"
                  :disabled="settingPrimaryId === pm.id"
                  class="px-2.5 py-1 rounded-lg text-[11px] font-bold border border-emerald-500/40 text-emerald-500 hover:bg-emerald-500/10 transition cursor-pointer"
                >
                  {{ settingPrimaryId === pm.id ? 'Setting...' : 'Make Primary' }}
                </button>
                <button
                  v-if="!pm.is_primary"
                  @click="deleteAccount(pm.id)"
                  :disabled="deletingId === pm.id"
                  class="p-1 rounded-lg text-slate-400 hover:text-red-400 transition cursor-pointer"
                  title="Remove Account"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: ACTIVE ESCROW HOLDINGS -->
      <div v-else-if="activeTab === 'escrow'">
        <div :class="['rounded-2xl border overflow-hidden shadow-xs', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
          <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
            <div>
              <h2 class="text-sm font-bold m-0">Orders with Funds Locked in Escrow Custody</h2>
              <p :class="['text-xs m-0', isDark ? 'text-slate-400' : 'text-slate-500']">
                Funds are protected by Chapa Escrow and released automatically upon delivery &amp; warehouse inspection
              </p>
            </div>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">
              Total Held: ETB {{ formatNumber(summary.held_in_escrow) }}
            </span>
          </div>

          <div v-if="escrowHeldOrders.length === 0" class="p-12 text-center text-xs text-slate-400">
            No active orders currently held in escrow. When buyers place produce orders, escrow funds will appear here.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr :class="['border-b uppercase font-bold text-[10px] tracking-wider', isDark ? 'border-slate-800 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-500']">
                  <th class="py-3.5 px-4">Order #</th>
                  <th class="py-3.5 px-4">Buyer Customer</th>
                  <th class="py-3.5 px-4">Escrow Balance</th>
                  <th class="py-3.5 px-4">Order Stage</th>
                  <th class="py-3.5 px-4">Escrow Status</th>
                  <th class="py-3.5 px-4 text-right">Settlement Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="order in escrowHeldOrders" :key="order.id" :class="isDark ? 'hover:bg-slate-800/40' : 'hover:bg-slate-50'">
                  <td class="py-3.5 px-4 font-mono font-bold text-blue-500">
                    {{ order.order_number }}
                  </td>
                  <td class="py-3.5 px-4 font-semibold">
                    {{ order.buyer_name }}
                  </td>
                  <td class="py-3.5 px-4 font-mono font-bold text-amber-500">
                    ETB {{ formatNumber(order.amount) }}
                  </td>
                  <td class="py-3.5 px-4">
                    <span :class="['inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase', order.status === 'delivered' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-blue-500/15 text-blue-400 border border-blue-500/30']">
                      {{ order.status }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-500 border border-amber-500/30">
                      <Lock class="w-3 h-3" /> Chapa Custody
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <button
                      v-if="order.inspection_ready"
                      @click="triggerDisbursement(order.id)"
                      :disabled="disbursingId === order.id"
                      class="px-3 py-1.5 rounded-xl text-[11px] font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition cursor-pointer disabled:opacity-50"
                    >
                      <RefreshCw v-if="disbursingId === order.id" class="w-3 h-3 animate-spin inline mr-1" />
                      <span>{{ disbursingId === order.id ? 'Settling...' : 'Release Payout' }}</span>
                    </button>
                    <span v-else class="text-[11px] text-slate-400 italic">
                      Awaiting warehouse arrival
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 3: SETTLEMENT & PAYOUT HISTORY -->
      <div v-else-if="activeTab === 'history'">
        <div :class="['rounded-2xl border overflow-hidden shadow-xs', isDark ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
          <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
              <h2 class="text-sm font-bold m-0">Settlement Payout History</h2>
              <p :class="['text-xs m-0', isDark ? 'text-slate-400' : 'text-slate-500']">
                Audit log of all funds disbursed from Chapa Escrow Custody into your bank account
              </p>
            </div>
            <span class="text-xs text-slate-400 font-mono">
              Total Settled: <span class="font-bold text-emerald-500">ETB {{ formatNumber(summary.disbursed_total) }}</span>
            </span>
          </div>

          <div v-if="payoutHistory.length === 0" class="p-12 text-center text-xs text-slate-400">
            No completed escrow payouts found in database yet. Once deliveries are inspected, disbursements appear here.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr :class="['border-b uppercase font-bold text-[10px] tracking-wider', isDark ? 'border-slate-800 bg-[#0f172a] text-slate-400' : 'border-slate-200 bg-slate-50 text-slate-500']">
                  <th class="py-3.5 px-4">Disbursement Ref</th>
                  <th class="py-3.5 px-4">Order #</th>
                  <th class="py-3.5 px-4">Produce Details</th>
                  <th class="py-3.5 px-4">Amount Disbursed</th>
                  <th class="py-3.5 px-4">Settled To Account</th>
                  <th class="py-3.5 px-4">Disbursed Date</th>
                  <th class="py-3.5 px-4 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr v-for="item in payoutHistory" :key="item.id" :class="isDark ? 'hover:bg-slate-800/40' : 'hover:bg-slate-50'">
                  <td class="py-3.5 px-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                    {{ item.payout_reference }}
                  </td>
                  <td class="py-3.5 px-4 font-mono font-semibold text-slate-600 dark:text-slate-300">
                    {{ item.order_number }}
                  </td>
                  <td class="py-3.5 px-4 max-w-xs truncate text-[11px]" :title="item.items_summary">
                    {{ item.items_summary || 'Agricultural Produce' }}
                  </td>
                  <td class="py-3.5 px-4 font-mono font-bold text-emerald-500">
                    ETB {{ formatNumber(item.amount) }}
                  </td>
                  <td class="py-3.5 px-4 text-[11px]">
                    <div class="font-bold">{{ item.account_details?.bank_name }}</div>
                    <div class="font-mono text-slate-400">{{ item.account_details?.account_number }}</div>
                  </td>
                  <td class="py-3.5 px-4 text-[11px] text-slate-400 whitespace-nowrap">
                    {{ formatDate(item.disbursed_at) }}
                  </td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-500 border border-emerald-500/30">
                      <CheckCircle2 class="w-3 h-3" /> Disbursed
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <!-- MODAL: REGISTER PAYOUT ACCOUNT -->
    <div
      v-if="addPayoutModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
      @click.self="addPayoutModalOpen = false"
    >
      <div :class="['rounded-2xl border w-full max-w-md overflow-hidden shadow-2xl transition-all', isDark ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-900']">
        <div :class="['px-6 py-4 border-b flex items-center justify-between', isDark ? 'border-slate-800 bg-[#0f172a]' : 'border-slate-200 bg-slate-50']">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-500 flex items-center justify-center">
              <Building class="w-4 h-4" />
            </div>
            <div>
              <h2 class="text-base font-bold m-0">Register Payout Receiving Account</h2>
              <p :class="['text-[11px] m-0', isDark ? 'text-slate-400' : 'text-slate-500']">
                Destination account for automated Chapa escrow produce payouts
              </p>
            </div>
          </div>
          <button @click="addPayoutModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-200 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitAddPayoutMethod" class="p-6 space-y-4 text-xs">
          <div>
            <label :class="['block font-bold mb-1.5', isDark ? 'text-slate-300' : 'text-slate-700']">
              Payout Channel / Bank *
            </label>
            <select
              v-model="newMethodForm.bank_name"
              required
              :class="['w-full px-3 py-2.5 rounded-xl border text-xs outline-none focus:border-emerald-500', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            >
              <option value="Commercial Bank of Ethiopia (CBE)">Commercial Bank of Ethiopia (CBE)</option>
              <option value="Awash Bank">Awash Bank</option>
              <option value="Dashen Bank">Dashen Bank</option>
              <option value="Bank of Abyssinia">Bank of Abyssinia</option>
              <option value="Telebirr (Ethio Telecom)">Telebirr (Ethio Telecom)</option>
              <option value="CBE Birr">CBE Birr</option>
              <option value="Cooperative Bank of Oromia (Coop)">Cooperative Bank of Oromia (Coop)</option>
              <option value="Oromia Bank">Oromia Bank</option>
              <option value="Nib International Bank">Nib International Bank</option>
              <option value="Zemen Bank">Zemen Bank</option>
            </select>
          </div>

          <div>
            <label :class="['block font-bold mb-1.5', isDark ? 'text-slate-300' : 'text-slate-700']">
              Account Holder / Co-op Legal Name *
            </label>
            <input
              v-model="newMethodForm.account_holder"
              type="text"
              required
              placeholder="e.g. Bahir Dar Agro Farmers Cooperative"
              :class="['w-full px-3 py-2.5 rounded-xl border text-xs outline-none focus:border-emerald-500', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            />
          </div>

          <div>
            <label :class="['block font-bold mb-1.5', isDark ? 'text-slate-300' : 'text-slate-700']">
              Account Number or Mobile Wallet Phone *
            </label>
            <input
              v-model="newMethodForm.account_number"
              type="text"
              required
              placeholder="e.g. 1000 4829 10482 or 0911234567"
              :class="['w-full px-3 py-2.5 rounded-xl border text-xs outline-none focus:border-emerald-500 font-mono', isDark ? 'bg-[#0f172a] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
            />
          </div>

          <label class="flex items-center gap-2 cursor-pointer pt-1">
            <input
              v-model="newMethodForm.is_primary"
              type="checkbox"
              class="rounded text-emerald-600 focus:ring-emerald-500"
            />
            <span :class="['font-bold', isDark ? 'text-slate-300' : 'text-slate-700']">
              Set as primary payout receiving account
            </span>
          </label>

          <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-2.5">
            <button
              type="button"
              @click="addPayoutModalOpen = false"
              :class="['px-4 py-2 rounded-xl font-bold border transition cursor-pointer', isDark ? 'border-slate-700 text-slate-300 hover:bg-slate-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100']"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="savingMethod"
              class="px-5 py-2 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md transition cursor-pointer disabled:opacity-50 flex items-center gap-1.5"
            >
              <RefreshCw v-if="savingMethod" class="w-3.5 h-3.5 animate-spin" />
              <span>{{ savingMethod ? 'Saving Account...' : 'Save Payout Account' }}</span>
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
import { useTheme } from '@/composables/useTheme'
import { farmerAPI } from '@/api/farmer'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import {
  Plus, Building, Star, CheckCircle2, Lock, Clock, Receipt,
  Smartphone, Trash2, RefreshCw, X, ShieldCheck
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const { isDark } = useTheme()

const loading = ref(true)
const activeTab = ref<'accounts' | 'escrow' | 'history'>('accounts')
const payoutMethods = ref<any[]>([])
const primaryMethod = ref<any | null>(null)
const escrowHeldOrders = ref<any[]>([])
const payoutHistory = ref<any[]>([])

const summary = ref({
  held_in_escrow: 0,
  disbursed_total: 0,
  total_sales: 0,
  pending_inspection_count: 0,
  completed_payouts_count: 0
})

const addPayoutModalOpen = ref(false)
const savingMethod = ref(false)
const settingPrimaryId = ref<number | null>(null)
const deletingId = ref<number | null>(null)
const disbursingId = ref<number | null>(null)

const newMethodForm = ref({
  bank_name: 'Commercial Bank of Ethiopia (CBE)',
  account_holder: '',
  account_number: '',
  type: 'bank_transfer',
  is_primary: true
})

const formatNumber = (val: number | string | undefined): string => {
  const n = Number(val) || 0
  return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (isoString?: string): string => {
  if (!isoString) return 'N/A'
  try {
    const d = new Date(isoString)
    return d.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch {
    return String(isoString)
  }
}

const maskAccount = (num?: string): string => {
  if (!num) return '••••'
  const clean = String(num).trim()
  if (clean.length <= 4) return clean
  return '•••• ' + clean.slice(-4)
}

const loadPayoutData = async () => {
  loading.value = true
  try {
    const res = await farmerAPI.getPayoutSettings()
    const data = res.data?.data || {}
    payoutMethods.value = data.payout_methods || []
    primaryMethod.value = data.primary_method || (payoutMethods.value.find((m: any) => m.is_primary) || payoutMethods.value[0] || null)
    escrowHeldOrders.value = data.escrow_held_orders || []
    payoutHistory.value = data.payout_history || []
    if (data.escrow_summary) {
      summary.value = {
        held_in_escrow: Number(data.escrow_summary.held_in_escrow) || 0,
        disbursed_total: Number(data.escrow_summary.disbursed_total) || 0,
        total_sales: Number(data.escrow_summary.total_sales) || 0,
        pending_inspection_count: Number(data.escrow_summary.pending_inspection_count) || 0,
        completed_payouts_count: Number(data.escrow_summary.completed_payouts_count) || 0
      }
    }
    // Pre-fill holder name with current user/co-op
    if (!newMethodForm.value.account_holder) {
      newMethodForm.value.account_holder = data.cooperative || data.farmer_name || authStore.user?.name || 'Farmers Cooperative'
    }
  } catch (err) {
    console.error('Failed to load farmer payout settings:', err)
  } finally {
    loading.value = false
  }
}

const openAddPayoutModal = () => {
  if (!newMethodForm.value.account_holder) {
    newMethodForm.value.account_holder = authStore.user?.name || 'Farmers Cooperative'
  }
  newMethodForm.value.account_number = ''
  newMethodForm.value.is_primary = payoutMethods.value.length === 0
  addPayoutModalOpen.value = true
}

const submitAddPayoutMethod = async () => {
  savingMethod.value = true
  try {
    const isMobile = newMethodForm.value.bank_name.toLowerCase().includes('telebirr') || newMethodForm.value.bank_name.toLowerCase().includes('birr')
    const payload = {
      bank_name: newMethodForm.value.bank_name,
      account_holder: newMethodForm.value.account_holder,
      account_number: newMethodForm.value.account_number,
      type: isMobile ? 'mobile_money' : 'bank_transfer',
      is_primary: newMethodForm.value.is_primary
    }
    await farmerAPI.addPayoutMethod(payload)
    addPayoutModalOpen.value = false
    await loadPayoutData()
  } catch (err: any) {
    console.error('Failed to register payout method:', err)
    alert(err?.response?.data?.message || 'Failed to register receiving payout account.')
  } finally {
    savingMethod.value = false
  }
}

const makePrimary = async (id: number) => {
  settingPrimaryId.value = id
  try {
    await farmerAPI.setPrimaryPayoutMethod(id)
    await loadPayoutData()
  } catch (err: any) {
    console.error('Failed to set primary payout method:', err)
    alert(err?.response?.data?.message || 'Failed to update primary payout method.')
  } finally {
    settingPrimaryId.value = null
  }
}

const deleteAccount = async (id: number) => {
  if (!window.confirm('Are you sure you want to remove this receiving payout account?')) return
  deletingId.value = id
  try {
    await farmerAPI.deletePayoutMethod(id)
    await loadPayoutData()
  } catch (err: any) {
    console.error('Failed to delete payout method:', err)
    alert(err?.response?.data?.message || 'Failed to remove payout account.')
  } finally {
    deletingId.value = null
  }
}

const triggerDisbursement = async (orderId: number) => {
  if (!window.confirm('Release Chapa Escrow funds for this delivered order to your primary receiving account?')) return
  disbursingId.value = orderId
  try {
    const res = await farmerAPI.disburseEscrowOrder(orderId)
    if (res.data?.success) {
      alert('Payout disbursed successfully! Chapa Reference: ' + (res.data.payout_reference || 'DISB-COMPLETED'))
      await loadPayoutData()
    } else {
      alert(res.data?.message || 'Escrow disbursement triggered.')
    }
  } catch (err: any) {
    console.error('Failed to disburse escrow:', err)
    alert(err?.response?.data?.message || 'Failed to release escrow payout.')
  } finally {
    disbursingId.value = null
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  loadPayoutData()
})
</script>
