<template>
  <div class="insurance-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="insurance-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Crop Insurance</h1>
          <p>Protect your crops with comprehensive insurance coverage</p>
        </div>
        <button @click="showNewPolicy = true" class="btn-new">
          <Plus size="16" />
          <span>New Policy</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading insurance policies...</p>
      </div>

      <!-- Content -->
      <div v-if="!loading" class="insurance-content">
        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <Shield size="20" class="stat-icon active" />
            <div>
              <span class="stat-label">Active Policies</span>
              <span class="stat-value">{{ stats.activePolicies }}</span>
            </div>
          </div>
          <div class="stat-card">
            <DollarSign size="20" class="stat-icon coverage" />
            <div>
              <span class="stat-label">Total Coverage</span>
              <span class="stat-value">${{ formatNumber(stats.totalCoverage) }}</span>
            </div>
          </div>
          <div class="stat-card">
            <AlertCircle size="20" class="stat-icon claims" />
            <div>
              <span class="stat-label">Claims Filed</span>
              <span class="stat-value">{{ stats.claimsFiled }}</span>
            </div>
          </div>
          <div class="stat-card">
            <CheckCircle size="20" class="stat-icon approved" />
            <div>
              <span class="stat-label">Approved Claims</span>
              <span class="stat-value">${{ formatNumber(stats.approvedClaims) }}</span>
            </div>
          </div>
        </div>

        <!-- Active Policies -->
        <div class="policies-section">
          <div class="section-header">
            <h2>Your Policies</h2>
            <select v-model="filterStatus" class="filter-select">
              <option value="">All Policies</option>
              <option value="active">Active</option>
              <option value="expired">Expired</option>
              <option value="pending">Pending</option>
            </select>
          </div>

          <div v-if="filteredPolicies.length > 0" class="policies-grid">
            <div v-for="policy in filteredPolicies" :key="policy.id" class="policy-card">
              <div class="card-header">
                <h3>{{ policy.name }}</h3>
                <span class="status-badge" :class="`status-${policy.status}`">
                  {{ capitalize(policy.status) }}
                </span>
              </div>

              <div class="card-content">
                <div class="policy-item">
                  <span class="label">Policy ID:</span>
                  <span class="value">{{ policy.id }}</span>
                </div>
                <div class="policy-item">
                  <span class="label">Crop:</span>
                  <span class="value">{{ policy.crop }}</span>
                </div>
                <div class="policy-item">
                  <span class="label">Coverage Amount:</span>
                  <span class="value highlight">${{ formatNumber(policy.coverage) }}</span>
                </div>
                <div class="policy-item">
                  <span class="label">Premium (Annual):</span>
                  <span class="value">${{ formatNumber(policy.premium) }}</span>
                </div>
                <div class="policy-item">
                  <span class="label">Validity:</span>
                  <span class="value">{{ formatDate(policy.startDate) }} to {{ formatDate(policy.endDate) }}</span>
                </div>
                <div class="policy-item">
                  <span class="label">Coverage Type:</span>
                  <span class="value">{{ policy.coverageType }}</span>
                </div>
              </div>

              <div class="card-footer">
                <button @click="viewPolicyDetails(policy)" class="btn-action">
                  <Eye size="16" />
                  View Details
                </button>
                <button @click="fileClaim(policy)" class="btn-action btn-primary">
                  <FileText size="16" />
                  File Claim
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <Shield size="48" class="empty-icon" />
            <p>No insurance policies found</p>
          </div>
        </div>

        <!-- Insurance Products -->
        <div class="products-section">
          <h2>Available Insurance Products</h2>
          <div class="products-grid">
            <div v-for="product in insuranceProducts" :key="product.id" class="product-card">
              <div class="product-header">
                <h3>{{ product.name }}</h3>
                <span class="badge">{{ product.coverage }}</span>
              </div>
              <p class="product-description">{{ product.description }}</p>
              <div class="product-features">
                <div class="feature">
                  <span class="label">Premium:</span>
                  <span class="value">${{ formatNumber(product.premium) }}/year</span>
                </div>
                <div class="feature">
                  <span class="label">Deductible:</span>
                  <span class="value">{{ product.deductible }}</span>
                </div>
                <div class="feature">
                  <span class="label">Waiting Period:</span>
                  <span class="value">{{ product.waitingPeriod }}</span>
                </div>
              </div>
              <button @click="purchasePolicy(product)" class="btn-purchase">
                Purchase Now
              </button>
            </div>
          </div>
        </div>

        <!-- Claims History -->
        <div class="claims-section">
          <h2>Claims History</h2>
          <div v-if="claims.length > 0" class="claims-table-wrapper">
            <table class="claims-table">
              <thead>
                <tr>
                  <th>Claim ID</th>
                  <th>Policy</th>
                  <th>Date Filed</th>
                  <th>Amount Claimed</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="claim in claims" :key="claim.id" class="table-row">
                  <td class="td-id">{{ claim.id }}</td>
                  <td class="td-policy">{{ claim.policyId }}</td>
                  <td class="td-date">{{ formatDate(claim.dateFile) }}</td>
                  <td class="td-amount">${{ formatNumber(claim.amount) }}</td>
                  <td class="td-status">
                    <span class="claim-badge" :class="`claim-${claim.status}`">
                      {{ capitalize(claim.status) }}
                    </span>
                  </td>
                  <td class="td-actions">
                    <button @click="viewClaimDetails(claim)" class="action-btn">
                      <Eye size="16" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="empty-state">
            <FileText size="48" class="empty-icon" />
            <p>No claims filed yet</p>
          </div>
        </div>

        <!-- Policy Details Modal -->
        <div v-if="selectedPolicy" class="modal-overlay" @click="selectedPolicy = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>{{ selectedPolicy.name }} - Policy Details</h2>
              <button @click="selectedPolicy = null" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <div class="detail-section">
                <h3>Coverage Details</h3>
                <div class="detail-grid">
                  <div class="detail-item">
                    <span class="label">Policy ID:</span>
                    <span class="value">{{ selectedPolicy.id }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Status:</span>
                    <span class="status-badge" :class="`status-${selectedPolicy.status}`">
                      {{ capitalize(selectedPolicy.status) }}
                    </span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Crop:</span>
                    <span class="value">{{ selectedPolicy.crop }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Coverage Type:</span>
                    <span class="value">{{ selectedPolicy.coverageType }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Coverage Amount:</span>
                    <span class="value highlight">${{ formatNumber(selectedPolicy.coverage) }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Premium (Annual):</span>
                    <span class="value">${{ formatNumber(selectedPolicy.premium) }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Valid From:</span>
                    <span class="value">{{ formatDate(selectedPolicy.startDate) }}</span>
                  </div>
                  <div class="detail-item">
                    <span class="label">Valid Until:</span>
                    <span class="value">{{ formatDate(selectedPolicy.endDate) }}</span>
                  </div>
                </div>
              </div>

              <div class="detail-section">
                <h3>Coverage Details</h3>
                <ul class="coverage-list">
                  <li v-for="(item, index) in selectedPolicy.coverageDetails" :key="index">
                    {{ item }}
                  </li>
                </ul>
              </div>

              <div class="detail-section">
                <h3>Terms & Conditions</h3>
                <div class="terms-text">
                  <p>{{ selectedPolicy.terms }}</p>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedPolicy = null" class="btn-secondary">Close</button>
              <button @click="downloadPolicy(selectedPolicy)" class="btn-primary">
                <Download size="16" />
                Download Policy
              </button>
            </div>
          </div>
        </div>

        <!-- File Claim Modal -->
        <div v-if="showClaimForm" class="modal-overlay" @click="showClaimForm = false">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>File Insurance Claim</h2>
              <button @click="showClaimForm = false" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="submitClaim" class="form">
                <div class="form-group">
                  <label>Policy</label>
                  <select v-model="claimForm.policyId" required class="form-control">
                    <option value="">Select a policy</option>
                    <option v-for="policy in policies" :key="policy.id" :value="policy.id">
                      {{ policy.name }} ({{ policy.id }})
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Damage Type</label>
                  <select v-model="claimForm.damageType" required class="form-control">
                    <option value="">Select damage type</option>
                    <option value="drought">Drought</option>
                    <option value="flood">Flood</option>
                    <option value="pest">Pest Infestation</option>
                    <option value="disease">Disease</option>
                    <option value="hail">Hail Damage</option>
                    <option value="frost">Frost/Cold</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Date of Loss</label>
                  <input v-model="claimForm.dateOfLoss" type="date" required class="form-control" />
                </div>

                <div class="form-group">
                  <label>Estimated Loss Amount ($)</label>
                  <input v-model.number="claimForm.amount" type="number" required placeholder="Enter amount" class="form-control" />
                </div>

                <div class="form-group">
                  <label>Description</label>
                  <textarea v-model="claimForm.description" required placeholder="Describe the damage and loss" class="form-control" rows="5"></textarea>
                </div>

                <div class="form-group">
                  <label>Attach Documents</label>
                  <input type="file" multiple class="form-control" />
                  <small>Upload photos, estimates, or supporting documents</small>
                </div>

                <div class="form-actions">
                  <button type="button" @click="showClaimForm = false" class="btn-secondary">Cancel</button>
                  <button type="submit" class="btn-primary">Submit Claim</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- New Policy Modal -->
        <div v-if="showNewPolicy" class="modal-overlay" @click="showNewPolicy = false">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Purchase New Policy</h2>
              <button @click="showNewPolicy = false" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="submitNewPolicy" class="form">
                <div class="form-group">
                  <label>Select Product</label>
                  <select v-model="policyForm.product" required class="form-control">
                    <option value="">Choose a product</option>
                    <option v-for="product in insuranceProducts" :key="product.id" :value="product.id">
                      {{ product.name }}
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Crop Type</label>
                  <select v-model="policyForm.crop" required class="form-control">
                    <option value="">Select crop</option>
                    <option value="rice">Rice</option>
                    <option value="wheat">Wheat</option>
                    <option value="corn">Corn</option>
                    <option value="soybean">Soybean</option>
                    <option value="vegetables">Vegetables</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Acreage (acres)</label>
                  <input v-model.number="policyForm.acreage" type="number" required placeholder="Enter acreage" class="form-control" />
                </div>

                <div class="form-group">
                  <label>Policy Start Date</label>
                  <input v-model="policyForm.startDate" type="date" required class="form-control" />
                </div>

                <div class="form-actions">
                  <button type="button" @click="showNewPolicy = false" class="btn-secondary">Cancel</button>
                  <button type="submit" class="btn-primary">Purchase Policy</button>
                </div>
              </form>
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
import {
  Plus, Shield, DollarSign, AlertCircle, CheckCircle, Eye, FileText, X, Download
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const selectedPolicy = ref(null)
const showClaimForm = ref(false)
const showNewPolicy = ref(false)
const filterStatus = ref('')

const claimForm = ref({
  policyId: '',
  damageType: '',
  dateOfLoss: '',
  amount: null,
  description: ''
})

const policyForm = ref({
  product: '',
  crop: '',
  acreage: null,
  startDate: ''
})

const mockPolicies = [
  {
    id: 'POL-001',
    name: 'Comprehensive Crop Cover',
    crop: 'Rice',
    coverage: 50000,
    premium: 1500,
    status: 'active',
    startDate: '2026-06-01',
    endDate: '2027-06-01',
    coverageType: 'Multi-risk',
    coverageDetails: [
      'Drought coverage up to 70% loss',
      'Flood damage protection',
      'Pest and disease coverage',
      'Accidental fire protection',
      'Crop failure recovery'
    ],
    terms: 'Policy is valid for one agricultural season. Claim settlement within 30 days of approval. Deductible: 10% of coverage amount.'
  },
  {
    id: 'POL-002',
    name: 'Monsoon Protection Plan',
    crop: 'Corn',
    coverage: 75000,
    premium: 2000,
    status: 'active',
    startDate: '2026-05-15',
    endDate: '2027-05-15',
    coverageType: 'Weather-based',
    coverageDetails: [
      'Heavy rainfall protection',
      'Excessive moisture damage',
      'Wind damage up to 80% coverage',
      'Waterlogging assistance',
      'Quick claim settlement'
    ],
    terms: 'Weather-based indemnity. Payouts based on rainfall/weather data. No requirement of loss assessment.'
  },
  {
    id: 'POL-003',
    name: 'Horticulture Protection',
    crop: 'Vegetables',
    coverage: 30000,
    premium: 900,
    status: 'pending',
    startDate: '2026-10-01',
    endDate: '2027-10-01',
    coverageType: 'Specialized',
    coverageDetails: [
      'Vegetable-specific disease coverage',
      'Post-harvest loss protection',
      'Market price support',
      'Organic certification protection'
    ],
    terms: 'Specialized coverage for horticulture. Additional documentation required for claims.'
  }
]

const mockClaims = [
  {
    id: 'CLM-001',
    policyId: 'POL-001',
    dateFile: '2026-08-15',
    amount: 15000,
    status: 'approved',
    damageType: 'Drought'
  },
  {
    id: 'CLM-002',
    policyId: 'POL-002',
    dateFile: '2026-08-20',
    amount: 25000,
    status: 'pending',
    damageType: 'Excessive Rainfall'
  }
]

const insuranceProducts = [
  {
    id: 1,
    name: 'Comprehensive Multi-Risk',
    coverage: 'Up to 70%',
    description: 'Complete protection against natural calamities',
    premium: 3,
    deductible: '10%',
    waitingPeriod: 'Immediate'
  },
  {
    id: 2,
    name: 'Weather-Based Indemnity',
    coverage: 'Up to 80%',
    description: 'Protection based on weather data and indices',
    premium: 2.5,
    deductible: 'No deductible',
    waitingPeriod: 'Immediate'
  },
  {
    id: 3,
    name: 'Named Peril Cover',
    coverage: 'Up to 60%',
    description: 'Coverage for specific named perils only',
    premium: 2,
    deductible: '15%',
    waitingPeriod: '15 days'
  },
  {
    id: 4,
    name: 'Livestock Insurance',
    coverage: 'Up to 90%',
    description: 'Protection for livestock and poultry',
    premium: 4,
    deductible: '5%',
    waitingPeriod: '30 days'
  }
]

const policies = ref(mockPolicies)
const claims = ref(mockClaims)

const filteredPolicies = computed(() => {
  if (!filterStatus.value) return policies.value
  return policies.value.filter(p => p.status === filterStatus.value)
})

const stats = computed(() => ({
  activePolicies: policies.value.filter(p => p.status === 'active').length,
  totalCoverage: policies.value.reduce((sum, p) => sum + p.coverage, 0),
  claimsFiled: claims.value.length,
  approvedClaims: claims.value
    .filter(c => c.status === 'approved')
    .reduce((sum, c) => sum + c.amount, 0)
}))

const fetchPolicies = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)
const formatDate = (date) => new Date(date).toLocaleDateString()
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)

const viewPolicyDetails = (policy) => { selectedPolicy.value = policy }
const fileClaim = (policy) => { 
  claimForm.value.policyId = policy.id
  showClaimForm.value = true
}
const viewClaimDetails = (claim) => { alert(`Claim details: ${claim.id}`) }
const downloadPolicy = (policy) => { alert(`Downloading policy ${policy.id}`) }
const purchasePolicy = (product) => {
  policyForm.value.product = product.id
  showNewPolicy.value = true
}
const submitClaim = () => {
  alert('Claim submitted successfully')
  showClaimForm.value = false
}
const submitNewPolicy = () => {
  alert('Policy purchase request submitted')
  showNewPolicy.value = false
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchPolicies() })
</script>

<style scoped>
.insurance-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.insurance-container {
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
  background: #8b5cf6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-new:hover {
  background: #7c3aed;
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
  border-top-color: #8b5cf6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.insurance-content {
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

.stat-icon.active { color: #8b5cf6; }
.stat-icon.coverage { color: #059669; }
.stat-icon.claims { color: #f59e0b; }
.stat-icon.approved { color: #3b82f6; }

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

.policies-section,
.products-section,
.claims-section {
  margin-bottom: 30px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2,
.products-section h2,
.claims-section h2 {
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

.policies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.policy-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
  transition: all 0.3s;
  display: flex;
  flex-direction: column;
}

.policy-card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
  border-color: #8b5cf6;
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

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.status-active { background: #e9d5ff; color: #6b21a8; }
.status-expired { background: #fee2e2; color: #991b1b; }
.status-pending { background: #fef3c7; color: #92400e; }

.card-content {
  padding: 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.policy-item {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}

.policy-item .label {
  color: #6b7280;
  font-weight: 600;
}

.policy-item .value {
  color: #1f2937;
  font-weight: 600;
}

.policy-item .value.highlight {
  color: #8b5cf6;
}

.card-footer {
  padding: 12px 16px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 8px;
}

.btn-action {
  flex: 1;
  padding: 10px;
  background: #f3f4f6;
  color: #4b5563;
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

.btn-action:hover {
  background: #e5e7eb;
}

.btn-action.btn-primary {
  background: #e9d5ff;
  color: #6b21a8;
  border-color: #8b5cf6;
}

.btn-action.btn-primary:hover {
  background: #8b5cf6;
  color: white;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.product-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
  transition: all 0.3s;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
  border-color: #8b5cf6;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.product-header h3 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.badge {
  background: #e9d5ff;
  color: #6b21a8;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.product-description {
  font-size: 13px;
  color: #6b7280;
  margin: 0 0 16px 0;
  line-height: 1.5;
}

.product-features {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;
}

.feature {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}

.feature .label {
  color: #6b7280;
  font-weight: 600;
}

.feature .value {
  color: #1f2937;
  font-weight: 600;
}

.btn-purchase {
  width: 100%;
  padding: 10px;
  background: #8b5cf6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-purchase:hover {
  background: #7c3aed;
}

.claims-table-wrapper {
  overflow-x: auto;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.claims-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.claims-table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.claims-table th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 12px;
}

.claims-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
}

.claims-table tbody tr:hover {
  background: #f9fafb;
}

.table-row td {
  padding: 16px;
  color: #1f2937;
}

.claim-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.claim-approved { background: #d1fae5; color: #065f46; }
.claim-pending { background: #fef3c7; color: #92400e; }
.claim-rejected { background: #fee2e2; color: #991b1b; }

.td-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  background: none;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 6px 8px;
  cursor: pointer;
  color: #6b7280;
  transition: all 0.2s;
}

.action-btn:hover {
  border-color: #8b5cf6;
  color: #8b5cf6;
  background: #f3e8ff;
}

.empty-state {
  padding: 60px 20px;
  text-align: center;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
  overflow-y: auto;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 700px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  margin: 20px auto;
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  background: white;
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

.detail-section {
  margin-bottom: 24px;
}

.detail-section h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 16px 0;
  padding-bottom: 12px;
  border-bottom: 2px solid #e5e7eb;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item .label {
  font-size: 11px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.detail-item .value {
  font-size: 13px;
  color: #1f2937;
  font-weight: 600;
}

.detail-item .value.highlight {
  color: #8b5cf6;
}

.coverage-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.coverage-list li {
  padding: 10px;
  background: #f9fafb;
  border-left: 3px solid #8b5cf6;
  margin-bottom: 8px;
  font-size: 13px;
  color: #4b5563;
}

.terms-text {
  background: #f9fafb;
  padding: 16px;
  border-radius: 8px;
  font-size: 13px;
  color: #4b5563;
  line-height: 1.6;
}

.terms-text p {
  margin: 0;
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

.form-control:focus {
  outline: none;
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.form-group small {
  display: block;
  margin-top: 4px;
  font-size: 12px;
  color: #6b7280;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.btn-primary,
.btn-secondary {
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: #8b5cf6;
  color: white;
}

.btn-primary:hover {
  background: #7c3aed;
}

.btn-secondary {
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  position: sticky;
  bottom: 0;
  background: white;
}

@media (max-width: 768px) {
  .insurance-container {
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

  .policies-grid,
  .products-grid {
    grid-template-columns: 1fr;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .modal-content {
    width: 95%;
  }
}
</style>
