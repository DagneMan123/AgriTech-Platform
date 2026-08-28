<template>
  <nav class="navbar">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
      <div class="flex items-center">
        <h1 class="text-2xl font-bold text-green-600">AgriConnect</h1>
      </div>
      <div class="flex items-center gap-4">
        <ThemeToggle />
        <slot name="right" />
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import ThemeToggle from '@/components/Theme/ThemeToggle.vue'
</script>

<style scoped>
.navbar {
  background-color: var(--bg-light);
  box-shadow: var(--shadow-md);
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

html.dark .navbar {
  background-color: var(--bg-dark-secondary);
  box-shadow: var(--shadow-dark-md);
}
</style>
