<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Farmer Dashboard</h1>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <stat-card title="Farms" :value="dashboard?.farms_count || 0" icon="🌾" />
      <stat-card title="Crops" :value="dashboard?.crops_count || 0" icon="🌱" />
      <stat-card title="Products" :value="dashboard?.products_count || 0" icon="📦" />
      <stat-card title="Orders" :value="dashboard?.orders_count || 0" icon="📋" />
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Orders -->
      <div class="card">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Recent Orders</h3>
        <div v-if="dashboard?.recent_orders?.length === 0" class="text-gray-500">No orders yet</div>
        <div v-else class="space-y-2">
          <div v-for="order in dashboard?.recent_orders" :key="order.id" class="flex justify-between items-center p-2 bg-gray-50 rounded">
            <span class="font-medium">Order #{{ order.id }}</span>
            <span class="text-sm text-gray-600">{{ formatDate(order.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Market Prices -->
      <div class="card">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Market Prices</h3>
        <div v-if="dashboard?.market_prices?.length === 0" class="text-gray-500">No prices available</div>
        <div v-else class="space-y-2">
          <div v-for="price in dashboard?.market_prices" :key="price.id" class="flex justify-between items-center p-2 bg-gray-50 rounded">
            <span class="font-medium">{{ price.product_name }}</span>
            <span class="text-green-600 font-bold">{{ price.price }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
      <h3 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <router-link to="/app/farmer/farms/create" class="btn-primary px-4 py-2 text-center">
          Create Farm
        </router-link>
        <router-link to="/app/farmer/products/create" class="btn-primary px-4 py-2 text-center">
          Add Product
        </router-link>
        <router-link to="/app/farmer/consultations" class="btn-secondary px-4 py-2 text-center">
          Get Consultation
        </router-link>
        <router-link to="/app/farmer/loans" class="btn-secondary px-4 py-2 text-center">
          Request Loan
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useFarmerStore } from '@/stores/farmerStore'
import StatCard from '@/components/StatCard.vue'
import { formatDistanceToNow } from 'date-fns'

const farmerStore = useFarmerStore()
const dashboard = ref<any>(null)

const formatDate = (date: string) => {
  return formatDistanceToNow(new Date(date), { addSuffix: true })
}

onMounted(async () => {
  try {
    dashboard.value = await farmerStore.fetchDashboard()
  } catch (error) {
    console.error('Failed to load dashboard:', error)
  }
})
</script>
