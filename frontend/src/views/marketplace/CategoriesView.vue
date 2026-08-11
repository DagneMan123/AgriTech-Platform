<template>
  <DashboardLayout>
    <template #title>Marketplace Categories</template>
    <template #subtitle>Browse and manage product categories</template>

    <!-- Category Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <RouterLink
        v-for="category in categories"
        :key="category.id"
        :to="`/marketplace/category/${category.id}`"
        class="group cursor-pointer"
      >
        <AppCard class="group-hover:shadow-lg transition h-full">
          <div class="aspect-video bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg mb-4 flex items-center justify-center group-hover:scale-105 transition">
            <svg class="w-16 h-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m0 0L4 7m8 4v10l8-4v-10L12 11m0 0L4 7" />
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 group-hover:text-green-600 transition">
            {{ category.name }}
          </h3>
          <p class="text-sm text-gray-600 mt-2">{{ category.productCount }} products</p>
          <p class="text-xs text-gray-500 mt-1">{{ category.description }}</p>
        </AppCard>
      </RouterLink>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AppCard from '@/components/common/AppCard.vue'
import { RouterLink } from 'vue-router'

const categories = ref([
  { id: 1, name: 'Fresh Produce', productCount: 245, description: 'Vegetables, fruits, and herbs' },
  { id: 2, name: 'Grains & Cereals', productCount: 128, description: 'Rice, wheat, maize, and more' },
  { id: 3, name: 'Dairy Products', productCount: 87, description: 'Milk, cheese, butter, yogurt' },
  { id: 4, name: 'Livestock', productCount: 156, description: 'Meat, poultry, and eggs' },
  { id: 5, name: 'Beverages', productCount: 92, description: 'Juice, tea, coffee, wine' },
  { id: 6, name: 'Oils & Spices', productCount: 134, description: 'Cooking oils, seasonings' },
  { id: 7, name: 'Processed Foods', productCount: 203, description: 'Preserved, packaged items' },
  { id: 8, name: 'Organic Products', productCount: 178, description: 'Certified organic items' }
])
</script>
