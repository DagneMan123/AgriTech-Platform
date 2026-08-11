/**
 * User Roles with Registration Requirements
 */
export const USER_ROLES = {
  ADMIN: 'admin',
  FARMER: 'farmer',
  BUYER: 'buyer',
  SUPPLIER: 'supplier',
  TRANSPORT: 'transport',
  COOPERATIVE: 'cooperative',
  EXPERT: 'expert',
  FINANCIAL: 'financial'
} as const

/**
 * Roles that allow self-registration
 */
export const SELF_REGISTRATION_ROLES = [
  USER_ROLES.FARMER,
  USER_ROLES.BUYER,
  USER_ROLES.SUPPLIER,
  USER_ROLES.TRANSPORT,
  USER_ROLES.COOPERATIVE,
  USER_ROLES.EXPERT
] as const

/**
 * Roles that are created by Admin only
 */
export const ADMIN_ONLY_ROLES = [
  USER_ROLES.FINANCIAL
] as const

/**
 * Role Display Names
 */
export const ROLE_DISPLAY_NAMES: Record<string, string> = {
  [USER_ROLES.ADMIN]: 'Administrator',
  [USER_ROLES.FARMER]: 'Farmer',
  [USER_ROLES.BUYER]: 'Buyer',
  [USER_ROLES.SUPPLIER]: 'Supplier',
  [USER_ROLES.TRANSPORT]: 'Transport Provider',
  [USER_ROLES.COOPERATIVE]: 'Cooperative',
  [USER_ROLES.EXPERT]: 'Agricultural Expert',
  [USER_ROLES.FINANCIAL]: 'Financial Institution'
}

/**
 * Order Status
 */
export const ORDER_STATUS = {
  PENDING: 'pending',
  CONFIRMED: 'confirmed',
  SHIPPED: 'shipped',
  DELIVERED: 'delivered',
  CANCELLED: 'cancelled'
} as const

/**
 * Payment Status
 */
export const PAYMENT_STATUS = {
  PENDING: 'pending',
  COMPLETED: 'completed',
  FAILED: 'failed',
  REFUNDED: 'refunded'
} as const

/**
 * Loan Status
 */
export const LOAN_STATUS = {
  PENDING: 'pending',
  APPROVED: 'approved',
  REJECTED: 'rejected',
  DISBURSED: 'disbursed'
} as const

/**
 * Pagination Defaults
 */
export const PAGINATION_DEFAULTS = {
  PER_PAGE: 10,
  MAX_PER_PAGE: 100
} as const
