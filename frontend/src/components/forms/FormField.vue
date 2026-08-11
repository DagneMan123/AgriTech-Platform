<template>
  <div class="form-field mb-6">
    <label v-if="label" :for="id" class="block text-sm font-semibold text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <div class="relative">
      <input
        :id="id"
        :type="type"
        :placeholder="placeholder"
        :value="modelValue"
        :required="required"
        :disabled="disabled"
        :class="[
          'w-full px-4 py-2 rounded-lg border transition',
          'focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent',
          error 
            ? 'border-red-500 bg-red-50' 
            : 'border-gray-300 bg-white hover:border-gray-400'
        ]"
        @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      />
      <span v-if="icon" class="absolute right-3 top-2.5 text-gray-400">
        {{ icon }}
      </span>
    </div>

    <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
    <p v-else-if="hint" class="mt-2 text-sm text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  id?: string
  modelValue: string | number
  type?: string
  label?: string
  placeholder?: string
  hint?: string
  error?: string
  icon?: string
  required?: boolean
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  required: false,
  disabled: false
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number]
}>()

const id = computed(() => props.id || `field-${Math.random().toString(36).substr(2, 9)}`)
</script>

<style scoped>
.form-field {
  width: 100%;
}
</style>
