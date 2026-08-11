import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import authService from '@/services/auth.service'
import type { User, LoginPayload, RegisterPayload } from '@/types/user'

export function useAuth() {
  const router = useRouter()
  const user = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!user.value && !!authService.getToken())
  const userRole = computed(() => user.value?.role || null)

  const login = async (credentials: LoginPayload) => {
    loading.value = true
    error.value = null
    try {
      const response = await authService.login(credentials)
      user.value = response.user
      localStorage.setItem('user_role', response.user.role)
      return response.user
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const register = async (data: RegisterPayload) => {
    loading.value = true
    error.value = null
    try {
      const response = await authService.register(data)
      user.value = response.user
      localStorage.setItem('user_role', response.user.role)
      return response.user
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      await authService.logout()
    } finally {
      user.value = null
      localStorage.removeItem('user_role')
      router.push('/auth/login')
    }
  }

  const fetchProfile = async () => {
    try {
      user.value = await authService.getProfile()
      return user.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch profile'
      throw err
    }
  }

  const updateProfile = async (data: Partial<User>) => {
    try {
      user.value = await authService.updateProfile(data)
      return user.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to update profile'
      throw err
    }
  }

  return {
    user,
    loading,
    error,
    isAuthenticated,
    userRole,
    login,
    register,
    logout,
    fetchProfile,
    updateProfile
  }
}
