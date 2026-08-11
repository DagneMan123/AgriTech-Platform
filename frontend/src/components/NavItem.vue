<template>
  <router-link :to="href" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-300 hover:text-white" :class="{ 'bg-gray-800 text-white': isActive }">
    <span class="text-xl">{{ icon }}</span>
    <span class="font-medium">{{ label }}</span>
  </router-link>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps<{
  href: string
  icon: string
  label: string
}>()

const route = useRoute()
const isActive = computed(() => route.path.startsWith(props.href))
</script>
