<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <h1>Weather Forecast</h1>
        <p>Get weather information for your farms</p>
      </div>

      <div class="content-section">
        <div class="weather-grid">
          <div class="weather-card" v-for="day in 7" :key="day">
            <div class="weather-date">{{ new Date(2026, 7, day).toLocaleDateString('en-US', {weekday: 'short', month: 'short', day: 'numeric'}) }}</div>
            <div class="weather-icon">
              <i :class="['fas', ['fa-sun', 'fa-cloud-sun', 'fa-cloud', 'fa-cloud-rain'][day % 4]]"></i>
            </div>
            <div class="weather-info">
              <p class="temp">{{ 20 + day }}°C</p>
              <p class="condition">{{ ['Sunny', 'Partly Cloudy', 'Cloudy', 'Rainy'][day % 4] }}</p>
              <p class="humidity">Humidity: {{ 50 + day * 5 }}%</p>
              <p class="wind">Wind: {{ 10 + day }}km/h</p>
            </div>
          </div>
        </div>
      </div>

      <div class="content-section" style="margin-top: 20px;">
        <h2>Weather Alerts</h2>
        <div class="alerts-list">
          <div class="alert-item alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="alert-content">
              <h4>Heavy Rain Expected</h4>
              <p>Heavy rain expected in your area on August 15-16</p>
            </div>
          </div>
          <div class="alert-item alert-info">
            <i class="fas fa-info-circle"></i>
            <div class="alert-content">
              <h4>Optimal Planting Conditions</h4>
              <p>August 12-14 shows optimal conditions for planting</p>
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
.weather-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; }
.weather-card { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 8px; padding: 20px; text-align: center; }
.weather-date { font-size: 12px; margin-bottom: 10px; opacity: 0.9; }
.weather-icon { font-size: 32px; margin: 10px 0; }
.weather-info p { margin: 5px 0; font-size: 13px; }
.temp { font-size: 20px; font-weight: bold; }
.alerts-list { display: flex; flex-direction: column; gap: 15px; }
.alert-item { display: flex; align-items: flex-start; gap: 15px; padding: 15px; border-radius: 8px; border-left: 4px solid; }
.alert-item i { font-size: 20px; }
.alert-warning { background-color: #fef3c7; border-left-color: #f59e0b; color: #92400e; }
.alert-warning i { color: #f59e0b; }
.alert-info { background-color: #dbeafe; border-left-color: #3b82f6; color: #1e40af; }
.alert-info i { color: #3b82f6; }
.alert-content h4 { margin: 0 0 5px 0; }
.alert-content p { margin: 0; font-size: 13px; }
@media (max-width: 768px) { .farmer-page { margin-left: 0; } .weather-grid { grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); } }
</style>
