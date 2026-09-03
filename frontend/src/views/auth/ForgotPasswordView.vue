<template>
  <div class="w-full max-w-md">
    <!-- Main Form Card -->
    <div class="bg-white rounded-lg shadow-md p-8 space-y-6">
      <!-- Title -->
      <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Forgot Password?</h2>
        <p class="text-gray-600 text-sm leading-relaxed">
          No problem. Enter your email address and we'll send you a link to reset your password.
        </p>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800 text-sm">{{ error }}</p>
      </div>

      <!-- Success Message -->
      <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800 text-sm">{{ success }}</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleForgotPassword" class="space-y-6">
        <!-- Email Input -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="you@example.com"
            required
            class="input-field w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
          />
          <p class="text-xs text-gray-500 mt-1">We'll send the recovery link to this address</p>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="btn-primary w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 rounded-lg transition duration-200 flex items-center justify-center"
        >
          <span v-if="!loading">Send Reset Link →</span>
          <span v-else class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Sending...
          </span>
        </button>

        <!-- Back to Login Link -->
        <router-link
          to="/auth/login"
          class="block text-center text-sm text-green-600 hover:text-green-700 font-medium"
        >
          ← Back to Login
        </router-link>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'

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
/* ከ Login ገጽዎ ጋር ተመሳሳይ ንድፍ እንዲኖረው */
.input-field:focus {
  border-color: #16a34a;
  box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.2);
}
</style>