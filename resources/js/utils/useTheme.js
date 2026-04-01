import { useTheme } from 'vuetify'
import { ref, onMounted } from 'vue'

const isDark = ref(false)

export const useDarkTheme = () => {
  const theme = useTheme()

  const toggleDark = () => {
    isDark.value = !isDark.value
    theme.global.name.value = isDark.value ? 'dark' : 'light'
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
  }

  onMounted(() => {
    const savedTheme = localStorage.getItem('theme')
    isDark.value = savedTheme === 'dark'
    theme.global.name.value = savedTheme || 'light'
  })

  return { isDark, toggleDark }
}
