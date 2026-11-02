<template>
  <div class="buyer-layout" :class="{ 'dark': isDark }">
    <!-- Sidebar -->
    <BuyerSidebar @logout="handleLogout" />

    <!-- Main Content -->
    <div class="cart-container">
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
                <p class="item-supplier">
                  <i class="fas fa-store"></i> {{ item.supplier_name || 'Supplier' }}
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
              <div class="summary-row shipping-row">
                <span class="label">Shipping:</span>
                <div class="shipping-options">
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
                </div>
              </div>


              <div class="summary-row">
                <span class="label">Tax (16%):</span>
                <span class="value">ETB {{ formatNumber(tax) }}</span>
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

              <!-- Total -->
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
import { useTheme } from '@/composables/useTheme'
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
const router = useRouter()
const { isDark, toggleTheme } = useTheme()

// State
const deliveryDestinations = ETHIOPIAN_DELIVERY_DESTINATIONS
const ethiopianRegions = ETHIOPIAN_REGIONS
const savedDest = (typeof window !== 'undefined' && sessionStorage.getItem('checkout_destination')) || ''
const initialDest = resolveDestinationHubAndRegion(savedDest || authStore.user?.region || 'Addis Ababa')
const selectedDestination = ref<string>(`${initialDest.city}, ${initialDest.region}`)
const selectedRegion = selectedDestination
const selectedItems = ref<number[]>([])
const showSelectAll = ref(false)
const savedShip = (typeof window !== 'undefined' && sessionStorage.getItem('checkout_shipping')) as any
const shippingType = ref<'standard' | 'express' | 'overnight'>(savedShip || 'standard')
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

const selectedShippingRoutes = computed<OriginShippingRoute[]>(() => {
  return selectedShippingOption.value.routes || []
})

// Auto-fallback if cold-chain is filtered out for non-perishable goods
watch(
  dynamicShippingOptions,
  (newOptions) => {
    if (newOptions.length > 0 && !newOptions.some(o => o.key === shippingType.value)) {
      shippingType.value = newOptions[0].key as 'standard' | 'express' | 'overnight'
    }
  },
  { immediate: true }
)

const selectedShippingOption = computed(() => {
  return (
    dynamicShippingOptions.value.find(o => o.key === shippingType.value) ||
    dynamicShippingOptions.value[0] || { fee: 350 }
  )
})

const selectedShippingFee = computed(() => selectedShippingOption.value.fee)
const shippingCost = selectedShippingFee

onMounted(async () => {
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

// Computed properties
const subtotal = computed(() => {
  return buyerStore.cart.reduce((sum, item) => sum + (item.subtotal || 0), 0)
})

const tax = computed(() => {
  return Math.round(subtotal.value * 0.16)
})

const total = computed(() => {
  return subtotal.value + shippingCost.value + tax.value - promoDiscount.value
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
    console.log('Promo code applied: 10% discount')
  } else if (promoCode.value === 'SAVE500') {
    promoDiscount.value = 500
    console.log('Promo code applied: ETB 500 discount')
  } else {
    promoDiscount.value = 0
    alert('Invalid promo code')
  }
}

const saveCart = () => {
  console.log('Cart saved for later')
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

/* Dark Mode Overrides */
:global(html.dark) .buyer-layout,
.buyer-layout.dark {
  background-color: #0b0f19 !important;
  color: #f1f5f9;
}

:global(html.dark) .cart-container,
.buyer-layout.dark .cart-container {
  background-color: #0b0f19 !important;
  color: #f1f5f9;
}

:global(html.dark) .page-title,
.buyer-layout.dark .page-title {
  color: #f8fafc !important;
}

:global(html.dark) .page-subtitle,
.buyer-layout.dark .page-subtitle {
  color: #94a3b8 !important;
}

:global(html.dark) .cart-main,
.buyer-layout.dark .cart-main {
  background: #131b2e !important;
  border: 1px solid #1e293b !important;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4) !important;
}

:global(html.dark) .cart-items-header,
.buyer-layout.dark .cart-items-header {
  background: #131b2e !important;
  border-bottom: 1px solid #1e293b !important;
  color: #f8fafc !important;
}

:global(html.dark) .cart-items,
.buyer-layout.dark .cart-items {
  background: #131b2e !important;
}

:global(html.dark) .cart-item,
.buyer-layout.dark .cart-item {
  background: #131b2e !important;
  border-bottom: 1px solid #1e293b !important;
  color: #f8fafc !important;
}

:global(html.dark) .cart-item:hover,
.buyer-layout.dark .cart-item:hover {
  background: #182238 !important;
}

:global(html.dark) .cart-item-actions,
.buyer-layout.dark .cart-item-actions {
  background: #0f172a !important;
  border-top: 1px solid #1e293b !important;
}

:global(html.dark) .item-name,
.buyer-layout.dark .item-name {
  color: #f8fafc !important;
}

:global(html.dark) .item-supplier,
.buyer-layout.dark .item-supplier {
  color: #94a3b8 !important;
}

:global(html.dark) .item-price,
.buyer-layout.dark .item-price {
  color: #94a3b8 !important;
}

:global(html.dark) .quantity-control,
.buyer-layout.dark .quantity-control {
  background: #0b0f19 !important;
  border: 1px solid #334155 !important;
}

:global(html.dark) .qty-btn,
.buyer-layout.dark .qty-btn {
  background: transparent !important;
  color: #94a3b8 !important;
}

:global(html.dark) .qty-btn:hover,
.buyer-layout.dark .qty-btn:hover {
  background: #1e293b !important;
  color: #f8fafc !important;
}

:global(html.dark) .qty-input,
.buyer-layout.dark .qty-input {
  background: transparent !important;
  color: #f8fafc !important;
}

:global(html.dark) .summary-card,
.buyer-layout.dark .summary-card {
  background: #131b2e !important;
  border: 1px solid #1e293b !important;
  color: #f8fafc !important;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4) !important;
}

:global(html.dark) .summary-title,
.buyer-layout.dark .summary-title {
  color: #f8fafc !important;
  border-bottom: 1px solid #1e293b !important;
}

:global(html.dark) .summary-row,
.buyer-layout.dark .summary-row {
  color: #94a3b8 !important;
}

:global(html.dark) .summary-row .label,
.buyer-layout.dark .summary-row .label {
  color: #94a3b8 !important;
}

:global(html.dark) .summary-row .value,
.buyer-layout.dark .summary-row .value {
  color: #f8fafc !important;
}

:global(html.dark) .summary-total,
.buyer-layout.dark .summary-total {
  border-top: 2px solid #1e293b !important;
  border-bottom: 2px solid #1e293b !important;
}

:global(html.dark) .total-label,
.buyer-layout.dark .total-label {
  color: #f8fafc !important;
}

:global(html.dark) .radio-option,
.buyer-layout.dark .radio-option {
  color: #cbd5e1 !important;
}

:global(html.dark) .shipping-options,
.buyer-layout.dark .shipping-options {
  border-top: 1px solid #1e293b !important;
}

:global(html.dark) .shipping-summary-details,
.buyer-layout.dark .shipping-summary-details {
  background: #0b0f19 !important;
  border: 1px solid #1e293b !important;
  color: #94a3b8 !important;
}

:global(html.dark) .promo-input input,
.buyer-layout.dark .promo-input input {
  background: #0b0f19 !important;
  border: 1px solid #334155 !important;
  color: #f8fafc !important;
}

:global(html.dark) .empty-cart,
.buyer-layout.dark .empty-cart {
  background: #131b2e !important;
  border: 1px solid #1e293b !important;
  color: #f8fafc !important;
}

:global(html.dark) .empty-cart h2,
.buyer-layout.dark .empty-cart h2 {
  color: #f8fafc !important;
}

:global(html.dark) .empty-cart p,
.buyer-layout.dark .empty-cart p {
  color: #94a3b8 !important;
}

:global(html.dark) .empty-icon,
.buyer-layout.dark .empty-icon {
  background: #0b0f19 !important;
  color: #60a5fa !important;
}

:global(html.dark) .recommendations,
.buyer-layout.dark .recommendations {
  background: #131b2e !important;
  border: 1px solid #1e293b !important;
}

:global(html.dark) .recommendations-title,
.buyer-layout.dark .recommendations-title {
  color: #f8fafc !important;
}

:global(html.dark) .rec-card,
.buyer-layout.dark .rec-card {
  background: #0b0f19 !important;
  border: 1px solid #1e293b !important;
}

:global(html.dark) .rec-name,
.buyer-layout.dark .rec-name {
  color: #f8fafc !important;
}

:global(html.dark) .badge,
.buyer-layout.dark .badge {
  background: rgba(37, 99, 235, 0.2) !important;
  color: #60a5fa !important;
}

:global(html.dark) .btn-secondary,
.buyer-layout.dark .btn-secondary {
  background: #1e293b !important;
  border-color: #334155 !important;
  color: #e2e8f0 !important;
}

:global(html.dark) .action-btn,
.buyer-layout.dark .action-btn {
  background: #1e293b !important;
  border-color: #334155 !important;
  color: #94a3b8 !important;
}

:global(html.dark) .action-btn:hover,
.buyer-layout.dark .action-btn:hover {
  background: #334155 !important;
  color: #f8fafc !important;
}

/* Header */
.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 32px;
  background-color: #ffffff;
  border-bottom: 1px solid #e2e8f0;
  transition: background-color 0.3s ease, border-color 0.3s ease;
  gap: 20px;
}

:global(html.dark) .cart-header,
.buyer-layout.dark .cart-header {
  background-color: #131b2e !important;
  border-bottom: 1px solid #1e293b !important;
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
  padding: 0;
}

.cart-item {
  display: grid;
  grid-template-columns: 36px 80px minmax(120px, 1fr) 95px 115px 95px 86px;
  gap: 12px;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e5e7eb;
  transition: all 0.3s;
  box-sizing: border-box;
  width: 100%;
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
  line-height: 1.3;
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
  transition: all 0.3s;
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
  flex-shrink: 0;
  min-width: 82px;
  padding-right: 2px;
}

.action-btn {
  width: 36px;
  height: 36px;
  min-width: 36px;
  min-height: 36px;
  flex-shrink: 0;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  color: #6b7280;
  font-size: 14px;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
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

:global(html.dark) .region-select,
.buyer-layout.dark .region-select {
  background-color: #0b0f19 !important;
  border-color: #334155 !important;
  color: #f8fafc !important;
}

.shipping-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #e5e7eb;
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

.radio-option input {
  cursor: pointer;
  accent-color: #3b82f6;
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

.promo-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
  transition: all 0.3s;
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
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 16px;
}

.btn-checkout:hover:not(:disabled) {
  background: #1d4ed8;
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(37, 99, 235, 0.35);
}

.btn-checkout:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-full {
  width: 100%;
  margin-top: 12px;
}

.security-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
  padding: 12px;
  background: #f0fdf4;
  border-radius: 6px;
  font-size: 12px;
  color: #16a34a;
}

/* Recommendations */
.recommendations-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.rec-title {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 16px 0;
}

.rec-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.rec-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  transition: all 0.3s;
}

.rec-item:hover {
  border-color: #3b82f6;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.rec-image {
  width: 60px;
  height: 60px;
  background: #f3f4f6;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #d1d5db;
  flex-shrink: 0;
}

.rec-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.rec-name {
  font-size: 13px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.rec-price {
  font-size: 14px;
  font-weight: 700;
  color: #2563eb;
  margin: 0;
}

.btn-add-rec {
  align-self: flex-start;
  padding: 6px 12px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  margin-top: 4px;
}

.btn-add-rec:hover {
  background: #2563eb;
}

/* Button Styles */
.btn-primary {
  background: #3b82f6;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary:hover {
  background: #2563eb;
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
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 13px;
}

.btn-danger {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.btn-danger:hover {
  background: #fecaca;
}

/* Responsive */
@media (max-width: 1024px) {
  .cart-item {
    grid-template-columns: 32px 70px minmax(100px, 1fr) 85px 105px 85px 84px;
    gap: 8px;
    padding: 12px 14px;
  }

  .item-image {
    width: 70px;
    height: 70px;
  }
}

@media (max-width: 768px) {
  .cart-container {
    margin-left: 0;
    padding: 0;
  }

  .cart-header {
    padding: 20px 16px;
  }

  .cart-body {
    padding: 20px 16px 36px;
  }

  .cart-content {
    grid-template-columns: 1fr;
  }

  .cart-item {
    grid-template-columns: 32px 60px minmax(90px, 1fr) auto;
    gap: 8px;
    padding: 12px 8px;
  }

  .item-image {
    width: 60px;
    height: 60px;
  }

  .item-price,
  .item-subtotal {
    display: none;
  }

  .item-actions {
    display: flex !important;
    gap: 6px;
    min-width: unset;
  }

  .item-details {
    gap: 2px;
  }

  .item-name {
    font-size: 13px;
  }

  .item-supplier {
    font-size: 11px;
  }

  .summary-card {
    position: static;
  }

  .cart-item-actions {
    flex-wrap: wrap;
  }
}

@media (max-width: 480px) {
  .cart-container {
    padding: 0;
  }

  .cart-header {
    padding: 16px 12px;
  }

  .page-title {
    font-size: 22px;
  }

  .cart-body {
    padding: 16px 12px 28px;
  }

  .cart-item {
    grid-template-columns: 32px 50px minmax(80px, 1fr) auto;
    padding: 12px 8px;
  }

  .item-image {
    width: 50px;
    height: 50px;
    display: block;
  }
}



</style>


.active-formula-card .formula-details {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 10px;
  line-height: 1.4;
}

