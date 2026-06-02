import { ref } from 'vue'

const showConfirmModal = ref(false)
const confirmModalTitle = ref('')
const confirmModalMessage = ref('')
const confirmButtonText = ref('Hapus Permanen')
let confirmCallback = null
let cancelCallback = null

export function useConfirm() {
  function triggerConfirm(title, message, callback, btnText = 'Hapus Permanen', onCancel = null) {
    confirmModalTitle.value = title
    confirmModalMessage.value = message
    confirmButtonText.value = btnText
    confirmCallback = callback
    cancelCallback = onCancel
    showConfirmModal.value = true
  }

  function handleConfirmAccept() {
    showConfirmModal.value = false
    if (confirmCallback) confirmCallback()
  }

  function handleConfirmCancel() {
    showConfirmModal.value = false
    if (cancelCallback) cancelCallback()
    confirmCallback = null
    cancelCallback = null
  }

  return {
    showConfirmModal,
    confirmModalTitle,
    confirmModalMessage,
    confirmButtonText,
    triggerConfirm,
    handleConfirmAccept,
    handleConfirmCancel
  }
}
