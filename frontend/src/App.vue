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
  // Initialize theme from localStorage
  // The store already syncs to DOM via its watcher with immediate: true
  themeStore.initializeTheme()
  
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
  transition: background-color 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
              color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(html.dark) .app-container {
  background-color: var(--bg-dark);
  color: var(--text-dark-primary);
}
</style>
