import apiClient from '@/api/config'

class AdminService {
  // Users
  getUsers(filters?: any) {
    return apiClient.get('/admin/users', { params: filters })
  }

  getUser(id: number) {
    return apiClient.get(`/admin/users/${id}`)
  }

  createUser(data: any) {
    return apiClient.post('/admin/users', data)
  }

  updateUser(id: number, data: any) {
    return apiClient.put(`/admin/users/${id}`, data)
  }

  deleteUser(id: number) {
    return apiClient.delete(`/admin/users/${id}`)
  }

  // Create Financial Institution User (Admin Only)
  createFinancialUser(data: any) {
    return apiClient.post('/admin/users', {
      ...data,
      role: 'financial'
    })
  }

  // Roles
  getRoles() {
    return apiClient.get('/admin/roles')
  }

  getRole(id: number) {
    return apiClient.get(`/admin/roles/${id}`)
  }

  createRole(data: any) {
    return apiClient.post('/admin/roles', data)
  }

  updateRole(id: number, data: any) {
    return apiClient.put(`/admin/roles/${id}`, data)
  }

  deleteRole(id: number) {
    return apiClient.delete(`/admin/roles/${id}`)
  }

  // Permissions
  getPermissions() {
    return apiClient.get('/admin/permissions')
  }

  getPermission(id: number) {
    return apiClient.get(`/admin/permissions/${id}`)
  }

  createPermission(data: any) {
    return apiClient.post('/admin/permissions', data)
  }

  updatePermission(id: number, data: any) {
    return apiClient.put(`/admin/permissions/${id}`, data)
  }

  deletePermission(id: number) {
    return apiClient.delete(`/admin/permissions/${id}`)
  }

  // Activity Logs
  getActivityLogs(filters?: any) {
    return apiClient.get('/admin/activity-logs', { params: filters })
  }

  // Settings
  getSettings() {
    return apiClient.get('/admin/settings')
  }

  updateSettings(data: any) {
    return apiClient.post('/admin/settings', data)
  }

  // Reports
  getReports(filters?: any) {
    return apiClient.get('/admin/reports', { params: filters })
  }

  // Dashboard
  getDashboard() {
    return apiClient.get('/admin/dashboard')
  }
}

export default new AdminService()
