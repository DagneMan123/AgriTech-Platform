<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Farmer Portal</h2>
    </div>

    <nav class="sidebar-menu">
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('farmManagement')">
          <span class="section-title-wrapper">
            <Tractor class="section-icon" />
            <span>Farm Management</span>
          </span>
          <component :is="expandedSections.farmManagement ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.farmManagement" class="section-items">
          <router-link to="/farmer/dashboard" class="menu-item sub-item" active-class="active">
            <LayoutDashboard class="sub-icon" />
            <span>Dashboard</span>
          </router-link>
          <router-link to="/farmer/farms" class="menu-item sub-item" active-class="active">
            <Home class="sub-icon" />
            <span>My Farms</span>
          </router-link>
          <router-link to="/farmer/farm-map" class="menu-item sub-item" active-class="active">
            <Map class="sub-icon" />
            <span>Farm Map</span>
          </router-link>
          <router-link to="/farmer/crops" class="menu-item sub-item" active-class="active">
            <Sprout class="sub-icon" />
            <span>Crops</span>
          </router-link>
          <router-link to="/farmer/crop-activities" class="menu-item sub-item" active-class="active">
            <ClipboardList class="sub-icon" />
            <span>Crop Activities</span>
          </router-link>
          <router-link to="/farmer/harvests" class="menu-item sub-item" active-class="active">
            <Wheat class="sub-icon" />
            <span>Harvests</span>
          </router-link>
        </div>
      </div>

      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('marketplace')">
          <span class="section-title-wrapper">
            <Store class="section-icon" />
            <span>Marketplace</span>
          </span>
          <component :is="expandedSections.marketplace ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.marketplace" class="section-items">
          <router-link to="/farmer/products" class="menu-item sub-item" active-class="active">
            <Package class="sub-icon" />
            <span>My Products</span>
          </router-link>
          <router-link to="/farmer/my-sales" class="menu-item sub-item" active-class="active">
            <TrendingUp class="sub-icon" />
            <span>My Sales</span>
          </router-link>
          <router-link to="/farmer/customer-orders" class="menu-item sub-item" active-class="active">
            <ShoppingCart class="sub-icon" />
            <span>Customer Orders</span>
          </router-link>
        </div>
      </div>

      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('farmInputs')">
          <span class="section-title-wrapper">
            <Leaf class="section-icon" />
            <span>Farm Inputs</span>
          </span>
          <component :is="expandedSections.farmInputs ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.farmInputs" class="section-items">
          <router-link to="/farmer/seeds" class="menu-item sub-item" active-class="active">
            <Flower2 class="sub-icon" />
            <span>Seeds</span>
          </router-link>
          <router-link to="/farmer/fertilizers" class="menu-item sub-item" active-class="active">
            <FlaskConical class="sub-icon" />
            <span>Fertilizers</span>
          </router-link>
          <router-link to="/farmer/pesticides" class="menu-item sub-item" active-class="active">
            <ShieldAlert class="sub-icon" />
            <span>Pesticides</span>
          </router-link>
          <router-link to="/farmer/machinery" class="menu-item sub-item" active-class="active">
            <Wrench class="sub-icon" />
            <span>Machinery & Equipment</span>
          </router-link>
          <router-link to="/farmer/input-orders" class="menu-item sub-item" active-class="active">
            <FileText class="sub-icon" />
            <span>My Input Orders</span>
          </router-link>
          <router-link to="/farmer/subsidy-requests" class="menu-item sub-item" active-class="active">
            <BadgeDollarSign class="sub-icon" />
            <span>Subsidy Requests</span>
          </router-link>
        </div>
      </div>

      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('logistics')">
          <span class="section-title-wrapper">
            <Truck class="section-icon" />
            <span>Logistics</span>
          </span>
          <component :is="expandedSections.logistics ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.logistics" class="section-items">
          <router-link to="/farmer/transport-requests" class="menu-item sub-item" active-class="active">
            <Send class="sub-icon" />
            <span>Transport Requests</span>
          </router-link>
          <router-link to="/farmer/active-deliveries" class="menu-item sub-item" active-class="active">
            <Truck class="sub-icon" />
            <span>Active Deliveries</span>
          </router-link>
          <router-link to="/farmer/delivery-tracking" class="menu-item sub-item" active-class="active">
            <MapPin class="sub-icon" />
            <span>Delivery Tracking</span>
          </router-link>
        </div>
      </div>

      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('services')">
          <span class="section-title-wrapper">
            <CloudSun class="section-icon" />
            <span>Agricultural Services</span>
          </span>
          <component :is="expandedSections.services ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.services" class="section-items">
          <router-link to="/farmer/weather" class="menu-item sub-item" active-class="active">
            <CloudSunRain class="sub-icon" />
            <span>Weather Forecast</span>
          </router-link>
          <router-link to="/farmer/market-prices" class="menu-item sub-item" active-class="active">
            <BarChart3 class="sub-icon" />
            <span>Market Prices</span>
          </router-link>
          <router-link to="/farmer/consultations" class="menu-item sub-item" active-class="active">
            <MessageSquare class="sub-icon" />
            <span>Consultations</span>
          </router-link>
          <router-link to="/farmer/training" class="menu-item sub-item" active-class="active">
            <BookOpen class="sub-icon" />
            <span>Training Material</span>
          </router-link>
        </div>
      </div>

      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('financial')">
          <span class="section-title-wrapper">
            <Landmark class="section-icon" />
            <span>Financial Services</span>
          </span>
          <component :is="expandedSections.financial ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.financial" class="section-items">
          <router-link to="/farmer/loans" class="menu-item sub-item" active-class="active">
            <HandCoins class="sub-icon" />
            <span>Loans</span>
          </router-link>
          <router-link to="/farmer/insurance" class="menu-item sub-item" active-class="active">
            <ShieldCheck class="sub-icon" />
            <span>Insurance</span>
          </router-link>
          <router-link to="/farmer/payments" class="menu-item sub-item" active-class="active">
            <Wallet class="sub-icon" />
            <span>Payments</span>
          </router-link>
          <router-link to="/farmer/financial-history" class="menu-item sub-item" active-class="active">
            <History class="sub-icon" />
            <span>Financial History</span>
          </router-link>
        </div>
      </div>

      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('reports')">
          <span class="section-title-wrapper">
            <PieChart class="section-icon" />
            <span>Reports</span>
          </span>
          <component :is="expandedSections.reports ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.reports" class="section-items">
          <router-link to="/farmer/reports/farm" class="menu-item sub-item" active-class="active">
            <FileSpreadsheet class="sub-icon" />
            <span>Farm Reports</span>
          </router-link>
          <router-link to="/farmer/reports/sales" class="menu-item sub-item" active-class="active">
            <TrendingUp class="sub-icon" />
            <span>Sales Reports</span>
          </router-link>
          <router-link to="/farmer/reports/production" class="menu-item sub-item" active-class="active">
            <BarChart3 class="sub-icon" />
            <span>Production Reports</span>
          </router-link>
        </div>
      </div>
    </nav>

    <div class="sidebar-bottom">
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('account')">
          <span class="section-title-wrapper">
            <UserCog class="section-icon" />
            <span>Account</span>
          </span>
          <component :is="expandedSections.account ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.account" class="section-items">
          <router-link to="/farmer/profile" class="menu-item sub-item" active-class="active">
            <User class="sub-icon" />
            <span>Profile</span>
          </router-link>
          <button class="menu-item sub-item logout-btn" @click="emitLogout">
            <LogOut class="sub-icon" />
            <span>Logout</span>
          </button>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, watch, onMounted, defineEmits } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { 
  ChevronRight, ChevronDown, Tractor, LayoutDashboard, Home, Map, Sprout, 
  ClipboardList, Wheat, Store, Package, TrendingUp, ShoppingCart, 
  Leaf, Flower2, FlaskConical, ShieldAlert, Wrench, FileText, 
  BadgeDollarSign, Truck, Send, MapPin, CloudSun, CloudSunRain, BarChart3, 
  MessageSquare, BookOpen, Landmark, HandCoins, ShieldCheck, Wallet, History, 
  PieChart, FileSpreadsheet, UserCog, User, LogOut 
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const emit = defineEmits(['logout'])

const expandedSections = ref({
  farmManagement: true,
  marketplace: false,
  farmInputs: false,
  logistics: false,
  services: false,
  financial: false,
  reports: false,
  account: false
})

// Map routes to their sections for auto-expansion
const routeSectionMap = {
  'farmer-dashboard': 'farmManagement',
  'farmer-farms': 'farmManagement',
  'farmer-farm-map': 'farmManagement',
  'farmer-crops': 'farmManagement',
  'farmer-crop-activities': 'farmManagement',
  'farmer-harvests': 'farmManagement',
  
  'farmer-products': 'marketplace',
  'farmer-my-sales': 'marketplace',
  'farmer-customer-orders': 'marketplace',
  
  'farmer-seeds': 'farmInputs',
  'farmer-fertilizers': 'farmInputs',
  'farmer-pesticides': 'farmInputs',
  'farmer-machinery': 'farmInputs',
  'farmer-input-orders': 'farmInputs',
  'farmer-subsidy-requests': 'farmInputs',
  
  'farmer-transport-requests': 'logistics',
  'farmer-active-deliveries': 'logistics',
  'farmer-delivery-tracking': 'logistics',
  
  'farmer-weather': 'services',
  'farmer-market-prices': 'services',
  'farmer-consultations': 'services',
  'farmer-training': 'services',
  
  'farmer-loans': 'financial',
  'farmer-insurance': 'financial',
  'farmer-payments': 'financial',
  'farmer-financial-history': 'financial',
  
  'farmer-reports-farm': 'reports',
  'farmer-reports-sales': 'reports',
  'farmer-reports-production': 'reports',
  
  'farmer-profile': 'account'
}

const toggleSection = (section) => {
  expandedSections.value[section] = !expandedSections.value[section]
}

const autoExpandSection = () => {
  const currentRouteName = route.name
  const sectionToExpand = routeSectionMap[currentRouteName]
  
  if (sectionToExpand) {
    expandedSections.value[sectionToExpand] = true
  }
}

watch(() => route.name, () => {
  autoExpandSection()
}, { immediate: true })

const handleLogout = async () => {
  try {
    await auth.logout()
    await router.push('/auth/login')
  } catch (error) {
    console.error('Logout error:', error)
  }
}

const emitLogout = async () => {
  await handleLogout()
  emit('logout')
}

onMounted(() => {
  autoExpandSection()
})
</script>

<style scoped>
.sidebar {
  width: 260px;
  background: white;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  overflow-y: auto;
  z-index: 1000;
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.sidebar-header h2 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.sidebar-menu {
  flex: 1;
  padding: 10px 0;
  overflow-y: auto;
}

.menu-section {
  margin: 5px 0;
}

.section-toggle {
  padding: 12px 20px;
  color: #4b5563;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.3s;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.section-title-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-toggle:hover {
  color: #1f2937;
  background: #f9fafb;
}

.section-icon {
  width: 16px;
  height: 16px;
  color: #9ca3af;
}

.toggle-icon {
  width: 16px;
  height: 16px;
  color: #4b5563;
}

.section-items {
  display: flex;
  flex-direction: column;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
  }
  to {
    opacity: 1;
    max-height: 500px;
  }
}

.menu-item {
  padding: 10px 20px 10px 44px;
  color: #6b7280;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  transition: all 0.2s;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  position: relative;
}

.sub-icon {
  width: 15px;
  height: 15px;
  color: #9ca3af;
}

.menu-item:hover {
  color: #1f2937;
  background: #f3f4f6;
}

.menu-item:hover .sub-icon {
  color: #1f2937;
}

.menu-item.active {
  color: #10b981;
  background: #ecfdf5;
  font-weight: 500;
}

.menu-item.active .sub-icon {
  color: #10b981;
}

.menu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #10b981;
}

.sidebar-bottom {
  padding: 10px 0;
  border-top: 1px solid #e5e7eb;
}

.logout-btn {
  color: #ef4444 !important;
}

.logout-btn .sub-icon {
  color: #ef4444 !important;
}

.logout-btn:hover {
  background: #fee2e2 !important;
}
</style>