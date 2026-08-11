export interface User {
  id: number
  email: string
  full_name: string
  phone: string
  role: UserRole
  role_id: number
  address: string
  region: string
  email_verified_at?: string
  created_at: string
  updated_at: string
}

export type UserRole = 'admin' | 'farmer' | 'buyer' | 'supplier' | 'transport' | 'cooperative' | 'expert' | 'financial'

export interface RegisterPayload {
  full_name: string
  email: string
  phone: string
  password: string
  password_confirmation: string
  address: string
  region: string
  role: UserRole
}

export interface LoginPayload {
  email: string
  password: string
}

export interface AuthResponse {
  user: User
  token: string
}

export interface PasswordReset {
  token: string
  email: string
  password: string
  password_confirmation: string
}
