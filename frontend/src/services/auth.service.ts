import apiClient from '@/api/config'
import type { User, LoginPayload, RegisterPayload, AuthResponse, PasswordReset } from '@/types/user'

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

  /**
   * User Registration - Self registration for roles
   * Roles that can self-register: farmer, buyer, supplier, transport, cooperative, expert
   * Note: Financial Institution users must be created by Admin
   */
  async register(data: RegisterPayload): Promise<AuthResponse> {
    const response = await apiClient.post<AuthResponse>('/auth/register', data)
    if (response.data.token) {
      localStorage.setItem('auth_token', response.data.token)
    }
    return response.data
  }

  /**
   * User Logout
   */
  async logout(): Promise<void> {
    try {
      await apiClient.post('/auth/logout')
    } finally {
      localStorage.removeItem('auth_token')
    }
  }

  /**
   * Get current user profile
   */
  async getProfile(): Promise<User> {
    const response = await apiClient.get<User>('/auth/profile')
    return response.data
  }

  /**
   * Update user profile
   */
  async updateProfile(data: Partial<User>): Promise<User> {
    const response = await apiClient.post<User>('/auth/profile/update', data)
    return response.data
  }

  /**
   * Change password
   */
  async changePassword(data: {
    current_password: string
    password: string
    password_confirmation: string
  }): Promise<void> {
    await apiClient.post('/auth/change-password', data)
  }

  /**
   * Request password reset
   */
  async forgotPassword(email: string): Promise<void> {
    await apiClient.post('/auth/forgot-password', { email })
  }

  /**
   * Reset password with token
   */
  async resetPassword(data: PasswordReset): Promise<void> {
    await apiClient.post('/auth/reset-password', data)
  }

  /**
   * Get stored token
   */
  getToken(): string | null {
    return localStorage.getItem('auth_token')
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated(): boolean {
    return !!this.getToken()
  }

  /**
   * Check if user has specific role
   */
  hasRole(role: string): boolean {
    const userRole = localStorage.getItem('user_role')
    return userRole === role
  }
}

export default new AuthService()
