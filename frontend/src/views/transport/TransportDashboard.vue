<template>
  <div class="transport-layout">
    <TransportSidebar @logout="handleLogout" />
    <div class="transport-dashboard">
      <!-- Header -->
      <div class="dashboard-header">
        <h1>Transport Dashboard</h1>
        <p>Manage deliveries and track shipments</p>
      </div>

      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="card-icon deliveries">
            <i class="fas fa-box"></i>
          </div>
          <div class="card-content">
            <h3>Total Deliveries</h3>
            <p class="card-value">{{ dashboard?.summary.total_deliveries || 0 }}</p>
            <p class="card-sub">All time</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon active">
            <i class="fas fa-truck"></i>
          </div>
          <div class="card-content">
            <h3>Active Deliveries</h3>
            <p class="card-value">{{ dashboard?.summary.active_deliveries || 0 }}</p>
            <p class="card-sub">In progress</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon vehicles">
            <i class="fas fa-car"></i>
          </div>
          <div class="card-content">
            <h3>Active Vehicles</h3>
            <p class="card-value">{{ dashboard?.summary.active_vehicles || 0 }}</p>
            <p class="card-sub">Available</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon revenue">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="card-content">
            <h3>Total Revenue</h3>
            <p class="card-value">${{ formatNumber(dashboard?.summary.total_revenue) }}</p>
            <p class="card-sub">Earnings</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon time">
            <i class="fas fa-hourglass-half"></i>
          </div>
          <div class="card-content">
            <h3>Avg Delivery Time</h3>
            <p class="card-value">{{ dashboard?.summary.avg_delivery_time || 0 }}h</p>
            <p class="card-sub">Hours</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon pending">
            <i class="fas fa-clock"></i>
          </div>
          <div class="card-content">
            <h3>Pending Requests</h3>
            <p class="card-value">{{ dashboard?.summary.pending_requests || 0 }}</p>
            <p class="card-sub">New orders</p>
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

        <!-- Delivery Requests Tab -->
        <div v-show="activeTab === 'requests'" class="tab-content">
          <div class="section-header">
            <h2>Delivery Requests</h2>
            <div class="filter-controls">
              <select v-model="requestFilter" class="filter-select">
                <option value="">All Requests</option>
                <option value="pending">Pending</option>
                <option value="accepted">Accepted</option>
              </select>
            </div>
          </div>

          <div class="requests-grid">
            <div v-for="request in dashboard?.delivery_requests || []" :key="request.id" class="request-card">
              <div class="request-header">
                <h4>#{{ request.id }}</h4>
                <span :class="['status-badge', `status-${request.status}`]">{{ request.status }}</span>
              </div>
              <div class="request-details">
                <p><strong>From:</strong> {{ request.from_location }}</p>
                <p><strong>To:</strong> {{ request.to_location }}</p>
                <p><strong>Distance:</strong> {{ request.distance }}km</p>
                <p><strong>Fee:</strong> ${{ request.delivery_fee }}</p>
              </div>
              <div class="request-actions">
                <button v-if="request.status === 'pending'" class="btn-accept">Accept</button>
                <button v-if="request.status === 'accepted'" class="btn-view">View</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Deliveries Tab -->
        <div v-show="activeTab === 'active'" class="tab-content">
          <div class="section-header">
            <h2>Active Deliveries</h2>
          </div>

          <div class="deliveries-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Recipient</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Status</th>
                  <th>Progress</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="delivery in dashboard?.active_deliveries || []" :key="delivery.id">
                  <td>#{{ delivery.id }}</td>
                  <td>{{ delivery.recipient_name }}</td>
                  <td>{{ delivery.from_location }}</td>
                  <td>{{ delivery.to_location }}</td>
                  <td><span :class="['status-badge', `status-${delivery.status}`]">{{ delivery.status }}</span></td>
                  <td>
                    <div class="progress-bar">
                      <div class="progress-fill" :style="{ width: delivery.progress + '%' }"></div>
                    </div>
                  </td>
                  <td class="action-buttons">
                    <button class="btn-small btn-update">Update</button>
                    <button class="btn-small btn-track">Track</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Vehicles Tab -->
        <div v-show="activeTab === 'vehicles'" class="tab-content">
          <div class="section-header">
            <h2>Vehicle Fleet</h2>
            <button class="btn-primary">+ Add Vehicle</button>
          </div>

          <div class="vehicles-grid">
            <div v-for="vehicle in dashboard?.vehicles || []" :key="vehicle.id" class="vehicle-card">
              <div class="vehicle-header">
                <h4>{{ vehicle.registration_number }}</h4>
                <span :class="['status-badge', `status-${vehicle.status}`]">{{ vehicle.status }}</span>
              </div>
              <div class="vehicle-info">
                <p><strong>Type:</strong> {{ vehicle.vehicle_type }}</p>
                <p><strong>Capacity:</strong> {{ vehicle.capacity_kg }}kg</p>
                <p><strong>Driver:</strong> {{ vehicle.driver_name }}</p>
                <p><strong>Last Service:</strong> {{ formatDate(vehicle.last_service) }}</p>
              </div>
              <div class="vehicle-actions">
                <button class="btn-small btn-edit">Edit</button>
                <button class="btn-small btn-maintenance">Maintenance</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Tab -->
        <div v-show="activeTab === 'performance'" class="tab-content">
          <div class="section-header">
            <h2>Performance & Analytics</h2>
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

          <div class="analytics-grid">
            <div class="analytics-card">
              <h3>Completed Deliveries</h3>
              <p class="big-number">{{ dashboard?.analytics.completed_deliveries || 0 }}</p>
            </div>
            <div class="analytics-card">
              <h3>On-Time Rate</h3>
              <p class="big-number">{{ dashboard?.analytics.ontime_rate || 0 }}%</p>
            </div>
            <div class="analytics-card">
              <h3>Avg Rating</h3>
              <p class="big-number">{{ dashboard?.analytics.avg_rating || 0 }}/5</p>
            </div>
            <div class="analytics-card">
              <h3>Revenue</h3>
              <p class="big-number">${{ formatNumber(dashboard?.analytics.revenue) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="recent-section">
        <div class="recent-card">
          <h2>Recent Deliveries</h2>
          <div class="list-items">
            <div v-for="delivery in dashboard?.recent_deliveries?.slice(0, 5) || []" :key="delivery.id" class="list-item">
              <span class="item-id">#{{ delivery.id }}</span>
              <span class="item-location">{{ delivery.to_location }}</span>
              <span class="item-status" :class="`status-${delivery.status}`">{{ delivery.status }}</span>
            </div>
          </div>
        </div>

        <div class="recent-card">
          <h2>Top Routes</h2>
          <div class="list-items">
            <div v-for="route in dashboard?.top_routes || []" :key="route.name" class="list-item">
              <span class="route-name">{{ route.name }}</span>
              <span class="route-count">{{ route.count }} deliveries</span>
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
import TransportSidebar from '@/components/Sidebar/TransportSidebar.vue'

const auth = useAuthStore()
const router = useRouter()
const activeTab = ref('requests')
const tabs = ['requests', 'active', 'vehicles', 'performance']

const dashboard = ref(null)
const requestFilter = ref('')
const selectedPeriod = ref(30)

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  try {
    const res = await fetch('/api/transport/dashboard', {
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
    requests: 'Delivery Requests',
    active: 'Active Deliveries',
    vehicles: 'Vehicle Fleet',
    performance: 'Performance'
  }
  return names[tab] || tab
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.transport-layout {
  display: flex;
  height: 100vh;
}

.transport-dashboard {
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

.card-icon.deliveries { background-color: #3b82f6; }
.card-icon.active { background-color: #8b5cf6; }
.card-icon.vehicles { background-color: #10b981; }
.card-icon.revenue { background-color: #f59e0b; }
.card-icon.time { background-color: #ef4444; }
.card-icon.pending { background-color: #06b6d4; }

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
  color: #8b5cf6;
}

.tab-btn.active {
  color: #8b5cf6;
  border-bottom-color: #8b5cf6;
}

.tab-content {
  padding: 25px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
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
}

.requests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.request-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 15px;
  transition: all 0.3s;
}

.request-card:hover {
  border-color: #8b5cf6;
  box-shadow: 0 2px 8px rgba(139, 92, 246, 0.1);
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.request-header h4 {
  margin: 0;
  color: #333;
}

.request-details p {
  margin: 5px 0;
  font-size: 14px;
  color: #666;
}

.request-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
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

.progress-bar {
  width: 100px;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #8b5cf6;
  transition: width 0.3s;
}

.vehicles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.vehicle-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 15px;
  transition: all 0.3s;
}

.vehicle-card:hover {
  border-color: #8b5cf6;
  box-shadow: 0 2px 8px rgba(139, 92, 246, 0.1);
}

.vehicle-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.vehicle-header h4 {
  margin: 0;
  color: #333;
}

.vehicle-info p {
  margin: 5px 0;
  font-size: 14px;
  color: #666;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.status-accepted {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge.status-active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.status-completed {
  background: #d1fae5;
  color: #065f46;
}

.btn-primary {
  background: #8b5cf6;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
}

.btn-accept {
  background: #10b981;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
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

.btn-update {
  background: #3b82f6;
  color: white;
}

.btn-track {
  background: #8b5cf6;
  color: white;
}

.btn-view {
  background: #8b5cf6;
  color: white;
}

.btn-edit {
  background: #3b82f6;
  color: white;
}

.btn-maintenance {
  background: #f59e0b;
  color: white;
}

.analytics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
}

.analytics-card {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.analytics-card h3 {
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
  background: #8b5cf6;
  color: white;
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

.item-id {
  font-weight: 600;
  color: #333;
}

.item-location {
  font-size: 14px;
  color: #666;
}

.item-status {
  font-size: 12px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 3px;
}

.route-name {
  font-weight: 600;
  color: #333;
}

.route-count {
  font-size: 14px;
  color: #666;
}

@media (max-width: 768px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .tab-buttons {
    flex-wrap: wrap;
  }
  
  .requests-grid {
    grid-template-columns: 1fr;
  }
}
</style>
