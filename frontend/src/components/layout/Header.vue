<template>
  <header class="bg-white shadow-sm p-6">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-gray-900">{{ title }}</h1>
      <slot />
    </div>
  </header>
</template>

<script setup lang="ts">
interface Props {
  title: string
}

defineProps<Props>()
</script>
