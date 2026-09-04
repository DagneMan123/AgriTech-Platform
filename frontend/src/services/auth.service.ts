import apiClient from '@/api/config'
import type { User, LoginPayload, AuthResponse, PasswordReset } from '@/types/user'

class AuthService {
  /**
   * User Login
   */
  async login(credentials: LoginPayload): Promise<AuthResponse> {
    const response = await apiClient.post<AuthResponse>('/auth/login', credentials)
    if (response.data.token) {
      localStorage.setItem('auth_token', response.data.token)
    }
    return response.data
  }
  async logout(): Promise<void> {
    try {
      await apiClient.post('/auth/logout')
    } finally {
      localStorage.removeItem('auth_token')
    }
  }

  
  async getProfile(): Promise<User> {
    const response = await apiClient.get<User>('/auth/profile')
    return response.data
  }

 
  async updateProfile(data: Partial<User>): Promise<User> {
    const response = await apiClient.post<User>('/auth/profile/update', data)
    return response.data
  }

  
  async changePassword(data: {
    current_password: string
    password: string
    password_confirmation: string
  }): Promise<void> {
    await apiClient.post('/auth/change-password', data)
  }

  
  async forgotPassword(email: string): Promise<void> {
    await apiClient.post('/auth/forgot-password', { email })
  }

  
  async resetPassword(data: PasswordReset): Promise<void> {
    await apiClient.post('/auth/reset-password', data)
  }

  
  getToken(): string | null {
    return localStorage.getItem('auth_token')
  }

 
  isAuthenticated(): boolean {
    return !!this.getToken()
  }

 
  hasRole(role: string): boolean {
    const userRole = localStorage.getItem('user_role')
    return userRole === role
  }
}

export default new AuthService()
