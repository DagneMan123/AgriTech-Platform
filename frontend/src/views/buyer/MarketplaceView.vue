<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Marketplace</h1>

    <!-- Search and Filters -->
    <div class="card space-y-4">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search products..."
        @input="handleSearch"
        class="input-field"
      />
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <select v-model="selectedCategory" @change="handleSearch" class="input-field">
          <option value="">All Categories</option>
          <option value="vegetables">Vegetables</option>
          <option value="grains">Grains</option>
          <option value="fruits">Fruits</option>
        </select>
      </div>
    </div>

    <!-- Products Grid -->
    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Loading products...</p>
    </div>

    <div v-else-if="marketplaceStore.products.length === 0" class="card text-center py-12">
      <p class="text-gray-500">No products found</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="product in marketplaceStore.products" :key="product.id" class="card hover:shadow-lg transition cursor-pointer" @click="viewProduct(product.id)">
        <img v-if="product.image" :src="product.image" :alt="product.name" class="w-full h-40 object-cover rounded mb-4" />
        <h3 class="text-lg font-bold text-gray-900">{{ product.name }}</h3>
        <p class="text-gray-600 text-sm">{{ product.farmer_name }}</p>
        <p class="text-green-600 font-semibold text-lg mt-2">Ksh {{ product.price }}</p>
        <div class="mt-4 flex space-x-2">
          <button @click.stop="addToCart(product)" class="btn-primary px-3 py-2 flex-1">
            Add to Cart
          </button>
          <button @click.stop="toggleWishlist(product)" class="btn-secondary px-3 py-2">
            ❤️
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketplaceStore } from '@/stores/marketplaceStore'
import { useBuyerStore } from '@/stores/buyerStore'

const router = useRouter()
const marketplaceStore = useMarketplaceStore()
const buyerStore = useBuyerStore()
const loading = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('')

onMounted(async () => {
  loading.value = true
  try {
    await marketplaceStore.fetchProducts()
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    loading.value = false
  }
})

const handleSearch = async () => {
  loading.value = true
  try {
    if (searchQuery.value) {
      await marketplaceStore.searchProducts(searchQuery.value, { category: selectedCategory.value })
    } else {
      await marketplaceStore.fetchProducts(1, 12, { category: selectedCategory.value })
    }
  } catch (error) {
    console.error('Search failed:', error)
  } finally {
    loading.value = false
  }
}

const viewProduct = (id: number) => {
  router.push(`/app/buyer/products/${id}`)
}

const addToCart = async (product: any) => {
  try {
    await buyerStore.addToCart({
      product_id: product.id,
      quantity: 1
    })
    alert('Added to cart')
  } catch (error) {
    console.error('Failed to add to cart:', error)
  }
}

const toggleWishlist = async (product: any) => {
  try {
    await buyerStore.addToWishlist({ product_id: product.id })
  } catch (error) {
    console.error('Failed to add to wishlist:', error)
  }
}
</script>
