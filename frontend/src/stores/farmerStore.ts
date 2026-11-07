import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { farmerAPI } from '@/api/farmer'

export const useFarmerStore = defineStore('farmer', () => {
  const farms = ref<any[]>([])
  const crops = ref<any[]>([])
  const harvests = ref<any[]>([])
  const products = ref<any[]>([])
  const orders = ref<any[]>([])
  const consultations = ref<any[]>([])
  const loans = ref<any[]>([])
  const contracts = ref<any[]>([])
  const contractStats = ref<any>({
    total_contracts: 0,
    active_contracts: 0,
    pending_contracts: 0,
    completed_contracts: 0,
    total_contracted_value: 0,
    advance_escrow_held: 0
  })
  const pendingContractsCount = computed(() => {
    if (contractStats.value && typeof contractStats.value.pending_contracts === 'number') {
      return contractStats.value.pending_contracts
    }
    const list = Array.isArray(contracts.value) ? contracts.value : []
    return list.filter(c => ['proposed', 'draft', 'pending'].includes((c.status || '').toLowerCase())).length
  })
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

  // Forward Contracts
  const fetchContracts = async (params?: any) => {
    loading.value = true
    try {
      const response = await farmerAPI.getContracts(params)
      const raw = response.data?.data || response.data
      const list = Array.isArray(raw) ? raw : (Array.isArray(raw?.data) ? raw.data : [])
      contracts.value = list
      if (response.data?.stats) {
        contractStats.value = response.data.stats
      }
      return list
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch contracts'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createContract = async (data: any) => {
    try {
      const response = await farmerAPI.createContract(data)
      const created = response.data?.data || response.data
      contracts.value.unshift(created)
      if (contractStats.value) {
        contractStats.value.total_contracts++
        contractStats.value.pending_contracts++
      }
      return created
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to create contract'
      throw err
    }
  }

  const acceptContract = async (id: number, data?: { notes?: string }) => {
    try {
      const response = await farmerAPI.acceptContract(id, data)
      const updated = response.data?.data || response.data
      const index = contracts.value.findIndex(c => c.id === id)
      if (index > -1) {
        contracts.value[index] = updated
      }
      if (contractStats.value) {
        if (contractStats.value.pending_contracts > 0) contractStats.value.pending_contracts--
        contractStats.value.active_contracts++
      }
      return updated
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to accept contract'
      throw err
    }
  }

  const rejectContract = async (id: number, data?: { reason?: string }) => {
    try {
      const response = await farmerAPI.rejectContract(id, data)
      const updated = response.data?.data || response.data
      const index = contracts.value.findIndex(c => c.id === id)
      if (index > -1) {
        contracts.value[index] = updated
      }
      if (contractStats.value && contractStats.value.pending_contracts > 0) {
        contractStats.value.pending_contracts--
      }
      return updated
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to reject contract'
      throw err
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
    contracts,
    contractStats,
    pendingContractsCount,
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
    fetchContracts,
    createContract,
    acceptContract,
    rejectContract,
  }
})
