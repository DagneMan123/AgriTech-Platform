import { USER_ROLES, SELF_REGISTRATION_ROLES, ADMIN_ONLY_ROLES } from './constants'
import type { UserRole } from '@/types/user'

/**
 * Check if role allows self-registration
 */
export const canSelfRegister = (role: UserRole): boolean => {
  return SELF_REGISTRATION_ROLES.includes(role as any)
}

/**
 * Check if role must be created by admin
 */
export const isAdminOnlyRole = (role: UserRole): boolean => {
  return ADMIN_ONLY_ROLES.includes(role as any)
}

/**
 * Get dashboard route for a role
 */
export const getDashboardRoute = (role: UserRole): string => {
  const routes: Record<UserRole, string> = {
    admin: '/admin/dashboard',
    farmer: '/farmer/dashboard',
    buyer: '/buyer/dashboard',
    supplier: '/supplier/dashboard',
    transport: '/transport/dashboard',
    cooperative: '/cooperative/dashboard',
    expert: '/expert/dashboard',
    financial: '/financial/dashboard'
  }
  return routes[role] || '/dashboard'
}

/**
 * Check if user has specific role
 */
export const hasRole = (userRole: UserRole | null, targetRole: UserRole): boolean => {
  return userRole === targetRole
}

/**
 * Check if user has any of the roles
 */
export const hasAnyRole = (userRole: UserRole | null, roles: UserRole[]): boolean => {
  return roles.includes(userRole as UserRole)
}

/**
 * Get role permissions (can be extended)
 */
export const getRolePermissions = (role: UserRole): string[] => {
  const permissions: Record<UserRole, string[]> = {
    admin: ['manage-users', 'manage-roles', 'manage-permissions', 'view-reports', 'manage-settings'],
    farmer: ['manage-farms', 'manage-crops', 'manage-products', 'manage-orders', 'request-consultation'],
    buyer: ['browse-marketplace', 'manage-cart', 'manage-orders', 'make-payments', 'write-reviews'],
    supplier: ['manage-products', 'manage-inventory', 'manage-warehouses', 'process-orders'],
    transport: ['manage-vehicles', 'manage-deliveries', 'track-orders', 'manage-drivers'],
    cooperative: ['manage-members', 'manage-sales', 'view-reports'],
    expert: ['manage-consultations', 'publish-articles', 'manage-training'],
    financial: ['manage-loans', 'manage-insurance', 'manage-payments', 'view-transactions']
  }
  return permissions[role] || []
}

/**
 * Check if user has permission
 */
export const hasPermission = (role: UserRole, permission: string): boolean => {
  return getRolePermissions(role).includes(permission)
}
