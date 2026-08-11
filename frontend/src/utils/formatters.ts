/**
 * Format a number as a currency string
 * @param value - Number to format
 * @param currency - Currency code (default: USD)
 * @param locale - Locale string (default: en-US)
 * @returns Formatted currency string
 */
export function formatCurrency(
  value: number | null | undefined,
  currency: string = 'USD',
  locale: string = 'en-US'
): string {
  if (value === null || value === undefined || isNaN(value)) {
    return `$0.00`
  }

  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

/**
 * Format a number with thousands separator
 * @param value - Number to format
 * @param decimals - Number of decimal places
 * @param locale - Locale string
 * @returns Formatted number string
 */
export function formatNumber(
  value: number | null | undefined,
  decimals: number = 0,
  locale: string = 'en-US'
): string {
  if (value === null || value === undefined || isNaN(value)) {
    return '0'
  }

  return new Intl.NumberFormat(locale, {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  }).format(value)
}

/**
 * Format a number as percentage
 * @param value - Number to format (0-1 for 0-100%)
 * @param decimals - Number of decimal places
 * @param locale - Locale string
 * @returns Formatted percentage string
 */
export function formatPercentage(
  value: number | null | undefined,
  decimals: number = 1,
  locale: string = 'en-US'
): string {
  if (value === null || value === undefined || isNaN(value)) {
    return '0%'
  }

  return new Intl.NumberFormat(locale, {
    style: 'percent',
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  }).format(value)
}

/**
 * Format bytes to human-readable size
 * @param bytes - Number of bytes
 * @returns Human-readable size string
 */
export function formatBytes(bytes: number | null | undefined): string {
  if (bytes === null || bytes === undefined || bytes === 0) {
    return '0 Bytes'
  }

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

/**
 * Format date to string
 * @param date - Date object or date string
 * @param format - Format string or options
 * @param locale - Locale string
 * @returns Formatted date string
 */
export function formatDate(
  date: Date | string | null | undefined,
  options: Intl.DateTimeFormatOptions = {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  },
  locale: string = 'en-US'
): string {
  if (!date) {
    return 'N/A'
  }

  const dateObj = typeof date === 'string' ? new Date(date) : date

  if (isNaN(dateObj.getTime())) {
    return 'Invalid date'
  }

  return new Intl.DateTimeFormat(locale, options).format(dateObj)
}

/**
 * Format date and time
 * @param date - Date object or date string
 * @param locale - Locale string
 * @returns Formatted date and time string
 */
export function formatDateTime(
  date: Date | string | null | undefined,
  locale: string = 'en-US'
): string {
  return formatDate(date, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  }, locale)
}

/**
 * Format time only
 * @param date - Date object or date string
 * @param locale - Locale string
 * @returns Formatted time string
 */
export function formatTime(
  date: Date | string | null | undefined,
  locale: string = 'en-US'
): string {
  return formatDate(date, {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  }, locale)
}

/**
 * Format relative time (e.g., "2 hours ago")
 * @param date - Date object or date string
 * @param baseDate - Base date for calculation (default: now)
 * @param locale - Locale string
 * @returns Relative time string
 */
export function formatRelativeTime(
  date: Date | string | null | undefined,
  baseDate: Date = new Date(),
  locale: string = 'en-US'
): string {
  if (!date) {
    return 'N/A'
  }

  const dateObj = typeof date === 'string' ? new Date(date) : date

  if (isNaN(dateObj.getTime())) {
    return 'Invalid date'
  }

  const seconds = Math.floor((baseDate.getTime() - dateObj.getTime()) / 1000)

  if (seconds < 60) return `${seconds} seconds ago`
  if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`
  if (seconds < 604800) return `${Math.floor(seconds / 86400)} days ago`
  if (seconds < 2592000) return `${Math.floor(seconds / 604800)} weeks ago`
  if (seconds < 31536000) return `${Math.floor(seconds / 2592000)} months ago`
  return `${Math.floor(seconds / 31536000)} years ago`
}

/**
 * Format duration in milliseconds to time string
 * @param milliseconds - Duration in milliseconds
 * @returns Time string (HH:MM:SS)
 */
export function formatDuration(milliseconds: number | null | undefined): string {
  if (milliseconds === null || milliseconds === undefined || milliseconds < 0) {
    return '00:00:00'
  }

  const totalSeconds = Math.floor(milliseconds / 1000)
  const hours = Math.floor(totalSeconds / 3600)
  const minutes = Math.floor((totalSeconds % 3600) / 60)
  const seconds = totalSeconds % 60

  const pad = (num: number) => String(num).padStart(2, '0')

  return `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`
}

/**
 * Truncate string to max length with ellipsis
 * @param value - String to truncate
 * @param maxLength - Maximum length
 * @param suffix - Suffix when truncated (default: "...")
 * @returns Truncated string
 */
export function truncateString(
  value: string | null | undefined,
  maxLength: number = 50,
  suffix: string = '...'
): string {
  if (!value) return ''

  if (value.length <= maxLength) {
    return value
  }

  return value.substring(0, maxLength - suffix.length) + suffix
}

/**
 * Format phone number
 * @param value - Phone number
 * @param format - Phone format (default: "(XXX) XXX-XXXX")
 * @returns Formatted phone number
 */
export function formatPhoneNumber(
  value: string | null | undefined,
  format: string = '(XXX) XXX-XXXX'
): string {
  if (!value) return ''

  // Remove all non-digit characters
  const digits = value.replace(/\D/g, '')

  if (digits.length === 10) {
    return `(${digits.substring(0, 3)}) ${digits.substring(3, 6)}-${digits.substring(6)}`
  }

  return value
}

/**
 * Capitalize first letter of string
 * @param value - String to capitalize
 * @returns Capitalized string
 */
export function capitalize(value: string | null | undefined): string {
  if (!value) return ''
  return value.charAt(0).toUpperCase() + value.slice(1)
}

/**
 * Convert camelCase to Title Case
 * @param value - camelCase string
 * @returns Title Case string
 */
export function titleCase(value: string | null | undefined): string {
  if (!value) return ''

  return value
    .replace(/([A-Z])/g, ' $1')
    .replace(/^./, str => str.toUpperCase())
    .trim()
}

/**
 * Convert string to slug
 * @param value - String to convert
 * @returns Slug string
 */
export function slugify(value: string | null | undefined): string {
  if (!value) return ''

  return value
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_]+/g, '-')
    .replace(/^-+|-+$/g, '')
}

/**
 * Format array to comma-separated string
 * @param items - Array of items
 * @param lastSeparator - Separator before last item (default: ", ")
 * @returns Comma-separated string
 */
export function formatList(
  items: any[] | null | undefined,
  lastSeparator: string = ', '
): string {
  if (!items || items.length === 0) return ''

  if (items.length === 1) return String(items[0])

  const allButLast = items.slice(0, -1).join(', ')
  const last = items[items.length - 1]

  return `${allButLast}${lastSeparator}${last}`
}
