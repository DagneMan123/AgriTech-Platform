<template>
  <div class="farmer-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="farmer-page">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Loan Applications</h1>
          <p>Apply for agricultural loans and manage your repayments</p>
        </div>
        <button class="btn-primary btn-large" @click="openApplyLoanDialog">
          <i class="fas fa-plus"></i> Apply for Loan
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon total">
            <i class="fas fa-file-contract"></i>
          </div>
          <div class="stat-content">
            <h3>Total Applications</h3>
            <p class="stat-value">{{ loans.length }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon pending">
            <i class="fas fa-hourglass-start"></i>
          </div>
          <div class="stat-content">
            <h3>Pending Review</h3>
            <p class="stat-value">{{ pendingCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon approved">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-content">
            <h3>Approved</h3>
            <p class="stat-value">{{ approvedCount }}</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon active">
            <i class="fas fa-money-check-alt"></i>
          </div>
          <div class="stat-content">
            <h3>Active Loans</h3>
            <p class="stat-value">{{ activeCount }}</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="controls-section">
        <div class="filter-group">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search loans..."
            class="search-input"
          />
          <select v-model="statusFilter" class="status-select">
            <option value="">All Status</option>
            <option value="pending">Pending Review</option>
            <option value="approved">Approved</option>
            <option value="disbursed">Disbursed</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
      </div>

      <!-- Loans List -->
      <div class="loans-section">
        <div v-if="loading" class="loading-state">
          <p><i class="fas fa-spinner fa-spin"></i> Loading loans...</p>
        </div>

        <div v-else-if="filteredLoans.length === 0" class="empty-state">
          <i class="fas fa-inbox"></i>
          <h3>No loans found</h3>
          <p>{{ loans.length === 0 ? 'Apply for your first loan' : 'No loans match your filters' }}</p>
        </div>

        <div v-else class="loans-list">
          <div v-for="loan in filteredLoans" :key="loan.id" class="loan-card">
            <div class="loan-header">
              <div class="loan-title">
                <h3>{{ loan.purpose || 'Loan Application' }}</h3>
                <p class="loan-number">{{ loan.loan_number }}</p>
              </div>
              <div class="header-right">
                <span class="status-badge" :class="`status-${loan.status}`">
                  {{ formatStatus(loan.status) }}
                </span>
              </div>
            </div>

            <div class="loan-body">
              <div class="loan-details-grid">
                <div class="detail-item">
                  <span class="label">Loan Amount</span>
                  <span class="value">ETB {{ formatNumber(loan.amount) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Duration</span>
                  <span class="value">{{ loan.duration_months }} months</span>
                </div>
                <div class="detail-item">
                  <span class="label">Interest Rate</span>
                  <span class="value">{{ loan.interest_rate }}%</span>
                </div>
                <div class="detail-item">
                  <span class="label">Applied Date</span>
                  <span class="value">{{ formatDate(loan.created_at) }}</span>
                </div>
              </div>

              <div v-if="loan.status === 'active' || loan.status === 'disbursed'" class="repayment-info">
                <div class="repayment-label">Repayment Progress</div>
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: getProgressPercentage(loan) + '%' }"></div>
                </div>
                <div class="progress-text">
                  ETB {{ formatNumber(loan.amount_paid || 0) }} / ETB {{ formatNumber(loan.amount) }}
                </div>
              </div>
            </div>

            <div class="loan-footer">
              <button class="btn-small btn-view" @click="viewLoanDetails(loan)">
                <i class="fas fa-eye"></i> View Details
              </button>
              <button v-if="loan.status === 'active'" class="btn-small btn-repay" @click="recordRepayment(loan)">
                <i class="fas fa-money-bill-wave"></i> Record Repayment
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Apply Loan Modal -->
      <div v-if="showApplyDialog" class="modal-overlay" @click="closeApplyLoanDialog">
        <div class="modal-dialog" @click.stop>
          <div class="modal-header">
            <h2>Apply for Agricultural Loan</h2>
            <button class="close-btn" @click="closeApplyLoanDialog">&times;</button>
          </div>

          <div class="modal-content">
            <form @submit.prevent="submitLoanApplication">
              <!-- Loan Amount -->
              <div class="form-group">
                <label for="loan-amount">Loan Amount (ETB) *</label>
                <div class="input-with-currency">
                  <span class="currency">ETB</span>
                  <input
                    id="loan-amount"
                    v-model.number="loanForm.amount"
                    type="number"
                    placeholder="50000"
                    step="100"
                    min="1000"
                    required
                  />
                </div>
                <span v-if="formErrors.amount" class="error-text">{{ formErrors.amount }}</span>
              </div>

              <!-- Loan Purpose -->
              <div class="form-group">
                <label for="loan-purpose">Loan Purpose *</label>
                <select id="loan-purpose" v-model="loanForm.purpose" required>
                  <option value="">Select Purpose</option>
                  <option value="land_purchase">Land Purchase</option>
                  <option value="equipment_purchase">Equipment Purchase</option>
                  <option value="seeds_fertilizer">Seeds & Fertilizer</option>
                  <option value="livestock">Livestock Purchase</option>
                  <option value="farm_infrastructure">Farm Infrastructure</option>
                  <option value="working_capital">Working Capital</option>
                  <option value="irrigation_system">Irrigation System</option>
                  <option value="other">Other</option>
                </select>
                <span v-if="formErrors.purpose" class="error-text">{{ formErrors.purpose }}</span>
              </div>

              <!-- Loan Duration -->
              <div class="form-row">
                <div class="form-group">
                  <label for="loan-duration">Duration (months) *</label>
                  <input
                    id="loan-duration"
                    v-model.number="loanForm.duration_months"
                    type="number"
                    placeholder="12"
                    min="3"
                    max="60"
                    required
                  />
                  <span v-if="formErrors.duration_months" class="error-text">{{ formErrors.duration_months }}</span>
                </div>

                <div class="form-group">
                  <label for="loan-rate">Interest Rate (%) - Auto</label>
                  <input
                    id="loan-rate"
                    v-model.number="loanForm.interest_rate"
                    type="number"
                    placeholder="12"
                    step="0.1"
                    disabled
                  />
                </div>
              </div>

              <!-- Description -->
              <div class="form-group">
                <label for="loan-description">Detailed Description *</label>
                <textarea
                  id="loan-description"
                  v-model="loanForm.description"
                  placeholder="Explain how you plan to use this loan and its impact on your farm..."
                  rows="4"
                  required
                ></textarea>
                <span v-if="formErrors.description" class="error-text">{{ formErrors.description }}</span>
              </div>

              <!-- Form Actions -->
              <div class="form-actions">
                <button type="button" class="btn-secondary" @click="closeApplyLoanDialog">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="submitting">
                  {{ submitting ? 'Submitting...' : 'Apply for Loan' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Loan Details Modal -->
      <div v-if="showDetailsModal" class="modal-overlay" @click="closeDetailsModal">
        <div class="modal-dialog modal-large" @click.stop>
          <div class="modal-header">
            <h2>Loan Details - {{ selectedLoan?.loan_number }}</h2>
            <button class="close-btn" @click="closeDetailsModal">&times;</button>
          </div>

          <div class="modal-content">
            <div class="details-grid">
              <div class="detail-section">
                <h3>Loan Information</h3>
                <div class="detail-row">
                  <span class="label">Loan Number:</span>
                  <span class="value">{{ selectedLoan?.loan_number }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Amount:</span>
                  <span class="value">ETB {{ formatNumber(selectedLoan?.amount) }}</span>
                </div>
                <div class="detail-row">
                  <span class="label">Duration:</span>
                  <span class="value">{{ selectedLoan?.duration_months }} months</span>
                </div>
                <div class="detail-row">
                  <span class="label">Interest Rate:</span>
                  <span class="value">{{ selectedLoan?.interest_rate }}%</span>
                </div>
              </div>

              <div class="detail-section">
                <h3>Status Information</h3>
                <div class="detail-row">
                  <span class="label">Status:</span>
                  <span class="value status-badge" :class="`status-${selectedLoan?.status}`">
                    {{ formatStatus(selectedLoan?.status) }}
                  </span>
                </div>
                <div class="detail-row">
                  <span class="label">Applied Date:</span>
                  <span class="value">{{ formatDate(selectedLoan?.created_at) }}</span>
                </div>
                <div v-if="selectedLoan?.approved_at" class="detail-row">
                  <span class="label">Approved Date:</span>
                  <span class="value">{{ formatDate(selectedLoan.approved_at) }}</span>
                </div>
                <div v-if="selectedLoan?.disbursed_at" class="detail-row">
                  <span class="label">Disbursed Date:</span>
                  <span class="value">{{ formatDate(selectedLoan.disbursed_at) }}</span>
                </div>
              </div>

              <div class="detail-section full-width">
                <h3>Purpose & Description</h3>
                <p class="description-text">{{ selectedLoan?.description }}</p>
              </div>

              <div v-if="selectedLoan?.status === 'active' || selectedLoan?.status === 'disbursed'" class="detail-section full-width">
                <h3>Repayment Progress</h3>
                <div class="progress-info">
                  <div class="progress-bar-large">
                    <div class="progress-fill" :style="{ width: getProgressPercentage(selectedLoan) + '%' }"></div>
                  </div>
                  <div class="progress-stats">
                    <div class="stat">
                      <span class="stat-label">Amount Paid:</span>
                      <span class="stat-value">ETB {{ formatNumber(selectedLoan?.amount_paid || 0) }}</span>
                    </div>
                    <div class="stat">
                      <span class="stat-label">Remaining Balance:</span>
                      <span class="stat-value">ETB {{ formatNumber(selectedLoan?.remaining_balance || 0) }}</span>
                    </div>
                    <div class="stat">
                      <span class="stat-label">Total Amount:</span>
                      <span class="stat-value">ETB {{ formatNumber(selectedLoan?.amount) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-actions">
              <button class="btn-secondary" @click="closeDetailsModal">Close</button>
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
const loans = ref([])
const loading = ref(true)
const searchQuery = ref('')
const statusFilter = ref('')
const showApplyDialog = ref(false)
const showDetailsModal = ref(false)
const selectedLoan = ref(null)
const submitting = ref(false)
const formErrors = ref({})

// Loan Form
const loanForm = ref({
  amount: '',
  purpose: '',
  duration_months: 12,
  interest_rate: 12,
  description: '',
})

// Computed
const filteredLoans = computed(() => {
  return loans.value.filter(loan => {
    const matchesSearch =
      loan.purpose?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      loan.loan_number?.includes(searchQuery.value) ||
      loan.description?.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesStatus = !statusFilter.value || loan.status === statusFilter.value

    return matchesSearch && matchesStatus
  })
})

const pendingCount = computed(() => loans.value.filter(l => l.status === 'pending').length)
const approvedCount = computed(() => loans.value.filter(l => l.status === 'approved').length)
const activeCount = computed(() => loans.value.filter(l => l.status === 'active' || l.status === 'disbursed').length)

// Lifecycle
onMounted(async () => {
  await fetchLoans()
})

// API Functions
const fetchLoans = async () => {
  try {
    loading.value = true
    const res = await fetch('/api/farmer/loans', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })

    if (!res.ok) {
      loans.value = []
      return
    }

    const data = await res.json()
    loans.value = data.data || data || []
  } catch (error) {
    console.error('Error fetching loans:', error)
    loans.value = []
  } finally {
    loading.value = false
  }
}

// Modal Functions
const openApplyLoanDialog = () => {
  resetLoanForm()
  showApplyDialog.value = true
}

const closeApplyLoanDialog = () => {
  showApplyDialog.value = false
  resetLoanForm()
}

const resetLoanForm = () => {
  loanForm.value = {
    amount: '',
    purpose: '',
    duration_months: 12,
    interest_rate: 12,
    description: '',
  }
  formErrors.value = {}
}

const viewLoanDetails = (loan) => {
  selectedLoan.value = loan
  showDetailsModal.value = true
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedLoan.value = null
}

const recordRepayment = (loan) => {
  // TODO: Implement repayment recording
  alert('Repayment feature coming soon')
}

// Form Submission
const submitLoanApplication = async () => {
  try {
    submitting.value = true
    formErrors.value = {}

    const payload = {
      amount: loanForm.value.amount,
      purpose: loanForm.value.purpose,
      duration_months: loanForm.value.duration_months,
      description: loanForm.value.description,
    }

    const response = await fetch('/api/farmer/loans', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    })

    const data = await response.json()

    if (!response.ok) {
      formErrors.value = data.errors || { general: data.message || 'Error occurred' }
      return
    }

    showApplyDialog.value = false
    resetLoanForm()
    await fetchLoans()
  } catch (error) {
    console.error('Error:', error)
    formErrors.value = { general: 'An error occurred' }
  } finally {
    submitting.value = false
  }
}

// Utility Functions
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US').format(num || 0)
}

const formatStatus = (status) => {
  const statuses = {
    'pending': 'Pending Review',
    'approved': 'Approved',
    'disbursed': 'Disbursed',
    'active': 'Active',
    'completed': 'Completed',
    'rejected': 'Rejected',
    'defaulted': 'Defaulted',
  }
  return statuses[status] || status
}

const getProgressPercentage = (loan) => {
  if (loan.amount > 0) {
    return ((loan.amount_paid || 0) / loan.amount) * 100
  }
  return 0
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.farmer-layout { display: flex; height: 100vh; }
.farmer-page { margin-left: 260px; flex: 1; overflow-y: auto; background-color: #f5f5f5; padding: 20px; }

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  gap: 20px;
}

.header-content h1 {
  font-size: 32px;
  font-weight: bold;
  color: #333;
  margin: 0 0 5px 0;
}

.header-content p {
  color: #666;
  margin: 0;
}

.btn-large {
  padding: 12px 24px;
  font-size: 16px;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  gap: 15px;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.stat-card:hover { transform: translateY(-2px); }

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.stat-icon.total { background-color: #10b981; }
.stat-icon.pending { background-color: #f59e0b; }
.stat-icon.approved { background-color: #8b5cf6; }
.stat-icon.active { background-color: #3b82f6; }

.stat-content h3 {
  margin: 0;
  font-size: 12px;
  color: #666;
  text-transform: uppercase;
  font-weight: 600;
}

.stat-value {
  margin: 5px 0 0 0;
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

/* Controls */
.controls-section {
  background: white;
  border-radius: 8px;
  padding: 15px 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-group {
  display: flex;
  gap: 15px;
  align-items: center;
}

.search-input,
.status-select {
  padding: 10px 15px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.search-input:focus,
.status-select:focus {
  outline: none;
  border-color: #10b981;
}

.search-input { flex: 1; min-width: 250px; }

/* Loans Section */
.loans-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.empty-state i {
  font-size: 48px;
  color: #d1d5db;
  margin-bottom: 15px;
  display: block;
}

.empty-state h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

/* Loans List */
.loans-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.loan-card {
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  transition: all 0.3s;
}

.loan-card:hover {
  border-color: #10b981;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
  transform: translateY(-2px);
}

.loan-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
  gap: 10px;
}

.loan-title h3 {
  margin: 0 0 5px 0;
  color: #333;
  font-size: 18px;
}

.loan-number {
  font-size: 12px;
  color: #999;
  margin: 0;
  font-weight: 600;
}

.header-right {
  display: flex;
  gap: 8px;
  align-items: center;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending { background-color: #fef3c7; color: #92400e; }
.status-badge.status-approved { background-color: #dbeafe; color: #1e40af; }
.status-badge.status-disbursed { background-color: #f3e8ff; color: #6b21a8; }
.status-badge.status-active { background-color: #d1fae5; color: #065f46; }
.status-badge.status-completed { background-color: #d1fae5; color: #065f46; }
.status-badge.status-rejected { background-color: #fee2e2; color: #991b1b; }

.loan-body {
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e5e7eb;
}

.loan-details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-bottom: 15px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.detail-item .label {
  font-size: 12px;
  color: #666;
  text-transform: uppercase;
  font-weight: 600;
}

.detail-item .value {
  font-size: 14px;
  color: #333;
  font-weight: 600;
}

.repayment-info {
  background: #f0fdf4;
  padding: 12px;
  border-radius: 4px;
}

.repayment-label {
  font-size: 12px;
  font-weight: 600;
  color: #065f46;
  margin-bottom: 8px;
}

.progress-bar {
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #059669);
  transition: width 0.3s;
}

.progress-text {
  font-size: 12px;
  color: #065f46;
  font-weight: 600;
}

.loan-footer {
  display: flex;
  gap: 8px;
}

.btn-small {
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  min-width: 80px;
}

.btn-view {
  background-color: #8b5cf6;
  color: white;
}

.btn-view:hover {
  background-color: #7c3aed;
}

.btn-repay {
  background-color: #10b981;
  color: white;
}

.btn-repay:hover {
  background-color: #059669;
}

/* Buttons */
.btn-primary {
  background-color: #10b981;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover:not(:disabled) {
  background-color: #059669;
}

.btn-primary:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

.btn-secondary {
  background-color: #6b7280;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-secondary:hover {
  background-color: #4b5563;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-dialog {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 650px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-dialog.modal-large {
  max-width: 800px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.modal-header h2 {
  margin: 0;
  color: #333;
  font-size: 20px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: #333;
}

.modal-content {
  padding: 25px;
}

/* Form */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
  font-family: inherit;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-group input:disabled {
  background-color: #f3f4f6;
  color: #9ca3af;
}

.form-group textarea {
  resize: vertical;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.input-with-currency {
  display: flex;
  align-items: center;
}

.currency {
  padding: 10px 12px;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-right: none;
  border-radius: 4px 0 0 4px;
  font-weight: 600;
  color: #333;
}

.input-with-currency input {
  border-radius: 0 4px 4px 0;
  margin: 0;
}

.error-text {
  display: block;
  color: #ef4444;
  font-size: 12px;
  margin-top: 5px;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 25px;
  padding-top: 15px;
  border-top: 1px solid #e5e7eb;
}

/* Details */
.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 25px;
}

.detail-section {
  background: #f9fafb;
  padding: 15px;
  border-radius: 6px;
}

.detail-section.full-width {
  grid-column: 1 / -1;
}

.detail-section h3 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
  text-transform: uppercase;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row .label {
  color: #666;
  font-weight: 500;
  font-size: 13px;
}

.detail-row .value {
  color: #333;
  font-weight: 600;
  text-align: right;
}

.description-text {
  color: #555;
  line-height: 1.6;
  margin: 0;
}

.progress-info {
  background: white;
  padding: 15px;
  border-radius: 4px;
}

.progress-bar-large {
  height: 12px;
  background: #e5e7eb;
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 15px;
}

.progress-bar-large .progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #059669);
  transition: width 0.3s;
}

.progress-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 15px;
}

.stat {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.stat-label {
  font-size: 12px;
  color: #666;
  font-weight: 600;
  text-transform: uppercase;
}

.stat-value {
  font-size: 14px;
  color: #10b981;
  font-weight: 700;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  padding-top: 15px;
  border-top: 1px solid #e5e7eb;
}

/* Responsive */
@media (max-width: 768px) {
  .farmer-page { margin-left: 0; }
  .page-header { flex-direction: column; align-items: stretch; }
  .btn-large { width: 100%; text-align: center; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .filter-group { flex-direction: column; }
  .search-input { width: 100%; }
  .form-row { grid-template-columns: 1fr; }
  .modal-dialog { width: 95%; max-height: 95vh; }
  .progress-stats { grid-template-columns: 1fr; }
  .loan-details-grid { grid-template-columns: 1fr; }
}
</style>
