import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI, LoginRequest, RegisterRequest } from '@/api/auth'
import { useRouter } from 'vue-router'

export const useAuthStore = defineStore('auth', () => {
  const router = useRouter()
  
  const user = ref<any>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => {
    if (user.value?.role) {
      return user.value.role
    }
    return localStorage.getItem('user_role')
  })

  const checkToken = () => {
    const savedToken = localStorage.getItem('auth_token')
    if (savedToken) {
      token.value = savedToken
    }
  }

  const login = async (credentials: LoginRequest) => {
    loading.value = true
    error.value = null
    try {
      const response = await authAPI.login(credentials)
      const { user: userData, token: newToken } = response.data
      
      // Store token immediately for faster redirect
      token.value = newToken
      localStorage.setItem('auth_token', newToken)
      localStorage.setItem('user_role', userData.role)
      
      // Update user data
      user.value = userData
      loading.value = false
      
      return userData
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Login failed'
      loading.value = false
      throw err
    }
  }

  const register = async (data: RegisterRequest) => {
    loading.value = true
    error.value = null
    try {
      const response = await authAPI.register(data)
      const { user: userData, token: newToken } = response.data
      
      user.value = userData
      token.value = newToken
      localStorage.setItem('auth_token', newToken)
      localStorage.setItem('user_role', userData.role)
      
      return userData
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchProfile = async () => {
    try {
      const response = await authAPI.profile()
      user.value = response.data
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch profile'
      throw err
    }
  }

  const updateProfile = async (data: any) => {
    try {
      const response = await authAPI.updateProfile(data)
      user.value = response.data
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to update profile'
      throw err
    }
  }

  const changePassword = async (data: any) => {
    try {
      await authAPI.changePassword(data)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to change password'
      throw err
    }
  }

  const logout = async () => {
    loading.value = true
    try {
      await authAPI.logout()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user_role')
      loading.value = false
      router.push('/login')
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    userRole,
    checkToken,
    login,
    register,
    fetchProfile,
    updateProfile,
    changePassword,
    logout,
  }
})
