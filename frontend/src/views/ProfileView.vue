<template>
  <div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">My Profile</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- Profile Picture -->
      <div class="card text-center">
        <img :src="profileImage" :alt="user?.full_name" class="w-32 h-32 rounded-full mx-auto mb-4">
        <h2 class="text-xl font-bold text-gray-900">{{ user?.full_name }}</h2>
        <p class="text-gray-600 capitalize">{{ user?.role }}</p>
      </div>

      <!-- Profile Form -->
      <div class="md:col-span-2">
        <form @submit.prevent="handleUpdateProfile" class="card space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
              <input v-model="form.full_name" type="text" class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input v-model="form.email" type="email" class="input-field" disabled />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input v-model="form.phone" type="tel" class="input-field" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
              <input v-model="form.region" type="text" class="input-field" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <input v-model="form.address" type="text" class="input-field" />
          </div>

          <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800">{{ error }}</p>
          </div>

          <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ success }}</p>
          </div>

          <button type="submit" :disabled="loading" class="btn-primary w-full">
            {{ loading ? 'Updating...' : 'Update Profile' }}
          </button>
        </form>
      </div>
    </div>

    <!-- Change Password -->
    <div class="card mt-8">
      <h3 class="text-xl font-bold text-gray-900 mb-4">Change Password</h3>
      <form @submit.prevent="handleChangePassword" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
          <input v-model="passwordForm.current_password" type="password" class="input-field" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <input v-model="passwordForm.password" type="password" class="input-field" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input v-model="passwordForm.password_confirmation" type="password" class="input-field" required />
          </div>
        </div>

        <div v-if="passwordError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-red-800">{{ passwordError }}</p>
        </div>

        <div v-if="passwordSuccess" class="p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-green-800">{{ passwordSuccess }}</p>
        </div>

        <button type="submit" :disabled="passwordLoading" class="btn-primary">
          {{ passwordLoading ? 'Changing...' : 'Change Password' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const user = ref<any>(null)
const profileImage = ref('')
const form = ref({
  full_name: '',
  email: '',
  phone: '',
  address: '',
  region: ''
})
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})
const passwordLoading = ref(false)
const passwordError = ref<string | null>(null)
const passwordSuccess = ref<string | null>(null)

onMounted(async () => {
  try {
    const profile = await authStore.fetchProfile()
    user.value = profile
    form.value = {
      full_name: profile.full_name,
      email: profile.email,
      phone: profile.phone,
      address: profile.address,
      region: profile.region
    }
    profileImage.value = 'https://api.dicebear.com/7.x/avataaars/svg?seed=' + profile.id
  } catch (err) {
    error.value = 'Failed to load profile'
  }
})

const handleUpdateProfile = async () => {
  loading.value = true
  error.value = null
  success.value = null
  try {
    await authStore.updateProfile(form.value)
    success.value = 'Profile updated successfully'
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to update profile'
  } finally {
    loading.value = false
  }
}

const handleChangePassword = async () => {
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    passwordError.value = 'Passwords do not match'
    return
  }

  passwordLoading.value = true
  passwordError.value = null
  passwordSuccess.value = null
  try {
    await authStore.changePassword(passwordForm.value)
    passwordSuccess.value = 'Password changed successfully'
    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: ''
    }
  } catch (err: any) {
    passwordError.value = err.response?.data?.message || 'Failed to change password'
  } finally {
    passwordLoading.value = false
  }
}
</script>
