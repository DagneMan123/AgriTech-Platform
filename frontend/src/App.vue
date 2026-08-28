<template>
  <div class="app-container">
    <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component :is="Component" :key="$route.fullPath" />
      </transition>
    </router-view>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { onMounted } from 'vue'

const authStore = useAuthStore()
const themeStore = useThemeStore()

onMounted(() => {
  // Initialize theme
  themeStore.initializeTheme()
  
  // Watch for system theme changes
  themeStore.watchSystemTheme()
  
  // Check authentication token
  authStore.checkToken()
})
</script>

<style scoped>
.app-container {
  min-height: 100vh;
  width: 100%;
  background-color: var(--bg-light);
  color: var(--text-light-primary);
  transition: background-color 0.3s ease, color 0.3s ease;
}

html.dark .app-container {
  background-color: var(--bg-dark);
  color: var(--text-dark-primary);
}
</style>
