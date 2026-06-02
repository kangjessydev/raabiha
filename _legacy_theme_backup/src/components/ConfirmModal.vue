<script setup>
import { useConfirm } from '../composables/useConfirm'

const {
  showConfirmModal,
  confirmModalTitle,
  confirmModalMessage,
  confirmButtonText,
  handleConfirmAccept,
  handleConfirmCancel
} = useConfirm()
</script>

<template>
  <transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="handleConfirmCancel"></div>
      
      <!-- Modal Box -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      >
        <div class="relative bg-white rounded-2xl shadow-xl border border-[#e0e0e0] max-w-sm w-full p-6 flex flex-col gap-4 z-10">
          <div class="flex items-start gap-3.5">
            <!-- Icon -->
            <div class="p-2.5 bg-red-50 text-red-600 rounded-xl flex-shrink-0">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </div>
            <!-- Text -->
            <div class="flex-1 min-w-0">
              <h3 class="text-[15px] font-bold text-[#1e1e1e] mb-1.5 leading-snug">{{ confirmModalTitle }}</h3>
              <p class="text-[12.5px] text-[#757575] leading-relaxed">{{ confirmModalMessage }}</p>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="flex justify-end gap-2.5 pt-1.5">
            <button @click="handleConfirmCancel" class="px-4 py-2 text-[12.5px] font-medium text-[#757575] hover:bg-[#f0f0f1] rounded-xl transition-all border border-[#e0e0e0] cursor-pointer">
              Batal
            </button>
            <button @click="handleConfirmAccept" class="px-4 py-2 text-[12.5px] font-medium bg-red-600 hover:bg-red-700 text-white rounded-xl transition-all shadow-sm cursor-pointer">
              {{ confirmButtonText }}
            </button>
          </div>
        </div>
      </transition>
    </div>
  </transition>
</template>
