<template>
  <div class="auth-container" :class="{ 'light': isLight, 'dark': !isLight }">
    
    <!-- Hero Section -->
    <div class="hero-section">
      <div class="hero-image-overlay"></div>
      <div class="hero-content">
        <span class="hero-badge">Smart Farming Platform</span>
        <h2>Account<br />Recovery.</h2>
        <p>No worries! We'll help you get back to your AgriConnect dashboard safely.</p>
      </div>
    </div>

    <!-- Form Section -->
    <div class="form-section">
      
      <!-- Theme Toggle Corner -->
      <div class="theme-toggle-corner">
        <button type="button" class="icon-theme-btn" @click="toggleTheme" :title="isLight ? 'Switch to Dark' : 'Switch to Light'">
          <span class="theme-icon">{{ isLight ? '☀️' : '🌙' }}</span>
        </button>
      </div>

      <div class="form-card">
        <!-- Top Green Accent Bar -->
        <div class="form-card-top-bar"></div>

        <div class="form-container">
          
          <!-- Reset Form Card (When token & success are normal) -->
          <template v-if="!resetSuccess && !invalidToken">
            <div class="form-header">
              <div class="logo-box">
                <span class="logo-icon">🌾</span> AgriTech
              </div>
              <h2 class="form-title">Reset Password?</h2>
              <p class="form-subtitle">Enter your new password below to secure your account.</p>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="error-box">
              {{ error }}
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="success-box">
              {{ successMessage }}
            </div>

            <form @submit.prevent="handleResetPassword" class="login-form">
              
              <!-- Email Display Field (Read-only) -->
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18">
                    <path fill="currentColor" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                  </svg>
                  <input
                    type="email"
                    :value="form.email"
                    disabled
                    class="form-input opacity-75 cursor-not-allowed"
                  />
                </div>
              </div>

              <!-- New Password -->
              <div class="form-group">
                <label for="password" class="form-label">New Password</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18">
                    <path fill="currentColor" d="M12 1C6.48 1 2 5.48 2 11c0 1.54.36 3 .97 4.29C2.35 16.29 2 17.12 2 18v2c0 1.1.9 2 2 2h1v-2H4v-2c0-.55.45-1 1-1h16c1.1 0 2-.9 2-2v-6c0-5.52-4.48-10-10-10zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 7 15.5 7 14 7.67 14 8.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 7 8.5 7 7 7.67 7 8.5 7.67 10 8.5 10z"/>
                  </svg>
                  <input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    minlength="8"
                    class="form-input password-input"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    @click="showPassword = !showPassword"
                  >
                    <svg viewBox="0 0 24 24" width="18" height="18">
                      <path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Confirm Password -->
              <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18">
                    <path fill="currentColor" d="M12 1C6.48 1 2 5.48 2 11c0 1.54.36 3 .97 4.29C2.35 16.29 2 17.12 2 18v2c0 1.1.9 2 2 2h1v-2H4v-2c0-.55.45-1 1-1h16c1.1 0 2-.9 2-2v-6c0-5.52-4.48-10-10-10zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 7 15.5 7 14 7.67 14 8.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 7 8.5 7 7 7.67 7 8.5 7.67 10 8.5 10z"/>
                  </svg>
                  <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    required
                    minlength="8"
                    class="form-input password-input"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <svg viewBox="0 0 24 24" width="18" height="18">
                      <path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                    </svg>
                  </button>
                </div>
              </div>

              <button
                type="submit"
                :disabled="loading"
                class="btn-sign-in"
              >
                <span v-if="!loading">Reset Password ↪</span>
                <span v-else class="loading-spinner"></span>
              </button>
            </form>

            <div class="text-center mt-4">
              <router-link to="/auth/login" class="forgot-link">
                ← Back to Login
              </router-link>
            </div>
          </template>

          <!-- Success State -->
          <template v-else-if="resetSuccess">
            <div class="form-header text-center">
              <div class="text-4xl mb-3 text-emerald-500">✓</div>
              <h2 class="form-title">Success!</h2>
              <p class="form-subtitle">Your password has been successfully reset. You can now log in.</p>
              <router-link to="/auth/login" class="btn-sign-in inline-block text-center mt-4 text-decoration-none">
                Sign In Now
              </router-link>
            </div>
          </template>

          <!-- Invalid Token / 404 State -->
          <template v-else>
            <div class="form-header text-center">
              <div class="text-3xl font-bold text-red-500 mb-2">404</div>
              <h2 class="form-title">Link Expired</h2>
              <p class="form-subtitle">This password reset link is invalid or has expired.</p>
              <router-link to="/auth/forgot-password" class="btn-sign-in inline-block text-center mt-4 text-decoration-none">
                Request New Link
              </router-link>
            </div>
          </template>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTheme } from '@/composables/useTheme'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const { isLight, toggleTheme } = useTheme()

const form = ref({
  token: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)
const successMessage = ref<string | null>(null)
const resetSuccess = ref(false)
const invalidToken = ref(false)

onMounted(() => {
  form.value.token = (route.query.token as string) || ''
  form.value.email = (route.query.email as string) || ''

  if (!form.value.token || !form.value.email) {
    invalidToken.value = true
  }
})

const handleResetPassword = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match.'
    return
  }

  loading.value = true
  error.value = null

  try {
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/auth/reset-password`, {
      token: form.value.token,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    })

    successMessage.value = response.data.message || 'Password reset successful!'
    resetSuccess.value = true

    setTimeout(() => {
      router.push('/auth/login')
    }, 2500)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to reset password. Please try again.'
    if (err.response?.status === 422) {
      invalidToken.value = true
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ከላይ ያለው የ Sign In ፔጅዎ CSS ፍጹም በሆነ መልኩ እዚህም ተካቷል */
.auth-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  height: 100vh;
  background-color: #0b0f17;
  color: #fff;
  transition: all 0.3s ease;
}

.hero-section {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 4rem;
  background-image: url('https://www.digi.com/getattachment/Blog/post/IoT-in-Agriculture/GettyImages-2167394255-1080x720.jpg?lang=en-US');
  background-size: cover;
  background-position: center;
  position: relative;
  overflow: hidden;
}

.hero-image-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: linear-gradient(to top, rgba(11, 15, 23, 0.95), rgba(11, 15, 23, 0.4) 60%);
}

.hero-content {
  position: relative;
  z-index: 10;
}

.hero-badge {
  display: inline-block;
  background-color: rgba(16, 185, 129, 0.2);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.4);
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.hero-content h2 {
  font-size: 3.5rem;
  font-weight: 800;
  color: white;
  line-height: 1.1;
  margin-bottom: 0.75rem;
}

.hero-content p {
  color: #cbd5e1;
  font-size: 1rem;
}

.form-section {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 2rem;
  background-color: #0b0f17;
  position: relative;
  transition: background-color 0.3s ease;
}

.theme-toggle-corner {
  position: absolute;
  top: 2rem;
  right: 2rem;
}

.icon-theme-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid #1f2937;
  background-color: #111827;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.1rem;
  transition: all 0.2s ease;
}

.icon-theme-btn:hover {
  background-color: #1f2937;
  border-color: #10b981;
}

.form-card {
  width: 100%;
  max-width: 400px;
  background-color: #111827;
  border: 1px solid #1f2937;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
  transition: all 0.3s ease;
}

.form-card-top-bar {
  height: 4px;
  background: linear-gradient(90deg, #10b981, #059669);
  width: 100%;
}

.form-container {
  padding: 2rem;
}

.form-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.logo-box {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background-color: #0b0f17;
  color: #10b981;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  font-weight: bold;
  font-size: 1.05rem;
  border: 1px solid #1f2937;
  margin-bottom: 1.25rem;
}

.form-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.4rem;
  color: #fff;
}

.form-subtitle {
  font-size: 0.85rem;
  color: #9ca3af;
  margin-bottom: 1.5rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-label {
  font-size: 0.85rem;
  font-weight: 500;
  color: #d1d5db;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 0.9rem;
  color: #6b7280;
  pointer-events: none;
}

.form-input {
  width: 100%;
  padding: 0.7rem 2.6rem 0.7rem 2.5rem;
  border: 1px solid #1f2937;
  border-radius: 0.5rem;
  background-color: #0b0f17;
  color: #fff;
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.password-toggle-btn {
  position: absolute;
  right: 0.75rem;
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.password-toggle-btn:hover {
  color: #10b981;
}

.forgot-link {
  font-size: 0.8rem;
  color: #10b981;
  text-decoration: none;
}

.forgot-link:hover {
  text-decoration: underline;
}

.error-box {
  padding: 0.6rem;
  background-color: rgba(220, 38, 38, 0.2);
  border: 1px solid rgba(220, 38, 38, 0.4);
  border-radius: 0.5rem;
  color: #fca5a5;
  font-size: 0.8rem;
  margin-bottom: 1rem;
}

.success-box {
  padding: 0.6rem;
  background-color: rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(16, 185, 129, 0.4);
  border-radius: 0.5rem;
  color: #6ee7b7;
  font-size: 0.8rem;
  margin-bottom: 1rem;
}

.btn-sign-in {
  width: 100%;
  padding: 0.75rem 1rem;
  background-color: #10b981;
  color: #052e16;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 0.5rem;
}

.btn-sign-in:hover:not(:disabled) {
  background-color: #059669;
  color: #fff;
}

.loading-spinner {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(0, 0, 0, 0.3);
  border-radius: 50%;
  border-top-color: #052e16;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Dark Mode Styles */
.auth-container.dark {
  background-color: #0b0f17;
  color: #fff;
}
.auth-container.dark .form-section { background-color: #0b0f17; }
.auth-container.dark .icon-theme-btn { background-color: #111827; border-color: #1f2937; }
.auth-container.dark .form-card { background-color: #111827; border-color: #1f2937; }
.auth-container.dark .logo-box { background-color: #0b0f17; border-color: #1f2937; color: #10b981; }
.auth-container.dark .form-title { color: #fff; }
.auth-container.dark .form-subtitle { color: #9ca3af; }
.auth-container.dark .form-label { color: #d1d5db; }
.auth-container.dark .form-input { background-color: #0b0f17; color: #fff; border-color: #1f2937; }

/* Light Mode Styles */
.auth-container.light {
  background-color: #f8fafc;
  color: #0f172a;
}
.auth-container.light .form-section { background-color: #f8fafc; }
.auth-container.light .icon-theme-btn { background-color: #ffffff; border-color: #cbd5e1; }
.auth-container.light .form-card { background-color: #ffffff; border-color: #e2e8f0; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); }
.auth-container.light .logo-box { background-color: #f1f5f9; border-color: #e2e8f0; }
.auth-container.light .form-title { color: #0f172a; }
.auth-container.light .form-subtitle { color: #64748b; }
.auth-container.light .form-label { color: #334155; }
.auth-container.light .form-input { background-color: #f8fafc; color: #0f172a; border-color: #cbd5e1; }

@media (max-width: 768px) {
  .auth-container {
    grid-template-columns: 1fr;
  }
  .hero-section {
    display: none;
  }
}
</style>