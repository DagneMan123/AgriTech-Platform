<template>
  <form @submit.prevent="handleRegister" class="bg-white rounded-lg shadow-md p-8 space-y-4">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Create Account</h2>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
        <input v-model="form.full_name" type="text" required class="input-field" />
        <p v-if="fieldErrors.full_name" class="text-red-600 text-sm mt-1">{{ fieldErrors.full_name[0] }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
        <input v-model="form.phone" type="tel" required class="input-field" />
        <p v-if="fieldErrors.phone" class="text-red-600 text-sm mt-1">{{ fieldErrors.phone[0] }}</p>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
      <input v-model="form.email" type="email" required class="input-field" />
      <p v-if="fieldErrors.email" class="text-red-600 text-sm mt-1">{{ fieldErrors.email[0] }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
        <input v-model="form.address" type="text" required class="input-field" />
        <p v-if="fieldErrors.address" class="text-red-600 text-sm mt-1">{{ fieldErrors.address[0] }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
        <input v-model="form.region" type="text" required class="input-field" />
        <p v-if="fieldErrors.region" class="text-red-600 text-sm mt-1">{{ fieldErrors.region[0] }}</p>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
      <select v-model="form.role" required class="input-field">
        <option value="">Select a role</option>
        <option value="farmer">Farmer</option>
        <option value="buyer">Buyer</option>
        <option value="supplier">Supplier</option>
        <option value="transport">Transport Provider</option>
        <option value="expert">Agricultural Expert</option>
        <option value="financial">Financial Institution</option>
        <option value="cooperative">Cooperative</option>
      </select>
      <p v-if="fieldErrors.role" class="text-red-600 text-sm mt-1">{{ fieldErrors.role[0] }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input v-model="form.password" type="password" required class="input-field" />
        <p v-if="fieldErrors.password" class="text-red-600 text-sm mt-1">{{ fieldErrors.password[0] }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
        <input v-model="form.password_confirmation" type="password" required class="input-field" />
        <p v-if="fieldErrors.password_confirmation" class="text-red-600 text-sm mt-1">{{ fieldErrors.password_confirmation[0] }}</p>
      </div>
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-800">{{ error }}</p>
    </div>

    <button type="submit" :disabled="loading" class="btn-primary w-full">
      {{ loading ? 'Creating account...' : 'Register' }}
    </button>

    <p class="text-center text-gray-600">
      Already have an account?
      <router-link to="/auth/login" class="text-green-600 hover:text-green-700 font-medium">
        Login here
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
  full_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  address: '',
  region: '',
  role: ''
})
const loading = ref(false)
const error = ref<string | null>(null)
const fieldErrors = ref<Record<string, string[]>>({})

const handleRegister = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }

  loading.value = true
  error.value = null
  fieldErrors.value = {}
  
  try {
    const user = await authStore.register(form.value)
    
    // Redirect to login page after successful registration
    router.push('/auth/login')
  } catch (err: any) {
    // Handle validation errors from API
    if (err.response?.status === 422) {
      if (err.response.data?.errors) {
        fieldErrors.value = err.response.data.errors
        error.value = 'Please check the errors below and try again'
      } else {
        error.value = err.response.data?.message || 'Validation failed'
      }
    } else {
      error.value = err.response?.data?.message || 'Registration failed'
    }
    console.error('Registration error:', err.response?.data)
  } finally {
    loading.value = false
  }
}

</script>
