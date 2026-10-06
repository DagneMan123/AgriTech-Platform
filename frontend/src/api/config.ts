import axios, { AxiosInstance, AxiosError } from 'axios'
import { useAuthStore } from '@/stores/authStore'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const apiClient: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 15000,
  withCredentials: true,
})

apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

apiClient.interceptors.response.use(
  (response) => {
    const newToken = response.headers['x-new-token']
    if (newToken) {
      localStorage.setItem('auth_token', newToken)
    }
    return response
  },
  (error: AxiosError) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      
      if (localStorage.getItem('auth_token')) {
        authStore.logout()
      }
    }
    
    if (error.response?.status === 403) {
      console.warn('Access denied - insufficient permissions')
    }
    
    return Promise.reject(error)
  }
)

export default apiClient
