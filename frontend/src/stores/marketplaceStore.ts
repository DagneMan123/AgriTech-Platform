import { defineStore } from 'pinia'
import { ref } from 'vue'
import { marketplaceAPI } from '@/api/marketplace'

export const useMarketplaceStore = defineStore('marketplace', () => {
  const products = ref<any[]>([])
  const categories = ref<any[]>([])
  const marketPrices = ref<any[]>([])
  const weather = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const fetchProducts = async (page?: number, limit?: number, filters?: any) => {
    loading.value = true
    try {
      const response = await marketplaceAPI.getPublicProducts(page, limit, filters)
      products.value = response.data.data || response.data
      return products.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch products'
      throw err
    } finally {
      loading.value = false
    }
  }

  const searchProducts = async (query: string, filters?: any) => {
    loading.value = true
    try {
      const response = await marketplaceAPI.searchProducts(query, filters)
      products.value = response.data.data || response.data
      return products.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Search failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchCategories = async () => {
    try {
      const response = await marketplaceAPI.getCategories()
      categories.value = response.data.data || response.data
      return categories.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch categories'
      throw err
    }
  }

  const fetchMarketPrices = async (filters?: any) => {
    try {
      const response = await marketplaceAPI.getMarketPrices(filters)
      marketPrices.value = response.data.data || response.data
      return marketPrices.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch market prices'
      throw err
    }
  }

  const fetchWeather = async (filters?: any) => {
    try {
      const response = await marketplaceAPI.getWeather(filters)
      weather.value = response.data.data || response.data
      return weather.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch weather'
      throw err
    }
  }

  return {
    products,
    categories,
    marketPrices,
    weather,
    loading,
    error,
    fetchProducts,
    searchProducts,
    fetchCategories,
    fetchMarketPrices,
    fetchWeather,
  }
})
