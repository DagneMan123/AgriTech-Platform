<template>
  <div class="weather-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="weather-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Weather Forecast</h1>
          <p>View detailed weather forecasts and climate alerts for your farm</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading weather data...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchWeatherData" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="weather-content">
        <!-- Location & Time -->
        <div class="location-card">
          <div class="location-header">
            <div>
              <h3>{{ currentLocation }}</h3>
              <p class="location-coords">Coordinates: {{ coordinates }}</p>
            </div>
            <button @click="showLocationSelector = true" class="btn-change-location">
              <MapPin size="16" />
              <span>Change Location</span>
            </button>
          </div>
        </div>

        <!-- Current Weather -->
        <div class="current-weather">
          <div class="weather-main">
            <div class="weather-icon-large">
              <Cloud size="80" />
            </div>
            <div class="weather-info">
              <span class="temperature">24°C</span>
              <span class="condition">Partly Cloudy</span>
              <p class="description">Light winds, good farming conditions</p>
            </div>
          </div>

          <div class="weather-details-grid">
            <div class="detail-card">
              <Droplets size="20" class="detail-icon" />
              <span class="label">Humidity</span>
              <span class="value">65%</span>
            </div>
            <div class="detail-card">
              <Wind size="20" class="detail-icon" />
              <span class="label">Wind Speed</span>
              <span class="value">12 km/h</span>
            </div>
            <div class="detail-card">
              <Eye size="20" class="detail-icon" />
              <span class="label">Visibility</span>
              <span class="value">10 km</span>
            </div>
            <div class="detail-card">
              <Gauge size="20" class="detail-icon" />
              <span class="label">Pressure</span>
              <span class="value">1013 mb</span>
            </div>
            <div class="detail-card">
              <CloudRain size="20" class="detail-icon" />
              <span class="label">Precipitation</span>
              <span class="value">0 mm</span>
            </div>
            <div class="detail-card">
              <Sun size="20" class="detail-icon" />
              <span class="label">UV Index</span>
              <span class="value">4 (Moderate)</span>
            </div>
          </div>
        </div>

        <!-- 7-Day Forecast -->
        <div class="forecast-section">
          <div class="section-header">
            <h3>7-Day Forecast</h3>
          </div>
          <div class="forecast-cards">
            <div v-for="day in forecastDays" :key="day.id" class="forecast-card">
              <span class="forecast-day">{{ day.day }}</span>
              <component :is="day.icon" size="32" class="forecast-icon" />
              <span class="forecast-condition">{{ day.condition }}</span>
              <span class="forecast-temp">{{ day.high }}°</span>
              <span class="forecast-low">{{ day.low }}°</span>
              <span class="forecast-rain">
                <Droplets size="12" />
                {{ day.rainChance }}%
              </span>
            </div>
          </div>
        </div>

        <!-- Hourly Forecast -->
        <div class="hourly-section">
          <div class="section-header">
            <h3>24-Hour Forecast</h3>
          </div>
          <div class="hourly-scroll">
            <div v-for="hour in hourlyForecast" :key="hour.id" class="hourly-card">
              <span class="hour-time">{{ hour.time }}</span>
              <Cloud size="24" class="hour-icon" />
              <span class="hour-temp">{{ hour.temp }}°</span>
              <span class="hour-condition">{{ hour.condition }}</span>
            </div>
          </div>
        </div>

        <!-- Alerts -->
        <div v-if="alerts.length > 0" class="alerts-section">
          <div class="section-header">
            <AlertTriangle size="18" class="alert-header-icon" />
            <h3>Weather Alerts</h3>
          </div>
          <div v-for="alert in alerts" :key="alert.id" class="alert-item" :class="`alert-${alert.type}`">
            <div class="alert-icon">
              <AlertCircle size="20" />
            </div>
            <div class="alert-content">
              <span class="alert-title">{{ alert.title }}</span>
              <p class="alert-message">{{ alert.message }}</p>
              <span class="alert-time">{{ alert.time }}</span>
            </div>
          </div>
        </div>

        <!-- Agricultural Recommendations -->
        <div class="recommendations-section">
          <div class="section-header">
            <h3>Agricultural Recommendations</h3>
          </div>
          <div class="recommendations-grid">
            <div v-for="rec in recommendations" :key="rec.id" class="recommendation-card" :class="`rec-${rec.type}`">
              <div class="rec-icon">
                <component :is="rec.icon" size="24" />
              </div>
              <div class="rec-content">
                <h4>{{ rec.title }}</h4>
                <p>{{ rec.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Location Selector Modal -->
      <div v-if="showLocationSelector" class="modal-overlay" @click="showLocationSelector = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>Select Farm Location</h2>
            <button @click="showLocationSelector = false" class="btn-close">
              <X size="20" />
            </button>
          </div>
          <div class="modal-body">
            <div class="location-list">
              <div
                v-for="location in farmLocations"
                :key="location.id"
                @click="selectLocation(location)"
                class="location-item"
                :class="{ active: currentLocation === location.name }"
              >
                <MapPin size="16" />
                <div class="location-info">
                  <span class="location-name">{{ location.name }}</span>
                  <span class="location-details">{{ location.area }} • {{ location.region }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import {
  Cloud, CloudRain, Sun, Wind, Droplets, Eye, Gauge, AlertCircle, RotateCcw, X, MapPin,
  AlertTriangle, Sprout, Droplet, Thermometer, Zap
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const error = ref(null)
const showLocationSelector = ref(false)
const currentLocation = ref('Green Valley Farm')
const coordinates = ref('37.7749°N, 122.4194°W')

const farmLocations = [
  { id: 1, name: 'Green Valley Farm', area: '50 hectares', region: 'Central Region' },
  { id: 2, name: 'Sunny Acres', area: '75 hectares', region: 'Northern Region' },
  { id: 3, name: 'Harvest Farm', area: '45 hectares', region: 'Southern Region' }
]

const forecastDays = ref([
  { id: 1, day: 'Today', icon: Cloud, condition: 'Cloudy', high: 24, low: 18, rainChance: 20 },
  { id: 2, day: 'Tomorrow', icon: CloudRain, condition: 'Rainy', high: 22, low: 16, rainChance: 80 },
  { id: 3, day: 'Thursday', icon: Cloud, condition: 'Cloudy', high: 23, low: 17, rainChance: 40 },
  { id: 4, day: 'Friday', icon: Sun, condition: 'Sunny', high: 26, low: 19, rainChance: 10 },
  { id: 5, day: 'Saturday', icon: Sun, condition: 'Sunny', high: 28, low: 21, rainChance: 5 },
  { id: 6, day: 'Sunday', icon: Cloud, condition: 'Cloudy', high: 25, low: 19, rainChance: 30 },
  { id: 7, day: 'Monday', icon: CloudRain, condition: 'Rainy', high: 23, low: 17, rainChance: 70 }
])

const hourlyForecast = ref([
  { id: 1, time: '12:00 PM', temp: 24, condition: 'Cloudy' },
  { id: 2, time: '1:00 PM', temp: 25, condition: 'Cloudy' },
  { id: 3, time: '2:00 PM', temp: 26, condition: 'Sunny' },
  { id: 4, time: '3:00 PM', temp: 26, condition: 'Sunny' },
  { id: 5, time: '4:00 PM', temp: 25, condition: 'Partly Cloudy' },
  { id: 6, time: '5:00 PM', temp: 24, condition: 'Cloudy' },
  { id: 7, time: '6:00 PM', temp: 22, condition: 'Cloudy' },
  { id: 8, time: '7:00 PM', temp: 20, condition: 'Clear' }
])

const alerts = ref([
  {
    id: 1,
    type: 'warning',
    title: 'Heavy Rain Expected',
    message: 'Rain expected tomorrow with 80% probability. Consider postponing field activities.',
    time: 'Today at 3:00 PM'
  }
])

const recommendations = ref([
  {
    id: 1,
    type: 'positive',
    icon: Sprout,
    title: 'Good Planting Conditions',
    description: 'Current weather is ideal for planting. Soil moisture and temperature are optimal.'
  },
  {
    id: 2,
    type: 'caution',
    icon: Droplet,
    title: 'Irrigation Needed',
    description: 'Plan irrigation for tomorrow after rain. This will optimize water usage.'
  },
  {
    id: 3,
    type: 'warning',
    icon: AlertTriangle,
    title: 'Harvest Advisory',
    description: 'Avoid harvesting tomorrow due to heavy rain. Wait for dry conditions.'
  },
  {
    id: 4,
    type: 'positive',
    icon: Thermometer,
    title: 'Pesticide Application',
    description: 'Conditions are suitable for pesticide application today and Friday.'
  }
])

const fetchWeatherData = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const selectLocation = (location) => {
  currentLocation.value = location.name
  coordinates.value = '37.7749°N, 122.4194°W'
  showLocationSelector.value = false
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchWeatherData() })
</script>

<style scoped>
.weather-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.weather-container {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.page-header {
  background: white;
  padding: 25px 30px;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.header-content h1 {
  font-size: 28px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 8px 0;
}

.header-content p {
  color: #6b7280;
  font-size: 14px;
  margin: 0;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  gap: 20px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-container {
  background: #fee2e2;
  border: 2px solid #fca5a5;
  border-radius: 12px;
  padding: 40px;
  margin: 30px;
  text-align: center;
}

.error-icon {
  color: #dc2626;
  margin-bottom: 15px;
}

.error-message {
  color: #991b1b;
  font-size: 16px;
  margin-bottom: 20px;
}

.btn-retry {
  background: #dc2626;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.weather-content {
  padding: 30px;
  flex: 1;
}

.location-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.location-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.location-card h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.location-coords {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
}

.btn-change-location {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: #eff6ff;
  color: #3b82f6;
  border: 1px solid #3b82f6;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.2s;
}

.btn-change-location:hover {
  background: #3b82f6;
  color: white;
}

.current-weather {
  background: white;
  border-radius: 12px;
  padding: 30px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.weather-main {
  display: flex;
  align-items: center;
  gap: 40px;
  margin-bottom: 30px;
  padding-bottom: 30px;
  border-bottom: 1px solid #e5e7eb;
}

.weather-icon-large {
  color: #f59e0b;
  flex-shrink: 0;
}

.weather-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.temperature {
  font-size: 48px;
  font-weight: 700;
  color: #1f2937;
}

.condition {
  font-size: 20px;
  font-weight: 600;
  color: #4b5563;
}

.description {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.weather-details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
}

.detail-card {
  background: #f9fafb;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  text-align: center;
}

.detail-icon {
  color: #3b82f6;
}

.detail-card .label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
}

.detail-card .value {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
}

.forecast-section,
.hourly-section,
.alerts-section,
.recommendations-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e5e7eb;
}

.section-header h3 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.alert-header-icon {
  color: #dc2626;
}

.forecast-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 16px;
}

.forecast-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  text-align: center;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: center;
}

.forecast-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.forecast-day {
  font-size: 13px;
  font-weight: 600;
  color: #6b7280;
}

.forecast-icon {
  color: #f59e0b;
}

.forecast-condition {
  font-size: 12px;
  color: #4b5563;
}

.forecast-temp {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
}

.forecast-low {
  font-size: 12px;
  color: #6b7280;
}

.forecast-rain {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  color: #3b82f6;
}

.hourly-scroll {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  padding: 8px 0;
}

.hourly-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 12px;
  min-width: 100px;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 6px;
  align-items: center;
  flex-shrink: 0;
}

.hour-time {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
}

.hour-icon {
  color: #f59e0b;
}

.hour-temp {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
}

.hour-condition {
  font-size: 10px;
  color: #6b7280;
}

.alert-item {
  display: flex;
  gap: 12px;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 12px;
  border-left: 4px solid;
}

.alert-item:last-child {
  margin-bottom: 0;
}

.alert-warning {
  background: #fef3c7;
  border-left-color: #f59e0b;
}

.alert-icon {
  color: #f59e0b;
  flex-shrink: 0;
  margin-top: 2px;
}

.alert-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.alert-title {
  font-size: 14px;
  font-weight: 600;
  color: #92400e;
}

.alert-message {
  font-size: 13px;
  color: #92400e;
  margin: 0;
  line-height: 1.5;
}

.alert-time {
  font-size: 11px;
  color: #b45309;
  margin-top: 4px;
}

.recommendations-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.recommendation-card {
  border-radius: 8px;
  padding: 16px;
  display: flex;
  gap: 12px;
  border-left: 4px solid;
}

.rec-positive {
  background: #ecfdf5;
  border-left-color: #10b981;
}

.rec-caution {
  background: #fef3c7;
  border-left-color: #f59e0b;
}

.rec-warning {
  background: #fee2e2;
  border-left-color: #dc2626;
}

.rec-icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.rec-positive .rec-icon { color: #10b981; }
.rec-caution .rec-icon { color: #f59e0b; }
.rec-warning .rec-icon { color: #dc2626; }

.rec-content h4 {
  font-size: 14px;
  font-weight: 600;
  margin: 0 0 4px 0;
}

.rec-positive .rec-content h4 { color: #065f46; }
.rec-caution .rec-content h4 { color: #92400e; }
.rec-warning .rec-content h4 { color: #991b1b; }

.rec-content p {
  font-size: 13px;
  margin: 0;
  line-height: 1.5;
}

.rec-positive .rec-content p { color: #065f46; }
.rec-caution .rec-content p { color: #92400e; }
.rec-warning .rec-content p { color: #991b1b; }

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 400px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #6b7280;
  padding: 0;
}

.modal-body {
  padding: 24px;
}

.location-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.location-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.location-item:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.location-item.active {
  background: #eff6ff;
  border-color: #3b82f6;
}

.location-item svg {
  color: #3b82f6;
  flex-shrink: 0;
}

.location-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.location-name {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
}

.location-details {
  font-size: 12px;
  color: #6b7280;
}

@media (max-width: 768px) {
  .weather-container {
    margin-left: 0;
  }

  .weather-main {
    flex-direction: column;
    gap: 20px;
  }

  .location-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }

  .btn-change-location {
    width: 100%;
    justify-content: center;
  }

  .weather-details-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .forecast-cards {
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
  }

  .recommendations-grid {
    grid-template-columns: 1fr;
  }
}
</style>
