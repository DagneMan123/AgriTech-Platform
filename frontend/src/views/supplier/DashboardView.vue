<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Supplier Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <stat-card title="Products" :value="10" icon="📦" />
      <stat-card title="Orders" :value="24" icon="📋" />
      <stat-card title="Revenue" :value="'Ksh 450K'" icon="💰" />
      <stat-card title="Warehouses" :value="3" icon="🏭" />
    </div>
    <div class="flex space-x-4">
      <router-link to="/app/supplier/products" class="btn-primary">View Products</router-link>
      <router-link to="/app/supplier/orders" class="btn-secondary">View Orders</router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import StatCard from '@/components/StatCard.vue'
</script>
