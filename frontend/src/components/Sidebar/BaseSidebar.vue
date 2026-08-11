<template>
  <div :class="['sidebar', { 'sidebar-collapsed': !isOpen }]">
    <!-- Header -->
    <div class="sidebar-header">
      <h2>{{ headerTitle }}</h2>
      <p v-if="headerSubtitle" class="subtitle">{{ headerSubtitle }}</p>
      <button class="collapse-btn" @click="toggleSidebar">
        <i :class="['fas', isOpen ? 'fa-chevron-left' : 'fa-chevron-right']"></i>
      </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <div v-for="section in menuSections" :key="section.section" class="nav-section">
        <h3 
          v-if="section.items.length > 0"
          class="section-title"
          @click="toggleSection(section.section)"
          :class="{ 'collapsible': section.collapsible }"
        >
          <i v-if="section.collapsible" class="fas fa-chevron-down toggle-icon"></i>
          {{ section.section }}
        </h3>

        <transition-group name="slide" tag="div">
          <router-link
            v-for="item in section.items"
            v-show="!section.collapsible || expandedSections[section.section]"
            :key="item.label"
            :to="item.route"
            class="nav-item"
            :class="{ active: isActive(item.route) }"
          >
            <i :class="['nav-icon', item.icon]"></i>
            <span v-if="isOpen" class="nav-label">{{ item.label }}</span>
            <span v-if="item.badge && isOpen" class="badge">{{ item.badge }}</span>
          </router-link>
        </transition-group>
      </div>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
      <div class="quick-links">
        <button class="quick-link" title="Notifications">
          <i class="fas fa-bell"></i>
          <span v-if="notifications" class="badge-dot">{{ notifications }}</span>
        </button>
        <button class="quick-link" title="Messages">
          <i class="fas fa-envelope"></i>
          <span v-if="messages" class="badge-dot">{{ messages }}</span>
        </button>
      </div>

      <div class="user-info">
        <img :src="userAvatar" :alt="userName" class="avatar">
        <div v-if="isOpen" class="user-details">
          <p class="user-name">{{ userName }}</p>
          <small class="user-role">{{ userRole }}</small>
        </div>
      </div>

      <button class="logout-btn" @click="handleLogout" title="Logout">
        <i class="fas fa-sign-out-alt"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const props = defineProps({
  role: {
    type: String,
    required: true
  },
  headerTitle: {
    type: String,
    default: 'Dashboard'
  },
  headerSubtitle: {
    type: String,
    default: ''
  },
  menuSections: {
    type: Array,
    required: true
  },
  notifications: {
    type: Number,
    default: 0
  },
  messages: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['logout'])

const router = useRouter()
const auth = useAuthStore()
const isOpen = ref(true)
const expandedSections = ref({})

// Initialize all sections as expanded
const initializeExpandedSections = () => {
  props.menuSections.forEach(section => {
    expandedSections.value[section.section] = true
  })
}

initializeExpandedSections()

const toggleSidebar = () => {
  isOpen.value = !isOpen.value
}

const toggleSection = (section) => {
  const menuSection = props.menuSections.find(s => s.section === section)
  if (menuSection?.collapsible) {
    expandedSections.value[section] = !expandedSections.value[section]
  }
}

const isActive = (route) => {
  return router.currentRoute.value.path === route
}

const handleLogout = async () => {
  if (confirm('Are you sure you want to logout?')) {
    emit('logout')
    await auth.logout()
    router.push('/login')
  }
}

const userName = computed(() => auth.user?.name || 'User')
const userRole = computed(() => {
  const roles = {
    admin: 'Administrator',
    farmer: 'Farmer',
    buyer: 'Buyer',
    supplier: 'Supplier',
    transport: 'Transport Provider',
    cooperative: 'Cooperative',
    expert: 'Agricultural Expert',
    financial: 'Financial Institution'
  }
  return roles[props.role] || props.role
})
const userAvatar = computed(() => auth.user?.avatar || 'https://via.placeholder.com/40')
</script>

<style scoped>
.sidebar {
  width: 260px;
  height: 100vh;
  background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
  color: #e5e7eb;
  padding: 0;
  position: fixed;
  left: 0;
  top: 0;
  overflow-y: auto;
  z-index: 1000;
  border-right: 1px solid #374151;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
}

.sidebar-collapsed {
  width: 80px;
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid #374151;
  text-align: center;
  position: relative;
  flex-shrink: 0;
}

.sidebar-header h2 {
  font-size: 18px;
  margin: 0;
  color: white;
  font-weight: 700;
}

.sidebar-header .subtitle {
  font-size: 12px;
  color: #9ca3af;
  margin-top: 4px;
}

.collapse-btn {
  position: absolute;
  right: 15px;
  top: 20px;
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 16px;
  transition: color 0.3s;
}

.collapse-btn:hover {
  color: #f3f4f6;
}

.sidebar-nav {
  flex: 1;
  padding: 20px 0;
  overflow-y: auto;
}

.nav-section {
  margin-bottom: 5px;
}

.section-title {
  padding: 10px 20px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #9ca3af;
  margin: 0;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  user-select: none;
  transition: color 0.3s;
}

.section-title:hover {
  color: #f3f4f6;
}

.section-title.collapsible {
  cursor: pointer;
}

.toggle-icon {
  font-size: 10px;
  transition: transform 0.3s;
}

.nav-section.collapsed .toggle-icon {
  transform: rotate(-90deg);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 20px;
  color: #d1d5db;
  text-decoration: none;
  transition: all 0.3s;
  border-left: 3px solid transparent;
  position: relative;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.05);
  color: white;
}

.nav-item.active {
  background-color: rgba(59, 130, 246, 0.1);
  color: #60a5fa;
  border-left-color: #3b82f6;
}

.nav-item.active[data-role="farmer"] {
  border-left-color: #10b981;
  color: #6ee7b7;
}

.nav-item.active[data-role="buyer"] {
  border-left-color: #8b5cf6;
  color: #c4b5fd;
}

.nav-item.active[data-role="supplier"] {
  border-left-color: #f59e0b;
  color: #fbbf24;
}

.nav-item.active[data-role="transport"] {
  border-left-color: #ef4444;
  color: #fca5a5;
}

.nav-item.active[data-role="cooperative"] {
  border-left-color: #14b8a6;
  color: #7ee8c9;
}

.nav-item.active[data-role="expert"] {
  border-left-color: #6366f1;
  color: #a5b4fc;
}

.nav-item.active[data-role="financial"] {
  border-left-color: #eab308;
  color: #facc15;
}

.nav-icon {
  font-size: 16px;
  min-width: 20px;
  text-align: center;
}

.nav-label {
  flex: 1;
  font-size: 14px;
  font-weight: 500;
}

.badge {
  background: #ef4444;
  color: white;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}

.sidebar-footer {
  border-top: 1px solid #374151;
  padding: 15px 20px;
  flex-shrink: 0;
  background: #0f172a;
}

.quick-links {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}

.quick-link {
  flex: 1;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid #374151;
  color: #e5e7eb;
  padding: 8px;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  transition: all 0.3s;
}

.quick-link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.badge-dot {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 700;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #374151;
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: white;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 12px;
  color: #9ca3af;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.logout-btn {
  width: 100%;
  padding: 10px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.logout-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

/* Animations */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.slide-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

/* Scrollbar Styling */
.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
  background: #374151;
  border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
  background: #4b5563;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    width: 200px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.3);
  }

  .sidebar.open {
    transform: translateX(0);
  }
}

@media (max-width: 480px) {
  .sidebar {
    width: 100vw;
    max-width: 250px;
  }
}
</style>
