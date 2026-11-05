import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { buyerAPI } from '@/api/buyer'

export const useBuyerStore = defineStore('buyer', () => {
  const cart = ref<any[]>([])
  const orders = ref<any[]>([])
  const wishlist = ref<any[]>([])
  const returns = ref<any[]>([])
  const contracts = ref<any[]>([])
  const contractStats = ref<any>(null)
  const contractFarmers = ref<any[]>([])
  const dashboard = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const cartCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + (Number(item.quantity) || 1), 0)
  })

  // Cart
  const fetchCart = async () => {
    loading.value = true
    try {
      const response = await buyerAPI.getCart()
      cart.value = Array.isArray(response.data?.data)
        ? response.data.data
        : (Array.isArray(response.data) ? response.data : [])
      return cart.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch cart'
      throw err
    } finally {
      loading.value = false
    }
  }

  const addToCart = async (data: any) => {
    try {
      const response = await buyerAPI.addToCart(data)
      await fetchCart()
      return response.data.data || response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to add to cart'
      throw err
    }
  }

  const updateCartItem = async (id: number, data: any) => {
    try {
      const response = await buyerAPI.updateCartItem(id, data)
      await fetchCart()
      return response.data.data || response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to update cart item'
      throw err
    }
  }

  const removeFromCart = async (id: number) => {
    try {
      await buyerAPI.removeFromCart(id)
      cart.value = cart.value.filter(item => item.id !== id && item.cart_id !== id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to remove from cart'
      throw err
    }
  }

  const checkout = async (data: any) => {
    try {
      const response = await buyerAPI.checkout(data)
      cart.value = []
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to checkout'
      throw err
    }
  }

  // Orders
  const fetchOrders = async (params?: any) => {
    loading.value = true
    error.value = null
    try {
      const response = await buyerAPI.getOrders(params)
      const raw = response.data?.orders || response.data?.data || response.data
      const list = Array.isArray(raw) ? raw : (Array.isArray(raw?.data) ? raw.data : [])
      orders.value = list
      return list
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch orders'
      throw err
    } finally {
      loading.value = false
    }
  }

  const cancelOrder = async (id: number | string, reason = 'Cancelled by buyer') => {
    try {
      const response = await buyerAPI.cancelOrder(id, { reason })
      const updated = response.data?.data || response.data
      const index = orders.value.findIndex(o => o.id === id || o.order_number === id)
      if (index > -1 && updated) {
        orders.value[index] = { ...orders.value[index], ...updated, status: 'cancelled' }
      }
      return updated
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to cancel order'
      throw err
    }
  }

  // Returns & Refunds
  const fetchReturns = async () => {
    loading.value = true
    try {
      const response = await buyerAPI.getReturns()
      returns.value = response.data.data || response.data
      return returns.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch returns'
      throw err
    } finally {
      loading.value = false
    }
  }

  const createReturn = async (data: any) => {
    try {
      const response = await buyerAPI.createReturn(data)
      const newRet = response.data.data || response.data
      returns.value.unshift(newRet)
      return newRet
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to submit return request'
      throw err
    }
  }

  const cancelReturn = async (id: number) => {
    try {
      const response = await buyerAPI.cancelReturn(id)
      const updated = response.data.data || response.data
      const index = returns.value.findIndex(r => r.id === id)
      if (index > -1) {
        returns.value[index] = updated
      }
      return updated
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to cancel return request'
      throw err
    }
  }

  // Wishlist
  const fetchWishlist = async () => {
    loading.value = true
    try {
      const response = await buyerAPI.getWishlist()
      const raw = response.data?.data?.data || response.data?.data || response.data || []
      wishlist.value = Array.isArray(raw) ? raw : []
      return wishlist.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch wishlist'
      wishlist.value = []
      throw err
    } finally {
      loading.value = false
    }
  }

  const addToWishlist = async (data: any) => {
    try {
      const response = await buyerAPI.addToWishlist(data)
      const newItem = response.data?.data || response.data
      if (!Array.isArray(wishlist.value)) {
        wishlist.value = []
      }
      if (newItem && typeof newItem === 'object') {
        wishlist.value.push(newItem)
      }
      return newItem
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to add to wishlist'
      throw err
    }
  }

  const removeFromWishlist = async (id: number) => {
    try {
      await buyerAPI.removeFromWishlist(id)
      if (Array.isArray(wishlist.value)) {
        wishlist.value = wishlist.value.filter(item => item.id !== id && item.product_id !== id)
      }
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to remove from wishlist'
      throw err
    }
  }

  // Dashboard
  const fetchDashboard = async () => {
    loading.value = true
    try {
      const response = await buyerAPI.getDashboard()
      dashboard.value = response.data.data || response.data
      return dashboard.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch dashboard'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Direct Contracts
  const fetchContracts = async (params?: any) => {
    loading.value = true
    try {
      const response = await buyerAPI.getContracts(params)
      contracts.value = response.data.data?.data || response.data.data || response.data
      if (response.data.stats) {
        contractStats.value = response.data.stats
      }
      return contracts.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch contracts'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchContractFarmers = async () => {
    try {
      const response = await buyerAPI.getContractFarmers()
      contractFarmers.value = response.data.data || response.data
      return contractFarmers.value
    } catch (err: any) {
      console.error('Failed to fetch contract farmers:', err)
      return []
    }
  }

  const createContract = async (data: any) => {
    loading.value = true
    try {
      const response = await buyerAPI.createContract(data)
      const newContract = response.data.data || response.data
      contracts.value.unshift(newContract)
      return newContract
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to create contract'
      throw err
    } finally {
      loading.value = false
    }
  }

  const cancelContract = async (id: number, reason?: string) => {
    try {
      const response = await buyerAPI.cancelContract(id, reason)
      await fetchContracts()
      return response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to cancel contract'
      throw err
    }
  }

  return {
    cart,
    cartCount,
    orders,
    wishlist,
    returns,
    contracts,
    contractStats,
    contractFarmers,
    dashboard,
    loading,
    error,
    fetchCart,
    addToCart,
    updateCartItem,
    removeFromCart,
    checkout,
    fetchOrders,
    cancelOrder,
    fetchReturns,
    createReturn,
    cancelReturn,
    fetchContracts,
    fetchContractFarmers,
    createContract,
    cancelContract,
    fetchWishlist,
    addToWishlist,
    removeFromWishlist,
    fetchDashboard,
  }
})
