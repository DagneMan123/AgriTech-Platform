<template>
  <div class="subsidy-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="subsidy-container">
      <div class="page-header">
        <div class="header-content">
          <h1>Subsidy Requests</h1>
          <p>Apply for and manage government subsidies and financial assistance</p>
        </div>
        <button @click="showNewRequest = true" class="btn-new">
          <Plus size="16" />
          <span>New Request</span>
        </button>
      </div>

      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading requests...</p>
      </div>

      <div v-if="!loading && !error" class="subsidy-content">
        <div class="stats-grid">
          <div class="stat-card">
            <FileText size="20" />
            <div>
              <span class="stat-label">Total Requests</span>
              <span class="stat-value">{{ requests.length }}</span>
            </div>
          </div>
          <div class="stat-card">
            <CheckCircle size="20" />
            <div>
              <span class="stat-label">Approved</span>
              <span class="stat-value">{{ requests.filter(r => r.status === 'approved').length }}</span>
            </div>
          </div>
          <div class="stat-card">
            <Clock size="20" />
            <div>
              <span class="stat-label">Pending</span>
              <span class="stat-value">{{ requests.filter(r => r.status === 'pending').length }}</span>
            </div>
          </div>
          <div class="stat-card">
            <DollarSign size="20" />
            <div>
              <span class="stat-label">Total Approved</span>
              <span class="stat-value">${{ formatNumber(totalApproved) }}</span>
            </div>
          </div>
        </div>

        <div class="requests-grid">
          <div v-for="request in requests" :key="request.id" class="request-card">
            <div class="request-header">
              <h3>{{ request.programName }}</h3>
              <span class="status-badge" :class="`status-${request.status}`">
                {{ capitalize(request.status) }}
              </span>
            </div>
            <div class="request-details">
              <p><strong>Type:</strong> {{ request.type }}</p>
              <p><strong>Amount Requested:</strong> ${{ formatNumber(request.amount) }}</p>
              <p><strong>Status:</strong> {{ request.statusMessage }}</p>
              <p><strong>Applied:</strong> {{ formatDate(request.appliedDate) }}</p>
            </div>
            <div class="request-footer">
              <button @click="viewRequest(request)" class="btn-view">
                <Eye size="14" />
                View Details
              </button>
              <button v-if="request.status === 'rejected'" @click="showNewRequest = true" class="btn-reapply">
                <RefreshCw size="14" />
                Reapply
              </button>
            </div>
          </div>
        </div>

        <div v-if="requests.length === 0" class="empty-state">
          <Gift size="48" />
          <p>No subsidy requests yet</p>
          <button @click="showNewRequest = true" class="btn-primary">Create First Request</button>
        </div>
      </div>

      <!-- New Request Form Modal -->
      <div v-if="showNewRequest" class="modal-overlay" @click="showNewRequest = false">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>New Subsidy Request</h2>
            <button @click="showNewRequest = false" class="btn-close">
              <X size="20" />
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitRequest" class="form">
              <div class="form-group">
                <label>Subsidy Program</label>
                <select v-model="newRequest.program" required class="form-control">
                  <option value="">Select a program</option>
                  <option value="seeds">Seed Subsidy Program</option>
                  <option value="fertilizer">Fertilizer Subsidy Scheme</option>
                  <option value="equipment">Equipment Purchase Assistance</option>
                  <option value="irrigation">Irrigation System Subsidy</option>
                  <option value="organic">Organic Farming Transition</option>
                </select>
              </div>

              <div class="form-group">
                <label>Amount Required ($)</label>
                <input v-model.number="newRequest.amount" type="number" required placeholder="Enter amount" class="form-control" />
              </div>

              <div class="form-group">
                <label>Description</label>
                <textarea v-model="newRequest.description" required placeholder="Describe your subsidy request" class="form-control" rows="4"></textarea>
              </div>

              <div class="form-group">
                <label>Supporting Documents</label>
                <div class="file-upload">
                  <input type="file" multiple class="file-input" />
                  <span>Upload documents (PDF, images)</span>
                </div>
              </div>

              <div class="form-actions">
                <button type="button" @click="showNewRequest = false" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Submit Request</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- View Details Modal -->
      <div v-if="selectedRequest" class="modal-overlay" @click="selectedRequest = null">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h2>{{ selectedRequest.programName }}</h2>
            <button @click="selectedRequest = null" class="btn-close">
              <X size="20" />
            </button>
          </div>
          <div class="modal-body">
            <div class="detail-section">
              <h3>Request Details</h3>
              <div class="detail-item">
                <span class="label">Program:</span>
                <span class="value">{{ selectedRequest.programName }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Type:</span>
                <span class="value">{{ selectedRequest.type }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Amount Requested:</span>
                <span class="value">${{ formatNumber(selectedRequest.amount) }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Status:</span>
                <span class="status-badge" :class="`status-${selectedRequest.status}`">
                  {{ capitalize(selectedRequest.status) }}
                </span>
              </div>
            </div>

            <div class="detail-section">
              <h3>Timeline</h3>
              <p><strong>Applied:</strong> {{ formatDate(selectedRequest.appliedDate) }}</p>
              <p><strong>Last Updated:</strong> {{ formatDate(selectedRequest.updatedDate) }}</p>
              <p v-if="selectedRequest.approvedDate"><strong>Approved:</strong> {{ formatDate(selectedRequest.approvedDate) }}</p>
            </div>

            <div class="detail-section">
              <h3>Description</h3>
              <p>{{ selectedRequest.description }}</p>
            </div>

            <div v-if="selectedRequest.remarks" class="detail-section remarks">
              <h3>Officer Remarks</h3>
              <p>{{ selectedRequest.remarks }}</p>
            </div>
          </div>

          <div class="modal-footer">
            <button @click="selectedRequest = null" class="btn-secondary">Close</button>
            <button v-if="selectedRequest.status === 'approved'" @click="downloadApprovalLetter" class="btn-primary">
              <Download size="14" />
              Download Approval Letter
            </button>
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
import {
  Plus, X, Eye, RefreshCw, Gift, Download, FileText, CheckCircle, Clock, DollarSign
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const showNewRequest = ref(false)
const selectedRequest = ref(null)

const newRequest = ref({
  program: '',
  amount: null,
  description: ''
})

const requests = ref([
  {
    id: 1,
    programName: 'Seed Subsidy Program',
    type: 'Subsidy',
    amount: 5000,
    status: 'approved',
    statusMessage: 'Approved - Fund transferred',
    appliedDate: '2026-08-01',
    updatedDate: '2026-08-15',
    approvedDate: '2026-08-15',
    description: 'Applied for seeds subsidy for crop diversification',
    remarks: 'Application approved. Funds approved for $5,000'
  },
  {
    id: 2,
    programName: 'Equipment Purchase Assistance',
    type: 'Grant',
    amount: 25000,
    status: 'pending',
    statusMessage: 'Under review by agricultural department',
    appliedDate: '2026-09-01',
    updatedDate: '2026-09-02',
    approvedDate: null,
    description: 'Request for tractor purchase assistance',
    remarks: null
  },
  {
    id: 3,
    programName: 'Organic Farming Transition',
    type: 'Subsidy',
    amount: 8000,
    status: 'approved',
    statusMessage: 'Approved - Certification pending',
    appliedDate: '2026-07-15',
    updatedDate: '2026-07-25',
    approvedDate: '2026-07-25',
    description: 'Support for transitioning to organic farming practices',
    remarks: 'Approved. Certification required within 6 months'
  },
  {
    id: 4,
    programName: 'Irrigation System Subsidy',
    type: 'Subsidy',
    amount: 12000,
    status: 'rejected',
    statusMessage: 'Not eligible for this cycle',
    appliedDate: '2026-06-01',
    updatedDate: '2026-06-30',
    approvedDate: null,
    description: 'Solar-powered irrigation system installation',
    remarks: 'Farm size does not meet minimum requirements'
  }
])

const totalApproved = computed(() => {
  return requests.value
    .filter(r => r.status === 'approved')
    .reduce((sum, r) => sum + r.amount, 0)
})

const fetchRequests = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)
const formatDate = (date) => new Date(date).toLocaleDateString()
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)

const viewRequest = (request) => { selectedRequest.value = request }
const submitRequest = () => {
  alert('Request submitted successfully')
  showNewRequest.value = false
  newRequest.value = { program: '', amount: null, description: '' }
}
const downloadApprovalLetter = () => { alert('Approval letter downloaded') }

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchRequests() })
</script>

<style scoped>
.subsidy-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.subsidy-container {
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
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.btn-new {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-new:hover {
  background: #059669;
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
  border-top-color: #10b981;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.subsidy-content {
  padding: 30px;
  flex: 1;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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

.stat-card svg {
  color: #d1d5db;
  flex-shrink: 0;
}

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

.requests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.request-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border-left: 4px solid #10b981;
  transition: all 0.3s;
}

.request-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.request-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
  gap: 12px;
}

.request-header h3 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.status-approved {
  background: #d1fae5;
  color: #065f46;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-rejected {
  background: #fee2e2;
  color: #991b1b;
}

.request-details {
  margin-bottom: 16px;
  font-size: 13px;
  color: #4b5563;
}

.request-details p {
  margin: 8px 0;
}

.request-footer {
  display: flex;
  gap: 8px;
}

.btn-view,
.btn-reapply {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: white;
  color: #4b5563;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-view:hover,
.btn-reapply:hover {
  border-color: #10b981;
  color: #10b981;
  background: #ecfdf5;
}

.empty-state {
  text-align: center;
  padding: 80px 20px;
}

.empty-state svg {
  color: #d1d5db;
  margin-bottom: 16px;
}

.empty-state p {
  font-size: 16px;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 20px;
}

.btn-primary {
  background: #10b981;
  color: white;
  padding: 10px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: #059669;
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

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #4b5563;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  color: #1f2937;
  font-family: inherit;
}

.file-upload {
  border: 2px dashed #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.file-upload:hover {
  border-color: #10b981;
  background: #ecfdf5;
}

.file-input {
  display: none;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.btn-secondary {
  padding: 10px 20px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: white;
  color: #4b5563;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #f3f4f6;
}

.detail-section {
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.detail-section h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 13px;
}

.detail-item .label {
  color: #6b7280;
  font-weight: 600;
}

.detail-item .value {
  color: #1f2937;
}

.detail-section.remarks {
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
  border: none;
  margin-bottom: 0;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.modal-footer .btn-primary,
.modal-footer .btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

@media (max-width: 768px) {
  .subsidy-container {
    margin-left: 0;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-new {
    width: 100%;
    justify-content: center;
  }

  .requests-grid {
    grid-template-columns: 1fr;
  }

  .request-header {
    flex-direction: column;
  }
}
</style>
