import apiClient from './config'

export interface LoginRequest {
  email: string
  password: string
}

export interface RegisterRequest {
  full_name: string
  email: string
  phone: string
  password: string
  password_confirmation: string
  address: string
  region: string
  role: string
}

export interface AuthResponse {
  user: {
    id: number
    email: string
    full_name: string
    phone: string
    role: string
    role_id: number
    email_verified_at?: string
  }
  token: string
}

export interface ProfileResponse {
  id: number
  email: string
  full_name: string
  phone: string
  role: string
  address: string
  region: string
  email_verified_at?: string
  created_at: string
}

export const authAPI = {
  login: (data: LoginRequest) => 
    apiClient.post<AuthResponse>('/auth/login', data),
  
  register: (data: RegisterRequest) => 
    apiClient.post<AuthResponse>('/auth/register', data),
  
  logout: () => 
    apiClient.post('/auth/logout'),
  
  profile: () => 
    apiClient.get<ProfileResponse>('/auth/profile'),
  
  updateProfile: (data: Partial<ProfileResponse>) => 
    apiClient.post('/auth/profile/update', data),
  
  changePassword: (data: { current_password: string; password: string; password_confirmation: string }) => 
    apiClient.post('/auth/change-password', data),
  
  forgotPassword: (email: string) => 
    apiClient.post('/auth/forgot-password', { email }),
  
  resetPassword: (data: { token: string; email: string; password: string; password_confirmation: string }) => 
    apiClient.post('/auth/reset-password', data),
}
