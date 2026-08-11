<template>
  <div class="financial-layout">
    <FinancialSidebar @logout="handleLogout" />
    <div class="financial-dashboard">
      <!-- Header -->
      <div class="dashboard-header">
        <h1>Financial Institution Dashboard</h1>
        <p>Manage loans, insurance, and financial operations</p>
      </div>

      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="card-icon loans">
            <i class="fas fa-file-contract"></i>
          </div>
          <div class="card-content">
            <h3>Total Loans</h3>
            <p class="card-value">{{ dashboard?.summary.total_loans || 0 }}</p>
            <p class="card-sub">All portfolios</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon pending">
            <i class="fas fa-hourglass-half"></i>
          </div>
          <div class="card-content">
            <h3>Pending Applications</h3>
            <p class="card-value">{{ dashboard?.summary.pending_applications || 0 }}</p>
            <p class="card-sub">Under review</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon approved">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="card-content">
            <h3>Approved</h3>
            <p class="card-value">{{ dashboard?.summary.approved_loans || 0 }}</p>
            <p class="card-sub">Total approved</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon disbursed">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="card-content">
            <h3>Disbursed Amount</h3>
            <p class="card-value">${{ formatNumber(dashboard?.summary.total_disbursed) }}</p>
            <p class="card-sub">Loan amount</p>
          </div>
        </div>

        <div class="summary-card">
          <div class="card-icon outstanding">
            <i class="fas fa-wallet"></i>
          </div>
          <div class="card-content">
            <h3>Outstanding</h3>
            <p class="card-value">${{ formatNumber(dashboard?.summary.outstanding_amount) }}</p>
            <p class="card-sub">Remaining balance</p>
          </div>
        </div>

        <div class="summary-card alert">
          <div class="card-icon risk">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
          <div class="card-content">
            <h3>Default Rate</h3>
            <p class="card-value">{{ dashboard?.summary.default_rate || 0 }}%</p>
            <p class="card-sub">Portfolio risk</p>
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

        <!-- Loan Applications Tab -->
        <div v-show="activeTab === 'applications'" class="tab-content">
          <div class="section-header">
            <h2>Pending Loan Applications</h2>
            <div class="filter-controls">
              <select v-model="applicationFilter" class="filter-select">
                <option value="">All Applications</option>
                <option value="pending">Pending</option>
                <option value="reviewing">Under Review</option>
              </select>
            </div>
          </div>

          <div class="applications-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Farmer</th>
                  <th>Amount</th>
                  <th>Purpose</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="app in dashboard?.pending_applications || []" :key="app.id">
                  <td>#{{ app.id }}</td>
                  <td>{{ app.farmer_name }}</td>
                  <td>${{ formatNumber(app.amount) }}</td>
                  <td>{{ app.purpose }}</td>
                  <td><span :class="['status-badge', `status-${app.status}`]">{{ app.status }}</span></td>
                  <td>{{ formatDate(app.created_at) }}</td>
                  <td class="action-buttons">
                    <button class="btn-small btn-review">Review</button>
                    <button class="btn-small btn-approve">Approve</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Loan Management Tab -->
        <div v-show="activeTab === 'loans'" class="tab-content">
          <div class="section-header">
            <h2>Loan Portfolio</h2>
            <div class="filter-controls">
              <select v-model="loanFilter" class="filter-select">
                <option value="">All Loans</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="defaulted">Defaulted</option>
              </select>
            </div>
          </div>

          <div class="loans-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Farmer</th>
                  <th>Amount</th>
                  <th>Interest Rate</th>
                  <th>Disbursed</th>
                  <th>Outstanding</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="loan in dashboard?.loans || []" :key="loan.id">
                  <td>#{{ loan.id }}</td>
                  <td>{{ loan.farmer_name }}</td>
                  <td>${{ formatNumber(loan.loan_amount) }}</td>
                  <td>{{ loan.interest_rate }}%</td>
                  <td>${{ formatNumber(loan.disbursed_amount) }}</td>
                  <td>${{ formatNumber(loan.outstanding_amount) }}</td>
                  <td><span :class="['status-badge', `status-${loan.status}`]">{{ loan.status }}</span></td>
                  <td class="action-buttons">
                    <button class="btn-small btn-view">View</button>
                    <button class="btn-small btn-repayment">Repayment</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Insurance Tab -->
        <div v-show="activeTab === 'insurance'" class="tab-content">
          <div class="section-header">
            <h2>Insurance Policies</h2>
            <button class="btn-primary" @click="showInsuranceDialog = true">+ New Policy</button>
          </div>

          <div class="insurance-grid">
            <div v-for="insurance in dashboard?.insurance_policies || []" :key="insurance.id" class="insurance-card">
              <div class="insurance-header">
                <h4>{{ insurance.farmer_name }}</h4>
                <span :class="['status-badge', `status-${insurance.status}`]">{{ insurance.status }}</span>
              </div>
              <div class="insurance-details">
                <p><strong>Policy ID:</strong> {{ insurance.policy_id }}</p>
                <p><strong>Coverage:</strong> ${{ formatNumber(insurance.coverage_amount) }}</p>
                <p><strong>Premium:</strong> ${{ formatNumber(insurance.premium_amount) }}</p>
                <p><strong>Expiry:</strong> {{ formatDate(insurance.expiry_date) }}</p>
              </div>
              <div class="insurance-actions">
                <button class="btn-small btn-edit">Edit</button>
                <button class="btn-small btn-renew">Renew</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Repayments Tab -->
        <div v-show="activeTab === 'repayments'" class="tab-content">
          <div class="section-header">
            <h2>Repayment Tracking</h2>
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

          <div class="repayment-stats">
            <div class="stat-card">
              <h4>Due Today</h4>
              <p class="stat-value">${{ formatNumber(dashboard?.repayment_stats?.due_today) }}</p>
            </div>
            <div class="stat-card">
              <h4>Overdue</h4>
              <p class="stat-value overdue">${{ formatNumber(dashboard?.repayment_stats?.overdue) }}</p>
            </div>
            <div class="stat-card">
              <h4>Paid This Month</h4>
              <p class="stat-value">${{ formatNumber(dashboard?.repayment_stats?.paid_this_month) }}</p>
            </div>
            <div class="stat-card">
              <h4>Repayment Rate</h4>
              <p class="stat-value">{{ dashboard?.repayment_stats?.repayment_rate || 0 }}%</p>
            </div>
          </div>

          <div class="repayments-table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Farmer</th>
                  <th>Loan ID</th>
                  <th>Amount Due</th>
                  <th>Due Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="repayment in dashboard?.repayments || []" :key="repayment.id">
                  <td>{{ repayment.farmer_name }}</td>
                  <td>#{{ repayment.loan_id }}</td>
                  <td>${{ formatNumber(repayment.amount_due) }}</td>
                  <td>{{ formatDate(repayment.due_date) }}</td>
                  <td><span :class="['status-badge', `status-${repayment.status}`]">{{ repayment.status }}</span></td>
                  <td class="action-buttons">
                    <button class="btn-small btn-process">Process</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Risk Assessment Tab -->
        <div v-show="activeTab === 'risk'" class="tab-content">
          <div class="section-header">
            <h2>Portfolio Risk Assessment</h2>
          </div>

          <div class="risk-overview">
            <div class="risk-card low">
              <h3>{{ dashboard?.risk_assessment?.low_risk_count || 0 }}</h3>
              <p>Low Risk Loans</p>
              <span class="percentage">{{ dashboard?.risk_assessment?.low_risk_percentage || 0 }}%</span>
            </div>
            <div class="risk-card medium">
              <h3>{{ dashboard?.risk_assessment?.medium_risk_count || 0 }}</h3>
              <p>Medium Risk Loans</p>
              <span class="percentage">{{ dashboard?.risk_assessment?.medium_risk_percentage || 0 }}%</span>
            </div>
            <div class="risk-card high">
              <h3>{{ dashboard?.risk_assessment?.high_risk_count || 0 }}</h3>
              <p>High Risk Loans</p>
              <span class="percentage">{{ dashboard?.risk_assessment?.high_risk_percentage || 0 }}%</span>
            </div>
            <div class="health-score">
              <h3>Portfolio Health Score</h3>
              <p class="score">{{ dashboard?.risk_assessment?.portfolio_health_score || 0 }}/100</p>
            </div>
          </div>

          <div class="high-risk-section">
            <h3>High Risk Loans</h3>
            <div class="high-risk-table">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Farmer</th>
                    <th>Loan Amount</th>
                    <th>Days Overdue</th>
                    <th>Risk Factor</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="loan in dashboard?.high_risk_loans || []" :key="loan.id">
                    <td>{{ loan.farmer_name }}</td>
                    <td>${{ formatNumber(loan.loan_amount) }}</td>
                    <td>{{ loan.days_overdue }}</td>
                    <td><span class="risk-badge high">{{ loan.risk_factor }}</span></td>
                    <td class="action-buttons">
                      <button class="btn-small btn-contact">Contact</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="recent-section">
        <div class="recent-card">
          <h2>Recent Transactions</h2>
          <div class="list-items">
            <div v-for="transaction in dashboard?.recent_transactions?.slice(0, 5) || []" :key="transaction.id" class="list-item">
              <span class="item-type">{{ transaction.type }}</span>
              <span class="item-amount">${{ formatNumber(transaction.amount) }}</span>
              <span class="item-date">{{ formatDate(transaction.date) }}</span>
            </div>
          </div>
        </div>

        <div class="recent-card">
          <h2>Portfolio Summary</h2>
          <div class="list-items">
            <div class="list-item">
              <span class="label">Total Disbursed:</span>
              <span class="value">${{ formatNumber(dashboard?.summary.total_disbursed) }}</span>
            </div>
            <div class="list-item">
              <span class="label">Total Outstanding:</span>
              <span class="value">${{ formatNumber(dashboard?.summary.outstanding_amount) }}</span>
            </div>
            <div class="list-item">
              <span class="label">Total Repaid:</span>
              <span class="value">${{ formatNumber(dashboard?.summary.total_repaid) }}</span>
            </div>
            <div class="list-item">
              <span class="label">Repayment Rate:</span>
              <span class="value">{{ dashboard?.summary.repayment_rate || 0 }}%</span>
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
import FinancialSidebar from '@/components/Sidebar/FinancialSidebar.vue'

const auth = useAuthStore()
const router = useRouter()
const activeTab = ref('applications')
const tabs = ['applications', 'loans', 'insurance', 'repayments', 'risk']

const dashboard = ref(null)
const applicationFilter = ref('')
const loanFilter = ref('')
const selectedPeriod = ref(30)
const showInsuranceDialog = ref(false)

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  try {
    const res = await fetch('/api/financial/dashboard', {
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
    applications: 'Loan Applications',
    loans: 'Loan Portfolio',
    insurance: 'Insurance',
    repayments: 'Repayments',
    risk: 'Risk Assessment'
  }
  return names[tab] || tab
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.financial-layout {
  display: flex;
  height: 100vh;
}

.financial-dashboard {
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

.summary-card.alert {
  border-left: 4px solid #ef4444;
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

.card-icon.loans { background-color: #3b82f6; }
.card-icon.pending { background-color: #f59e0b; }
.card-icon.approved { background-color: #10b981; }
.card-icon.disbursed { background-color: #06b6d4; }
.card-icon.outstanding { background-color: #8b5cf6; }
.card-icon.risk { background-color: #ef4444; }

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
  color: #6366f1;
}

.tab-btn.active {
  color: #6366f1;
  border-bottom-color: #6366f1;
}

.tab-content {
  padding: 25px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
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
  font-size: 14px;
}

.btn-primary {
  background: #6366f1;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary:hover {
  background: #4f46e5;
}

.applications-table-container,
.loans-table-container,
.repayments-table-container,
.high-risk-table {
  overflow-x: auto;
  margin-bottom: 20px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
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

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.status-reviewing {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-badge.status-active {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-completed {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-defaulted {
  background-color: #fee2e2;
  color: #991b1b;
}

.status-badge.status-paid {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.status-overdue {
  background-color: #fecaca;
  color: #7c2d12;
}

.action-buttons {
  display: flex;
  gap: 8px;
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

.btn-review { background: #3b82f6; color: white; }
.btn-approve { background: #10b981; color: white; }
.btn-view { background: #6366f1; color: white; }
.btn-repayment { background: #8b5cf6; color: white; }
.btn-edit { background: #3b82f6; color: white; }
.btn-renew { background: #06b6d4; color: white; }
.btn-process { background: #6366f1; color: white; }
.btn-contact { background: #ef4444; color: white; }

.insurance-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.insurance-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 15px;
  transition: all 0.3s;
}

.insurance-card:hover {
  border-color: #6366f1;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
}

.insurance-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 10px;
}

.insurance-header h4 {
  margin: 0;
  color: #333;
}

.insurance-details p {
  margin: 5px 0;
  font-size: 13px;
  color: #666;
}

.insurance-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.repayment-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-bottom: 25px;
}

.stat-card {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.stat-card h4 {
  color: #666;
  margin-bottom: 10px;
  font-size: 14px;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.stat-value.overdue {
  color: #ef4444;
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
  background: #6366f1;
  color: white;
}

.risk-overview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.risk-card {
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  color: white;
}

.risk-card.low {
  background: linear-gradient(135deg, #10b981, #059669);
}

.risk-card.medium {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.risk-card.high {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.risk-card h3 {
  font-size: 32px;
  margin: 0 0 10px 0;
}

.risk-card p {
  font-size: 14px;
  margin: 0;
  opacity: 0.9;
}

.percentage {
  display: block;
  font-size: 12px;
  margin-top: 8px;
  opacity: 0.8;
}

.health-score {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  border-radius: 8px;
  padding: 20px;
  text-align: center;
  color: white;
}

.health-score h3 {
  margin: 0 0 10px 0;
  font-size: 14px;
}

.score {
  font-size: 32px;
  margin: 0;
  font-weight: bold;
}

.high-risk-section {
  margin-top: 30px;
}

.high-risk-section h3 {
  margin-bottom: 15px;
  color: #333;
}

.risk-badge.high {
  background: #fee2e2;
  color: #991b1b;
  padding: 6px 12px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 12px;
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

.item-type {
  font-weight: 600;
  color: #333;
}

.item-amount {
  color: #10b981;
  font-weight: 600;
}

.item-date {
  font-size: 12px;
  color: #999;
}

.label {
  font-weight: 600;
  color: #666;
}

.value {
  font-weight: 700;
  color: #333;
}

@media (max-width: 768px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .tab-buttons {
    flex-wrap: wrap;
  }
  
  .insurance-grid {
    grid-template-columns: 1fr;
  }
  
  .risk-overview {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
