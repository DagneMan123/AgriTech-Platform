<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <!-- Logo Section -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-green-600 mb-2">🌾 AgriConnect</h1>
        <p class="text-gray-600">Reset Your Password</p>
      </div>

      <!-- Success Message -->
      <div v-if="resetSuccess" class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
        <div class="text-center">
          <div class="text-4xl mb-3">✓</div>
          <h3 class="text-green-800 font-semibold mb-2">Password Reset Successfully!</h3>
          <p class="text-green-700 text-sm mb-4">Your password has been reset. You can now log in with your new password.</p>
          <router-link
            to="/auth/login"
            class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition"
          >
            Go to Login
          </router-link>
        </div>
      </div>

      <!-- Reset Form -->
      <form v-if="!resetSuccess" @submit.prevent="handleResetPassword" class="bg-white rounded-lg shadow-lg p-8">
        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
          {{ errorMessage }}
        </div>

        <!-- Success Alert -->
        <div v-if="successMessage" class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
          {{ successMessage }}
        </div>

        <!-- Email Display (Read-only) -->
        <div class="mb-6 p-4 bg-gray-50 border border-gray-300 rounded-lg">
          <label class="block text-gray-700 font-semibold mb-2">📧 Email Address</label>
          <p class="text-gray-800 font-medium text-lg">{{ formData.email || 'No email provided' }}</p>
          <p class="text-xs text-gray-500 mt-2">This email will be used to reset your password (read-only)</p>
        </div>

        <!-- Password Field -->
        <div class="mb-4">
          <label for="password" class="block text-gray-700 font-semibold mb-2">New Password</label>
          <div class="relative">
            <input
              :type="showPassword ? 'text' : 'password'"
              id="password"
              v-model="formData.password"
              placeholder="Enter your new password"
              required
              minlength="8"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-3 text-gray-500 hover:text-gray-700"
            >
              <span v-if="showPassword">👁️</span>
              <span v-else>👁️‍🗨️</span>
            </button>
          </div>
          <p class="text-xs text-gray-500 mt-1">At least 8 characters</p>
        </div>

        <!-- Confirm Password Field -->
        <div class="mb-6">
          <label for="password_confirmation" class="block text-gray-700 font-semibold mb-2">Confirm Password</label>
          <div class="relative">
            <input
              :type="showConfirmPassword ? 'text' : 'password'"
              id="password_confirmation"
              v-model="formData.password_confirmation"
              placeholder="Confirm your new password"
              required
              minlength="8"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
            />
            <button
              type="button"
              @click="showConfirmPassword = !showConfirmPassword"
              class="absolute right-3 top-3 text-gray-500 hover:text-gray-700"
            >
              <span v-if="showConfirmPassword">👁️</span>
              <span v-else>👁️‍🗨️</span>
            </button>
          </div>
        </div>

        <!-- Password Requirements -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
          <p class="text-sm font-semibold text-gray-700 mb-2">Password Requirements:</p>
          <ul class="text-xs text-gray-600 space-y-1">
            <li :class="{ 'text-green-600': formData.password.length >= 8 }">
              ✓ At least 8 characters
            </li>
            <li :class="{ 'text-green-600': /[A-Z]/.test(formData.password) }">
              ✓ Contains uppercase letter (A-Z)
            </li>
            <li :class="{ 'text-green-600': /[a-z]/.test(formData.password) }">
              ✓ Contains lowercase letter (a-z)
            </li>
            <li :class="{ 'text-green-600': /[0-9]/.test(formData.password) }">
              ✓ Contains number (0-9)
            </li>
          </ul>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading || !formData.password || !formData.password_confirmation"
          class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-3 rounded-full transition duration-200"
        >
          <span v-if="!isLoading">Reset Password</span>
          <span v-else class="flex items-center justify-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
          </span>
        </button>

        <!-- Back to Login -->
        <div class="text-center mt-4">
          <router-link
            to="/auth/login"
            class="text-green-600 hover:text-green-700 font-semibold text-sm"
          >
            Remember your password? Log in
          </router-link>
        </div>
      </form>

      <!-- 404 Message -->
      <div v-if="invalidToken" class="bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="text-6xl mb-4">404</div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">Reset Link Expired</h3>
        <p class="text-gray-600 mb-6">This password reset link has expired or is invalid. Please request a new one.</p>
        <router-link
          to="/auth/forgot-password"
          class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition"
        >
          Request New Link
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const formData = ref({
  password: '',
  password_confirmation: '',
  token: '',
  email: ''
})

const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const resetSuccess = ref(false)
const invalidToken = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Validate password strength
const isPasswordStrong = computed(() => {
  const pwd = formData.value.password
  return (
    pwd.length >= 8 &&
    /[A-Z]/.test(pwd) &&
    /[a-z]/.test(pwd) &&
    /[0-9]/.test(pwd)
  )
})

// Check passwords match
const passwordsMatch = computed(() => {
  return formData.value.password === formData.value.password_confirmation
})

// Can submit
const canSubmit = computed(() => {
  return isPasswordStrong.value && passwordsMatch.value && !isLoading.value
})

onMounted(() => {
  // Get token and email from URL query params
  formData.value.token = (route.query.token as string) || ''
  formData.value.email = (route.query.email as string) || ''

  // Check if token is present
  if (!formData.value.token || !formData.value.email) {
    invalidToken.value = true
  }
})

const handleResetPassword = async () => {
  if (!canSubmit.value) {
    errorMessage.value = 'Please ensure passwords match and meet all requirements'
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/auth/reset-password`,
      {
        token: formData.value.token,
        email: formData.value.email,
        password: formData.value.password,
        password_confirmation: formData.value.password_confirmation
      }
    )

    successMessage.value = response.data.message
    resetSuccess.value = true

    // Redirect to login after 2 seconds
    setTimeout(() => {
      router.push('/auth/login')
    }, 2000)
  } catch (error: any) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      errorMessage.value = Object.values(errors).flat().join(', ')
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'An error occurred while resetting your password. Please try again.'
    }

    // If token invalid, show 404
    if (error.response?.status === 422) {
      invalidToken.value = true
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
input:focus {
  border-color: #16a34a;
}
</style>
