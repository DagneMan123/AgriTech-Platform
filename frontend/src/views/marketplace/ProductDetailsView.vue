<template>
  <DashboardLayout>
    <template #title>{{ product.name }}</template>
    <template #subtitle>{{ product.seller }} - Product Details</template>

    <!-- Breadcrumb -->
    <Breadcrumb :items="breadcrumbs" class="mb-6" />

    <!-- Product Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Product Image & Gallery -->
      <div class="lg:col-span-1">
        <AppCard class="sticky top-24">
          <div class="aspect-square bg-gray-200 rounded-lg mb-4 flex items-center justify-center">
            <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>

          <!-- Quick Actions -->
          <div class="space-y-2">
            <AppButton class="w-full bg-green-600 hover:bg-green-700">
              <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Add to Cart
            </AppButton>
            <AppButton class="w-full border border-gray-300 text-gray-700 hover:bg-gray-50">
              <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
              Add to Wishlist
            </AppButton>
          </div>
        </AppCard>
      </div>

      <!-- Product Information -->
      <div class="lg:col-span-2">
        <!-- Rating -->
        <div class="flex items-center mb-4">
          <div class="flex">
            <span v-for="i in 5" :key="i" :class="i <= Math.round(product.rating) ? 'text-yellow-400' : 'text-gray-300'">★</span>
          </div>
          <span class="ml-3 text-lg font-semibold">{{ product.rating }}/5</span>
          <span class="ml-2 text-gray-600">({{ product.reviews }} reviews)</span>
        </div>

        <!-- Price -->
        <div class="mb-6">
          <div class="flex items-baseline gap-2">
            <span class="text-4xl font-bold text-green-600">${{ product.price }}</span>
            <span v-if="product.originalPrice" class="text-xl text-gray-500 line-through">
              ${{ product.originalPrice }}
            </span>
            <span v-if="product.discount" class="ml-2 px-3 py-1 bg-red-100 text-red-600 rounded-full text-sm font-semibold">
              Save {{ product.discount }}%
            </span>
          </div>
          <p class="text-sm text-gray-600 mt-2">Free shipping on orders over $50</p>
        </div>

        <!-- Product Description -->
        <AppCard title="Description" class="mb-6">
          <p class="text-gray-700">{{ product.description }}</p>
        </AppCard>

        <!-- Specifications -->
        <AppCard title="Specifications" class="mb-6">
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600">Category</span>
              <span class="font-semibold">{{ product.category }}</span>
            </div>
            <div class="flex justify-between border-t pt-3">
              <span class="text-gray-600">Quantity Available</span>
              <span class="font-semibold">{{ product.stock }} units</span>
            </div>
            <div class="flex justify-between border-t pt-3">
              <span class="text-gray-600">Unit Size</span>
              <span class="font-semibold">{{ product.unitSize }}</span>
            </div>
            <div class="flex justify-between border-t pt-3">
              <span class="text-gray-600">Origin</span>
              <span class="font-semibold">{{ product.origin }}</span>
            </div>
          </div>
        </AppCard>

        <!-- Seller Information -->
        <AppCard title="Seller Information">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                {{ product.seller.charAt(0) }}
              </div>
              <div class="ml-4">
                <p class="font-semibold">{{ product.seller }}</p>
                <p class="text-sm text-gray-600">Member since 2024</p>
              </div>
            </div>
            <AppButton variant="secondary">Visit Store</AppButton>
          </div>
        </AppCard>
      </div>
    </div>

    <!-- Reviews Section -->
    <div class="mt-8">
      <AppCard title="Customer Reviews">
        <div class="space-y-4">
          <div v-for="review in reviews" :key="review.id" class="pb-4 border-b last:border-b-0">
            <div class="flex justify-between items-start">
              <div>
                <p class="font-semibold">{{ review.author }}</p>
                <div class="flex mt-1">
                  <span v-for="i in 5" :key="i" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                </div>
              </div>
              <span class="text-sm text-gray-600">{{ review.date }}</span>
            </div>
            <p class="mt-2 text-gray-700">{{ review.comment }}</p>
          </div>
        </div>
      </AppCard>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AppCard from '@/components/common/AppCard.vue'
import AppButton from '@/components/common/AppButton.vue'
import Breadcrumb from '@/components/layout/Breadcrumb.vue'

const breadcrumbs = ref([
  { label: 'Marketplace', path: '/marketplace' },
  { label: 'Fresh Produce', path: '/marketplace/category/1' },
  { label: 'Organic Tomatoes' }
])

const product = ref({
  id: 1,
  name: 'Organic Tomatoes - Premium Quality',
  seller: 'Green Farm Co',
  price: 3.99,
  originalPrice: 5.99,
  discount: 33,
  rating: 4.8,
  reviews: 245,
  description: 'Our premium organic tomatoes are grown without synthetic pesticides or fertilizers. Hand-picked at peak ripeness for maximum flavor and nutrition. Perfect for salads, cooking, or fresh eating.',
  category: 'Fresh Produce',
  stock: 156,
  unitSize: '1kg',
  origin: 'Local Farm'
})

const reviews = ref([
  { id: 1, author: 'Sarah M.', rating: 5, comment: 'Excellent quality! Fresh and delicious. Will order again.', date: '2 days ago' },
  { id: 2, author: 'John D.', rating: 4, comment: 'Good quality overall. Arrived in perfect condition.', date: '1 week ago' },
  { id: 3, author: 'Emily R.', rating: 5, comment: 'Best tomatoes I have bought online. Highly recommend!', date: '2 weeks ago' }
])
</script>
