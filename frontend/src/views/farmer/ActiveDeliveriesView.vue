<template>
  <div class="active-deliveries-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="deliveries-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Active Deliveries</h1>
          <p>Real-time tracking of your deliveries in progress</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading deliveries...</p>
      </div>

      <!-- Content -->
      <div v-if="!loading" class="deliveries-content">
        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <Truck size="20" class="stat-icon active" />
            <div>
              <span class="stat-label">In Transit</span>
              <span class="stat-value">{{ stats.inTransit }}</span>
            </div>
          </div>
          <div class="stat-card">
            <Clock size="20" class="stat-icon eta" />
            <div>
              <span class="stat-label">Avg. ETA</span>
              <span class="stat-value">{{ stats.avgEta }}</span>
            </div>
          </div>
          <div class="stat-card">
            <Package size="20" class="stat-icon total" />
            <div>
              <span class="stat-label">Items in Transit</span>
              <span class="stat-value">{{ stats.totalItems }}</span>
            </div>
          </div>
          <div class="stat-card">
            <MapPin size="20" class="stat-icon distance" />
            <div>
              <span class="stat-label">Total Distance</span>
              <span class="stat-value">{{ stats.totalDistance }} km</span>
            </div>
          </div>
        </div>

        <!-- Active Deliveries Cards -->
        <div class="deliveries-section">
          <div class="section-header">
            <h2>Active Deliveries</h2>
            <div class="controls">
              <select v-model="filterStatus" class="filter-select">
                <option value="">All</option>
                <option value="on-way">On Way</option>
                <option value="near">Nearby</option>
                <option value="delayed">Delayed</option>
              </select>
            </div>
          </div>

          <div v-if="filteredDeliveries.length > 0" class="deliveries-grid">
            <div v-for="delivery in filteredDeliveries" :key="delivery.id" class="delivery-card">
              <div class="card-header">
                <div>
                  <h3>{{ delivery.id }}</h3>
                  <p class="destination">To: {{ delivery.destination }}</p>
                </div>
                <span class="status-badge" :class="`status-${delivery.status}`">
                  {{ capitalize(delivery.status) }}
                </span>
              </div>

              <div class="card-info">
                <div class="info-item">
                  <span class="label">Product:</span>
                  <span class="value">{{ delivery.product }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Quantity:</span>
                  <span class="value">{{ delivery.quantity }} {{ delivery.unit }}</span>
                </div>
              </div>

              <!-- Map or Location -->
              <div class="delivery-map">
                <div class="map-placeholder">
                  <MapPin size="24" />
                  <p>{{ delivery.currentLocation }}</p>
                </div>
              </div>

              <!-- Progress -->
              <div class="delivery-progress">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: delivery.progress + '%' }"></div>
                </div>
                <p class="progress-text">{{ delivery.progress }}% - {{ delivery.distanceCovered }} km of {{ delivery.totalDistance }} km</p>
              </div>

              <!-- Driver & ETA -->
              <div class="delivery-details">
                <div class="detail">
                  <User size="16" />
                  <div>
                    <span class="detail-label">Driver</span>
                    <span class="detail-value">{{ delivery.driver }}</span>
                  </div>
                </div>
                <div class="detail">
                  <Clock size="16" />
                  <div>
                    <span class="detail-label">ETA</span>
                    <span class="detail-value">{{ delivery.eta }}</span>
                  </div>
                </div>
                <div class="detail">
                  <Phone size="16" />
                  <div>
                    <span class="detail-label">Contact</span>
                    <span class="detail-value">{{ delivery.driverPhone }}</span>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="card-actions">
                <button @click="contactDriver(delivery)" class="btn-action btn-contact">
                  <MessageSquare size="16" />
                  Contact Driver
                </button>
                <button @click="viewTracking(delivery)" class="btn-action btn-track">
                  <Navigation size="16" />
                  Track Live
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <Truck size="48" class="empty-icon" />
            <p>No active deliveries</p>
          </div>
        </div>

        <!-- Tracking Modal -->
        <div v-if="selectedDelivery" class="modal-overlay" @click="selectedDelivery = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Live Tracking - {{ selectedDelivery.id }}</h2>
              <button @click="selectedDelivery = null" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <div class="tracking-map">
                <div class="map-placeholder-large">
                  <MapPin size="48" />
                  <p>{{ selectedDelivery.currentLocation }}</p>
                </div>
              </div>

              <div class="tracking-details">
                <div class="timeline">
                  <div class="timeline-item">
                    <div class="timeline-marker start"></div>
                    <div class="timeline-content">
                      <span class="time">Departed</span>
                      <p>{{ selectedDelivery.origin }}</p>
                      <small>{{ selectedDelivery.departTime }}</small>
                    </div>
                  </div>

                  <div class="timeline-item current">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <span class="time">Current Location</span>
                      <p>{{ selectedDelivery.currentLocation }}</p>
                      <small>{{ selectedDelivery.currentTime }}</small>
                    </div>
                  </div>

                  <div class="timeline-item">
                    <div class="timeline-marker end"></div>
                    <div class="timeline-content">
                      <span class="time">Destination (ETA)</span>
                      <p>{{ selectedDelivery.destination }}</p>
                      <small>{{ selectedDelivery.eta }}</small>
                    </div>
                  </div>
                </div>

                <div class="tracking-stats">
                  <div class="stat-row">
                    <span class="stat-label">Speed:</span>
                    <span class="stat-value">{{ selectedDelivery.speed }} km/h</span>
                  </div>
                  <div class="stat-row">
                    <span class="stat-label">Distance to Destination:</span>
                    <span class="stat-value">{{ selectedDelivery.distanceRemaining }} km</span>
                  </div>
                  <div class="stat-row">
                    <span class="stat-label">Estimated Time:</span>
                    <span class="stat-value">{{ selectedDelivery.timeRemaining }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedDelivery = null" class="btn-secondary">Close</button>
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
import { useTheme } from '@/composables/useTheme'
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import {
  Truck, Clock, Package, MapPin, User, Phone, MessageSquare, Navigation, X
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()
const { isDark, isLight } = useTheme()

const loading = ref(false)
const selectedDelivery = ref(null)
const filterStatus = ref('')

const mockDeliveries = [
  {
    id: 'DLV-001',
    product: 'Tomatoes',
    quantity: 500,
    unit: 'kg',
    origin: 'Green Valley Farm',
    destination: 'City Market Central Hub',
    currentLocation: 'Highway 5, Near km 15',
    status: 'on-way',
    progress: 65,
    distanceCovered: 65,
    totalDistance: 100,
    driver: 'John Smith',
    driverPhone: '+1-555-0401',
    departTime: '06:00 AM',
    currentTime: '08:15 AM',
    eta: '09:30 AM',
    speed: 85,
    distanceRemaining: 35,
    timeRemaining: '25 mins'
  },
  {
    id: 'DLV-002',
    product: 'Corn',
    quantity: 1000,
    unit: 'kg',
    origin: 'Sunny Acres',
    destination: 'Regional Distribution Center',
    currentLocation: 'City Center, Downtown Area',
    status: 'near',
    progress: 92,
    distanceCovered: 92,
    totalDistance: 100,
    driver: 'Maria Garcia',
    driverPhone: '+1-555-0402',
    departTime: '05:30 AM',
    currentTime: '08:45 AM',
    eta: '09:00 AM',
    speed: 45,
    distanceRemaining: 8,
    timeRemaining: '12 mins'
  },
  {
    id: 'DLV-003',
    product: 'Lettuce',
    quantity: 300,
    unit: 'kg',
    origin: 'Harvest Farm',
    destination: 'Wholesale Center',
    currentLocation: 'Outskirts, Business District',
    status: 'delayed',
    progress: 45,
    distanceCovered: 45,
    totalDistance: 100,
    driver: 'Robert Wilson',
    driverPhone: '+1-555-0403',
    departTime: '07:00 AM',
    currentTime: '08:30 AM',
    eta: '10:15 AM',
    speed: 35,
    distanceRemaining: 55,
    timeRemaining: '1 hour 45 mins'
  }
]

const deliveries = ref(mockDeliveries)

const filteredDeliveries = computed(() => {
  if (!filterStatus.value) return deliveries.value
  return deliveries.value.filter(d => d.status === filterStatus.value)
})

const stats = computed(() => ({
  inTransit: deliveries.value.length,
  avgEta: '1 hour 5 mins',
  totalItems: deliveries.value.reduce((sum, d) => sum + d.quantity, 0),
  totalDistance: deliveries.value.reduce((sum, d) => sum + d.totalDistance, 0)
}))

const fetchDeliveries = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)
const contactDriver = (delivery) => { alert(`Contact ${delivery.driver} at ${delivery.driverPhone}`) }
const viewTracking = (delivery) => { selectedDelivery.value = delivery }

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchDeliveries() })
</script>

<style scoped>
.active-deliveries-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.deliveries-container {
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

.deliveries-content {
  padding: 30px;
  flex: 1;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.stat-icon {
  color: #d1d5db;
  flex-shrink: 0;
}

.stat-icon.active { color: #3b82f6; }
.stat-icon.eta { color: #8b5cf6; }
.stat-icon.total { color: #10b981; }
.stat-icon.distance { color: #f59e0b; }

.stat-label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
}

.deliveries-section {
  margin-top: 30px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 13px;
  color: #4b5563;
  background: white;
  cursor: pointer;
}

.deliveries-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.delivery-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
  transition: all 0.3s;
}

.delivery-card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
  border-color: #3b82f6;
}

.card-header {
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.card-header h3 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.destination {
  font-size: 13px;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.status-on-way { background: #dbeafe; color: #1e40af; }
.status-near { background: #fef3c7; color: #92400e; }
.status-delayed { background: #fee2e2; color: #991b1b; }

.card-info {
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.info-item {
  display: flex;
  flex-direction: column;
}

.info-item .label {
  font-size: 11px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.info-item .value {
  font-size: 13px;
  color: #1f2937;
  font-weight: 600;
}

.delivery-map {
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.map-placeholder {
  background: #f3f4f6;
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.map-placeholder svg {
  color: #3b82f6;
}

.map-placeholder p {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
}

.delivery-progress {
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #1e40af);
  transition: width 0.3s;
}

.progress-text {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
}

.delivery-details {
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.detail {
  display: flex;
  align-items: center;
  gap: 8px;
}

.detail svg {
  color: #3b82f6;
  flex-shrink: 0;
}

.detail-label {
  display: block;
  font-size: 10px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.detail-value {
  display: block;
  font-size: 12px;
  color: #1f2937;
  font-weight: 600;
}

.card-actions {
  padding: 12px 16px;
  display: flex;
  gap: 8px;
}

.btn-action {
  flex: 1;
  padding: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.2s;
}

.btn-contact {
  background: #eff6ff;
  color: #1e40af;
  border-color: #3b82f6;
}

.btn-contact:hover {
  background: #3b82f6;
  color: white;
}

.btn-track {
  background: #ecfdf5;
  color: #065f46;
  border-color: #10b981;
}

.btn-track:hover {
  background: #10b981;
  color: white;
}

.empty-state {
  padding: 60px 20px;
  text-align: center;
}

.empty-icon {
  color: #d1d5db;
  margin-bottom: 16px;
}

.empty-state p {
  font-size: 16px;
  font-weight: 600;
  color: #6b7280;
}

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
  max-width: 600px;
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

.tracking-map {
  margin-bottom: 24px;
}

.map-placeholder-large {
  background: #f3f4f6;
  border-radius: 8px;
  padding: 40px 20px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.map-placeholder-large svg {
  color: #3b82f6;
}

.map-placeholder-large p {
  font-size: 14px;
  color: #6b7280;
  margin: 0;
}

.tracking-details {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
}

.timeline {
  margin-bottom: 20px;
}

.timeline-item {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  position: relative;
}

.timeline-item:not(:last-child)::after {
  content: '';
  position: absolute;
  left: 9px;
  top: 40px;
  width: 2px;
  height: 30px;
  background: #e5e7eb;
}

.timeline-item.current::after {
  background: #3b82f6;
}

.timeline-marker {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid #e5e7eb;
  background: white;
  flex-shrink: 0;
  margin-top: 2px;
}

.timeline-marker.start {
  background: #10b981;
  border-color: #10b981;
}

.timeline-marker.end {
  background: #ef4444;
  border-color: #ef4444;
}

.timeline-item.current .timeline-marker {
  background: #3b82f6;
  border-color: #3b82f6;
  width: 24px;
  height: 24px;
  margin-top: 0;
}

.timeline-content {
  flex: 1;
  padding-top: 2px;
}

.timeline-content .time {
  font-weight: 700;
  color: #1f2937;
  font-size: 13px;
}

.timeline-content p {
  margin: 4px 0;
  font-size: 13px;
  color: #4b5563;
}

.timeline-content small {
  font-size: 11px;
  color: #6b7280;
}

.tracking-stats {
  background: #f9fafb;
  border-radius: 6px;
  padding: 12px;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  font-size: 13px;
  border-bottom: 1px solid #e5e7eb;
}

.stat-row:last-child {
  border-bottom: none;
}

.stat-label {
  color: #6b7280;
  font-weight: 600;
}

.stat-value {
  color: #1f2937;
  font-weight: 700;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
}

.btn-secondary {
  padding: 10px 20px;
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

@media (max-width: 768px) {
  .deliveries-container {
    margin-left: 0;
  }

  .deliveries-grid {
    grid-template-columns: 1fr;
  }

  .delivery-details {
    grid-template-columns: 1fr;
  }
}

/* ==================== DARK MODE STYLES ==================== */

/* Layout & Background */
.active-deliveries-layout.dark {
  background-color: #0b0f17;
}

.active-deliveries-layout.dark .deliveries-container {
  background-color: #0b0f17;
}

/* Page Header */
.active-deliveries-layout.dark .page-header {
  background: #131b2e;
  border-bottom-color: #2d3f52;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.active-deliveries-layout.dark .header-content h1 {
  color: #f1f5f9;
}

.active-deliveries-layout.dark .header-content p {
  color: #cbd5e1;
}

/* Loading */
.active-deliveries-layout.dark .loading-container {
  color: #cbd5e1;
}

.active-deliveries-layout.dark .spinner {
  border-color: #2d3f52;
  border-top-color: #3b82f6;
}

/* Content */
.active-deliveries-layout.dark .deliveries-content {
  background-color: #0b0f17;
}

/* Stats Grid */
.active-deliveries-layout.dark .stat-card {
  background: #1a2338;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.active-deliveries-layout.dark .stat-label {
  color: #94a3b8;
}

.active-deliveries-layout.dark .stat-value {
  color: #f1f5f9;
}

/* Deliveries Section */
.active-deliveries-layout.dark .section-header h2 {
  color: #f1f5f9;
}

.active-deliveries-layout.dark .filter-select {
  background: #1a2338;
  border-color: #2d3f52;
  color: #f1f5f9;
}

.active-deliveries-layout.dark .filter-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Delivery Cards */
.active-deliveries-layout.dark .delivery-card {
  background: #1a2338;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  border-color: #2d3f52;
}

.active-deliveries-layout.dark .delivery-card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
  border-color: #3b82f6;
}

.active-deliveries-layout.dark .card-header {
  border-bottom-color: #2d3f52;
}

.active-deliveries-layout.dark .card-header h3 {
  color: #f1f5f9;
}

.active-deliveries-layout.dark .destination {
  color: #cbd5e1;
}

.active-deliveries-layout.dark .status-on-way {
  background: #1a2e4d;
  color: #60a5fa;
}

.active-deliveries-layout.dark .status-near {
  background: #1a1a2e;
  color: #fbbf24;
}

.active-deliveries-layout.dark .status-delayed {
  background: #2e1a1a;
  color: #f87171;
}

.active-deliveries-layout.dark .card-info {
  border-bottom-color: #2d3f52;
}

.active-deliveries-layout.dark .info-item .label {
  color: #94a3b8;
}

.active-deliveries-layout.dark .info-item .value {
  color: #cbd5e1;
}

.active-deliveries-layout.dark .delivery-map {
  border-bottom-color: #2d3f52;
}

.active-deliveries-layout.dark .map-placeholder {
  background: #0f1620;
  border-color: #2d3f52;
}

.active-deliveries-layout.dark .map-placeholder p {
  color: #cbd5e1;
}

.active-deliveries-layout.dark .delivery-progress {
  border-bottom-color: #2d3f52;
}

.active-deliveries-layout.dark .progress-bar {
  background: #0f1620;
}

.active-deliveries-layout.dark .progress-text {
  color: #cbd5e1;
}

.active-deliveries-layout.dark .delivery-details {
  border-bottom-color: #2d3f52;
}

.active-deliveries-layout.dark .detail svg {
  color: #60a5fa;
}

.active-deliveries-layout.dark .detail-label {
  color: #94a3b8;
}

.active-deliveries-layout.dark .detail-value {
  color: #cbd5e1;
}

.active-deliveries-layout.dark .card-actions {
  padding: 12px 16px;
  gap: 8px;
}

.active-deliveries-layout.dark .btn-contact {
  background: #1a2e4d;
  color: #60a5fa;
  border-color: #3b82f6;
}

.active-deliveries-layout.dark .btn-contact:hover {
  background: #3b82f6;
  color: white;
}

.active-deliveries-layout.dark .btn-track {
  background: #1a2e2e;
  color: #4ade80;
  border-color: #10b981;
}

.active-deliveries-layout.dark .btn-track:hover {
  background: #10b981;
  color: white;
}

/* Empty State */
.active-deliveries-layout.dark .empty-state {
  color: #64748b;
}

.active-deliveries-layout.dark .empty-icon {
  color: #2d3f52;
}

.active-deliveries-layout.dark .empty-state p {
  color: #cbd5e1;
}

/* Modal */
.active-deliveries-layout.dark .modal-overlay {
  background: rgba(0, 0, 0, 0.7);
}

.active-deliveries-layout.dark .modal-content {
  background: #131b2e;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.active-deliveries-layout.dark .modal-header {
  background: #1a2338;
  border-bottom-color: #2d3f52;
}

.active-deliveries-layout.dark .modal-header h2 {
  color: #f1f5f9;
}

.active-deliveries-layout.dark .btn-close {
  color: #94a3b8;
}

.active-deliveries-layout.dark .btn-close:hover {
  color: #f1f5f9;
}

/* Modal Body */
.active-deliveries-layout.dark .modal-body {
  background: #131b2e;
  color: #cbd5e1;
}

.active-deliveries-layout.dark .map-placeholder-large {
  background: #0f1620;
}

.active-deliveries-layout.dark .map-placeholder-large p {
  color: #cbd5e1;
}

/* Tracking Details */
.active-deliveries-layout.dark .tracking-details {
  border-color: #2d3f52;
  background: transparent;
}

.active-deliveries-layout.dark .timeline-item:not(:last-child)::after {
  background: #2d3f52;
}

.active-deliveries-layout.dark .timeline-item.current::after {
  background: #3b82f6;
}

.active-deliveries-layout.dark .timeline-content .time {
  color: #f1f5f9;
}

.active-deliveries-layout.dark .timeline-content p {
  color: #cbd5e1;
}

.active-deliveries-layout.dark .timeline-content small {
  color: #64748b;
}

.active-deliveries-layout.dark .tracking-stats {
  background: #0f1620;
}

.active-deliveries-layout.dark .stat-row {
  border-bottom-color: #2d3f52;
}

.active-deliveries-layout.dark .stat-label {
  color: #94a3b8;
}

.active-deliveries-layout.dark .stat-value {
  color: #f1f5f9;
}

/* Modal Footer */
.active-deliveries-layout.dark .modal-footer {
  background: #1a2338;
  border-top-color: #2d3f52;
}

.active-deliveries-layout.dark .btn-secondary {
  background: #0f1620;
  border-color: #2d3f52;
  color: #cbd5e1;
}

.active-deliveries-layout.dark .btn-secondary:hover {
  background: #1a2338;
}
</style>
