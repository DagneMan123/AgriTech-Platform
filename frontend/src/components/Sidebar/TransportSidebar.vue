<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Transport Portal</h2>
      <p>Delivery Management</p>
    </div>

    <nav class="sidebar-menu">
      <router-link to="/transport/dashboard" class="menu-item main-dashboard-link" active-class="active">
        <LayoutDashboard class="sub-icon" />
        <span>Dashboard</span>
      </router-link>

      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('deliveries')">
          <span class="section-title-wrapper">
            <Truck class="section-icon" />
            <span>Deliveries</span>
          </span>
          <component :is="expandedSections.deliveries ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.deliveries" class="section-items">
          <router-link to="/transport/deliveries/available" class="menu-item sub-item" active-class="active">
            <Inbox class="sub-icon" />
            <span>Available Requests</span>
          </router-link>
          <router-link to="/transport/deliveries/active" class="menu-item sub-item" active-class="active">
            <Clock class="sub-icon" />
            <span>Active Deliveries</span>
          </router-link>
          <router-link to="/transport/deliveries/history" class="menu-item sub-item" active-class="active">
            <History class="sub-icon" />
            <span>Delivery History</span>
          </router-link>
          <router-link to="/transport/deliveries/route-map" class="menu-item sub-item" active-class="active">
            <Map class="sub-icon" />
            <span>Route Map</span>
          </router-link>
        </div>
      </div>

      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('fleet')">
          <span class="section-title-wrapper">
            <Car class="section-icon" />
            <span>Fleet Management</span>
          </span>
          <component :is="expandedSections.fleet ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.fleet" class="section-items">
          <router-link to="/transport/fleet/vehicles" class="menu-item sub-item" active-class="active">
            <Truck class="sub-icon" />
            <span>Vehicles</span>
          </router-link>
          <router-link to="/transport/fleet/drivers" class="menu-item sub-item" active-class="active">
            <Users class="sub-icon" />
            <span>Drivers</span>
          </router-link>
          <router-link to="/transport/fleet/maintenance" class="menu-item sub-item" active-class="active">
            <Wrench class="sub-icon" />
            <span>Maintenance Logs</span>
          </router-link>
        </div>
      </div>

      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('finance')">
          <span class="section-title-wrapper">
            <Wallet class="section-icon" />
            <span>Finance & Payments</span>
          </span>
          <component :is="expandedSections.finance ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.finance" class="section-items">
          <router-link to="/transport/finance/earnings" class="menu-item sub-item" active-class="active">
            <Banknote class="sub-icon" />
            <span>Freight Earnings</span>
          </router-link>
          <router-link to="/transport/finance/payouts" class="menu-item sub-item" active-class="active">
            <Receipt class="sub-icon" />
            <span>Payouts</span>
          </router-link>
          <router-link to="/transport/finance/history" class="menu-item sub-item" active-class="active">
            <History class="sub-icon" />
            <span>Transaction History</span>
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
          <router-link to="/transport/reports/deliveries" class="menu-item sub-item" active-class="active">
            <FileSpreadsheet class="sub-icon" />
            <span>Delivery Reports</span>
          </router-link>
          <router-link to="/transport/reports/performance" class="menu-item sub-item" active-class="active">
            <BarChart3 class="sub-icon" />
            <span>Performance Analytics</span>
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
          <router-link to="/transport/account/licenses" class="menu-item sub-item" active-class="active">
            <ShieldCheck class="sub-icon" />
            <span>Transport Licenses</span>
          </router-link>
          <router-link to="/transport/account/notifications" class="menu-item sub-item" active-class="active">
            <Bell class="sub-icon" />
            <span>Notifications</span>
          </router-link>
          <router-link to="/transport/account/messages" class="menu-item sub-item" active-class="active">
            <Mail class="sub-icon" />
            <span>Messages</span>
          </router-link>
          <router-link to="/transport/account/profile" class="menu-item sub-item" active-class="active">
            <User class="sub-icon" />
            <span>Profile</span>
          </router-link>
          <button class="menu-item sub-item logout-btn" @click="emit('logout')" type="button">
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
import { 
  ChevronRight, ChevronDown, LayoutDashboard, Truck, Inbox, Clock, 
  History, Map, Car, Users, Wrench, Wallet, Banknote, Receipt, 
  PieChart, FileSpreadsheet, BarChart3, UserCog, ShieldCheck, 
  Bell, Mail, User, LogOut 
} from 'lucide-vue-next'

const emit = defineEmits(['logout'])

const expandedSections = ref({
  deliveries: true,
  fleet: false,
  finance: false,
  reports: false,
  account: false
})

const toggleSection = (section) => {
  expandedSections.value[section] = !expandedSections.value[section]
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
  margin: 0 0 4px 0;
}

.sidebar-header p {
  font-size: 12px;
  color: #6b7280;
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

.main-dashboard-link {
  padding: 12px 20px;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 5px;
  padding-left: 20px;
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
  color: #8b5cf6;
  background: #faf5ff;
  font-weight: 500;
}

.main-dashboard-link.active .sub-icon {
  color: #8b5cf6;
}

.main-dashboard-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #8b5cf6;
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
  color: #8b5cf6;
  background: #faf5ff;
  font-weight: 500;
}

.menu-item.active .sub-icon {
  color: #8b5cf6;
}

.menu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #8b5cf6;
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