<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Buyer Portal</h2>
    </div>

    <nav class="sidebar-menu">
      <!-- Dashboard -->
      <router-link to="/buyer/dashboard" class="menu-item main-dashboard-link" active-class="active">
        <LayoutDashboard class="sub-icon" />
        <span>Dashboard</span>
      </router-link>

      <!-- Marketplace -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('marketplace')">
          <span class="section-title-wrapper">
            <Store class="section-icon" />
            <span>Marketplace</span>
          </span>
          <component :is="expandedSections.marketplace ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.marketplace" class="section-items">
          <router-link to="/buyer/marketplace" class="menu-item sub-item" active-class="active">
            <Package class="sub-icon" />
            <span>Browse Products</span>
          </router-link>
          <router-link to="/buyer/marketplace/categories" class="menu-item sub-item" active-class="active">
            <LayoutGrid class="sub-icon" />
            <span>Categories</span>
          </router-link>
          <router-link to="/buyer/wishlist" class="menu-item sub-item" active-class="active">
            <Heart class="sub-icon" />
            <span>Wishlist / Saved Items</span>
          </router-link>
        </div>
      </div>

      <!-- Orders & Purchases -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('orders')">
          <span class="section-title-wrapper">
            <ShoppingCart class="section-icon" />
            <span>Orders & Purchases</span>
          </span>
          <component :is="expandedSections.orders ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.orders" class="section-items">
          <router-link to="/buyer/orders" class="menu-item sub-item" active-class="active">
            <ShoppingBag class="sub-icon" />
            <span>My Orders</span>
          </router-link>
          <router-link to="/buyer/orders/active" class="menu-item sub-item" active-class="active">
            <Clock class="sub-icon" />
            <span>Active Orders</span>
          </router-link>
          <router-link to="/buyer/orders/history" class="menu-item sub-item" active-class="active">
            <History class="sub-icon" />
            <span>Order History</span>
          </router-link>
          <router-link to="/buyer/returns" class="menu-item sub-item" active-class="active">
            <RotateCcw class="sub-icon" />
            <span>Returns & Refunds</span>
          </router-link>
        </div>
      </div>

      <!-- Contract Farming -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('contracts')">
          <span class="section-title-wrapper">
            <Handshake class="section-icon" />
            <span>Contract Farming</span>
          </span>
          <component :is="expandedSections.contracts ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.contracts" class="section-items">
          <router-link to="/buyer/contracts" class="menu-item sub-item" active-class="active">
            <FileText class="sub-icon" />
            <span>Direct Contracts</span>
          </router-link>
          <router-link to="/buyer/contracts/agreements" class="menu-item sub-item" active-class="active">
            <FileSignature class="sub-icon" />
            <span>Contract Agreements</span>
          </router-link>
          <router-link to="/buyer/contracts/milestones" class="menu-item sub-item" active-class="active">
            <CheckSquare class="sub-icon" />
            <span>Milestone Tracking</span>
          </router-link>
        </div>
      </div>

      <!-- Logistics & Delivery -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('logistics')">
          <span class="section-title-wrapper">
            <Truck class="section-icon" />
            <span>Logistics & Delivery</span>
          </span>
          <component :is="expandedSections.logistics ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.logistics" class="section-items">
          <router-link to="/buyer/delivery/tracking" class="menu-item sub-item" active-class="active">
            <MapPin class="sub-icon" />
            <span>Delivery Tracking</span>
          </router-link>
          <router-link to="/buyer/delivery/addresses" class="menu-item sub-item" active-class="active">
            <BookUser class="sub-icon" />
            <span>Delivery Addresses</span>
          </router-link>
          <router-link to="/buyer/transport" class="menu-item sub-item" active-class="active">
            <Send class="sub-icon" />
            <span>Transport Coordination</span>
          </router-link>
        </div>
      </div>

      <!-- Payments & Finance -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('payments')">
          <span class="section-title-wrapper">
            <Banknote class="section-icon" />
            <span>Payments & Finance</span>
          </span>
          <component :is="expandedSections.payments ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.payments" class="section-items">
          <router-link to="/buyer/payments/methods" class="menu-item sub-item" active-class="active">
            <CreditCard class="sub-icon" />
            <span>Payment Methods</span>
          </router-link>
          <router-link to="/buyer/payments/history" class="menu-item sub-item" active-class="active">
            <Receipt class="sub-icon" />
            <span>Transaction History</span>
          </router-link>
          <router-link to="/buyer/payments/escrow" class="menu-item sub-item" active-class="active">
            <ShieldCheck class="sub-icon" />
            <span>Escrow & Invoices</span>
          </router-link>
        </div>
      </div>

      <!-- Agricultural Services -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('services')">
          <span class="section-title-wrapper">
            <CloudSun class="section-icon" />
            <span>Agricultural Services</span>
          </span>
          <component :is="expandedSections.services ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.services" class="section-items">
          <router-link to="/buyer/services/prices" class="menu-item sub-item" active-class="active">
            <BarChart3 class="sub-icon" />
            <span>Market Prices</span>
          </router-link>
          <router-link to="/buyer/services/suppliers" class="menu-item sub-item" active-class="active">
            <Building2 class="sub-icon" />
            <span>Supplier Directory</span>
          </router-link>
          <router-link to="/buyer/services/consultations" class="menu-item sub-item" active-class="active">
            <UserCheck class="sub-icon" />
            <span>Consultations</span>
          </router-link>
        </div>
      </div>

      <!-- Reports -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('reports')">
          <span class="section-title-wrapper">
            <PieChart class="section-icon" />
            <span>Reports</span>
          </span>
          <component :is="expandedSections.reports ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.reports" class="section-items">
          <router-link to="/buyer/reports/purchases" class="menu-item sub-item" active-class="active">
            <FileSpreadsheet class="sub-icon" />
            <span>Purchase Reports</span>
          </router-link>
          <router-link to="/buyer/reports/expenses" class="menu-item sub-item" active-class="active">
            <BarChart2 class="sub-icon" />
            <span>Expense Analysis</span>
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Account -->
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
          <router-link to="/buyer/notifications" class="menu-item sub-item" active-class="active">
            <Bell class="sub-icon" />
            <span>Notifications</span>
          </router-link>
          <router-link to="/buyer/messages" class="menu-item sub-item" active-class="active">
            <Mail class="sub-icon" />
            <span>Messages</span>
          </router-link>
          <router-link to="/buyer/profile" class="menu-item sub-item" active-class="active">
            <User class="sub-icon" />
            <span>Business Profile</span>
          </router-link>
          <button class="menu-item sub-item logout-btn" @click="handleLogout">
            <LogOut class="sub-icon" />
            <span>Logout</span>
          </button>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { 
  ChevronRight, ChevronDown, LayoutDashboard, Store, Package, LayoutGrid, 
  Heart, ShoppingCart, ShoppingBag, Clock, History, RotateCcw, Handshake, 
  FileText, FileSignature, CheckSquare, Truck, MapPin, BookUser, Send, 
  Banknote, CreditCard, Receipt, ShieldCheck, CloudSun, BarChart3, Building2, 
  UserCheck, PieChart, FileSpreadsheet, BarChart2, UserCog, Bell, Mail, 
  User, LogOut 
} from 'lucide-vue-next'

const router = useRouter()
const auth = useAuthStore()

const expandedSections = ref({
  marketplace: true,
  orders: true,
  contracts: true,
  logistics: false,
  payments: false,
  services: false,
  reports: false,
  account: false
})

const toggleSection = (section) => {
  expandedSections.value[section] = !expandedSections.value[section]
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
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

/* ለብቻው ላለው የ Dashboard አገናኝ የሚሆን ስታይል */
.main-dashboard-link {
  padding: 12px 20px;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 5px;
}

.main-dashboard-link:hover {
  color: #1f2937;
  background: #f9fafb;
}

.main-dashboard-link .sub-icon {
  color: #9ca3af;
  width: 16px;
  height: 16px;
}

.main-dashboard-link.active {
  color: #2563eb;
  background: #eff6ff;
  font-weight: 500;
}

.main-dashboard-link.active .sub-icon {
  color: #2563eb;
}

.main-dashboard-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #2563eb;
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
  color: #2563eb;
  background: #eff6ff;
  font-weight: 500;
}

.menu-item.active .sub-icon {
  color: #2563eb;
}

.menu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #2563eb;
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