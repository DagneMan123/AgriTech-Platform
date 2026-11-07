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
            to="/buyer/orders?tab=active"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-500 text-white shadow-sm transition-all transform active:scale-95 cursor-pointer"
          >
            <Truck class="w-4 h-4" />
            <span>Track in My Orders</span>
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
              <div class="relative" v-if="savedAddresses && savedAddresses.length > 0">
                <select
                  v-model="selectedSavedAddressId"
                  @change="applySavedAddress"
                  :class="['px-3 py-1.5 rounded-xl border text-xs font-semibold cursor-pointer focus:outline-none focus:border-blue-500', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-200' : 'bg-slate-50 border-slate-300 text-slate-800']"
                >
                  <option :value="null">Saved Delivery Addresses</option>
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
                <div class="mb-1.5">
                  <label :class="['block text-xs font-bold', darkMode ? 'text-slate-300' : 'text-slate-700']">
                    Delivery Destination (Hub &amp; Region)
                  </label>
                </div>

                <!-- Single Destination Display (No multi-option dropdown) -->
                <div class="relative">
                  <MapPin class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-blue-500 pointer-events-none" />
                  <input
                    type="text"
                    :value="destinationDisplayName || selectedDestination"
                    readonly
                    :class="[
                      'w-full pl-9 pr-3 py-2.5 rounded-xl border text-xs font-bold cursor-default select-none focus:outline-none transition',
                      darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-800'
                    ]"
                  />
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

            <!-- Dynamic Shipping Options Grid (Including Self-Pickup) -->
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

                  <!-- Dynamic Weight & Distance Formula Details -->
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

              <!-- Self-Pickup / Farm-Gate Option -->
              <label
                :class="[
                  'relative rounded-xl border p-4 cursor-pointer transition-all flex flex-col justify-between group',
                  selectedShipping === 'self-pickup'
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
                        value="self-pickup"
                        class="text-blue-600 focus:ring-blue-500"
                      />
                      <span class="text-xs font-bold leading-tight">Self-Pickup / Farm-Gate</span>
                    </div>
                  </div>

                  <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase mb-2 bg-blue-500/20 text-blue-500 border border-blue-500/30">
                    FREE TRANSPORT
                  </span>

                  <p :class="['text-[11px] mb-2 leading-snug', darkMode ? 'text-slate-400' : 'text-slate-500']">
                    Pick up goods directly from the farmer's origin warehouse/farm-gate using your own vehicle.
                  </p>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                  <span class="text-[10px] text-slate-400">Buyer Transport:</span>
                  <p class="text-sm font-black text-blue-600 dark:text-blue-400">
                    ETB 0
                  </p>
                </div>
              </label>
            </div>
          </section>

          <!-- SECTION 3: PAYMENT METHOD -->
          <section :class="['rounded-2xl border p-5 sm:p-6 shadow-2xs transition-all', darkMode ? 'bg-[#131b2e] border-slate-800' : 'bg-white border-slate-200']">
            <div class="flex items-center justify-between gap-2.5 mb-4 pb-3 border-b border-slate-200 dark:border-slate-800 flex-wrap">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                  <CreditCard class="w-4 h-4" />
                </div>
                <div>
                  <h2 class="text-sm font-bold tracking-tight">3. Buyer Payment Source &amp; Escrow Authorization</h2>
                  <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-500']">Funds held in neutral Chapa Escrow Custody &bull; Farmer payout is released upon warehouse acceptance</p>
                </div>
              </div>
              <router-link
                to="/buyer/payments"
                class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1"
              >
                Manage Profile Wallets &rarr;
              </router-link>
            </div>

            <!-- Escrow Holding Trust Notice -->
            <div :class="['mb-5 p-3.5 rounded-xl border flex items-start gap-3', darkMode ? 'bg-blue-950/20 border-blue-900/50 text-blue-200' : 'bg-blue-50/80 border-blue-200 text-blue-900']">
              <ShieldCheck class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" />
              <div class="text-xs leading-relaxed">
                <span class="font-bold">🔒 Chapa &amp; Agricultural Escrow Custody Protection:</span>
                Your payment routes directly into official escrow holding. Zero funds are sent directly to the vendor until produce arrives at your warehouse depot and passes quality inspection.
              </div>
            </div>

            <!-- Primary Payment Method Banner (Highlighted Active Settlement) -->
            <div
              v-if="primaryPaymentMethod"
              :class="[
                'mb-5 p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all',
                selectedSavedMethodId === primaryPaymentMethod.id
                  ? (darkMode ? 'bg-emerald-950/30 border-emerald-500/50 ring-1 ring-emerald-500/30' : 'bg-emerald-50 border-emerald-300 ring-1 ring-emerald-500/20')
                  : (darkMode ? 'bg-slate-900/60 border-slate-800' : 'bg-slate-50 border-slate-200')
              ]"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                  <Star class="w-5 h-5 fill-current text-amber-500" />
                </div>
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-slate-900 dark:text-slate-100">
                      {{ primaryPaymentMethod.bank_name }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white shadow-xs">
                      <Star class="w-2.5 h-2.5 fill-current" /> BUYER PRIMARY WALLET
                    </span>
                  </div>
                  <p :class="['text-[11px] mt-0.5', darkMode ? 'text-slate-400' : 'text-slate-600']">
                    A/C: <span class="font-mono font-bold">{{ maskAccountNumber(primaryPaymentMethod.account_number) }}</span>
                    &bull; Account Holder: <span class="font-medium">{{ primaryPaymentMethod.account_holder }}</span>
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2 self-end sm:self-auto">
                <button
                  type="button"
                  v-if="selectedSavedMethodId !== primaryPaymentMethod.id"
                  @click="selectSavedPaymentMethod(primaryPaymentMethod)"
                  class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition cursor-pointer"
                >
                  Use Primary Wallet
                </button>
                <span
                  v-else
                  class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500/15 text-emerald-500 border border-emerald-500/30 flex items-center gap-1.5"
                >
                  <CheckCircle2 class="w-3.5 h-3.5" /> Pre-Selected Funding Source
                </span>
              </div>
            </div>

            <!-- Saved Payment Methods Grid (From User Profile / Settings) -->
            <div v-if="savedPaymentMethods.length > 0" class="mb-5">
              <div class="flex items-center justify-between mb-2.5">
                <label class="text-xs font-bold flex items-center gap-1.5">
                  <span>Your Registered Funding Sources (Buyer Wallets / Accounts)</span>
                  <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-500/10 text-blue-500 border border-blue-500/20">
                    {{ savedPaymentMethods.length }} Registered
                  </span>
                </label>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                <div
                  v-for="pm in savedPaymentMethods"
                  :key="pm.id"
                  @click="selectSavedPaymentMethod(pm)"
                  :class="[
                    'p-3.5 rounded-xl border cursor-pointer transition-all flex flex-col justify-between relative',
                    selectedSavedMethodId === pm.id
                      ? (darkMode ? 'border-blue-500 bg-blue-500/15 ring-2 ring-blue-500/30' : 'border-blue-600 bg-blue-50/70 ring-2 ring-blue-600/30')
                      : (darkMode ? 'border-slate-800 bg-[#0b0f19] hover:border-slate-700' : 'border-slate-200 bg-white hover:border-slate-300')
                  ]"
                >
                  <div>
                    <div class="flex items-start justify-between gap-1 mb-1.5">
                      <span class="text-xs font-bold truncate max-w-[170px]" :title="pm.bank_name">
                        {{ pm.bank_name }}
                      </span>
                      <span
                        v-if="pm.is_primary"
                        class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-500/15 text-amber-500 border border-amber-500/30 shrink-0"
                      >
                        <Star class="w-2.5 h-2.5 fill-current" /> PRIMARY
                      </span>
                    </div>

                    <p class="text-[11px] font-mono font-bold text-slate-500 dark:text-slate-400 mb-1">
                      {{ maskAccountNumber(pm.account_number) }}
                    </p>

                    <p class="text-[10px] text-slate-400 truncate">
                      Holder: <span class="font-medium text-slate-700 dark:text-slate-300">{{ pm.account_holder }}</span>
                    </p>
                  </div>

                  <div class="flex items-center justify-between pt-2.5 mt-2 border-t border-slate-100 dark:border-slate-800 text-[10px]">
                    <span :class="selectedSavedMethodId === pm.id ? 'text-blue-500 font-bold flex items-center gap-1' : 'text-slate-400'">
                      <Check v-if="selectedSavedMethodId === pm.id" class="w-3 h-3" />
                      {{ selectedSavedMethodId === pm.id ? 'Active Funding Source' : 'Click to Select' }}
                    </span>
                    <span class="uppercase tracking-wider text-[9px] text-slate-400">
                      {{ pm.type === 'bank_transfer' ? 'Bank' : 'Mobile' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Standard Payment Options -->
            <div class="mb-5">
              <label class="block text-xs font-bold mb-2 text-slate-700 dark:text-slate-300">
                Or Select Payment Channel:
              </label>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label
                  v-for="pm in paymentMethodsList"
                  :key="pm.key"
                  @click="selectStandardPaymentMethod(pm.key)"
                  :class="[
                    'relative rounded-xl border p-4 cursor-pointer transition-all flex items-start gap-3',
                    paymentForm.method === pm.key && !selectedSavedMethodId
                      ? (darkMode ? 'border-blue-500 bg-blue-500/10 ring-2 ring-blue-500/30' : 'border-blue-600 bg-blue-50/50 ring-2 ring-blue-600/20')
                      : (darkMode ? 'border-slate-800 bg-[#0b0f19] hover:border-slate-700' : 'border-slate-200 bg-white hover:border-slate-300')
                  ]"
                >
                  <input
                    type="radio"
                    :checked="paymentForm.method === pm.key && !selectedSavedMethodId"
                    @change="selectStandardPaymentMethod(pm.key)"
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
            </div>

            <!-- Dynamic Payment Details Panel -->
            <div :class="['rounded-xl p-4 border transition-all text-xs', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div v-if="paymentForm.method === 'telebirr'" class="space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2">
                  <div class="flex items-center gap-2 text-blue-600 font-bold">
                    <Smartphone class="w-4 h-4" />
                    <span>Telebirr Mobile Payment (Chapa Escrow)</span>
                  </div>
                  <span v-if="selectedSavedMethod" class="text-[11px] text-emerald-500 font-bold flex items-center gap-1">
                    <CheckCircle2 class="w-3.5 h-3.5" /> Using Buyer Wallet: {{ selectedSavedMethod.bank_name }}
                  </span>
                </div>
                <div class="max-w-xs">
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Buyer's Telebirr Number (To Receive Chapa PIN Prompt):</label>
                  <input
                    v-model="paymentForm.telebirr_phone"
                    type="text"
                    placeholder="09... or 07..."
                    :class="['w-full px-3 py-2 rounded-xl border text-xs focus:outline-none focus:border-blue-500', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                  />
                  <span class="text-[10px] text-slate-400 mt-1 block">A secure USSD push prompt will be sent to your phone to authorize transfer to Escrow.</span>
                </div>
              </div>
              <div v-else-if="paymentForm.method === 'cbe_birr'" class="space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2">
                  <div class="flex items-center gap-2 text-blue-500 font-bold">
                    <Building class="w-4 h-4" />
                    <span>CBE Birr Mobile Money (Chapa Escrow)</span>
                  </div>
                  <span v-if="selectedSavedMethod" class="text-[11px] text-emerald-500 font-bold flex items-center gap-1">
                    <CheckCircle2 class="w-3.5 h-3.5" /> Using Buyer Account: {{ selectedSavedMethod.bank_name }}
                  </span>
                </div>
                <div class="max-w-xs">
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Buyer's CBE Birr Phone Number:</label>
                  <input
                    v-model="paymentForm.cbe_phone"
                    type="text"
                    placeholder="09... or 07..."
                    :class="['w-full px-3 py-2 rounded-xl border text-xs focus:outline-none focus:border-blue-500', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                  />
                </div>
              </div>
              <div v-else-if="paymentForm.method === 'bank_transfer'" class="space-y-3">
                <div class="flex items-center justify-between flex-wrap gap-2">
                  <div class="flex items-center gap-2 text-blue-500 font-bold">
                    <Building class="w-4 h-4" />
                    <span>Direct Bank Transfer &bull; Chapa &amp; Agricultural Escrow Custody</span>
                  </div>
                  <span v-if="selectedSavedMethod" class="text-[11px] text-emerald-500 font-bold flex items-center gap-1">
                    <CheckCircle2 class="w-3.5 h-3.5" /> Using Buyer A/C: {{ selectedSavedMethod.bank_name }}
                  </span>
                </div>

                <!-- Escrow Depository Bank Details -->
                <div :class="['p-3 rounded-xl border text-[11px] space-y-1.5', darkMode ? 'bg-slate-900 border-slate-700 text-slate-300' : 'bg-white border-slate-300 text-slate-700']">
                  <div class="font-bold text-slate-900 dark:text-slate-100 flex items-center justify-between">
                    <span>AgriConnect Escrow Custody Account</span>
                    <span class="text-[10px] text-emerald-500 font-extrabold uppercase">Official Depository</span>
                  </div>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[10px] font-mono">
                    <div>Bank: <span class="font-bold">Commercial Bank of Ethiopia (CBE)</span></div>
                    <div>Account No: <span class="font-bold text-blue-600 dark:text-blue-400">1000 4829 10482</span></div>
                    <div>Beneficiary: <span class="font-bold">AgriConnect Escrow Custody (Chapa Partner)</span></div>
                    <div>Branch: <span class="font-bold">Addis Ababa FinTech Center</span></div>
                  </div>
                  <p class="text-[10px] text-slate-400 italic pt-1">
                    Transfer the total amount to this escrow account. Funds will be held in custody until delivery is confirmed.
                  </p>
                </div>

                <div class="max-w-md">
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Buyer's Bank Deposit / Transfer Reference Number:</label>
                  <input
                    v-model="paymentForm.bank_ref"
                    type="text"
                    placeholder="e.g. FT26301ABC... or bank reference"
                    :class="['w-full px-3 py-2 rounded-xl border text-xs focus:outline-none focus:border-blue-500', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                  />
                </div>
              </div>
              <div v-else-if="paymentForm.method === 'card'" class="space-y-3">
                <div class="flex items-center gap-2 text-blue-500 font-bold">
                  <CreditCard class="w-4 h-4" />
                  <span>Debit / Credit Card (Chapa Secured Gateway)</span>
                </div>
                <p class="text-[11px] text-slate-400">
                  You will be securely redirected to the Chapa encrypted payment portal to authorize payment with your Visa, Mastercard, or local Ethiopian debit card. Funds are held in escrow custody.
                </p>
              </div>
              <div v-else class="space-y-2">
                <div class="flex items-center gap-2 text-amber-500 font-bold">
                  <ShieldCheck class="w-4 h-4" />
                  <span>Agro-Escrow / Cash on Delivery</span>
                </div>
                <p class="text-[11px] text-slate-400">
                  Produce will be staged and delivered to your designated warehouse. Payment is finalized upon physical quality inspection.
                </p>
              </div>
            </div>
          </section>
        </div>

        <!-- RIGHT COLUMN: Order Summary & Pay Action -->
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
                  <div class="flex items-center justify-between gap-1">
                    <h4 :class="['text-xs font-bold truncate', darkMode ? 'text-slate-200' : 'text-slate-800']">
                      {{ item.product_name }}
                    </h4>
                    <button
                      type="button"
                      @click="handleRemoveItem(item.id)"
                      :disabled="updatingCartItemId === item.id"
                      class="text-slate-400 hover:text-red-500 transition p-1 cursor-pointer disabled:opacity-40"
                    >
                      <Trash2 class="w-3 h-3" />
                    </button>
                  </div>

                  <div class="flex items-center gap-2 text-[10px] mb-1.5 flex-wrap">
                    <span :class="darkMode ? 'text-slate-400' : 'text-slate-500'">
                      ETB {{ formatNumber(item.unit_price || item.price || 0) }} / {{ item.unit || 'kg' }}
                    </span>
                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[9px] font-semibold border border-emerald-500/20">
                      📍 Origin: {{ item.origin_location || item.product?.origin_location || 'Adama' }}
                    </span>
                  </div>

                  <div class="flex items-center gap-2">
                    <div :class="['flex items-center border rounded-lg overflow-hidden', darkMode ? 'border-slate-700 bg-[#0b0f19]' : 'border-slate-200 bg-white']">
                      <button
                        type="button"
                        @click="handleDecrementQuantity(item)"
                        :disabled="updatingCartItemId === item.id || item.quantity <= 1"
                        class="px-2 py-0.5 text-slate-400 hover:text-slate-200 transition disabled:opacity-30 cursor-pointer"
                      >
                        <Minus class="w-2.5 h-2.5" />
                      </button>
                      <input
                        type="number"
                        min="1"
                        :value="item.quantity"
                        @change="handleQuantityChange(item, $event)"
                        :disabled="updatingCartItemId === item.id"
                        class="w-10 text-center text-[11px] font-bold bg-transparent border-0 focus:ring-0 p-0 text-slate-800 dark:text-slate-100"
                      />
                      <button
                        type="button"
                        @click="handleIncrementQuantity(item)"
                        :disabled="updatingCartItemId === item.id"
                        class="px-2 py-0.5 text-slate-400 hover:text-slate-200 transition disabled:opacity-30 cursor-pointer"
                      >
                        <Plus class="w-2.5 h-2.5" />
                      </button>
                    </div>
                    <span class="text-[10px] text-slate-400">
                      {{ formatNumber(getItemWeightKg(item)) }} kg
                    </span>
                  </div>
                </div>

                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 shrink-0 self-center">
                  ETB {{ formatNumber(item.subtotal || ((item.unit_price || item.price) * item.quantity)) }}
                </span>
              </div>
            </div>

            <!-- Financial Calculations (Clean Summary) -->
            <div :class="['rounded-xl p-3 mb-5 space-y-2.5 border text-xs', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex justify-between items-center text-slate-500 dark:text-slate-400">
                <span>Produce Subtotal:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">ETB {{ formatNumber(summarySubtotal) }}</span>
              </div>

              <!-- Shipping Row -->
              <div class="flex justify-between items-center text-slate-500 dark:text-slate-400">
                <span class="truncate pr-2">Shipping ({{ selectedShipping === 'self-pickup' ? 'Self-Pickup / Farm-Gate' : selectedShippingTitle }}):</span>
                <span class="font-bold text-blue-600 dark:text-blue-400 shrink-0">
                  {{ selectedShipping === 'self-pickup' ? 'ETB 0 (Free)' : 'ETB ' + formatNumber(selectedShippingFee) }}
                </span>
              </div>

              <!-- Destination Hub & Region (Keeps original destination even when self-pickup is selected) -->
              <div class="flex items-center justify-between text-[11px] pt-1 pb-0.5 border-t border-slate-100 dark:border-slate-800/60">
                <span class="text-slate-500 dark:text-slate-400 font-medium">Destination:</span>
                <span class="font-bold text-blue-600 dark:text-blue-400">{{ selectedDestination }}</span>
              </div>

              <!-- Multi-Origin Breakdown (Hidden completely when Self-Pickup is selected) -->
              <div v-if="selectedShipping !== 'self-pickup' && selectedShippingRoutes.length > 0" class="space-y-1.5 pt-1 border-t border-slate-100 dark:border-slate-800/60">
                <div v-if="selectedShippingRoutes.length > 1" class="flex items-center justify-between text-[11px] font-bold text-blue-600 dark:text-blue-400 pb-1">
                  <span>Multi-Origin Split Routes ({{ selectedShippingRoutes.length }} origins):</span>
                  <span class="text-[10px] font-mono text-slate-400">{{ selectedShippingRoutes.length }} Shipments</span>
                </div>

                <div
                  v-for="(route, idx) in selectedShippingRoutes"
                  :key="route.origin + '-' + idx"
                  class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 text-[11px] space-y-1"
                >
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800 dark:text-slate-100">
                      📍 {{ route.origin }} → <span class="text-blue-600 dark:text-blue-400">{{ route.destination }}</span>
                    </span>
                    <span class="font-bold text-blue-600 dark:text-blue-400">
                      ETB {{ formatNumber(route.fee) }}
                    </span>
                  </div>

                  <div class="flex items-center justify-between text-[10px] text-slate-400">
                    <span>~{{ route.distanceKm }} km • {{ formatNumber(route.weightKg) }} kg ({{ route.itemCount }} item{{ route.itemCount > 1 ? 's' : '' }})</span>
                    <span v-if="route.isFlatRate" class="text-emerald-500 font-semibold">Flat Rate (≤ 5 kg)</span>
                    <span v-else class="text-blue-500 font-semibold">Bulk Matrix</span>
                  </div>

                  <div
                    v-if="!route.isFlatRate && route.formulaStr"
                    class="text-[9px] text-blue-600 dark:text-blue-400 font-mono bg-blue-500/5 px-1.5 py-0.5 rounded border border-blue-500/10 leading-tight"
                  >
                    {{ route.formulaStr }}
                  </div>
                </div>
              </div>

              <div class="flex justify-between items-center pt-2 border-t border-slate-100 dark:border-slate-800/60 text-slate-500 dark:text-slate-400">
                <span>Platform Escrow Fee:</span>
                <span class="font-bold text-blue-500">FREE</span>
              </div>

              <div v-if="summaryDiscount > 0" class="flex justify-between items-center text-blue-500">
                <span>Promo Discount:</span>
                <span class="font-bold">-ETB {{ formatNumber(summaryDiscount) }}</span>
              </div>

              <!-- Payment Settlement Channel Row -->
              <div class="flex justify-between items-center text-slate-500 dark:text-slate-400 text-xs pt-2 border-t border-slate-100 dark:border-slate-800/60">
                <span>Buyer Funding Source:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 text-right">
                  <span class="truncate max-w-[150px]" :title="currentSelectedPaymentTitle">{{ currentSelectedPaymentTitle }}</span>
                  <span v-if="selectedSavedMethod?.is_primary" class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 shrink-0">PRIMARY</span>
                </span>
              </div>

              <!-- Escrow Custody Holding Row -->
              <div class="flex justify-between items-center text-[11px] pt-1 text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1">
                  <ShieldCheck class="w-3.5 h-3.5 text-emerald-500" />
                  <span>Escrow Custody:</span>
                </span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">Chapa Agro-Custody</span>
              </div>

              <div class="flex justify-between items-center text-[10px] pb-1 text-slate-400">
                <span>Farmer Payout Release:</span>
                <span class="font-medium text-slate-600 dark:text-slate-300">Upon Warehouse Inspection</span>
              </div>

              <!-- Grand Total -->
              <div class="border-t border-slate-200 dark:border-slate-800 pt-2 flex justify-between items-center">
                <div>
                  <span class="text-sm font-extrabold text-slate-900 dark:text-slate-100 block">Total Due:</span>
                  <span class="text-[10px] text-slate-400">Locked in neutral escrow</span>
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
                {{ submitting ? submittingMessage : `Confirm Order & Pay (ETB ${formatNumber(summaryGrandTotal)})` }}
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
  getItemWeightKg,
 
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
  Sparkles, RefreshCw, Lock, ShieldCheck, Leaf,
  Plus, Minus, Trash2, Info, ChevronDown,
  Star, Smartphone
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const buyerStore = useBuyerStore()
const authStore = useAuthStore()
const themeStore = useThemeStore()

const darkMode = computed(() => themeStore.isDark)
const sidebarOpen = ref(false)
const submitting = ref(false)
const submittingMessage = ref('Processing Secure Escrow Payment...')
const orderSuccess = ref(false)
const errorMessage = ref<string | null>(null)
const confirmedOrders = ref<any[]>([])
const updatingCartItemId = ref<number | null>(null)

const cartItems = computed(() => buyerStore.cart || [])
const deliveryDestinations = ETHIOPIAN_DELIVERY_DESTINATIONS

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

const destinationFromCart = computed<string>({
  get() {
    return (route.query.region as string) || (typeof window !== 'undefined' ? sessionStorage.getItem('checkout_destination') : '') || ''
  },
  set(val) {
    if (typeof window !== 'undefined') {
      sessionStorage.setItem('checkout_destination', val)
    }
  }
})
const selectedDestination = ref<string>(destinationFromCart.value || `${initialDestInfo.city}, ${initialDestInfo.region}`)

const resolvedDest = computed(() => resolveDestinationHubAndRegion(selectedDestination.value))
const destinationDisplayName = computed(() => resolvedDest.value.displayName)

// Hybrid input mode: toggles between dropdown (pre-existing options) and text field (custom address)
const destinationInputMode = ref<'dropdown' | 'text'>('dropdown')
const customDestinationText = ref<string>('')
const lastSelectedHubDestination = ref<string>(selectedDestination.value)

const isSetInCart = computed(() => {
  if (!destinationFromCart.value) return false
  const cartDest = resolveDestinationHubAndRegion(destinationFromCart.value).displayName
  return destinationDisplayName.value === cartDest
})

const toggleDestinationMode = () => {
  if (destinationInputMode.value === 'dropdown') {
    destinationInputMode.value = 'text'
    if (!customDestinationText.value) {
      customDestinationText.value = selectedDestination.value
    }
  } else {
    switchToDropdown()
  }
}

const switchToDropdown = () => {
  destinationInputMode.value = 'dropdown'
  if (lastSelectedHubDestination.value && lastSelectedHubDestination.value !== '__custom__') {
    selectedDestination.value = lastSelectedHubDestination.value
  } else {
    selectedDestination.value = `${initialDestInfo.city}, ${initialDestInfo.region}`
  }
}

const handleDestinationDropdownChange = () => {
  if (selectedDestination.value === '__custom__') {
    destinationInputMode.value = 'text'
    customDestinationText.value = lastSelectedHubDestination.value || ''
  } else {
    lastSelectedHubDestination.value = selectedDestination.value
  }
}

const handleCustomDestinationInput = () => {
  if (customDestinationText.value.trim()) {
    selectedDestination.value = customDestinationText.value.trim()
    const parts = customDestinationText.value.split(',')
    addressForm.value.city = parts[0]?.trim() || customDestinationText.value.trim()
    if (parts[1]?.trim()) {
      addressForm.value.region = parts[1].trim()
    }
  }
}

const formattedAddressPreview = computed(() => {
  return formatHierarchicalAddress({
    street: addressForm.value.address,
    woreda: addressForm.value.woreda,
    zone: addressForm.value.zone,
    region: addressForm.value.region,
    country: 'Ethiopia'
  })
})

watch(selectedDestination, (newDest) => {
  if (newDest) {
    const res = resolveDestinationHubAndRegion(newDest)
    if (addressForm.value.region !== res.region) {
      addressForm.value.region = res.region
    }
  }
})

const totalWeightKg = computed(() => getCartTotalWeightKg(cartItems.value))
const hasPerishables = computed(() => cartHasPerishables(cartItems.value))
const cartOriginInfo = computed(() => resolveCartOriginAndDistance(cartItems.value, selectedDestination.value))
const selectedOrigin = computed(() => cartOriginInfo.value.origin || 'Adama')
const selectedDistanceKm = computed(() => calculateOriginDestinationDistanceKm(selectedOrigin.value, selectedDestination.value))

const shippingOptions = computed<DynamicShippingOption[]>(() => {
  return getDynamicShippingOptions(cartItems.value, selectedDestination.value)
})

const selectedShippingRoutes = computed<OriginShippingRoute[]>(() => {
  if (selectedShipping.value === 'self-pickup') return []
  return selectedShippingOption.value.routes || []
})

const selectedShipping = ref<string>((route.query.shipping as string) || (typeof window !== 'undefined' ? sessionStorage.getItem('checkout_shipping') : null) || 'standard')

const selectedShippingOption = computed(() => {
  if (selectedShipping.value === 'self-pickup') {
    return {
      key: 'self-pickup',
      title: 'Self-Pickup / Farm-Gate',
      desc: 'Buyer transport from origin warehouse',
      fee: 0,
      badge: 'FREE',
      isPerishableOnly: false,
      transitDays: 'Immediate',
      estimatedDays: 'Immediate',
      routes: [],
      breakdown: null
    }
  }
  return (
    shippingOptions.value.find(o => o.key === selectedShipping.value) ||
    shippingOptions.value[0] || {
      key: 'standard' as const,
      title: 'Standard Agricultural Freight',
      desc: `${selectedOrigin.value} → ${selectedDestination.value}`,
      fee: 350,
      badge: 'STANDARD FLAT RATE',
      isPerishableOnly: false,
      transitDays: '3 - 5 days',
      estimatedDays: '3 - 5 days',
      routes: [],
      breakdown: null
    }
  )
})

const selectedShippingFee = computed(() => selectedShipping.value === 'self-pickup' ? 0 : selectedShippingOption.value.fee)
const selectedShippingTitle = computed(() => selectedShippingOption.value.title)

const handleIncrementQuantity = async (item: any) => {
  if (updatingCartItemId.value === item.id) return
  updatingCartItemId.value = item.id
  try {
    await buyerStore.updateCartItem(item.id, { quantity: (Number(item.quantity) || 1) + 1 })
  } finally {
    updatingCartItemId.value = null
  }
}

const handleDecrementQuantity = async (item: any) => {
  if (updatingCartItemId.value === item.id || (Number(item.quantity) || 1) <= 1) return
  updatingCartItemId.value = item.id
  try {
    await buyerStore.updateCartItem(item.id, { quantity: (Number(item.quantity) || 1) - 1 })
  } finally {
    updatingCartItemId.value = null
  }
}

const handleQuantityChange = async (item: any, event: any) => {
  if (updatingCartItemId.value === item.id) return
  const inputVal = parseInt(event.target.value)
  const newQty = isNaN(inputVal) || inputVal < 1 ? 1 : inputVal
  updatingCartItemId.value = item.id
  try {
    await buyerStore.updateCartItem(item.id, { quantity: newQty })
  } finally {
    updatingCartItemId.value = null
  }
}

const handleRemoveItem = async (itemId: number) => {
  if (updatingCartItemId.value === itemId) return
  updatingCartItemId.value = itemId
  try {
    await buyerStore.removeFromCart(itemId)
  } finally {
    updatingCartItemId.value = null
  }
}

const primaryPaymentMethod = ref<any>(null)
const savedPaymentMethods = ref<any[]>([])
const loadingPaymentMethods = ref(false)
const selectedSavedMethodId = ref<number | string | null>(null)

const maskAccountNumber = (num: string | number) => {
  if (!num) return ''
  const str = String(num).trim()
  return str.length <= 4 ? str : '•••• ' + str.slice(-4)
}

const selectSavedPaymentMethod = (method: any) => {
  if (!method) return
  selectedSavedMethodId.value = method.id

  const mType = (method.type || '').toLowerCase()
  const bName = (method.bank_name || '').toLowerCase()

  if (mType === 'bank_transfer' || bName.includes('commercial bank') || bName.includes('cbe') || bName.includes('bank')) {
    paymentForm.value.method = 'bank_transfer'
    paymentForm.value.bank_ref = method.account_number || ''
  } else if (mType === 'mobile_money' && (bName.includes('cbe birr') || bName.includes('cbe-birr'))) {
    paymentForm.value.method = 'cbe_birr'
    paymentForm.value.cbe_phone = method.account_number || ''
  } else if (mType === 'mobile_money' || bName.includes('telebirr') || bName.includes('chapa')) {
    paymentForm.value.method = 'telebirr'
    paymentForm.value.telebirr_phone = method.account_number || ''
  } else if (mType === 'card' || bName.includes('card')) {
    paymentForm.value.method = 'card'
  } else {
    paymentForm.value.method = 'bank_transfer'
    paymentForm.value.bank_ref = method.account_number || ''
  }
}

const selectStandardPaymentMethod = (key: string) => {
  paymentForm.value.method = key
  // Check if standard key corresponds to one of the saved methods
  const matched = savedPaymentMethods.value.find((m: any) => {
    const mType = (m.type || '').toLowerCase()
    const bName = (m.bank_name || '').toLowerCase()
    if (key === 'bank_transfer' && (mType === 'bank_transfer' || bName.includes('bank'))) return true
    if (key === 'telebirr' && (bName.includes('telebirr') || bName.includes('chapa'))) return true
    if (key === 'cbe_birr' && bName.includes('cbe')) return true
    if (key === 'card' && (mType === 'card' || bName.includes('card'))) return true
    return false
  })
  if (matched) {
    selectedSavedMethodId.value = matched.id
  } else {
    selectedSavedMethodId.value = null
  }
}

const selectedSavedMethod = computed(() => {
  if (!selectedSavedMethodId.value) return null
  return savedPaymentMethods.value.find((m: any) => m.id === selectedSavedMethodId.value) || null
})

const currentSelectedPaymentTitle = computed(() => {
  if (selectedSavedMethod.value) {
    return `${selectedSavedMethod.value.bank_name} (${maskAccountNumber(selectedSavedMethod.value.account_number)})`
  }
  const match = paymentMethodsList.value.find(p => p.key === paymentForm.value.method)
  return match ? match.title : paymentForm.value.method
})

const paymentMethodsList = computed(() => [
  { key: 'telebirr', title: 'Telebirr (Chapa)', desc: 'Instant mobile wallet payment', badge: 'POPULAR' },
  { key: 'cbe_birr', title: 'CBE Birr', desc: 'Commercial Bank of Ethiopia mobile pay', badge: null },
  { key: 'card', title: 'Debit / Credit Card', desc: 'Visa, Mastercard & Ethiopian bank cards', badge: 'SECURE' },
  { key: 'bank_transfer', title: 'Direct Bank Transfer', desc: 'Deposit to escrow bank account', badge: null },
  { key: 'cash_on_delivery', title: 'Agro-Escrow / COD', desc: 'Inspect produce upon physical delivery', badge: null }
])

const paymentForm = ref({
  method: 'bank_transfer',
  telebirr_phone: '',
  cbe_phone: '',
  bank_ref: ''
})

// Financial Calculations (Tax Fully Excluded - 0% Tax)
const summarySubtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => {
    const price = Number(item.unit_price || item.price || 0)
    const qty = Number(item.quantity || 1)
    return sum + (item.subtotal ? Number(item.subtotal) : price * qty)
  }, 0)
})

const summaryDiscount = computed(() => {
  const promo = route.query.promo as string
  if (promo === 'SAVE10') return Math.round(summarySubtotal.value * 0.1)
  if (promo === 'SAVE500') return 500
  return 0
})

const summaryGrandTotal = computed(() => {
  return Math.max(0, summarySubtotal.value + selectedShippingFee.value - summaryDiscount.value)
})

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num || 0)
}

const formatPaymentName = (key: string) => {
  const match = paymentMethodsList.value.find(p => p.key === key)
  return match ? match.title : key
}

const applySavedAddress = () => {
  if (!selectedSavedAddressId.value) return
  const addr = savedAddresses.value.find(a => a.id === selectedSavedAddressId.value)
  if (addr) {
    addressForm.value.recipient_name = addr.contact_name || addr.recipient_name || authStore.user?.name || ''
    addressForm.value.phone = addr.contact_phone || addr.phone || authStore.user?.phone || ''
    addressForm.value.region = addr.region || ''
    addressForm.value.zone = addr.sub_city || addr.zone || ''
    addressForm.value.woreda = addr.woreda_kebele || addr.woreda || ''
    addressForm.value.address = addr.street_address || addr.address_line || addr.address || ''
  }
}

onMounted(async () => {
  themeStore.initializeTheme()
  if (!buyerStore.cart || buyerStore.cart.length === 0) {
    await buyerStore.fetchCart().catch(() => {})
  }
  
  // Fetch saved addresses from database API securely without store error
  try {
    const res = await buyerAPI.getDeliveryAddresses()
    const list = res?.data?.data || res?.data || res || []
    if (Array.isArray(list)) {
      savedAddresses.value = list
    }
  } catch (e) {
    savedAddresses.value = []
  }

  // Fetch saved payment methods from database and auto-select PRIMARY
  loadingPaymentMethods.value = true
  try {
    const pRes = await buyerAPI.getPaymentMethods()
    const pList = pRes?.data?.data || pRes?.data || []
    if (Array.isArray(pList) && pList.length > 0) {
      savedPaymentMethods.value = pList
      const primary = pList.find((m: any) => m.is_primary) || pList[0]
      if (primary) {
        primaryPaymentMethod.value = primary
        selectSavedPaymentMethod(primary)
      }
    }
  } catch (e) {
    console.warn('Failed to load profile payment methods:', e)
  } finally {
    loadingPaymentMethods.value = false
  }
})

const handlePlaceOrder = async () => {
  errorMessage.value = null
  submitting.value = true
  submittingMessage.value = 'Processing Secure Escrow Payment...'

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
      phone: addressForm.value.phone || authStore.user?.phone || '0934318549',
      delivery_fee: selectedShippingFee.value,
      shipping_type: selectedShipping.value,
      payment_method: paymentForm.value.method,
      payment_reference: selectedSavedMethod.value
        ? `${selectedSavedMethod.value.bank_name} - ${selectedSavedMethod.value.account_number}`
        : (paymentForm.value.bank_ref || 'CHAPA-' + Date.now()),
      payment_method_id: selectedSavedMethodId.value || null,
      tax_amount: 0, // Tax-free agricultural produce
      promo_discount: summaryDiscount.value,
      notes: addressForm.value.notes
    }

    submittingMessage.value = 'Connecting to Chapa Escrow Gateway...'
    const res = await buyerStore.checkout(payload)
    confirmedOrders.value = res.data || res.orders || []

    // If Chapa hosted checkout URL was provided by server, seamlessly forward to Chapa gateway
    const chapaCheckoutUrl = res?.checkout_url || res?.data?.checkout_url
    if (chapaCheckoutUrl) {
      submittingMessage.value = 'Redirecting to Official Chapa Payment...'
      window.location.href = chapaCheckoutUrl
      return
    }

    // Client-side fallback initialization if backend didn't return checkout_url directly
    const isDigitalChapa = ['telebirr', 'cbe_birr', 'card', 'chapa', 'bank_transfer'].includes(paymentForm.value.method)
    if (isDigitalChapa && confirmedOrders.value.length > 0) {
      try {
        submittingMessage.value = 'Initializing Chapa Escrow Session...'
        const orderList = Array.isArray(confirmedOrders.value) ? confirmedOrders.value : [confirmedOrders.value]
        const orderIds = orderList.map((o: any) => o.id)
        const initRes = await buyerAPI.initializeChapaPayment({
          order_ids: orderIds,
          amount: summaryGrandTotal.value,
          channel: paymentForm.value.method,
          phone_number: addressForm.value.phone || authStore.user?.phone || '0934318549',
          email: authStore.user?.email || 'aydenfudagne@gmail.com',
          return_url: `${window.location.origin}/buyer/orders?tab=active&payment_success=true&escrow_locked=true`
        })
        const directUrl = initRes.data?.data?.checkout_url || initRes.data?.checkout_url
        if (directUrl) {
          submittingMessage.value = 'Redirecting to Official Chapa Payment...'
          window.location.href = directUrl
          return
        }
      } catch (chapaErr) {
        console.warn('Fallback Chapa init warning:', chapaErr)
      }
    }

    orderSuccess.value = true
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || err.message || 'Failed to place order.'
  } finally {
    submitting.value = false
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>