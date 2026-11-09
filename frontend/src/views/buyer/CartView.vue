<template>
  <div class="buyer-layout" :class="{ 'dark': isDark }">
    <!-- Sidebar -->
    <BuyerSidebar @logout="handleLogout" />

    <!-- Main Content -->
    <div class="cart-container" :class="{ 'dark': isDark }">
      <!-- Header Section -->
      <div class="cart-header">
        <div>
          <h1 class="page-title">Shopping Cart</h1>
          <p class="page-subtitle">Review and manage your items</p>
        </div>
        <div class="header-actions">
          <BuyerHeaderNav />
        </div>
      </div>

      <div class="cart-body">
        <!-- Empty Cart State -->
        <div v-if="buyerStore.cart.length === 0" class="empty-cart">
          <div class="empty-icon">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <h2>Your cart is empty</h2>
          <p>Start shopping to add items to your cart</p>
          <button @click="continueShopping" class="btn-primary">
            <i class="fas fa-shopping-bag"></i> Browse Marketplace
          </button>
        </div>

        <!-- Cart Content -->
        <div v-else class="cart-content">
          <!-- Main Cart Section -->
          <div class="cart-main">
            <!-- Cart Header -->
            <div class="cart-items-header">
              <div class="item-count">
                <span class="badge">{{ buyerStore.cart.length }} item(s)</span>
              </div>
              <button @click="showSelectAll = !showSelectAll" class="btn-secondary btn-sm">
                <i :class="['fas', showSelectAll ? 'fa-check-square' : 'fa-square']"></i>
                Select All
              </button>
            </div>

            <!-- Cart Items -->
            <div class="cart-items">
              <div
                v-for="item in buyerStore.cart"
                :key="item.id"
                class="cart-item"
                :class="{ selected: selectedItems.includes(item.id) }"
              >
                <!-- Item Checkbox -->
                <div class="item-checkbox">
                  <input
                    type="checkbox"
                    :checked="selectedItems.includes(item.id)"
                    @change="toggleItemSelect(item.id)"
                    class="checkbox"
                  />
                </div>

                <!-- Item Image -->
                <div class="item-image">
                  <img
                    v-if="formatImageUrl(item.image_url)"
                    :src="formatImageUrl(item.image_url)"
                    :alt="item.product_name"
                    @error="(e: any) => e.target.style.display = 'none'"
                    class="image"
                  />
                  <div v-else class="no-image">
                    <i class="fas fa-leaf"></i>
                  </div>
                </div>

                <!-- Item Details -->
                <div class="item-details">
                  <h3 class="item-name">{{ item.product_name }}</h3>
                  <p v-if="item.supplier_name || item.product?.farmer_name || item.product?.farmer?.user?.name" class="item-supplier">
                    <i class="fas fa-store"></i> {{ item.product?.farmer?.user?.name || item.supplier_name || item.product?.farmer_name || item.product?.farmer?.name || item.product?.farmer?.farm_name || item.product?.farmer?.cooperative_name || '' }}
                  </p>
                  <p class="item-sku">SKU: {{ item.sku || 'N/A' }}</p>
                </div>

                <!-- Item Price -->
                <div class="item-price">
                  <span class="unit-price">ETB {{ formatNumber(item.unit_price || 0) }}</span>
                </div>

                <!-- Item Quantity -->
                <div class="item-quantity">
                  <button @click="decrementQuantity(item.id)" class="qty-btn">
                    <i class="fas fa-minus"></i>
                  </button>
                  <input
                    type="number"
                    :value="item.quantity"
                    @change="updateQuantity(item.id, $event)"
                    min="1"
                    class="qty-input"
                  />
                  <button @click="incrementQuantity(item.id)" class="qty-btn">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>

                <!-- Item Subtotal -->
                <div class="item-subtotal">
                  <span class="subtotal-amount">ETB {{ formatNumber(item.subtotal || 0) }}</span>
                </div>

                <!-- Item Actions -->
                <div class="item-actions">
                  <button
                    type="button"
                    @click="addToWishlist(item)"
                    :class="['action-btn', { 'wishlist-active': isItemInWishlist(item.product_id || item.id) }]"
                    :title="isItemInWishlist(item.product_id || item.id) ? 'Remove from Wishlist' : 'Add to Wishlist'"
                  >
                    <Heart
                      class="w-4 h-4 transition-colors"
                      :class="isItemInWishlist(item.product_id || item.id) ? 'fill-red-500 text-red-500' : ''"
                    />
                  </button>
                  <button
                    type="button"
                    @click="removeFromCart(item.id)"
                    class="action-btn danger"
                    title="Remove from Cart"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Cart Actions -->
            <div class="cart-item-actions">
              <button
                v-if="selectedItems.length > 0"
                @click="removeSelected"
                class="btn-danger"
              >
                <i class="fas fa-trash"></i> Remove Selected
              </button>
              <button @click="continueShopping" class="btn-secondary">
                <i class="fas fa-shopping-bag"></i> Add More Items
              </button>
            </div>
          </div>

          <!-- Order Summary Sidebar -->
          <div class="cart-summary">
            <!-- Summary Card -->
            <div class="summary-card">
              <h2 class="summary-title">Order Summary</h2>

              <!-- Summary Items -->
              <div class="summary-items">
                <div class="summary-row">
                  <span class="label">Subtotal:</span>
                  <span class="value">ETB {{ formatNumber(subtotal) }}</span>
                </div>
                <div class="summary-row">
                  <span class="label">Destination:</span>
                  <select
                    v-model="selectedDestination"
                    class="region-select"
                  >
                    <option v-for="d in deliveryDestinations" :key="d.city + '-' + d.region" :value="d.city + ', ' + d.region">
                      {{ d.label }}
                    </option>
                  </select>
                </div>

                <!-- Shipping Options (Including Self-Pickup styled identically) -->
                <div class="summary-row shipping-row flex-col items-start">
                  <span class="label mb-2">Shipping:</span>
                  <div class="shipping-options w-full">
                    <label
                      v-for="opt in dynamicShippingOptions"
                      :key="opt.key"
                      class="radio-option"
                    >
                      <input
                        type="radio"
                        v-model="shippingType"
                        :value="opt.key"
                      />
                      <div class="radio-content">
                        <span class="font-semibold">{{ opt.title }} (ETB {{ formatNumber(opt.fee) }})</span>
                      </div>
                    </label>

                    <!-- Self-Pickup / Farm-Gate Option (Styled identically to standard options) -->
                    <label class="radio-option border-t pt-2 mt-2 border-slate-200 dark:border-slate-800">
                      <input
                        type="radio"
                        v-model="shippingType"
                        value="self-pickup"
                      />
                      <div class="radio-content">
                        <span class="font-semibold">Self-Pickup / Farm-Gate (ETB 0)</span>
                      </div>
                    </label>
                  </div>
                </div>

                <!-- Discount Code -->
                <div class="discount-section">
                  <label>Promo Code:</label>
                  <div class="promo-input-group">
                    <input
                      v-model="promoCode"
                      type="text"
                      placeholder="Enter promo code"
                      class="promo-input"
                    />
                    <button @click="applyPromoCode" class="btn-apply">Apply</button>
                  </div>
                  <div v-if="promoDiscount > 0" class="discount-applied">
                    <i class="fas fa-check"></i> Discount: -ETB {{ formatNumber(promoDiscount) }}
                  </div>
                </div>

                <div class="summary-divider"></div>

                <!-- Total (Tax-Free & Self-Pickup Supported) -->
                <div class="summary-total">
                  <span class="total-label">Total:</span>
                  <span class="total-amount">ETB {{ formatNumber(total) }}</span>
                </div>
              </div>

              <!-- Checkout Button -->
              <button
                @click="proceedToCheckout"
                :disabled="buyerStore.cart.length === 0"
                class="btn-checkout"
              >
                <i class="fas fa-credit-card"></i> Proceed to Checkout
              </button>

              <!-- Save for Later -->
              <button @click="saveCart" class="btn-secondary btn-full">
                <i class="fas fa-bookmark"></i> Save for Later
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Heart, Trash2 } from 'lucide-vue-next'
import { useBuyerStore } from '@/stores/buyerStore'
import { useAuthStore } from '@/stores/authStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'
import BuyerHeaderNav from '@/components/layout/BuyerHeaderNav.vue'
import { formatImageUrl } from '@/utils/helpers'
import { useThemeStore } from '@/stores/themeStore'
import {
  getDynamicShippingOptions,
  calculateOriginDestinationDistanceKm,
  resolveCartOriginAndDistance,
  getCartTotalWeightKg,
  ETHIOPIAN_REGIONS,
  ETHIOPIAN_DELIVERY_DESTINATIONS,
  resolveDestinationHubAndRegion,
  type DynamicShippingOption,
  type OriginShippingRoute
} from '@/utils/shippingCalculator'

// Stores and Router
const buyerStore = useBuyerStore()
const authStore = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()
const isDark = computed(() => themeStore.isDark)

// State
const deliveryDestinations = ETHIOPIAN_DELIVERY_DESTINATIONS
const savedDest = (typeof window !== 'undefined' && sessionStorage.getItem('checkout_destination')) || ''
const initialDest = resolveDestinationHubAndRegion(savedDest || authStore.user?.region || 'Addis Ababa')
const selectedDestination = ref<string>(`${initialDest.city}, ${initialDest.region}`)
const selectedItems = ref<number[]>([])
const showSelectAll = ref(false)
const savedShip = (typeof window !== 'undefined' && sessionStorage.getItem('checkout_shipping')) as any
const shippingType = ref<string>(savedShip || 'standard')
const promoCode = ref('')
const promoDiscount = ref(0)

watch(selectedDestination, (newVal) => {
  if (newVal && typeof window !== 'undefined') {
    sessionStorage.setItem('checkout_destination', newVal)
  }
}, { immediate: true })

watch(shippingType, (newVal) => {
  if (newVal && typeof window !== 'undefined') {
    sessionStorage.setItem('checkout_shipping', newVal)
  }
}, { immediate: true })

// Dynamic shipping options calculated from cart items and weight/perishability
const cartOriginInfo = computed(() => resolveCartOriginAndDistance(buyerStore.cart || [], selectedDestination.value))
const selectedOrigin = computed(() => cartOriginInfo.value.origin || 'Adama')
const selectedDistanceKm = computed(() => calculateOriginDestinationDistanceKm(selectedOrigin.value, selectedDestination.value))
const totalWeightKg = computed(() => Math.max(1, getCartTotalWeightKg(buyerStore.cart || [])))

const dynamicShippingOptions = computed<DynamicShippingOption[]>(() => {
  return getDynamicShippingOptions(buyerStore.cart || [], selectedDestination.value)
})

// Shipping fee logic supporting Self-Pickup (ETB 0)
const selectedShippingFee = computed(() => {
  if (shippingType.value === 'self-pickup') {
    return 0
  }
  const found = dynamicShippingOptions.value.find(o => o.key === shippingType.value)
  return found ? found.fee : (dynamicShippingOptions.value[0]?.fee || 350)
})

const shippingCost = selectedShippingFee

onMounted(async () => {
  themeStore.initializeTheme()
  try {
    await buyerStore.fetchCart()
  } catch (error) {
    console.error('Failed to load cart:', error)
  }
  try {
    await buyerStore.fetchWishlist()
  } catch (error) {
    console.error('Failed to load wishlist:', error)
  }
})

// Computed properties (Tax fully excluded)
const subtotal = computed(() => {
  return buyerStore.cart.reduce((sum, item) => sum + (item.subtotal || 0), 0)
})

const total = computed(() => {
  return Math.max(0, subtotal.value + shippingCost.value - promoDiscount.value)
})

// Methods
const formatNumber = (num: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
  }).format(num || 0)
}

const toggleItemSelect = (itemId: number) => {
  const index = selectedItems.value.indexOf(itemId)
  if (index > -1) {
    selectedItems.value.splice(index, 1)
  } else {
    selectedItems.value.push(itemId)
  }
}

const incrementQuantity = async (itemId: number) => {
  const item = buyerStore.cart.find(i => i.id === itemId)
  if (item) {
    await updateQuantity(itemId, { target: { value: (item.quantity || 1) + 1 } })
  }
}

const decrementQuantity = async (itemId: number) => {
  const item = buyerStore.cart.find(i => i.id === itemId)
  if (item && item.quantity > 1) {
    await updateQuantity(itemId, { target: { value: (item.quantity || 1) - 1 } })
  }
}

const updateQuantity = async (itemId: number, event: any) => {
  const quantity = parseInt(event.target.value) || 1
  try {
    await buyerStore.updateCartItem(itemId, { quantity })
  } catch (error) {
    console.error('Failed to update quantity:', error)
  }
}

const removeFromCart = async (itemId: number) => {
  try {
    await buyerStore.removeFromCart(itemId)
    selectedItems.value = selectedItems.value.filter(id => id !== itemId)
  } catch (error) {
    console.error('Failed to remove from cart:', error)
  }
}

const removeSelected = async () => {
  if (confirm(`Remove ${selectedItems.value.length} item(s) from cart?`)) {
    try {
      for (const itemId of selectedItems.value) {
        await buyerStore.removeFromCart(itemId)
      }
      selectedItems.value = []
    } catch (error) {
      console.error('Failed to remove items:', error)
    }
  }
}

const isItemInWishlist = (productId: number) => {
  return (buyerStore.wishlist || []).some((w: any) => w.product_id === productId || w.id === productId)
}

const addToWishlist = async (item: any) => {
  const prodId = item?.product_id || item?.id || item
  try {
    if (isItemInWishlist(prodId)) {
      const match = (buyerStore.wishlist || []).find((w: any) => w.product_id === prodId || w.id === prodId)
      if (match?.id) {
        await buyerStore.removeFromWishlist(match.id)
      }
    } else {
      await buyerStore.addToWishlist({ product_id: prodId })
    }
  } catch (error) {
    console.error('Failed to update wishlist:', error)
  }
}

const applyPromoCode = () => {
  if (promoCode.value === 'SAVE10') {
    promoDiscount.value = Math.round(subtotal.value * 0.1)
  } else if (promoCode.value === 'SAVE500') {
    promoDiscount.value = 500
  } else {
    promoDiscount.value = 0
    alert('Invalid promo code')
  }
}

const saveCart = () => {
  alert('Cart saved successfully!')
}

const continueShopping = () => {
  router.push('/buyer/marketplace')
}

const proceedToCheckout = () => {
  if (!buyerStore.cart || buyerStore.cart.length === 0) {
    alert('Your shopping cart is empty. Please add items before checking out.')
    return
  }
  if (typeof window !== 'undefined') {
    sessionStorage.setItem('checkout_destination', selectedDestination.value)
    sessionStorage.setItem('checkout_shipping', shippingType.value)
  }
  router.push({
    path: '/buyer/checkout',
    query: {
      shipping: shippingType.value,
      region: selectedDestination.value,
      promo: promoCode.value && promoDiscount.value > 0 ? promoCode.value : undefined
    }
  })
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
/* Layout */
.buyer-layout {
  display: flex;
  height: 100vh;
}

.cart-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f5f5;
  min-height: 100vh;
  padding: 0;
}

/* Header */
.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 32px;
  background-color: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  gap: 20px;
}

.page-title {
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.025em;
  color: #0f172a;
  margin: 0 0 4px 0;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.cart-body {
  padding: 28px 32px 48px;
}

/* Empty Cart */
.empty-cart {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 12px;
  border: 2px dashed #d1d5db;
}

.empty-icon {
  font-size: 64px;
  color: #d1d5db;
  margin-bottom: 20px;
}

.empty-cart h2 {
  font-size: 24px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.empty-cart p {
  font-size: 16px;
  color: #6b7280;
  margin: 0 0 24px 0;
}

/* Cart Content */
.cart-content {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 24px;
}

/* Cart Main */
.cart-main {
  background: white;
  border-radius: 12px;
  overflow: visible;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.cart-items-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: linear-gradient(to right, #f9fafb, #ffffff);
}

.badge {
  display: inline-block;
  padding: 6px 12px;
  background: #dbeafe;
  color: #1e40af;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

/* Cart Items */
.cart-items {
  display: flex;
  flex-direction: column;
}

.cart-item {
  display: grid;
  grid-template-columns: 36px 80px minmax(120px, 1fr) 95px 115px 95px 86px;
  gap: 12px;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
  transition: all 0.3s;
}

.cart-item:hover {
  background: #f9fafb;
}

.cart-item.selected {
  background: #eff6ff;
}

.item-checkbox {
  display: flex;
  align-items: center;
  justify-content: center;
}

.checkbox {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #3b82f6;
}

.item-image {
  width: 80px;
  height: 80px;
  background: #f3f4f6;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
}

.image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: #d1d5db;
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.item-name {
  font-size: 15px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.item-supplier {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.item-sku {
  font-size: 11px;
  color: #9ca3af;
  margin: 0;
}

.item-price {
  text-align: right;
  min-width: 100px;
}

.unit-price {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
}

.item-quantity {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 4px;
  min-width: 140px;
}

.qty-btn {
  width: 30px;
  height: 30px;
  border: none;
  background: none;
  cursor: pointer;
  color: #6b7280;
  font-size: 12px;
  border-radius: 4px;
}

.qty-btn:hover {
  background: #f0f0f0;
  color: #1f2937;
}

.qty-input {
  flex: 1;
  border: none;
  text-align: center;
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  outline: none;
  width: 40px;
}

.item-subtotal {
  text-align: right;
  min-width: 100px;
}

.subtotal-amount {
  font-size: 16px;
  font-weight: 700;
  color: #2563eb;
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: flex-end;
}

.action-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-btn:hover {
  color: #3b82f6;
  border-color: #3b82f6;
}

.action-btn.danger:hover {
  color: #ef4444;
  border-color: #ef4444;
  background: #fee2e2;
}

.cart-item-actions {
  display: flex;
  gap: 12px;
  padding: 16px 24px;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

/* Cart Summary */
.cart-summary {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 20px;
}

.summary-title {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.summary-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: #6b7280;
}

.summary-row .label {
  font-weight: 500;
}

.summary-row .value {
  color: #1f2937;
  font-weight: 600;
}

.region-select {
  padding: 4px 8px;
  font-size: 12px;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  background-color: #fff;
  color: #1e293b;
  outline: none;
}

.shipping-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 4px;
}

.radio-option {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 13px;
  cursor: pointer;
  color: #1f2937;
}

.radio-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.discount-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
  margin-top: 12px;
}

.discount-section label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
}

.promo-input-group {
  display: flex;
  gap: 8px;
}

.promo-input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 13px;
}

.btn-apply {
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.btn-apply:hover {
  background: #2563eb;
}

.discount-applied {
  font-size: 12px;
  color: #10b981;
  display: flex;
  align-items: center;
  gap: 6px;
}

.summary-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 8px 0;
}

.summary-total {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-top: 2px solid #e5e7eb;
  border-bottom: 2px solid #e5e7eb;
}

.total-label {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
}

.total-amount {
  font-size: 22px;
  font-weight: 700;
  color: #2563eb;
}

.btn-checkout {
  width: 100%;
  padding: 14px 20px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 16px;
}

.btn-checkout:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-checkout:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-full {
  width: 100%;
  margin-top: 12px;
}

.btn-primary {
  background: #3b82f6;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-secondary {
  background: #f0f0f0;
  color: #1f2937;
  padding: 10px 20px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 13px;
}

.btn-danger {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fecaca;
  padding: 8px 16px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

/* ════════════════════════════════════════════════════════════════ */
/* ── COMPREHENSIVE DARK MODE THEME STYLES ── */
/* ════════════════════════════════════════════════════════════════ */
.buyer-layout.dark,
:global(html.dark) .buyer-layout {
  background-color: #0b0f19;
  color: #f1f5f9;
}

.buyer-layout.dark .cart-container,
.cart-container.dark,
:global(html.dark) .cart-container {
  background-color: #0b0f19;
  color: #f1f5f9;
}

.buyer-layout.dark .cart-header,
:global(html.dark) .cart-header {
  background-color: #131b2e;
  border-bottom: 1px solid #1e293b;
}

.buyer-layout.dark .page-title,
:global(html.dark) .page-title {
  color: #f8fafc;
}

.buyer-layout.dark .page-subtitle,
:global(html.dark) .page-subtitle {
  color: #94a3b8;
}

.buyer-layout.dark .empty-cart,
:global(html.dark) .empty-cart {
  background: #131b2e;
  border-color: #334155;
}

.buyer-layout.dark .empty-icon,
:global(html.dark) .empty-icon {
  color: #475569;
}

.buyer-layout.dark .empty-cart h2,
:global(html.dark) .empty-cart h2 {
  color: #f1f5f9;
}

.buyer-layout.dark .empty-cart p,
:global(html.dark) .empty-cart p {
  color: #94a3b8;
}

.buyer-layout.dark .cart-main,
:global(html.dark) .cart-main {
  background: #131b2e;
  border: 1px solid #1e293b;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.buyer-layout.dark .cart-items-header,
:global(html.dark) .cart-items-header {
  background: #0f172a;
  border-bottom: 1px solid #1e293b;
}

.buyer-layout.dark .badge,
:global(html.dark) .badge {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.35);
}

.buyer-layout.dark .cart-item,
:global(html.dark) .cart-item {
  background: #131b2e;
  border-bottom: 1px solid #1e293b;
}

.buyer-layout.dark .cart-item:hover,
:global(html.dark) .cart-item:hover {
  background: #1a233a;
}

.buyer-layout.dark .cart-item.selected,
:global(html.dark) .cart-item.selected {
  background: rgba(30, 58, 138, 0.28);
}

.buyer-layout.dark .item-image,
:global(html.dark) .item-image {
  background: #0f172a;
  border: 1px solid #1e293b;
}

.buyer-layout.dark .no-image,
:global(html.dark) .no-image {
  color: #475569;
}

.buyer-layout.dark .item-name,
:global(html.dark) .item-name {
  color: #f1f5f9;
}

.buyer-layout.dark .item-supplier,
:global(html.dark) .item-supplier {
  color: #94a3b8;
}

.buyer-layout.dark .item-sku,
:global(html.dark) .item-sku {
  color: #64748b;
}

.buyer-layout.dark .unit-price,
:global(html.dark) .unit-price {
  color: #e2e8f0;
}

.buyer-layout.dark .item-quantity,
:global(html.dark) .item-quantity {
  border-color: #334155;
  background: #0f172a;
}

.buyer-layout.dark .qty-btn,
:global(html.dark) .qty-btn {
  color: #94a3b8;
}

.buyer-layout.dark .qty-btn:hover,
:global(html.dark) .qty-btn:hover {
  background: #1e293b;
  color: #ffffff;
}

.buyer-layout.dark .qty-input,
:global(html.dark) .qty-input {
  color: #f1f5f9;
  background: transparent;
}

.buyer-layout.dark .subtotal-amount,
:global(html.dark) .subtotal-amount {
  color: #60a5fa;
}

.buyer-layout.dark .action-btn,
:global(html.dark) .action-btn {
  background: #0f172a;
  border-color: #334155;
  color: #94a3b8;
}

.buyer-layout.dark .action-btn:hover,
:global(html.dark) .action-btn:hover {
  color: #60a5fa;
  border-color: #3b82f6;
  background: #1e293b;
}

.buyer-layout.dark .action-btn.danger:hover,
:global(html.dark) .action-btn.danger:hover {
  color: #f87171;
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.2);
}

.buyer-layout.dark .action-btn.wishlist-active,
:global(html.dark) .action-btn.wishlist-active {
  color: #f87171;
  border-color: #ef4444;
  background: rgba(239, 68, 68, 0.15);
}

.buyer-layout.dark .cart-item-actions,
:global(html.dark) .cart-item-actions {
  background: #0f172a;
  border-top: 1px solid #1e293b;
}

.buyer-layout.dark .summary-card,
:global(html.dark) .summary-card {
  background: #131b2e;
  border: 1px solid #1e293b;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.buyer-layout.dark .summary-title,
:global(html.dark) .summary-title {
  color: #f1f5f9;
}

.buyer-layout.dark .summary-row,
:global(html.dark) .summary-row {
  color: #94a3b8;
}

.buyer-layout.dark .summary-row .value,
:global(html.dark) .summary-row .value {
  color: #f1f5f9;
}

.buyer-layout.dark .region-select,
:global(html.dark) .region-select {
  background-color: #0f172a;
  border-color: #334155;
  color: #f1f5f9;
}

.buyer-layout.dark .region-select option,
:global(html.dark) .region-select option {
  background-color: #0f172a;
  color: #f1f5f9;
}

.buyer-layout.dark .radio-option,
:global(html.dark) .radio-option {
  color: #e2e8f0;
}

.buyer-layout.dark .radio-option:hover,
:global(html.dark) .radio-option:hover {
  color: #60a5fa;
}

.buyer-layout.dark .radio-option.border-t,
:global(html.dark) .radio-option.border-t {
  border-color: #1e293b !important;
}

.buyer-layout.dark .discount-section,
:global(html.dark) .discount-section {
  border-top-color: #1e293b;
}

.buyer-layout.dark .discount-section label,
:global(html.dark) .discount-section label {
  color: #94a3b8;
}

.buyer-layout.dark .promo-input,
:global(html.dark) .promo-input {
  background: #0f172a;
  border-color: #334155;
  color: #f1f5f9;
}

.buyer-layout.dark .promo-input::placeholder,
:global(html.dark) .promo-input::placeholder {
  color: #64748b;
}

.buyer-layout.dark .summary-divider,
:global(html.dark) .summary-divider {
  background: #1e293b;
}

.buyer-layout.dark .summary-total,
:global(html.dark) .summary-total {
  border-top-color: #1e293b;
  border-bottom-color: #1e293b;
}

.buyer-layout.dark .total-label,
:global(html.dark) .total-label {
  color: #f1f5f9;
}

.buyer-layout.dark .total-amount,
:global(html.dark) .total-amount {
  color: #60a5fa;
}

.buyer-layout.dark .btn-secondary,
:global(html.dark) .btn-secondary {
  background: #1e293b;
  color: #e2e8f0;
  border-color: #334155;
}

.buyer-layout.dark .btn-secondary:hover,
:global(html.dark) .btn-secondary:hover {
  background: #334155;
  color: #ffffff;
}

.buyer-layout.dark .btn-danger,
:global(html.dark) .btn-danger {
  background: rgba(220, 38, 38, 0.2);
  color: #f87171;
  border-color: rgba(239, 68, 68, 0.35);
}

.buyer-layout.dark .btn-danger:hover,
:global(html.dark) .btn-danger:hover {
  background: rgba(220, 38, 38, 0.3);
  color: #fca5a5;
}

/* ════════════════════════════════════════════════════════════════ */
/* ── RESPONSIVE MEDIA QUERIES ── */
/* ════════════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
  .cart-container {
    margin-left: 0;
  }

  .cart-content {
    grid-template-columns: 1fr;
  }

  .cart-header {
    padding: 16px 20px;
  }

  .cart-body {
    padding: 20px 16px 40px;
  }

  .cart-item {
    grid-template-columns: 32px 70px minmax(100px, 1fr) auto;
    gap: 10px;
  }
}

</style>