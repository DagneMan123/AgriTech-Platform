<template>
  <div class="dashboard-layout min-h-screen" :style="{ backgroundColor: 'var(--bg-light-secondary)' }">
    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 w-64 h-screen overflow-y-auto shadow-lg" style="background-color: var(--bg-dark-secondary); color: var(--text-dark-primary); border-right: 1px solid var(--border-light);">
      <!-- Logo -->
      <div class="p-6 border-b" style="border-color: var(--border-light);">
        <RouterLink to="/" class="flex items-center space-x-2">
          <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-blue-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-lg">A</span>
          </div>
          <div>
            <div class="text-white font-bold">AgriConnect</div>
            <div class="text-xs text-gray-400">Dashboard</div>
          </div>
        </RouterLink>
      </div>

      <!-- Navigation -->
      <nav class="mt-6">
        <slot name="sidebar"></slot>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="ml-64">
      <!-- Top Header -->
      <header class="sticky top-0 z-40 shadow-sm" style="background-color: var(--bg-light); border-bottom: 1px solid var(--border-light);">
        <div class="px-8 py-4 flex justify-between items-center">
          <div class="flex-1">
            <h1 class="text-2xl font-bold" style="color: var(--text-light-primary);">
              <slot name="title">Dashboard</slot>
            </h1>
            <p class="text-sm mt-1" style="color: var(--text-light-secondary);">
              <slot name="subtitle">Welcome back to your dashboard</slot>
            </p>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <!-- Theme Toggle -->
            <ThemeToggle />

            <!-- Notifications -->
            <button class="relative p-2 transition" style="color: var(--text-light-secondary);" @mouseenter="$event.target.style.color = 'var(--text-light-primary)'" @mouseleave="$event.target.style.color = 'var(--text-light-secondary)'">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Profile Dropdown -->
            <div class="flex items-center space-x-3 pl-4" style="border-left: 1px solid var(--border-light);">
              <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                {{ userInitial }}
              </div>
              <div>
                <p class="text-sm font-semibold" style="color: var(--text-light-primary);">{{ userName }}</p>
                <p class="text-xs" style="color: var(--text-light-secondary);">{{ userRole }}</p>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-8">
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import ThemeToggle from '@/components/Theme/ThemeToggle.vue'

const authStore = useAuthStore()

const userName = computed(() => authStore.user?.name || 'User')
const userRole = computed(() => authStore.user?.role || 'Guest')
const userInitial = computed(() => userName.value.charAt(0).toUpperCase())
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease;
}

aside {
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

aside::-webkit-scrollbar {
  width: 8px;
}

aside::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

aside::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
  transition: background 0.3s ease;
}

aside::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

header {
  transition: background-color 0.3s ease, border-color 0.3s ease;
}
</style>
