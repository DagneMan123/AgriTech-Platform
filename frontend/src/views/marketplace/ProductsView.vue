<template>
  <DashboardLayout>
    <template #title>Marketplace Products</template>
    <template #subtitle>Browse all available agricultural products</template>

    <!-- Filters & Search -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <FormField
          v-model="searchQuery"
          type="text"
          placeholder="Search products..."
          label="Search"
        />
        <FormSelect
          v-model="filterCategory"
          :options="categoryOptions"
          label="Category"
          placeholder="All categories"
        />
        <FormSelect
          v-model="filterSort"
          :options="sortOptions"
          label="Sort By"
        />
        <div class="flex items-end">
          <AppButton class="w-full">Filter</AppButton>
        </div>
      </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <RouterLink
        v-for="product in products"
        :key="product.id"
        :to="`/marketplace/product/${product.id}`"
        class="group"
      >
        <AppCard class="group-hover:shadow-lg transition h-full flex flex-col">
          <!-- Product Image -->
          <div class="aspect-square bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg mb-4 flex items-center justify-center group-hover:scale-105 transition overflow-hidden">
            <img v-if="product.image" :src="product.image" :alt="product.name" class="w-full h-full object-cover" />
            <svg v-else class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>

          <!-- Product Info -->
          <h3 class="text-lg font-semibold text-gray-900 group-hover:text-green-600 transition line-clamp-2">
            {{ product.name }}
          </h3>
          <p class="text-sm text-gray-600 mt-1">{{ product.seller }}</p>

          <!-- Rating -->
          <div class="flex items-center mt-2 mb-3">
            <span class="text-yellow-400">★</span>
            <span class="text-sm text-gray-600 ml-1">{{ product.rating }} ({{ product.reviews }} reviews)</span>
          </div>

          <!-- Price -->
          <div class="mt-auto">
            <div class="flex items-center justify-between">
              <span class="text-2xl font-bold text-green-600">${{ product.price }}</span>
              <span v-if="product.originalPrice" class="text-sm text-gray-500 line-through">
                ${{ product.originalPrice }}
              </span>
            </div>
            <button class="mt-3 w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
              Add to Cart
            </button>
          </div>
        </AppCard>
      </RouterLink>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
      <AppPagination :total-items="100" :items-per-page="20" @change="currentPage = $event" />
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AppCard from '@/components/common/AppCard.vue'
import AppButton from '@/components/common/AppButton.vue'
import AppPagination from '@/components/common/AppPagination.vue'
import FormField from '@/components/forms/FormField.vue'
import FormSelect from '@/components/forms/FormSelect.vue'
import { RouterLink } from 'vue-router'

const searchQuery = ref('')
const filterCategory = ref('')
const filterSort = ref('popular')
const currentPage = ref(1)

const categoryOptions = [
  { value: 'produce', label: 'Fresh Produce' },
  { value: 'grains', label: 'Grains & Cereals' },
  { value: 'dairy', label: 'Dairy Products' },
  { value: 'livestock', label: 'Livestock' }
]

const sortOptions = [
  { value: 'popular', label: 'Most Popular' },
  { value: 'price-low', label: 'Price: Low to High' },
  { value: 'price-high', label: 'Price: High to Low' },
  { value: 'rating', label: 'Highest Rated' },
  { value: 'newest', label: 'Newest' }
]

const products = ref([
  { id: 1, name: 'Organic Tomatoes', seller: 'Green Farm Co', price: 3.99, originalPrice: 5.99, rating: 4.8, reviews: 245, image: null },
  { id: 2, name: 'Fresh Lettuce', seller: 'Vegetable Valley', price: 2.50, originalPrice: null, rating: 4.5, reviews: 128, image: null },
  { id: 3, name: 'Whole Wheat Bread', seller: 'Grain Mill', price: 4.25, originalPrice: null, rating: 4.9, reviews: 342, image: null },
  { id: 4, name: 'Fresh Milk (1L)', seller: 'Dairy Farms', price: 2.99, originalPrice: 3.49, rating: 4.6, reviews: 89, image: null }
])
</script>
