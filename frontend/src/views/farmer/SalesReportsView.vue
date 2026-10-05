<template>
  <div class="farmer-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    
    <div class="farmer-page">
      <div class="page-container">
        <div class="page-header">
          <h1>Sales Reports</h1>
          <p>Track your product sales and revenue performance</p>
        </div>

        <div class="controls">
          <input v-model="filters.search" type="text" placeholder="Search products..." class="search-input" />
          <select v-model="filters.period" class="filter-select">
            <option value="week">Last Week</option>
            <option value="month">Last Month</option>
            <option value="quarter">Last Quarter</option>
            <option value="year">Last Year</option>
          </select>
          <button @click="exportData" class="btn-export">Export CSV</button>
        </div>

        <div class="summary-cards">
          <div class="summary-card">
            <h4>Total Revenue</h4>
            <p class="amount">$12,450</p>
            <span class="change positive">+12.5% from last period</span>
          </div>
          <div class="summary-card">
            <h4>Total Orders</h4>
            <p class="amount">124</p>
            <span class="change positive">+8 from last period</span>
          </div>
          <div class="summary-card">
            <h4>Average Order Value</h4>
            <p class="amount">$100.40</p>
            <span class="change">No change</span>
          </div>
          <div class="summary-card">
            <h4>Best Seller</h4>
            <p class="amount">Tomatoes</p>
            <span class="change">45 units sold</span>
          </div>
        </div>

        <div class="reports-section">
          <div class="sales-table">
            <h2>Sales By Product</h2>
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Units Sold</th>
                    <th>Price/Unit</th>
                    <th>Total Revenue</th>
                    <th>Orders</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Tomatoes</td>
                    <td>450</td>
                    <td>$2.50</td>
                    <td>$1,125</td>
                    <td>45</td>
                    <td><span class="status-badge active">Active</span></td>
                  </tr>
                  <tr>
                    <td>Carrots</td>
                    <td>320</td>
                    <td>$1.80</td>
                    <td>$576</td>
                    <td>32</td>
                    <td><span class="status-badge active">Active</span></td>
                  </tr>
                  <tr>
                    <td>Lettuce</td>
                    <td>280</td>
                    <td>$3.00</td>
                    <td>$840</td>
                    <td>28</td>
                    <td><span class="status-badge active">Active</span></td>
                  </tr>
                  <tr>
                    <td>Peppers</td>
                    <td>200</td>
                    <td>$3.50</td>
                    <td>$700</td>
                    <td>20</td>
                    <td><span class="status-badge active">Active</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="sales-table">
            <h2>Top Buyers</h2>
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Buyer Name</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Avg Order Value</th>
                    <th>Last Order</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Local Market Store</td>
                    <td>32</td>
                    <td>$4,200</td>
                    <td>$131.25</td>
                    <td>2 days ago</td>
                  </tr>
                  <tr>
                    <td>Fresh Produce Co.</td>
                    <td>28</td>
                    <td>$3,850</td>
                    <td>$137.50</td>
                    <td>3 days ago</td>
                  </tr>
                  <tr>
                    <td>Organic Store</td>
                    <td>24</td>
                    <td>$2,800</td>
                    <td>$116.67</td>
                    <td>1 day ago</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="chart-section">
          <h2>Revenue Trend</h2>
          <div class="chart-placeholder">
            <p>Monthly revenue trend chart</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useTheme } from '@/composables/useTheme'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()
const { isDark, isLight } = useTheme()

const filters = ref({
  search: '',
  period: 'month'
})

const exportData = () => {
  alert('CSV export initiated')
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout {
  display: flex;
  min-height: 100vh;
}

.farmer-layout.dark {
  background: #0b0f17;
}

.farmer-page {
  margin-left: 260px;
  flex: 1;
  background-color: #f9fafb;
  min-height: 100vh;
  transition: background-color 0.3s ease;
}

.farmer-layout.dark .farmer-page {
  background-color: #0b0f17;
}

.page-container {
  padding: 30px;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  font-size: 28px;
  color: #1f2937;
  margin: 0 0 8px 0;
  font-weight: 700;
  transition: color 0.3s ease;
}

.dark .page-header h1 {
  color: #f1f5f9;
}

.page-header p {
  color: #6b7280;
  margin: 0;
  font-size: 14px;
  transition: color 0.3s ease;
}

.dark .page-header p {
  color: #cbd5e1;
}

.controls {
  display: flex;
  gap: 12px;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.search-input,
.filter-select {
  padding: 10px 14px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  color: #1f2937;
  transition: all 0.3s ease;
}

.dark .search-input,
.dark .filter-select {
  background: #0f1620;
  border-color: #2d3f52;
  color: #f1f5f9;
}

.dark .search-input:focus,
.dark .filter-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.search-input {
  flex: 1;
  min-width: 200px;
}

.filter-select {
  min-width: 120px;
}

.btn-export {
  background: #3b82f6;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: background 0.3s;
}

.btn-export:hover {
  background: #2563eb;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.dark .summary-card {
  background: #1a2338;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.summary-card h4 {
  color: #6b7280;
  font-size: 12px;
  text-transform: uppercase;
  margin: 0 0 10px 0;
  font-weight: 600;
  transition: color 0.3s ease;
}

.dark .summary-card h4 {
  color: #cbd5e1;
}

.summary-card .amount {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 8px 0;
  transition: color 0.3s ease;
}

.dark .summary-card .amount {
  color: #f1f5f9;
}

.summary-card .change {
  font-size: 12px;
  color: #9ca3af;
  transition: color 0.3s ease;
}

.dark .summary-card .change {
  color: #94a3b8;
}

.summary-card .change.positive {
  color: #059669;
}

.dark .summary-card .change.positive {
  color: #10b981;
}

.reports-section {
  display: flex;
  flex-direction: column;
  gap: 30px;
  margin-bottom: 30px;
}

.sales-table {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: all 0.3s ease;
}

.dark .sales-table {
  background: #1a2338;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.sales-table h2 {
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  margin: 0;
  font-size: 18px;
  color: #1f2937;
  transition: all 0.3s ease;
}

.dark .sales-table h2 {
  color: #f1f5f9;
  border-bottom-color: #2d3f52;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.sales-table table {
  width: 100%;
  border-collapse: collapse;
  min-width: 600px;
}

.sales-table thead {
  background: #f9fafb;
  transition: background-color 0.3s ease;
}

.dark .sales-table thead {
  background: #0f1620;
}

.sales-table th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #6b7280;
  font-size: 13px;
  text-transform: uppercase;
  border-bottom: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.dark .sales-table th {
  color: #cbd5e1;
  border-bottom-color: #2d3f52;
}

.sales-table td {
  padding: 16px;
  border-bottom: 1px solid #f3f4f6;
  font-size: 14px;
  color: #1f2937;
  transition: all 0.3s ease;
}

.dark .sales-table td {
  color: #f1f5f9;
  border-bottom-color: #2d3f52;
}

.sales-table tbody tr:hover {
  background: #f9fafb;
}

.dark .sales-table tbody tr:hover {
  background: rgba(45, 63, 82, 0.5);
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.status-badge.active {
  background: #d1fae5;
  color: #065f46;
}

.dark .status-badge.active {
  background: #064e3b;
  color: #10b981;
}

.chart-section {
  background: white;
  padding: 24px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.dark .chart-section {
  background: #1a2338;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.chart-section h2 {
  font-size: 18px;
  margin: 0 0 20px 0;
  color: #1f2937;
  transition: color 0.3s ease;
}

.dark .chart-section h2 {
  color: #f1f5f9;
}

.chart-placeholder {
  background: #f9fafb;
  border: 2px dashed #d1d5db;
  border-radius: 6px;
  padding: 60px 20px;
  text-align: center;
  color: #9ca3af;
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.dark .chart-placeholder {
  background: #0f1620;
  border-color: #2d3f52;
  color: #94a3b8;
}

@media (max-width: 768px) {
  .farmer-page {
    margin-left: 0;
  }
}
</style>