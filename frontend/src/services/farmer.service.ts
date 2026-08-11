import apiClient from '@/api/config'

class FarmerService {
  // Farms
  getFarms() {
    return apiClient.get('/farmer/farms')
  }

  getFarm(id: number) {
    return apiClient.get(`/farmer/farms/${id}`)
  }

  createFarm(data: FormData) {
    return apiClient.post('/farmer/farms', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  updateFarm(id: number, data: FormData) {
    return apiClient.put(`/farmer/farms/${id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  deleteFarm(id: number) {
    return apiClient.delete(`/farmer/farms/${id}`)
  }

  // Crops
  getCrops() {
    return apiClient.get('/farmer/crops')
  }

  createCrop(data: any) {
    return apiClient.post('/farmer/crops', data)
  }

  updateCrop(id: number, data: any) {
    return apiClient.put(`/farmer/crops/${id}`, data)
  }

  deleteCrop(id: number) {
    return apiClient.delete(`/farmer/crops/${id}`)
  }

  // Products
  getProducts() {
    return apiClient.get('/farmer/products')
  }

  createProduct(data: FormData) {
    return apiClient.post('/farmer/products', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  updateProduct(id: number, data: FormData) {
    return apiClient.put(`/farmer/products/${id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  deleteProduct(id: number) {
    return apiClient.delete(`/farmer/products/${id}`)
  }

  // Orders
  getOrders() {
    return apiClient.get('/farmer/orders')
  }

  getOrder(id: number) {
    return apiClient.get(`/farmer/orders/${id}`)
  }

  acceptOrder(id: number) {
    return apiClient.post(`/farmer/orders/${id}/accept`)
  }

  rejectOrder(id: number) {
    return apiClient.post(`/farmer/orders/${id}/reject`)
  }

  // Dashboard
  getDashboard() {
    return apiClient.get('/farmer/dashboard')
  }

  // Consultations
  getConsultations() {
    return apiClient.get('/farmer/consultations')
  }

  requestConsultation(data: any) {
    return apiClient.post('/farmer/consultations', data)
  }

  // Loans
  getLoans() {
    return apiClient.get('/farmer/loans')
  }

  applyLoan(data: any) {
    return apiClient.post('/farmer/loans', data)
  }
}

export default new FarmerService()
