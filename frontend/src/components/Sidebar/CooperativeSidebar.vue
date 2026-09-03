<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Cooperative Portal</h2>
      <p>Collective Agriculture</p>
    </div>

    <nav class="sidebar-menu">
      <!-- Dashboard -->
      <router-link to="/cooperative/dashboard" class="menu-item main-dashboard-link" active-class="active">
        <LayoutDashboard class="sub-icon" />
        <span>Dashboard</span>
      </router-link>

      <!-- Member Management -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('members')">
          <span class="section-title-wrapper">
            <Users class="section-icon" />
            <span>Member Management</span>
          </span>
          <component :is="expandedSections.members ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.members" class="section-items">
          <router-link to="/cooperative/members/list" class="menu-item sub-item" active-class="active">
            <UserCheck class="sub-icon" />
            <span>Member Farmers</span>
          </router-link>
          <router-link to="/cooperative/members/add" class="menu-item sub-item" active-class="active">
            <UserPlus class="sub-icon" />
            <span>Add Member</span>
          </router-link>
          <router-link to="/cooperative/members/contributions" class="menu-item sub-item" active-class="active">
            <Coins class="sub-icon" />
            <span>Member Contributions</span>
          </router-link>
        </div>
      </div>

      <!-- Aggregation & Produce -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('aggregation')">
          <span class="section-title-wrapper">
            <Warehouse class="section-icon" />
            <span>Aggregation & Produce</span>
          </span>
          <component :is="expandedSections.aggregation ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.aggregation" class="section-items">
          <router-link to="/cooperative/aggregation/collected" class="menu-item sub-item" active-class="active">
            <PackageCheck class="sub-icon" />
            <span>Collected Produce</span>
          </router-link>
          <router-link to="/cooperative/aggregation/inventory" class="menu-item sub-item" active-class="active">
            <Boxes class="sub-icon" />
            <span>Bulk Inventory</span>
          </router-link>
          <router-link to="/cooperative/aggregation/sales" class="menu-item sub-item" active-class="active">
            <Store class="sub-icon" />
            <span>Market Sales</span>
          </router-link>
        </div>
      </div>

      <!-- Input Distribution -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('inputs')">
          <span class="section-title-wrapper">
            <Truck class="section-icon" />
            <span>Input Distribution</span>
          </span>
          <component :is="expandedSections.inputs ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.inputs" class="section-items">
          <router-link to="/cooperative/inputs/received" class="menu-item sub-item" active-class="active">
            <PackagePlus class="sub-icon" />
            <span>Received Inputs</span>
          </router-link>
          <router-link to="/cooperative/inputs/distribute" class="menu-item sub-item" active-class="active">
            <Share2 class="sub-icon" />
            <span>Distribute to Members</span>
          </router-link>
          <router-link to="/cooperative/inputs/requests" class="menu-item sub-item" active-class="active">
            <ClipboardList class="sub-icon" />
            <span>Input Requests & Subsidies</span>
          </router-link>
        </div>
      </div>

      <!-- Finance & Reports -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('finance')">
          <span class="section-title-wrapper">
            <Wallet class="section-icon" />
            <span>Finance & Reports</span>
          </span>
          <component :is="expandedSections.finance ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.finance" class="section-items">
          <router-link to="/cooperative/finance/wallet" class="menu-item sub-item" active-class="active">
            <CreditCard class="sub-icon" />
            <span>Cooperative Wallet</span>
          </router-link>
          <router-link to="/cooperative/finance/payouts" class="menu-item sub-item" active-class="active">
            <Receipt class="sub-icon" />
            <span>Member Payouts</span>
          </router-link>
          <router-link to="/cooperative/finance/reports" class="menu-item sub-item" active-class="active">
            <BarChart3 class="sub-icon" />
            <span>Performance Reports</span>
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
          <router-link to="/cooperative/account/certifications" class="menu-item sub-item" active-class="active">
            <Award class="sub-icon" />
            <span>Cooperative Certifications</span>
          </router-link>
          <router-link to="/cooperative/account/notifications" class="menu-item sub-item" active-class="active">
            <Bell class="sub-icon" />
            <span>Notifications</span>
          </router-link>
          <router-link to="/cooperative/account/messages" class="menu-item sub-item" active-class="active">
            <Mail class="sub-icon" />
            <span>Messages</span>
          </router-link>
          <router-link to="/cooperative/account/profile" class="menu-item sub-item" active-class="active">
            <Building class="sub-icon" />
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
  ChevronRight, ChevronDown, LayoutDashboard, Users, UserCheck, 
  UserPlus, Coins, Warehouse, PackageCheck, Boxes, Store, 
  Truck, PackagePlus, Share2, ClipboardList, Wallet, CreditCard, 
  Receipt, BarChart3, UserCog, Award, Bell, Mail, Building, LogOut 
} from 'lucide-vue-next'

const emit = defineEmits(['logout'])

const expandedSections = ref({
  members: true,
  aggregation: false,
  inputs: false,
  finance: false,
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
  color: #d97706;
  background: #fef3c7;
  font-weight: 500;
}

.main-dashboard-link.active .sub-icon {
  color: #d97706;
}

.main-dashboard-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #d97706;
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
  color: #d97706;
  background: #fef3c7;
  font-weight: 500;
}

.menu-item.active .sub-icon {
  color: #d97706;
}

.menu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #d97706;
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