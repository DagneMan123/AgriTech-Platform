<template>
  <div :class="['bg-white rounded-lg shadow-md p-6', hoverEffect && 'hover:shadow-lg transition-shadow']">
    <slot />
  </div>
</template>

<script setup lang="ts">
interface Props {
  hoverEffect?: boolean
}

withDefaults(defineProps<Props>(), {
  hoverEffect: false
})
</script>
