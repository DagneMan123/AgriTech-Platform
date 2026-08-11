/**
 * Format currency to Kenyan Shilling
 */
export const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(amount)
}

/**
 * Format date in a readable way
 */
export const formatDate = (date: string | Date): string => {
  return new Intl.DateTimeFormat('en-KE', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(date))
}

/**
 * Format phone number
 */
export const formatPhoneNumber = (phone: string): string => {
  const cleaned = phone.replace(/\D/g, '')
  if (cleaned.length === 9) {
    return `+254${cleaned}`
  }
  return phone
}

/**
 * Validate email
 */
export const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

/**
 * Validate phone number (Kenya)
 */
export const isValidPhone = (phone: string): boolean => {
  const phoneRegex = /^(\+254|0)[17]\d{8}$/
  return phoneRegex.test(phone)
}

/**
 * Get initials from name
 */
export const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
}

/**
 * Truncate text
 */
export const truncateText = (text: string, length: number = 50): string => {
  if (text.length <= length) return text
  return text.substring(0, length) + '...'
}

/**
 * Generate random color
 */
export const generateRandomColor = (): string => {
  const colors = [
    '#22c55e', // green
    '#3b82f6', // blue
    '#8b5cf6', // purple
    '#ef4444', // red
    '#f59e0b', // amber
    '#ec4899', // pink
  ]
  return colors[Math.floor(Math.random() * colors.length)]
}

/**
 * Convert file size to readable format
 */
export const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

/**
 * Check if object is empty
 */
export const isEmpty = (obj: Record<string, any>): boolean => {
  return Object.keys(obj).length === 0
}

/**
 * Deep copy object
 */
export const deepCopy = <T>(obj: T): T => {
  return JSON.parse(JSON.stringify(obj))
}

/**
 * Debounce function
 */
export const debounce = (func: Function, delay: number) => {
  let timeoutId: NodeJS.Timeout
  return (...args: any[]) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => func(...args), delay)
  }
}

/**
 * Throttle function
 */
export const throttle = (func: Function, limit: number) => {
  let inThrottle: boolean
  return (...args: any[]) => {
    if (!inThrottle) {
      func(...args)
      inThrottle = true
      setTimeout(() => (inThrottle = false), limit)
    }
  }
}

/**
 * Sleep function (delay)
 */
export const sleep = (ms: number): Promise<void> => {
  return new Promise(resolve => setTimeout(resolve, ms))
}

/**
 * Get query parameters from URL
 */
export const getQueryParams = (search: string): Record<string, string> => {
  const params: Record<string, string> = {}
  const searchParams = new URLSearchParams(search)
  searchParams.forEach((value, key) => {
    params[key] = value
  })
  return params
}

/**
 * Build query string from object
 */
export const buildQueryString = (params: Record<string, any>): string => {
  const searchParams = new URLSearchParams()
  Object.keys(params).forEach(key => {
    if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
      searchParams.append(key, params[key])
    }
  })
  return searchParams.toString()
}

/**
 * Download file from URL
 */
export const downloadFile = (url: string, filename: string): void => {
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

/**
 * Convert canvas to blob
 */
export const canvasToBlob = (canvas: HTMLCanvasElement): Promise<Blob | null> => {
  return new Promise(resolve => {
    canvas.toBlob(blob => resolve(blob))
  })
}

/**
 * Get role display name
 */
export const getRoleDisplayName = (role: string): string => {
  const roleNames: Record<string, string> = {
    admin: 'Administrator',
    farmer: 'Farmer',
    buyer: 'Buyer',
    supplier: 'Supplier',
    transport: 'Transport Provider',
    expert: 'Agricultural Expert',
    financial: 'Financial Institution',
    cooperative: 'Cooperative',
  }
  return roleNames[role] || role
}

/**
 * Get role icon
 */
export const getRoleIcon = (role: string): string => {
  const roleIcons: Record<string, string> = {
    admin: '⚙️',
    farmer: '🌾',
    buyer: '🛒',
    supplier: '📦',
    transport: '🚚',
    expert: '👨‍🏫',
    financial: '💰',
    cooperative: '👥',
  }
  return roleIcons[role] || '👤'
}

/**
 * Validate file type
 */
export const isValidFileType = (file: File, allowedTypes: string[]): boolean => {
  return allowedTypes.includes(file.type)
}

/**
 * Get file extension
 */
export const getFileExtension = (filename: string): string => {
  return filename.slice((filename.lastIndexOf('.') - 1 >>> 0) + 2)
}

/**
 * Check if date is today
 */
export const isToday = (date: Date | string): boolean => {
  const today = new Date()
  const checkDate = new Date(date)
  return (
    checkDate.getDate() === today.getDate() &&
    checkDate.getMonth() === today.getMonth() &&
    checkDate.getFullYear() === today.getFullYear()
  )
}

/**
 * Calculate days between dates
 */
export const daysBetween = (date1: Date | string, date2: Date | string): number => {
  const d1 = new Date(date1)
  const d2 = new Date(date2)
  const millisecondsPerDay = 1000 * 60 * 60 * 24
  return Math.floor((d2.getTime() - d1.getTime()) / millisecondsPerDay)
}
