<template>
  <div class="location-map w-full h-80">
    <!-- Placeholder for map implementation -->
    <div class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-center">
      <p class="text-gray-500 text-sm">Location Map - Integrate with Leaflet or Mapbox</p>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  center?: [number, number]
  zoom?: number
  markers?: Array<{ lat: number; lng: number; title: string }>
}

defineProps<Props>()
</script>

<style scoped>
.location-map {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
