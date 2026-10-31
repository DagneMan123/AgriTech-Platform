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

  // Payment Methods
  getPaymentMethods: () => apiClient.get('/buyer/payment-methods'),
  createPaymentMethod: (data: any) => apiClient.post('/buyer/payment-methods', data),
  deletePaymentMethod: (id: number | string) => apiClient.delete(`/buyer/payment-methods/${id}`),
  setDefaultPaymentMethod: (id: number | string) => apiClient.post(`/buyer/payment-methods/${id}/default`),
  getPaymentTransactions: () => apiClient.get('/buyer/payment-methods/transactions'),

  // Chapa Payment Gateway (Telebirr, CBE Birr, Awash, Cards)
  initializeChapaPayment: (data: any) => apiClient.post('/buyer/chapa/initialize', data),
  verifyChapaPayment: (txRef: string) => apiClient.get(`/buyer/chapa/verify/${txRef}`),
  getChapaBanks: () => apiClient.get('/buyer/chapa/banks'),

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

  // Returns & Refunds
  getReturns: () => apiClient.get('/buyer/returns'),
  createReturn: (data: any) => apiClient.post('/buyer/returns', data),
  getReturn: (id: number) => apiClient.get('/buyer/returns/' + id),
  cancelReturn: (id: number) => apiClient.post('/buyer/returns/' + id + '/cancel'),

  // Dashboard
  getDashboard: () => apiClient.get('/buyer/dashboard'),

  // Delivery & Tracking
  getDeliveryTracking: (params?: any) => apiClient.get('/buyer/dashboard/delivery-tracking', { params }),
  getDeliveryAddresses: () => apiClient.get('/buyer/delivery-addresses'),
  createDeliveryAddress: (data: any) => apiClient.post('/buyer/delivery-addresses', data),
  updateDeliveryAddress: (id: number | string, data: any) => apiClient.put(`/buyer/delivery-addresses/${id}`, data),
  deleteDeliveryAddress: (id: number | string) => apiClient.delete(`/buyer/delivery-addresses/${id}`),
  setDefaultDeliveryAddress: (id: number | string) => apiClient.post(`/buyer/delivery-addresses/${id}/default`),

  // Transport Coordination
  getTransportCoordination: () => apiClient.get('/buyer/transport/coordination'),
  createTransportCoordination: (data: any) => apiClient.post('/buyer/transport/coordination', data),
  cancelTransportCoordination: (id: number | string) => apiClient.post(`/buyer/transport/coordination/${id}/cancel`),
  getAvailableTransporters: () => apiClient.get('/buyer/transport/transporters'),

  // Direct Contracts & Contract Farming
  getContracts: (params?: any) => apiClient.get('/buyer/contracts', { params }),
  getContractFarmers: () => apiClient.get('/buyer/contracts/farmers'),
  createContract: (data: any) => apiClient.post('/buyer/contracts', data),
  getContract: (id: number) => apiClient.get('/buyer/contracts/' + id),
  cancelContract: (id: number, reason?: string) => apiClient.post('/buyer/contracts/' + id + '/cancel', { reason }),

  // Market Prices & Commodity Trends
  getMarketPrices: (params?: any) => apiClient.get('/market-prices', { params }),
  getMarketPriceTrends: (params?: any) => apiClient.get('/market-prices/trends', { params }),
}
