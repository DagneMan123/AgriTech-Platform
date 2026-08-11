<template>
  <div class="card hover:shadow-lg transition">
    <div class="text-4xl mb-4">{{ icon }}</div>
    <h4 class="text-xl font-bold text-gray-900 mb-2">{{ title }}</h4>
    <p class="text-gray-600">{{ description }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  icon: string
  title: string
  description: string
}>()
</script>
