<template>
  <DashboardLayout>
    <template #title>Cooperative Dashboard</template>
    <template #subtitle>Manage your cooperative activities and members</template>
    <template #sidebar>
      <nav class="space-y-2">
        <RouterLink
          to="/cooperative/dashboard"
          class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition"
          active-class="bg-green-600"
        >
          Dashboard
        </RouterLink>
        <RouterLink
          to="/cooperative/members"
          class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition"
          active-class="bg-green-600"
        >
          Members
        </RouterLink>
        <RouterLink
          to="/cooperative/sales"
          class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition"
          active-class="bg-green-600"
        >
          Sales
        </RouterLink>
        <RouterLink
          to="/cooperative/reports"
          class="block px-4 py-2 rounded-lg hover:bg-gray-800 transition"
          active-class="bg-green-600"
        >
          Reports
        </RouterLink>
      </nav>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Members -->
      <AppCard class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-blue-100 text-sm font-medium">Total Members</p>
            <p class="text-4xl font-bold mt-2">{{ stats.totalMembers }}</p>
          </div>
          <svg class="w-12 h-12 opacity-20" fill="currentColor" viewBox="0 0 20 20">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v2h8v-2zM16 11a2 2 0 11-4 0 2 2 0 014 0zM3 14h2.751A4.997 4.997 0 0113.466 15H1v-2a6 6 0 0112-6v3h3.154a7 7 0 11-14.666 0z" />
          </svg>
        </div>
      </AppCard>

      <!-- Total Sales -->
      <AppCard class="bg-gradient-to-br from-green-500 to-green-600 text-white">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-green-100 text-sm font-medium">Total Sales</p>
            <p class="text-4xl font-bold mt-2">${{ stats.totalSales.toLocaleString() }}</p>
          </div>
          <svg class="w-12 h-12 opacity-20" fill="currentColor" viewBox="0 0 20 20">
            <path d="M8.16 5.314l4.897-1.596A1 1 0 0114.59 4.747l1.293 5.802a1 1 0 01-.835 1.22l-4.898 1.596a1 1 0 01-1.22-.834l-1.293-5.803a1 1 0 01.834-1.22z" />
          </svg>
        </div>
      </AppCard>

      <!-- Active Orders -->
      <AppCard class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-yellow-100 text-sm font-medium">Active Orders</p>
            <p class="text-4xl font-bold mt-2">{{ stats.activeOrders }}</p>
          </div>
          <svg class="w-12 h-12 opacity-20" fill="currentColor" viewBox="0 0 20 20">
            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3z" />
          </svg>
        </div>
      </AppCard>

      <!-- Member Engagement -->
      <AppCard class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-purple-100 text-sm font-medium">Engagement Rate</p>
            <p class="text-4xl font-bold mt-2">{{ stats.engagementRate }}%</p>
          </div>
          <svg class="w-12 h-12 opacity-20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2 1 1 0 010 2zM9 9a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2zM9 15a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2zM5.5 11a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
          </svg>
        </div>
      </AppCard>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <AppCard title="Sales Trend">
        <LineChart />
      </AppCard>

      <AppCard title="Member Activity">
        <BarChart />
      </AppCard>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AppCard from '@/components/common/AppCard.vue'
import LineChart from '@/components/charts/LineChart.vue'
import BarChart from '@/components/charts/BarChart.vue'
import { RouterLink } from 'vue-router'

const stats = ref({
  totalMembers: 145,
  totalSales: 125000,
  activeOrders: 28,
  engagementRate: 87
})
</script>
