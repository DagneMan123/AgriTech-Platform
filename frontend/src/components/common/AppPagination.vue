<template>
  <div class="flex items-center justify-between mt-6">
    <div class="text-sm text-gray-600">
      Showing <strong>{{ start }}</strong> to <strong>{{ end }}</strong> of <strong>{{ total }}</strong> results
    </div>
    <div class="flex gap-2">
      <app-button
        variant="secondary"
        size="sm"
        :disabled="currentPage === 1"
        @click="$emit('previous')"
      >
        Previous
      </app-button>
      <button
        v-for="page in pages"
        :key="page"
        :class="[
          'px-3 py-1 rounded text-sm font-medium transition-colors',
          page === currentPage
            ? 'bg-green-600 text-white'
            : 'bg-gray-200 text-gray-900 hover:bg-gray-300'
        ]"
        @click="$emit('goto', page)"
      >
        {{ page }}
      </button>
      <app-button
        variant="secondary"
        size="sm"
        :disabled="currentPage === lastPage"
        @click="$emit('next')"
      >
        Next
      </app-button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import AppButton from './AppButton.vue'

interface Props {
  currentPage: number
  lastPage: number
  perPage: number
  total: number
  pages: number[]
}

const props = defineProps<Props>()

const start = computed(() => (props.currentPage - 1) * props.perPage + 1)
const end = computed(() => Math.min(props.currentPage * props.perPage, props.total))

defineEmits<{
  previous: []
  next: []
  goto: [page: number]
}>()
</script>
