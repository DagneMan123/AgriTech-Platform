<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">My Wishlist</h1>

    <div v-if="buyerStore.wishlist.length === 0" class="card text-center py-12">
      <p class="text-gray-500">Your wishlist is empty</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="item in buyerStore.wishlist" :key="item.id" class="card">
        <h3 class="font-bold">{{ item.product_name }}</h3>
        <p class="text-green-600 font-semibold text-lg">Ksh {{ item.price }}</p>
        <div class="flex space-x-2 mt-4">
          <button class="btn-primary px-3 py-2 flex-1">Add to Cart</button>
          <button @click="removeFromWishlist(item.id)" class="btn-danger px-3 py-2">❌</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useBuyerStore } from '@/stores/buyerStore'
import { onMounted } from 'vue'

const buyerStore = useBuyerStore()

onMounted(async () => {
  try {
    await buyerStore.fetchWishlist()
  } catch (error) {
    console.error('Failed to load wishlist:', error)
  }
})

const removeFromWishlist = async (id: number) => {
  try {
    await buyerStore.removeFromWishlist(id)
  } catch (error) {
    console.error('Failed to remove from wishlist:', error)
  }
}
</script>
