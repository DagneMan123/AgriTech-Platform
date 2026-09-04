<template>
  <div class="payments-layout">
    <FarmerSidebar @logout="handleLogout" />
    <div class="payments-container">
      <!-- Header -->
      <div class="page-header">
        <div class="header-content">
          <h1>Payment Methods</h1>
          <p>Manage your payment methods and transaction history</p>
        </div>
        <button @click="showAddPayment = true" class="btn-new">
          <Plus size="16" />
          <span>Add Payment Method</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <p>Loading payment information...</p>
      </div>

      <!-- Content -->
      <div v-if="!loading" class="payments-content">
        <!-- Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <CreditCard size="20" class="stat-icon methods" />
            <div>
              <span class="stat-label">Payment Methods</span>
              <span class="stat-value">{{ paymentMethods.length }}</span>
            </div>
          </div>
          <div class="stat-card">
            <TrendingUp size="20" class="stat-icon total" />
            <div>
              <span class="stat-label">Total Transactions</span>
              <span class="stat-value">{{ transactions.length }}</span>
            </div>
          </div>
          <div class="stat-card">
            <DollarSign size="20" class="stat-icon spent" />
            <div>
              <span class="stat-label">Total Spent (This Month)</span>
              <span class="stat-value">${{ formatNumber(monthlySpent) }}</span>
            </div>
          </div>
          <div class="stat-card">
            <CheckCircle size="20" class="stat-icon pending" />
            <div>
              <span class="stat-label">Pending Payments</span>
              <span class="stat-value">{{ pendingPayments }}</span>
            </div>
          </div>
        </div>

        <!-- Payment Methods Section -->
        <div class="methods-section">
          <h2>Your Payment Methods</h2>
          <div v-if="paymentMethods.length > 0" class="methods-grid">
            <div v-for="method in paymentMethods" :key="method.id" class="method-card">
              <div class="method-header">
                <div class="method-icon" :class="`icon-${method.type}`">
                  <CreditCard v-if="method.type === 'card'" size="24" />
                  <Smartphone v-else-if="method.type === 'mobile'" size="24" />
                  <Bank v-else-if="method.type === 'bank'" size="24" />
                </div>
                <div class="method-info">
                  <h3>{{ method.name }}</h3>
                  <p class="method-detail">{{ method.displayNumber }}</p>
                </div>
                <div v-if="method.isDefault" class="default-badge">Default</div>
              </div>

              <div class="method-body">
                <p v-if="method.type === 'card'" class="method-text">
                  Expires: {{ method.expiryDate }}
                </p>
                <p v-if="method.type === 'mobile'" class="method-text">
                  Provider: {{ method.provider }}
                </p>
                <p v-if="method.type === 'bank'" class="method-text">
                  Account: {{ method.accountNumber }}
                </p>
              </div>

              <div class="method-actions">
                <button 
                  v-if="!method.isDefault" 
                  @click="setDefault(method)" 
                  class="btn-action"
                >
                  Set as Default
                </button>
                <button @click="editMethod(method)" class="btn-action">
                  <Edit size="14" />
                  Edit
                </button>
                <button @click="deleteMethod(method)" class="btn-action btn-danger">
                  <Trash2 size="14" />
                  Delete
                </button>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            <CreditCard size="48" class="empty-icon" />
            <p>No payment methods added</p>
          </div>
        </div>

        <!-- Transaction History Section -->
        <div class="transactions-section">
          <div class="section-header">
            <h2>Recent Transactions</h2>
            <div class="filter-group">
              <select v-model="filterType" class="filter-select">
                <option value="">All Types</option>
                <option value="purchase">Purchase</option>
                <option value="payment">Payment</option>
                <option value="refund">Refund</option>
              </select>
              <select v-model="filterStatus" class="filter-select">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
              </select>
            </div>
          </div>

          <div v-if="filteredTransactions.length > 0" class="transactions-list">
            <div v-for="transaction in filteredTransactions" :key="transaction.id" class="transaction-item">
              <div class="trans-left">
                <div class="transaction-icon" :class="`icon-${transaction.type}`">
                  <ShoppingCart v-if="transaction.type === 'purchase'" size="20" />
                  <Send v-else-if="transaction.type === 'payment'" size="20" />
                  <RotateCcw v-else-if="transaction.type === 'refund'" size="20" />
                </div>
              </div>

              <div class="trans-center">
                <div class="transaction-info">
                  <h3>{{ transaction.description }}</h3>
                  <p class="transaction-meta">
                    {{ formatDate(transaction.date) }} at {{ transaction.time }}
                  </p>
                </div>
              </div>

              <div class="trans-right">
                <span class="transaction-amount" :class="`amount-${transaction.type}`">
                  {{ transaction.type === 'refund' ? '+' : '-' }}${{ formatNumber(transaction.amount) }}
                </span>
                <span class="transaction-status" :class="`status-${transaction.status}`">
                  {{ capitalize(transaction.status) }}
                </span>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <ShoppingCart size="48" class="empty-icon" />
            <p>No transactions found</p>
          </div>
        </div>

        <!-- Payment Schedules Section -->
        <div class="schedules-section">
          <h2>Upcoming Payment Schedules</h2>
          <div v-if="paymentSchedules.length > 0" class="schedules-list">
            <div v-for="schedule in paymentSchedules" :key="schedule.id" class="schedule-card">
              <div class="schedule-header">
                <div>
                  <h3>{{ schedule.reason }}</h3>
                  <p class="schedule-id">ID: {{ schedule.id }}</p>
                </div>
                <span class="amount-badge">${{ formatNumber(schedule.amount) }}</span>
              </div>

              <div class="schedule-body">
                <div class="schedule-detail">
                  <span class="label">Due Date:</span>
                  <span class="value">{{ formatDate(schedule.dueDate) }}</span>
                </div>
                <div class="schedule-detail">
                  <span class="label">Status:</span>
                  <span class="status-badge" :class="`status-${schedule.status}`">
                    {{ capitalize(schedule.status) }}
                  </span>
                </div>
                <div class="schedule-detail">
                  <span class="label">Payment Method:</span>
                  <span class="value">{{ schedule.paymentMethod }}</span>
                </div>
              </div>

              <div class="schedule-actions">
                <button @click="payNow(schedule)" class="btn-pay">
                  <DollarSign size="14" />
                  Pay Now
                </button>
              </div>
            </div>
          </div>

          <div v-else class="empty-state">
            <Calendar size="48" class="empty-icon" />
            <p>No upcoming payment schedules</p>
          </div>
        </div>

        <!-- Add Payment Method Modal -->
        <div v-if="showAddPayment" class="modal-overlay" @click="showAddPayment = false">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Add Payment Method</h2>
              <button @click="showAddPayment = false" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="submitPaymentMethod" class="form">
                <div class="form-group">
                  <label>Payment Method Type</label>
                  <select v-model="newPaymentForm.type" required class="form-control">
                    <option value="">Select type</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="bank">Bank Account</option>
                    <option value="mobile">Mobile Wallet</option>
                  </select>
                </div>

                <!-- Card Fields -->
                <div v-if="newPaymentForm.type === 'card'">
                  <div class="form-group">
                    <label>Cardholder Name</label>
                    <input v-model="newPaymentForm.name" type="text" required class="form-control" />
                  </div>
                  <div class="form-group">
                    <label>Card Number</label>
                    <input v-model="newPaymentForm.cardNumber" type="text" placeholder="1234 5678 9012 3456" class="form-control" />
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label>Expiry Date</label>
                      <input v-model="newPaymentForm.expiryDate" type="text" placeholder="MM/YY" class="form-control" />
                    </div>
                    <div class="form-group">
                      <label>CVV</label>
                      <input v-model="newPaymentForm.cvv" type="text" placeholder="123" class="form-control" />
                    </div>
                  </div>
                </div>

                <!-- Bank Fields -->
                <div v-if="newPaymentForm.type === 'bank'">
                  <div class="form-group">
                    <label>Account Holder Name</label>
                    <input v-model="newPaymentForm.name" type="text" required class="form-control" />
                  </div>
                  <div class="form-group">
                    <label>Bank Name</label>
                    <input v-model="newPaymentForm.bankName" type="text" required class="form-control" />
                  </div>
                  <div class="form-group">
                    <label>Account Number</label>
                    <input v-model="newPaymentForm.accountNumber" type="text" required class="form-control" />
                  </div>
                  <div class="form-group">
                    <label>Routing Number</label>
                    <input v-model="newPaymentForm.routingNumber" type="text" required class="form-control" />
                  </div>
                </div>

                <!-- Mobile Wallet Fields -->
                <div v-if="newPaymentForm.type === 'mobile'">
                  <div class="form-group">
                    <label>Wallet Provider</label>
                    <select v-model="newPaymentForm.provider" required class="form-control">
                      <option value="">Select provider</option>
                      <option value="apple-pay">Apple Pay</option>
                      <option value="google-pay">Google Pay</option>
                      <option value="paypal">PayPal</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Email/Phone</label>
                    <input v-model="newPaymentForm.walletId" type="text" required class="form-control" />
                  </div>
                </div>

                <div class="form-group checkbox">
                  <input v-model="newPaymentForm.isDefault" type="checkbox" id="set-default" />
                  <label for="set-default">Set as default payment method</label>
                </div>

                <div class="form-actions">
                  <button type="button" @click="showAddPayment = false" class="btn-secondary">Cancel</button>
                  <button type="submit" class="btn-primary">Add Payment Method</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Receipt Modal -->
        <div v-if="selectedTransaction" class="modal-overlay" @click="selectedTransaction = null">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h2>Transaction Receipt</h2>
              <button @click="selectedTransaction = null" class="btn-close">
                <X size="20" />
              </button>
            </div>
            <div class="modal-body receipt">
              <div class="receipt-header">
                <p class="receipt-id">Receipt #{{ selectedTransaction.id }}</p>
                <p class="receipt-date">{{ formatDate(selectedTransaction.date) }}</p>
              </div>

              <div class="receipt-item">
                <span>{{ selectedTransaction.description }}</span>
                <span>${{ formatNumber(selectedTransaction.amount) }}</span>
              </div>

              <div class="receipt-details">
                <div class="receipt-row">
                  <span>Payment Method:</span>
                  <span>{{ selectedTransaction.method }}</span>
                </div>
                <div class="receipt-row">
                  <span>Status:</span>
                  <span>{{ capitalize(selectedTransaction.status) }}</span>
                </div>
                <div class="receipt-row">
                  <span>Transaction ID:</span>
                  <span>{{ selectedTransaction.transactionId }}</span>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="selectedTransaction = null" class="btn-secondary">Close</button>
              <button @click="downloadReceipt(selectedTransaction)" class="btn-primary">
                <Download size="16" />
                Download Receipt
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
import FarmerSidebar from '@/components/Sidebar/FarmerSidebar.vue'
import {
  Plus, CreditCard, TrendingUp, DollarSign, CheckCircle, Smartphone, Bank, Edit, Trash2,
  ShoppingCart, Send, RotateCcw, Calendar, X, Download
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const showAddPayment = ref(false)
const selectedTransaction = ref(null)
const filterType = ref('')
const filterStatus = ref('')

const paymentMethods = ref([
  {
    id: 1,
    type: 'card',
    name: 'Personal Visa Card',
    displayNumber: '•••• •••• •••• 4892',
    expiryDate: '12/25',
    isDefault: true
  },
  {
    id: 2,
    type: 'bank',
    name: 'Agricultural Bank Account',
    displayNumber: 'Account ending in 5678',
    accountNumber: '••••••••5678',
    isDefault: false
  },
  {
    id: 3,
    type: 'mobile',
    name: 'Google Pay',
    displayNumber: 'farmer@email.com',
    provider: 'Google Pay',
    isDefault: false
  }
])

const transactions = ref([
  {
    id: 'TXN-001',
    type: 'purchase',
    description: 'Seeds Purchase - Tomato Seeds',
    amount: 450,
    date: '2026-09-03',
    time: '10:30 AM',
    status: 'completed',
    method: 'Visa Card',
    transactionId: 'TXN-2026-001'
  },
  {
    id: 'TXN-002',
    type: 'payment',
    description: 'Loan EMI Payment',
    amount: 867,
    date: '2026-09-02',
    time: '02:15 PM',
    status: 'completed',
    method: 'Bank Transfer',
    transactionId: 'TXN-2026-002'
  },
  {
    id: 'TXN-003',
    type: 'purchase',
    description: 'Fertilizer Purchase',
    amount: 320,
    date: '2026-09-01',
    time: '09:45 AM',
    status: 'completed',
    method: 'Google Pay',
    transactionId: 'TXN-2026-003'
  },
  {
    id: 'TXN-004',
    type: 'refund',
    description: 'Refund - Pesticide Return',
    amount: 150,
    date: '2026-08-31',
    time: '11:20 AM',
    status: 'completed',
    method: 'Visa Card',
    transactionId: 'TXN-2026-004'
  },
  {
    id: 'TXN-005',
    type: 'payment',
    description: 'Insurance Premium Payment',
    amount: 500,
    date: '2026-08-30',
    time: '03:50 PM',
    status: 'pending',
    method: 'Bank Transfer',
    transactionId: 'TXN-2026-005'
  }
])

const paymentSchedules = ref([
  {
    id: 'PS-001',
    reason: 'Loan EMI - Production Loan',
    amount: 867,
    dueDate: '2026-10-05',
    status: 'upcoming',
    paymentMethod: 'Bank Transfer'
  },
  {
    id: 'PS-002',
    reason: 'Insurance Premium - Crop Coverage',
    amount: 1500,
    dueDate: '2026-10-10',
    status: 'upcoming',
    paymentMethod: 'Visa Card'
  }
])

const newPaymentForm = ref({
  type: '',
  name: '',
  isDefault: false,
  cardNumber: '',
  expiryDate: '',
  cvv: '',
  bankName: '',
  accountNumber: '',
  routingNumber: '',
  provider: '',
  walletId: ''
})

const filteredTransactions = computed(() => {
  let result = transactions.value
  if (filterType.value) {
    result = result.filter(t => t.type === filterType.value)
  }
  if (filterStatus.value) {
    result = result.filter(t => t.status === filterStatus.value)
  }
  return result.sort((a, b) => new Date(b.date) - new Date(a.date))
})

const monthlySpent = computed(() => {
  const now = new Date()
  const currentMonth = now.getMonth()
  const currentYear = now.getFullYear()
  
  return transactions.value
    .filter(t => {
      const tDate = new Date(t.date)
      return tDate.getMonth() === currentMonth && tDate.getFullYear() === currentYear && t.type !== 'refund'
    })
    .reduce((sum, t) => sum + t.amount, 0)
})

const pendingPayments = computed(() => {
  return transactions.value.filter(t => t.status === 'pending').length
})

const fetchPayments = async () => {
  loading.value = true
  await new Promise(r => setTimeout(r, 500))
  loading.value = false
}

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)
const formatDate = (date) => new Date(date).toLocaleDateString()
const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1)

const setDefault = (method) => {
  paymentMethods.value.forEach(m => m.isDefault = false)
  method.isDefault = true
  alert(`${method.name} set as default payment method`)
}

const editMethod = (method) => { alert(`Edit functionality for ${method.name}`) }
const deleteMethod = (method) => { 
  const index = paymentMethods.value.indexOf(method)
  if (index > -1) {
    paymentMethods.value.splice(index, 1)
    alert(`${method.name} removed`)
  }
}

const payNow = (schedule) => { alert(`Payment processing for ${schedule.id}`) }
const submitPaymentMethod = () => {
  alert('Payment method added successfully')
  showAddPayment.value = false
}

const downloadReceipt = (transaction) => { alert(`Downloading receipt for ${transaction.id}`) }

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => { fetchPayments() })
</script>

<style scoped>
.payments-layout {
  display: flex;
  height: 100vh;
  background-color: #f0f2f5;
}

.payments-container {
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
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s;
}

.btn-new:hover {
  background: #2563eb;
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

.payments-content {
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

.stat-icon.methods { color: #3b82f6; }
.stat-icon.total { color: #8b5cf6; }
.stat-icon.spent { color: #059669; }
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

.methods-section,
.transactions-section,
.schedules-section {
  margin-bottom: 30px;
}

.methods-section h2,
.transactions-section h2,
.schedules-section h2 {
  font-size: 20px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.methods-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.method-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
  transition: all 0.3s;
}

.method-card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
  border-color: #3b82f6;
}

.method-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
  justify-content: space-between;
}

.method-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-card { background: #dbeafe; color: #1e40af; }
.icon-bank { background: #f0fdf4; color: #15803d; }
.icon-mobile { background: #fef3c7; color: #92400e; }

.method-icon svg {
  width: 24px;
  height: 24px;
}

.method-info {
  flex: 1;
}

.method-info h3 {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.method-detail {
  font-size: 12px;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.default-badge {
  background: #dcfce7;
  color: #166534;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.method-body {
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.method-text {
  font-size: 13px;
  color: #4b5563;
  margin: 0;
}

.method-actions {
  display: flex;
  gap: 8px;
}

.btn-action {
  flex: 1;
  padding: 8px 12px;
  background: #f3f4f6;
  color: #4b5563;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.2s;
}

.btn-action:hover {
  background: #e5e7eb;
  border-color: #3b82f6;
  color: #3b82f6;
}

.btn-action.btn-danger:hover {
  background: #fee2e2;
  border-color: #ef4444;
  color: #ef4444;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filter-group {
  display: flex;
  gap: 12px;
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

.transactions-list {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.transaction-item {
  display: flex;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
  transition: background 0.2s;
  cursor: pointer;
}

.transaction-item:hover {
  background: #f9fafb;
}

.transaction-item:last-child {
  border-bottom: none;
}

.trans-left {
  flex-shrink: 0;
  margin-right: 16px;
}

.transaction-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.icon-purchase { background: #3b82f6; }
.icon-payment { background: #8b5cf6; }
.icon-refund { background: #10b981; }

.trans-center {
  flex: 1;
}

.transaction-info h3 {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.transaction-meta {
  font-size: 12px;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.trans-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
}

.transaction-amount {
  font-size: 14px;
  font-weight: 700;
}

.amount-purchase { color: #ef4444; }
.amount-payment { color: #ef4444; }
.amount-refund { color: #10b981; }

.transaction-status {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
}

.status-completed { background: #d1fae5; color: #065f46; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-failed { background: #fee2e2; color: #991b1b; }

.schedules-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.schedule-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e5e7eb;
}

.schedule-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.schedule-header h3 {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.schedule-id {
  font-size: 12px;
  color: #6b7280;
  margin: 4px 0 0 0;
}

.amount-badge {
  background: #dbeafe;
  color: #1e40af;
  padding: 8px 12px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 14px;
}

.schedule-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
}

.schedule-detail {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}

.schedule-detail .label {
  color: #6b7280;
  font-weight: 600;
}

.schedule-detail .value {
  color: #1f2937;
  font-weight: 600;
}

.schedule-actions {
  display: flex;
}

.btn-pay {
  flex: 1;
  padding: 10px;
  background: #3b82f6;
  color: white;
  border: none;
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

.btn-pay:hover {
  background: #2563eb;
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

.modal-body.receipt {
  background: #f9fafb;
}

.receipt {
  background: white;
  border: 2px dashed #e5e7eb;
  border-radius: 8px;
  padding: 24px;
}

.receipt-header {
  text-align: center;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 2px solid #e5e7eb;
}

.receipt-id,
.receipt-date {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
}

.receipt-id {
  font-weight: 700;
  color: #1f2937;
}

.receipt-item {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 20px;
  padding: 16px;
  background: #f3f4f6;
  border-radius: 6px;
}

.receipt-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.receipt-row {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
}

.receipt-row span:first-child {
  color: #6b7280;
  font-weight: 600;
}

.receipt-row span:last-child {
  color: #1f2937;
  font-weight: 600;
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
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group.checkbox {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 20px;
}

.form-group.checkbox input {
  width: auto;
  margin: 0;
}

.form-group.checkbox label {
  margin: 0;
  font-size: 13px;
  color: #4b5563;
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
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
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
  .payments-container {
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

  .methods-grid {
    grid-template-columns: 1fr;
  }

  .schedules-list {
    grid-template-columns: 1fr;
  }

  .filter-group {
    width: 100%;
    margin-top: 12px;
  }

  .filter-select {
    flex: 1;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .modal-content {
    width: 95%;
  }
}
</style>
