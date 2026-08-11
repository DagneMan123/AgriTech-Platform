import apiClient from './config'

export const financialAPI = {
  // Loans
  getLoans: () => apiClient.get('/financial/loans'),
  
  getLoan: (id: number) => apiClient.get(`/financial/loans/${id}`),
  
  createLoan: (data: any) => apiClient.post('/financial/loans', data),
  
  approveLoan: (id: number, data: any) => apiClient.post(`/financial/loans/${id}/approve`, data),
  
  rejectLoan: (id: number, data: any) => apiClient.post(`/financial/loans/${id}/reject`, data),

  // Insurance
  getInsurancePolicies: () => apiClient.get('/financial/insurance'),
  
  getInsurancePolicy: (id: number) => apiClient.get(`/financial/insurance/${id}`),
  
  createInsurancePolicy: (data: any) => apiClient.post('/financial/insurance', data),
  
  approveInsurance: (id: number, data: any) => apiClient.post(`/financial/insurance/${id}/approve`, data),

  // Payments
  getPayments: () => apiClient.get('/financial/payments'),
  
  getPayment: (id: number) => apiClient.get(`/financial/payments/${id}`),
  
  createPayment: (data: any) => apiClient.post('/financial/payments', data),
  
  verifyPayment: (id: number) => apiClient.post(`/financial/payments/${id}/verify`),

  // Transactions
  getTransactions: (filters?: any) => apiClient.get('/financial/transactions', { params: filters }),
  
  getTransaction: (id: number) => apiClient.get(`/financial/transactions/${id}`),

  // Dashboard
  getDashboard: () => apiClient.get('/financial/dashboard'),
}
