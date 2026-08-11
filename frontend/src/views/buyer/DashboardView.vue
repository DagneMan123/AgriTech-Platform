<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Buyer Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <stat-card title="Total Orders" :value="dashboard?.total_orders || 0" icon="📋" />
      <stat-card title="In Transit" :value="dashboard?.orders_in_transit || 0" icon="🚚" />
      <stat-card title="Wishlist Items" :value="dashboard?.wishlist_count || 0" icon="❤️" />
      <stat-card title="Total Spent" :value="`Ksh ${dashboard?.total_spent || 0}`" icon="💰" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="card">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Recent Orders</h3>
        <div v-if="dashboard?.recent_orders?.length === 0" class="text-gray-500">No orders yet</div>
        <div v-else class="space-y-3">
          <div v-for="order in dashboard?.recent_orders" :key="order.id" class="flex justify-between items-center p-3 bg-gray-50 rounded">
            <span>Order #{{ order.id }}</span>
            <span class="text-sm text-gray-600">{{ formatDate(order.created_at) }}</span>
          </div>
        </div>
      </div>

      <div class="card">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h3>
        <div class="space-y-2">
          <router-link to="/app/buyer/marketplace" class="btn-primary w-full px-4 py-2 text-center">
            Browse Marketplace
          </router-link>
          <router-link to="/app/buyer/cart" class="btn-secondary w-full px-4 py-2 text-center">
            View Cart
          </router-link>
          <router-link to="/app/buyer/wishlist" class="btn-secondary w-full px-4 py-2 text-center">
            My Wishlist
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useBuyerStore } from '@/stores/buyerStore'
import StatCard from '@/components/StatCard.vue'
import { formatDistanceToNow } from 'date-fns'

const buyerStore = useBuyerStore()
const dashboard = ref<any>(null)

const formatDate = (date: string) => {
  return formatDistanceToNow(new Date(date), { addSuffix: true })
}

onMounted(async () => {
  try {
    dashboard.value = await buyerStore.fetchDashboard()
  } catch (error) {
    console.error('Failed to load dashboard:', error)
  }
})
</script>
