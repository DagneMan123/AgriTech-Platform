<template>
  <div class="flex items-center gap-2 sm:gap-3">
    <!-- Theme Toggle -->
    <button
      type="button"
      @click="toggleTheme"
      :class="[
        'w-9 h-9 sm:w-10 sm:h-10 rounded-xl border flex items-center justify-center transition-all cursor-pointer shadow-xs',
        isDark 
          ? 'bg-slate-800/80 border-slate-700 text-amber-400 hover:bg-slate-800 hover:border-slate-600' 
          : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300'
      ]"
      :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
      aria-label="Toggle Theme"
    >
      <Sun v-if="isDark" class="w-4 h-4 text-amber-400" />
      <Moon v-else class="w-4 h-4 text-slate-600" />
    </button>

    <!-- ── NOTIFICATION BELL ICON ── -->
    <div class="relative" ref="notificationDropdownRef">
      <button
        type="button"
        @click="toggleNotifications"
        :class="[
          'relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl border flex items-center justify-center transition-all cursor-pointer shadow-xs',
          showNotifications
            ? isDark ? 'bg-blue-600/20 border-blue-500 text-blue-400' : 'bg-blue-50 border-blue-300 text-blue-600'
            : isDark ? 'bg-slate-800/80 border-slate-700 text-slate-300 hover:bg-slate-800' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'
        ]"
        title="Notifications"
        aria-label="Notifications"
      >
        <Bell class="w-4 h-4 sm:w-5 sm:h-5 transition-transform group-hover:scale-110" />

        <!-- Unread Badge Counter -->
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-600 text-white text-[10px] font-extrabold flex items-center justify-center rounded-full ring-2 ring-white dark:ring-slate-900 shadow-sm animate-pulse"
        >
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </span>
      </button>

      <!-- Notifications Dropdown Menu -->
      <transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div
          v-if="showNotifications"
          :class="[
            'absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl border shadow-xl z-50 overflow-hidden',
            isDark ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-800'
          ]"
        >
          <!-- Dropdown Header -->
          <div :class="['p-3.5 border-b flex items-center justify-between', isDark ? 'border-slate-800 bg-slate-900/60' : 'border-slate-100 bg-slate-50/80']">
            <div class="flex items-center gap-2">
              <span class="font-bold text-xs uppercase tracking-wider">Notifications</span>
              <span
                v-if="unreadCount > 0"
                class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-600 text-white"
              >
                {{ unreadCount }} new
              </span>
            </div>
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
            >
              Mark all read
            </button>
          </div>

          <!-- Notification Items List -->
          <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
            <div v-if="loading" class="p-4 space-y-3">
              <div v-for="i in 3" :key="i" class="animate-pulse flex gap-3">
                <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 shrink-0"></div>
                <div class="flex-1 space-y-1.5">
                  <div class="w-3/4 h-3 bg-slate-200 dark:bg-slate-700 rounded"></div>
                  <div class="w-1/2 h-2.5 bg-slate-100 dark:bg-slate-800 rounded"></div>
                </div>
              </div>
            </div>

            <div v-else-if="notifications.length === 0" class="p-8 text-center">
              <div class="w-10 h-10 rounded-full bg-blue-500/10 text-blue-500 flex items-center justify-center mx-auto mb-2">
                <BellOff class="w-5 h-5" />
              </div>
              <p class="text-xs font-semibold mb-0.5">No notifications</p>
              <p class="text-[11px] text-slate-400">You're all caught up on commercial alerts.</p>
            </div>

            <div
              v-for="item in notifications"
              :key="item.id"
              @click="handleNotificationClick(item)"
              :class="[
                'p-3 transition-colors flex items-start gap-3 cursor-pointer group',
                !item.read_at 
                  ? isDark ? 'bg-blue-600/10 hover:bg-blue-600/15' : 'bg-blue-50/60 hover:bg-blue-50' 
                  : isDark ? 'hover:bg-slate-800/50' : 'hover:bg-slate-50'
              ]"
            >
              <!-- Icon Indicator -->
              <div
                :class="[
                  'w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5',
                  !item.read_at
                    ? 'bg-blue-600 text-white shadow-xs'
                    : isDark ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-500'
                ]"
              >
                <Bell class="w-4 h-4" />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1 mb-0.5">
                  <span :class="['text-xs truncate font-semibold', !item.read_at ? 'text-blue-600 dark:text-blue-400 font-bold' : '']">
                    {{ item.title }}
                  </span>
                  <span class="text-[10px] text-slate-400 shrink-0">{{ item.time_formatted }}</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                  {{ item.message }}
                </p>
              </div>

              <!-- Unread Blue Dot -->
              <div v-if="!item.read_at" class="w-2 h-2 rounded-full bg-blue-600 shrink-0 mt-1.5"></div>
            </div>
          </div>

          <!-- Dropdown Footer -->
          <div :class="['p-2.5 border-t text-center', isDark ? 'border-slate-800 bg-slate-900/60' : 'border-slate-100 bg-slate-50/80']">
            <router-link
              to="/buyer/notifications"
              @click="showNotifications = false"
              class="inline-flex items-center justify-center gap-1.5 w-full py-1.5 text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition"
            >
              <span>View All Notifications</span>
              <ChevronRight class="w-3.5 h-3.5" />
            </router-link>
          </div>
        </div>
      </transition>
    </div>

    <!-- ── USER PROFILE AVATAR (RIGHT NEXT TO NOTIFICATION BELL) ── -->
    <div class="relative" ref="profileDropdownRef">
      <button
        type="button"
        @click="toggleProfile"
        :class="[
          'flex items-center gap-2.5 p-1 sm:p-1.5 rounded-xl border transition-all cursor-pointer shadow-xs group',
          showProfile
            ? isDark ? 'bg-slate-800 border-blue-500' : 'bg-blue-50/50 border-blue-300'
            : isDark ? 'bg-slate-800/80 border-slate-700 hover:bg-slate-800' : 'bg-white border-slate-200 hover:bg-slate-50'
        ]"
        title="User Account"
        aria-label="User Account Menu"
      >
        <!-- Avatar Circle -->
        <div class="relative w-8 h-8 rounded-lg overflow-hidden bg-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0 border border-blue-500/30">
          <img
            v-if="userAvatarUrl"
            :src="userAvatarUrl"
            :alt="userName"
            class="w-full h-full object-cover"
          />
          <span v-else class="tracking-wider uppercase">
            {{ userInitials }}
          </span>
          <!-- Active Online Green Dot -->
          <span class="absolute bottom-0 right-0 w-2 h-2 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
        </div>

        <!-- Name & Role (Hidden on mobile) -->
        <div class="hidden md:flex flex-col text-left pr-1 leading-tight">
          <span :class="['text-xs font-bold truncate max-w-[110px]', isDark ? 'text-slate-200' : 'text-slate-800']">
            {{ userName }}
          </span>
          <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold uppercase tracking-wider">
            Buyer
          </span>
        </div>

        <ChevronDown :class="['w-3.5 h-3.5 text-slate-400 transition-transform duration-200 hidden sm:block', showProfile ? 'rotate-180 text-blue-500' : '']" />
      </button>

      <!-- Profile Dropdown Menu -->
      <transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div
          v-if="showProfile"
          :class="[
            'absolute right-0 mt-2 w-64 rounded-2xl border shadow-xl z-50 overflow-hidden',
            isDark ? 'bg-[#131b2e] border-slate-700 text-slate-100' : 'bg-white border-slate-200 text-slate-800'
          ]"
        >
          <!-- User Info Banner -->
          <div :class="['p-4 border-b flex items-center gap-3', isDark ? 'border-slate-800 bg-slate-900/60' : 'border-slate-100 bg-slate-50/80']">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold text-sm flex items-center justify-center shrink-0 border border-blue-500/30">
              <img
                v-if="userAvatarUrl"
                :src="userAvatarUrl"
                :alt="userName"
                class="w-full h-full object-cover rounded-xl"
              />
              <span v-else class="tracking-wider uppercase">{{ userInitials }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-xs font-bold truncate">{{ userName }}</div>
              <div class="text-[11px] text-slate-400 truncate">{{ userEmail }}</div>
              <span class="inline-block mt-1 px-2 py-0.2 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-blue-500/10 text-blue-500 border border-blue-500/20">
                Verified Enterprise Buyer
              </span>
            </div>
          </div>

          <!-- Quick Navigation Links -->
          <div class="p-2 space-y-0.5">
            <router-link
              to="/buyer/profile"
              @click="showProfile = false"
              :class="[
                'flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition cursor-pointer',
                isDark ? 'hover:bg-slate-800 text-slate-200' : 'hover:bg-slate-100 text-slate-700'
              ]"
            >
              <User class="w-4 h-4 text-blue-500" />
              <span>Business Profile</span>
            </router-link>

            <router-link
              to="/buyer/messages"
              @click="showProfile = false"
              :class="[
                'flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition cursor-pointer',
                isDark ? 'hover:bg-slate-800 text-slate-200' : 'hover:bg-slate-100 text-slate-700'
              ]"
            >
              <Mail class="w-4 h-4 text-blue-500" />
              <span>Messages &amp; Inquiries</span>
            </router-link>

            <router-link
              to="/buyer/notifications"
              @click="showProfile = false"
              :class="[
                'flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium transition cursor-pointer',
                isDark ? 'hover:bg-slate-800 text-slate-200' : 'hover:bg-slate-100 text-slate-700'
              ]"
            >
              <div class="flex items-center gap-2.5">
                <Bell class="w-4 h-4 text-blue-500" />
                <span>Notification Center</span>
              </div>
              <span
                v-if="unreadCount > 0"
                class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-red-600 text-white"
              >
                {{ unreadCount }}
              </span>
            </router-link>

            <router-link
              to="/buyer/reports/purchases"
              @click="showProfile = false"
              :class="[
                'flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition cursor-pointer',
                isDark ? 'hover:bg-slate-800 text-slate-200' : 'hover:bg-slate-100 text-slate-700'
              ]"
            >
              <FileSpreadsheet class="w-4 h-4 text-blue-500" />
              <span>Purchase Reports</span>
            </router-link>
          </div>

          <!-- Divider & Logout -->
          <div :class="['p-2 border-t', isDark ? 'border-slate-800' : 'border-slate-100']">
            <button
              type="button"
              @click="handleLogout"
              class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition cursor-pointer"
            >
              <LogOut class="w-4 h-4 text-red-500" />
              <span>Sign Out</span>
            </button>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useThemeStore } from '@/stores/themeStore'
import { notificationAPI } from '@/api/notifications'
import { 
  Bell, BellOff, Sun, Moon, ChevronDown, ChevronRight, 
  User, Mail, FileSpreadsheet, LogOut 
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const themeStore = useThemeStore()

// Dark mode state connected to global reactive themeStore
const isDark = computed(() => themeStore.isDark)

const toggleTheme = () => {
  themeStore.toggleTheme()
}

// User details
const userName = computed(() => {
  return authStore.user?.name || authStore.user?.full_name || 'Commercial Buyer'
})

const userEmail = computed(() => {
  return authStore.user?.email || 'buyer@agritech.et'
})

const userInitials = computed(() => {
  const name = userName.value
  if (!name) return 'BY'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return name.slice(0, 2).toUpperCase()
})

const userAvatarUrl = computed(() => {
  if (authStore.user?.profile_image) {
    return authStore.user.profile_image
  }
  return null
})

// Dropdown states
const showNotifications = ref(false)
const showProfile = ref(false)
const notificationDropdownRef = ref<HTMLElement | null>(null)
const profileDropdownRef = ref<HTMLElement | null>(null)

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    showProfile.value = false
    fetchNotifications()
  }
}

const toggleProfile = () => {
  showProfile.value = !showProfile.value
  if (showProfile.value) {
    showNotifications.value = false
  }
}

// Notifications state
interface NotificationItem {
  id: string | number
  title: string
  message: string
  read_at: string | null
  time_formatted: string
  created_at: string
}

const notifications = ref<NotificationItem[]>([])
const unreadCount = ref(0)
const loading = ref(false)

const formatTimeAgo = (dateStr: string) => {
  if (!dateStr) return 'Just now'
  const date = new Date(dateStr)
  const seconds = Math.floor((new Date().getTime() - date.getTime()) / 1000)
  if (seconds < 60) return 'Just now'
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return `${minutes}m ago`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  return `${days}d ago`
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const res = await notificationAPI.getNotifications(1, 8)
    const rawList = res.data?.data?.data || res.data?.data || res.data || []
    
    if (Array.isArray(rawList)) {
      notifications.value = rawList.map((n: any) => ({
        id: n.id,
        title: n.data?.title || n.title || 'Market Alert',
        message: n.data?.message || n.message || 'Notification received',
        read_at: n.read_at,
        created_at: n.created_at,
        time_formatted: formatTimeAgo(n.created_at)
      }))
    }

    // Update unread count
    const countRes = await notificationAPI.getNotificationCount().catch(() => null)
    if (countRes && countRes.data && countRes.data.data) {
      unreadCount.value = countRes.data.data.unread ?? 0
    } else {
      unreadCount.value = notifications.value.filter(n => !n.read_at).length
    }
  } catch (err) {
    // If backend notifications table is empty, default gracefully
    unreadCount.value = 0
  } finally {
    loading.value = false
  }
}

const markAllAsRead = async () => {
  try {
    await notificationAPI.markAllAsRead()
    notifications.value.forEach(n => { n.read_at = new Date().toISOString() })
    unreadCount.value = 0
  } catch (err) {
    console.error('Failed to mark all as read:', err)
  }
}

const handleNotificationClick = async (item: NotificationItem) => {
  if (!item.read_at) {
    try {
      await notificationAPI.markAsRead(item.id)
      item.read_at = new Date().toISOString()
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (e) {
      console.error(e)
    }
  }
  showNotifications.value = false
  router.push('/buyer/notifications')
}

// Click outside handler
const handleClickOutside = (e: MouseEvent) => {
  const target = e.target as Node
  if (notificationDropdownRef.value && !notificationDropdownRef.value.contains(target)) {
    showNotifications.value = false
  }
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(target)) {
    showProfile.value = false
  }
}

const handleLogout = async () => {
  showProfile.value = false
  await authStore.logout()
  router.push('/login')
}

let pollInterval: any = null

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  fetchNotifications()

  // Poll every 45 seconds for new alerts
  pollInterval = setInterval(fetchNotifications, 45000)

  // Ensure auth user profile is loaded if empty
  if (!authStore.user) {
    authStore.fetchProfile().catch(() => {})
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (pollInterval) clearInterval(pollInterval)
})
</script>
