<template>
  <button
    @click="toggleThemeMode"
    class="theme-toggle"
    type="button"
    :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    :title="isDark ? 'Light Mode' : 'Dark Mode'"
  >
    <span class="toggle-icon">{{ isDark ? '☀️' : '🌙' }}</span>
    <span class="toggle-text">{{ isDark ? 'Light' : 'Dark' }}</span>
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
.theme-toggle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background-color: var(--bg-light-tertiary);
  border: 2px solid var(--border-light);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
  z-index: 50;
  color: var(--text-light-primary);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.theme-toggle:hover {
  background-color: var(--bg-light-secondary);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border-color: #60a5fa;
}

.theme-toggle:active {
  transform: translateY(0);
}

html.dark .theme-toggle {
  background-color: var(--bg-dark-tertiary);
  border-color: var(--border-light);
  color: var(--text-dark-primary);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

html.dark .theme-toggle:hover {
  background-color: var(--bg-dark-secondary);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
  border-color: #60a5fa;
}

.toggle-icon {
  display: inline-block;
  font-size: 18px;
  line-height: 1;
  animation: fadeInScale 0.3s ease;
}

.toggle-text {
  display: inline-block;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@media (max-width: 640px) {
  .toggle-text {
    display: none;
  }
  
  .theme-toggle {
    padding: 0.5rem;
  }
}
</style>
