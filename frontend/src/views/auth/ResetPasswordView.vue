<template>
  <div 
    class="min-h-screen flex flex-col lg:flex-row overflow-hidden transition-colors duration-500"
    :style="{ 
      backgroundColor: isDarkMode ? '#0b0f19' : '#f8fafc',
      color: isDarkMode ? '#f3f4f6' : '#1f2937'
    }"
  >
    
    <!-- Left Side: Image & AgriTech Branding -->
    <div class="lg:w-1/2 relative hidden lg:flex flex-col justify-between p-12 overflow-hidden bg-gray-900">
      <!-- Background Image with Overlay -->
      <div class="absolute inset-0 z-0">
        <img 
          src="https://images.unsplash.com/photo-1625246333195-78d9c38ad449?q=80&w=1200&auto=format&fit=crop" 
          alt="Agriculture Farming" 
          class="w-full h-full object-cover opacity-60"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-[#0b0f19] via-black/40 to-transparent"></div>
      </div>

      <!-- Top Tag -->
      <div class="z-10">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 border border-green-500/30 text-green-400 backdrop-blur-md">
          🌾 Smart Farming Platform
        </span>
      </div>

      <!-- Bottom Text -->
      <div class="z-10 space-y-3">
        <h1 class="text-4xl lg:text-5xl font-extrabold text-white tracking-tight">
          Account <br />Recovery.
        </h1>
        <p class="text-sm text-gray-300 max-w-sm">
          No worries! We'll help you get back to your AgriConnect dashboard safely.
        </p>
      </div>
    </div>

    <!-- Right Side: Reset Password Form with Theme Toggle -->
    <div 
      class="lg:w-1/2 flex items-center justify-center px-6 py-12 relative transition-colors duration-500"
      :style="{ backgroundColor: isDarkMode ? '#0b0f19' : '#f8fafc' }"
    >
      
      <!-- Theme Toggle Button (Top Right Corner) -->
      <div class="absolute top-6 right-6 z-25">
        <button 
          type="button" 
          class="w-10 h-10 rounded-full transition-all duration-300 hover:scale-110 flex items-center justify-center shadow-md" 
          @click="toggleTheme" 
          :title="isDarkMode ? 'Switch to Light' : 'Switch to Dark'"
          :style="{
            backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
            color: isDarkMode ? '#facc15' : '#4b5563',
            border: isDarkMode ? '1px solid #374151' : '1px solid #e5e7eb'
          }"
        >
          <span class="text-lg">{{ isDarkMode ? '🌙' : '☀️' }}</span>
        </button>
      </div>

      <div class="w-full max-w-md">
        
        <!-- Reset Form Card -->
        <div 
          v-if="!resetSuccess && !invalidToken" 
          class="border-2 rounded-2xl shadow-2xl p-8 relative overflow-hidden transition-colors duration-500"
          :style="{
            backgroundColor: isDarkMode ? '#111827' : '#ffffff',
            borderColor: isDarkMode ? '#374151' : '#cbd5e1'
          }"
        >
          
          <!-- Top Green Accent Line -->
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-green-400 via-emerald-400 to-green-500"></div>

          <!-- Logo / Header Section -->
          <div class="text-center mb-8 pt-2">
            <div 
              class="inline-flex items-center space-x-2 px-4 py-2 border rounded-xl shadow-inner mb-4 transition-colors duration-500"
              :style="{
                backgroundColor: isDarkMode ? '#0b0f19' : '#f9fafb',
                borderColor: isDarkMode ? '#1f2937' : '#e5e7eb'
              }"
            >
              <span class="text-xl">🌾</span>
              <span class="text-emerald-400 font-bold tracking-wide">AgriConnect</span>
            </div>
            <h1 class="text-2xl font-bold mb-1" :style="{ color: isDarkMode ? '#ffffff' : '#111827' }">Reset Password?</h1>
            <p class="text-xs" :style="{ color: isDarkMode ? '#9ca3af' : '#6b7280' }">Enter your new password below to secure your account.</p>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="mb-4 p-3 bg-red-950/50 border border-red-900 text-red-400 rounded-lg text-xs">
            {{ errorMessage }}
          </div>

          <!-- Success Alert -->
          <div v-if="successMessage" class="mb-4 p-3 bg-green-950/50 border border-green-900 text-green-400 rounded-lg text-xs">
            {{ successMessage }}
          </div>

          <form @submit.prevent="handleResetPassword" class="space-y-5">
            
            <!-- Email Display (Read-only) -->
            <div>
              <label class="block text-xs font-semibold mb-1" :style="{ color: isDarkMode ? '#9ca3af' : '#4b5563' }">Email Address</label>
              <div 
                class="flex items-center px-4 py-3 border rounded-xl text-sm transition-colors duration-500"
                :style="{
                  backgroundColor: isDarkMode ? '#0b0f19' : '#f9fafb',
                  borderColor: isDarkMode ? '#1f2937' : '#e5e7eb',
                  color: isDarkMode ? '#d1d5db' : '#374151'
                }"
              >
                <span class="mr-3 text-gray-500">📧</span>
                <span class="truncate">{{ formData.email || 'you@example.com' }}</span>
              </div>
              <p class="text-[10px] text-gray-500 mt-1">We'll send the recovery link to this address</p>
            </div>

            <!-- Password Field -->
            <div>
              <label for="password" class="block text-xs font-semibold mb-1" :style="{ color: isDarkMode ? '#9ca3af' : '#4b5563' }">New Password</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">🔒</span>
                <input
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  v-model="formData.password"
                  placeholder="••••••••"
                  required
                  minlength="8"
                  class="w-full pl-11 pr-10 py-3 border rounded-xl text-sm !outline-none focus:!outline-none focus:!ring-0 border-slate-300 dark:border-slate-700 focus:!border-emerald-400 transition bg-white dark:bg-[#0b0f19] text-slate-900 dark:text-white"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-300 text-sm"
                >
                  <span v-if="showPassword">👁️</span>
                  <span v-else>👁️‍🗨️</span>
                </button>
              </div>
            </div>

            <!-- Confirm Password Field -->
            <div>
              <label for="password_confirmation" class="block text-xs font-semibold mb-1" :style="{ color: isDarkMode ? '#9ca3af' : '#4b5563' }">Confirm Password</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">🔒</span>
                <input
                  :type="showConfirmPassword ? 'text' : 'password'"
                  id="password_confirmation"
                  v-model="formData.password_confirmation"
                  placeholder="••••••••"
                  required
                  minlength="8"
                  class="w-full pl-11 pr-10 py-3 border rounded-xl text-sm !outline-none focus:!outline-none focus:!ring-0 border-slate-300 dark:border-slate-700 focus:!border-emerald-400 transition bg-white dark:bg-[#0b0f19] text-slate-900 dark:text-white"
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-300 text-sm"
                >
                  <span v-if="showConfirmPassword">👁️</span>
                  <span v-else>👁️‍🗨️</span>
                </button>
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="isLoading || !formData.password || !formData.password_confirmation"
              class="w-full py-3.5 px-4 bg-[#2ee69d] hover:bg-[#25c483] disabled:opacity-50 text-slate-950 font-extrabold rounded-xl shadow-lg shadow-[#2ee69d]/20 transition duration-200 text-base flex items-center justify-center space-x-2"
            >
              <span v-if="!isLoading">Reset Password &rarr;</span>
              <span v-else>Processing...</span>
            </button>
          </form>

          <!-- Back to Login Link (Green Color for both Light and Dark mode) -->
          <div class="text-center mt-6">
            <router-link
              to="/auth/login"
              class="text-xs text-emerald-500 hover:text-emerald-600 dark:text-emerald-400 dark:hover:text-emerald-300 font-semibold transition"
            >
              &larr; Back to Login
            </router-link>
          </div>
        </div>

        <!-- Success Card -->
        <div 
          v-if="resetSuccess" 
          class="border-2 rounded-2xl shadow-2xl p-8 text-center relative overflow-hidden transition-colors duration-500"
          :style="{
            backgroundColor: isDarkMode ? '#111827' : '#ffffff',
            borderColor: isDarkMode ? '#374151' : '#cbd5e1'
          }"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-[#2ee69d]"></div>
          <div class="text-4xl mb-3 text-[#2ee69d]">✓</div>
          <h3 class="font-bold text-xl mb-2" :style="{ color: isDarkMode ? '#ffffff' : '#111827' }">Password Reset Successful!</h3>
          <p class="text-xs mb-6" :style="{ color: isDarkMode ? '#9ca3af' : '#6b7280' }">Your password has been changed successfully. You can now sign in with your new credentials.</p>
          <router-link
            to="/auth/login"
            class="inline-block w-full py-3.5 bg-[#2ee69d] hover:bg-[#25c483] text-slate-950 font-extrabold text-center rounded-xl shadow-lg transition text-base"
          >
            Sign In Now
          </router-link>
        </div>

        <!-- 404 / Invalid Token Card -->
        <div 
          v-if="invalidToken" 
          class="border-2 rounded-2xl shadow-2xl p-8 text-center relative overflow-hidden transition-colors duration-500"
          :style="{
            backgroundColor: isDarkMode ? '#111827' : '#ffffff',
            borderColor: isDarkMode ? '#374151' : '#cbd5e1'
          }"
        >
          <div class="absolute top-0 left-0 right-0 h-1 bg-red-500"></div>
          <div class="text-4xl font-bold text-red-500 mb-2">404</div>
          <h3 class="font-bold text-lg mb-2" :style="{ color: isDarkMode ? '#ffffff' : '#111827' }">Link Expired or Invalid</h3>
          <p class="text-xs mb-6" :style="{ color: isDarkMode ? '#9ca3af' : '#6b7280' }">This password reset token is invalid or has expired.</p>
          <router-link
            to="/auth/forgot-password"
            class="inline-block w-full py-3.5 bg-[#2ee69d] hover:bg-[#25c483] text-slate-950 font-extrabold text-center rounded-xl shadow-lg transition text-base"
          >
            Request New Link
          </router-link>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useThemeStore } from '@/stores/themeStore'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const themeStore = useThemeStore()

const isDarkMode = computed(() => themeStore.isDark)

const toggleTheme = () => {
  themeStore.toggleTheme()
}

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

const isPasswordStrong = computed(() => {
  const pwd = formData.value.password
  return pwd.length >= 8 && /[A-Z]/.test(pwd) && /[0-9]/.test(pwd)
})

const passwordsMatch = computed(() => {
  return formData.value.password === formData.value.password_confirmation
})

const canSubmit = computed(() => {
  return isPasswordStrong.value && passwordsMatch.value && !isLoading.value
})

onMounted(() => {
  themeStore.initializeTheme()

  formData.value.token = (route.query.token as string) || ''
  formData.value.email = (route.query.email as string) || ''

  if (!formData.value.token || !formData.value.email) {
    invalidToken.value = true
  }
})

const handleResetPassword = async () => {
  if (!canSubmit.value) {
    errorMessage.value = 'Please ensure passwords match and meet all requirements.'
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
      errorMessage.value = 'An error occurred while resetting your password.'
    }

    if (error.response?.status === 422) {
      invalidToken.value = true
    }
  } finally {
    isLoading.value = false
  }
}
</script>