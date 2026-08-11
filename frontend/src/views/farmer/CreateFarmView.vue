<template>
  <div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Create Farm</h1>

    <form @submit.prevent="handleCreateFarm" class="card space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Farm Name</label>
        <input v-model="form.name" type="text" required class="input-field" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
        <textarea v-model="form.description" required class="input-field h-24"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
          <input v-model="form.location" type="text" required class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Area (acres)</label>
          <input v-model.number="form.area" type="number" required class="input-field" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
          <input v-model.number="form.latitude" type="number" required class="input-field" step="0.0001" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
          <input v-model.number="form.longitude" type="number" required class="input-field" step="0.0001" />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Farm Images</label>
        <input @change="handleImageUpload" type="file" multiple accept="image/*" class="input-field" />
      </div>

      <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800">{{ error }}</p>
      </div>

      <button type="submit" :disabled="loading" class="btn-primary w-full">
        {{ loading ? 'Creating...' : 'Create Farm' }}
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmerStore } from '@/stores/farmerStore'

const router = useRouter()
const farmerStore = useFarmerStore()
const form = ref({
  name: '',
  description: '',
  location: '',
  area: 0,
  latitude: 0,
  longitude: 0,
  images: null as FileList | null
})
const loading = ref(false)
const error = ref<string | null>(null)

const handleImageUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  form.value.images = target.files
}

const handleCreateFarm = async () => {
  loading.value = true
  error.value = null
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('description', form.value.description)
    formData.append('location', form.value.location)
    formData.append('area', form.value.area.toString())
    formData.append('latitude', form.value.latitude.toString())
    formData.append('longitude', form.value.longitude.toString())
    
    if (form.value.images) {
      Array.from(form.value.images).forEach((file, index) => {
        formData.append(`images[${index}]`, file)
      })
    }

    await farmerStore.createFarm(formData)
    router.push('/app/farmer/farms')
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to create farm'
  } finally {
    loading.value = false
  }
}
</script>
