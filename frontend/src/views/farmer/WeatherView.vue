<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <div class="page-header">
        <h1>Weather Forecast</h1>
        <p>Get weather information for your farms</p>
      </div>

      <!-- Location Selection -->
      <div class="content-section">
        <div class="location-selector">
          <div class="form-group">
            <label>Select Farm Location</label>
            <select v-model="selectedFarm" @change="loadWeatherForFarm" class="form-input">
              <option value="">-- Choose a farm --</option>
              <option v-for="farm in farms" :key="farm.id" :value="farm.id">
                {{ farm.name }} ({{ farm.region }}, {{ farm.woreda }})
              </option>
            </select>
          </div>
          <button v-if="selectedFarm" @click="refreshWeather" class="btn btn-primary" :disabled="loading">
            <i class="fas fa-sync" :class="{ 'fa-spin': loading }"></i> Refresh
          </button>
        </div>
      </div>

      <!-- Current Weather -->
      <div v-if="currentWeather && selectedFarm" class="content-section">
        <h2>Current Weather</h2>
        <div class="current-weather">
          <div class="weather-main">
            <div class="temp-display">
              <i :class="['fas', getWeatherIcon(currentWeather.weather?.[0]?.main)]" style="font-size: 48px;"></i>
              <div class="temp-info">
                <div class="temp">{{ currentWeather.main?.temp }}°C</div>
                <div class="condition">{{ currentWeather.weather?.[0]?.main }}</div>
                <div class="description">{{ currentWeather.weather?.[0]?.description }}</div>
              </div>
            </div>
            <div class="weather-details">
              <div class="detail-item">
                <i class="fas fa-droplet"></i>
                <div>
                  <div class="detail-label">Humidity</div>
                  <div class="detail-value">{{ currentWeather.main?.humidity }}%</div>
                </div>
              </div>
              <div class="detail-item">
                <i class="fas fa-wind"></i>
                <div>
                  <div class="detail-label">Wind Speed</div>
                  <div class="detail-value">{{ currentWeather.wind?.speed }} m/s</div>
                </div>
              </div>
              <div class="detail-item">
                <i class="fas fa-gauge"></i>
                <div>
                  <div class="detail-label">Pressure</div>
                  <div class="detail-value">{{ currentWeather.main?.pressure }} hPa</div>
                </div>
              </div>
              <div class="detail-item">
                <i class="fas fa-eye"></i>
                <div>
                  <div class="detail-label">Visibility</div>
                  <div class="detail-value">{{ (currentWeather.visibility / 1000).toFixed(1) }} km</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 7-Day Forecast -->
      <div v-if="forecastData && selectedFarm" class="content-section">
        <h2>7-Day Forecast</h2>
        <div class="forecast-loading" v-if="loading">
          <i class="fas fa-spinner fa-spin"></i> Loading forecast...
        </div>
        <div v-else-if="processedForecast.length > 0" class="weather-grid">
          <div class="weather-card" v-for="day in processedForecast" :key="day.date">
            <div class="weather-date">{{ formatDate(day.date) }}</div>
            <div class="weather-icon">
              <i :class="['fas', getWeatherIcon(day.condition)]"></i>
            </div>
            <div class="weather-info">
              <p class="temp">{{ day.temp }}°C</p>
              <p class="temp-range">{{ day.minTemp }}° - {{ day.maxTemp }}°</p>
              <p class="condition">{{ day.condition }}</p>
              <p class="humidity">
                <i class="fas fa-droplet"></i> {{ day.humidity }}%
              </p>
              <p class="wind">
                <i class="fas fa-wind"></i> {{ day.windSpeed }} m/s
              </p>
              <p class="rain" v-if="day.rainChance">
                <i class="fas fa-cloud-rain"></i> Rain: {{ day.rainChance }}%
              </p>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <i class="fas fa-cloud-sun"></i>
          <p>No forecast data available. Please select a farm and refresh.</p>
        </div>
      </div>

      <!-- Weather Alerts -->
      <div v-if="selectedFarm" class="content-section">
        <h2>Weather Alerts</h2>
        <div v-if="alerts.length > 0" class="alerts-list">
          <div v-for="(alert, index) in alerts" :key="index" :class="['alert-item', alert.type]">
            <i :class="['fas', getAlertIcon(alert.type)]"></i>
            <div class="alert-content">
              <h4>{{ alert.title }}</h4>
              <p>{{ alert.message }}</p>
            </div>
          </div>
        </div>
        <div v-else class="no-alerts">
          <i class="fas fa-check-circle"></i>
          <p>No weather alerts at this time</p>
        </div>
      </div>

      <!-- Planting Recommendations -->
      <div v-if="selectedFarm && currentWeather" class="content-section">
        <h2>Planting Recommendations</h2>
        <div class="recommendations">
          <div :class="['recommendation-card', getRecommendationClass()]">
            <i :class="['fas', getRecommendationIcon()]"></i>
            <div>
              <h4>{{ getPlantingRecommendation().title }}</h4>
              <p>{{ getPlantingRecommendation().message }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()

// State
const farms = ref([])
const selectedFarm = ref('')
const currentWeather = ref(null)
const forecastData = ref(null)
const alerts = ref([])
const loading = ref(false)
const error = ref('')

// API base URL
const API_BASE = 'http://localhost:8000/api'

// Fetch farms on mount
onMounted(async () => {
  await fetchFarms()
})

// Fetch user's farms
const fetchFarms = async () => {
  try {
    const response = await fetch(`${API_BASE}/farmer/farms`, {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })
    if (response.ok) {
      const data = await response.json()
      farms.value = data.data || []
    }
  } catch (err) {
    console.error('Error fetching farms:', err)
    error.value = 'Failed to load farms'
  }
}

// Load weather for selected farm
const loadWeatherForFarm = async () => {
  if (!selectedFarm.value) return
  
  const farm = farms.value.find(f => f.id === parseInt(selectedFarm.value))
  if (!farm) return

  loading.value = true
  try {
    // Get current weather
    const currentResponse = await fetch(`${API_BASE}/farmer/weather/current`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        latitude: farm.latitude || -9.0320,
        longitude: farm.longitude || 38.7469
      })
    })

    if (currentResponse.ok) {
      const data = await currentResponse.json()
      currentWeather.value = data.data
    }

    // Get forecast
    const forecastResponse = await fetch(`${API_BASE}/farmer/weather/forecast`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        latitude: farm.latitude || -9.0320,
        longitude: farm.longitude || 38.7469,
        days: 7
      })
    })

    if (forecastResponse.ok) {
      const data = await forecastResponse.json()
      forecastData.value = data.data
    }

    // Get alerts
    const alertsResponse = await fetch(`${API_BASE}/farmer/weather/alerts`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        latitude: farm.latitude || -9.0320,
        longitude: farm.longitude || 38.7469
      })
    })

    if (alertsResponse.ok) {
      const data = await alertsResponse.json()
      parseAlerts(data.data || [])
    }
  } catch (err) {
    console.error('Error loading weather:', err)
    error.value = 'Failed to load weather data'
  } finally {
    loading.value = false
  }
}

// Refresh weather data
const refreshWeather = () => {
  loadWeatherForFarm()
}

// Parse weather alerts
const parseAlerts = (alertData) => {
  alerts.value = []
  
  if (!currentWeather.value) return

  // Check for rain
  if (currentWeather.value.rain) {
    alerts.value.push({
      type: 'alert-warning',
      title: 'Rain Alert',
      message: `Precipitation expected: ${currentWeather.value.rain['1h'] || 0}mm`
    })
  }

  // Check for extreme temperature
  const temp = currentWeather.value.main?.temp
  if (temp > 35) {
    alerts.value.push({
      type: 'alert-danger',
      title: 'High Temperature',
      message: `Temperature is ${temp}°C. Monitor irrigation needs.`
    })
  }

  // Check for strong wind
  if (currentWeather.value.wind?.speed > 10) {
    alerts.value.push({
      type: 'alert-warning',
      title: 'Strong Wind',
      message: `Wind speed: ${currentWeather.value.wind.speed} m/s. Secure loose items.`
    })
  }

  // Add optimal conditions alert
  if (temp >= 20 && temp <= 28 && currentWeather.value.main?.humidity >= 60) {
    alerts.value.push({
      type: 'alert-success',
      title: 'Optimal Planting Conditions',
      message: 'Current conditions are favorable for planting activities.'
    })
  }
}

// Process forecast data from API
const processedForecast = computed(() => {
  if (!forecastData.value || !forecastData.value.list) return []

  const dailyData = {}
  
  forecastData.value.list.forEach(item => {
    const date = new Date(item.dt * 1000).toLocaleDateString()
    if (!dailyData[date]) {
      dailyData[date] = {
        temps: [],
        humidity: [],
        windSpeed: [],
        condition: item.weather?.[0]?.main,
        rainChance: item.pop ? Math.round(item.pop * 100) : 0,
        date: new Date(item.dt * 1000)
      }
    }
    dailyData[date].temps.push(item.main?.temp)
    dailyData[date].humidity.push(item.main?.humidity)
    dailyData[date].windSpeed.push(item.wind?.speed)
  })

  return Object.values(dailyData).map(day => ({
    date: day.date,
    temp: Math.round(day.temps.reduce((a, b) => a + b, 0) / day.temps.length),
    minTemp: Math.round(Math.min(...day.temps)),
    maxTemp: Math.round(Math.max(...day.temps)),
    humidity: Math.round(day.humidity.reduce((a, b) => a + b, 0) / day.humidity.length),
    windSpeed: Math.round(day.windSpeed.reduce((a, b) => a + b, 0) / day.windSpeed.length * 10) / 10,
    condition: day.condition,
    rainChance: day.rainChance
  })).slice(0, 7)
})

// Get weather icon based on condition
const getWeatherIcon = (condition) => {
  const iconMap = {
    'Clear': 'fa-sun',
    'Clouds': 'fa-cloud',
    'Rain': 'fa-cloud-rain',
    'Drizzle': 'fa-cloud-rain',
    'Thunderstorm': 'fa-bolt',
    'Snow': 'fa-snowflake',
    'Mist': 'fa-smog',
    'Smoke': 'fa-smog',
    'Haze': 'fa-smog',
    'Dust': 'fa-wind',
    'Fog': 'fa-smog',
    'Sand': 'fa-wind',
    'Ash': 'fa-wind',
    'Squall': 'fa-wind',
    'Tornado': 'fa-tornado'
  }
  return iconMap[condition] || 'fa-cloud-sun'
}

// Get alert icon
const getAlertIcon = (type) => {
  if (type === 'alert-danger') return 'fa-exclamation-circle'
  if (type === 'alert-warning') return 'fa-exclamation-triangle'
  if (type === 'alert-success') return 'fa-check-circle'
  return 'fa-info-circle'
}

// Format date
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  })
}

// Get planting recommendation
const getPlantingRecommendation = () => {
  if (!currentWeather.value) {
    return { title: 'Loading...', message: 'Fetching weather data...' }
  }

  const temp = currentWeather.value.main?.temp
  const humidity = currentWeather.value.main?.humidity
  const condition = currentWeather.value.weather?.[0]?.main

  if (condition === 'Rain' || condition === 'Thunderstorm') {
    return {
      title: '⛈️ Not Suitable - Rain Expected',
      message: 'Wait for weather to clear before planting. Recent rain will aid germination.'
    }
  }

  if (temp > 35) {
    return {
      title: '🌡️ Caution - High Temperature',
      message: 'Very hot conditions. Consider early morning or late evening planting. Increase irrigation.'
    }
  }

  if (temp < 15) {
    return {
      title: '❄️ Not Suitable - Too Cold',
      message: 'Temperature too low for optimal germination. Wait for warmer weather.'
    }
  }

  if (humidity < 40) {
    return {
      title: '💧 Moderate - Low Humidity',
      message: 'Humidity is low. Increase irrigation frequency after planting. Suitable for most crops.'
    }
  }

  if (temp >= 20 && temp <= 28 && humidity >= 60) {
    return {
      title: '✅ Excellent - Optimal Conditions',
      message: 'Perfect conditions for planting! Temperature and humidity are ideal for seed germination.'
    }
  }

  return {
    title: '👍 Good - Suitable',
    message: 'Weather conditions are generally suitable for planting. Monitor forecasts for changes.'
  }
}

// Get recommendation class
const getRecommendationClass = () => {
  const rec = getPlantingRecommendation()
  if (rec.title.includes('Excellent')) return 'recommendation-success'
  if (rec.title.includes('Good') || rec.title.includes('Moderate')) return 'recommendation-info'
  if (rec.title.includes('Caution')) return 'recommendation-warning'
  return 'recommendation-danger'
}

// Get recommendation icon
const getRecommendationIcon = () => {
  const rec = getPlantingRecommendation()
  if (rec.title.includes('Excellent')) return 'fa-check-circle'
  if (rec.title.includes('Good') || rec.title.includes('Moderate')) return 'fa-thumbs-up'
  if (rec.title.includes('Caution')) return 'fa-exclamation-triangle'
  return 'fa-times-circle'
}

// Logout handler
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

.content-section { 
  background: white; 
  border-radius: 8px; 
  padding: 25px; 
  box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
  margin-bottom: 20px;
}

.content-section h2 { 
  font-size: 20px; 
  font-weight: bold; 
  color: #333; 
  margin-bottom: 20px; 
}

/* Location Selector */
.location-selector {
  display: flex;
  gap: 15px;
  align-items: flex-end;
}

.form-group {
  flex: 1;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: #333;
}

.form-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.form-input:hover { border-color: #10b981; }
.form-input:focus { 
  outline: none; 
  border-color: #10b981; 
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
}

.btn-primary {
  background-color: #10b981;
  color: white;
}

.btn-primary:hover:not(:disabled) { 
  background-color: #059669;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Current Weather */
.current-weather {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 8px;
  padding: 30px;
  color: white;
}

.weather-main {
  display: flex;
  gap: 40px;
  align-items: flex-start;
}

.temp-display {
  display: flex;
  align-items: center;
  gap: 20px;
}

.temp-info {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.temp {
  font-size: 48px;
  font-weight: bold;
}

.condition {
  font-size: 24px;
  font-weight: 600;
}

.description {
  font-size: 14px;
  opacity: 0.9;
  text-transform: capitalize;
}

.weather-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
  flex: 1;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.1);
  padding: 12px;
  border-radius: 6px;
}

.detail-item i {
  font-size: 20px;
}

.detail-label {
  font-size: 12px;
  opacity: 0.8;
}

.detail-value {
  font-size: 18px;
  font-weight: 600;
}

/* Forecast Grid */
.forecast-loading {
  text-align: center;
  padding: 40px;
  color: #666;
}

.forecast-loading i {
  font-size: 24px;
  margin-right: 10px;
}

.weather-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); 
  gap: 15px; 
}

.weather-card { 
  background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
  color: white; 
  border-radius: 8px; 
  padding: 20px; 
  text-align: center;
  transition: all 0.3s;
  border: 2px solid transparent;
}

.weather-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
  border-color: rgba(255, 255, 255, 0.3);
}

.weather-date { 
  font-size: 12px; 
  margin-bottom: 10px; 
  opacity: 0.9;
  font-weight: 600;
}

.weather-icon { 
  font-size: 32px; 
  margin: 10px 0; 
}

.weather-info { 
  display: flex;
  flex-direction: column;
  gap: 5px;
  font-size: 13px;
}

.weather-info p { margin: 0; }

.temp { 
  font-size: 20px; 
  font-weight: bold; 
}

.temp-range {
  font-size: 12px;
  opacity: 0.8;
}

.condition {
  font-size: 14px;
  font-weight: 600;
}

.humidity, .wind, .rain {
  display: flex;
  align-items: center;
  gap: 6px;
  justify-content: center;
  opacity: 0.9;
}

/* Alerts */
.alerts-list { 
  display: flex; 
  flex-direction: column; 
  gap: 15px; 
}

.alert-item { 
  display: flex; 
  align-items: flex-start; 
  gap: 15px; 
  padding: 15px; 
  border-radius: 8px; 
  border-left: 4px solid; 
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.alert-item i { 
  font-size: 20px; 
  margin-top: 2px;
}

.alert-warning { 
  background-color: #fef3c7; 
  border-left-color: #f59e0b; 
  color: #92400e; 
}

.alert-warning i { color: #f59e0b; }

.alert-danger {
  background-color: #fee2e2;
  border-left-color: #ef4444;
  color: #7f1d1d;
}

.alert-danger i { color: #ef4444; }

.alert-success {
  background-color: #d1fae5;
  border-left-color: #10b981;
  color: #065f46;
}

.alert-success i { color: #10b981; }

.alert-info { 
  background-color: #dbeafe; 
  border-left-color: #3b82f6; 
  color: #1e40af; 
}

.alert-info i { color: #3b82f6; }

.alert-content h4 { 
  margin: 0 0 5px 0;
  font-weight: 600;
}

.alert-content p { 
  margin: 0; 
  font-size: 13px; 
}

.no-alerts {
  text-align: center;
  padding: 30px;
  color: #999;
}

.no-alerts i {
  font-size: 32px;
  color: #10b981;
  margin-bottom: 10px;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 40px;
  color: #999;
}

.empty-state i {
  font-size: 48px;
  color: #ddd;
  margin-bottom: 15px;
}

/* Recommendations */
.recommendations {
  display: grid;
  gap: 15px;
}

.recommendation-card {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  padding: 20px;
  border-radius: 8px;
  border-left: 4px solid;
}

.recommendation-card i {
  font-size: 24px;
  margin-top: 2px;
}

.recommendation-card h4 {
  margin: 0 0 5px 0;
  font-size: 16px;
  font-weight: 600;
}

.recommendation-card p {
  margin: 0;
  font-size: 14px;
  opacity: 0.9;
}

.recommendation-success {
  background-color: #d1fae5;
  border-left-color: #10b981;
  color: #065f46;
}

.recommendation-success i { color: #10b981; }

.recommendation-info {
  background-color: #dbeafe;
  border-left-color: #3b82f6;
  color: #1e40af;
}

.recommendation-info i { color: #3b82f6; }

.recommendation-warning {
  background-color: #fef3c7;
  border-left-color: #f59e0b;
  color: #92400e;
}

.recommendation-warning i { color: #f59e0b; }

.recommendation-danger {
  background-color: #fee2e2;
  border-left-color: #ef4444;
  color: #7f1d1d;
}

.recommendation-danger i { color: #ef4444; }

/* Responsive */
@media (max-width: 1200px) {
  .weather-main { flex-direction: column; }
  .weather-details { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) { 
  .farmer-page { margin-left: 0; padding: 15px; }
  .weather-grid { grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); }
  .location-selector { flex-direction: column; align-items: stretch; }
  .weather-main { gap: 20px; }
  .weather-details { grid-template-columns: 1fr; }
  .content-section { padding: 15px; }
}
</style>
