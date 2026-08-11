# Dashboard Quick Start Guide

## 🚀 Quick Access

### Test Each Dashboard

Use these curl commands or Postman to test:

```bash
# Get your token first
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "farmer@example.com",
    "password": "password"
  }'

# Store token in variable
TOKEN="your_token_from_login"
```

### Farmer Dashboard
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/farmer/dashboard
```

### Buyer Dashboard
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/buyer/dashboard
```

### Supplier Dashboard
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/supplier/dashboard
```

### Transport Dashboard
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/transport/dashboard
```

### Cooperative Dashboard
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/cooperative/dashboard
```

### Expert Dashboard
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/expert/dashboard
```

### Financial Dashboard
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/financial/dashboard
```

### Admin Dashboard
```bash
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/admin/dashboard
```

---

## 📊 Endpoint Structure

Each dashboard follows this pattern:

### Main Dashboard (Overview)
```
GET /{role}/dashboard
```
Returns: Summary stats, recent activity, trends

### Specialized Endpoints
```
GET /{role}/dashboard/{feature}
```

Examples:
- `/farmer/dashboard/sales-analytics`
- `/buyer/dashboard/order-history`
- `/supplier/dashboard/inventory`
- `/transport/dashboard/active-deliveries`
- `/cooperative/dashboard/financial-reports`
- `/expert/dashboard/engagement-analytics`
- `/financial/dashboard/risk-assessment`

---

## 🎯 Common Patterns

### Pagination
```
GET /api/buyer/dashboard/order-history?page=1
```
Default: 20 items per page

### Filtering by Status
```
GET /api/financial/dashboard/loans?status=pending
GET /api/transport/dashboard/deliveries?status=in_transit
```

### Date Range
```
GET /api/farmer/dashboard/sales-analytics?period=30
GET /api/financial/dashboard/portfolio-analytics?period=90
```

### Combined
```
GET /api/expert/dashboard/consultations?status=completed&page=2
```

---

## 📈 Response Examples

### Summary Response
```json
{
  "summary": {
    "total_orders": 42,
    "pending_orders": 5,
    "completed_orders": 35,
    "total_revenue": 15000
  },
  "recent_orders": [...],
  "trends": [...]
}
```

### List Response (Paginated)
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "next": "...",
    "prev": "..."
  },
  "meta": {
    "current_page": 1,
    "total": 100,
    "per_page": 20
  }
}
```

---

## 🔑 Key Features by Role

### 👨‍🌾 Farmer
- Farm and crop management
- Sales tracking
- Weather monitoring
- Market prices
- Loan applications
- Consultation booking

### 👤 Buyer
- Browse products
- Order history
- Real-time delivery tracking
- Payment methods
- Product reviews
- Wishlist management

### 📦 Supplier
- Inventory management
- Warehouse tracking
- Sales by customer
- License management
- Order processing
- Delivery coordination

### 🚚 Transport Provider
- Active deliveries
- Vehicle tracking
- Route optimization
- Revenue analytics
- Driver management
- Delivery history

### 🤝 Cooperative
- Member management
- Bulk purchasing
- Bulk sales
- Collection centers
- Financial reports
- Member contributions

### 🎓 Expert
- Consultation requests
- Training materials
- Articles/Publications
- Farmer engagement
- Performance metrics
- Geographic reach

### 💰 Financial Institution
- Loan applications
- Portfolio management
- Risk assessment
- Repayment tracking
- Insurance policies
- Interest revenue

### 👨‍💼 Admin
- System monitoring
- User management
- Platform statistics
- Order oversight
- Payment tracking
- Activity logs

---

## 🔐 Authentication

### Get Token
```bash
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}
```

### Use Token
```bash
Authorization: Bearer {token}
```

### Token Expiration
- Tokens are valid for the current session
- Logout to invalidate: `POST /api/auth/logout`

---

## ⚠️ Common Errors

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```
**Solution**: Include valid Bearer token

### 403 Forbidden
```json
{
  "message": "This action is unauthorized."
}
```
**Solution**: Your role doesn't have access to this endpoint

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```
**Solution**: Check endpoint path and parameters

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Error message"]
  }
}
```
**Solution**: Check required parameters and their formats

---

## 🎨 Frontend Integration

### Vue.js Example
```javascript
// Fetch farmer dashboard
async function getFarmerDashboard() {
  const response = await fetch('/api/farmer/dashboard', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return response.json();
}
```

### React Example
```javascript
// Fetch farmer dashboard
const fetchFarmerDashboard = async () => {
  try {
    const response = await fetch('/api/farmer/dashboard', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });
    const data = await response.json();
    setDashboard(data);
  } catch (error) {
    console.error('Error:', error);
  }
};
```

---

## 📊 Data Visualization Tips

### Charts to Display
- **Line Charts**: Revenue/spending trends
- **Bar Charts**: Orders/deliveries by status
- **Pie Charts**: Distribution (by role, product, crop type)
- **Gauge Charts**: Inventory utilization, portfolio health
- **Map**: Geographic reach, delivery routes

### Popular Libraries
- Chart.js
- Recharts (React)
- Vue-chartjs (Vue)
- D3.js (Advanced)

---

## 🚀 Performance Tips

1. **Pagination**: Always paginate large lists
2. **Caching**: Cache dashboard data for 5 minutes
3. **Lazy Loading**: Load charts on demand
4. **Filtering**: Pre-filter data on backend
5. **Debouncing**: Debounce search/filter inputs

---

## 📱 Mobile Considerations

- Use responsive grid layouts
- Stack charts vertically on mobile
- Reduce data points on mobile
- Use touch-friendly buttons
- Optimize image sizes

---

## 🔗 Related Documentation

- **API Reference**: `DASHBOARD_ENDPOINTS.md`
- **Implementation Details**: `DASHBOARDS_IMPLEMENTATION_SUMMARY.md`
- **Auth Guide**: See auth controller
- **Database Schema**: See migrations

---

## ✅ Checklist

- [ ] All dashboards created
- [ ] Routes properly configured
- [ ] Role-based access working
- [ ] Data aggregations correct
- [ ] Pagination implemented
- [ ] Error handling in place
- [ ] Documentation complete
- [ ] Tested with sample data

---

## 📞 Support

For issues:
1. Check the error message
2. Verify authentication token
3. Confirm user role
4. Check request parameters
5. Review error logs: `storage/logs/laravel.log`
