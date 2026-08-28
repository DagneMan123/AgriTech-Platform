import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  const isDark = ref(false)

  // Initialize theme from localStorage or system preference
  const initializeTheme = () => {
    const stored = localStorage.getItem('theme-preference')
    
    if (stored) {
      isDark.value = stored === 'dark'
    } else {
      // Check system preference
      isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
    }
    
    applyTheme()
  }

  // Apply theme to DOM and localStorage
  const applyTheme = () => {
    console.log('Applying theme, isDark:', isDark.value)
    
    if (isDark.value) {
      document.documentElement.classList.add('dark')
      document.documentElement.setAttribute('data-theme', 'dark')
      localStorage.setItem('theme-preference', 'dark')
    } else {
      document.documentElement.classList.remove('dark')
      document.documentElement.setAttribute('data-theme', 'light')
      localStorage.setItem('theme-preference', 'light')
    }
    
    console.log('Dark class applied:', document.documentElement.classList.contains('dark'))
  }

  // Toggle between light and dark mode
  const toggleTheme = () => {
    isDark.value = !isDark.value
    applyTheme()
  }

  // Set specific theme
  const setTheme = (dark: boolean) => {
    isDark.value = dark
    applyTheme()
  }

  // Watch for system theme changes
  const watchSystemTheme = () => {
    if (!window.matchMedia) return () => {}
    
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    
    const handleChange = (e: MediaQueryListEvent) => {
      if (!localStorage.getItem('theme-preference')) {
        isDark.value = e.matches
        applyTheme()
      }
    }

    // Support both old and new addEventListener syntax
    if (mediaQuery.addEventListener) {
      mediaQuery.addEventListener('change', handleChange)
    } else if (mediaQuery.addListener) {
      mediaQuery.addListener(handleChange)
    }
    
    // Return cleanup function
    return () => {
      if (mediaQuery.removeEventListener) {
        mediaQuery.removeEventListener('change', handleChange)
      } else if (mediaQuery.removeListener) {
        mediaQuery.removeListener(handleChange)
      }
    }
  }

  return {
    isDark,
    initializeTheme,
    toggleTheme,
    setTheme,
    watchSystemTheme
  }
})
