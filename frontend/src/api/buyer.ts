import apiClient from './config'

export const buyerAPI = {
  // Profile
  getProfile: () => apiClient.get('/buyer/profile'),
  
  updateProfile: (data: any) => apiClient.post('/buyer/profile', data),

  // Cart
  getCart: () => apiClient.get('/buyer/cart'),
  
  addToCart: (data: any) => apiClient.post('/buyer/cart', data),
  
  updateCartItem: (id: number, data: any) => apiClient.put(`/buyer/cart/${id}`, data),
  
  removeFromCart: (id: number) => apiClient.delete(`/buyer/cart/${id}`),
  
  checkout: (data: any) => apiClient.post('/buyer/cart/checkout', data),

  // Orders
  getOrders: () => apiClient.get('/buyer/orders'),
  
  getOrder: (id: number) => apiClient.get(`/buyer/orders/${id}`),
  
  createOrder: (data: any) => apiClient.post('/buyer/orders', data),
  
  cancelOrder: (id: number) => apiClient.post(`/buyer/orders/${id}/cancel`),

  // Payments
  getPayments: () => apiClient.get('/buyer/payments'),
  
  createPayment: (data: any) => apiClient.post('/buyer/payments', data),
  
  verifyPayment: (id: number) => apiClient.post(`/buyer/payments/${id}/verify`),

  // Reviews
  getReviews: () => apiClient.get('/buyer/reviews'),
  
  createReview: (data: any) => apiClient.post('/buyer/reviews', data),
  
  updateReview: (id: number, data: any) => apiClient.put(`/buyer/reviews/${id}`, data),
  
  deleteReview: (id: number) => apiClient.delete(`/buyer/reviews/${id}`),

  // Wishlist
  getWishlist: () => apiClient.get('/buyer/wishlist'),
  
  addToWishlist: (data: any) => apiClient.post('/buyer/wishlist', data),
  
  removeFromWishlist: (id: number) => apiClient.delete(`/buyer/wishlist/${id}`),

  // Marketplace
  searchMarketplace: (query: string, filters?: any) => 
    apiClient.get('/buyer/marketplace/search', { params: { q: query, ...filters } }),
  
  getMarketplace: (page?: number, limit?: number) => 
    apiClient.get('/buyer/marketplace', { params: { page, limit } }),

  // Dashboard
  getDashboard: () => apiClient.get('/buyer/dashboard'),
}
