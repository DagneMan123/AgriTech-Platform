<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <stat-card title="Total Users" :value="256" icon="👥" />
      <stat-card title="Total Orders" :value="1024" icon="📋" />
      <stat-card title="Revenue" :value="'Ksh 5.2M'" icon="💰" />
      <stat-card title="Active Deliveries" :value="42" icon="🚚" />
    </div>
    <div class="flex space-x-4">
      <router-link to="/app/admin/users" class="btn-primary">Manage Users</router-link>
      <router-link to="/app/admin/reports" class="btn-secondary">View Reports</router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import StatCard from '@/components/StatCard.vue'
</script>
