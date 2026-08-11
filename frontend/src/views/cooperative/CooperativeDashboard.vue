<template>
  <div class="cooperative-layout">
    <CooperativeSidebar @logout="handleLogout" />
    <div class="cooperative-dashboard">
      <!-- Header -->
      <div class="dashboard-header">
        <h1>Cooperative Dashboard</h1>
        <p>Manage collective agriculture operations</p>
      </div>

      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="card-icon members">
            <i class="fas fa-users"></i>
          </div>
          <div class="card-content">
            <h3>Total Members</h3>
            <p class="card-value">{{ dashboard?.summary.total_members || 0 }}</p>
            <p class="card-sub">Active members</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon farms">
            <i class="fas fa-home"></i>
          </div>
          <div class="card-content">
            <h3>Member Farms</h3>
            <p class="card-value">{{ dashboard?.summary.total_farms || 0 }}</p>
            <p class="card-sub">{{ dashboard?.summary.total_farm_area }}ha total</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon sales">
            <i class="fas fa-cart-arrow-down"></i>
          </div>
          <div class="card-content">
            <h3>Bulk Sales</h3>
            <p class="card-value">${{ formatNumber(dashboard?.summary.bulk_sales_revenue) }}</p>
            <p class="card-sub">Revenue</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon purchases">
            <i class="fas fa-cart-plus"></i>
          </div>
          <div class="card-content">
            <h3>Bulk Purchases</h3>
            <p class="card-value">${{ formatNumber(dashboard?.summary.bulk_purchases_cost) }}</p>
            <p class="card-sub">Expenses</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon centers">
            <i class="fas fa-warehouse"></i>
          </div>
          <div class="card-content">
            <h3>Collection Centers</h3>
            <p class="card-value">{{ dashboard?.summary.total_centers || 0 }}</p>
            <p class="card-sub">Operational</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon profit">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="card-content">
            <h3>Net Profit</h3>
            <p class="card-value">${{ formatNumber(dashboard?.summary.net_profit) }}</p>
            <p class="card-sub">This period</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="dashboard-tabs">
        <div class="tab-buttons">
          <button 
            v-for="tab in tabs" 
            :key="tab"
            :class="['tab-btn', { active: activeTab === tab }]"
            @click="activeTab = tab"
          >
            {{ formatTabName(tab) }}
          </button>
        </div>

        <!-- Member Management Tab -->
        <div v-show="activeTab === 'members'" class="tab-content">
          <div class="section-header">
            <h2>Member Management</h2>
            <div class="filter-controls">
              <select v-model="memberFilter" class="filter-select">
                <option value="">All Members</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          <div class="members-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Farm</th>
                  <th>Area (ha)</th>
                  <th>Status</th>
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="member in dashboard?.members || []" :key="member.id">
                  <td>#{{ member.id }}</td>
                  <td>{{ member.name }}</td>
                  <td>{{ member.farm_name }}</td>
                  <td>{{ member.farm_area }}</td>
                  <td><span :class="['status-badge', `status-${member.status}`]">{{ member.status }}</span></td>
                  <td>{{ formatDate(member.joined_date) }}</td>
                  <td class="action-buttons">
                    <button class="btn-small btn-view">View</button>
                    <button class="btn-small btn-edit">Edit</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Collection Centers Tab -->
        <div v-show="activeTab === 'centers'" class="tab-content">
          <div class="section-header">
            <h2>Collection Centers</h2>
            <button class="btn-primary" @click="showCenterDialog = true">+ New Center</button>
          </div>

          <div class="centers-grid">
            <div v-for="center in dashboard?.collection_centers || []" :key="center.id" class="center-card">
              <div class="center-header">
                <h4>{{ center.name }}</h4>
                <span class="center-location">{{ center.location }}</span>
              </div>
              <div class="center-details">
                <p><strong>Capacity:</strong> {{ center.total_capacity }}kg</p>
                <p><strong>Current Stock:</strong> {{ center.current_stock }}kg</p>
                <p><strong>Utilization:</strong> {{ center.utilization_percentage }}%</p>
              </div>
              <div class="capacity-bar">
                <div class="capacity-fill" :style="{ width: center.utilization_percentage + '%' }"></div>
              </div>
              <div class="center-actions">
                <button class="btn-small btn-edit">Edit</button>
                <button class="btn-small btn-view">Details</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Bulk Sales Tab -->
        <div v-show="activeTab === 'sales'" class="tab-content">
          <div class="section-header">
            <h2>Bulk Sales</h2>
            <button class="btn-primary" @click="showSaleDialog = true">+ New Sale</button>
          </div>

          <div class="sales-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Buyer</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sale in dashboard?.bulk_sales || []" :key="sale.id">
                  <td>#{{ sale.id }}</td>
                  <td>{{ sale.product_name }}</td>
                  <td>{{ sale.quantity }}kg</td>
                  <td>{{ sale.buyer_name }}</td>
                  <td>${{ formatNumber(sale.amount) }}</td>
                  <td><span :class="['status-badge', `status-${sale.status}`]">{{ sale.status }}</span></td>
                  <td>{{ formatDate(sale.sale_date) }}</td>
                  <td class="action-buttons">
                    <button class="btn-small btn-view">View</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Bulk Purchases Tab -->
        <div v-show="activeTab === 'purchases'" class="tab-content">
          <div class="section-header">
            <h2>Bulk Purchases</h2>
            <button class="btn-primary" @click="showPurchaseDialog = true">+ New Purchase</button>
          </div>

          <div class="purchases-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Item</th>
                  <th>Quantity</th>
                  <th>Supplier</th>
                  <th>Cost</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="purchase in dashboard?.bulk_purchases || []" :key="purchase.id">
                  <td>#{{ purchase.id }}</td>
                  <td>{{ purchase.item_name }}</td>
                  <td>{{ purchase.quantity }}</td>
                  <td>{{ purchase.supplier_name }}</td>
                  <td>${{ formatNumber(purchase.cost) }}</td>
                  <td><span :class="['status-badge', `status-${purchase.status}`]">{{ purchase.status }}</span></td>
                  <td>{{ formatDate(purchase.purchase_date) }}</td>
                  <td class="action-buttons">
                    <button class="btn-small btn-view">View</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Financial Reports Tab -->
        <div v-show="activeTab === 'reports'" class="tab-content">
          <div class="section-header">
            <h2>Financial Reports</h2>
            <div class="period-selector">
              <button 
                v-for="period in [7, 30, 90]"
                :key="period"
                :class="['period-btn', { active: selectedPeriod === period }]"
                @click="selectedPeriod = period"
              >
                {{ period }} Days
              </button>
            </div>
          </div>

          <div class="financial-cards">
            <div class="financial-card">
              <h3>Total Sales</h3>
              <p class="big-number">${{ formatNumber(dashboard?.financial_summary?.total_sales) }}</p>
            </div>
            <div class="financial-card">
              <h3>Total Purchases</h3>
              <p class="big-number">${{ formatNumber(dashboard?.financial_summary?.total_purchases) }}</p>
            </div>
            <div class="financial-card">
              <h3>Member Payouts</h3>
              <p class="big-number">${{ formatNumber(dashboard?.financial_summary?.member_payouts) }}</p>
            </div>
            <div class="financial-card profit">
              <h3>Net Profit</h3>
              <p class="big-number">${{ formatNumber(dashboard?.financial_summary?.net_profit) }}</p>
            </div>
          </div>

          <div class="top-members">
            <h3>Top Performing Members</h3>
            <div class="top-list">
              <div v-for="member in dashboard?.top_members || []" :key="member.id" class="top-item">
                <span class="member-rank">{{ member.rank }}</span>
                <span class="member-name">{{ member.name }}</span>
                <span class="member-sales">${{ formatNumber(member.total_sales) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="recent-section">
        <div class="recent-card">
          <h2>Recent Sales</h2>
          <div class="list-items">
            <div v-for="sale in dashboard?.recent_sales?.slice(0, 5) || []" :key="sale.id" class="list-item">
              <span class="item-product">{{ sale.product_name }}</span>
              <span class="item-amount">${{ formatNumber(sale.amount) }}</span>
              <span class="item-date">{{ formatDate(sale.date) }}</span>
            </div>
          </div>
        </div>

        <div class="recent-card">
          <h2>Cooperative Stats</h2>
          <div class="list-items">
            <div class="list-item">
              <span class="label">Member Growth:</span>
              <span class="value">{{ dashboard?.statistics?.member_growth || 0 }}%</span>
            </div>
            <div class="list-item">
              <span class="label">Avg Farm Size:</span>
              <span class="value">{{ dashboard?.statistics?.avg_farm_size || 0 }}ha</span>
            </div>
            <div class="list-item">
              <span class="label">Total Production:</span>
              <span class="value">{{ formatNumber(dashboard?.statistics?.total_production) }}kg</span>
            </div>
            <div class="list-item">
              <span class="label">Market Reach:</span>
              <span class="value">{{ dashboard?.statistics?.market_reach || 0 }} buyers</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
import CooperativeSidebar from '@/components/Sidebar/CooperativeSidebar.vue'

const auth = useAuthStore()
const router = useRouter()
const activeTab = ref('members')
const tabs = ['members', 'centers', 'sales', 'purchases', 'reports']

const dashboard = ref(null)
const memberFilter = ref('')
const selectedPeriod = ref(30)
const showCenterDialog = ref(false)
const showSaleDialog = ref(false)
const showPurchaseDialog = ref(false)

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  try {
    const res = await fetch('/api/cooperative/dashboard', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    const data = await res.json()
    dashboard.value = data.data
  } catch (error) {
    console.error('Error fetching dashboard:', error)
  }
}

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatTabName = (tab) => {
  const names = {
    members: 'Member Management',
    centers: 'Collection Centers',
    sales: 'Bulk Sales',
    purchases: 'Bulk Purchases',
    reports: 'Financial Reports'
  }
  return names[tab] || tab
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.cooperative-layout {
  display: flex;
  height: 100vh;
}

.cooperative-dashboard {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f5f5f5;
  padding: 20px;
}

.dashboard-header {
  margin-bottom: 30px;
}

.dashboard-header h1 {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.dashboard-header p {
  color: #666;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.summary-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.card-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.card-icon.members { background-color: #ec4899; }
.card-icon.farms { background-color: #10b981; }
.card-icon.sales { background-color: #3b82f6; }
.card-icon.purchases { background-color: #f59e0b; }
.card-icon.centers { background-color: #8b5cf6; }
.card-icon.profit { background-color: #06b6d4; }

.card-content h3 {
  font-size: 12px;
  color: #666;
  margin-bottom: 5px;
  text-transform: uppercase;
  font-weight: 600;
}

.card-value {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.card-sub {
  font-size: 12px;
  color: #999;
}

.dashboard-tabs {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 30px;
}

.tab-buttons {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
  overflow-x: auto;
}

.tab-btn {
  flex: 1;
  padding: 15px 20px;
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
  font-weight: 500;
  transition: all 0.3s;
  border-bottom: 3px solid transparent;
  white-space: nowrap;
}

.tab-btn:hover {
  color: #ec4899;
}

.tab-btn.active {
  color: #ec4899;
  border-bottom-color: #ec4899;
}

.tab-content {
  padding: 25px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
}

.section-header h2 {
  font-size: 20px;
  font-weight: bold;
  color: #333;
}

.filter-controls {
  display: flex;
  gap: 10px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
}

.btn-primary {
  background: #ec4899;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover {
  background: #be185d;
}

.members-table-container,
.sales-table-container,
.purchases-table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.data-table th {
  background-color: #f9fafb;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 12px;
  border-bottom: 1px solid #e5e7eb;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-active {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-inactive {
  background-color: #f3f4f6;
  color: #6b7280;
}

.status-badge.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.status-completed {
  background-color: #d1fae5;
  color: #065f46;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-small {
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-view {
  background: #ec4899;
  color: white;
}

.btn-edit {
  background: #3b82f6;
  color: white;
}

.centers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.center-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 15px;
  transition: all 0.3s;
}

.center-card:hover {
  border-color: #ec4899;
  box-shadow: 0 2px 8px rgba(236, 72, 153, 0.1);
}

.center-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 10px;
}

.center-header h4 {
  margin: 0;
  color: #333;
}

.center-location {
  font-size: 12px;
  color: #999;
}

.center-details p {
  margin: 5px 0;
  font-size: 13px;
  color: #666;
}

.capacity-bar {
  width: 100%;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
  margin: 10px 0;
}

.capacity-fill {
  height: 100%;
  background: #ec4899;
  transition: width 0.3s;
}

.center-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.financial-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.financial-card {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.financial-card.profit {
  background: linear-gradient(135deg, #ec4899, #db2777);
  color: white;
}

.financial-card.profit h3 {
  color: white;
}

.financial-card.profit .big-number {
  color: white;
}

.financial-card h3 {
  color: #666;
  margin-bottom: 10px;
  font-size: 14px;
}

.big-number {
  font-size: 28px;
  font-weight: bold;
  color: #333;
}

.period-selector {
  display: flex;
  gap: 10px;
}

.period-btn {
  padding: 8px 16px;
  background: #f3f4f6;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s;
}

.period-btn.active {
  background: #ec4899;
  color: white;
}

.top-members {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.top-members h3 {
  margin: 0 0 15px 0;
  color: #333;
}

.top-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.top-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 10px;
  background: white;
  border-radius: 4px;
}

.member-rank {
  background: #ec4899;
  color: white;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 12px;
}

.member-name {
  flex: 1;
  font-weight: 500;
  color: #333;
}

.member-sales {
  font-size: 13px;
  color: #666;
  font-weight: 600;
}

.recent-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.recent-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recent-card h2 {
  font-size: 18px;
  margin-bottom: 15px;
  color: #333;
}

.list-items {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: #f9fafb;
  border-radius: 4px;
}

.item-product {
  font-weight: 600;
  color: #333;
}

.item-amount {
  color: #ec4899;
  font-weight: 600;
}

.item-date {
  font-size: 12px;
  color: #999;
}

.label {
  font-weight: 600;
  color: #666;
}

.value {
  font-weight: 700;
  color: #333;
}

@media (max-width: 768px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .tab-buttons {
    flex-wrap: wrap;
  }
  
  .centers-grid {
    grid-template-columns: 1fr;
  }
  
  .financial-cards {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
