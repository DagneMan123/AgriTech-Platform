import axios, { AxiosInstance, AxiosError } from 'axios'
import { useAuthStore } from '@/stores/authStore'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

// Create axios instance with Sanctum configuration
const apiClient: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 15000,
  withCredentials: true,
})

/**
 * Request Interceptor: Add Sanctum Bearer Token
 * Automatically attaches the stored token to all requests
 */
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    
    // Add Bearer token to Authorization header
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

/**
 * Response Interceptor: Handle Auth Errors
 * - 401: Token invalid or expired - logout user
 * - 403: Forbidden - user doesn't have permission
 */
apiClient.interceptors.response.use(
  (response) => {
    // Update token if server sends a new one (optional - depends on your backend)
    const newToken = response.headers['x-new-token']
    if (newToken) {
      localStorage.setItem('auth_token', newToken)
    }
    return response
  },
  (error: AxiosError) => {
    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      
      // Only logout if we have a token (prevents infinite redirect)
      if (localStorage.getItem('auth_token')) {
        authStore.logout()
      }
    }
    
    // Handle 403 Forbidden
    if (error.response?.status === 403) {
      const authStore = useAuthStore()
      console.warn('Access denied - insufficient permissions')
      // You might redirect to an access denied page here
    }
    
    return Promise.reject(error)
  }
)

export default apiClient
