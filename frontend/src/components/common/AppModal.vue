<template>
  <teleport to="body">
    <transition name="modal">
      <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full mx-4 p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">{{ title }}</h2>
            <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="mb-6">
            <slot />
          </div>
          <div class="flex gap-2 justify-end">
            <app-button variant="secondary" @click="$emit('close')">Cancel</app-button>
            <app-button @click="$emit('confirm')">{{ confirmText }}</app-button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup lang="ts">
import AppButton from './AppButton.vue'

interface Props {
  isOpen: boolean
  title: string
  confirmText?: string
}

withDefaults(defineProps<Props>(), {
  confirmText: 'Confirm'
})

defineEmits<{
  close: []
  confirm: []
}>()
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
