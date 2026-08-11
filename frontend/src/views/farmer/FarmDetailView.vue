<template>
  <div class="space-y-6">
    <router-link to="/app/farmer/farms" class="text-green-600 hover:text-green-700">
      ← Back to Farms
    </router-link>

    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Loading farm details...</p>
    </div>

    <div v-else-if="farm">
      <div class="card">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">{{ farm.name }}</h1>
          <p class="text-gray-600 mt-2">{{ farm.description }}</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-gray-50 p-4 rounded">
            <p class="text-gray-600 text-sm">Location</p>
            <p class="text-lg font-semibold text-gray-900">{{ farm.location }}</p>
          </div>
          <div class="bg-gray-50 p-4 rounded">
            <p class="text-gray-600 text-sm">Area</p>
            <p class="text-lg font-semibold text-gray-900">{{ farm.area }} acres</p>
          </div>
          <div class="bg-gray-50 p-4 rounded">
            <p class="text-gray-600 text-sm">Crops</p>
            <p class="text-lg font-semibold text-gray-900">{{ farm.crops_count || 0 }}</p>
          </div>
          <div class="bg-gray-50 p-4 rounded">
            <p class="text-gray-600 text-sm">Harvests</p>
            <p class="text-lg font-semibold text-gray-900">{{ farm.harvests_count || 0 }}</p>
          </div>
        </div>

        <!-- Farm Images -->
        <div class="mb-6">
          <h3 class="text-lg font-bold text-gray-900 mb-4">Farm Images</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-if="farm.images && farm.images.length === 0" class="col-span-full text-center text-gray-500">
              No images
            </div>
            <img v-for="image in farm.images" :key="image.id" :src="image.url" :alt="farm.name" class="w-full h-40 object-cover rounded" />
          </div>
        </div>

        <div class="flex space-x-4">
          <button @click="editFarm" class="btn-secondary px-4 py-2">
            Edit
          </button>
          <button @click="deleteFarm" class="btn-danger px-4 py-2">
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { farmerAPI } from '@/api/farmer'

const route = useRoute()
const router = useRouter()
const farm = ref<any>(null)
const loading = ref(false)

onMounted(async () => {
  loading.value = true
  try {
    const response = await farmerAPI.getFarm(Number(route.params.id))
    farm.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to load farm:', error)
  } finally {
    loading.value = false
  }
})

const editFarm = () => {
  // Implement edit functionality
}

const deleteFarm = async () => {
  if (confirm('Are you sure you want to delete this farm?')) {
    try {
      await farmerAPI.deleteFarm(Number(route.params.id))
      router.push('/app/farmer/farms')
    } catch (error) {
      console.error('Failed to delete farm:', error)
    }
  }
}
</script>
