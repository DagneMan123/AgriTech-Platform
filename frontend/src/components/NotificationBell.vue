<template>
  <div class="relative">
    <button @click="showNotifications = !showNotifications" class="relative p-2 text-gray-600 hover:text-gray-900">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span v-if="unreadCount > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1 -translate-y-1 bg-red-600 rounded-full">{{ unreadCount }}</span>
    </button>

    <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-10">
      <div class="p-4 border-b">
        <h3 class="font-semibold text-gray-900">Notifications</h3>
      </div>
      <div class="max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="p-4 text-center text-gray-500">
          No notifications
        </div>
        <div v-for="notif in notifications" :key="notif.id" class="p-4 border-b hover:bg-gray-50 cursor-pointer">
          <p class="text-sm text-gray-900 font-medium">{{ notif.title }}</p>
          <p class="text-sm text-gray-600">{{ notif.message }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ formatDate(notif.created_at) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { notificationAPI } from '@/api/notifications'
import { formatDistanceToNow } from 'date-fns'

const showNotifications = ref(false)
const notifications = ref<any[]>([])
const loading = ref(false)

const unreadCount = computed(() => 
  notifications.value.filter(n => !n.read_at).length
)

const formatDate = (date: string) => {
  return formatDistanceToNow(new Date(date), { addSuffix: true })
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const response = await notificationAPI.getNotifications(1, 10)
    notifications.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchNotifications()
  // Refresh every 30 seconds
  setInterval(fetchNotifications, 30000)
})
</script>
