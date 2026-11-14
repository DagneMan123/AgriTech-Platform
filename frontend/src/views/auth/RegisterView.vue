<template>
  <div class="auth-container" :class="{ 'light': isLight, 'dark': !isLight }">
    <!-- Left Hero Section -->
    <div class="hero-section">
      <div class="hero-image-overlay"></div>
      <div class="hero-content">
        <span class="hero-badge">Smart Farming Platform</span>
        <h2>Join the<br />Ecosystem.</h2>
        <p>Connecting Ethiopian farmers, buyers, suppliers, and transporters in one transparent digital marketplace.</p>

        <div class="hero-highlights">
          <div class="highlight-item">
            <span class="highlight-icon">🌾</span>
            <div>
              <strong>Direct Farm Trade</strong>
              <p>Sell harvests without intermediaries at fair market value.</p>
            </div>
          </div>
          <div class="highlight-item">
            <span class="highlight-icon">🛡️</span>
            <div>
              <strong>Chapa Escrow Protection</strong>
              <p>Automated payment holding until produce inspection & delivery.</p>
            </div>
          </div>
          <div class="highlight-item">
            <span class="highlight-icon">🚚</span>
            <div>
              <strong>End-to-End Logistics</strong>
              <p>Coordinated regional transport from farmgate to warehouse.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Form Section -->
    <div class="form-section">
      <!-- Theme Switch Corner -->
      <div class="theme-toggle-corner">
        <button 
          type="button" 
          class="icon-theme-btn" 
          @click="toggleTheme" 
          :title="isLight ? 'Switch to Dark Mode' : 'Switch to Light Mode'"
        >
          <span class="theme-icon">{{ isLight ? '☀️' : '🌙' }}</span>
        </button>
      </div>

      <div class="form-card">
        <!-- Top Green Accent Bar -->
        <div class="form-card-top-bar"></div>

        <div class="form-container">
          <!-- Header -->
          <div class="form-header">
            <div class="logo-box">
              <span class="logo-icon">🌾</span> AgriTech
            </div>
            <h2 class="form-title">Create an Account</h2>
            <p class="form-subtitle">Choose your primary role and join the community.</p>
          </div>

          <!-- Role Selection -->
          <div class="role-selection-wrapper">
            <label class="form-label">Select Your Role</label>
            <div class="role-grid">
              <button
                type="button"
                v-for="r in availableRoles"
                :key="r.id"
                class="role-card"
                :class="{ 'active': form.role === r.id }"
                @click="form.role = r.id"
              >
                <div class="role-icon">{{ r.icon }}</div>
                <div class="role-info">
                  <span class="role-name">{{ r.label }}</span>
                  <span class="role-desc">{{ r.desc }}</span>
                </div>
                <div class="role-check" v-if="form.role === r.id">✓</div>
              </button>
            </div>
          </div>

          <!-- Registration Form -->
          <form @submit.prevent="handleRegister" class="register-form">
            <!-- Full Name -->
            <div class="form-group">
              <label for="name" class="form-label">Full Name</label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18">
                  <path fill="currentColor" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <input
                  id="name"
                  v-model="form.full_name"
                  type="text"
                  required
                  class="form-input"
                  placeholder="e.g. Abebe Bikila"
                />
              </div>
            </div>

            <!-- Email & Phone (Grid) -->
            <div class="form-row-2">
              <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18">
                    <path fill="currentColor" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                  </svg>
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    class="form-input"
                    placeholder="name@example.com"
                  />
                </div>
              </div>

              <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18">
                    <path fill="currentColor" d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                  </svg>
                  <input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    required
                    class="form-input"
                    placeholder="0911223344"
                  />
                </div>
              </div>
            </div>

            <!-- Region Selection -->
            <div class="form-group">
              <label for="region" class="form-label">Region / Location</label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18">
                  <path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
                <select
                  id="region"
                  v-model="form.region"
                  class="form-input form-select"
                >
                  <option value="" disabled>Select your regional location</option>
                  <option v-for="reg in ethiopianRegions" :key="reg" :value="reg">
                    {{ reg }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Password & Confirm Password (Grid) -->
            <div class="form-row-2">
              <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18">
                    <path fill="currentColor" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                  </svg>
                  <input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    minlength="6"
                    class="form-input password-input"
                    placeholder="Min 6 characters"
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    @click="showPassword = !showPassword"
                    tabindex="-1"
                  >
                    <svg viewBox="0 0 24 24" width="18" height="18">
                      <path fill="currentColor" :d="showPassword ? 'M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z' : 'M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.44-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z'"/>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-wrapper">
                  <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18">
                    <path fill="currentColor" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                  </svg>
                  <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    required
                    minlength="6"
                    class="form-input password-input"
                    placeholder="Repeat password"
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    @click="showConfirmPassword = !showConfirmPassword"
                    tabindex="-1"
                  >
                    <svg viewBox="0 0 24 24" width="18" height="18">
                      <path fill="currentColor" :d="showConfirmPassword ? 'M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z' : 'M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.44-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z'"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Terms & Agreement -->
            <div class="terms-agreement">
              <label class="terms-checkbox-label">
                <input 
                  type="checkbox" 
                  v-model="form.agreeTerms" 
                  required 
                  class="terms-checkbox"
                />
                <span class="terms-text">
                  I agree to the <a href="#" class="terms-link">Terms of Service</a> and <a href="#" class="terms-link">Privacy Policy</a>
                </span>
              </label>
            </div>

            <!-- Error Box -->
            <div v-if="error" class="error-box">
              {{ error }}
            </div>

            <!-- Success Box -->
            <div v-if="successMsg" class="success-box">
              {{ successMsg }}
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="loading"
              class="btn-register"
            >
              <span v-if="!loading">Create Account ↪</span>
              <span v-else class="loading-spinner"></span>
            </button>
          </form>

          <!-- Footer Switch to Login -->
          <div class="form-footer">
            <p class="footer-text">
              Already have an account?
              <router-link to="/auth/login" class="login-link">Sign In</router-link>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useTheme } from '@/composables/useTheme'

const router = useRouter()
const authStore = useAuthStore()
const { isLight, toggleTheme } = useTheme()

const availableRoles = [
  { id: 'farmer', label: 'Farmer', icon: '🌾', desc: 'Sell produce & track farm' },
  { id: 'buyer', label: 'Buyer', icon: '🛒', desc: 'Source & bulk escrow orders' },
  { id: 'supplier', label: 'Supplier', icon: '📦', desc: 'Agricultural inputs & tools' },
  { id: 'transport', label: 'Transporter', icon: '🚚', desc: 'Haulage & delivery fleet' },
  { id: 'cooperative', label: 'Cooperative', icon: '🏢', desc: 'Union & bulk member sales' },
  { id: 'expert', label: 'Expert', icon: '🔬', desc: 'Agronomy consultations' },
]

const ethiopianRegions = [
  'Addis Ababa',
  'Oromia',
  'Amhara',
  'Sidama',
  'SNNPR',
  'Tigray',
  'Somali',
  'Afar',
  'Benishangul-Gumuz',
  'Harari',
  'Dire Dawa',
  'South West Ethiopia'
]

const form = ref({
  full_name: '',
  email: '',
  phone: '',
  role: 'farmer',
  region: 'Addis Ababa',
  password: '',
  password_confirmation: '',
  agreeTerms: true
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)
const successMsg = ref<string | null>(null)

const handleRegister = async () => {
  if (loading.value) return

  error.value = null
  successMsg.value = null

  // Basic client-side validation
  if (!form.value.full_name.trim()) {
    error.value = 'Please enter your full name.'
    return
  }

  if (form.value.password.length < 6) {
    error.value = 'Password must be at least 6 characters long.'
    return
  }

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match. Please verify both fields.'
    return
  }

  if (!form.value.agreeTerms) {
    error.value = 'You must agree to the Terms of Service to create an account.'
    return
  }

  loading.value = true

  try {
    const user = await authStore.register({
      full_name: form.value.full_name.trim(),
      email: form.value.email.trim(),
      phone: form.value.phone.trim(),
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
      role: form.value.role,
      region: form.value.region,
      address: form.value.region
    })

    successMsg.value = 'Account created successfully! Redirecting to your dashboard...'

    const roleRoutes: Record<string, string> = {
      admin: '/admin/dashboard',
      farmer: '/farmer/dashboard',
      buyer: '/buyer/marketplace',
      supplier: '/supplier/dashboard',
      transport: '/transport/dashboard',
      expert: '/expert/dashboard',
      cooperative: '/cooperative/dashboard',
      financial: '/financial/dashboard'
    }

    const redirectUrl = roleRoutes[user?.role || form.value.role] || '/buyer/marketplace'
    setTimeout(() => {
      router.push(redirectUrl)
    }, 600)
  } catch (err: any) {
    if (err.response?.data?.errors) {
      const errs = err.response.data.errors
      const firstField = Object.keys(errs)[0]
      error.value = errs[firstField][0] || err.response.data.message || 'Registration failed.'
    } else {
      error.value = err.response?.data?.message || err.message || 'Registration failed. Please check your information.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-container {
  display: grid;
  grid-template-columns: 1fr 1.25fr;
  min-height: 100vh;
  background-color: #0b0f17;
  color: #fff;
  transition: all 0.3s ease;
}

/* Hero Section */
.hero-section {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 4rem 3.5rem;
  background-image: url('https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1600&auto=format&fit=crop&q=80');
  background-size: cover;
  background-position: center;
  position: relative;
  overflow: hidden;
}

.hero-image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to top, rgba(11, 15, 23, 0.96) 10%, rgba(11, 15, 23, 0.75) 60%, rgba(11, 15, 23, 0.5) 100%);
}

.hero-content {
  position: relative;
  z-index: 10;
  max-width: 520px;
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
  margin-bottom: 1.25rem;
}

.hero-content h2 {
  font-size: 3.25rem;
  font-weight: 800;
  color: white;
  line-height: 1.1;
  margin-bottom: 0.9rem;
  letter-spacing: -0.02em;
}

.hero-content p {
  color: #cbd5e1;
  font-size: 1.05rem;
  line-height: 1.5;
  margin-bottom: 2rem;
}

.hero-highlights {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 1.75rem;
}

.highlight-item {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.highlight-icon {
  font-size: 1.35rem;
  background: rgba(255, 255, 255, 0.08);
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.highlight-item strong {
  display: block;
  color: #fff;
  font-size: 0.95rem;
  margin-bottom: 0.2rem;
}

.highlight-item p {
  color: #94a3b8;
  font-size: 0.85rem;
  margin: 0;
}

/* Form Section */
.form-section {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 2.5rem 2rem;
  background-color: #0b0f17;
  position: relative;
  transition: background-color 0.3s ease;
  overflow-y: auto;
}

.theme-toggle-corner {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
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
  max-width: 580px;
  background-color: #111827;
  border: 1px solid #1f2937;
  border-radius: 0.85rem;
  overflow: hidden;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
  transition: all 0.3s ease;
  margin: 1.5rem 0;
}

.form-card-top-bar {
  height: 4px;
  background: linear-gradient(90deg, #10b981, #059669);
  width: 100%;
}

.form-container {
  padding: 2.25rem;
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
  margin-bottom: 1rem;
}

.form-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.35rem;
  color: #fff;
}

.form-subtitle {
  font-size: 0.88rem;
  color: #9ca3af;
  margin-bottom: 1rem;
}

/* Role Selector Grid */
.role-selection-wrapper {
  margin-bottom: 1.25rem;
}

.role-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.6rem;
  margin-top: 0.4rem;
}

.role-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 0.75rem;
  border: 1px solid #1f2937;
  background-color: #0b0f17;
  border-radius: 0.6rem;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
}

.role-card:hover {
  border-color: #374151;
  transform: translateY(-1px);
}

.role-card.active {
  border-color: #10b981;
  background-color: rgba(16, 185, 129, 0.08);
  box-shadow: 0 0 0 1px #10b981;
}

.role-icon {
  font-size: 1.35rem;
  margin-bottom: 0.3rem;
}

.role-name {
  display: block;
  font-size: 0.85rem;
  font-weight: 700;
  color: #f1f5f9;
}

.role-desc {
  display: block;
  font-size: 0.68rem;
  color: #94a3b8;
  line-height: 1.2;
  margin-top: 0.15rem;
}

.role-check {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  font-size: 0.75rem;
  font-weight: bold;
  background: #10b981;
  color: #052e16;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Register Form Inputs */
.register-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.form-label {
  font-size: 0.82rem;
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
  left: 0.85rem;
  color: #6b7280;
  pointer-events: none;
}

.form-input {
  width: 100%;
  padding: 0.65rem 2.4rem 0.65rem 2.4rem;
  border: 1px solid #1f2937;
  border-radius: 0.5rem;
  background-color: #0b0f17;
  color: #fff;
  font-size: 0.88rem;
  transition: all 0.2s ease;
}

.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.85rem center;
  background-size: 1rem;
  cursor: pointer;
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

/* Terms */
.terms-agreement {
  margin-top: 0.25rem;
}

.terms-checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.terms-checkbox {
  accent-color: #10b981;
  width: 15px;
  height: 15px;
  cursor: pointer;
}

.terms-text {
  font-size: 0.8rem;
  color: #94a3b8;
}

.terms-link {
  color: #10b981;
  text-decoration: none;
}

.terms-link:hover {
  text-decoration: underline;
}

/* Notification Boxes */
.error-box {
  padding: 0.65rem 0.85rem;
  background-color: rgba(220, 38, 38, 0.2);
  border: 1px solid rgba(220, 38, 38, 0.4);
  border-radius: 0.5rem;
  color: #fca5a5;
  font-size: 0.82rem;
  line-height: 1.35;
}

.success-box {
  padding: 0.65rem 0.85rem;
  background-color: rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(16, 185, 129, 0.4);
  border-radius: 0.5rem;
  color: #6ee7b7;
  font-size: 0.82rem;
  line-height: 1.35;
}

/* Register Button */
.btn-register {
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
  margin-top: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-register:hover:not(:disabled) {
  background-color: #059669;
  color: #fff;
  transform: translateY(-1px);
}

.btn-register:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading-spinner {
  display: inline-block;
  width: 1.1rem;
  height: 1.1rem;
  border: 2px solid rgba(0, 0, 0, 0.3);
  border-radius: 50%;
  border-top-color: #052e16;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Footer Link */
.form-footer {
  text-align: center;
  margin-top: 1.5rem;
  padding-top: 1.25rem;
  border-top: 1px solid #1f2937;
}

.footer-text {
  font-size: 0.85rem;
  color: #94a3b8;
  margin: 0;
}

.login-link {
  color: #10b981;
  font-weight: 600;
  text-decoration: none;
  margin-left: 0.3rem;
}

.login-link:hover {
  text-decoration: underline;
}

/* =========================================
   DARK MODE STYLES (DEFAULT)
========================================= */
.auth-container.dark {
  background-color: #0b0f17;
  color: #fff;
}

.auth-container.dark .form-section {
  background-color: #0b0f17;
}

.auth-container.dark .icon-theme-btn {
  background-color: #111827;
  border-color: #1f2937;
}

.auth-container.dark .form-card {
  background-color: #111827;
  border-color: #1f2937;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
}

.auth-container.dark .logo-box {
  background-color: #0b0f17;
  border-color: #1f2937;
  color: #10b981;
}

.auth-container.dark .form-title {
  color: #fff;
}

.auth-container.dark .form-subtitle {
  color: #9ca3af;
}

.auth-container.dark .form-label {
  color: #d1d5db;
}

.auth-container.dark .form-input {
  background-color: #0b0f17;
  color: #fff;
  border-color: #1f2937;
}

.auth-container.dark .form-input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.auth-container.dark .form-footer {
  border-top-color: #1f2937;
}

/* Autofill Fix for Dark Mode */
.auth-container.dark .form-input:-webkit-autofill,
.auth-container.dark .form-input:-webkit-autofill:hover,
.auth-container.dark .form-input:-webkit-autofill:focus,
.auth-container.dark .form-input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 1000px #0b0f17 inset !important;
  -webkit-text-fill-color: #fff !important;
  caret-color: #fff !important;
  transition: background-color 5000s ease-in-out 0s;
}

/* =========================================
   LIGHT MODE STYLES
========================================= */
.auth-container.light {
  background-color: #f8fafc;
  color: #0f172a;
}

.auth-container.light .form-section {
  background-color: #f8fafc;
}

.auth-container.light .icon-theme-btn {
  background-color: #ffffff;
  border-color: #cbd5e1;
}

.auth-container.light .form-card {
  background-color: #ffffff;
  border-color: #e2e8f0;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

.auth-container.light .logo-box {
  background-color: #f1f5f9;
  border-color: #e2e8f0;
  color: #059669;
}

.auth-container.light .form-title {
  color: #0f172a;
}

.auth-container.light .form-subtitle {
  color: #64748b;
}

.auth-container.light .role-card {
  background-color: #f8fafc;
  border-color: #e2e8f0;
}

.auth-container.light .role-card:hover {
  border-color: #cbd5e1;
}

.auth-container.light .role-card.active {
  background-color: #ecfdf5;
  border-color: #10b981;
}

.auth-container.light .role-name {
  color: #0f172a;
}

.auth-container.light .role-desc {
  color: #64748b;
}

.auth-container.light .form-label {
  color: #334155;
}

.auth-container.light .form-input {
  background-color: #f8fafc;
  color: #0f172a;
  border-color: #cbd5e1;
}

.auth-container.light .form-input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.auth-container.light .terms-text {
  color: #64748b;
}

.auth-container.light .form-footer {
  border-top-color: #e2e8f0;
}

.auth-container.light .footer-text {
  color: #64748b;
}

/* Autofill Fix for Light Mode */
.auth-container.light .form-input:-webkit-autofill,
.auth-container.light .form-input:-webkit-autofill:hover,
.auth-container.light .form-input:-webkit-autofill:focus,
.auth-container.light .form-input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 1000px #f8fafc inset !important;
  -webkit-text-fill-color: #0f172a !important;
  caret-color: #0f172a !important;
}

/* Responsive */
@media (max-width: 992px) {
  .auth-container {
    grid-template-columns: 1fr;
  }
  .hero-section {
    display: none;
  }
  .role-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 540px) {
  .form-row-2 {
    grid-template-columns: 1fr;
  }
  .role-grid {
    grid-template-columns: 1fr;
  }
  .form-container {
    padding: 1.5rem;
  }
}
</style>
