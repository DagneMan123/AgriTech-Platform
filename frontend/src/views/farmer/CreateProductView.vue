<template>
  <div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Add Product</h1>

    <form @submit.prevent="handleCreateProduct" class="card space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
        <input v-model="form.name" type="text" required class="input-field" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
        <textarea v-model="form.description" required class="input-field h-24"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Price (Ksh)</label>
          <input v-model.number="form.price" type="number" required class="input-field" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
          <input v-model.number="form.quantity" type="number" required class="input-field" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
          <select v-model="form.unit" required class="input-field">
            <option value="kg">Kilogram (kg)</option>
            <option value="lb">Pound (lb)</option>
            <option value="bags">Bags</option>
            <option value="units">Units</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
          <select v-model="form.category" required class="input-field">
            <option value="vegetables">Vegetables</option>
            <option value="grains">Grains</option>
            <option value="fruits">Fruits</option>
            <option value="dairy">Dairy</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Product Images</label>
        <input @change="handleImageUpload" type="file" multiple accept="image/*" class="input-field" />
      </div>

      <div v-if="error" class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800">{{ error }}</p>
      </div>

      <button type="submit" :disabled="loading" class="btn-primary w-full">
        {{ loading ? 'Creating...' : 'Add Product' }}
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
  price: 0,
  quantity: 0,
  unit: 'kg',
  category: 'vegetables',
  images: null as FileList | null
})
const loading = ref(false)
const error = ref<string | null>(null)

const handleImageUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  form.value.images = target.files
}

const handleCreateProduct = async () => {
  loading.value = true
  error.value = null
  try {
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('description', form.value.description)
    formData.append('price', form.value.price.toString())
    formData.append('quantity', form.value.quantity.toString())
    formData.append('unit', form.value.unit)
    formData.append('category', form.value.category)
    
    if (form.value.images) {
      Array.from(form.value.images).forEach((file, index) => {
        formData.append(`images[${index}]`, file)
      })
    }

    await farmerStore.createProduct(formData)
    router.push('/app/farmer/products')
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to add product'
  } finally {
    loading.value = false
  }
}
</script>
