<template>
  <DashboardLayout>
    <template #title>Search Products</template>
    <template #subtitle>Find exactly what you're looking for</template>

    <!-- Search Box -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="flex gap-2">
        <FormField
          v-model="searchQuery"
          type="text"
          placeholder="Search for products, sellers, categories..."
          class="flex-1"
        />
        <AppButton class="px-8">
          <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Search
        </AppButton>
      </div>

      <!-- Popular Searches -->
      <div v-if="!searchQuery" class="mt-4">
        <p class="text-sm text-gray-600 font-medium mb-2">Popular Searches:</p>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="search in popularSearches"
            :key="search"
            class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-green-100 hover:text-green-700 transition"
            @click="searchQuery = search"
          >
            {{ search }}
          </button>
        </div>
      </div>
    </div>

    <!-- Search Results -->
    <div v-if="searchQuery">
      <h2 class="text-xl font-bold text-gray-900 mb-6">
        Results for "{{ searchQuery }}" ({{ totalResults }} found)
      </h2>

      <!-- Filter Sidebar & Results Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Filters -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <div>
              <h3 class="font-semibold text-gray-900 mb-3">Category</h3>
              <div class="space-y-2">
                <label v-for="cat in categories" :key="cat" class="flex items-center">
                  <input type="checkbox" class="rounded border-gray-300" />
                  <span class="ml-2 text-sm text-gray-700">{{ cat }}</span>
                </label>
              </div>
            </div>

            <div class="border-t pt-6">
              <h3 class="font-semibold text-gray-900 mb-3">Price Range</h3>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input type="checkbox" class="rounded border-gray-300" />
                  <span class="ml-2 text-sm text-gray-700">$0 - $10</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" class="rounded border-gray-300" />
                  <span class="ml-2 text-sm text-gray-700">$10 - $50</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" class="rounded border-gray-300" />
                  <span class="ml-2 text-sm text-gray-700">$50+</span>
                </label>
              </div>
            </div>

            <div class="border-t pt-6">
              <h3 class="font-semibold text-gray-900 mb-3">Rating</h3>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input type="checkbox" class="rounded border-gray-300" />
                  <span class="ml-2 text-sm text-gray-700">★★★★★ 5 Star</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" class="rounded border-gray-300" />
                  <span class="ml-2 text-sm text-gray-700">★★★★ 4+ Star</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" class="rounded border-gray-300" />
                  <span class="ml-2 text-sm text-gray-700">★★★ 3+ Star</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Results Grid -->
        <div class="lg:col-span-3">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <AppCard v-for="product in searchResults" :key="product.id" class="group hover:shadow-lg transition">
              <div class="aspect-square bg-gray-200 rounded-lg mb-4 flex items-center justify-center group-hover:scale-105 transition">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 class="font-semibold text-gray-900 line-clamp-2">{{ product.name }}</h3>
              <p class="text-sm text-gray-600 mt-1">{{ product.seller }}</p>
              <div class="flex items-center mt-2">
                <span class="text-yellow-400">★</span>
                <span class="text-sm text-gray-600 ml-1">{{ product.rating }}</span>
              </div>
              <p class="text-xl font-bold text-green-600 mt-3">${{ product.price }}</p>
            </AppCard>
          </div>
        </div>
      </div>
    </div>

    <!-- No Search Yet -->
    <div v-else class="text-center py-12">
      <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <p class="text-gray-500 text-lg">Enter a search query to find products</p>
    </div>
  </DashboardLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import AppCard from '@/components/common/AppCard.vue'
import AppButton from '@/components/common/AppButton.vue'
import FormField from '@/components/forms/FormField.vue'

const searchQuery = ref('')
const popularSearches = ref(['Organic Tomatoes', 'Wheat Grain', 'Fresh Milk', 'Chicken Meat', 'Honey'])
const categories = ref(['Fresh Produce', 'Grains', 'Dairy', 'Livestock', 'Processed Foods'])

const mockSearchResults = ref([
  { id: 1, name: 'Organic Tomatoes - Fresh', seller: 'Green Farm', price: 3.99, rating: 4.8 },
  { id: 2, name: 'Heirloom Tomato Variety', seller: 'Heritage Seeds', price: 5.50, rating: 4.9 },
  { id: 3, name: 'Cherry Tomatoes (1kg)', seller: 'Valley Farms', price: 4.25, rating: 4.6 }
])

const totalResults = computed(() => searchQuery.value ? 324 : 0)
const searchResults = computed(() => searchQuery.value ? mockSearchResults.value : [])
</script>
