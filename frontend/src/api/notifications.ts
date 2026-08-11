import apiClient from './config'

export const notificationAPI = {
  getNotifications: (page?: number, limit?: number) =>
    apiClient.get('/notifications', { params: { page, limit } }),
  
  getNotification: (id: number) =>
    apiClient.get(`/notifications/${id}`),
  
  markAsRead: (id: number) =>
    apiClient.post(`/notifications/${id}/read`),
  
  markAllAsRead: () =>
    apiClient.post('/notifications/read-all'),
}
