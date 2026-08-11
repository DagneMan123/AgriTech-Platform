<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <h1>Reports</h1>
        <p>View and download your farm reports</p>
      </div>

      <div class="content-section">
        <div class="section-header">
          <h2>Sales & Production Reports</h2>
          <div class="period-selector">
            <button v-for="period in [7, 30, 90]" :key="period" class="period-btn">{{ period }} Days</button>
          </div>
        </div>

        <div class="reports-grid">
          <div class="report-card">
            <h3>Total Sales</h3>
            <p class="big-number">$12,500</p>
            <p class="subtitle">This period</p>
          </div>
          <div class="report-card">
            <h3>Total Production</h3>
            <p class="big-number">2,450</p>
            <p class="subtitle">units</p>
          </div>
          <div class="report-card">
            <h3>Avg Order Value</h3>
            <p class="big-number">$850</p>
            <p class="subtitle">Per order</p>
          </div>
          <div class="report-card">
            <h3>Profit Margin</h3>
            <p class="big-number">35%</p>
            <p class="subtitle">Average</p>
          </div>
        </div>
      </div>

      <div class="content-section" style="margin-top: 20px;">
        <h2>Available Reports</h2>
        <div class="reports-list">
          <div class="report-item" v-for="report in ['Monthly Sales Report', 'Production Analysis', 'Crop Yield Report', 'Financial Summary']" :key="report">
            <div class="report-info">
              <h4>{{ report }}</h4>
              <p>Generated on {{ new Date().toLocaleDateString() }}</p>
            </div>
            <div class="report-actions">
              <button class="btn-small btn-view">View</button>
              <button class="btn-small btn-download">Download</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout { display: flex; height: 100vh; }
.farmer-page { margin-left: 260px; flex: 1; overflow-y: auto; background-color: #f5f5f5; padding: 20px; }
.page-header { margin-bottom: 30px; }
.page-header h1 { font-size: 28px; font-weight: bold; color: #333; margin-bottom: 5px; }
.page-header p { color: #666; }
.content-section { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.content-section h2 { font-size: 20px; font-weight: bold; color: #333; margin-bottom: 20px; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px; }
.section-header h2 { margin: 0; }
.period-selector { display: flex; gap: 10px; }
.period-btn { padding: 8px 16px; background-color: #f3f4f6; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s; }
.period-btn:hover { background-color: #10b981; color: white; }
.reports-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
.report-card { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
.report-card h3 { margin: 0 0 10px 0; font-size: 14px; opacity: 0.9; }
.big-number { font-size: 28px; font-weight: bold; margin: 10px 0; }
.subtitle { font-size: 12px; opacity: 0.8; margin: 0; }
.reports-list { display: flex; flex-direction: column; gap: 15px; }
.report-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; }
.report-info h4 { margin: 0 0 5px 0; color: #333; font-size: 14px; }
.report-info p { margin: 0; font-size: 12px; color: #999; }
.report-actions { display: flex; gap: 10px; }
.btn-small { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; }
.btn-view { background-color: #8b5cf6; color: white; }
.btn-view:hover { background-color: #7c3aed; }
.btn-download { background-color: #3b82f6; color: white; }
.btn-download:hover { background-color: #2563eb; }
@media (max-width: 768px) { .farmer-page { margin-left: 0; } .reports-item { flex-direction: column; align-items: flex-start; } }
</style>
