import { computed } from 'vue'
import { useThemeStore } from '@/stores/themeStore'

export function useTheme() {
  const themeStore = useThemeStore()

  const isDark = computed(() => themeStore.isDark)
  const isLight = computed(() => !themeStore.isDark)

  return {
    isDark,
    isLight,
    toggleTheme: () => themeStore.toggleTheme(),
    setTheme: (dark: boolean) => themeStore.setTheme(dark),
    initializeTheme: () => themeStore.initializeTheme(),
    watchSystemTheme: () => themeStore.watchSystemTheme()
  }
}

