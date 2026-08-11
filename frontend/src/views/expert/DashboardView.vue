<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Expert Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <stat-card title="Consultations" :value="12" icon="💬" />
      <stat-card title="Articles" :value="8" icon="📰" />
      <stat-card title="Training Sessions" :value="5" icon="📚" />
    </div>
  </div>
</template>

<script setup lang="ts">
import StatCard from '@/components/StatCard.vue'
</script>
