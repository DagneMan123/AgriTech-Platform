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
            <p class="font-semibold text-slate-300 dark:text-slate-200 mt-0.5 truncate" :title="addressForm.address">
              {{ addressForm.woreda }}, {{ addressForm.region }}
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
                    {{ addr.title || addr.label || addr.street_address || 'Saved Address' }} ({{ addr.sub_city || addr.city || addr.region }})
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
                  Region *
                </label>
                <div
                  :class="['w-full px-3 py-2.5 rounded-xl border text-xs font-semibold flex items-center justify-between', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-100' : 'bg-slate-50 border-slate-300 text-slate-900']"
                >
                  <span>{{ selectedDestination }}</span>
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

              <!-- Real-time Metrics Pill -->
              <div class="flex items-center gap-2 flex-wrap">
                <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-300' : 'bg-slate-50 border-slate-200 text-slate-700']">
                  <Scale class="w-3.5 h-3.5 text-blue-500" />
                  <span>Cargo Weight: <strong>{{ formatNumber(totalWeightKg) }} kg</strong></span>
                </span>
                <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border', darkMode ? 'bg-[#0b0f19] border-slate-700 text-slate-300' : 'bg-slate-50 border-slate-200 text-slate-700']">
                  <Compass class="w-3.5 h-3.5 text-blue-500" />
                  <span>{{ selectedOrigin }} → {{ selectedDestination }}: ~{{ selectedDistanceKm }} km</span>
                </span>
              </div>
            </div>

            
            <!-- Route Header -->
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 rounded-xl border text-xs" :class="darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200'">
              <div class="flex items-center gap-2">
                <span class="text-slate-500 dark:text-slate-400 font-medium">Delivery Destination:</span>
                <strong class="text-blue-600 dark:text-blue-400 font-bold">{{ selectedDestination }}</strong>
                <span class="text-[10px] text-slate-400 font-normal">(Set in Cart)</span>
              </div>
              <span class="text-[11px] text-slate-400">
                Transit Distance: <strong class="text-blue-500">{{ selectedDistanceKm }} km</strong> from {{ selectedOrigin }}
              </span>
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

                  <!-- Dynamic Weight & Distance Formula Details -->
                  <div
                    v-if="opt.breakdown && !opt.breakdown.isFlatRate"
                    class="p-2 rounded-lg bg-blue-500/5 border border-blue-500/10 mb-3 text-[10px] text-blue-600 dark:text-blue-400 font-mono leading-tight"
                  >
                    {{ opt.breakdown.formulaStr }}
                  </div>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                  <span class="text-[10px] text-slate-400">Dynamic Fee:</span>
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

            <!-- Primary Payment Method Banner (Auto-populated from PostgreSQL) -->
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
              <!-- Telebirr details -->
              <div v-if="paymentForm.method === 'telebirr'" class="space-y-3">
                <div class="flex items-center gap-2 text-blue-600 font-bold">
                  <Phone class="w-4 h-4" />
                  <span>Telebirr Instant Payment</span>
                </div>
                <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-600']">
                  Enter your Telebirr registered phone number. A USSD or payment confirmation will be dispatched upon clicking 'Confirm Order &amp; Pay'.
                </p>
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

              <!-- CBE Birr details -->
              <div v-else-if="paymentForm.method === 'cbe_birr'" class="space-y-3">
                <div class="flex items-center gap-2 text-blue-500 font-bold">
                  <Building class="w-4 h-4" />
                  <span>CBE Birr Mobile Money</span>
                </div>
                <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-600']">
                  Provide your CBE Birr mobile number linked to Commercial Bank of Ethiopia.
                </p>
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

              <!-- Bank Transfer details -->
              <div v-else-if="paymentForm.method === 'bank_transfer'" class="space-y-3">
                <div class="flex items-center gap-2 text-blue-500 font-bold">
                  <Building class="w-4 h-4" />
                  <span>
                    {{ primaryPaymentMethod?.type === 'bank_transfer' ? `${primaryPaymentMethod.bank_name} Direct Transfer (Primary Method)` : 'Commercial Bank of Ethiopia (CBE) Direct Transfer' }}
                  </span>
                </div>
                <div :class="['p-3 rounded-lg border font-mono text-[11px] space-y-1', darkMode ? 'bg-slate-900 border-slate-800 text-slate-300' : 'bg-white border-slate-200 text-slate-700']">
                  <p><strong>Bank:</strong> {{ primaryPaymentMethod?.type === 'bank_transfer' ? primaryPaymentMethod.bank_name : 'Commercial Bank of Ethiopia' }}</p>
                  <p><strong>Account Name:</strong> {{ primaryPaymentMethod?.type === 'bank_transfer' ? primaryPaymentMethod.account_holder : 'AgriTech Ethiopia Escrow' }}</p>
                  <p><strong>Account Number:</strong> {{ primaryPaymentMethod?.type === 'bank_transfer' ? primaryPaymentMethod.account_number : '1000 4892 1093 4' }}</p>
                </div>
                <div class="max-w-xs">
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Transfer Transaction Reference:</label>
                  <input
                    v-model="paymentForm.bank_ref"
                    type="text"
                    :placeholder="primaryPaymentMethod?.type === 'bank_transfer' ? `Ref / A/C for ${primaryPaymentMethod.bank_name}...` : 'e.g. FT26301ABC...'"
                    :class="['w-full px-3 py-2 rounded-xl border text-xs focus:outline-none focus:border-blue-500', darkMode ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-300 text-slate-900']"
                  />
                </div>
              </div>

              <!-- Card details -->
              <div v-else-if="paymentForm.method === 'card'" class="space-y-3">
                <div class="flex items-center gap-2 text-blue-500 font-bold">
                  <CreditCard class="w-4 h-4" />
                  <span>Debit / Credit Card (Chapa Secured)</span>
                </div>
                <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-600']">
                  Payments are encrypted via 256-bit SSL Chapa Gateway. Cardholder name and phone will be verified.
                </p>
              </div>

              <!-- Cash on Delivery -->
              <div v-else class="space-y-2">
                <div class="flex items-center gap-2 text-amber-500 font-bold">
                  <ShieldCheck class="w-4 h-4" />
                  <span>Agro-Escrow / Cash on Delivery</span>
                </div>
                <p :class="['text-[11px]', darkMode ? 'text-slate-400' : 'text-slate-600']">
                  Inspect your fresh agricultural produce upon dispatch delivery before payment is collected and verified by the transport agent.
                </p>
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

            <!-- Items Mini List with Real-Time Quantity Modifiers -->
            <div class="max-h-64 overflow-y-auto space-y-3 pr-1 mb-4 divide-y divide-slate-100 dark:divide-slate-800/60">
              <div
                v-for="item in cartItems"
                :key="item.id"
                class="pt-3 first:pt-0 flex items-center gap-3"
              >
                <!-- Image or Leaf Placeholder -->
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
                    <!-- Inline Remove Button -->
                    <button
                      type="button"
                      @click="handleRemoveItem(item.id)"
                      :disabled="updatingCartItemId === item.id"
                      class="text-slate-400 hover:text-red-500 transition p-1 cursor-pointer disabled:opacity-40"
                      title="Remove from cart"
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

                  <!-- Real-time Quantity Modifiers -->
                  <div class="flex items-center gap-2">
                    <div :class="['flex items-center border rounded-lg overflow-hidden', darkMode ? 'border-slate-700 bg-[#0b0f19]' : 'border-slate-200 bg-white']">
                      <button
                        type="button"
                        @click="handleDecrementQuantity(item)"
                        :disabled="updatingCartItemId === item.id || item.quantity <= 1"
                        class="px-2 py-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition disabled:opacity-30 cursor-pointer"
                        title="Decrease quantity"
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
                        title="Enter quantity"
                      />
                      <button
                        type="button"
                        @click="handleIncrementQuantity(item)"
                        :disabled="updatingCartItemId === item.id"
                        class="px-2 py-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition disabled:opacity-30 cursor-pointer"
                        title="Increase quantity"
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

            <!-- Financial Calculations -->
            <div :class="['rounded-xl p-3 mb-5 space-y-2.5 border text-xs', darkMode ? 'bg-[#0b0f19] border-slate-800' : 'bg-slate-50 border-slate-200']">
              <div class="flex justify-between items-center text-slate-500 dark:text-slate-400">
                <span>Produce Subtotal:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">ETB {{ formatNumber(summarySubtotal) }}</span>
              </div>

              <!-- Real-time Dynamic Shipping Row with Weight & Distance Context -->
              <!-- Real-time Dynamic Shipping Row with Weight & Distance Context -->
              <div class="space-y-1.5">
                <div class="flex justify-between items-center text-slate-500 dark:text-slate-400">
                  <span class="truncate pr-2">Shipping ({{ selectedShippingTitle }}):</span>
                  <span class="font-bold text-blue-600 dark:text-blue-400 shrink-0">ETB {{ formatNumber(selectedShippingFee) }}</span>
                </div>

                <!-- Destination in Order Summary -->
                <div class="flex items-center justify-between text-[11px] pt-1 pb-0.5 border-t border-slate-100 dark:border-slate-800/60">
                  <span class="text-slate-500 dark:text-slate-400 font-medium">Destination:</span>
                  <span class="font-bold text-blue-600 dark:text-blue-400">{{ selectedDestination }}</span>
                </div>

                <div class="flex justify-between items-center text-[10px] text-slate-400">
                  <span>
                    <strong class="text-slate-700 dark:text-slate-200">{{ selectedOrigin }}</strong> → 
                    <strong class="text-blue-600 dark:text-blue-400 font-bold">{{ selectedDestination }}</strong>
                    (~{{ selectedDistanceKm }} km • {{ formatNumber(totalWeightKg) }} kg)
                  </span>
                </div>

                <!-- Live Dynamic Formula Breakdown -->
                <div
                  v-if="selectedShippingOption.breakdown && !selectedShippingOption.breakdown.isFlatRate"
                  class="text-[9px] text-blue-600 dark:text-blue-400 font-mono bg-blue-500/5 px-2 py-1 rounded border border-blue-500/10 leading-tight"
                >
                  Base ETB {{ selectedShippingOption.breakdown.baseFee }} + ({{ formatNumber(totalWeightKg) }}kg × ETB {{ selectedShippingOption.breakdown.ratePerKg }}) + ({{ selectedDistanceKm }}km to {{ selectedDestination }} × ETB {{ selectedShippingOption.breakdown.ratePerKm }}) = ETB {{ formatNumber(selectedShippingFee) }}
                </div>
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
                  <span class="text-[10px] text-slate-400">Includes freight &amp; settlement</span>
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
  getItemWeightKg,
  ETHIOPIAN_REGIONS_LOGISTICS,
  calculateOriginDestinationDistanceKm,
  resolveCartOriginAndDistance,
  WEIGHT_FLAT_THRESHOLD_KG,
  DISTANCE_SHORT_THRESHOLD_KM,
  type DynamicShippingOption
} from '@/utils/shippingCalculator'
import {
  Menu, ArrowLeft, Check, CheckCircle2, Truck, CreditCard,
  MapPin, Phone, User, Building, AlertCircle, ShoppingBag,
  Sparkles, RefreshCw, Lock, ShieldCheck, Leaf,
  Scale, Compass, Info, Plus, Minus, Trash2
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
const updatingCartItemId = ref<number | null>(null)

const cartItems = computed(() => buyerStore.cart || [])

// Ethiopian Regions list
const ethiopianRegions = [
  'Addis Ababa',
  'Oromia',
  'Amhara',
  'Sidama',
  'SNNPR',
  'Tigray',
  'Somali',
  'Dire Dawa',
  'Harari',
  'Afar',
  'Benishangul-Gumuz',
  'Gambela'
]

// Delivery Address Form
const addressForm = ref({
  recipient_name: '',
  phone: '',
  region: (route.query.region as string) || (typeof window !== 'undefined' ? sessionStorage.getItem('checkout_destination') : null) || 'Addis Ababa',
  zone: 'Bole Subcity',
  woreda: 'Woreda 03',
  address: 'Bole Road, Commercial Agro-Hub',
  notes: ''
})

const savedAddresses = ref<any[]>([])
const selectedSavedAddressId = ref<number | null>(null)

// ── DYNAMIC SHIPPING CALCULATION STATE & MATRIX ──
const destinationFromCart = computed<string>(() => {
  return (route.query.region as string) || (typeof window !== 'undefined' ? sessionStorage.getItem('checkout_destination') : '') || ''
})
const selectedDestination = ref<string>(destinationFromCart.value || addressForm.value.region || 'Addis Ababa')

// Synchronize selectedDestination and addressForm.region bidirectionally
watch(selectedDestination, (newDest) => {
  if (newDest && addressForm.value.region !== newDest) {
    addressForm.value.region = newDest
  }
})
watch(() => addressForm.value.region, (newReg) => {
  if (newReg && selectedDestination.value !== newReg) {
    selectedDestination.value = newReg
  }
})

const totalWeightKg = computed(() => getCartTotalWeightKg(cartItems.value))
const hasPerishables = computed(() => cartHasPerishables(cartItems.value))

// Resolve product origin from cart items
const cartOriginInfo = computed(() => {
  return resolveCartOriginAndDistance(cartItems.value, selectedDestination.value)
})

const selectedOrigin = computed(() => cartOriginInfo.value.origin || 'Adama')

// Dynamic distance fetched directly from distance matrix
const selectedDistanceKm = computed(() => {
  return calculateOriginDestinationDistanceKm(selectedOrigin.value, selectedDestination.value)
})

const regionInfo = computed(() => {
  return ETHIOPIAN_REGIONS_LOGISTICS[selectedDestination.value] || ETHIOPIAN_REGIONS_LOGISTICS['Addis Ababa']
})

// Dynamic shipping options recalculated instantly when destination, origin, or weight changes
const shippingOptions = computed<DynamicShippingOption[]>(() => {
  return getDynamicShippingOptions(cartItems.value, selectedDestination.value, selectedOrigin.value)
})

const selectedShipping = ref<string>((route.query.shipping as string) || (typeof window !== 'undefined' ? sessionStorage.getItem('checkout_shipping') : null) || 'standard')

// Immediate watchers for query params from shopping cart
watch(
  () => route.query.region,
  (newRegion) => {
    if (newRegion && typeof newRegion === 'string') {
      selectedDestination.value = newRegion
      addressForm.value.region = newRegion
      if (typeof window !== 'undefined') sessionStorage.setItem('checkout_destination', newRegion)
    }
  },
  { immediate: true }
)

watch(
  () => route.query.shipping,
  (newShip) => {
    if (newShip && typeof newShip === 'string') {
      selectedShipping.value = newShip
      if (typeof window !== 'undefined') sessionStorage.setItem('checkout_shipping', newShip)
    }
  },
  { immediate: true }
)


// Watch shipping options: auto-fallback if selected option is hidden (e.g. cold-chain hidden for grains)
watch(
  shippingOptions,
  (newOptions) => {
    if (newOptions.length > 0 && !newOptions.some(o => o.key === selectedShipping.value)) {
      selectedShipping.value = newOptions[0].key
    }
  },
  { immediate: true }
)

const selectedShippingOption = computed(() => {
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
      breakdown: {
        baseFee: 200,
        weightKg: totalWeightKg.value,
        ratePerKg: 10,
        weightCost: 0,
        originLocation: selectedOrigin.value,
        destinationLocation: selectedDestination.value,
        distanceKm: selectedDistanceKm.value,
        ratePerKm: 6,
        distanceCost: 0,
        isFlatRate: true,
        flatRate: 350,
        totalFee: 350,
        formulaStr: `Standard Flat Rate: ETB 350 (Light load ≤ 5kg)`
      }
    }
  )
})

const selectedShippingFee = computed(() => selectedShippingOption.value.fee)
const selectedShippingTitle = computed(() => selectedShippingOption.value.title)

// Real-time Cart Item Handlers
const handleIncrementQuantity = async (item: any) => {
  if (updatingCartItemId.value === item.id) return
  updatingCartItemId.value = item.id
  try {
    const newQty = (Number(item.quantity) || 1) + 1
    await buyerStore.updateCartItem(item.id, { quantity: newQty })
  } catch (e) {
    console.error('Failed to increment quantity:', e)
  } finally {
    updatingCartItemId.value = null
  }
}

const handleDecrementQuantity = async (item: any) => {
  if (updatingCartItemId.value === item.id || (Number(item.quantity) || 1) <= 1) return
  updatingCartItemId.value = item.id
  try {
    const newQty = (Number(item.quantity) || 1) - 1
    await buyerStore.updateCartItem(item.id, { quantity: newQty })
  } catch (e) {
    console.error('Failed to decrement quantity:', e)
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
  } catch (e) {
    console.error('Failed to change quantity:', e)
  } finally {
    updatingCartItemId.value = null
  }
}

const handleRemoveItem = async (itemId: number) => {
  if (updatingCartItemId.value === itemId) return
  updatingCartItemId.value = itemId
  try {
    await buyerStore.removeFromCart(itemId)
  } catch (e) {
    console.error('Failed to remove item:', e)
  } finally {
    updatingCartItemId.value = null
  }
}

// Payment Methods State & Helpers
const primaryPaymentMethod = ref<any>(null)
const savedPaymentMethods = ref<any[]>([])

const maskAccountNumber = (num: string | number) => {
  if (!num) return ''
  const str = String(num).trim()
  if (str.length <= 4) return str
  return '•••• ' + str.slice(-4)
}

const getMethodKeyForSaved = (pm: any): string => {
  if (!pm) return 'telebirr'
  const type = pm.type
  const bank = (pm.bank_name || '').toLowerCase()
  if (type === 'bank_transfer') return 'bank_transfer'
  if (type === 'mobile_money') {
    return bank.includes('cbe') ? 'cbe_birr' : 'telebirr'
  }
  if (type === 'card') return 'card'
  if (type === 'cash') return 'cash_on_delivery'
  return 'telebirr'
}

const applyPrimaryPaymentMethod = (pm: any) => {
  if (!pm) return
  primaryPaymentMethod.value = pm

  const targetKey = getMethodKeyForSaved(pm)
  paymentForm.value.method = targetKey

  if (targetKey === 'telebirr') {
    paymentForm.value.telebirr_phone = pm.account_number || authStore.user?.phone || ''
  } else if (targetKey === 'cbe_birr') {
    paymentForm.value.cbe_phone = pm.account_number || authStore.user?.phone || ''
  } else if (targetKey === 'bank_transfer') {
    paymentForm.value.bank_ref = `${pm.bank_name} (Acc: ${pm.account_number})`
  }
}

const paymentMethodsList = computed(() => {
  const isBankPrimary = primaryPaymentMethod.value?.type === 'bank_transfer'
  const bankTitle = isBankPrimary
    ? `${primaryPaymentMethod.value.bank_name} Direct Transfer`
    : 'CBE Direct Transfer'
  const bankDesc = isBankPrimary
    ? `Primary saved account (${primaryPaymentMethod.value.account_holder})`
    : 'Deposit to escrow bank account'
  const isTelebirrPrimary = primaryPaymentMethod.value?.type === 'mobile_money' && !(primaryPaymentMethod.value?.bank_name || '').toLowerCase().includes('cbe')
  const isCbeBirrPrimary = primaryPaymentMethod.value?.type === 'mobile_money' && (primaryPaymentMethod.value?.bank_name || '').toLowerCase().includes('cbe')
  const isCardPrimary = primaryPaymentMethod.value?.type === 'card'
  const isCashPrimary = primaryPaymentMethod.value?.type === 'cash'

  return [
    { key: 'telebirr', title: 'Telebirr (Chapa)', desc: isTelebirrPrimary ? `Primary saved wallet (${primaryPaymentMethod.value.account_holder})` : 'Instant mobile wallet payment', badge: isTelebirrPrimary ? 'PRIMARY' : 'POPULAR' },
    { key: 'cbe_birr', title: 'CBE Birr', desc: isCbeBirrPrimary ? `Primary saved mobile account (${primaryPaymentMethod.value.account_holder})` : 'Commercial Bank of Ethiopia mobile pay', badge: isCbeBirrPrimary ? 'PRIMARY' : null },
    { key: 'card', title: 'Debit / Credit Card', desc: isCardPrimary ? `Primary saved card (${primaryPaymentMethod.value.account_holder})` : 'Visa, Mastercard & Ethiopian bank cards', badge: isCardPrimary ? 'PRIMARY' : 'SECURE' },
    { key: 'bank_transfer', title: bankTitle, desc: bankDesc, badge: isBankPrimary ? 'PRIMARY' : null },
    { key: 'cash_on_delivery', title: 'Agro-Escrow / COD', desc: 'Inspect produce upon physical delivery', badge: isCashPrimary ? 'PRIMARY' : null }
  ]
})

const paymentForm = ref({
  method: 'telebirr',
  telebirr_phone: '',
  cbe_phone: '',
  bank_ref: ''
})

// Financial Calculations
const summarySubtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => {
    const price = Number(item.unit_price || item.price || 0)
    const qty = Number(item.quantity || 1)
    return sum + (item.subtotal ? Number(item.subtotal) : price * qty)
  }, 0)
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
  return Math.max(0, summarySubtotal.value + selectedShippingFee.value - summaryDiscount.value)
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
    addressForm.value.recipient_name = addr.contact_name || addr.recipient_name || authStore.user?.name || addressForm.value.recipient_name || 'Verified Buyer'
    addressForm.value.phone = addr.contact_phone || addr.phone || authStore.user?.phone || addressForm.value.phone || '+251 911 234 567'
    if (!destinationFromCart.value) { addressForm.value.region = addr.region || addressForm.value.region || 'Addis Ababa' } else { addressForm.value.region = selectedDestination.value }
    addressForm.value.zone = addr.sub_city || addr.zone || addr.city || addressForm.value.zone || 'Bole Subcity'
    addressForm.value.woreda = addr.woreda_kebele || addr.woreda || addressForm.value.woreda || 'Woreda 03'
    addressForm.value.address = addr.street_address || addr.address_line || addr.address || addressForm.value.address || 'Bole Road, Commercial Agro-Hub'
    if (addr.instructions || addr.notes) {
      addressForm.value.notes = addr.instructions || addr.notes
    }
  }
}

onMounted(async () => {
  themeStore.initializeTheme()

  // Ensure cart is fresh
  if (!buyerStore.cart || buyerStore.cart.length === 0) {
    await buyerStore.fetchCart().catch(() => {})
  }

  // Pre-fill user data
  if (authStore.user) {
    if (authStore.user.name || authStore.user.full_name) {
      addressForm.value.recipient_name = authStore.user.name || authStore.user.full_name
    }
    if (authStore.user.phone) {
      addressForm.value.phone = authStore.user.phone
      paymentForm.value.telebirr_phone = authStore.user.phone
      paymentForm.value.cbe_phone = authStore.user.phone
    }
    if (!destinationFromCart.value && authStore.user.region) { addressForm.value.region = authStore.user.region; selectedDestination.value = authStore.user.region }
    if (authStore.user.zone) addressForm.value.zone = authStore.user.zone
    if (authStore.user.woreda) addressForm.value.woreda = authStore.user.woreda
    if (authStore.user.address) addressForm.value.address = authStore.user.address
  }

  // Load saved delivery addresses if available
  try {
    const res = await buyerAPI.getDeliveryAddresses()
    const list = res.data?.data || res.data || []
    if (Array.isArray(list) && list.length > 0) {
      savedAddresses.value = list
      if (!destinationFromCart.value) {
        const defaultAddr = list.find(a => a.is_default) || list[0]
        selectedSavedAddressId.value = defaultAddr.id
        applySavedAddress()
      } else {
        const matchingAddr = list.find(a => (a.region || '').toLowerCase() === destinationFromCart.value.toLowerCase())
        if (matchingAddr) {
          selectedSavedAddressId.value = matchingAddr.id
          applySavedAddress()
        }
        addressForm.value.region = selectedDestination.value
      }
    }
  } catch (e) {
    // Graceful fallback to manual entry
  }

  // Load saved payment methods & auto-select primary from PostgreSQL database
  try {
    const res = await buyerAPI.getPaymentMethods()
    const methods = res.data?.data || []
    if (Array.isArray(methods) && methods.length > 0) {
      savedPaymentMethods.value = methods
      const primary = methods.find((m: any) => m.is_primary) || methods[0]
      if (primary) {
        applyPrimaryPaymentMethod(primary)
      }
    }
  } catch (err) {
    console.warn('Failed to load primary payment method from database:', err)
  }
})

const handlePlaceOrder = async () => {
  errorMessage.value = null

  // Ensure valid recipient name
  if (!addressForm.value.recipient_name?.trim()) {
    addressForm.value.recipient_name = authStore.user?.name || authStore.user?.email?.split('@')[0] || 'Valued Buyer'
  }

  // Ensure valid phone
  if (!addressForm.value.phone?.trim()) {
    addressForm.value.phone = authStore.user?.phone || '+251 911 234 567'
  }

  // Ensure valid dropoff address with graceful fallback instead of blocking
  if (!addressForm.value.address?.trim()) {
    addressForm.value.address = authStore.user?.address || `${addressForm.value.zone || 'Central Area'}, ${addressForm.value.region || 'Addis Ababa'}`
  }

  submitting.value = true

  try {
    const payload = {
      delivery_address: `${addressForm.value.address}, ${addressForm.value.woreda}, ${addressForm.value.zone}, ${addressForm.value.region}`,
      delivery_region: addressForm.value.region,
      delivery_zone: addressForm.value.zone,
      delivery_woreda: addressForm.value.woreda,
      phone: addressForm.value.phone,
      delivery_fee: selectedShippingFee.value,
      shipping_type: selectedShipping.value,
      payment_method: paymentForm.value.method,
      tax_amount: 0,
      promo_discount: summaryDiscount.value,
      notes: addressForm.value.notes,
      payment_reference: paymentForm.value.bank_ref || undefined
    }

    // 1. Create order(s) in PostgreSQL database
    const res = await buyerStore.checkout(payload)
    const orders = res.data || []
    confirmedOrders.value = orders
    const primaryOrder = Array.isArray(orders) ? orders[0] : orders
    const orderId = primaryOrder?.id

    // 2. Clear cart state in store
    await buyerStore.fetchCart().catch(() => {})

    // 3. If Cash on Delivery is chosen, show order confirmation modal
    if (paymentForm.value.method === 'cash_on_delivery') {
      orderSuccess.value = true
      return
    }

    // 4. Call Chapa initialize API to get checkout_url
    const chapaPayload = {
      amount: summaryGrandTotal.value,
      order_id: orderId,
      phone_number: addressForm.value.phone || paymentForm.value.telebirr_phone || paymentForm.value.cbe_phone || authStore.user?.phone || '0911000000',
      email: authStore.user?.email || 'aydenfudagne@gmail.com',
      channel: paymentForm.value.method,
      return_url: `${window.location.origin}/buyer/orders/active?order_id=${orderId || ''}&payment_status=completed`
    }

    const chapaRes = await buyerAPI.initializeChapaPayment(chapaPayload)
    const checkoutUrl = chapaRes.data?.data?.checkout_url || chapaRes.data?.checkout_url

    if (checkoutUrl) {
      // 5. Automatically redirect browser to Chapa's hosted test checkout page
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
