<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">🌾 AgriTech</h1>
        <p class="text-gray-600 mt-2">Reset Your Password</p>
      </div>

      <!-- Form Card -->
      <form @submit.prevent="handleResetPassword" class="bg-white rounded-lg shadow-md p-8 space-y-6">
        
        <!-- Info Message -->
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <p class="text-sm text-blue-800">
            Please enter a strong password with at least 8 characters including uppercase, lowercase, numbers, and special characters.
          </p>
        </div>

        <!-- Email Field (Pre-filled from URL) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
          <input 
            v-model="form.email" 
            type="email" 
            required 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            :readonly="emailFromUrl"
          />
          <p v-if="!emailFromUrl" class="text-xs text-gray-500 mt-1">
            Email from reset link will be auto-filled
          </p>
        </div>

        <!-- Token Field (Hidden but required) -->
        <input 
          v-model="form.token" 
          type="hidden"
        />

        <!-- New Password Field -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
          <div class="relative">
            <input 
              v-model="form.password" 
              :type="showPassword ? 'text' : 'password'" 
              required 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
              placeholder="Enter your new password"
              @input="validatePassword"
            />
            <button 
              type="button" 
              class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? '👁️' : '👁️‍🗨️' }}
            </button>
          </div>
          
          <!-- Password Strength Indicator -->
          <div class="mt-3 space-y-2">
            <div class="space-y-1">
              <p class="text-xs font-medium text-gray-600">Password Requirements:</p>
              <ul class="text-xs space-y-1">
                <li :class="passwordRequirements.minLength ? 'text-green-600' : 'text-gray-400'">
                  ✓ At least 8 characters
                </li>
                <li :class="passwordRequirements.hasUppercase ? 'text-green-600' : 'text-gray-400'">
                  ✓ At least one uppercase letter (A-Z)
                </li>
                <li :class="passwordRequirements.hasLowercase ? 'text-green-600' : 'text-gray-400'">
                  ✓ At least one lowercase letter (a-z)
                </li>
                <li :class="passwordRequirements.hasNumber ? 'text-green-600' : 'text-gray-400'">
                  ✓ At least one number (0-9)
                </li>
                <li :class="passwordRequirements.hasSpecial ? 'text-green-600' : 'text-gray-400'">
                  ✓ At least one special character (@$!%*?&)
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Confirm Password Field -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
          <div class="relative">
            <input 
              v-model="form.password_confirmation" 
              :type="showConfirmPassword ? 'text' : 'password'" 
              required 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
              placeholder="Confirm your new password"
            />
            <button 
              type="button" 
              class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600"
              @click="showConfirmPassword = !showConfirmPassword"
            >
              {{ showConfirmPassword ? '👁️' : '👁️‍🗨️' }}
            </button>
          </div>
          <p v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation" 
            class="text-xs text-red-600 mt-1">
            Passwords do not match
          </p>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-red-800 text-sm">{{ error }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-green-800 text-sm">{{ success }}</p>
          <p class="text-green-700 text-xs mt-2">Redirecting to login...</p>
        </div>

        <!-- Submit Button -->
        <button 
          type="submit" 
          :disabled="loading || !isFormValid" 
          class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition duration-200"
        >
          {{ loading ? 'Resetting Password...' : 'Reset Password' }}
        </button>

        <!-- Back to Login Link -->
        <router-link 
          to="/auth/login" 
          class="text-center block text-green-600 hover:text-green-700 text-sm"
        >
          Remember your password? Back to login
        </router-link>
      </form>

      <!-- Footer -->
      <p class="text-center text-xs text-gray-500 mt-8">
        © 2026 AgriTech Platform. All rights reserved.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authAPI } from '@/api/auth'

const route = useRoute()
const router = useRouter()

const form = ref({
  token: (route.query.token as string) || '',
  email: (route.query.email as string) || '',
  password: '',
  password_confirmation: ''
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)

const emailFromUrl = computed(() => {
  return (route.query.email as string) || false
})

const passwordRequirements = ref({
  minLength: false,
  hasUppercase: false,
  hasLowercase: false,
  hasNumber: false,
  hasSpecial: false
})

const isFormValid = computed(() => {
  return form.value.email && 
         form.value.token &&
         form.value.password &&
         form.value.password_confirmation === form.value.password &&
         Object.values(passwordRequirements.value).every(req => req)
})

const validatePassword = () => {
  const pwd = form.value.password
  
  passwordRequirements.value = {
    minLength: pwd.length >= 8,
    hasUppercase: /[A-Z]/.test(pwd),
    hasLowercase: /[a-z]/.test(pwd),
    hasNumber: /\d/.test(pwd),
    hasSpecial: /[@$!%*?&]/.test(pwd)
  }
}

const handleResetPassword = async () => {
  // Final validation
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }

  if (!form.value.token) {
    error.value = 'Invalid reset link. Please request a new password reset.'
    return
  }

  if (!isFormValid.value) {
    error.value = 'Please ensure your password meets all requirements'
    return
  }

  loading.value = true
  error.value = null

  try {
    await authAPI.resetPassword({
      token: form.value.token,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    })
    
    success.value = 'Password has been reset successfully! Redirecting to login...'
    
    setTimeout(() => {
      router.push('/auth/login')
    }, 2000)
  } catch (err: any) {
    error.value = err.response?.data?.message || err.response?.data?.errors?.token?.[0] || 'Failed to reset password. Please try again or request a new reset link.'
    console.error('Reset password error:', err)
  } finally {
    loading.value = false
  }
}
</script>
