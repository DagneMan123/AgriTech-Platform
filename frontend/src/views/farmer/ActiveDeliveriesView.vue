<template>
  <div class="active-deliveries-layout" :class="{ 'light': isLight, 'dark': isDark }">
    <FarmerSidebar @logout="handleLogout" />
    <div class="deliveries-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Active Deliveries &amp; Logistics Tracking</h1>
          <p>Real-time telemetry and dispatch status for produce currently in transit (Stage 3: Shipped)</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading live delivery telemetry from database...</p>
      </div>

      <!-- Error State -->
      <div v-if="error && !loading" class="error-container">
        <AlertCircle size="40" class="error-icon" />
        <p class="error-message">{{ error }}</p>
        <button @click="fetchDeliveries" class="btn-retry">
          <RotateCcw size="16" />
          Retry
        </button>
      </div>

      <!-- Content -->
      <div v-if="!loading && !error" class="deliveries-content">
        <!-- 4-Stage Visual Lifecycle Banner -->
        <div class="mb-6 p-4 rounded-2xl border bg-white dark:bg-[#131b2e] border-slate-200 dark:border-slate-800 shadow-xs">
          <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3 flex items-center justify-between">
            <span class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-purple-500 animate-ping"></span>
              Stage 3 Active Dispatches &mdash; Live Transit View
            </span>
            <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
              <ShieldCheck size="14" />
              Chapa Escrow Releases Upon Final Delivery
            </span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 opacity-70">
              <div class="font-bold text-amber-700 dark:text-amber-400 flex items-center gap-1.5 mb-1">
                <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                <span>Stage 1: Pending</span>
              </div>
              <p class="text-[11px] text-slate-600 dark:text-slate-400 m-0">Order/Request placed.</p>
            </div>
            <div class="p-3 rounded-xl bg-blue-500/10 border border-blue-500/20 opacity-70">
              <div class="font-bold text-blue-700 dark:text-blue-400 flex items-center gap-1.5 mb-1">
                <span class="w-5 h-5 rounded-full bg-blue-500 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                <span>Stage 2: Confirmed</span>
              </div>
              <p class="text-[11px] text-slate-600 dark:text-slate-400 m-0">Transporter/vehicle assigned.</p>
            </div>
            <div class="p-3 rounded-xl bg-purple-500/20 border-2 border-purple-500">
              <div class="font-bold text-purple-700 dark:text-purple-300 flex items-center gap-1.5 mb-1">
                <span class="w-5 h-5 rounded-full bg-purple-500 text-white flex items-center justify-center text-[10px] font-bold">3</span>
                <span>Stage 3: Shipped (Active)</span>
              </div>
              <p class="text-[11px] text-slate-700 dark:text-slate-300 m-0 font-medium">In transit to destination warehouse.</p>
            </div>
            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 opacity-70">
              <div class="font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 mb-1">
                <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">4</span>
                <span>Stage 4: Delivered &amp; Paid</span>
              </div>
              <p class="text-[11px] text-slate-600 dark:text-slate-400 m-0">Escrow released to farmer wallet.</p>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <Truck size="20" class="stat-icon active" />
            <div>
              <span class="stat-label">In Transit (Stage 3)</span>
              <span class="stat-value">{{ stats.inTransit }}</span>
            </div>
          </div>
          <div class="stat-card">
            <Clock size="20" class="stat-icon eta" />
            <div>
              <span class="stat-label">Logistics Hub</span>
              <span class="stat-value text-sm font-bold">Kality Central</span>
            </div>
          </div>
          <div class="stat-card">
            <Package size="20" class="stat-icon total" />
            <div>
              <span class="stat-label">Active Cargo Units</span>
              <span class="stat-value">{{ stats.totalItems }}</span>
            </div>
          </div>
          <div class="stat-card">
            <ShieldCheck size="20" class="stat-icon distance" style="color: #10b981;" />
            <div>
              <span class="stat-label">Protected Escrow</span>
              <span class="stat-value text-base">ETB {{ formatNumber(stats.escrowAmount) }}</span>
            </div>
          </div>
        </div>

        <!-- Active Deliveries Cards -->
        <div class="deliveries-section">
          <div class="section-header">
            <h2>Active In-Transit Shipments</h2>
            <div class="controls flex items-center gap-3">
              <span class="text-xs text-slate-500">{{ filteredDeliveries.length }} active cargo dispatches</span>
              <button @click="fetchDeliveries" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer" title="Refresh Live Data">
                <RotateCcw size="14" class="text-slate-600 dark:text-slate-400" />
              </button>
            </div>
          </div>

          <div v-if="filteredDeliveries.length > 0" class="deliveries-grid">
            <div v-for="delivery in filteredDeliveries" :key="delivery.id" class="delivery-card">
              <div class="card-header">
                <div>
                  <div class="flex items-center gap-2">
                    <h3 class="font-mono font-bold">{{ delivery.id }}</h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                      Stage 3: Shipped
                    </span>
                  </div>
                  <p class="destination">Destination: {{ delivery.destination }}</p>
                </div>
              </div>

              <div class="card-info">
                <div class="info-item">
                  <span class="label">Produce Cargo:</span>
                  <span class="value font-bold">{{ delivery.product }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Quantity:</span>
                  <span class="value font-semibold">{{ delivery.quantity }} {{ delivery.unit }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Escrow Value:</span>
                  <span class="value font-mono font-bold text-emerald-600 dark:text-emerald-400">
                    ETB {{ formatNumber(delivery.amount) }}
                  </span>
                </div>
                <div class="info-item">
                  <span class="label">Dispatched Date:</span>
                  <span class="value">{{ formatDate(delivery.departTime) }}</span>
                </div>
              </div>

              <!-- Route Card -->
              <div class="delivery-map">
                <div class="map-placeholder">
                  <MapPin size="20" class="text-purple-500" />
                  <p class="text-xs">
                    <strong class="text-slate-800 dark:text-slate-200">En Route:</strong>
                    {{ delivery.origin }} &rarr; {{ delivery.destination }}
                  </p>
                </div>
              </div>

              <!-- Driver & Vehicle Details -->
              <div class="delivery-details">
                <div class="detail">
                  <User size="16" />
                  <div>
                    <span class="detail-label">Carrier / Driver</span>
                    <span class="detail-value">{{ delivery.driver }}</span>
                  </div>
                </div>
                <div class="detail">
                  <Truck size="16" />
                  <div>
                    <span class="detail-label">Vehicle Plate</span>
                    <span class="detail-value font-mono">{{ delivery.vehicle }}</span>
                  </div>
                </div>
                <div class="detail">
                  <Phone size="16" />
                  <div>
                    <span class="detail-label">Driver Phone</span>
                    <span class="detail-value font-mono">{{ delivery.driverPhone }}</span>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="card-actions flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button @click="viewTracking(delivery)" class="btn-action btn-track flex-1">
                  <Navigation size="15" />
                  <span>View Route Telemetry</span>
                </button>
                <!-- Stage 4 Trigger: Mark Delivered & Release Escrow -->
                <button
                  @click="confirmDeliveryAndReleaseEscrow(delivery)"
                  class="btn-action btn-deliver flex-1"
                  style="background: #10b981; color: white; border-color: #10b981;"
                  title="Stage 4: Verify receipt and release Chapa Escrow payout"
                >
                  <CheckCircle2 size="15" />
                  <span>Mark as Delivered &amp; Release Escrow</span>
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
              <Truck size="40" class="opacity-40" />
            </div>
            <h4 class="text-base font-bold text-slate-800 dark:text-slate-200 mb-1">No Produce Currently in Transit</h4>
            <p class="text-xs text-slate-500 max-w-md mb-4 leading-relaxed">
              When orders or transport requests enter Stage 3 (Shipped / Dispatched), they will appear here with live route tracking until arriving at destination.
            </p>
            <router-link
              to="/farmer/customer-orders"
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-500 text-white hover:bg-emerald-600 transition-all cursor-pointer"
            >
              <span>View Customer Orders to Dispatch</span>
              <span>&rarr;</span>
            </router-link>
          </div>
        </div>

        <!-- Tracking Modal -->
        <div v-if="selectedDelivery" class="modal-overlay" @click="selectedDelivery = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Logistics Route Telemetry &mdash; {{ selectedDelivery.id }}</h2>
              <button @click="selectedDelivery = null" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <div class="tracking-details">
                <div class="timeline">
                  <div class="timeline-item start">
                    <div class="timeline-marker start"></div>
                    <div class="timeline-content">
                      <span class="time font-bold">1. Origin Farm Gate (Dispatched)</span>
                      <p>{{ selectedDelivery.origin }}</p>
                      <small>Picked up: {{ formatDate(selectedDelivery.departTime) }}</small>
                    </div>
                  </div>

                  <div class="timeline-item current">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                      <span class="time font-bold text-purple-600 dark:text-purple-400">2. In Transit (Cargo Dispatched)</span>
                      <p>Vehicle actively transporting {{ selectedDelivery.quantity }} {{ selectedDelivery.unit }} {{ selectedDelivery.product }}</p>
                      <small>Status: Stage 3 Shipped</small>
                    </div>
                  </div>

                  <div class="timeline-item end">
                    <div class="timeline-marker end"></div>
                    <div class="timeline-content">
                      <span class="time font-bold">3. Destination Hub</span>
                      <p>{{ selectedDelivery.destination }}</p>
                      <small>Inspection &amp; Escrow Release Destination</small>
                    </div>
                  </div>
                </div>

                <div class="tracking-stats mt-4 p-4 rounded-xl border bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-800">
                  <div class="stat-row flex justify-between py-1 text-xs">
                    <span class="stat-label text-slate-500">Transporter / Fleet:</span>
                    <span class="stat-value font-bold text-slate-800 dark:text-slate-200">{{ selectedDelivery.driver }} ({{ selectedDelivery.vehicle }})</span>
                  </div>
                  <div class="stat-row flex justify-between py-1 text-xs">
                    <span class="stat-label text-slate-500">Contact Number:</span>
                    <span class="stat-value font-mono font-bold text-slate-800 dark:text-slate-200">{{ selectedDelivery.driverPhone }}</span>
                  </div>
                  <div class="stat-row flex justify-between py-1 text-xs">
                    <span class="stat-label text-slate-500">Protected Chapa Escrow:</span>
                    <span class="stat-value font-mono font-bold text-emerald-600 dark:text-emerald-400">ETB {{ formatNumber(selectedDelivery.amount) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer flex items-center justify-between">
              <button @click="selectedDelivery = null" class="btn-secondary">Close</button>
              <button
                @click="confirmDeliveryAndReleaseEscrow(selectedDelivery); selectedDelivery = null;"
                class="btn-primary"
                style="background: #10b981;"
              >
                <CheckCircle2 size="15" />
                <span>Mark Delivered &amp; Release Escrow</span>
              </button>
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
import { farmerAPI } from '@/api/farmer'
import {
  Truck, Clock, Package, MapPin, User, Phone, Navigation, X,
  ShieldCheck, AlertCircle, RotateCcw, CheckCircle2
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()
const { isDark, isLight } = useTheme()

const loading = ref(false)
const error = ref(null)
const selectedDelivery = ref(null)
const deliveries = ref([])

const stats = computed(() => ({
  inTransit: deliveries.value.length,
  totalItems: deliveries.value.reduce((sum, d) => sum + Number(d.quantity || 0), 0),
  escrowAmount: deliveries.value.reduce((sum, d) => sum + Number(d.amount || 0), 0)
}))

const filteredDeliveries = computed(() => deliveries.value)

const fetchDeliveries = async () => {
  loading.value = true
  error.value = null
  try {
    const [ordersRes, transportRes] = await Promise.all([
      farmerAPI.getOrders(),
      farmerAPI.getTransportRequests()
    ])

    const rawOrders = ordersRes.data?.data || ordersRes.data || []
    const rawTransport = transportRes.data?.data || transportRes.data || []

    const list = []

    // 1. Process shipped/in-transit orders
    if (Array.isArray(rawOrders)) {
      rawOrders.forEach(o => {
        const s = (o.status || '').toLowerCase()
        if (s === 'shipped' || s === 'in_transit') {
          const item = (o.items && o.items[0]) || {}
          list.push({
            id: o.order_number || `ORD-${String(o.id).padStart(4, '0')}`,
            rawId: o.id,
            sourceType: 'order',
            product: item.product?.name || item.product_name || 'Agricultural Produce',
            quantity: Number(item.quantity || 1),
            unit: item.product?.quantity_unit || item.unit || 'kg',
            amount: Number(o.grand_total || o.subtotal || 0),
            origin: 'Bishoftu Regional Farm Gate',
            destination: o.delivery_address || 'Kality Central Grain Warehouse, Addis Ababa',
            driver: o.transporter?.name || 'National Logistics Fleet Driver',
            driverPhone: o.delivery_phone || o.transporter?.phone || '+251 91 123 4567',
            vehicle: o.vehicle?.plate_number || 'ET-38491 (Freight Truck)',
            departTime: o.updated_at || o.created_at,
            status: 'shipped'
          })
        }
      })
    }

    // 2. Process shipped/in-transit transport requests
    if (Array.isArray(rawTransport)) {
      rawTransport.forEach(t => {
        const s = (t.status || '').toLowerCase()
        if (s === 'shipped' || s === 'in_transit') {
          list.push({
            id: `TRN-${String(t.id).padStart(4, '0')}`,
            rawId: t.id,
            sourceType: 'transport_request',
            product: t.product_name || t.product?.name || 'Produce Cargo',
            quantity: Number(t.quantity || 0),
            unit: t.unit || 'bags',
            amount: Number(t.estimated_cost || 0),
            origin: t.pickup_location || 'Farm Gate Hub',
            destination: t.delivery_location || 'Kality Central Grain Warehouse, Addis Ababa',
            driver: t.transporter?.company_name || t.transporter?.contact_name || t.contact_person || 'Assigned Transporter',
            driverPhone: t.transporter?.phone || t.contact_phone || '+251 92 000 0000',
            vehicle: t.vehicle?.plate_number || 'ET-49201',
            departTime: t.pickup_date || t.created_at,
            status: 'shipped'
          })
        }
      })
    }

    deliveries.value = list
  } catch (err) {
    console.error('Error fetching active deliveries:', err)
    error.value = 'Failed to load active shipments from database.'
  } finally {
    loading.value = false
  }
}

// Stage 4 Transition: Mark as Delivered and Release Escrow
const confirmDeliveryAndReleaseEscrow = async (delivery) => {
  const confirmed = confirm(
    `Confirm final delivery for ${delivery.id}?\n\nThis transitions to Stage 4: Delivered & Paid and releases Chapa Escrow funds directly to your digital wallet account.`
  )
  if (!confirmed) return

  try {
    if (delivery.sourceType === 'order') {
      await farmerAPI.updateOrderStatus(delivery.rawId, 'delivered')
    } else {
      await farmerAPI.updateTransportRequestStatus(delivery.rawId, 'delivered')
    }

    alert(`Delivery confirmed! Chapa Escrow funds have been successfully released to your farmer digital wallet account.`)
    await fetchDeliveries()
  } catch (err) {
    console.error('Failed to confirm delivery:', err)
    alert(err?.response?.data?.message || 'Failed to confirm delivery and release escrow.')
  }
}

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const viewTracking = (delivery) => {
  selectedDelivery.value = delivery
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  fetchDeliveries()
})
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
  padding: 24px 32px;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
}

.header-content h1 {
  font-size: 26px;
  font-weight: 800;
  color: #1f2937;
  margin: 0 0 6px 0;
  letter-spacing: -0.02em;
}

.header-content p {
  color: #6b7280;
  font-size: 13px;
  margin: 0;
}

.deliveries-content {
  padding: 28px 32px;
  flex: 1;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 18px;
  margin-bottom: 26px;
}

.stat-card {
  background: white;
  border-radius: 14px;
  padding: 18px 22px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.stat-icon {
  flex-shrink: 0;
  padding: 10px;
  border-radius: 12px;
}

.stat-icon.active { color: #8b5cf6; background: rgba(139, 92, 246, 0.1); }
.stat-icon.eta { color: #3b82f6; background: rgba(59, 130, 246, 0.1); }
.stat-icon.total { color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
.stat-icon.distance { color: #10b981; background: rgba(16, 185, 129, 0.1); }

.stat-label {
  display: block;
  font-size: 11px;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 700;
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
  color: #0f172a;
}

.deliveries-section {
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2 {
  font-size: 16px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.deliveries-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
  gap: 20px;
}

.delivery-card {
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.card-header h3 {
  font-size: 16px;
  margin: 0;
  color: #0f172a;
}

.destination {
  font-size: 12px;
  color: #64748b;
  margin: 4px 0 0;
}

.card-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  background: white;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid #f1f5f9;
}

.info-item .label {
  display: block;
  font-size: 10px;
  text-transform: uppercase;
  font-weight: 700;
  color: #64748b;
}

.info-item .value {
  font-size: 12px;
  color: #0f172a;
}

.delivery-map {
  background: white;
  border: 1px dashed #cbd5e1;
  border-radius: 10px;
  padding: 14px;
}

.map-placeholder {
  display: flex;
  align-items: center;
  gap: 10px;
}

.delivery-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.detail {
  display: flex;
  align-items: center;
  gap: 10px;
}

.detail-label {
  display: block;
  font-size: 10px;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 700;
}

.detail-value {
  font-size: 12px;
  font-weight: 600;
  color: #0f172a;
}

.btn-action {
  padding: 9px 14px;
  border-radius: 9px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.2s;
  border: 1px solid #cbd5e1;
  background: white;
  color: #334155;
}

.btn-action:hover {
  background: #f1f5f9;
}

.btn-deliver:hover {
  background: #059669 !important;
}

.empty-state {
  padding: 56px 20px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 18px;
  width: 100%;
  max-width: 580px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  font-size: 16px;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
}

.modal-body {
  padding: 24px;
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 16px;
  position: relative;
  padding-left: 24px;
  border-left: 2px dashed #cbd5e1;
  margin-left: 10px;
}

.timeline-item {
  position: relative;
}

.timeline-marker {
  position: absolute;
  left: -31px;
  top: 2px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #8b5cf6;
  border: 2px solid white;
}

.timeline-marker.start { background: #3b82f6; }
.timeline-marker.end { background: #10b981; }

.timeline-content .time {
  font-size: 12px;
  color: #0f172a;
}

.timeline-content p {
  font-size: 13px;
  color: #475569;
  margin: 2px 0;
}

.timeline-content small {
  font-size: 11px;
  color: #94a3b8;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #f1f5f9;
}

.btn-secondary {
  padding: 9px 18px;
  border-radius: 9px;
  background: #f1f5f9;
  color: #334155;
  border: 1px solid #e2e8f0;
  font-weight: 700;
  font-size: 12px;
  cursor: pointer;
}

.btn-primary {
  padding: 9px 18px;
  border-radius: 9px;
  background: #10b981;
  color: white;
  border: none;
  font-weight: 700;
  font-size: 12px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* Dark Mode */
.dark.active-deliveries-layout {
  background-color: #0b101b;
}

.dark .page-header {
  background: #131b2e;
  border-bottom-color: #1e293b;
}

.dark .header-content h1 {
  color: #f8fafc;
}

.dark .stat-card {
  background: #131b2e;
  border-color: #1e293b;
}

.dark .stat-value {
  color: #f8fafc;
}

.dark .deliveries-section {
  background: #131b2e;
  border-color: #1e293b;
}

.dark .section-header h2 {
  color: #f8fafc;
}

.dark .delivery-card {
  background: #0f172a;
  border-color: #1e293b;
}

.dark .card-header h3 {
  color: #f8fafc;
}

.dark .card-info {
  background: #131b2e;
  border-color: #1e293b;
}

.dark .info-item .value {
  color: #f8fafc;
}

.dark .delivery-map {
  background: #131b2e;
  border-color: #334155;
}

.dark .detail-value {
  color: #f8fafc;
}

.dark .modal-content {
  background: #131b2e;
  border: 1px solid #1e293b;
  color: #f8fafc;
}

.dark .modal-header {
  border-bottom-color: #1e293b;
}

.dark .modal-header h2 {
  color: #f8fafc;
}

.dark .timeline-content .time {
  color: #f8fafc;
}

.dark .timeline-content p {
  color: #cbd5e1;
}

.dark .btn-action {
  background: #1e293b;
  border-color: #334155;
  color: #e2e8f0;
}

@media (max-width: 1024px) {
  .deliveries-container {
    margin-left: 0;
  }
}
</style>
