<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    
    <div class="farmer-page">
      <div class="page-container">
        <div class="page-header">
          <h1>Production Reports</h1>
          <p>Monitor crop yields, growth cycles, and productivity metrics</p>
        </div>

        <div class="controls">
          <input v-model="filters.search" type="text" placeholder="Search by crop..." class="search-input" />
          <select v-model="filters.crop" class="filter-select">
            <option value="">All Crops</option>
            <option value="tomato">Tomatoes</option>
            <option value="lettuce">Lettuce</option>
            <option value="carrot">Carrots</option>
            <option value="pepper">Peppers</option>
          </select>
          <button @click="generateReport" class="btn-generate">Generate Report</button>
        </div>

        <div class="summary-cards">
          <div class="summary-card">
            <h4>Total Yield This Season</h4>
            <p class="amount">8,450 kg</p>
            <span class="change positive">+15% vs last season</span>
          </div>
          <div class="summary-card">
            <h4>Average Yield Per Crop</h4>
            <p class="amount">1,056 kg</p>
            <span class="change">per crop</span>
          </div>
          <div class="summary-card">
            <h4>Crops In Progress</h4>
            <p class="amount">8</p>
            <span class="change">active cycles</span>
          </div>
          <div class="summary-card">
            <h4>Harvested This Month</h4>
            <p class="amount">1,240 kg</p>
            <span class="change">4 crops</span>
          </div>
        </div>

        <div class="reports-section">
          <div class="production-table">
            <h2>Crop Production Details</h2>
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Crop</th>
                    <th>Farm</th>
                    <th>Planted</th>
                    <th>Expected Yield</th>
                    <th>Current Status</th>
                    <th>Growth %</th>
                    <th>Days to Harvest</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Tomatoes</td>
                    <td>Main Farm</td>
                    <td>Jan 15</td>
                    <td>1,500 kg</td>
                    <td><span class="status-badge growing">Growing</span></td>
                    <td>75%</td>
                    <td>20 days</td>
                  </tr>
                  <tr>
                    <td>Lettuce</td>
                    <td>North Plot</td>
                    <td>Feb 01</td>
                    <td>800 kg</td>
                    <td><span class="status-badge growing">Growing</span></td>
                    <td>60%</td>
                    <td>35 days</td>
                  </tr>
                  <tr>
                    <td>Carrots</td>
                    <td>Main Farm</td>
                    <td>Dec 15</td>
                    <td>2,000 kg</td>
                    <td><span class="status-badge ready">Ready</span></td>
                    <td>100%</td>
                    <td>Harvest now</td>
                  </tr>
                  <tr>
                    <td>Peppers</td>
                    <td>South Plot</td>
                    <td>Jan 20</td>
                    <td>1,200 kg</td>
                    <td><span class="status-badge growing">Growing</span></td>
                    <td>55%</td>
                    <td>45 days</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="production-table">
            <h2>Recent Harvests</h2>
            <div class="table-responsive">
              <table>
                <thead>
                  <tr>
                    <th>Crop</th>
                    <th>Farm</th>
                    <th>Harvest Date</th>
                    <th>Yield</th>
                    <th>Quality Grade</th>
                    <th>Sold Amount</th>
                    <th>Revenue</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Lettuce</td>
                    <td>North Plot</td>
                    <td>Mar 10</td>
                    <td>750 kg</td>
                    <td><span class="grade-a">Grade A</span></td>
                    <td>650 kg</td>
                    <td>$1,950</td>
                  </tr>
                  <tr>
                    <td>Spinach</td>
                    <td>West Plot</td>
                    <td>Mar 05</td>
                    <td>420 kg</td>
                    <td><span class="grade-a">Grade A</span></td>
                    <td>400 kg</td>
                    <td>$1,200</td>
                  </tr>
                  <tr>
                    <td>Tomatoes</td>
                    <td>Main Farm</td>
                    <td>Feb 28</td>
                    <td>1,200 kg</td>
                    <td><span class="grade-b">Grade B</span></td>
                    <td>1,000 kg</td>
                    <td>$2,500</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="chart-section">
          <h2>Production Trends</h2>
          <div class="chart-placeholder">
            <p>Crop yield and production trends over time</p>
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
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()

const filters = ref({
  search: '',
  crop: ''
})

const generateReport = () => {
  alert('Report generated successfully')
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

.farmer-page {
  margin-left: 260px;
  flex: 1;
  background-color: #f9fafb;
  min-height: 100vh;
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
}

.page-header p {
  color: #6b7280;
  margin: 0;
  font-size: 14px;
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
}

.search-input {
  flex: 1;
  min-width: 200px;
}

.filter-select {
  min-width: 120px;
}

.btn-generate {
  background: #10b981;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: background 0.3s;
}

.btn-generate:hover {
  background: #059669;
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
}

.summary-card h4 {
  color: #6b7280;
  font-size: 12px;
  text-transform: uppercase;
  margin: 0 0 10px 0;
  font-weight: 600;
}

.summary-card .amount {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.summary-card .change {
  font-size: 12px;
  color: #9ca3af;
}

.summary-card .change.positive {
  color: #059669;
}

.reports-section {
  display: flex;
  flex-direction: column;
  gap: 30px;
  margin-bottom: 30px;
}

.production-table {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.production-table h2 {
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  margin: 0;
  font-size: 18px;
  color: #1f2937;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.production-table table {
  width: 100%;
  border-collapse: collapse;
  min-width: 600px;
}

.production-table thead {
  background: #f9fafb;
}

.production-table th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #6b7280;
  font-size: 13px;
  text-transform: uppercase;
  border-bottom: 1px solid #e5e7eb;
}

.production-table td {
  padding: 16px;
  border-bottom: 1px solid #f3f4f6;
  font-size: 14px;
  color: #1f2937;
}

.production-table tbody tr:hover {
  background: #f9fafb;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.growing {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge.ready {
  background: #d1fae5;
  color: #065f46;
}

.grade-a {
  display: inline-block;
  background: #d1fae5;
  color: #065f46;
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.grade-b {
  display: inline-block;
  background: #fed7aa;
  color: #92400e;
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.chart-section {
  background: white;
  padding: 24px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-section h2 {
  font-size: 18px;
  margin: 0 0 20px 0;
  color: #1f2937;
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
}

@media (max-width: 768px) {
  .farmer-page {
    margin-left: 0;
  }
}
</style>