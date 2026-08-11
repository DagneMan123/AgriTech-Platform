import apiClient from './config'

export const adminAPI = {
  // Users
  getUsers: (filters?: any) => apiClient.get('/admin/users', { params: filters }),
  
  getUser: (id: number) => apiClient.get(`/admin/users/${id}`),
  
  createUser: (data: any) => apiClient.post('/admin/users', data),
  
  updateUser: (id: number, data: any) => apiClient.put(`/admin/users/${id}`, data),
  
  deleteUser: (id: number) => apiClient.delete(`/admin/users/${id}`),

  // Roles
  getRoles: () => apiClient.get('/admin/roles'),
  
  getRole: (id: number) => apiClient.get(`/admin/roles/${id}`),
  
  createRole: (data: any) => apiClient.post('/admin/roles', data),
  
  updateRole: (id: number, data: any) => apiClient.put(`/admin/roles/${id}`, data),
  
  deleteRole: (id: number) => apiClient.delete(`/admin/roles/${id}`),

  // Permissions
  getPermissions: () => apiClient.get('/admin/permissions'),
  
  getPermission: (id: number) => apiClient.get(`/admin/permissions/${id}`),
  
  createPermission: (data: any) => apiClient.post('/admin/permissions', data),
  
  updatePermission: (id: number, data: any) => apiClient.put(`/admin/permissions/${id}`, data),
  
  deletePermission: (id: number) => apiClient.delete(`/admin/permissions/${id}`),

  // Activity Logs
  getActivityLogs: (filters?: any) => apiClient.get('/admin/activity-logs', { params: filters }),

  // Settings
  getSettings: () => apiClient.get('/admin/settings'),
  
  updateSettings: (data: any) => apiClient.post('/admin/settings', data),

  // Reports
  getReports: (filters?: any) => apiClient.get('/admin/reports', { params: filters }),

  // Dashboard
  getDashboard: () => apiClient.get('/admin/dashboard'),
}
