<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Financial Portal</h2>
      <p>Loans & Insurance</p>
    </div>

    <nav class="sidebar-menu">
      <!-- Dashboard -->
      <router-link to="/financial/dashboard" class="menu-item main-dashboard-link" active-class="active">
        <LayoutDashboard class="sub-icon" />
        <span>Dashboard</span>
      </router-link>

      <!-- Loans & Credit -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('loans')">
          <span class="section-title-wrapper">
            <CreditCard class="section-icon" />
            <span>Loans & Credit</span>
          </span>
          <component :is="expandedSections.loans ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.loans" class="section-items">
          <router-link to="/financial/loans/requests" class="menu-item sub-item" active-class="active">
            <FileText class="sub-icon" />
            <span>Loan Requests</span>
          </router-link>
          <router-link to="/financial/loans/active" class="menu-item sub-item" active-class="active">
            <CheckCircle2 class="sub-icon" />
            <span>Active Loans</span>
          </router-link>
          <router-link to="/financial/loans/repayments" class="menu-item sub-item" active-class="active">
            <CalendarClock class="sub-icon" />
            <span>Repayment Schedules</span>
          </router-link>
          <router-link to="/financial/loans/scoring" class="menu-item sub-item" active-class="active">
            <ShieldAlert class="sub-icon" />
            <span>Credit Risk Scoring</span>
          </router-link>
        </div>
      </div>

      <!-- Insurance -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('insurance')">
          <span class="section-title-wrapper">
            <Umbrella class="section-icon" />
            <span>Insurance</span>
          </span>
          <component :is="expandedSections.insurance ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.insurance" class="section-items">
          <router-link to="/financial/insurance/applications" class="menu-item sub-item" active-class="active">
            <FilePlus class="sub-icon" />
            <span>Insurance Applications</span>
          </router-link>
          <router-link to="/financial/insurance/policies" class="menu-item sub-item" active-class="active">
            <FileCheck class="sub-icon" />
            <span>Active Policies</span>
          </router-link>
          <router-link to="/financial/insurance/claims" class="menu-item sub-item" active-class="active">
            <AlertCircle class="sub-icon" />
            <span>Claim Requests</span>
          </router-link>
          <router-link to="/financial/insurance/settlements" class="menu-item sub-item" active-class="active">
            <HandCoins class="sub-icon" />
            <span>Claim Settlements</span>
          </router-link>
        </div>
      </div>

      <!-- Transactions -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('transactions')">
          <span class="section-title-wrapper">
            <ArrowLeftRight class="section-icon" />
            <span>Transactions</span>
          </span>
          <component :is="expandedSections.transactions ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.transactions" class="section-items">
          <router-link to="/financial/transactions/disbursed" class="menu-item sub-item" active-class="active">
            <Send class="sub-icon" />
            <span>Disbursed Funds</span>
          </router-link>
          <router-link to="/financial/transactions/escrow" class="menu-item sub-item" active-class="active">
            <Lock class="sub-icon" />
            <span>Escrow Accounts</span>
          </router-link>
          <router-link to="/financial/transactions/history" class="menu-item sub-item" active-class="active">
            <History class="sub-icon" />
            <span>Financial History</span>
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
          <router-link to="/financial/reports/financial" class="menu-item sub-item" active-class="active">
            <FileSpreadsheet class="sub-icon" />
            <span>Financial Reports</span>
          </router-link>
          <router-link to="/financial/reports/risk" class="menu-item sub-item" active-class="active">
            <BarChart3 class="sub-icon" />
            <span>Risk Analytics</span>
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
          <router-link to="/financial/account/licenses" class="menu-item sub-item" active-class="active">
            <Building2 class="sub-icon" />
            <span>Regulatory Licenses</span>
          </router-link>
          <router-link to="/financial/account/notifications" class="menu-item sub-item" active-class="active">
            <Bell class="sub-icon" />
            <span>Notifications</span>
          </router-link>
          <router-link to="/financial/account/messages" class="menu-item sub-item" active-class="active">
            <Mail class="sub-icon" />
            <span>Messages</span>
          </router-link>
          <router-link to="/financial/account/profile" class="menu-item sub-item" active-class="active">
            <Building class="sub-icon" />
            <span>Institution Profile</span>
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
  ChevronRight, ChevronDown, LayoutDashboard, CreditCard, FileText, 
  CheckCircle2, CalendarClock, ShieldAlert, Umbrella, FilePlus, 
  FileCheck, AlertCircle, HandCoins, ArrowLeftRight, Send, Lock, 
  History, PieChart, FileSpreadsheet, BarChart3, UserCog, Building2, 
  Bell, Mail, Building, LogOut 
} from 'lucide-vue-next'

const emit = defineEmits(['logout'])

const expandedSections = ref({
  loans: true,
  insurance: false,
  transactions: false,
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
  color: #6366f1;
  background: #eef2ff;
  font-weight: 500;
}

.main-dashboard-link.active .sub-icon {
  color: #6366f1;
}

.main-dashboard-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #6366f1;
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
  color: #6366f1;
  background: #eef2ff;
  font-weight: 500;
}

.menu-item.active .sub-icon {
  color: #6366f1;
}

.menu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #6366f1;
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