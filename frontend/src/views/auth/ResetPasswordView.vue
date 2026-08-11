<template>
  <form @submit.prevent="handleResetPassword" class="bg-white rounded-lg shadow-md p-8 space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Reset Password</h2>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
      <input v-model="form.email" type="email" required class="input-field" />
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
      <input v-model="form.password" type="password" required class="input-field" />
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
      <input v-model="form.password_confirmation" type="password" required class="input-field" />
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-800">{{ error }}</p>
    </div>

    <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
      <p class="text-green-800">{{ success }}</p>
    </div>

    <button type="submit" :disabled="loading" class="btn-primary w-full">
      {{ loading ? 'Resetting...' : 'Reset Password' }}
    </button>
  </form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authAPI } from '@/api/auth'

const route = useRoute()
const router = useRouter()
const form = ref({
  token: route.params.token as string,
  email: '',
  password: '',
  password_confirmation: ''
})
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)

const handleResetPassword = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }

  loading.value = true
  error.value = null
  try {
    await authAPI.resetPassword(form.value)
    success.value = 'Password has been reset successfully'
    setTimeout(() => router.push('/auth/login'), 2000)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to reset password'
  } finally {
    loading.value = false
  }
}
</script>
