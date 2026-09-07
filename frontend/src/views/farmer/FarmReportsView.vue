<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    
    <div class="farmer-page">
      <div class="page-container">
        <div class="page-header">
          <h1>Farm Reports</h1>
          <p>Comprehensive farm performance and activity reports</p>
        </div>

        <div class="controls">
          <input v-model="filters.search" type="text" placeholder="Search reports..." class="search-input" />
          <select v-model="filters.period" class="filter-select">
            <option value="all">All Time</option>
            <option value="month">This Month</option>
            <option value="quarter">This Quarter</option>
            <option value="year">This Year</option>
          </select>
          <button @click="downloadReport" class="btn-download">Download</button>
        </div>

        <div class="reports-grid">
          <div class="report-card">
            <div class="report-header">
              <h3>Overall Farm Summary</h3>
              <span class="badge">Monthly</span>
            </div>
            <div class="report-body">
              <div class="metric">
                <span>Total Land Area</span>
                <span class="value">45.5 hectares</span>
              </div>
              <div class="metric">
                <span>Active Farms</span>
                <span class="value">3</span>
              </div>
              <div class="metric">
                <span>Current Crops</span>
                <span class="value">8</span>
              </div>
              <div class="metric">
                <span>Soil pH Average</span>
                <span class="value">6.8</span>
              </div>
            </div>
            <button class="btn-view">View Details</button>
          </div>

          <div class="report-card">
            <div class="report-header">
              <h3>Farm Health Report</h3>
              <span class="badge success">Good</span>
            </div>
            <div class="report-body">
              <div class="metric">
                <span>Soil Condition</span>
                <span class="value">Excellent</span>
              </div>
              <div class="metric">
                <span>Water Quality</span>
                <span class="value">Good</span>
              </div>
              <div class="metric">
                <span>Pest Issues</span>
                <span class="value">None</span>
              </div>
              <div class="metric">
                <span>Last Inspection</span>
                <span class="value">5 days ago</span>
              </div>
            </div>
            <button class="btn-view">View Details</button>
          </div>

          <div class="report-card">
            <div class="report-header">
              <h3>Resource Usage Report</h3>
              <span class="badge">Current Month</span>
            </div>
            <div class="report-body">
              <div class="metric">
                <span>Water Usage</span>
                <span class="value">2,450 liters</span>
              </div>
              <div class="metric">
                <span>Fertilizer Applied</span>
                <span class="value">125 kg</span>
              </div>
              <div class="metric">
                <span>Pesticides Used</span>
                <span class="value">45 liters</span>
              </div>
              <div class="metric">
                <span>Labor Hours</span>
                <span class="value">320 hrs</span>
              </div>
            </div>
            <button class="btn-view">View Details</button>
          </div>

          <div class="report-card">
            <div class="report-header">
              <h3>Equipment Usage</h3>
              <span class="badge">Monthly</span>
            </div>
            <div class="report-body">
              <div class="metric">
                <span>Machinery Hours</span>
                <span class="value">156 hrs</span>
              </div>
              <div class="metric">
                <span>Maintenance Due</span>
                <span class="value">2 items</span>
              </div>
              <div class="metric">
                <span>Fuel Consumed</span>
                <span class="value">580 liters</span>
              </div>
              <div class="metric">
                <span>Operational Cost</span>
                <span class="value">$1,240</span>
              </div>
            </div>
            <button class="btn-view">View Details</button>
          </div>
        </div>

        <div class="chart-section">
          <h2>Farm Performance Trends</h2>
          <div class="chart-placeholder">
            <p>Farm productivity and health trends chart</p>
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
  period: 'month'
})

const downloadReport = () => {
  alert('Report download initiated')
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

.btn-download {
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

.btn-download:hover {
  background: #059669;
}

.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.report-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.report-header {
  background: #f3f4f6;
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.report-header h3 {
  margin: 0;
  font-size: 16px;
  color: #1f2937;
  font-weight: 600;
}

.badge {
  background: #dbeafe;
  color: #1e40af;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.badge.success {
  background: #d1fae5;
  color: #065f46;
}

.report-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}

.metric {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #f3f4f6;
  font-size: 14px;
}

.metric span:first-child {
  color: #6b7280;
}

.metric .value {
  color: #1f2937;
  font-weight: 600;
}

.btn-view {
  background: white;
  border: 1px solid #d1d5db;
  color: #1f2937;
  padding: 10px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s;
  margin: 0 16px 16px 16px;
}

.btn-view:hover {
  background: #f9fafb;
  border-color: #9ca3af;
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