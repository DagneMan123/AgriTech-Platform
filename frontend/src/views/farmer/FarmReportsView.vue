<template>
  <div class="farmer-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    
    <div class="farmer-page">
      <div class="page-container">
        <div class="page-header">
          <h1>Farm Reports</h1>
          <p>Comprehensive farm performance and activity reports</p>
        </div>

        <div class="page-content-wrapper">
          <!-- Modern Professional Filter Container -->
          <div class="filters-card">
            <div class="search-input-wrapper">
              <input 
                v-model="filters.search" 
                type="text" 
                placeholder="Search reports..." 
                class="dark-input" 
              />
            </div>

            <div class="select-wrapper">
              <select v-model="filters.period" class="dark-select">
                <option value="all">All Time</option>
                <option value="month">This Month</option>
                <option value="quarter">This Quarter</option>
                <option value="year">This Year</option>
              </select>
            </div>

            <button @click="downloadReport" class="btn-download">
              Download
            </button>
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
  transition: background-color 0.3s ease;
}

.farmer-layout.dark {
  background: #0b0f17;
}

.farmer-layout.dark .farmer-page {
  background-color: #0b0f17;
}

.page-container {
  padding: 0;
}

.page-header {
  background: white;
  padding: 30px;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  margin-bottom: 30px;
}

.farmer-layout.dark .page-header {
  background: #131b2e;
  border-bottom-color: #1e293b;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.page-header h1 {
  font-size: 28px;
  font-weight: 800;
  margin: 0 0 8px 0;
  transition: color 0.3s ease;
}

.farmer-layout.light .page-header h1 {
  color: #1f2937;
}

.farmer-layout.dark .page-header h1 {
  color: #f1f5f9;
}

.page-header p {
  margin: 0;
  font-size: 14px;
  transition: color 0.3s ease;
}

.farmer-layout.light .page-header p {
  color: #6b7280;
}

.farmer-layout.dark .page-header p {
  color: #cbd5e1;
}

.page-content-wrapper {
  padding: 0 30px 30px 30px;
}

/* =========================================================
   Filter Card Container & Input Styling
   ========================================================= */
.filters-card {
  background-color: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  box-sizing: border-box;
}

.farmer-layout.dark .filters-card {
  background-color: #131b2e;
  border-color: #1e293b;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.search-input-wrapper {
  flex: 1;
}

.dark-input,
.dark-select {
  width: 100%;
  background-color: #f9fafb;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  color: #1f2937;
  font-size: 14px;
  padding: 10px 14px;
  outline: none;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.farmer-layout.dark .dark-input,
.farmer-layout.dark .dark-select {
  background-color: #1a2338;
  border-color: #2d3f52;
  color: #f3f4f6;
}

.dark-input::placeholder {
  color: #9ca3af;
}

.farmer-layout.dark .dark-input::placeholder {
  color: #94a3b8;
}

.dark-input:hover,
.dark-select:hover {
  border-color: #9ca3af;
}

.farmer-layout.dark .dark-input:hover,
.farmer-layout.dark .dark-select:hover {
  border-color: #475569;
}

.dark-input:focus,
.dark-select:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

.select-wrapper {
  width: 180px;
  flex-shrink: 0;
}

.dark-select {
  cursor: pointer;
}

.farmer-layout.dark .dark-select option {
  background-color: #1a2338;
  color: #f3f4f6;
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
  white-space: nowrap;
  transition: background 0.3s, transform 0.1s;
}

.btn-download:hover {
  background: #059669;
}

/* =========================================================
   Reports Grid & Cards Styling
   ========================================================= */
.reports-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.report-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
}

.farmer-layout.dark .report-card {
  background: #131b2e;
  border-color: #1e293b;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.report-header {
  background: #f8fafc;
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.3s ease;
}

.farmer-layout.dark .report-header {
  background: #1a2338;
  border-bottom-color: #2d3f52;
}

.report-header h3 {
  margin: 0;
  font-size: 16px;
  color: #1f2937;
  font-weight: 600;
}

.farmer-layout.dark .report-header h3 {
  color: #f1f5f9;
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

.farmer-layout.dark .badge {
  background: #1e3a8a;
  color: #60a5fa;
}

.farmer-layout.dark .badge.success {
  background: #064e3b;
  color: #34d399;
}

.report-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.metric {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
  font-size: 14px;
}

.farmer-layout.dark .metric {
  border-bottom-color: #1e293b;
}

.metric span:first-child {
  color: #6b7280;
}

.farmer-layout.dark .metric span:first-child {
  color: #94a3b8;
}

.metric .value {
  color: #1f2937;
  font-weight: 600;
}

.farmer-layout.dark .metric .value {
  color: #f1f5f9;
}

.btn-view {
  background: #f8fafc;
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
  background: #e2e8f0;
}

.farmer-layout.dark .btn-view {
  background: #1a2338;
  border-color: #2d3f52;
  color: #f1f5f9;
}

.farmer-layout.dark .btn-view:hover {
  background: #232e48;
}

/* =========================================================
   Chart Section Styling
   ========================================================= */
.chart-section {
  background: white;
  border: 1px solid #e5e7eb;
  padding: 24px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.farmer-layout.dark .chart-section {
  background: #131b2e;
  border-color: #1e293b;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.chart-section h2 {
  font-size: 18px;
  margin: 0 0 20px 0;
  color: #1f2937;
}

.farmer-layout.dark .chart-section h2 {
  color: #f1f5f9;
}

.chart-placeholder {
  background: #f9fafb;
  border: 2px dashed #d1d5db;
  border-radius: 6px;
  padding: 60px 20px;
  text-align: center;
  color: #9ca3af;
  min-height: 250px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.farmer-layout.dark .chart-placeholder {
  background: #1a2338;
  border-color: #2d3f52;
  color: #94a3b8;
}

/* Responsive Handling */
@media (max-width: 768px) {
  .farmer-page {
    margin-left: 0;
  }

  .page-content-wrapper {
    padding: 0 16px 20px 16px;
  }

  .filters-card {
    flex-direction: column;
    align-items: stretch;
  }

  .select-wrapper {
    width: 100%;
  }

  .btn-download {
    width: 100%;
  }
}
</style>