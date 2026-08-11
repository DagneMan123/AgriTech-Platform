<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Cart Items -->
      <div class="lg:col-span-2">
        <div v-if="buyerStore.cart.length === 0" class="card text-center py-12">
          <p class="text-gray-500 mb-4">Your cart is empty</p>
          <router-link to="/app/buyer/marketplace" class="btn-primary">
            Continue Shopping
          </router-link>
        </div>

        <div v-else class="space-y-4">
          <div v-for="item in buyerStore.cart" :key="item.id" class="card flex items-center justify-between">
            <div class="flex-1">
              <h3 class="font-bold text-gray-900">{{ item.product_name }}</h3>
              <p class="text-gray-600">{{ item.product_price }} x {{ item.quantity }}</p>
            </div>
            <p class="text-lg font-bold text-green-600">Ksh {{ item.subtotal }}</p>
            <button @click="removeFromCart(item.id)" class="btn-danger px-3 py-2 ml-4">
              Remove
            </button>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="card">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Order Summary</h3>
        <div class="space-y-3 border-b pb-4 mb-4">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span>Ksh {{ subtotal }}</span>
          </div>
          <div class="flex justify-between">
            <span>Shipping</span>
            <span>Ksh {{ shippingCost }}</span>
          </div>
          <div class="flex justify-between">
            <span>Tax</span>
            <span>Ksh {{ tax }}</span>
          </div>
        </div>
        <div class="flex justify-between text-lg font-bold mb-6">
          <span>Total</span>
          <span class="text-green-600">Ksh {{ total }}</span>
        </div>
        <button @click="checkout" :disabled="buyerStore.cart.length === 0" class="btn-primary w-full px-4 py-3">
          Proceed to Checkout
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useBuyerStore } from '@/stores/buyerStore'

const router = useRouter()
const buyerStore = useBuyerStore()

onMounted(async () => {
  try {
    await buyerStore.fetchCart()
  } catch (error) {
    console.error('Failed to load cart:', error)
  }
})

const subtotal = computed(() => {
  return buyerStore.cart.reduce((sum, item) => sum + (item.subtotal || 0), 0)
})

const shippingCost = ref(500)
const tax = computed(() => Math.round(subtotal.value * 0.16))
const total = computed(() => subtotal.value + shippingCost.value + tax.value)

const removeFromCart = async (id: number) => {
  try {
    await buyerStore.removeFromCart(id)
  } catch (error) {
    console.error('Failed to remove from cart:', error)
  }
}

const checkout = async () => {
  try {
    await buyerStore.checkout({
      shipping_cost: shippingCost.value,
      tax_amount: tax.value
    })
    router.push('/app/buyer/orders')
  } catch (error) {
    console.error('Checkout failed:', error)
  }
}
</script>
