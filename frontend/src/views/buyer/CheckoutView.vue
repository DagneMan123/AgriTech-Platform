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
      <div :class="['border-b border-t-0 border-x-0 transition-all -mx-4 lg:-mx-8 -mt-4 lg:-mt-8 mb-6 px-6 lg:px-8 py-[24px]', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="lg:hidden p-2 rounded-xl border transition-all text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800"
              aria-label="Toggle menu"
            >
              <Menu class="w-5 h-5" />
            </button>
            <div>
              <div class="flex items-center gap-2 mb-1">
                <router-link
                  to="/buyer/cart"
                  class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline"
                >
                  <ArrowLeft class="w-3.5 h-3.5" />
                  Back to Cart
                </router-link>
              </div>
              <h1 :class="['text-[26px] lg:text-[28px] font-[800] m-0 tracking-tight flex items-center gap-2', darkMode ? 'text-slate-100' : 'text-slate-900']">
                Produce Checkout &amp; Payment
              </h1>
              <p :class="['text-xs lg:text-sm m-0 mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                Complete delivery address and secure payment powered by Ethiopian banking and Chapa gateway
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <BuyerHeaderNav />
          </div>
        </div>
      </div>

      <!-- Stepper Indicator -->
      <div :class="['rounded-2xl border p-4 mb-6 shadow-2xs transition-all flex items-center justify-between overflow-x-auto gap-4', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="flex items-center gap-3 shrink-0">
          <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-xs">
            <Check class="w-4 h-4" />
          </div>
          <div>
            <p class="text-xs font-bold leading-tight">1. Shopping Cart</p>
            <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-500']">{{ cartItems.length }} item(s) selected</p>
          </div>
        </div>

        <div class="h-0.5 flex-1 min-w-[30px] rounded bg-blue-600"></div>

        <div class="flex items-center gap-3 shrink-0">
          <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-xs text-white', orderSuccess ? 'bg-blue-600' : 'bg-blue-600 ring-4 ring-blue-500/20']">
            <Check v-if="orderSuccess" class="w-4 h-4" />
            <span v-else>2</span>
          </div>
          <div>
            <p class="text-xs font-bold leading-tight">2. Shipping &amp; Payment</p>
            <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-500']">{{ orderSuccess ? 'Completed' : 'Current Step' }}</p>
          </div>
        </div>

        <div :class="['h-0.5 flex-1 min-w-[30px] rounded', orderSuccess ? 'bg-blue-600' : (darkMode ? 'bg-slate-800' : 'bg-slate-200')]"></div>

        <div class="flex items-center gap-3 shrink-0">
          <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-xs', orderSuccess ? 'bg-blue-600 text-white ring-4 ring-blue-500/20' : (darkMode ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-400')]">
            <Check v-if="orderSuccess" class="w-4 h-4" />
            <span v-else>3</span>
          </div>
          <div>
            <p class="text-xs font-bold leading-tight">3. Order Confirmed</p>
            <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-500']">{{ orderSuccess ? 'Fulfilled' : 'Pending Confirmation' }}</p>
          </div>
        </div>
      </div>

      <!-- ── SUCCESS CONFIRMATION SCREEN ── -->
      <section v-if="orderSuccess" :class="['rounded-2xl border p-6 sm:p-10 mb-8 shadow-md text-center max-w-3xl mx-auto transition-all', darkMode ? 'bg-[#131b2e] border-blue-500/40 text-slate-100' : 'bg-white border-blue-200 text-slate-800']">
        <div class="w-20 h-20 rounded-full bg-blue-500/10 text-blue-500 border-2 border-blue-500/30 flex items-center justify-center mx-auto mb-5 shadow-lg animate-bounce">
          <CheckCircle2 class="w-10 h-10" />
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-500 border border-blue-500/30 mb-3">
          <Sparkles class="w-3.5 h-3.5" /> Order Successfully Placed
        </span>
        <h2 class="text-2xl sm:text-3xl font-black tracking-tight mb-2">Thank you for your order!</h2>
        <p :class="['text-xs sm:text-sm max-w-md mx-auto mb-6 leading-relaxed', darkMode ? 'text-slate-400' : 'text-slate-600']">
          Your order has been recorded in the database. Farmers are preparing your fresh harvest for fulfillment.
        </p>

        <!-- Order Numbers & Details Summary -->
        <div :class="['rounded-2xl p-5 mb-8 border text-left grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
          <div>
            <span class="text-slate-400 uppercase text-[10px] font-bold tracking-wider">Order Reference:</span>
            <p class="font-mono font-bold text-sm text-blue-500 mt-0.5">
              {{ confirmedOrders[0]?.order_number || 'ORD-ETH-SUCCESS' }}
            </p>
          </div>
          <div>
            <span class="text-slate-400 uppercase text-[10px] font-bold tracking-wider">Total Paid / Payable:</span>
            <p class="font-mono font-bold text-sm text-blue-500 mt-0.5">
              ETB {{ formatNumber(summaryGrandTotal) }}
            </p>
          </div>
          <div>
            <span class="text-slate-400 uppercase text-[10px] font-bold tracking-wider">Payment Method:</span>
            <p class="font-semibold text-slate-300 dark:text-slate-200 capitalize mt-0.5 flex items-center gap-1.5">
              <CreditCard class="w-3.5 h-3.5 text-blue-500" />
              {{ formatPaymentName(paymentForm.method) }}
            </p>
          </div>
          <div>
            <span class="text-slate-400 uppercase text-[10px] font-bold tracking-wider">Delivery Destination:</span>
            <p class="font-semibold text-slate-300 dark:text-slate-200 mt-0.5 truncate" :title="formattedAddressPreview">
              {{ formattedAddressPreview }}
            </p>
          </div>
        </div>

        <!-- Action Links -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
          <router-link
            to="/buyer/orders/active"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-500 text-white shadow-sm transition-all transform active:scale-95 cursor-pointer"
          >
            <Truck class="w-4 h-4" />
            <span>Track in Active Orders</span>
          </router-link>
          <router-link
            to="/buyer/marketplace"
            :class="['w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-xs border transition-all cursor-pointer', darkMode ? 'border-slate-700 hover:bg-slate-800 text-slate-300' : 'border-slate-300 hover:bg-slate-100 text-slate-700']"
          >
            <ShoppingBag class="w-4 h-4" />
            <span>Continue Shopping</span>
          </router-link>
        </div>
      </section>

      <!-- ── EMPTY CART NOTICE ── -->
      <section v-else-if="cartItems.length === 0" :class="['rounded-2xl border p-12 text-center shadow-2xs transition-all max-w-xl mx-auto', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
        <div class="w-16 h-16 rounded-full bg-blue-500/10 text-blue-500 flex items-center justify-center mx-auto mb-4">
          <ShoppingBag class="w-8 h-8" />
        </div>
        <h3 class="text-lg font-bold mb-1">Your cart is currently empty</h3>
        <p :class="['text-xs mb-6 max-w-sm mx-auto', darkMode ? 'text-slate-400' : 'text-slate-500']">
          Please add agricultural produce from our marketplace before proceeding to checkout.
        </p>
        <router-link
          to="/buyer/marketplace"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-500 text-white shadow-sm transition-all cursor-pointer"
        >
          <ShoppingBag class="w-4 h-4" />
          <span>Browse Marketplace</span>
        </router-link>
      </section>

      <!-- ── CHECKOUT FORM & SUMMARY GRID ── -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">

        <!-- LEFT COLUMN: Address, Shipping & Payment (8 Cols) -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-6">

          <!-- SECTION 1: DELIVERY ADDRESS -->
          <section :class="['rounded-2xl border p-5 sm:p-6 shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-200 dark:border-slate-800">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                  <MapPin class="w-4 h-4" />
                </div>
                <div>
                  <h2 class="text-sm font-bold tracking-tight">1. Delivery Address &amp; Recipient</h2>
                  <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-500']">Where should the produce be dispatched?</p>
                </div>
              </div>

              <!-- Saved Addresses Quick Select -->
              <div v-if="savedAddresses.length > 0" class="relative">
                <select
                  v-model="selectedSavedAddressId"
                  @change="applySavedAddress"
                  :class="['px-3 py-1.5 rounded-xl border text-xs font-semibold cursor-pointer focus:outline-none focus:border-blue-500', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-800']"
                >
                  <option :value="null">Use Custom Address</option>
                  <option v-for="addr in savedAddresses" :key="addr.id" :value="addr.id">
                    {{ addr.title || addr.label || addr.street_address || 'Saved Address' }} ({{ formatHierarchicalLocation(addr) }})
                  </option>
                </select>
              </div>
            </div>

            <!-- Address Form Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label :class="['block text-xs font-bold mb-1.5', darkMode ? 'text-slate-300' : 'text-slate-700']">
                  Recipient Full Name *
                </label>
                <div class="relative">
                  <User class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                  <input
                    v-model="addressForm.recipient_name"
                    type="text"
                    placeholder="e.g. Abebe Bikila"
                    required
                    :class="['w-full pl-9 pr-3 py-2.5 rounded-xl border text-xs focus:outline-none focus:border-blue-500 transition', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                  />
                </div>
              </div>

              <div>
                <label :class="['block text-xs font-bold mb-1.5', darkMode ? 'text-slate-300' : 'text-slate-700']">
                  Contact Phone Number *
                </label>
                <div class="relative">
                  <Phone class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                  <input
                    v-model="addressForm.phone"
                    type="text"
                    placeholder="e.g. +251 911 234 567"
                    required
                    :class="['w-full pl-9 pr-3 py-2.5 rounded-xl border text-xs focus:outline-none focus:border-blue-500 transition', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                  />
                </div>
              </div>

              <div>
                <label :class="['block text-xs font-bold mb-1.5', darkMode ? 'text-slate-300' : 'text-slate-700']">
                  Delivery Destination (Hub &amp; Region) *
                </label>
                <div
                  :class="['w-full px-3 py-2.5 rounded-xl border text-xs font-semibold flex items-center justify-between', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                >
                  <span>{{ destinationDisplayName }}</span>
                  <span class="text-[10px] text-blue-500 font-semibold tracking-wide uppercase">Set in Cart</span>
                </div>
              </div>

              <div>
                <label :class="['block text-xs font-bold mb-1.5', darkMode ? 'text-slate-300' : 'text-slate-700']">
                  Zone / Sub-City *
                </label>
                <input
                  v-model="addressForm.zone"
                  type="text"
                  placeholder="e.g. Bole Subcity / West Shewa"
                  required
                  :class="['w-full px-3 py-2.5 rounded-xl border text-xs focus:outline-none focus:border-blue-500 transition', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                />
              </div>

              <div>
                <label :class="['block text-xs font-bold mb-1.5', darkMode ? 'text-slate-300' : 'text-slate-700']">
                  Woreda / Kebele *
                </label>
                <input
                  v-model="addressForm.woreda"
                  type="text"
                  placeholder="e.g. Woreda 03, Kebele 12"
                  required
                  :class="['w-full px-3 py-2.5 rounded-xl border text-xs focus:outline-none focus:border-blue-500 transition', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                />
              </div>

              <div>
                <label :class="['block text-xs font-bold mb-1.5', darkMode ? 'text-slate-300' : 'text-slate-700']">
                  Specific Street / Warehouse / Landmark *
                </label>
                <input
                  v-model="addressForm.address"
                  type="text"
                  placeholder="e.g. Near Edna Mall, Warehouse 4B"
                  required
                  :class="['w-full px-3 py-2.5 rounded-xl border text-xs focus:outline-none focus:border-blue-500 transition', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                />
              </div>

              <div class="sm:col-span-2">
                <label :class="['block text-xs font-bold mb-1.5', darkMode ? 'text-slate-300' : 'text-slate-700']">
                  Special Delivery Instructions (Optional)
                </label>
                <input
                  v-model="addressForm.notes"
                  type="text"
                  placeholder="e.g. Forklift available on site, deliver before 3 PM"
                  :class="['w-full px-3 py-2 rounded-xl border text-xs focus:outline-none focus:border-blue-500 transition', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                />
              </div>
            </div>
          </section>

          <!-- SECTION 2: SHIPPING SPEED & FREIGHT (DYNAMIC CALCULATION) -->
          <section :class="['rounded-2xl border p-5 sm:p-6 shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-200 dark:border-slate-800 flex-wrap gap-2">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                  <Truck class="w-4 h-4" />
                </div>
                <div>
                  <h2 class="text-sm font-bold tracking-tight">2. Freight &amp; Delivery Logistics</h2>
                  <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-500']">
                    Dynamic rates calculated automatically by cargo weight, transit distance, and crop perishability
                  </p>
                </div>
              </div>
            </div>

            <!-- Dynamic Category Filtering Notice -->
            <div
              v-if="hasPerishables"
              class="mb-4 p-3 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-600 dark:text-blue-400 text-xs flex items-center gap-2.5"
            >
              <Leaf class="w-4 h-4 shrink-0" />
              <span>
                <strong>Fresh Perishable Produce in Cart:</strong> Cold-Chain refrigerated transit (+4°C) is active and recommended to prevent harvest spoilage.
              </span>
            </div>
            <div
              v-else
              class="mb-4 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs flex items-center gap-2.5"
            >
              <Info class="w-4 h-4 shrink-0" />
              <span>
                <strong>Dry Produce / Non-Perishable Cargo:</strong> Cart contains shelf-stable produce (e.g. grains, pulses, seeds). Cold-chain shipping is dynamically filtered out.
              </span>
            </div>

            <!-- Dynamic Shipping Options Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              <label
                v-for="opt in shippingOptions"
                :key="opt.key"
                :class="[
                  'relative rounded-xl border p-4 cursor-pointer transition-all flex flex-col justify-between group',
                  selectedShipping === opt.key
                    ? (darkMode ? 'border-blue-500 bg-blue-500/10 ring-2 ring-blue-500/30' : 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-600/20')
                    : (darkMode ? 'border-slate-800 bg-[#0b0f19] hover:border-slate-700' : 'border-slate-200 bg-white hover:border-slate-300')
                ]"
              >
                <div>
                  <div class="flex items-start justify-between gap-1 mb-2">
                    <div class="flex items-center gap-2">
                      <input
                        type="radio"
                        v-model="selectedShipping"
                        :value="opt.key"
                        class="text-blue-600 focus:ring-blue-500"
                      />
                      <span class="text-xs font-bold leading-tight">{{ opt.title }}</span>
                    </div>
                  </div>

                  <span
                    v-if="opt.badge"
                    class="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase mb-2 bg-blue-500/20 text-blue-500 border border-blue-500/30"
                  >
                    {{ opt.badge }}
                  </span>

                  <p :class="['text-[11px] mb-2 leading-snug', darkMode ? 'text-slate-400' : 'text-slate-500']">
                    {{ opt.desc }}
                  </p>

                  <div
                    v-if="opt.breakdown && !opt.breakdown.isFlatRate"
                    class="p-2 rounded-lg bg-blue-500/5 border border-blue-500/10 mb-3 text-[10px] text-blue-600 dark:text-blue-400 font-mono leading-tight"
                  >
                    {{ opt.breakdown.formulaStr }}
                  </div>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                  <span class="text-[10px] text-slate-400">
                    {{ opt.isMultiOrigin ? `Total (${opt.originCount} origins):` : 'Dynamic Fee:' }}
                  </span>
                  <p class="text-sm font-black text-blue-600 dark:text-blue-400">
                    ETB {{ formatNumber(opt.fee) }}
                  </p>
                </div>
              </label>
            </div>
          </section>

          <!-- SECTION 3: PAYMENT METHOD -->
          <section :class="['rounded-2xl border p-5 sm:p-6 shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
            <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-200 dark:border-slate-800">
              <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                <CreditCard class="w-4 h-4" />
              </div>
              <div>
                <h2 class="text-sm font-bold tracking-tight">3. Payment Channel &amp; Settlement</h2>
                <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-500']">Integrated with Chapa Ethiopian payment gateway</p>
              </div>
            </div>

            <!-- Primary Payment Method Banner -->
            <div
              v-if="primaryPaymentMethod"
              :class="[
                'mb-4 p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all',
                darkMode ? 'bg-emerald-950/20 border-emerald-500/30' : 'bg-emerald-50 border-emerald-200'
              ]"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                  <CheckCircle2 class="w-5 h-5" />
                </div>
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-slate-900 dark:text-slate-100">
                      {{ primaryPaymentMethod.bank_name }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white shadow-xs">
                      Default Primary Method
                    </span>
                  </div>
                  <p :class="['text-[11px] mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                    A/C: <span class="font-mono font-bold">{{ maskAccountNumber(primaryPaymentMethod.account_number) }}</span>
                    &bull; Account Holder: <span class="font-medium">{{ primaryPaymentMethod.account_holder }}</span>
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-3 self-end sm:self-auto">
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                  <Check class="w-3.5 h-3.5" /> Pre-Selected
                </span>
                <router-link
                  to="/buyer/payments"
                  class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1"
                >
                  Manage Methods &rarr;
                </router-link>
              </div>
            </div>

            <!-- Payment Options Radio Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
              <label
                v-for="pm in paymentMethodsList"
                :key="pm.key"
                :class="[
                  'relative rounded-xl border p-4 cursor-pointer transition-all flex items-start gap-3',
                  paymentForm.method === pm.key
                    ? (darkMode ? 'border-blue-500 bg-blue-500/10 ring-2 ring-blue-500/30' : 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-600/20')
                    : (darkMode ? 'border-slate-800 bg-[#0b0f19] hover:border-slate-700' : 'border-slate-200 bg-white hover:border-slate-300')
                ]"
              >
                <input
                  type="radio"
                  v-model="paymentForm.method"
                  :value="pm.key"
                  class="mt-1 text-blue-600 focus:ring-blue-500"
                />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold">{{ pm.title }}</span>
                    <span v-if="pm.badge" class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-blue-500/20 text-blue-500 border border-blue-500/30">
                      {{ pm.badge }}
                    </span>
                  </div>
                  <p :class="['text-[11px] leading-tight', darkMode ? 'text-slate-400' : 'text-slate-500']">
                    {{ pm.desc }}
                  </p>
                </div>
              </label>
            </div>

            <!-- Dynamic Payment Details Panel -->
            <div :class="['rounded-xl p-4 border transition-all text-xs', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div v-if="paymentForm.method === 'telebirr'" class="space-y-3">
                <div class="flex items-center gap-2 text-blue-600 font-bold">
                  <Phone class="w-4 h-4" />
                  <span>Telebirr Instant Payment</span>
                </div>
                <div class="max-w-xs">
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Telebirr Mobile Number:</label>
                  <input
                    v-model="paymentForm.telebirr_phone"
                    type="text"
                    placeholder="09... or 07..."
                    :class="['w-full px-3 py-2 rounded-xl border text-xs focus:outline-none focus:border-blue-500', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                  />
                </div>
              </div>

              <div v-else-if="paymentForm.method === 'cbe_birr'" class="space-y-3">
                <div class="flex items-center gap-2 text-blue-500 font-bold">
                  <Building class="w-4 h-4" />
                  <span>CBE Birr Mobile Money</span>
                </div>
                <div class="max-w-xs">
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">CBE Birr Mobile Number:</label>
                  <input
                    v-model="paymentForm.cbe_phone"
                    type="text"
                    placeholder="09... or 07..."
                    :class="['w-full px-3 py-2 rounded-xl border text-xs focus:outline-none focus:border-blue-500', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                  />
                </div>
              </div>

              <div v-else-if="paymentForm.method === 'bank_transfer'" class="space-y-3">
                <div class="flex items-center gap-2 text-blue-500 font-bold">
                  <Building class="w-4 h-4" />
                  <span>Direct Bank Transfer</span>
                </div>
                <div class="max-w-xs">
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Transfer Transaction Reference:</label>
                  <input
                    v-model="paymentForm.bank_ref"
                    type="text"
                    placeholder="e.g. FT26301ABC..."
                    :class="['w-full px-3 py-2 rounded-xl border text-xs focus:outline-none focus:border-blue-500', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                  />
                </div>
              </div>
            </div>
          </section>
        </div>

        <!-- RIGHT COLUMN: Order Summary & Pay Action (4/5 Cols) -->
        <div class="lg:col-span-5 xl:col-span-4">
          <div :class="['rounded-2xl border p-5 sm:p-6 shadow-sm sticky top-6 transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
            <h2 class="text-sm font-bold tracking-tight mb-4 pb-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
              <span>Order Summary</span>
              <span class="text-xs font-semibold text-blue-500">{{ cartItems.length }} item(s)</span>
            </h2>

            <!-- Items Mini List -->
            <div class="max-h-64 overflow-y-auto space-y-3 pr-1 mb-4 divide-y divide-slate-100 dark:divide-slate-800/60">
              <div
                v-for="item in cartItems"
                :key="item.id"
                class="pt-3 first:pt-0 flex items-center gap-3"
              >
                <div :class="['w-12 h-12 rounded-xl overflow-hidden shrink-0 flex items-center justify-center border', darkMode ? 'bg-slate-900 border-slate-800' : 'bg-slate-50 border-slate-200']">
                  <img
                    v-if="formatImageUrl(item.image_url)"
                    :src="formatImageUrl(item.image_url)"
                    :alt="item.product_name"
                    @error="(e: any) => e.target.style.display = 'none'"
                    class="w-full h-full object-cover"
                  />
                  <Leaf v-else class="w-5 h-5 text-blue-500" />
                </div>

                <div class="flex-1 min-w-0">
                  <h4 :class="['text-xs font-bold truncate', darkMode ? 'text-slate-200' : 'text-slate-800']">
                    {{ item.product_name }}
                  </h4>
                  <div class="flex items-center gap-2 text-[10px] mb-1.5 flex-wrap">
                    <span :class="darkMode ? 'text-slate-400' : 'text-slate-500'">
                      ETB {{ formatNumber(item.unit_price || item.price || 0) }} / {{ item.unit || 'kg' }} ({{ item.quantity }} {{ item.unit || 'kg' }})
                    </span>
                  </div>
                </div>

                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 shrink-0 self-center">
                  ETB {{ formatNumber(item.subtotal || ((item.unit_price || item.price) * item.quantity)) }}
                </span>
              </div>
            </div>

            <!-- Financial Calculations (Including 16% Tax) -->
            <div :class="['rounded-xl p-3 mb-5 space-y-2.5 border text-xs', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex justify-between items-center text-slate-500 dark:text-slate-400">
                <span>Subtotal:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">ETB {{ formatNumber(summarySubtotal) }}</span>
              </div>

              <div class="flex justify-between items-center text-slate-500 dark:text-slate-400">
                <span class="truncate pr-2">Shipping ({{ selectedShippingTitle }}):</span>
                <span class="font-bold text-blue-600 dark:text-blue-400 shrink-0">ETB {{ formatNumber(selectedShippingFee) }}</span>
              </div>

              <!-- 16% Tax Calculation (Professionally Added) -->
              <div class="flex justify-between items-center text-slate-500 dark:text-slate-400">
                <span>Tax (16% VAT):</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">ETB {{ formatNumber(summaryTax) }}</span>
              </div>

              <div class="flex justify-between items-center text-slate-500 dark:text-slate-400">
                <span>Platform Escrow Fee:</span>
                <span class="font-bold text-blue-500">FREE</span>
              </div>

              <div v-if="summaryDiscount > 0" class="flex justify-between items-center text-blue-500">
                <span>Promo Discount:</span>
                <span class="font-bold">-ETB {{ formatNumber(summaryDiscount) }}</span>
              </div>

              <div class="border-t border-slate-200 dark:border-slate-800 pt-2 flex justify-between items-center">
                <div>
                  <span class="text-sm font-extrabold text-slate-900 dark:text-slate-100 block">Total Due:</span>
                  <span class="text-[10px] text-slate-400">Includes freight, tax &amp; settlement</span>
                </div>
                <span class="text-base font-black text-blue-600 dark:text-blue-400">
                  ETB {{ formatNumber(summaryGrandTotal) }}
                </span>
              </div>
            </div>

            <!-- Error Banner -->
            <div v-if="errorMessage" class="p-3 mb-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-500 text-xs flex items-center gap-2">
              <AlertCircle class="w-4 h-4 shrink-0" />
              <span>{{ errorMessage }}</span>
            </div>

            <!-- Submit Button -->
            <button
              type="button"
              @click="handlePlaceOrder"
              :disabled="submitting || cartItems.length === 0"
              class="w-full py-3.5 px-4 rounded-xl font-extrabold text-xs text-white bg-blue-600 hover:bg-blue-500 shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <RefreshCw v-if="submitting" class="w-4 h-4 animate-spin" />
              <Lock v-else class="w-4 h-4" />
              <span>
                {{ submitting ? 'Redirecting to Chapa Gateway...' : `Confirm Order & Pay (ETB ${formatNumber(summaryGrandTotal)})` }}
              </span>
            </button>

            <!-- Guarantee Notice -->
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-center gap-2 text-[11px] text-slate-400 text-center">
              <ShieldCheck class="w-4 h-4 text-blue-500 shrink-0" />
              <span>Protected by Chapa &amp; Verified Agro-Escrow</span>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useBuyerStore } from '@/stores/buyerStore'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { buyerAPI } from '@/api/buyer'
import { formatImageUrl } from '@/utils/helpers'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import BuyerHeaderNav from '@/components/layout/BuyerHeaderNav.vue'
import {
  getDynamicShippingOptions,
  getCartTotalWeightKg,
  cartHasPerishables,
  ETHIOPIAN_REGIONS_LOGISTICS,
  calculateOriginDestinationDistanceKm,
  resolveCartOriginAndDistance,
  ETHIOPIAN_DELIVERY_DESTINATIONS,
  resolveDestinationHubAndRegion,
  formatHierarchicalAddress,
  formatHierarchicalLocation,
  type DynamicShippingOption,
  type OriginShippingRoute
} from '@/utils/shippingCalculator'
import {
  Menu, ArrowLeft, Check, CheckCircle2, Truck, CreditCard,
  MapPin, Phone, User, Building, AlertCircle, ShoppingBag,
  Sparkles, RefreshCw, Lock, ShieldCheck, Leaf, Info
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const buyerStore = useBuyerStore()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)
const sidebarOpen = ref(false)
const submitting = ref(false)
const orderSuccess = ref(false)
const errorMessage = ref<string | null>(null)
const confirmedOrders = ref<any[]>([])

const cartItems = computed(() => buyerStore.cart || [])

const initialDestInfo = resolveDestinationHubAndRegion(
  (route.query.region as string) || 
  (typeof window !== 'undefined' ? sessionStorage.getItem('checkout_destination') : null) || 
  'Addis Ababa'
)

const addressForm = ref({
  recipient_name: '',
  phone: '',
  region: initialDestInfo.region,
  city: initialDestInfo.city,
  zone: initialDestInfo.defaultZone || 'Bole Subcity',
  woreda: 'Woreda 03',
  address: 'Commercial Agro-Distribution Depot',
  notes: ''
})

const savedAddresses = ref<any[]>([])
const selectedSavedAddressId = ref<number | null>(null)

const destinationFromCart = computed<string>(() => {
  return (route.query.region as string) || (typeof window !== 'undefined' ? sessionStorage.getItem('checkout_destination') : '') || ''
})
const selectedDestination = ref<string>(destinationFromCart.value || `${initialDestInfo.city}, ${initialDestInfo.region}`)

const resolvedDest = computed(() => resolveDestinationHubAndRegion(selectedDestination.value))
const destinationDisplayName = computed(() => resolvedDest.value.displayName)

const formattedAddressPreview = computed(() => {
  return formatHierarchicalAddress({
    street: addressForm.value.address,
    woreda: addressForm.value.woreda,
    zone: addressForm.value.zone,
    region: addressForm.value.region,
    country: 'Ethiopia'
  })
})

const totalWeightKg = computed(() => getCartTotalWeightKg(cartItems.value))
const hasPerishables = computed(() => cartHasPerishables(cartItems.value))

const shippingOptions = computed<DynamicShippingOption[]>(() => {
  return getDynamicShippingOptions(cartItems.value, selectedDestination.value)
})

const selectedShipping = ref<string>((route.query.shipping as string) || (typeof window !== 'undefined' ? sessionStorage.getItem('checkout_shipping') : null) || 'standard')

const selectedShippingOption = computed(() => {
  return (
    shippingOptions.value.find(o => o.key === selectedShipping.value) ||
    shippingOptions.value[0] || {
      key: 'standard' as const,
      title: 'Standard Agricultural Freight',
      desc: `Standard Delivery`,
      fee: 350,
      badge: 'STANDARD FLAT RATE'
    }
  )
})

const selectedShippingFee = computed(() => selectedShippingOption.value.fee)
const selectedShippingTitle = computed(() => selectedShippingOption.value.title)

// Payment Methods
const primaryPaymentMethod = ref<any>(null)
const savedPaymentMethods = ref<any[]>([])

const maskAccountNumber = (num: string | number) => {
  if (!num) return ''
  const str = String(num).trim()
  if (str.length <= 4) return str
  return '•••• ' + str.slice(-4)
}

const paymentMethodsList = computed(() => [
  { key: 'telebirr', title: 'Telebirr (Chapa)', desc: 'Instant mobile wallet payment', badge: 'POPULAR' },
  { key: 'cbe_birr', title: 'CBE Birr', desc: 'Commercial Bank of Ethiopia mobile pay', badge: null },
  { key: 'bank_transfer', title: 'Direct Bank Transfer', desc: 'Deposit to escrow bank account', badge: null },
  { key: 'cash_on_delivery', title: 'Agro-Escrow / COD', desc: 'Inspect produce upon physical delivery', badge: null }
])

const paymentForm = ref({
  method: 'telebirr',
  telebirr_phone: '',
  cbe_phone: '',
  bank_ref: ''
})

// Financial Calculations (Subtotal, Tax 16%, Grand Total)
const summarySubtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => {
    const price = Number(item.unit_price || item.price || 0)
    const qty = Number(item.quantity || 1)
    return sum + (item.subtotal ? Number(item.subtotal) : price * qty)
  }, 0)
})

// 16% Tax calculation (ከተዛማጅ የ Shopping Cart ገጽ ጋር የተመሳሰለ)
const summaryTax = computed(() => {
  return Math.round(summarySubtotal.value * 0.16)
})

const summaryDiscount = computed(() => {
  const promo = route.query.promo as string
  if (promo === 'SAVE10') {
    return Math.round(summarySubtotal.value * 0.1)
  } else if (promo === 'SAVE500') {
    return 500
  }
  return 0
})

const summaryGrandTotal = computed(() => {
  return Math.max(0, summarySubtotal.value + selectedShippingFee.value + summaryTax.value - summaryDiscount.value)
})

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  }).format(num || 0)
}

const formatPaymentName = (key: string) => {
  const match = paymentMethodsList.value.find(p => p.key === key)
  return match ? match.title : key
}

const applySavedAddress = () => {
  if (!selectedSavedAddressId.value) return
  const addr = savedAddresses.value.find(a => a.id === selectedSavedAddressId.value)
  if (addr) {
    addressForm.value.recipient_name = addr.contact_name || addr.recipient_name || authStore.user?.name || 'Verified Buyer'
    addressForm.value.phone = addr.contact_phone || addr.phone || authStore.user?.phone || '+251 911 234 567'
    addressForm.value.region = addr.region || 'Addis Ababa'
    addressForm.value.zone = addr.sub_city || addr.zone || 'Bole Subcity'
    addressForm.value.woreda = addr.woreda_kebele || addr.woreda || 'Woreda 03'
    addressForm.value.address = addr.street_address || addr.address_line || 'Commercial Agro-Distribution Depot'
  }
}

onMounted(async () => {
  themeStore.initializeTheme()

  if (!buyerStore.cart || buyerStore.cart.length === 0) {
    await buyerStore.fetchCart().catch(() => {})
  }

  if (authStore.user) {
    if (authStore.user.name || authStore.user.full_name) {
      addressForm.value.recipient_name = authStore.user.name || authStore.user.full_name
    }
    if (authStore.user.phone) {
      addressForm.value.phone = authStore.user.phone
      paymentForm.value.telebirr_phone = authStore.user.phone
      paymentForm.value.cbe_phone = authStore.user.phone
    }
  }

  try {
    const res = await buyerAPI.getDeliveryAddresses()
    const list = res.data?.data || res.data || []
    if (Array.isArray(list) && list.length > 0) {
      savedAddresses.value = list
      const defaultAddr = list.find(a => a.is_default) || list[0]
      selectedSavedAddressId.value = defaultAddr.id
      applySavedAddress()
    }
  } catch (e) {}
})

const handlePlaceOrder = async () => {
  errorMessage.value = null
  submitting.value = true

  try {
    const formattedAddress = formatHierarchicalAddress({
      street: addressForm.value.address,
      woreda: addressForm.value.woreda,
      zone: addressForm.value.zone,
      region: addressForm.value.region,
      country: 'Ethiopia'
    })

    const payload = {
      delivery_address: formattedAddress,
      delivery_region: addressForm.value.region,
      delivery_zone: addressForm.value.zone,
      delivery_woreda: addressForm.value.woreda,
      phone: addressForm.value.phone,
      delivery_fee: selectedShippingFee.value,
      shipping_type: selectedShipping.value,
      payment_method: paymentForm.value.method,
      tax_amount: summaryTax.value, // የተሰላው 16% ታክስ ወደ ዳታቤዝ ይላካል
      promo_discount: summaryDiscount.value,
      notes: addressForm.value.notes,
      payment_reference: paymentForm.value.bank_ref || undefined
    }

    const res = await buyerStore.checkout(payload)
    const orders = res.data || []
    confirmedOrders.value = orders
    const primaryOrder = Array.isArray(orders) ? orders[0] : orders
    const orderId = primaryOrder?.id

    await buyerStore.fetchCart().catch(() => {})

    if (paymentForm.value.method === 'cash_on_delivery') {
      orderSuccess.value = true
      return
    }

    const chapaPayload = {
      amount: summaryGrandTotal.value,
      order_id: orderId,
      phone_number: addressForm.value.phone || paymentForm.value.telebirr_phone || '0911000000',
      email: authStore.user?.email || 'aydenfudagne@gmail.com',
      channel: paymentForm.value.method,
      return_url: `${window.location.origin}/buyer/orders/active?order_id=${orderId || ''}&payment_status=completed`
    }

    const chapaRes = await buyerAPI.initializeChapaPayment(chapaPayload)
    const checkoutUrl = chapaRes.data?.data?.checkout_url || chapaRes.data?.checkout_url

    if (checkoutUrl) {
      window.location.href = checkoutUrl
    } else {
      orderSuccess.value = true
    }
  } catch (err: any) {
    console.error('Checkout error:', err)
    errorMessage.value = err.response?.data?.message || err.message || 'Failed to place order. Please review information and try again.'
  } finally {
    submitting.value = false
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>