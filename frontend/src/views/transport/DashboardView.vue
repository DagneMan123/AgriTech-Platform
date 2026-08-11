<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Transport Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <stat-card title="Active Deliveries" :value="5" icon="🚚" />
      <stat-card title="Vehicles" :value="8" icon="🚗" />
      <stat-card title="Drivers" :value="12" icon="👨‍✈️" />
    </div>
  </div>
</template>

<script setup lang="ts">
import StatCard from '@/components/StatCard.vue'
</script>
