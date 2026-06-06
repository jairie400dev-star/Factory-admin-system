import { ref } from 'vue'

// Tiny global toast queue shared across the app.
const toasts = ref([])
let seq = 0

export function useToast() {
  function push(message, type = 'success', timeout = 3200) {
    const id = ++seq
    toasts.value.push({ id, message, type })
    if (timeout) setTimeout(() => dismiss(id), timeout)
  }
  function dismiss(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }
  return {
    toasts,
    dismiss,
    success: (m) => push(m, 'success'),
    error: (m) => push(m, 'error'),
    info: (m) => push(m, 'info'),
  }
}
