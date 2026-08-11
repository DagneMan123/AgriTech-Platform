import apiClient from './config'

export const transportAPI = {
  // Vehicles
  getVehicles: () => apiClient.get('/transport/vehicles'),
  
  getVehicle: (id: number) => apiClient.get(`/transport/vehicles/${id}`),
  
  createVehicle: (data: any) => apiClient.post('/transport/vehicles', data),
  
  updateVehicle: (id: number, data: any) => apiClient.put(`/transport/vehicles/${id}`, data),
  
  deleteVehicle: (id: number) => apiClient.delete(`/transport/vehicles/${id}`),

  // Deliveries
  getDeliveries: () => apiClient.get('/transport/deliveries'),
  
  getDelivery: (id: number) => apiClient.get(`/transport/deliveries/${id}`),
  
  createDelivery: (data: any) => apiClient.post('/transport/deliveries', data),
  
  acceptDelivery: (id: number) => apiClient.post(`/transport/deliveries/${id}/accept`),
  
  completeDelivery: (id: number, data: any) => apiClient.post(`/transport/deliveries/${id}/complete`, data),

  // Tracking
  createTracking: (data: any) => apiClient.post('/transport/tracking', data),
  
  getTracking: (deliveryId: number) => apiClient.get(`/transport/tracking/${deliveryId}`),

  // Dashboard
  getDashboard: () => apiClient.get('/transport/dashboard'),
}
