<template>
  <form @submit.prevent="handleLogin" class="login-form">
    <div>
      <label for="email" class="form-label">Email Address</label>
      <input
        id="email"
        v-model="form.email"
        type="email"
        required
        class="form-input"
        placeholder="you@example.com"
      />
    </div>

    <div>
      <label for="password" class="form-label">Password</label>
      <input
        id="password"
        v-model="form.password"
        type="password"
        required
        class="form-input"
        placeholder="••••••••"
      />
    </div>

    <div class="flex items-center justify-between">
      <label class="flex items-center checkbox-label">
        <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
        <span class="ml-2 text-sm">Remember me</span>
      </label>
      <router-link to="/auth/forgot-password" class="link-text">
        Forgot password?
      </router-link>
    </div>

    <div v-if="error" class="error-box">
      <p>{{ error }}</p>
    </div>

    <button
      type="submit"
      :disabled="loading"
      class="btn-primary w-full"
    >
      {{ loading ? 'Logging in...' : 'Login' }}
    </button>
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

<style scoped>
.login-form {
  background-color: var(--bg-light);
  border-radius: 0.5rem;
  box-shadow: var(--shadow-md);
  padding: 2rem;
  gap: 1.5rem;
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
}

html.dark .login-form {
  background-color: var(--bg-dark-secondary);
  box-shadow: var(--shadow-dark-md);
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-light-primary);
  margin-bottom: 0.5rem;
}

html.dark .form-label {
  color: var(--text-dark-primary);
}

.form-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border-light);
  border-radius: 0.375rem;
  background-color: var(--bg-light);
  color: var(--text-light-primary);
  font-size: 1rem;
  font-family: inherit;
  transition: all 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: #16a34a;
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

html.dark .form-input {
  background-color: var(--bg-dark-secondary);
  color: var(--text-dark-primary);
  border-color: var(--border-dark);
}

html.dark .form-input:focus {
  box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.1);
}

.checkbox-label {
  color: var(--text-light-primary);
}

html.dark .checkbox-label {
  color: var(--text-dark-primary);
}

.link-text {
  font-size: 0.875rem;
  color: #16a34a;
  text-decoration: none;
  transition: color 0.2s ease;
}

.link-text:hover {
  color: #15803d;
}

html.dark .link-text {
  color: #4ade80;
}

html.dark .link-text:hover {
  color: #22c55e;
}

.link-primary {
  color: #16a34a;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
}

.link-primary:hover {
  color: #15803d;
}

html.dark .link-primary {
  color: #4ade80;
}

html.dark .link-primary:hover {
  color: #22c55e;
}

.error-box {
  padding: 1rem;
  background-color: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
  color: #991b1b;
  transition: all 0.3s ease;
}

html.dark .error-box {
  background-color: rgba(220, 38, 38, 0.1);
  border-color: rgba(220, 38, 38, 0.3);
  color: #fca5a5;
}

.btn-primary {
  padding: 0.5rem 1rem;
  background-color: #16a34a;
  color: white;
  border: none;
  border-radius: 9999px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background-color: #15803d;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
