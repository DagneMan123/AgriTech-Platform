import apiClient from '@/api/config'

class TransportService {
  // Vehicles
  getVehicles() {
    return apiClient.get('/transport/vehicles')
  }

  getVehicle(id: number) {
    return apiClient.get(`/transport/vehicles/${id}`)
  }

  createVehicle(data: any) {
    return apiClient.post('/transport/vehicles', data)
  }

  updateVehicle(id: number, data: any) {
    return apiClient.put(`/transport/vehicles/${id}`, data)
  }

  deleteVehicle(id: number) {
    return apiClient.delete(`/transport/vehicles/${id}`)
  }

  // Deliveries
  getDeliveries() {
    return apiClient.get('/transport/deliveries')
  }

  getDelivery(id: number) {
    return apiClient.get(`/transport/deliveries/${id}`)
  }

  createDelivery(data: any) {
    return apiClient.post('/transport/deliveries', data)
  }

  acceptDelivery(id: number) {
    return apiClient.post(`/transport/deliveries/${id}/accept`)
  }

  completeDelivery(id: number, data: any) {
    return apiClient.post(`/transport/deliveries/${id}/complete`, data)
  }

  // Tracking
  createTracking(data: any) {
    return apiClient.post('/transport/tracking', data)
  }

  getTracking(deliveryId: number) {
    return apiClient.get(`/transport/tracking/${deliveryId}`)
  }

  // Dashboard
  getDashboard() {
    return apiClient.get('/transport/dashboard')
  }
}

export default new TransportService()
