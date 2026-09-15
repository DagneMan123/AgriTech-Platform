// /src/composables/useTheme.ts
import { ref } from 'vue'

// ከ localStorage በማንበብ እንጀምራለን
const savedTheme = localStorage.getItem('theme_light') === 'true'
const isLight = ref(savedTheme)

// የመጀመሪያውን ጭነት በዚሁ እናስተካክላለን
document.documentElement.classList.toggle('light', isLight.value)

export function useTheme() {
  const toggleTheme = () => {
    isLight.value = !isLight.value
    // ሲቀየር ቋሚ እንዲሆን በ localStorage እናስቀምጠዋለን
    localStorage.setItem('theme_light', String(isLight.value))
    document.documentElement.classList.toggle('light', isLight.value)
  }

  return {
    isLight,
    toggleTheme
  }
}