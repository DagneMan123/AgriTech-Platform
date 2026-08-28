<template>
  <button
    @click="toggleThemeMode"
    class="toggle-btn"
    type="button"
    :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
  >
    <span class="toggle-text">{{ isDark ? '☀️ Light' : '🌙 Dark' }}</span>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useThemeStore } from '@/stores/themeStore'

const themeStore = useThemeStore()
const isDark = computed(() => themeStore.isDark)

const toggleThemeMode = () => {
  themeStore.toggleTheme()
}
</script>

<style scoped>
.toggle-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  background-color: #f3f4f6;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
  z-index: 50;
}

.toggle-btn:hover {
  background-color: #e5e7eb;
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.toggle-btn:active {
  transform: scale(0.95);
}

html.dark .toggle-btn {
  background-color: #2d3748;
  border-color: #4a5568;
  color: #f7fafc;
}

html.dark .toggle-btn:hover {
  background-color: #4a5568;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.toggle-text {
  display: inline-block;
}
</style>
