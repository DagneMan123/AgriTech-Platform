<template>
  <form @submit.prevent="handleLogin" class="bg-white rounded-lg shadow-md p-8 space-y-6">
    <div>
      <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
      <input
        id="email"
        v-model="form.email"
        type="email"
        required
        class="input-field"
        placeholder="you@example.com"
      />
    </div>

    <div>
      <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
      <input
        id="password"
        v-model="form.password"
        type="password"
        required
        class="input-field"
        placeholder="••••••••"
      />
    </div>

    <div class="flex items-center justify-between">
      <label class="flex items-center">
        <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
        <span class="ml-2 text-sm text-gray-700">Remember me</span>
      </label>
      <router-link to="/auth/forgot-password" class="text-sm text-green-600 hover:text-green-700">
        Forgot password?
      </router-link>
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-800">{{ error }}</p>
    </div>

    <button
      type="submit"
      :disabled="loading"
      class="btn-primary w-full"
    >
      {{ loading ? 'Logging in...' : 'Login' }}
    </button>

    <p class="text-center text-gray-600">
      Don't have an account?
      <router-link to="/auth/register" class="text-green-600 hover:text-green-700 font-medium">
        Register here
      </router-link>
    </p>
  </form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()
const form = ref({
  email: '',
  password: ''
})
const loading = ref(false)
const error = ref<string | null>(null)

const handleLogin = async () => {
  loading.value = true
  error.value = null
  try {
    const user = await authStore.login(form.value)
    
    // Redirect based on role
    const roleRoutes: Record<string, string> = {
      admin: '/admin/dashboard',
      farmer: '/farmer/dashboard',
      buyer: '/buyer/dashboard',
      supplier: '/supplier/dashboard',
      transport: '/transport/dashboard',
      expert: '/expert/dashboard',
      financial: '/financial/dashboard',
      cooperative: '/cooperative/dashboard'
    }
    
    const redirectUrl = roleRoutes[user.role] || '/dashboard'
    router.push(redirectUrl)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>
