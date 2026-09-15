import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  // Get initial theme from localStorage, default to false (light mode)
  const getStoredTheme = () => {
    if (typeof window === 'undefined') return false
    try {
      const stored = localStorage.getItem('theme-preference')
      return stored === 'dark'
    } catch {
      return false
    }
  }

  // Initialize isDark from stored preference
  const isDark = ref(getStoredTheme())

  // Apply theme to DOM immediately
  const syncThemeToDom = () => {
    if (typeof document === 'undefined') return
    
    if (isDark.value) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }

  // Save theme to localStorage
  const saveTheme = () => {
    if (typeof localStorage === 'undefined') return
    try {
      localStorage.setItem('theme-preference', isDark.value ? 'dark' : 'light')
    } catch {
      // Silently fail if localStorage is not available
    }
  }

  // Watch for changes and sync whenever isDark changes
  watch(isDark, () => {
    syncThemeToDom()
    saveTheme()
  }, { immediate: true })

  // Sync immediately on store creation
  syncThemeToDom()

  // Initialize theme from localStorage
  const initializeTheme = () => {
    const stored = getStoredTheme()
    if (stored !== isDark.value) {
      isDark.value = stored
    }
    syncThemeToDom()
  }

  // Toggle between light and dark mode
  const toggleTheme = () => {
    isDark.value = !isDark.value
  }

  // Set specific theme
  const setTheme = (dark: boolean) => {
    isDark.value = dark
  }

  // Watch for system theme changes (disabled - only user-initiated changes)
  const watchSystemTheme = () => {
    return () => {}
  }

  return {
    isDark,
    initializeTheme,
    toggleTheme,
    setTheme,
    watchSystemTheme,
    syncThemeToDom
  }
})
