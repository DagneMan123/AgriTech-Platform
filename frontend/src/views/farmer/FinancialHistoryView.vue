<template>
  <div class="financial-history-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="history-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Financial History</h1>
          <p>Comprehensive view of all your financial transactions and reports</p>
        </div>
        <button @click="downloadReport" class="btn-export">
          <Download size="16" />
          <span>Export Report</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading financial history...</p>
      </div>

      <!-- Content -->
      <div v-if="!loading" class="history-content">
        <!-- Summary Cards -->
        <div class="summary-section">
          <h2>Financial Summary</h2>
          <div class="summary-grid">
            <div class="summary-card income">
              <div class="card-icon">
                <TrendingUp size="24" />
              </div>
              <div>
                <p class="card-label">Total Income</p>
                <p class="card-value">${{ formatNumber(summary.totalIncome) }}</p>
                <p class="card-period">This Year</p>
              </div>
            </div>

            <div class="summary-card expense">
              <div class="card-icon">
                <TrendingDown size="24" />
              </div>
              <div>
                <p class="card-label">Total Expenses</p>
                <p class="card-value">${{ formatNumber(summary.totalExpenses) }}</p>
                <p class="card-period">This Year</p>
              </div>
            </div>

            <div class="summary-card profit">
              <div class="card-icon">
                <BarChart3 size="24" />
              </div>
              <div>
                <p class="card-label">Net Profit</p>
                <p class="card-value" :class="{ negative: summary.netProfit < 0 }">
                  ${{ formatNumber(summary.netProfit) }}
                </p>
                <p class="card-period">This Year</p>
              </div>
            </div>

            <div class="summary-card balance">
              <div class="card-icon">
                <PieChart size="24" />
              </div>
              <div>
                <p class="card-label">Account Balance</p>
                <p class="card-value">${{ formatNumber(summary.accountBalance) }}</p>
                <p class="card-period">Current</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters & Controls -->
        <div class="controls-section">
          <div class="filter-group">
            <select v-model="filterType" class="filter-select">
              <option value="">All Transactions</option>
              <option value="income">Income Only</option>
              <option value="expense">Expenses Only</option>
              <option value="payment">Payments</option>
            </select>
            <select v-model="filterMonth" class="filter-select">
              <option value="">All Months</option>
              <option value="9">September 2026</option>
              <option value="8">August 2026</option>
              <option value="7">July 2026</option>
              <option value="6">June 2026</option>
            </select>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search description..."
              class="search-input"
            />
          </div>
        </div>

        <!-- Transaction List -->
        <div class="transactions-section">
          <div class="section-header">
            <h2>Transaction History</h2>
            <select v-model="sortBy" class="sort-select">
              <option value="recent">Most Recent</option>
              <option value="oldest">Oldest First</option>
              <option value="amount-high">Highest Amount</option>
              <option value="amount-low">Lowest Amount</option>
            </select>
          </div>

          <div v-if="filteredTransactions.length > 0" class="transactions-list">
            <div v-for="(monthGroup, index) in groupedTransactions" :key="index" class="month-group">
              <div class="month-header">
                <h3>{{ monthGroup.monthName }}</h3>
                <div class="month-summary">
                  <span class="summary-item income">Income: ${{ formatNumber(monthGroup.totalIncome) }}</span>
                  <span class="summary-item expense">Expenses: ${{ formatNumber(monthGroup.totalExpenses) }}</span>
                </div>
              </div>

              <div class="transactions-table">
                <div v-for="transaction in monthGroup.transactions" :key="transaction.id" class="transaction-row">
                  <div class="row-date">
                    <p class="date">{{ formatDate(transaction.date) }}</p>
                    <p class="day">{{ getDayName(transaction.date) }}</p>
                  </div>

                  <div class="row-details">
                    <h4>{{ transaction.description }}</h4>
                    <p class="category">{{ transaction.category }}</p>
                  </div>

                  <div class="row-reference">
                    <p class="reference">{{ transaction.reference }}</p>
                  </div>

                  <div class="row-amount">
                    <span class="amount" :class="`type-${transaction.type}`">
                      {{ transaction.type === 'income' ? '+' : '-' }}${{ formatNumber(transaction.amount) }}
                    </span>
                  </div>

                  <div class="row-actions">
                    <button @click="viewTransaction(transaction)" class="btn-view">
                      <Eye size="16" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <BarChart3 size="48" class="empty-icon" />
            <p>No transactions found</p>
          </div>
        </div>

        <!-- Monthly Breakdown -->
        <div class="breakdown-section">
          <h2>Monthly Breakdown</h2>
          <div class="breakdown-grid">
            <div v-for="month in monthlyData" :key="month.month" class="breakdown-card">
              <h3>{{ month.monthName }}</h3>
              <div class="breakdown-content">
                <div class="breakdown-item">
                  <span class="label">Income</span>
                  <span class="value income">+${{ formatNumber(month.income) }}</span>
                </div>
                <div class="breakdown-item">
                  <span class="label">Expenses</span>
                  <span class="value expense">-${{ formatNumber(month.expenses) }}</span>
                </div>
                <div class="breakdown-divider"></div>
                <div class="breakdown-item net">
                  <span class="label">Net</span>
                  <span class="value" :class="{ negative: month.net < 0 }">
                    ${{ formatNumber(month.net) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Expense Categories -->
        <div class="categories-section">
          <h2>Expense Breakdown by Category</h2>
          <div class="categories-grid">
            <div v-for="category in expenseCategories" :key="category.name" class="category-card">
              <div class="category-icon" :class="`icon-${category.type}`">
                <component :is="category.icon" size="24" />
              </div>
              <div class="category-content">
                <h3>{{ category.name }}</h3>
                <p class="category-amount">${{ formatNumber(category.amount) }}</p>
                <div class="category-progress">
                  <div class="progress-bar" :style="{ width: category.percentage + '%' }"></div>
                </div>
                <p class="category-percent">{{ category.percentage }}% of total</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Transaction Details Modal -->
        <div v-if="selectedTransaction" class="modal-overlay" @click="selectedTransaction = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Transaction Details</h2>
              <button @click="selectedTransaction = null" class="btn-close">
                <X size="20" />
              </button>
            </div>

            <div class="modal-body">
              <div class="detail-header">
                <div class="detail-icon" :class="`icon-${selectedTransaction.type}`">
                  <DollarSign size="32" />
                </div>
                <div>
                  <h3>{{ selectedTransaction.description }}</h3>
                  <p>{{ formatDate(selectedTransaction.date) }}</p>
                </div>
              </div>

              <div class="detail-amount">
                <p class="amount-label">{{ selectedTransaction.type === 'income' ? 'Income' : 'Expense' }}</p>
                <p class="amount-value" :class="`type-${selectedTransaction.type}`">
                  {{ selectedTransaction.type === 'income' ? '+' : '-' }}${{ formatNumber(selectedTransaction.amount) }}
                </p>
              </div>

              <div class="details-grid">
                <div class="detail-item">
                  <span class="label">Category:</span>
                  <span class="value">{{ selectedTransaction.category }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Reference:</span>
                  <span class="value">{{ selectedTransaction.reference }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Payment Method:</span>
                  <span class="value">{{ selectedTransaction.paymentMethod }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Status:</span>
                  <span class="value">{{ selectedTransaction.status }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Notes:</span>
                  <span class="value">{{ selectedTransaction.notes || 'N/A' }}</span>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedTransaction = null" class="btn-secondary">Close</button>
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
  Download, TrendingUp, TrendingDown, BarChart3, PieChart, Eye, X, DollarSign,
  ShoppingCart, Package, Zap, Droplet
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const selectedTransaction = ref(null)
const filterType = ref('')
const filterMonth = ref('')
const searchQuery = ref('')
const sortBy = ref('recent')

const mockTransactions = [
  // September 2026
  {
    id: 'TXN-001',
    date: '2026-09-03',
    type: 'income',
    description: 'Tomato Sales',
    category: 'Sales',
    amount: 1250,
    reference: 'INV-2026-089',
    paymentMethod: 'Bank Transfer',
    status: 'Completed',
    notes: 'Delivered to City Market'
  },
  {
    id: 'TXN-002',
    date: '2026-09-02',
    type: 'expense',
    description: 'Seeds Purchase',
    category: 'Input Costs',
    amount: 450,
    reference: 'PO-2026-234',
    paymentMethod: 'Credit Card',
    status: 'Completed',
    notes: 'Tomato and Carrot seeds'
  },
  {
    id: 'TXN-003',
    date: '2026-09-01',
    type: 'expense',
    description: 'Fertilizer',
    category: 'Input Costs',
    amount: 320,
    reference: 'PO-2026-235',
    paymentMethod: 'Credit Card',
    status: 'Completed',
    notes: 'NPK 20:20:20'
  },
  {
    id: 'TXN-004',
    date: '2026-09-01',
    type: 'expense',
    description: 'Pesticide Application',
    category: 'Maintenance',
    amount: 180,
    reference: 'PO-2026-236',
    paymentMethod: 'Cash',
    status: 'Completed',
    notes: 'Weekly spray'
  },
  // August 2026
  {
    id: 'TXN-005',
    date: '2026-08-28',
    type: 'income',
    description: 'Corn Sales',
    category: 'Sales',
    amount: 2000,
    reference: 'INV-2026-085',
    paymentMethod: 'Bank Transfer',
    status: 'Completed',
    notes: 'Delivered to Wholesale Center'
  },
  {
    id: 'TXN-006',
    date: '2026-08-25',
    type: 'expense',
    description: 'Equipment Rental',
    category: 'Equipment',
    amount: 500,
    reference: 'REF-2026-156',
    paymentMethod: 'Bank Transfer',
    status: 'Completed',
    notes: 'Tractor rental for plowing'
  },
  {
    id: 'TXN-007',
    date: '2026-08-20',
    type: 'income',
    description: 'Subsidy Payment',
    category: 'Government Support',
    amount: 5000,
    reference: 'SUB-2026-001',
    paymentMethod: 'Government Transfer',
    status: 'Completed',
    notes: 'Agricultural subsidy'
  },
  {
    id: 'TXN-008',
    date: '2026-08-15',
    type: 'expense',
    description: 'Insurance Premium',
    category: 'Insurance',
    amount: 1500,
    reference: 'INS-2026-001',
    paymentMethod: 'Bank Transfer',
    status: 'Completed',
    notes: 'Annual crop insurance'
  },
  // July 2026
  {
    id: 'TXN-009',
    date: '2026-07-30',
    type: 'income',
    description: 'Lettuce Sales',
    category: 'Sales',
    amount: 800,
    reference: 'INV-2026-078',
    paymentMethod: 'Bank Transfer',
    status: 'Completed',
    notes: 'Direct to buyer'
  },
  {
    id: 'TXN-010',
    date: '2026-07-20',
    type: 'expense',
    description: 'Irrigation Setup',
    category: 'Infrastructure',
    amount: 3000,
    reference: 'PO-2026-210',
    paymentMethod: 'Bank Transfer',
    status: 'Completed',
    notes: 'Drip irrigation system'
  }
]

const transactions = ref(mockTransactions)

const summary = computed(() => {
  const income = transactions.value
    .filter(t => t.type === 'income')
    .reduce((sum, t) => sum + t.amount, 0)
  const expenses = transactions.value
    .filter(t => t.type === 'expense')
    .reduce((sum, t) => sum + t.amount, 0)
  return {
    totalIncome: income,
    totalExpenses: expenses,
    netProfit: income - expenses,
    accountBalance: 15000
  }
})

const filteredTransactions = computed(() => {
  let result = transactions.value

  if (filterType.value) {
    result = result.filter(t => t.type === filterType.value)
  }

  if (filterMonth.value) {
    result = result.filter(t => {
      const month = new Date(t.date).getMonth() + 1
      return month === parseInt(filterMonth.value)
    })
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(t => t.description.toLowerCase().includes(query))
  }

  if (sortBy.value === 'oldest') {
    result.sort((a, b) => new Date(a.date) - new Date(b.date))
  } else if (sortBy.value === 'amount-high') {
    result.sort((a, b) => b.amount - a.amount)
  } else if (sortBy.value === 'amount-low') {
    result.sort((a, b) => a.amount - b.amount)
  } else {
    result.sort((a, b) => new Date(b.date) - new Date(a.date))
  }

  return result
})

const groupedTransactions = computed(() => {
  const groups = {}
  filteredTransactions.value.forEach(t => {
    const month = new Date(t.date).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
    if (!groups[month]) {
      groups[month] = {
        monthName: month,
        transactions: [],
        totalIncome: 0,
        totalExpenses: 0
      }
    }
    groups[month].transactions.push(t)
    if (t.type === 'income') {
      groups[month].totalIncome += t.amount
    } else {
      groups[month].totalExpenses += t.amount
    }
  })
  return Object.values(groups)
})

const monthlyData = [
  { month: 9, monthName: 'September', income: 1250, expenses: 950 },
  { month: 8, monthName: 'August', income: 7800, expenses: 2500 },
  { month: 7, monthName: 'July', income: 800, expenses: 3000 }
]

monthlyData.forEach(m => { m.net = m.income - m.expenses })

const expenseCategories = [
  { name: 'Input Costs', type: 'input', icon: 'ShoppingCart', amount: 770, percentage: 35 },
  { name: 'Equipment', type: 'equipment', icon: 'Package', amount: 500, percentage: 23 },
  { name: 'Infrastructure', type: 'infrastructure', icon: 'Zap', amount: 3000, percentage: 27 },
  { name: 'Insurance', type: 'insurance', icon: 'Droplet', amount: 1500, percentage: 15 }
]

const fetchHistory = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)
const formatDate = (date) => new Date(date).toLocaleDateString()
const getDayName = (date) => new Date(date).toLocaleDateString('en-US', { weekday: 'short' })
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)

const viewTransaction = (transaction) => { selectedTransaction.value = transaction }
const downloadReport = () => { alert('Financial report downloaded') }

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchHistory() })
</script>

<style scoped>
.financial-history-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.history-container {
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

.btn-export {
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

.btn-export:hover {
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

.history-content {
  padding: 30px;
  flex: 1;
}

.summary-section {
  margin-bottom: 30px;
}

.summary-section h2 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border-left: 4px solid;
}

.summary-card.income {
  border-left-color: #10b981;
}

.summary-card.expense {
  border-left-color: #ef4444;
}

.summary-card.profit {
  border-left-color: #3b82f6;
}

.summary-card.balance {
  border-left-color: #8b5cf6;
}

.card-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 12px;
  flex-shrink: 0;
  color: white;
}

.summary-card.income .card-icon { background: #d1fae5; color: #065f46; }
.summary-card.expense .card-icon { background: #fee2e2; color: #991b1b; }
.summary-card.profit .card-icon { background: #dbeafe; color: #1e40af; }
.summary-card.balance .card-icon { background: #f3e8ff; color: #6b21a8; }

.card-label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
  margin: 0;
}

.card-value {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
  margin: 8px 0;
}

.card-value.negative {
  color: #ef4444;
}

.card-period {
  font-size: 12px;
  color: #9ca3af;
  margin: 0;
}

.controls-section {
  background: white;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 30px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.filter-group {
  display: flex;
  gap: 12px;
}

.filter-select,
.search-input {
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
  color: #4b5563;
  background: white;
  font-family: inherit;
}

.search-input {
  flex: 1;
}

.search-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

.transactions-section {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
}

.section-header {
  padding: 20px 30px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.section-header h2 {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.sort-select {
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 13px;
  color: #4b5563;
  background: white;
  cursor: pointer;
}

.month-group {
  border-bottom: 1px solid #e5e7eb;
}

.month-group:last-child {
  border-bottom: none;
}

.month-header {
  padding: 16px 30px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.month-header h3 {
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.month-summary {
  display: flex;
  gap: 20px;
}

.summary-item {
  font-size: 13px;
  font-weight: 600;
}

.summary-item.income { color: #065f46; }
.summary-item.expense { color: #991b1b; }

.transactions-table {
  padding: 0 30px;
}

.transaction-row {
  display: grid;
  grid-template-columns: 100px 1fr 150px 150px 50px;
  gap: 16px;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid #f3f4f6;
}

.transaction-row:last-child {
  border-bottom: none;
}

.row-date {
  text-align: center;
}

.date {
  font-size: 13px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.day {
  font-size: 11px;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.row-details h4 {
  font-size: 13px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.row-details .category {
  font-size: 12px;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.row-reference {
  font-size: 12px;
  color: #6b7280;
  text-align: center;
}

.row-amount {
  text-align: right;
}

.amount {
  font-size: 14px;
  font-weight: 700;
}

.type-income { color: #059669; }
.type-expense { color: #ef4444; }

.row-actions {
  text-align: center;
}

.btn-view {
  background: none;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 6px 8px;
  cursor: pointer;
  color: #6b7280;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
}

.btn-view:hover {
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

.breakdown-section {
  margin-bottom: 30px;
}

.breakdown-section h2 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.breakdown-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.breakdown-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
}

.breakdown-card h3 {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 16px 0;
}

.breakdown-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.breakdown-item {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}

.breakdown-item .label {
  color: #6b7280;
  font-weight: 600;
}

.breakdown-item .value {
  font-weight: 700;
}

.breakdown-item .value.income { color: #059669; }
.breakdown-item .value.expense { color: #ef4444; }
.breakdown-item .value.negative { color: #ef4444; }

.breakdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 4px 0;
}

.breakdown-item.net {
  border-top: 1px solid #e5e7eb;
  padding-top: 12px;
}

.categories-section {
  margin-bottom: 30px;
}

.categories-section h2 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.category-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
  display: flex;
  gap: 16px;
}

.category-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: white;
}

.icon-input { background: #dbeafe; color: #1e40af; }
.icon-equipment { background: #f0fdf4; color: #15803d; }
.icon-infrastructure { background: #fef3c7; color: #92400e; }
.icon-insurance { background: #f3e8ff; color: #6b21a8; }

.category-content {
  flex: 1;
}

.category-content h3 {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.category-amount {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 8px 0;
}

.category-progress {
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #1e40af);
}

.category-percent {
  font-size: 12px;
  color: #6b7280;
  margin: 0;
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
  max-width: 600px;
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

.detail-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
}

.detail-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.detail-icon.income { background: #d1fae5; color: #065f46; }
.detail-icon.expense { background: #fee2e2; color: #991b1b; }

.detail-header h3 {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.detail-header p {
  font-size: 13px;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.detail-amount {
  text-align: center;
  padding: 20px;
  background: #f9fafb;
  border-radius: 8px;
  margin-bottom: 24px;
}

.amount-label {
  font-size: 12px;
  color: #6b7280;
  text-transform: uppercase;
  font-weight: 600;
  margin: 0;
}

.amount-value {
  font-size: 32px;
  font-weight: 700;
  margin: 8px 0 0 0;
}

.amount-value.type-income { color: #059669; }
.amount-value.type-expense { color: #ef4444; }

.details-grid {
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

.modal-footer {
  padding: 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  position: sticky;
  bottom: 0;
  background: white;
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

@media (max-width: 1024px) {
  .transaction-row {
    grid-template-columns: 80px 1fr 120px 120px 40px;
  }
}

@media (max-width: 768px) {
  .history-container {
    margin-left: 0;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-export {
    width: 100%;
    justify-content: center;
  }

  .filter-group {
    flex-direction: column;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }

  .breakdown-grid,
  .categories-grid {
    grid-template-columns: 1fr;
  }

  .transaction-row {
    grid-template-columns: 1fr;
  }

  .row-date,
  .row-reference,
  .row-amount,
  .row-actions {
    text-align: left;
  }

  .modal-content {
    width: 95%;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }
}
</style>
