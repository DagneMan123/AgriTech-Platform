<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>

    <div v-if="buyerStore.orders.length === 0" class="card text-center py-12">
      <p class="text-gray-500">No orders yet</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="order in buyerStore.orders" :key="order.id" class="card">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="font-bold">Order #{{ order.id }}</h3>
            <p class="text-gray-600">{{ order.items_count }} items</p>
          </div>
          <span :class="['px-3 py-1 rounded text-sm', orderStatusClass(order.status)]">{{ order.status }}</span>
        </div>
        <p class="text-green-600 font-bold mt-2">Ksh {{ order.total_amount }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useBuyerStore } from '@/stores/buyerStore'
import { onMounted } from 'vue'

const buyerStore = useBuyerStore()

onMounted(async () => {
  try {
    await buyerStore.fetchOrders()
  } catch (error) {
    console.error('Failed to load orders:', error)
  }
})

const orderStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    confirmed: 'bg-blue-100 text-blue-700',
    shipped: 'bg-purple-100 text-purple-700',
    delivered: 'bg-green-100 text-green-700'
  }
  return classes[status] || classes.pending
}
</script>
