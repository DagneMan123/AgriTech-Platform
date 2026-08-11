import apiClient from '@/api/config'

class SupplierService {
  // Products
  getProducts() {
    return apiClient.get('/supplier/products')
  }

  getProduct(id: number) {
    return apiClient.get(`/supplier/products/${id}`)
  }

  createProduct(data: FormData) {
    return apiClient.post('/supplier/products', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  updateProduct(id: number, data: FormData) {
    return apiClient.put(`/supplier/products/${id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  deleteProduct(id: number) {
    return apiClient.delete(`/supplier/products/${id}`)
  }

  // Inventory
  getInventory() {
    return apiClient.get('/supplier/inventory')
  }

  createInventoryItem(data: any) {
    return apiClient.post('/supplier/inventory', data)
  }

  updateInventoryItem(id: number, data: any) {
    return apiClient.put(`/supplier/inventory/${id}`, data)
  }

  deleteInventoryItem(id: number) {
    return apiClient.delete(`/supplier/inventory/${id}`)
  }

  // Warehouses
  getWarehouses() {
    return apiClient.get('/supplier/warehouses')
  }

  createWarehouse(data: any) {
    return apiClient.post('/supplier/warehouses', data)
  }

  updateWarehouse(id: number, data: any) {
    return apiClient.put(`/supplier/warehouses/${id}`, data)
  }

  deleteWarehouse(id: number) {
    return apiClient.delete(`/supplier/warehouses/${id}`)
  }

  // License
  applyLicense(data: FormData) {
    return apiClient.post('/supplier/license/apply', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  getLicense() {
    return apiClient.get('/supplier/license')
  }

  // Orders
  getOrders() {
    return apiClient.get('/supplier/orders')
  }

  getOrder(id: number) {
    return apiClient.get(`/supplier/orders/${id}`)
  }

  processOrder(id: number, data: any) {
    return apiClient.post(`/supplier/orders/${id}/process`, data)
  }

  // Dashboard
  getDashboard() {
    return apiClient.get('/supplier/dashboard')
  }
}

export default new SupplierService()
