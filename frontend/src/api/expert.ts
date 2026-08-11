import apiClient from './config'

export const expertAPI = {
  // Profile
  getProfile: () => apiClient.get('/expert/profile'),
  
  updateProfile: (data: any) => apiClient.post('/expert/profile', data),

  // Consultations
  getConsultations: () => apiClient.get('/expert/consultations'),
  
  getConsultation: (id: number) => apiClient.get(`/expert/consultations/${id}`),
  
  createConsultation: (data: any) => apiClient.post('/expert/consultations', data),
  
  replyConsultation: (id: number, data: any) => apiClient.post(`/expert/consultations/${id}/reply`, data),
  
  deleteConsultation: (id: number) => apiClient.delete(`/expert/consultations/${id}`),

  // Training Materials
  getTrainingMaterials: () => apiClient.get('/expert/training'),
  
  getTrainingMaterial: (id: number) => apiClient.get(`/expert/training/${id}`),
  
  createTrainingMaterial: (data: FormData) => apiClient.post('/expert/training', data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  updateTrainingMaterial: (id: number, data: FormData) => apiClient.put(`/expert/training/${id}`, data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  publishTrainingMaterial: (id: number) => apiClient.post(`/expert/training/${id}/publish`),
  
  deleteTrainingMaterial: (id: number) => apiClient.delete(`/expert/training/${id}`),

  // Articles
  getArticles: () => apiClient.get('/expert/articles'),
  
  getArticle: (id: number) => apiClient.get(`/expert/articles/${id}`),
  
  createArticle: (data: FormData) => apiClient.post('/expert/articles', data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  updateArticle: (id: number, data: FormData) => apiClient.put(`/expert/articles/${id}`, data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  }),
  
  publishArticle: (id: number) => apiClient.post(`/expert/articles/${id}/publish`),
  
  deleteArticle: (id: number) => apiClient.delete(`/expert/articles/${id}`),

  // Dashboard
  getDashboard: () => apiClient.get('/expert/dashboard'),
}
