<template>
  <div class="buyer-layout">
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
          <button @click="continueShopping" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Continue Shopping
          </button>
        </div>
      </div>

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
                  v-if="item.image_url"
                  :src="item.image_url"
                  :alt="item.product_name"
                  class="image"
                />
                <div v-else class="no-image">
                  <i class="fas fa-image"></i>
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
                <span class="unit-price">Ksh {{ formatNumber(item.unit_price || 0) }}</span>
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
                <span class="subtotal-amount">Ksh {{ formatNumber(item.subtotal || 0) }}</span>
              </div>

              <!-- Item Actions -->
              <div class="item-actions">
                <button
                  @click="addToWishlist(item.id)"
                  class="action-btn"
                  title="Add to Wishlist"
                >
                  <i class="fas fa-heart"></i>
                </button>
                <button
                  @click="removeFromCart(item.id)"
                  class="action-btn danger"
                  title="Remove from Cart"
                >
                  <i class="fas fa-trash"></i>
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
                <span class="value">Ksh {{ formatNumber(subtotal) }}</span>
              </div>
              <div class="summary-row">
                <span class="label">Shipping:</span>
                <div class="shipping-options">
                  <label class="radio-option">
                    <input
                      type="radio"
                      v-model="shippingType"
                      value="standard"
                      @change="updateShipping"
                    />
                    <span>Standard (Ksh {{ formatNumber(SHIPPING.STANDARD) }})</span>
                  </label>
                  <label class="radio-option">
                    <input
                      type="radio"
                      v-model="shippingType"
                      value="express"
                      @change="updateShipping"
                    />
                    <span>Express (Ksh {{ formatNumber(SHIPPING.EXPRESS) }})</span>
                  </label>
                  <label class="radio-option">
                    <input
                      type="radio"
                      v-model="shippingType"
                      value="overnight"
                      @change="updateShipping"
                    />
                    <span>Overnight (Ksh {{ formatNumber(SHIPPING.OVERNIGHT) }})</span>
                  </label>
                </div>
              </div>
              <div class="summary-row">
                <span class="label">Tax (16%):</span>
                <span class="value">Ksh {{ formatNumber(tax) }}</span>
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
                  <i class="fas fa-check"></i> Discount: -Ksh {{ formatNumber(promoDiscount) }}
                </div>
              </div>

              <div class="summary-divider"></div>

              <!-- Total -->
              <div class="summary-total">
                <span class="total-label">Total:</span>
                <span class="total-amount">Ksh {{ formatNumber(total) }}</span>
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

            <!-- Security Info -->
            <div class="security-info">
              <i class="fas fa-lock"></i>
              <span>Secure checkout. Your data is encrypted.</span>
            </div>
          </div>

          <!-- Recommendations -->
          <div class="recommendations-card">
            <h3 class="rec-title">You might also like</h3>
            <div class="rec-items">
              <div v-for="i in 3" :key="i" class="rec-item">
                <div class="rec-image">
                  <i class="fas fa-image"></i>
                </div>
                <div class="rec-info">
                  <p class="rec-name">Recommended Product {{ i }}</p>
                  <p class="rec-price">Ksh 2,500</p>
                  <button class="btn-add-rec">Add to Cart</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useBuyerStore } from '@/stores/buyerStore'
import { useAuthStore } from '@/stores/authStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

// Constants
const SHIPPING = {
  STANDARD: 500,
  EXPRESS: 1000,
  OVERNIGHT: 2000
}

// Stores and Router
const buyerStore = useBuyerStore()
const authStore = useAuthStore()
const router = useRouter()

// State
const selectedItems = ref<number[]>([])
const showSelectAll = ref(false)
const shippingType = ref<'standard' | 'express' | 'overnight'>('standard')
const shippingCost = ref(SHIPPING.STANDARD)
const promoCode = ref('')
const promoDiscount = ref(0)

onMounted(async () => {
  try {
    await buyerStore.fetchCart()
  } catch (error) {
    console.error('Failed to load cart:', error)
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

const addToWishlist = async (itemId: number) => {
  try {
    await buyerStore.addToWishlist(itemId)
    console.log('Added to wishlist')
  } catch (error) {
    console.error('Failed to add to wishlist:', error)
  }
}

const updateShipping = () => {
  const amounts: Record<string, number> = {
    standard: SHIPPING.STANDARD,
    express: SHIPPING.EXPRESS,
    overnight: SHIPPING.OVERNIGHT
  }
  shippingCost.value = amounts[shippingType.value] || SHIPPING.STANDARD
}

const applyPromoCode = () => {
  if (promoCode.value === 'SAVE10') {
    promoDiscount.value = Math.round(subtotal.value * 0.1)
    console.log('Promo code applied: 10% discount')
  } else if (promoCode.value === 'SAVE500') {
    promoDiscount.value = 500
    console.log('Promo code applied: Ksh 500 discount')
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

const proceedToCheckout = async () => {
  try {
    await buyerStore.checkout({
      shipping_type: shippingType.value,
      shipping_cost: shippingCost.value,
      tax_amount: tax.value,
      promo_discount: promoDiscount.value
    })
    router.push('/buyer/payments')
  } catch (error) {
    console.error('Checkout failed:', error)
  }
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
  padding: 40px 20px;
}

/* Header */
.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 40px;
  gap: 20px;
}

.page-title {
  font-size: 36px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.page-subtitle {
  font-size: 16px;
  color: #6b7280;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
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
  overflow: hidden;
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
  grid-template-columns: 40px 100px 1fr 120px 150px 120px 100px;
  gap: 16px;
  align-items: center;
  padding: 16px 24px;
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
  width: 100px;
  height: 100px;
  background: #f3f4f6;
  border-radius: 6px;
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
  font-size: 32px;
  color: #d1d5db;
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
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
  color: #10b981;
}

.item-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  min-width: 100px;
}

.action-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  color: #6b7280;
  font-size: 14px;
  transition: all 0.3s;
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
  align-items: center;
  gap: 8px;
  font-size: 13px;
  cursor: pointer;
  color: #1f2937;
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
  color: #10b981;
}

.btn-checkout {
  width: 100%;
  padding: 14px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
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
  color: #10b981;
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
    grid-template-columns: 40px 80px 1fr 100px 80px 80px;
    gap: 8px;
    padding: 12px 16px;
  }

  .item-image {
    width: 80px;
    height: 80px;
  }
}

@media (max-width: 768px) {
  .cart-container {
    margin-left: 0;
    padding: 20px 15px;
  }

  .cart-content {
    grid-template-columns: 1fr;
  }

  .cart-item {
    grid-template-columns: 40px 60px 1fr 60px;
    gap: 8px;
    padding: 12px;
  }

  .item-price,
  .item-quantity,
  .item-subtotal,
  .item-actions {
    display: none;
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
    padding: 16px 12px;
  }

  .page-title {
    font-size: 24px;
  }

  .cart-item {
    grid-template-columns: 40px 1fr;
    padding: 12px 8px;
  }

  .item-image {
    display: none;
  }
}
</style>

