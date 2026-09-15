<template>
  <div class="auth-container" :class="{ 'light': isLight, 'dark': !isLight }">
    <!-- Left Side - Hero Section -->
    <div class="hero-section">
      <div class="hero-image-overlay"></div>
      <div class="hero-content">
        <span class="hero-badge">Smart Farming Platform</span>
        <h2>Account<br />Recovery.</h2>
        <p>No worries! We'll help you get back to your AgriConnect dashboard safely.</p>
      </div>
    </div>

    <!-- Right Side - Forgot Password Form -->
    <div class="form-section">
      <div class="form-card">
        <!-- Top Green Accent Bar -->
        <div class="form-card-top-bar"></div>

        <div class="form-container">
          <!-- Header -->
          <div class="form-header">
            <div class="logo-box">
              <span class="logo-icon">🌾</span> AgriConnect
            </div>
            <h2 class="form-title">Forgot Password?</h2>
            <p class="form-subtitle">No problem. Enter your email address and we'll send you a link to reset your password.</p>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="error-box">
            {{ error }}
          </div>

          <!-- Success Message -->
          <div v-if="success" class="success-box">
            {{ success }}
          </div>

          <!-- Form -->
          <form @submit.prevent="handleForgotPassword" class="login-form">
            <div class="form-group">
              <label for="email" class="form-label">Email Address</label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18">
                  <path fill="currentColor" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                </svg>
                <input
                  id="email"
                  v-model="email"
                  type="email"
                  required
                  class="form-input"
                  placeholder="you@example.com"
                />
              </div>
              <span class="helper-text">We'll send the recovery link to this address</span>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="btn-sign-in"
            >
              <span v-if="!loading">Send Reset Link →</span>
              <span v-else class="loading-spinner"></span>
            </button>

            <div class="back-to-login-row">
              <router-link to="/auth/login" class="forgot-link">
                ← Back to Login
              </router-link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { useTheme } from '@/composables/useTheme'

const { isLight } = useTheme()
const email = ref('')
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)

const handleForgotPassword = async () => {
  if (!email.value) {
    error.value = 'Please enter your email address'
    return
  }

  loading.value = true
  error.value = null
  success.value = null

  try {
    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/auth/forgot-password`,
      { email: email.value }
    )

    success.value = response.data.message || 'Password reset link has been sent to your email'
    email.value = ''

    setTimeout(() => {
      success.value = null
    }, 5000)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to send reset link. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  height: 100vh;
  background-color: var(--bg-light, #0b0f17);
  color: var(--text-light-primary, #fff);
  transition: all 0.3s ease;
}

/* Hero Section */
.hero-section {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 4rem;
  background-image: url('https://images.unsplash.com/photo-1625246333195-78d9c38ad449?q=80&w=1200&auto=format&fit=crop');
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

/* Form Section */
.form-section {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 2rem;
  background-color: var(--bg-light, #0b0f17);
  position: relative;
  transition: background-color 0.3s ease;
}

.form-card {
  width: 100%;
  max-width: 400px;
  background-color: var(--bg-light-secondary, #111827);
  border: 1px solid var(--border-light, #1f2937);
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
  background-color: var(--bg-light, #0b0f17);
  color: #10b981;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  font-weight: bold;
  font-size: 1.05rem;
  border: 1px solid var(--border-light, #1f2937);
  margin-bottom: 1.25rem;
}

.form-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.4rem;
  color: var(--text-light-primary, #fff);
}

.form-subtitle {
  font-size: 0.85rem;
  color: var(--text-light-secondary, #9ca3af);
  margin-bottom: 0.5rem;
  line-height: 1.4;
}

/* Form Styles */
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
  color: var(--text-light-secondary, #d1d5db);
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 0.9rem;
  color: var(--text-light-tertiary, #6b7280);
  pointer-events: none;
}

.form-input {
  width: 100%;
  padding: 0.7rem 0.9rem 0.7rem 2.5rem;
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

/* Autofill Fix for Dark Mode */
.form-input:-webkit-autofill,
.form-input:-webkit-autofill:hover,
.form-input:-webkit-autofill:focus,
.form-input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 1000px #0b0f17 inset !important;
  -webkit-text-fill-color: #fff !important;
  caret-color: #fff !important;
  transition: background-color 5000s ease-in-out 0s;
}

.helper-text {
  font-size: 0.75rem;
  color: #6b7280;
}

.forgot-link {
  font-size: 0.85rem;
  color: #10b981;
  text-decoration: none;
  font-weight: 500;
}

.forgot-link:hover {
  text-decoration: underline;
}

.back-to-login-row {
  text-align: center;
  margin-top: 0.5rem;
}

.error-box {
  padding: 0.6rem;
  margin-bottom: 1rem;
  background-color: rgba(220, 38, 38, 0.2);
  border: 1px solid rgba(220, 38, 38, 0.4);
  border-radius: 0.5rem;
  color: #fca5a5;
  font-size: 0.8rem;
}

.success-box {
  padding: 0.6rem;
  margin-bottom: 1rem;
  background-color: rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(16, 185, 129, 0.4);
  border-radius: 0.5rem;
  color: #6ee7b7;
  font-size: 0.8rem;
}

/* Sign In Button */
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

.auth-container.dark .helper-text {
  color: #6b7280;
}

.auth-container.dark .forgot-link {
  color: #10b981;
}

.auth-container.dark .error-box {
  background-color: rgba(220, 38, 38, 0.2);
  border-color: rgba(220, 38, 38, 0.4);
  color: #fca5a5;
}

.auth-container.dark .success-box {
  background-color: rgba(16, 185, 129, 0.2);
  border-color: rgba(16, 185, 129, 0.4);
  color: #6ee7b7;
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
}

.auth-container.light .form-title {
  color: #0f172a;
}

.auth-container.light .form-subtitle {
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

.auth-container.light .helper-text {
  color: #64748b;
}

.auth-container.light .forgot-link {
  color: #10b981;
}

.auth-container.light .error-box {
  background-color: rgba(220, 38, 38, 0.1);
  border-color: rgba(220, 38, 38, 0.3);
  color: #dc2626;
}

.auth-container.light .success-box {
  background-color: rgba(16, 185, 129, 0.1);
  border-color: rgba(16, 185, 129, 0.3);
  color: #059669;
}

@media (max-width: 768px) {
  .auth-container {
    grid-template-columns: 1fr;
  }
  .hero-section {
    display: none;
  }
}
</style>