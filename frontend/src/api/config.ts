import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  // Reduced timeout from 30s to 15s to catch slow requests early
  timeout: 15000,
  // Enable HTTP keep-alive for better connection reuse
  httpAgent: { keepAlive: true },
  httpsAgent: { keepAlive: true },
})


apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  
  // Remove unsafe headers - browser will handle encoding automatically
  return config
}, (error) => {
  return Promise.reject(error)
})


apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
    }
    return Promise.reject(error)
  }
)

export default apiClient
