<template>
  <div class="card flex items-start space-x-4">
    <div class="text-2xl">✓</div>
    <div>
      <h4 class="font-bold text-gray-900">{{ title }}</h4>
      <p class="text-gray-600">{{ description }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string
  description: string
}>()
</script>
