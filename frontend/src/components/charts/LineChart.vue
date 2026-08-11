<template>
  <div class="line-chart w-full h-80">
    <!-- Placeholder for chart implementation -->
    <div class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-center">
      <p class="text-gray-500 text-sm">Line Chart - Integrate with Chart.js or ECharts</p>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  data?: Record<string, any>
  options?: Record<string, any>
}

defineProps<Props>()
</script>

<style scoped>
.line-chart {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
