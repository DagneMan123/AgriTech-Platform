import apiClient from './config'

export const supplierAPI = {
  // Products
  getProducts: () => apiClient.get('/supplier/products'),
  
  getProduct: (id: number) => apiClient.get(`/supplier/products/${id}`),
  
  createProduct: (data: FormData) => apiClient.post('/supplier/products', data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  updateProduct: (id: number, data: FormData) => apiClient.put(`/supplier/products/${id}`, data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  deleteProduct: (id: number) => apiClient.delete(`/supplier/products/${id}`),
  
  uploadProductImages: (id: number, formData: FormData) =>
    apiClient.post(`/supplier/products/${id}/upload-images`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),

  // Inventory
  getInventory: () => apiClient.get('/supplier/inventory'),
  
  getInventoryItem: (id: number) => apiClient.get(`/supplier/inventory/${id}`),
  
  createInventoryItem: (data: any) => apiClient.post('/supplier/inventory', data),
  
  updateInventoryItem: (id: number, data: any) => apiClient.put(`/supplier/inventory/${id}`, data),
  
  deleteInventoryItem: (id: number) => apiClient.delete(`/supplier/inventory/${id}`),

  // Warehouses
  getWarehouses: () => apiClient.get('/supplier/warehouses'),
  
  getWarehouse: (id: number) => apiClient.get(`/supplier/warehouses/${id}`),
  
  createWarehouse: (data: any) => apiClient.post('/supplier/warehouses', data),
  
  updateWarehouse: (id: number, data: any) => apiClient.put(`/supplier/warehouses/${id}`, data),
  
  deleteWarehouse: (id: number) => apiClient.delete(`/supplier/warehouses/${id}`),

  // License
  applyLicense: (data: FormData) => apiClient.post('/supplier/license/apply', data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  getLicense: () => apiClient.get('/supplier/license'),

  // Orders
  getOrders: () => apiClient.get('/supplier/orders'),
  
  getOrder: (id: number) => apiClient.get(`/supplier/orders/${id}`),
  
  processOrder: (id: number, data: any) => apiClient.post(`/supplier/orders/${id}/process`, data),

  // Dashboard
  getDashboard: () => apiClient.get('/supplier/dashboard'),
}
