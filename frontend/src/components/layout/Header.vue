<template>
  <header class="header">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold">{{ title }}</h1>
      <div class="flex items-center gap-4">
        <ThemeToggle />
        <slot />
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import ThemeToggle from '@/components/Theme/ThemeToggle.vue'

interface Props {
  title: string
}

defineProps<Props>()
</script>

<style scoped>
.header {
  background-color: var(--bg-light);
  box-shadow: var(--shadow-sm);
  padding: 1.5rem;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
  color: var(--text-light-primary);
}

html.dark .header {
  background-color: var(--bg-dark-secondary);
  color: var(--text-dark-primary);
  box-shadow: var(--shadow-dark-md);
}
</style>
