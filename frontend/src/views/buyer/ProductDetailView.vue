<template>
  <div class="max-w-4xl mx-auto">
    <router-link to="/app/buyer/marketplace" class="text-green-600 hover:text-green-700 mb-6 inline-block">
      ← Back to Marketplace
    </router-link>

    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Loading product...</p>
    </div>

    <div v-else-if="product" class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Images -->
      <div>
        <img v-if="product.image" :src="product.image" :alt="product.name" class="w-full h-96 object-cover rounded-lg mb-4" />
        <div class="flex space-x-2">
          <button v-for="i in 3" :key="i" class="w-20 h-20 bg-gray-200 rounded hover:bg-gray-300"></button>
        </div>
      </div>

      <!-- Product Info -->
      <div class="space-y-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>
          <p class="text-gray-600 mt-2">{{ product.farmer_name }}</p>
          <div class="flex items-center mt-2">
            <span class="text-yellow-400">★★★★★</span>
            <span class="ml-2 text-gray-600">({{ product.reviews_count || 0 }} reviews)</span>
          </div>
        </div>

        <div class="border-t border-b py-4">
          <p class="text-4xl font-bold text-green-600">Ksh {{ product.price }}</p>
          <p class="text-gray-600 mt-2">Available: {{ product.quantity }} {{ product.unit }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
          <div class="flex items-center space-x-4">
            <button @click="quantity = Math.max(1, quantity - 1)" class="btn-secondary px-3 py-2">-</button>
            <span class="text-xl font-bold">{{ quantity }}</span>
            <button @click="quantity++" class="btn-secondary px-3 py-2">+</button>
          </div>
        </div>

        <div class="space-y-2">
          <button @click="addToCart" class="btn-primary w-full px-4 py-3 text-lg">
            Add to Cart
          </button>
          <button @click="addToWishlist" class="btn-secondary w-full px-4 py-3">
            Add to Wishlist
          </button>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg">
          <h3 class="font-bold text-gray-900 mb-2">Description</h3>
          <p class="text-gray-600">{{ product.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { marketplaceAPI } from '@/api/marketplace'
import { useBuyerStore } from '@/stores/buyerStore'

const route = useRoute()
const router = useRouter()
const buyerStore = useBuyerStore()
const product = ref<any>(null)
const loading = ref(false)
const quantity = ref(1)

onMounted(async () => {
  loading.value = true
  try {
    const response = await marketplaceAPI.getPublicProduct(Number(route.params.id))
    product.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to load product:', error)
  } finally {
    loading.value = false
  }
})

const addToCart = async () => {
  try {
    await buyerStore.addToCart({
      product_id: product.value.id,
      quantity: quantity.value
    })
    router.push('/app/buyer/cart')
  } catch (error) {
    console.error('Failed to add to cart:', error)
  }
}

const addToWishlist = async () => {
  try {
    await buyerStore.addToWishlist({ product_id: product.value.id })
    alert('Added to wishlist')
  } catch (error) {
    console.error('Failed to add to wishlist:', error)
  }
}
</script>
