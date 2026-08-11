<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Financial Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <stat-card title="Active Loans" :value="24" icon="💳" />
      <stat-card title="Insurance Policies" :value="18" icon="🛡️" />
      <stat-card title="Total Disbursed" :value="'Ksh 2.5M'" icon="💰" />
    </div>
  </div>
</template>

<script setup lang="ts">
import StatCard from '@/components/StatCard.vue'
</script>
