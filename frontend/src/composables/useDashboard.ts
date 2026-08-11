import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import type { Ref } from 'vue'

export interface DashboardData {
  [key: string]: any
}

export interface CacheConfig {
  enabled: boolean
  ttl: number // Time to live in milliseconds
}

export interface DashboardOptions {
  apiEndpoint: string
  initialData?: DashboardData
  cacheConfig?: CacheConfig
  refreshInterval?: number // Auto-refresh interval in milliseconds
  errorRetryCount?: number
  errorRetryDelay?: number
}

interface CacheEntry {
  data: DashboardData
  timestamp: number
}

export function useDashboard(options: DashboardOptions) {
  // State management
  const data: Ref<DashboardData | null> = ref(options.initialData || null)
  const loading = ref(false)
  const error: Ref<string | null> = ref(null)
  const isRefreshing = ref(false)

  // Configuration
  const apiEndpoint = options.apiEndpoint
  const cacheConfig: CacheConfig = {
    enabled: true,
    ttl: 5 * 60 * 1000, // 5 minutes default
    ...options.cacheConfig
  }
  const refreshInterval = options.refreshInterval || null
  const errorRetryCount = options.errorRetryCount || 3
  const errorRetryDelay = options.errorRetryDelay || 1000

  // Cache management
  const cache: Map<string, CacheEntry> = new Map()
  let refreshTimer: number | null = null
  let retryCount = 0

  /**
   * Check if cached data is still valid
   */
  const isCacheValid = (key: string = 'dashboard'): boolean => {
    if (!cacheConfig.enabled) return false

    const entry = cache.get(key)
    if (!entry) return false

    const now = Date.now()
    return now - entry.timestamp < cacheConfig.ttl
  }

  /**
   * Get data from cache
   */
  const getCachedData = (key: string = 'dashboard'): DashboardData | null => {
    const entry = cache.get(key)
    return entry ? entry.data : null
  }

  /**
   * Store data in cache
   */
  const setCacheData = (data: DashboardData, key: string = 'dashboard'): void => {
    cache.set(key, {
      data,
      timestamp: Date.now()
    })
  }

  /**
   * Clear cache
   */
  const clearCache = (key?: string): void => {
    if (key) {
      cache.delete(key)
    } else {
      cache.clear()
    }
  }

  /**
   * Fetch dashboard data with error handling and retry logic
   */
  const fetchData = async (forceRefresh = false): Promise<DashboardData | null> => {
    const cacheKey = apiEndpoint

    // Check cache first (unless force refresh)
    if (!forceRefresh && isCacheValid(cacheKey)) {
      const cachedData = getCachedData(cacheKey)
      if (cachedData) {
        data.value = cachedData
        return cachedData
      }
    }

    loading.value = true
    error.value = null
    retryCount = 0

    const attemptFetch = async (): Promise<DashboardData | null> => {
      try {
        const response = await fetch(apiEndpoint, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${getAuthToken()}`
          }
        })

        if (!response.ok) {
          throw new Error(`HTTP Error: ${response.status}`)
        }

        const fetchedData = await response.json()

        // Validate data structure
        if (!fetchedData || typeof fetchedData !== 'object') {
          throw new Error('Invalid data format received from API')
        }

        // Cache the data
        setCacheData(fetchedData, cacheKey)
        data.value = fetchedData
        retryCount = 0
        error.value = null

        return fetchedData
      } catch (err: any) {
        retryCount++

        if (retryCount < errorRetryCount) {
          // Wait before retrying
          await new Promise(resolve => setTimeout(resolve, errorRetryDelay * retryCount))
          return attemptFetch()
        } else {
          const errorMessage = err instanceof Error ? err.message : 'Failed to fetch dashboard data'
          error.value = errorMessage
          console.error('Dashboard fetch error:', errorMessage)
          throw err
        }
      } finally {
        loading.value = false
      }
    }

    try {
      return await attemptFetch()
    } catch (err) {
      return null
    }
  }

  /**
   * Refresh data (typically called by user or interval)
   */
  const refresh = async (): Promise<DashboardData | null> => {
    isRefreshing.value = true
    try {
      return await fetchData(true)
    } finally {
      isRefreshing.value = false
    }
  }

  /**
   * Setup auto-refresh timer
   */
  const setupAutoRefresh = (): void => {
    if (!refreshInterval) return

    // Clear existing timer if any
    if (refreshTimer) {
      clearInterval(refreshTimer)
    }

    refreshTimer = window.setInterval(() => {
      if (!isRefreshing.value && !loading.value) {
        refresh()
      }
    }, refreshInterval)
  }

  /**
   * Stop auto-refresh
   */
  const stopAutoRefresh = (): void => {
    if (refreshTimer) {
      clearInterval(refreshTimer)
      refreshTimer = null
    }
  }

  /**
   * Get nested data using dot notation (e.g., "user.profile.name")
   */
  const getNestedData = (path: string, defaultValue: any = null): any => {
    if (!data.value) return defaultValue

    const keys = path.split('.')
    let result: any = data.value

    for (const key of keys) {
      if (result && typeof result === 'object' && key in result) {
        result = result[key]
      } else {
        return defaultValue
      }
    }

    return result
  }

  /**
   * Computed properties
   */
  const hasData = computed(() => data.value !== null && Object.keys(data.value || {}).length > 0)
  const hasError = computed(() => error.value !== null)
  const isLoading = computed(() => loading.value || isRefreshing.value)

  /**
   * Helper to get auth token (implement based on your auth system)
   */
  const getAuthToken = (): string => {
    return localStorage.getItem('auth_token') || ''
  }

  /**
   * Lifecycle hooks
   */
  onMounted(() => {
    // Fetch data on mount if no initial data
    if (!data.value) {
      fetchData()
    }
    // Setup auto-refresh if configured
    setupAutoRefresh()
  })

  onUnmounted(() => {
    // Clean up timers
    stopAutoRefresh()
  })

  return {
    // State
    data,
    loading,
    error,
    isRefreshing,

    // Computed
    hasData,
    hasError,
    isLoading,

    // Methods
    fetchData,
    refresh,
    clearCache,
    getNestedData,
    setupAutoRefresh,
    stopAutoRefresh,

    // Cache management
    isCacheValid,
    getCachedData,
    setCacheData
  }
}
