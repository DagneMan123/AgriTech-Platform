<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <nav class="hidden md:flex w-64 bg-gray-900 text-white flex-col overflow-y-auto">
      <div class="p-6 border-b border-gray-700">
        <h1 class="text-2xl font-bold text-green-400">AgriConnect</h1>
        <p class="text-gray-400 text-sm">v1.0</p>
      </div>

      <!-- User Info -->
      <div class="px-6 py-4 border-b border-gray-700">
        <p class="font-semibold">{{ authStore.user?.full_name }}</p>
        <p class="text-gray-400 text-sm capitalize">{{ authStore.user?.role }}</p>
      </div>

      <!-- Navigation -->
      <div class="flex-1 px-4 py-6">
        <nav-menu :role="authStore.userRole" />
      </div>

      <!-- Logout -->
      <div class="p-4 border-t border-gray-700">
        <button @click="handleLogout" class="w-full px-4 py-2 text-left hover:bg-gray-800 rounded transition">
          <span class="mr-2">👋</span> Logout
        </button>
      </div>
    </nav>

    <!-- Mobile Menu Button -->
    <div class="md:hidden fixed top-0 left-0 right-0 bg-gray-900 text-white z-50 flex items-center justify-between p-4">
      <h1 class="text-xl font-bold text-green-400">AgriConnect</h1>
      <button @click="mobileMenuOpen = !mobileMenuOpen">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header class="bg-white shadow-sm pt-16 md:pt-0">
        <div class="px-4 py-4 flex justify-between items-center">
          <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <notification-bell />
            
            <!-- Profile Dropdown -->
            <div class="relative">
              <button @click="profileDropdown = !profileDropdown" class="flex items-center space-x-2 hover:bg-gray-100 px-3 py-2 rounded">
                <img :src="profileImage" :alt="authStore.user?.full_name" class="w-8 h-8 rounded-full">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              
              <div v-if="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10">
                <router-link to="/app/profile" class="block px-4 py-2 hover:bg-gray-100 first:rounded-t-lg">
                  Profile
                </router-link>
                <button @click="handleLogout" class="block w-full text-left px-4 py-2 hover:bg-gray-100 last:rounded-b-lg">
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto">
        <div class="px-4 py-6 md:px-8">
          <router-view />
        </div>
      </main>
    </div>

    <!-- Mobile Menu -->
    <transition name="slide">
      <div v-if="mobileMenuOpen" class="fixed inset-0 bg-gray-900 text-white z-40 pt-20">
        <nav-menu :role="authStore.userRole" />
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import NavMenu from '@/components/NavMenu.vue'
import NotificationBell from '@/components/NotificationBell.vue'

const router = useRouter()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)
const profileDropdown = ref(false)
const profileImage = 'https://api.dicebear.com/7.x/avataaars/svg?seed=' + authStore.user?.id

const handleLogout = async () => {
  await authStore.logout()
  router.push('/auth/login')
}
</script>
