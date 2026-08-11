<template>
  <form @submit.prevent="handleForgotPassword" class="bg-white rounded-lg shadow-md p-8 space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Forgot Password?</h2>
    <p class="text-gray-600">Enter your email address and we'll send you a link to reset your password.</p>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
      <input v-model="email" type="email" required class="input-field" placeholder="you@example.com" />
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-800">{{ error }}</p>
    </div>

    <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
      <p class="text-green-800">{{ success }}</p>
    </div>

    <button type="submit" :disabled="loading" class="btn-primary w-full">
      {{ loading ? 'Sending...' : 'Send Reset Link' }}
    </button>

    <router-link to="/auth/login" class="text-center block text-green-600 hover:text-green-700">
      Back to login
    </router-link>
  </form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { authAPI } from '@/api/auth'

const email = ref('')
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)

const handleForgotPassword = async () => {
  loading.value = true
  error.value = null
  success.value = null
  try {
    await authAPI.forgotPassword(email.value)
    success.value = 'Password reset link has been sent to your email'
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to send reset link'
  } finally {
    loading.value = false
  }
}
</script>
