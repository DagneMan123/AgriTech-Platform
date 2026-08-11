import apiClient from '@/api/config'

class ExpertService {
  // Profile
  getProfile() {
    return apiClient.get('/expert/profile')
  }

  updateProfile(data: any) {
    return apiClient.post('/expert/profile', data)
  }

  // Consultations
  getConsultations() {
    return apiClient.get('/expert/consultations')
  }

  getConsultation(id: number) {
    return apiClient.get(`/expert/consultations/${id}`)
  }

  replyConsultation(id: number, data: any) {
    return apiClient.post(`/expert/consultations/${id}/reply`, data)
  }

  // Training Materials
  getTrainingMaterials() {
    return apiClient.get('/expert/training')
  }

  getTrainingMaterial(id: number) {
    return apiClient.get(`/expert/training/${id}`)
  }

  createTrainingMaterial(data: FormData) {
    return apiClient.post('/expert/training', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  updateTrainingMaterial(id: number, data: FormData) {
    return apiClient.put(`/expert/training/${id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  publishTrainingMaterial(id: number) {
    return apiClient.post(`/expert/training/${id}/publish`)
  }

  deleteTrainingMaterial(id: number) {
    return apiClient.delete(`/expert/training/${id}`)
  }

  // Articles
  getArticles() {
    return apiClient.get('/expert/articles')
  }

  getArticle(id: number) {
    return apiClient.get(`/expert/articles/${id}`)
  }

  createArticle(data: FormData) {
    return apiClient.post('/expert/articles', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  updateArticle(id: number, data: FormData) {
    return apiClient.put(`/expert/articles/${id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  publishArticle(id: number) {
    return apiClient.post(`/expert/articles/${id}/publish`)
  }

  deleteArticle(id: number) {
    return apiClient.delete(`/expert/articles/${id}`)
  }

  // Dashboard
  getDashboard() {
    return apiClient.get('/expert/dashboard')
  }
}

export default new ExpertService()
