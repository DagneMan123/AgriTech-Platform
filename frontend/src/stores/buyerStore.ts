import { defineStore } from 'pinia'
import { ref } from 'vue'
import { buyerAPI } from '@/api/buyer'

export const useBuyerStore = defineStore('buyer', () => {
  const cart = ref<any[]>([])
  const orders = ref<any[]>([])
  const wishlist = ref<any[]>([])
  const dashboard = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Cart
  const fetchCart = async () => {
    loading.value = true
    try {
      const response = await buyerAPI.getCart()
      cart.value = response.data.data || response.data
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
      cart.value.push(response.data.data || response.data)
      return response.data.data || response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to add to cart'
      throw err
    }
  }

  const removeFromCart = async (id: number) => {
    try {
      await buyerAPI.removeFromCart(id)
      cart.value = cart.value.filter(item => item.id !== id)
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
  const fetchOrders = async () => {
    loading.value = true
    try {
      const response = await buyerAPI.getOrders()
      orders.value = response.data.data || response.data
      return orders.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch orders'
      throw err
    } finally {
      loading.value = false
    }
  }

  const cancelOrder = async (id: number) => {
    try {
      const response = await buyerAPI.cancelOrder(id)
      const index = orders.value.findIndex(o => o.id === id)
      if (index > -1) {
        orders.value[index] = response.data.data || response.data
      }
      return response.data.data || response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to cancel order'
      throw err
    }
  }

  // Wishlist
  const fetchWishlist = async () => {
    loading.value = true
    try {
      const response = await buyerAPI.getWishlist()
      wishlist.value = response.data.data || response.data
      return wishlist.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to fetch wishlist'
      throw err
    } finally {
      loading.value = false
    }
  }

  const addToWishlist = async (data: any) => {
    try {
      const response = await buyerAPI.addToWishlist(data)
      wishlist.value.push(response.data.data || response.data)
      return response.data.data || response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Failed to add to wishlist'
      throw err
    }
  }

  const removeFromWishlist = async (id: number) => {
    try {
      await buyerAPI.removeFromWishlist(id)
      wishlist.value = wishlist.value.filter(item => item.id !== id)
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

  return {
    cart,
    orders,
    wishlist,
    dashboard,
    loading,
    error,
    fetchCart,
    addToCart,
    removeFromCart,
    checkout,
    fetchOrders,
    cancelOrder,
    fetchWishlist,
    addToWishlist,
    removeFromWishlist,
    fetchDashboard,
  }
})
