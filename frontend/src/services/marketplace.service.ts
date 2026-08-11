import apiClient from '@/api/config'

class MarketplaceService {
  // Public Products
  getPublicProducts(page?: number, limit?: number, filters?: any) {
    return apiClient.get('/marketplace/products', {
      params: { page, limit, ...filters }
    })
  }

  getPublicProduct(id: number) {
    return apiClient.get(`/marketplace/products/${id}`)
  }

  searchProducts(query: string, filters?: any) {
    return apiClient.get('/marketplace/products', {
      params: { q: query, ...filters }
    })
  }

  // Categories
  getCategories() {
    return apiClient.get('/marketplace/categories')
  }

  // Market Prices
  getMarketPrices(filters?: any) {
    return apiClient.get('/market-prices', { params: filters })
  }

  // Weather
  getWeather(filters?: any) {
    return apiClient.get('/weather', { params: filters })
  }

  // Locations
  getLocations(filters?: any) {
    return apiClient.get('/locations', { params: filters })
  }

  searchLocations(query: string) {
    return apiClient.post('/locations/search', { query })
  }
}

export default new MarketplaceService()
