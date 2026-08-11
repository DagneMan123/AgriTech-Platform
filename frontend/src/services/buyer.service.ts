import apiClient from '@/api/config'

class BuyerService {
  // Profile
  getProfile() {
    return apiClient.get('/buyer/profile')
  }

  updateProfile(data: any) {
    return apiClient.post('/buyer/profile', data)
  }

  // Cart
  getCart() {
    return apiClient.get('/buyer/cart')
  }

  addToCart(data: any) {
    return apiClient.post('/buyer/cart', data)
  }

  updateCartItem(id: number, data: any) {
    return apiClient.put(`/buyer/cart/${id}`, data)
  }

  removeFromCart(id: number) {
    return apiClient.delete(`/buyer/cart/${id}`)
  }

  checkout(data: any) {
    return apiClient.post('/buyer/cart/checkout', data)
  }

  // Orders
  getOrders() {
    return apiClient.get('/buyer/orders')
  }

  getOrder(id: number) {
    return apiClient.get(`/buyer/orders/${id}`)
  }

  cancelOrder(id: number) {
    return apiClient.post(`/buyer/orders/${id}/cancel`)
  }

  // Payments
  getPayments() {
    return apiClient.get('/buyer/payments')
  }

  createPayment(data: any) {
    return apiClient.post('/buyer/payments', data)
  }

  verifyPayment(id: number) {
    return apiClient.post(`/buyer/payments/${id}/verify`)
  }

  // Reviews
  createReview(data: any) {
    return apiClient.post('/buyer/reviews', data)
  }

  updateReview(id: number, data: any) {
    return apiClient.put(`/buyer/reviews/${id}`, data)
  }

  deleteReview(id: number) {
    return apiClient.delete(`/buyer/reviews/${id}`)
  }

  // Wishlist
  getWishlist() {
    return apiClient.get('/buyer/wishlist')
  }

  addToWishlist(data: any) {
    return apiClient.post('/buyer/wishlist', data)
  }

  removeFromWishlist(id: number) {
    return apiClient.delete(`/buyer/wishlist/${id}`)
  }

  // Marketplace
  searchMarketplace(query: string, filters?: any) {
    return apiClient.get('/buyer/marketplace/search', { params: { q: query, ...filters } })
  }

  getMarketplace(page?: number, limit?: number) {
    return apiClient.get('/buyer/marketplace', { params: { page, limit } })
  }

  // Dashboard
  getDashboard() {
    return apiClient.get('/buyer/dashboard')
  }
}

export default new BuyerService()
