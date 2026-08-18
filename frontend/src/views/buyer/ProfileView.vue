<template>
  <div class="buyer-layout">
    <BuyerSidebar @logout="handleLogout" />
    <div class="buyer-page">
      <div class="page-header">
        <h1>Profile</h1>
        <p>Manage your account information</p>
      </div>

      <div class="content-section">
        <h2>Personal Information</h2>
        <form @submit.prevent="updateProfile" class="form">
          <div class="form-row">
            <div class="form-group">
              <label>Full Name</label>
              <input v-model="profile.name" type="text" required />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="profile.email" type="email" required />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Phone</label>
              <input v-model="profile.phone" type="tel" />
            </div>
            <div class="form-group">
              <label>City</label>
              <input v-model="profile.city" type="text" />
            </div>
          </div>

          <div class="form-group">
            <label>Address</label>
            <textarea v-model="profile.address" rows="3"></textarea>
          </div>

          <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
      </div>

      <div class="content-section">
        <h2>Account Statistics</h2>
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Total Orders</div>
              <div class="stat-value">{{ stats.total_orders || 0 }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon" style="background: #dbeafe;">
              <i class="fas fa-star" style="color: #3b82f6;"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Reviews</div>
              <div class="stat-value">{{ stats.total_reviews || 0 }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7;">
              <i class="fas fa-bookmark" style="color: #f59e0b;"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Wishlist Items</div>
              <div class="stat-value">{{ stats.wishlist_count || 0 }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5;">
              <i class="fas fa-credit-card" style="color: #10b981;"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Total Spent</div>
              <div class="stat-value">{{ formatCurrency(stats.total_spent) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="content-section">
        <h2>Security</h2>
        <div class="security-section">
          <div class="security-item">
            <div class="security-info">
              <h4>Change Password</h4>
              <p>Update your password to keep your account secure</p>
            </div>
            <button @click="showPasswordForm = !showPasswordForm" class="btn btn-secondary">
              {{ showPasswordForm ? 'Cancel' : 'Change Password' }}
            </button>
          </div>

          <form v-if="showPasswordForm" @submit.prevent="changePassword" class="form password-form">
            <div class="form-group">
              <label>Current Password</label>
              <input v-model="passwordForm.current" type="password" required />
            </div>
            <div class="form-group">
              <label>New Password</label>
              <input v-model="passwordForm.new" type="password" required />
            </div>
            <div class="form-group">
              <label>Confirm Password</label>
              <input v-model="passwordForm.confirm" type="password" required />
            </div>
            <button type="submit" class="btn btn-primary">Update Password</button>
          </form>
        </div>
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

const profile = ref({
  name: '',
  email: '',
  phone: '',
  city: '',
  address: ''
})

const stats = ref({
  total_orders: 0,
  total_reviews: 0,
  wishlist_count: 0,
  total_spent: 0
})

const showPasswordForm = ref(false)
const passwordForm = ref({
  current: '',
  new: '',
  confirm: ''
})

const loadProfile = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/buyer/profile', {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      profile.value = data.data || profile.value
    }
  } catch (err) {
    console.error('Error loading profile:', err)
  }
}

const loadStats = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/buyer/dashboard', {
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      stats.value = data.data || stats.value
    }
  } catch (err) {
    console.error('Error loading stats:', err)
  }
}

const updateProfile = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/buyer/profile', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(profile.value)
    })

    if (response.ok) {
      alert('Profile updated successfully')
    }
  } catch (err) {
    console.error('Error updating profile:', err)
    alert('Failed to update profile')
  }
}

const changePassword = async () => {
  if (passwordForm.value.new !== passwordForm.value.confirm) {
    alert('Passwords do not match')
    return
  }

  try {
    const response = await fetch('http://localhost:8000/api/auth/change-password', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${auth.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        current_password: passwordForm.value.current,
        password: passwordForm.value.new,
        password_confirmation: passwordForm.value.confirm
      })
    })

    if (response.ok) {
      alert('Password changed successfully')
      showPasswordForm.value = false
      passwordForm.value = { current: '', new: '', confirm: '' }
    }
  } catch (err) {
    console.error('Error changing password:', err)
    alert('Failed to change password')
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-ET', {
    style: 'currency',
    currency: 'ETB'
  }).format(amount || 0)
}

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  loadProfile()
  loadStats()
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

.form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
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

.form-group input,
.form-group textarea {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #f9fafb;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  background: #d1fae5;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #10b981;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 12px;
  color: #9ca3af;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 20px;
  font-weight: bold;
  color: #1f2937;
}

.security-section {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.security-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: #f9fafb;
}

.security-info h4 {
  margin: 0 0 4px 0;
  color: #1f2937;
  font-size: 14px;
  font-weight: 600;
}

.security-info p {
  margin: 0;
  color: #6b7280;
  font-size: 13px;
}

.password-form {
  padding: 15px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 6px;
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

.btn-secondary {
  background-color: #e5e7eb;
  color: #1f2937;
}

.btn-secondary:hover {
  background-color: #d1d5db;
}

@media (max-width: 768px) {
  .buyer-page {
    margin-left: 0;
    padding: 20px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .security-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
}
</style>
