import { ref } from 'vue'

const visible = ref(false)
const message = ref('')
const color = ref('dark')
const timeout = ref(3000)

export function useSnackbar() {
  const showSnackbar = (msg, type = 'dark', time = 3000) => {
    // Reset agar bisa retrigger walau message sama
    visible.value = false
    setTimeout(() => {
      message.value = msg
      color.value = type
      timeout.value = time
      visible.value = true
    }, 10)
  }

  const closeSnackbar = () => {
    visible.value = false
  }

  return {
    visible,
    message,
    color,
    timeout,
    showSnackbar,
    closeSnackbar,
  }
}
