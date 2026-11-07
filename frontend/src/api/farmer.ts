import apiClient from './config'

// Farm API
export const farmerAPI = {
  // Farms
  getFarms: () => apiClient.get('/farmer/farms'),
  
  getFarm: (id: number) => apiClient.get(`/farmer/farms/${id}`),
  
  createFarm: (data: FormData) => apiClient.post('/farmer/farms', data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  updateFarm: (id: number, data: FormData) => apiClient.put(`/farmer/farms/${id}`, data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  deleteFarm: (id: number) => apiClient.delete(`/farmer/farms/${id}`),
  
  getFarmImages: (farmId: number) => apiClient.get(`/farmer/farms/${farmId}/images`),
  
  uploadFarmImages: (farmId: number, formData: FormData) => 
    apiClient.post(`/farmer/farms/${farmId}/upload-images`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),

  // Crops
  getCrops: () => apiClient.get('/farmer/crops'),
  
  getCrop: (id: number) => apiClient.get(`/farmer/crops/${id}`),
  
  createCrop: (data: any) => apiClient.post('/farmer/crops', data),
  
  updateCrop: (id: number, data: any) => apiClient.put(`/farmer/crops/${id}`, data),
  
  deleteCrop: (id: number) => apiClient.delete(`/farmer/crops/${id}`),

  // Harvests
  getHarvests: () => apiClient.get('/farmer/harvests'),
  
  getHarvest: (id: number) => apiClient.get(`/farmer/harvests/${id}`),
  
  createHarvest: (data: any) => apiClient.post('/farmer/harvests', data),
  
  updateHarvest: (id: number, data: any) => apiClient.put(`/farmer/harvests/${id}`, data),
  
  deleteHarvest: (id: number) => apiClient.delete(`/farmer/harvests/${id}`),

  // Products
  getProducts: () => apiClient.get('/farmer/products'),
  
  getProduct: (id: number) => apiClient.get(`/farmer/products/${id}`),
  
  createProduct: (data: FormData) => apiClient.post('/farmer/products', data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  updateProduct: (id: number, data: FormData) => apiClient.put(`/farmer/products/${id}`, data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  deleteProduct: (id: number) => apiClient.delete(`/farmer/products/${id}`),
  
  uploadProductImages: (productId: number, formData: FormData) =>
    apiClient.post(`/farmer/products/${productId}/upload-images`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),

  // Orders
  getOrders: () => apiClient.get('/farmer/orders'),
  
  getOrder: (id: number) => apiClient.get(`/farmer/orders/${id}`),
  
  acceptOrder: (id: number) => apiClient.post(`/farmer/orders/${id}/accept`),
  
  rejectOrder: (id: number) => apiClient.post(`/farmer/orders/${id}/reject`),

  // Consultations
  getConsultations: () => apiClient.get('/farmer/consultations'),
  
  getConsultation: (id: number) => apiClient.get(`/farmer/consultations/${id}`),
  
  createConsultation: (data: any) => apiClient.post('/farmer/consultations', data),
  
  deleteConsultation: (id: number) => apiClient.delete(`/farmer/consultations/${id}`),

  // Weather
  getWeather: () => apiClient.get('/farmer/weather'),
  
  getFarmWeather: () => apiClient.get('/farmer/weather'),

  // Loans
  getLoans: () => apiClient.get('/farmer/loans'),
  
  getLoan: (id: number) => apiClient.get(`/farmer/loans/${id}`),
  
  createLoan: (data: any) => apiClient.post('/farmer/loans', data),

  // Dashboard
  getDashboard: (timeRange?: string | number) => {
    const params = timeRange ? { time_range: timeRange } : {}
    return apiClient.get('/farmer/dashboard', { params })
  },

  // Payout Settings & Escrow Settlements
  getPayoutSettings: () => apiClient.get('/farmer/payout-settings'),
  addPayoutMethod: (data: any) => apiClient.post('/farmer/payout-settings', data),
  setPrimaryPayoutMethod: (id: number) => apiClient.put(`/farmer/payout-settings/${id}/primary`),
  deletePayoutMethod: (id: number) => apiClient.delete(`/farmer/payout-settings/${id}`),
  disburseEscrowOrder: (orderId: number) => apiClient.post(`/farmer/orders/${orderId}/disburse-escrow`),

  // Forward Supply Contracts & Escrow Milestones
  getContracts: (params?: any) => apiClient.get('/farmer/contracts', { params }),
  createContract: (data: any) => apiClient.post('/farmer/contracts', data),
  getContract: (id: number) => apiClient.get(`/farmer/contracts/${id}`),
  acceptContract: (id: number, data?: { notes?: string }) => apiClient.post(`/farmer/contracts/${id}/accept`, data),
  rejectContract: (id: number, data?: { reason?: string }) => apiClient.post(`/farmer/contracts/${id}/reject`, data),
  completeMilestone: (contractId: number, milestoneId: number) => apiClient.post(`/farmer/contracts/${contractId}/milestones/${milestoneId}/complete`)
}
