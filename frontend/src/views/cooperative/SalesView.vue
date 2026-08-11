<template>
  <DashboardLayout>
    <template #title>Cooperative Sales</template>
    <template #subtitle>Track sales performance and member contributions</template>

    <!-- Sales Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <AppCard class="bg-gradient-to-br from-green-500 to-green-600 text-white">
        <div>
          <p class="text-green-100 text-sm font-medium">Total Revenue</p>
          <p class="text-4xl font-bold mt-2">${{ totalRevenue.toLocaleString() }}</p>
          <p class="text-green-100 text-xs mt-2">This month</p>
        </div>
      </AppCard>

      <AppCard class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
        <div>
          <p class="text-blue-100 text-sm font-medium">Total Orders</p>
          <p class="text-4xl font-bold mt-2">{{ totalOrders }}</p>
          <p class="text-blue-100 text-xs mt-2">This month</p>
        </div>
      </AppCard>

      <AppCard class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white">
        <div>
          <p class="text-yellow-100 text-sm font-medium">Avg Order Value</p>
          <p class="text-4xl font-bold mt-2">${{ avgOrderValue.toLocaleString() }}</p>
          <p class="text-yellow-100 text-xs mt-2">This month</p>
        </div>
      </AppCard>

      <AppCard class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
        <div>
          <p class="text-purple-100 text-sm font-medium">Active Vendors</p>
          <p class="text-4xl font-bold mt-2">{{ activeVendors }}</p>
          <p class="text-purple-100 text-xs mt-2">Contributing</p>
        </div>
      </AppCard>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <AppCard title="Revenue Trend">
        <LineChart />
      </AppCard>

      <AppCard title="Sales by Member">
        <BarChart />
      </AppCard>
    </div>

    <!-- Top Sellers -->
    <AppCard title="Top Sellers This Month">
      <AppTable :columns="topSellersColumns" :data="topSellers" />
    </AppCard>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AppCard from '@/components/common/AppCard.vue'
import AppTable from '@/components/common/AppTable.vue'
import LineChart from '@/components/charts/LineChart.vue'
import BarChart from '@/components/charts/BarChart.vue'

const totalRevenue = ref(45000)
const totalOrders = ref(156)
const avgOrderValue = ref(288)
const activeVendors = ref(23)

const topSellers = ref([
  { rank: 1, name: 'John Farmer', products: 45, revenue: '$12,500', growth: '+15%' },
  { rank: 2, name: 'Jane Smith', products: 38, revenue: '$10,200', growth: '+8%' },
  { rank: 3, name: 'Bob Johnson', products: 32, revenue: '$8,900', growth: '+5%' }
])

const topSellersColumns = [
  { key: 'rank', label: 'Rank' },
  { key: 'name', label: 'Member' },
  { key: 'products', label: 'Products Sold' },
  { key: 'revenue', label: 'Revenue' },
  { key: 'growth', label: 'Growth' }
]
</script>
