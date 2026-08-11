import { defineStore } from 'pinia'
import { ref } from 'vue'
import { farmerAPI } from '@/api/farmer'

export const useFarmerStore = defineStore('farmer', () => {
  const farms = ref<any[]>([])
  const crops = ref<any[]>([])
  const harvests = ref<any[]>([])
  const products = ref<any[]>([])
  const orders = ref<any[]>([])
  const consultations = ref<any[]>([])
  const loans = ref<any[]>([])
  const dashboard = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Farms
  const fetchFarms = async () => {
    loading.value = true
    try {
      const response = await farmerAPI.getFarms()
      farms.value = response.data.data || response.data
      return farms.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch farms'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createFarm = async (formData: FormData) => {
    try {
      const response = await farmerAPI.createFarm(formData)
      farms.value.push(response.data.data || response.data)
      return response.data.data || response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to create farm'
      throw err
    }
  }

  const updateFarm = async (id: number, formData: FormData) => {
    try {
      const response = await farmerAPI.updateFarm(id, formData)
      const index = farms.value.findIndex(f => f.id === id)
      if (index > -1) {
        farms.value[index] = response.data.data || response.data
      }
      return response.data.data || response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to update farm'
      throw err
    }
  }

  const deleteFarm = async (id: number) => {
    try {
      await farmerAPI.deleteFarm(id)
      farms.value = farms.value.filter(f => f.id !== id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to delete farm'
      throw err
    }
  }

  // Crops
  const fetchCrops = async () => {
    loading.value = true
    try {
      const response = await farmerAPI.getCrops()
      crops.value = response.data.data || response.data
      return crops.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch crops'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createCrop = async (data: any) => {
    try {
      const response = await farmerAPI.createCrop(data)
      crops.value.push(response.data.data || response.data)
      return response.data.data || response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to create crop'
      throw err
    }
  }

  // Products
  const fetchProducts = async () => {
    loading.value = true
    try {
      const response = await farmerAPI.getProducts()
      products.value = response.data.data || response.data
      return products.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch products'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createProduct = async (formData: FormData) => {
    try {
      const response = await farmerAPI.createProduct(formData)
      products.value.push(response.data.data || response.data)
      return response.data.data || response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to create product'
      throw err
    }
  }

  // Orders
  const fetchOrders = async () => {
    loading.value = true
    try {
      const response = await farmerAPI.getOrders()
      orders.value = response.data.data || response.data
      return orders.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch orders'
      throw err
    } finally {
      loading.value = false
    }
  }

  const acceptOrder = async (id: number) => {
    try {
      const response = await farmerAPI.acceptOrder(id)
      const index = orders.value.findIndex(o => o.id === id)
      if (index > -1) {
        orders.value[index] = response.data.data || response.data
      }
      return response.data.data || response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to accept order'
      throw err
    }
  }

  // Dashboard
  const fetchDashboard = async () => {
    loading.value = true
    try {
      const response = await farmerAPI.getDashboard()
      dashboard.value = response.data.data || response.data
      return dashboard.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch dashboard'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    farms,
    crops,
    harvests,
    products,
    orders,
    consultations,
    loans,
    dashboard,
    loading,
    error,
    fetchFarms,
    createFarm,
    updateFarm,
    deleteFarm,
    fetchCrops,
    createCrop,
    fetchProducts,
    createProduct,
    fetchOrders,
    acceptOrder,
    fetchDashboard,
  }
})
