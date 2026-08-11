import apiClient from '@/api/config'

class FinancialService {
  // NOTE: Financial Institution users are created by Admin only
  // This service is for Financial Institution staff to manage loans and insurance

  // Loans
  getLoans() {
    return apiClient.get('/financial/loans')
  }

  getLoan(id: number) {
    return apiClient.get(`/financial/loans/${id}`)
  }

  approveLoan(id: number, data: any) {
    return apiClient.post(`/financial/loans/${id}/approve`, data)
  }

  rejectLoan(id: number, data: any) {
    return apiClient.post(`/financial/loans/${id}/reject`, data)
  }

  // Insurance
  getInsurancePolicies() {
    return apiClient.get('/financial/insurance')
  }

  getInsurancePolicy(id: number) {
    return apiClient.get(`/financial/insurance/${id}`)
  }

  createInsurancePolicy(data: any) {
    return apiClient.post('/financial/insurance', data)
  }

  approveInsurance(id: number, data: any) {
    return apiClient.post(`/financial/insurance/${id}/approve`, data)
  }

  // Payments
  getPayments() {
    return apiClient.get('/financial/payments')
  }

  getPayment(id: number) {
    return apiClient.get(`/financial/payments/${id}`)
  }

  createPayment(data: any) {
    return apiClient.post('/financial/payments', data)
  }

  verifyPayment(id: number) {
    return apiClient.post(`/financial/payments/${id}/verify`)
  }

  // Transactions
  getTransactions(filters?: any) {
    return apiClient.get('/financial/transactions', { params: filters })
  }

  getTransaction(id: number) {
    return apiClient.get(`/financial/transactions/${id}`)
  }

  // Dashboard
  getDashboard() {
    return apiClient.get('/financial/dashboard')
  }
}

export default new FinancialService()
