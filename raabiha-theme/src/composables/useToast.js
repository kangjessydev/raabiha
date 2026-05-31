import { ref } from 'vue'

const toast = ref({ show: false, type: 'success', message: '' })
let timeoutId = null

export function useToast() {
  function showToast(type, message) {
    if (timeoutId) clearTimeout(timeoutId)
    toast.value = { show: true, type, message }
    timeoutId = setTimeout(() => { toast.value.show = false }, 4000)
  }

  return { toast, showToast }
}
