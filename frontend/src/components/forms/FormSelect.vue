<template>
  <div class="form-select mb-6">
    <label v-if="label" :for="id" class="block text-sm font-semibold text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <select
        :id="id"
        :value="modelValue"
        :required="required"
        :disabled="disabled"
        :class="[
          'w-full px-4 py-2 rounded-lg border transition appearance-none',
          'focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent',
          error 
            ? 'border-red-500 bg-red-50' 
            : 'border-gray-300 bg-white hover:border-gray-400'
        ]"
        @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
      >
        <option value="">{{ placeholder }}</option>
        <option v-for="option in options" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </select>
      <svg class="absolute right-3 top-3 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
      </svg>
    </div>

    <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
    <p v-else-if="hint" class="mt-2 text-sm text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Option {
  value: string | number
  label: string
}

interface Props {
  id?: string
  modelValue: string | number
  options: Option[]
  label?: string
  placeholder?: string
  hint?: string
  error?: string
  required?: boolean
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
  placeholder: 'Select an option'
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
}>()

const id = computed(() => props.id || `select-${Math.random().toString(36).substr(2, 9)}`)
</script>

<style scoped>
.form-select {
  width: 100%;
}
</style>
