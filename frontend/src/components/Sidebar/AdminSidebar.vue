<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Admin Portal</h2>
      <p>Platform Control</p>
    </div>

    <nav class="sidebar-menu">
      <!-- DASHBOARD & PLATFORM INTELLIGENCE -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('intelligence')">
          <span class="section-title-wrapper">
            <LayoutDashboard class="section-icon" />
            <span> DASHBOARD & Platform Intelligence</span>
          </span>
          <component :is="expandedSections.intelligence ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.intelligence" class="section-items">
          <router-link to="/admin/dashboard" class="menu-item sub-item" active-class="active">
            <PieChart class="sub-icon" />
            <span>Executive Overview & KPIs</span>
          </router-link>
          <router-link to="/admin/metrics" class="menu-item sub-item" active-class="active">
            <Activity class="sub-icon" />
            <span>Real-time System Metrics</span>
          </router-link>
        </div>
      </div>

      <!-- USER & PROVISIONING MANAGEMENT -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('users')">
          <span class="section-title-wrapper">
            <Users class="section-icon" />
            <span>User Management</span>
          </span>
          <component :is="expandedSections.users ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.users" class="section-items">
          <router-link to="/admin/users" class="menu-item sub-item" active-class="active">
            <Contact class="sub-icon" />
            <span>Internal Directory</span>
          </router-link>
          <router-link to="/admin/users/create" class="menu-item sub-item" active-class="active">
            <UserPlus class="sub-icon" />
            <span>Create & Provision User</span>
          </router-link>
          <router-link to="/admin/roles" class="menu-item sub-item" active-class="active">
            <Shield class="sub-icon" />
            <span>Role & Permission Matrices</span>
          </router-link>
          <router-link to="/admin/access-control" class="menu-item sub-item" active-class="active">
            <ShieldCheck class="sub-icon" />
            <span>Access Control</span>
          </router-link>
        </div>
      </div>

      <!-- PORTAL VERIFICATION & COMPLIANCE -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('verification')">
          <span class="section-title-wrapper">
            <ClipboardCheck class="section-icon" />
            <span>Verification & Compliance</span>
          </span>
          <component :is="expandedSections.verification ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.verification" class="section-items">
          <router-link to="/admin/verifications/farmers" class="menu-item sub-item" active-class="active">
            <Sprout class="sub-icon" />
            <span>Farmers & Producers</span>
          </router-link>
          <router-link to="/admin/verifications/buyers" class="menu-item sub-item" active-class="active">
            <ShoppingCart class="sub-icon" />
            <span>Buyers Accreditation</span>
          </router-link>
          <router-link to="/admin/verifications/suppliers" class="menu-item sub-item" active-class="active">
            <Boxes class="sub-icon" />
            <span>Input Suppliers</span>
          </router-link>
          <router-link to="/admin/verifications/financial" class="menu-item sub-item" active-class="active">
            <Landmark class="sub-icon" />
            <span>Financial Institutions</span>
          </router-link>
          <router-link to="/admin/verifications/cooperatives" class="menu-item sub-item" active-class="active">
            <UsersRound class="sub-icon" />
            <span>Cooperatives Compliance</span>
          </router-link>
          <router-link to="/admin/verifications/experts" class="menu-item sub-item" active-class="active">
            <GraduationCap class="sub-icon" />
            <span>Agricultural Experts</span>
          </router-link>
          <router-link to="/admin/verifications/logistics" class="menu-item sub-item" active-class="active">
            <Truck class="sub-icon" />
            <span>Transport & Logistics</span>
          </router-link>
        </div>
      </div>

      <!-- MARKETPLACE & OVERSIGHT -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('marketplace')">
          <span class="section-title-wrapper">
            <Store class="section-icon" />
            <span>Marketplace & Oversight</span>
          </span>
          <component :is="expandedSections.marketplace ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.marketplace" class="section-items">
          <router-link to="/admin/marketplace/crops" class="menu-item sub-item" active-class="active">
            <Wheat class="sub-icon" />
            <span>Crops Catalogs</span>
          </router-link>
          <router-link to="/admin/marketplace/inputs" class="menu-item sub-item" active-class="active">
            <PackageSearch class="sub-icon" />
            <span>Input Inventories</span>
          </router-link>
          <router-link to="/admin/marketplace/moderation" class="menu-item sub-item" active-class="active">
            <ShieldAlert class="sub-icon" />
            <span>Content Moderation</span>
          </router-link>
        </div>
      </div>

      <!-- FINANCIAL & ESCROW MONITORING -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('finance')">
          <span class="section-title-wrapper">
            <Wallet class="section-icon" />
            <span>Financial & Escrow</span>
          </span>
          <component :is="expandedSections.finance ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.finance" class="section-items">
          <router-link to="/admin/finance/escrow" class="menu-item sub-item" active-class="active">
            <Vault class="sub-icon" />
            <span>Escrow Transactions</span>
          </router-link>
          <router-link to="/admin/finance/subsidies" class="menu-item sub-item" active-class="active">
            <BadgeDollarSign class="sub-icon" />
            <span>Credit & Subsidy Audits</span>
          </router-link>
          <router-link to="/admin/finance/revenue" class="menu-item sub-item" active-class="active">
            <FileSpreadsheet class="sub-icon" />
            <span>Platform Revenue & Fees</span>
          </router-link>
        </div>
      </div>

      <!-- SYSTEM CONFIGURATION & GOVERNANCE -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('governance')">
          <span class="section-title-wrapper">
            <Sliders class="section-icon" />
            <span>Configuration & Governance</span>
          </span>
          <component :is="expandedSections.governance ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.governance" class="section-items">
          <router-link to="/admin/settings/taxonomy" class="menu-item sub-item" active-class="active">
            <MapPin class="sub-icon" />
            <span>Taxonomy & Mapping</span>
          </router-link>
          <router-link to="/admin/settings/policies" class="menu-item sub-item" active-class="active">
            <FileText class="sub-icon" />
            <span>Subsidies & Policies</span>
          </router-link>
          <router-link to="/admin/settings/audit-logs" class="menu-item sub-item" active-class="active">
            <History class="sub-icon" />
            <span>Comprehensive Audit Logs</span>
          </router-link>
        </div>
      </div>

      <!-- ACCOUNT & SECURITY -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('account')">
          <span class="section-title-wrapper">
            <UserCog class="section-icon" />
            <span>Account & Security</span>
          </span>
          <component :is="expandedSections.account ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.account" class="section-items">
          <router-link to="/admin/notifications" class="menu-item sub-item" active-class="active">
            <Bell class="sub-icon" />
            <span>Notifications Center</span>
          </router-link>
          <router-link to="/admin/messaging" class="menu-item sub-item" active-class="active">
            <Mail class="sub-icon" />
            <span>Secure Messaging</span>
          </router-link>
          <router-link to="/admin/profile" class="menu-item sub-item" active-class="active">
            <User class="sub-icon" />
            <span>Administrator Profile</span>
          </router-link>
          <button class="menu-item sub-item logout-sub-btn" @click="emit('logout')">
            <LogOut class="sub-icon" />
            <span>Logout</span>
          </button>
        </div>
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { 
  ChevronRight, ChevronDown, LayoutDashboard, PieChart, Activity, Users, 
  Contact, UserPlus, Shield, ShieldCheck, ClipboardCheck, Sprout, ShoppingCart, 
  Boxes, Landmark, UsersRound, GraduationCap, Truck, Store, Wheat, 
  PackageSearch, ShieldAlert, Wallet, Vault, BadgeDollarSign, FileSpreadsheet, 
  Sliders, MapPin, FileText, History, UserCog, Bell, Mail, User, LogOut 
} from 'lucide-vue-next'

const emit = defineEmits(['logout'])

const expandedSections = ref({
  intelligence: true,
  users: true,
  verification: true,
  marketplace: false,
  finance: false,
  governance: false,
  account: true
})

const toggleSection = (section) => {
  expandedSections.value[section] = !expandedSections.value[section]
}
</script>

<style scoped>
.sidebar {
  width: 280px;
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
  margin: 4px 0;
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
  letter-spacing: 0.05em;
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
  width: 12px;
  height: 12px;
  color: #6b7280;
}

.section-items {
  display: flex;
  flex-direction: column;
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

.logout-sub-btn {
  color: #ef4444 !important;
}

.logout-sub-btn .sub-icon {
  color: #ef4444 !important;
}

.logout-sub-btn:hover {
  background: #fee2e2 !important;
}
</style>