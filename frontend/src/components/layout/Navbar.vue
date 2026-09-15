<template>
  <nav class="navbar" style="background-color: var(--bg-light); border-bottom: 1px solid var(--border-light);">
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
  transition: background-color 0.3s ease, border-color 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

html.dark .navbar {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}
</style>
