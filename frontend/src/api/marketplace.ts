import apiClient from './config'

export const marketplaceAPI = {
  // Public Products
  getPublicProducts: (page?: number, limit?: number, filters?: any) =>
    apiClient.get('/marketplace/products', { 
      params: { page, limit, ...filters } 
    }),
  
  getPublicProduct: (id: number) =>
    apiClient.get(`/marketplace/products/${id}`),
  
  searchProducts: (query: string, filters?: any) =>
    apiClient.get('/marketplace/products', {
      params: { q: query, ...filters }
    }),

  // Categories
  getCategories: () =>
    apiClient.get('/marketplace/categories'),

  // Market Prices
  getMarketPrices: (filters?: any) =>
    apiClient.get('/market-prices', { params: filters }),

  // Weather
  getWeather: (filters?: any) =>
    apiClient.get('/weather', { params: filters }),

  // Locations
  getLocations: (filters?: any) =>
    apiClient.get('/locations', { params: filters }),
  
  searchLocations: (query: string) =>
    apiClient.post('/locations/search', { query }),
}
