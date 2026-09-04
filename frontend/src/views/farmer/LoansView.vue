<template>
  <div class="loans-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="loans-container">
      <div class="page-header">
        <div class="header-content">
          <h1>Loans & Financing</h1>
          <p>Access agricultural loans and financial assistance programs</p>
        </div>
        <button @click="showNewApplication = true" class="btn-new">
          <Plus size="16" />
          <span>Apply for Loan</span>
        </button>
      </div>

      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading loans...</p>
      </div>

      <div v-if="!loading && !error" class="loans-content">
        <!-- Stats Cards -->
        <div class="stats-grid">
          <div class="stat-card">
            <DollarSign size="20" class="stat-icon active" />
            <div>
              <span class="stat-label">Active Loans</span>
              <span class="stat-value">{{ stats.active }}</span>
            </div>
          </div>
          <div class="stat-card">
            <TrendingUp size="20" class="stat-icon borrowed" />
            <div>
              <span class="stat-label">Total Borrowed</span>
              <span class="stat-value">${{ formatNumber(stats.totalBorrowed) }}</span>
            </div>
          </div>
          <div class="stat-card">
            <CheckCircle size="20" class="stat-icon completed" />
            <div>
              <span class="stat-label">Completed Loans</span>
              <span class="stat-value">{{ stats.completed }}</span>
            </div>
          </div>
          <div class="stat-card">
            <AlertCircle size="20" class="stat-icon pending" />
            <div>
              <span class="stat-label">Pending Applications</span>
              <span class="stat-value">{{ stats.pending }}</span>
            </div>
          </div>
        </div>

        <!-- Loans Table -->
        <div class="table-section">
          <div class="table-header">
            <h3>Your Loans</h3>
            <select v-model="filterStatus" class="filter-select">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="pending">Pending</option>
              <option value="completed">Completed</option>
            </select>
          </div>

          <div v-if="filteredLoans.length > 0" class="table-wrapper">
            <table class="loans-table">
              <thead>
                <tr>
                  <th>Loan ID</th>
                  <th>Type</th>
                  <th>Amount</th>
                  <th>Interest Rate</th>
                  <th>Term</th>
                  <th>Status</th>
                  <th>Next Payment</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="loan in filteredLoans" :key="loan.id" class="table-row">
                  <td class="td-id">{{ loan.id }}</td>
                  <td class="td-type">{{ loan.type }}</td>
                  <td class="td-amount">${{ formatNumber(loan.amount) }}</td>
                  <td class="td-rate">{{ loan.interestRate }}%</td>
                  <td class="td-term">{{ loan.term }} months</td>
                  <td class="td-status">
                    <span class="status-badge" :class="`status-${loan.status}`">
                      {{ capitalize(loan.status) }}
                    </span>
                  </td>
                  <td class="td-date">{{ formatDate(loan.nextPayment) }}</td>
                  <td class="td-actions">
                    <button @click="viewDetails(loan)" class="action-btn">
                      <Eye size="16" />
                    </button>
                    <button @click="makePayment(loan)" class="action-btn">
                      <CreditCard size="16" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="empty-state">
            <DollarSign size="48" class="empty-icon" />
            <p>No loans yet</p>
          </div>
        </div>

        <!-- Available Loan Products -->
        <div class="products-section">
          <h2>Available Loan Products</h2>
          <div class="products-grid">
            <div v-for="product in loanProducts" :key="product.id" class="product-card">
              <div class="product-header">
                <h3>{{ product.name }}</h3>
                <span class="badge">{{ product.maxAmount }}</span>
              </div>
              <p class="product-description">{{ product.description }}</p>
              <div class="product-features">
                <div class="feature">
                  <span class="label">Rate:</span>
                  <span class="value">{{ product.rate }}%</span>
                </div>
                <div class="feature">
                  <span class="label">Term:</span>
                  <span class="value">{{ product.term }}</span>
                </div>
                <div class="feature">
                  <span class="label">Min Amount:</span>
                  <span class="value">${{ formatNumber(product.minAmount) }}</span>
                </div>
              </div>
              <button @click="applyForProduct(product)" class="btn-apply-product">
                Apply Now
              </button>
            </div>
          </div>
        </div>

        <!-- Loan Details Modal -->
        <div v-if="selectedLoan" class="modal-overlay" @click="selectedLoan = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>{{ selectedLoan.id }} - Loan Details</h2>
              <button @click="selectedLoan = null" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="label">Loan Type:</span>
                  <span class="value">{{ selectedLoan.type }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Principal Amount:</span>
                  <span class="value">${{ formatNumber(selectedLoan.amount) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Interest Rate:</span>
                  <span class="value">{{ selectedLoan.interestRate }}% per annum</span>
                </div>
                <div class="detail-item">
                  <span class="label">Loan Term:</span>
                  <span class="value">{{ selectedLoan.term }} months</span>
                </div>
                <div class="detail-item">
                  <span class="label">Disbursal Date:</span>
                  <span class="value">{{ formatDate(selectedLoan.disbursalDate) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Maturity Date:</span>
                  <span class="value">{{ formatDate(selectedLoan.maturityDate) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Outstanding Amount:</span>
                  <span class="value highlight">${{ formatNumber(selectedLoan.outstanding) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Monthly EMI:</span>
                  <span class="value">${{ formatNumber(selectedLoan.emi) }}</span>
                </div>
              </div>

              <div class="payment-history">
                <h3>Payment History</h3>
                <table class="history-table">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Amount</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="payment in selectedLoan.payments" :key="payment.date">
                      <td>{{ formatDate(payment.date) }}</td>
                      <td>${{ formatNumber(payment.amount) }}</td>
                      <td>
                        <span class="payment-badge" :class="`payment-${payment.status}`">
                          {{ capitalize(payment.status) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedLoan = null" class="btn-secondary">Close</button>
              <button @click="downloadStatement(selectedLoan)" class="btn-primary">
                <Download size="16" />
                Download Statement
              </button>
            </div>
          </div>
        </div>

        <!-- Application Modal -->
        <div v-if="showNewApplication" class="modal-overlay" @click="showNewApplication = false">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Loan Application</h2>
              <button @click="showNewApplication = false" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="submitApplication" class="form">
                <div class="form-group">
                  <label>Loan Product</label>
                  <select v-model="application.product" required class="form-control">
                    <option value="">Select a product</option>
                    <option v-for="product in loanProducts" :key="product.id" :value="product.id">
                      {{ product.name }}
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Loan Amount ($)</label>
                  <input v-model.number="application.amount" type="number" required placeholder="Enter amount" class="form-control" />
                </div>

                <div class="form-group">
                  <label>Intended Use</label>
                  <textarea v-model="application.purpose" required placeholder="Describe how you'll use the loan" class="form-control" rows="4"></textarea>
                </div>

                <div class="form-group">
                  <label>Preferred Tenure (months)</label>
                  <input v-model.number="application.tenure" type="number" required placeholder="Number of months" class="form-control" />
                </div>

                <div class="form-actions">
                  <button type="button" @click="showNewApplication = false" class="btn-secondary">Cancel</button>
                  <button type="submit" class="btn-primary">Submit Application</button>
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
  Plus, DollarSign, TrendingUp, CheckCircle, AlertCircle, Eye, CreditCard, X, Download
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const showNewApplication = ref(false)
const selectedLoan = ref(null)
const filterStatus = ref('')

const application = ref({
  product: '',
  amount: null,
  purpose: '',
  tenure: null
})

const mockLoans = [
  {
    id: 'LN-001',
    type: 'Agricultural Production Loan',
    amount: 10000,
    interestRate: 8.5,
    term: 12,
    status: 'active',
    disbursalDate: '2026-06-01',
    maturityDate: '2027-06-01',
    outstanding: 6200,
    emi: 867,
    nextPayment: '2026-10-05',
    payments: [
      { date: '2026-07-05', amount: 867, status: 'paid' },
      { date: '2026-08-05', amount: 867, status: 'paid' },
      { date: '2026-09-05', amount: 867, status: 'paid' }
    ]
  },
  {
    id: 'LN-002',
    type: 'Equipment Purchase Loan',
    amount: 25000,
    interestRate: 9.25,
    term: 36,
    status: 'active',
    disbursalDate: '2026-04-01',
    maturityDate: '2029-04-01',
    outstanding: 21800,
    emi: 750,
    nextPayment: '2026-10-01',
    payments: [
      { date: '2026-05-01', amount: 750, status: 'paid' },
      { date: '2026-06-01', amount: 750, status: 'paid' },
      { date: '2026-07-01', amount: 750, status: 'paid' }
    ]
  },
  {
    id: 'LN-003',
    type: 'Seasonal Credit Loan',
    amount: 5000,
    interestRate: 7.5,
    term: 6,
    status: 'completed',
    disbursalDate: '2026-01-01',
    maturityDate: '2026-07-01',
    outstanding: 0,
    emi: 850,
    nextPayment: null,
    payments: [
      { date: '2026-02-01', amount: 850, status: 'paid' },
      { date: '2026-03-01', amount: 850, status: 'paid' },
      { date: '2026-04-01', amount: 850, status: 'paid' }
    ]
  }
]

const loanProducts = [
  {
    id: 1,
    name: 'Production Loan',
    description: 'For seeds, fertilizers, and seasonal input costs',
    rate: '8.5%',
    term: '6-12 months',
    minAmount: 1000,
    maxAmount: 'Up to $50K'
  },
  {
    id: 2,
    name: 'Equipment Loan',
    description: 'For purchasing farming machinery and equipment',
    rate: '9.25%',
    term: '24-60 months',
    minAmount: 5000,
    maxAmount: 'Up to $200K'
  },
  {
    id: 3,
    name: 'Infrastructure Loan',
    description: 'For building irrigation systems and structures',
    rate: '8.75%',
    term: '36-84 months',
    minAmount: 10000,
    maxAmount: 'Up to $500K'
  },
  {
    id: 4,
    name: 'Livestock Loan',
    description: 'For breeding animals and livestock development',
    rate: '8.0%',
    term: '12-48 months',
    minAmount: 2000,
    maxAmount: 'Up to $100K'
  }
]

const loans = ref(mockLoans)

const filteredLoans = computed(() => {
  if (!filterStatus.value) return loans.value
  return loans.value.filter(l => l.status === filterStatus.value)
})

const stats = computed(() => ({
  active: loans.value.filter(l => l.status === 'active').length,
  totalBorrowed: loans.value.reduce((sum, l) => sum + l.amount, 0),
  completed: loans.value.filter(l => l.status === 'completed').length,
  pending: 1
}))

const fetchLoans = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)
const formatDate = (date) => new Date(date).toLocaleDateString()
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)

const viewDetails = (loan) => { selectedLoan.value = loan }
const makePayment = (loan) => { alert(`Make payment for ${loan.id}`) }
const downloadStatement = (loan) => { alert(`Statement downloaded for ${loan.id}`) }
const applyForProduct = (product) => {
  application.value.product = product.id
  showNewApplication.value = true
}
const submitApplication = () => {
  alert('Loan application submitted successfully')
  showNewApplication.value = false
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchLoans() })
</script>

<style scoped>
.loans-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.loans-container {
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
  background: #059669;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-new:hover {
  background: #047857;
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
  border-top-color: #059669;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loans-content {
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

.stat-icon.active { color: #059669; }
.stat-icon.borrowed { color: #3b82f6; }
.stat-icon.completed { color: #8b5cf6; }
.stat-icon.pending { color: #f59e0b; }

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

.table-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  margin-bottom: 30px;
}

.table-header {
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-header h3 {
  font-size: 16px;
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

.table-wrapper {
  overflow-x: auto;
}

.loans-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.loans-table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.loans-table th {
  padding: 16px;
  text-align: left;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 12px;
}

.loans-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
}

.loans-table tbody tr:hover {
  background: #f9fafb;
}

.table-row td {
  padding: 16px;
  color: #1f2937;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.status-active { background: #d1fae5; color: #065f46; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-completed { background: #e9d5ff; color: #6b21a8; }

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
  display: flex;
  align-items: center;
}

.action-btn:hover {
  border-color: #059669;
  color: #059669;
  background: #ecfdf5;
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

.products-section {
  margin-top: 30px;
}

.products-section h2 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 20px 0;
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
  border-color: #059669;
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
  background: #d1fae5;
  color: #065f46;
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

.btn-apply-product {
  width: 100%;
  padding: 10px;
  background: #059669;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-apply-product:hover {
  background: #047857;
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
  max-width: 700px;
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

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 24px;
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
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
}

.detail-item .value {
  font-size: 14px;
  color: #1f2937;
  font-weight: 600;
}

.detail-item .value.highlight {
  color: #059669;
}

.payment-history {
  margin-bottom: 20px;
}

.payment-history h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 12px 0;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.history-table thead {
  background: #f3f4f6;
}

.history-table th {
  padding: 10px;
  text-align: left;
  font-weight: 600;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
}

.history-table td {
  padding: 10px;
  border-bottom: 1px solid #e5e7eb;
  color: #1f2937;
}

.payment-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.payment-paid { background: #d1fae5; color: #065f46; }
.payment-pending { background: #fef3c7; color: #92400e; }

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
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
  background: #059669;
  color: white;
}

.btn-primary:hover {
  background: #047857;
}

.btn-secondary {
  background: #f3f4f6;
  color: #4b5563;
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
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

@media (max-width: 768px) {
  .loans-container {
    margin-left: 0;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .products-grid {
    grid-template-columns: 1fr;
  }
}
</style>
