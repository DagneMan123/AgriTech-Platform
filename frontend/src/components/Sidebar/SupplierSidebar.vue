<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Supplier Portal</h2>
      <p>Agricultural Inputs</p>
    </div>

    <nav class="sidebar-menu">
      <!-- Dashboard -->
      <router-link to="/supplier/dashboard" class="menu-item main-dashboard-link" active-class="active">
        <LayoutDashboard class="sub-icon" />
        <span>Dashboard</span>
      </router-link>

      <!-- Inventory & Products -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('inventory')">
          <span class="section-title-wrapper">
            <Package class="section-icon" />
            <span>Inventory & Products</span>
          </span>
          <component :is="expandedSections.inventory ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.inventory" class="section-items">
          <router-link to="/supplier/products" class="menu-item sub-item" active-class="active">
            <Boxes class="sub-icon" />
            <span>Product Catalog</span>
          </router-link>
          <router-link to="/supplier/products/add" class="menu-item sub-item" active-class="active">
            <PlusCircle class="sub-icon" />
            <span>Add New Product</span>
          </router-link>
          <router-link to="/supplier/inventory/stock" class="menu-item sub-item" active-class="active">
            <Layers class="sub-icon" />
            <span>Stock Management</span>
          </router-link>
          <router-link to="/supplier/inventory/pricing" class="menu-item sub-item" active-class="active">
            <Tags class="sub-icon" />
            <span>Pricing & Discounts</span>
          </router-link>
        </div>
      </div>

      <!-- Orders & Fulfillment -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('orders')">
          <span class="section-title-wrapper">
            <ShoppingCart class="section-icon" />
            <span>Orders & Fulfillment</span>
          </span>
          <component :is="expandedSections.orders ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.orders" class="section-items">
          <router-link to="/supplier/orders/incoming" class="menu-item sub-item" active-class="active">
            <Inbox class="sub-icon" />
            <span>Incoming Orders</span>
          </router-link>
          <router-link to="/supplier/orders/processing" class="menu-item sub-item" active-class="active">
            <Clock class="sub-icon" />
            <span>Processing Orders</span>
          </router-link>
          <router-link to="/supplier/orders/completed" class="menu-item sub-item" active-class="active">
            <CheckCircle2 class="sub-icon" />
            <span>Completed Orders</span>
          </router-link>
        </div>
      </div>

      <!-- Government & Subsidies -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('government')">
          <span class="section-title-wrapper">
            <Landmark class="section-icon" />
            <span>Government & Subsidies</span>
          </span>
          <component :is="expandedSections.government ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.government" class="section-items">
          <router-link to="/supplier/subsidies/allocations" class="menu-item sub-item" active-class="active">
            <FileSpreadsheet class="sub-icon" />
            <span>Subsidy Allocations</span>
          </router-link>
          <router-link to="/supplier/subsidies/fertilizers" class="menu-item sub-item" active-class="active">
            <Sprout class="sub-icon" />
            <span>Fertilizer Supplies</span>
          </router-link>
          <router-link to="/supplier/subsidies/pesticides" class="menu-item sub-item" active-class="active">
            <ShieldAlert class="sub-icon" />
            <span>Pesticide Supplies</span>
          </router-link>
        </div>
      </div>

      <!-- Logistics & Shipments -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('logistics')">
          <span class="section-title-wrapper">
            <Truck class="section-icon" />
            <span>Logistics & Shipments</span>
          </span>
          <component :is="expandedSections.logistics ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.logistics" class="section-items">
          <router-link to="/supplier/shipments/outbound" class="menu-item sub-item" active-class="active">
            <Send class="sub-icon" />
            <span>Outbound Deliveries</span>
          </router-link>
          <router-link to="/supplier/shipments/requests" class="menu-item sub-item" active-class="active">
            <Truck class="sub-icon" />
            <span>Transport Requests</span>
          </router-link>
          <router-link to="/supplier/shipments/status" class="menu-item sub-item" active-class="active">
            <MapPin class="sub-icon" />
            <span>Delivery Status</span>
          </router-link>
        </div>
      </div>

      <!-- Finance & Payments -->
      <div class="menu-section">
        <button class="section-toggle" @click="toggleSection('finance')">
          <span class="section-title-wrapper">
            <Wallet class="section-icon" />
            <span>Finance & Payments</span>
          </span>
          <component :is="expandedSections.finance ? ChevronDown : ChevronRight" class="toggle-icon" />
        </button>
        <div v-show="expandedSections.finance" class="section-items">
          <router-link to="/supplier/finance/earnings" class="menu-item sub-item" active-class="active">
            <Banknote class="sub-icon" />
            <span>Earnings & Payouts</span>
          </router-link>
          <router-link to="/supplier/finance/invoices" class="menu-item sub-item" active-class="active">
            <Receipt class="sub-icon" />
            <span>Invoices</span>
          </router-link>
          <router-link to="/supplier/finance/history" class="menu-item sub-item" active-class="active">
            <History class="sub-icon" />
            <span>Transaction History</span>
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
          <router-link to="/supplier/reports/sales" class="menu-item sub-item" active-class="active">
            <BarChart2 class="sub-icon" />
            <span>Sales Reports</span>
          </router-link>
          <router-link to="/supplier/reports/inventory" class="menu-item sub-item" active-class="active">
            <BarChart3 class="sub-icon" />
            <span>Inventory Reports</span>
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
          <router-link to="/supplier/account/licenses" class="menu-item sub-item" active-class="active">
            <ShieldCheck class="sub-icon" />
            <span>Business Licenses & Docs</span>
          </router-link>
          <router-link to="/supplier/notifications" class="menu-item sub-item" active-class="active">
            <Bell class="sub-icon" />
            <span>Notifications</span>
          </router-link>
          <router-link to="/supplier/messages" class="menu-item sub-item" active-class="active">
            <Mail class="sub-icon" />
            <span>Messages</span>
          </router-link>
          <router-link to="/supplier/profile" class="menu-item sub-item" active-class="active">
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
  ChevronRight, ChevronDown, LayoutDashboard, Package, Boxes, PlusCircle, 
  Layers, Tags, ShoppingCart, Inbox, Clock, CheckCircle2, Landmark, 
  FileSpreadsheet, Sprout, ShieldAlert, Truck, Send, MapPin, Wallet, 
  Banknote, Receipt, History, PieChart, BarChart2, BarChart3, UserCog, 
  ShieldCheck, Bell, Mail, User, LogOut 
} from 'lucide-vue-next'

const emit = defineEmits(['logout'])

const expandedSections = ref({
  inventory: true,
  orders: true,
  government: false,
  logistics: false,
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
  color: #f59e0b;
  background: #fffbeb;
  font-weight: 500;
}

.main-dashboard-link.active .sub-icon {
  color: #f59e0b;
}

.main-dashboard-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #f59e0b;
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
  color: #f59e0b;
  background: #fffbeb;
  font-weight: 500;
}

.menu-item.active .sub-icon {
  color: #f59e0b;
}

.menu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #f59e0b;
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