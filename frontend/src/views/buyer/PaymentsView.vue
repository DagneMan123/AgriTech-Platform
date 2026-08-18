<template>
  <div class="buyer-layout">
    <BuyerSidebar @logout="handleLogout" />
    <div class="buyer-page">
      <div class="page-header">
        <h1>Payments</h1>
        <p>Manage your payment methods and history</p>
      </div>

      <div class="content-section">
        <h2>Payment Methods</h2>
        <div v-if="payments.length === 0" class="empty-state">
          <i class="fas fa-credit-card"></i>
          <p>No payments yet</p>
        </div>

        <div v-if="payments.length > 0" class="payments-list">
          <div v-for="payment in payments" :key="payment.id" class="payment-item">
            <div class="payment-icon">
              <i class="fas fa-credit-card"></i>
            </div>
            <div class="payment-info">
              <h4>{{ payment.method }}</h4>
              <p>Amount: {{ formatCurrency(payment.amount) }}</p>
              <p class="payment-date">{{ formatDate(payment.created_at) }}</p>
            </div>
            <div class="payment-status">
              <span :class="['status-badge', payment.status]">{{ payment.status }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="content-section">
        <h2>Add Payment Method</h2>
        <form @submit.prevent="addPaymentMethod" class="form">
          <div class="form-group">
            <label>Card Number</label>
            <input v-model="newPayment.cardNumber" type="text" placeholder="1234 5678 9012 3456" required />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Expiry Date</label>
              <input v-model="newPayment.expiryDate" type="text" placeholder="MM/YY" required />
            </div>
            <div class="form-group">
              <label>CVV</label>
              <input v-model="newPayment.cvv" type="text" placeholder="123" required />
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Add Payment Method</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import BuyerSidebar from '@/components/Sidebar/BuyerSidebar.vue'

const router = useRouter()
const auth = useAuthStore()

const payments = ref([])
const newPayment = ref({
  cardNumber: '',
  expiryDate: '',
  cvv: ''
})

const loadPayments = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/buyer/payments', {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      payments.value = data.data || []
    }
  } catch (err) {
    console.error('Error loading payments:', err)
  }
}

const addPaymentMethod = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/buyer/payments', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newPayment.value)
    })

    if (response.ok) {
      alert('Payment method added successfully')
      newPayment.value = { cardNumber: '', expiryDate: '', cvv: '' }
      loadPayments()
    }
  } catch (err) {
    console.error('Error adding payment:', err)
    alert('Failed to add payment method')
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-ET', {
    style: 'currency',
    currency: 'ETB'
  }).format(amount || 0)
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-US')
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  loadPayments()
})
</script>

<style scoped>
.buyer-layout {
  display: flex;
  height: 100vh;
}

.buyer-page {
  margin-left: 260px;
  flex: 1;
  overflow-y: auto;
  background-color: #f9fafb;
  padding: 30px;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  font-size: 28px;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 5px;
}

.page-header p {
  color: #6b7280;
  font-size: 14px;
}

.content-section {
  background: white;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
}

.content-section h2 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 20px;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #9ca3af;
}

.empty-state i {
  font-size: 48px;
  color: #d1d5db;
  margin-bottom: 15px;
  display: block;
}

.payments-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.payment-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  transition: all 0.3s;
}

.payment-item:hover {
  border-color: #3b82f6;
  background: #eff6ff;
}

.payment-icon {
  font-size: 24px;
  color: #3b82f6;
}

.payment-info {
  flex: 1;
}

.payment-info h4 {
  margin: 0 0 4px 0;
  color: #1f2937;
  font-size: 14px;
  font-weight: 600;
}

.payment-info p {
  margin: 4px 0;
  color: #6b7280;
  font-size: 13px;
}

.payment-date {
  color: #9ca3af;
  font-size: 12px;
}

.payment-status {
  display: flex;
  align-items: center;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.completed {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.failed {
  background: #fee2e2;
  color: #991b1b;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.form-group input {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}

@media (max-width: 768px) {
  .buyer-page {
    margin-left: 0;
    padding: 20px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
