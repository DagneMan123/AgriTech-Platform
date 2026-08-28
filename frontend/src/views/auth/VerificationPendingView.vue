<template>
  <div class="verification-wrapper">
    <div class="verification-container">
      <div class="verification-icon">
        <i class="fas fa-hourglass-half"></i>
      </div>

      <h1>Verification Pending</h1>
      <p class="subtitle">Your account is pending document verification</p>

      <div class="details-section">
        <p class="user-info">
          <strong>Name:</strong> {{ user?.name }}
        </p>
        <p class="user-info">
          <strong>Role:</strong> <span class="role-badge">{{ formatRole(user?.role) }}</span>
        </p>
        <p class="user-info">
          <strong>Email:</strong> {{ user?.email || 'Not provided' }}
        </p>
      </div>

      <div class="info-section">
        <h3>What happens next?</h3>
        <ol>
          <li>Our admin team will review your submitted documents</li>
          <li>You'll receive an email notification once verification is complete</li>
          <li>Your account will be activated automatically when approved</li>
          <li>If documents need corrections, we'll notify you</li>
        </ol>
      </div>

      <div class="estimated-time">
        <p>⏱️ Estimated verification time: <strong>24-48 hours</strong></p>
      </div>

      <div class="action-section">
        <button @click="goHome" class="home-btn">
          Go to Home
        </button>
        <button @click="logout" class="logout-btn">
          Logout
        </button>
      </div>

      <div class="info-box">
        <i class="fas fa-info-circle"></i>
        <p>
          Don't close or refresh this page. You can safely logout and login later.
          Your documents are securely stored and will be reviewed.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const user = ref<any>(null)

onMounted(() => {
  user.value = authStore.user
})

const formatRole = (role: string | undefined) => {
  const roleMap: Record<string, string> = {
    farmer: '👨‍🌾 Farmer',
    buyer: '🛒 Buyer',
    supplier: '🏭 Supplier',
    transport: '🚚 Transport Provider',
    expert: '👨‍⚕️ Expert',
    financial: '🏦 Financial Institution',
    cooperative: '🤝 Cooperative',
  }
  return roleMap[role || ''] || role
}

const goHome = () => {
  router.push('/')
}

const logout = () => {
  authStore.logout()
  router.push('/auth/login')
}
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.verification-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.verification-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 500px;
  padding: 40px;
  text-align: center;
}

.verification-icon {
  font-size: 64px;
  color: #f39c12;
  margin-bottom: 20px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

h1 {
  font-size: 28px;
  color: #333;
  margin: 0 0 8px 0;
  font-weight: 700;
}

.subtitle {
  font-size: 16px;
  color: #7f8c8d;
  margin: 0 0 30px 0;
}

.details-section {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 25px;
  text-align: left;
}

.user-info {
  margin: 10px 0;
  font-size: 14px;
  color: #555;
}

.user-info strong {
  color: #333;
  margin-right: 8px;
}

.role-badge {
  display: inline-block;
  background: #27ae60;
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.info-section {
  background: #e8f5e9;
  padding: 20px;
  border-radius: 8px;
  border-left: 4px solid #27ae60;
  margin-bottom: 25px;
  text-align: left;
}

.info-section h3 {
  margin: 0 0 15px 0;
  font-size: 16px;
  color: #27ae60;
}

.info-section ol {
  margin: 0;
  padding-left: 20px;
}

.info-section li {
  margin-bottom: 10px;
  font-size: 14px;
  color: #333;
  line-height: 1.6;
}

.estimated-time {
  background: #fef3cd;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 25px;
  font-size: 15px;
  color: #856404;
  border: 1px solid #ffeaa7;
}

.estimated-time p {
  margin: 0;
}

.action-section {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
}

.home-btn,
.logout-btn {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.home-btn {
  background: #27ae60;
  color: white;
}

.home-btn:hover {
  background: #229954;
  transform: translateY(-2px);
}

.logout-btn {
  background: #ecf0f1;
  color: #7f8c8d;
}

.logout-btn:hover {
  background: #d5dbdb;
}

.info-box {
  background: #e3f2fd;
  padding: 15px;
  border-radius: 8px;
  border-left: 4px solid #2196f3;
  color: #1565c0;
  font-size: 13px;
  line-height: 1.6;
}

.info-box i {
  margin-right: 8px;
  color: #2196f3;
}

.info-box p {
  margin: 0;
}

@media (max-width: 600px) {
  .verification-container {
    padding: 30px 20px;
  }

  .verification-icon {
    font-size: 48px;
  }

  h1 {
    font-size: 24px;
  }

  .action-section {
    flex-direction: column;
  }
}
</style>
