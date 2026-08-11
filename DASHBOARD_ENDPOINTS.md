# Dashboard Endpoints - Complete API Reference

All dashboard endpoints require authentication (`Authorization: Bearer {token}`) and role-based access control.

## 1. Administrator Dashboard

**Overview**
```
GET /api/admin/dashboard
```
Returns:
- Total users count
- Total orders count
- Total revenue
- Active deliveries
- Users by role (breakdown)
- Orders by status (breakdown)
- Recent orders (last 10)

**User Management**
```
GET /api/admin/users
GET /api/admin/users?search={term}&role={role}&page={page}
```

**System Statistics**
```
GET /api/admin/stats
```
Returns:
- Total farms
- Total products
- Total consultations
- Total loans
- Pending loans
- Active cooperatives

---

## 2. Farmer Dashboard

**Main Dashboard**
```
GET /api/farmer/dashboard
```
Returns:
- Summary statistics (farms, products, crops, orders, revenue)
- Recent orders
- Recent products
- Recent harvests
- Orders by status
- Revenue by month (6 months trend)
- Pending consultations and loans count

**Farm Overview**
```
GET /api/farmer/dashboard/farm-overview
```
Returns list of farms with:
- Farm details
- Crops count
- Workers count
- Total area

**Sales Analytics**
```
GET /api/farmer/dashboard/sales-analytics?period={days}
```
Returns:
- Total sales in period
- Sales count
- Average order value
- Top products
- Top buyers
- Recent sales

**Weather & Market Data**
```
GET /api/farmer/dashboard/weather-market
```
Returns:
- Current weather data
- Market prices for farmer's location

---

## 3. Buyer Dashboard

**Main Dashboard**
```
GET /api/buyer/dashboard
```
Returns:
- Order statistics (total, pending, completed, cancelled)
- Total spent and average order value
- Cart and wishlist items count
- Active deliveries
- Recent orders
- Recent reviews
- Active delivery details
- Orders by status
- Spending by month

**Order History**
```
GET /api/buyer/dashboard/order-history?status={status}&period={days}&page={page}
```
Filterable by status and date range

**Cart Summary**
```
GET /api/buyer/dashboard/cart-summary
```
Returns:
- Cart items with product details
- Subtotal
- Estimated tax (15%)
- Estimated total

**Delivery Tracking**
```
GET /api/buyer/dashboard/delivery-tracking?page={page}
```
Returns paginated deliveries with real-time tracking

**Payment History**
```
GET /api/buyer/dashboard/payment-history?page={page}
```
Returns all payments with order details

**Wishlist**
```
GET /api/buyer/dashboard/wishlist?page={page}
```
Returns paginated wishlist items with product details

**Purchase Analytics**
```
GET /api/buyer/dashboard/purchase-analytics
```
Returns:
- Top products purchased
- Favorite suppliers (most purchases)

---

## 4. Supplier Dashboard

**Main Dashboard**
```
GET /api/supplier/dashboard
```
Returns:
- Product statistics
- Inventory value and low stock items
- Orders statistics
- Revenue and average order value
- Warehouse capacity and utilization
- License status
- Recent orders and products
- Orders by status
- Monthly revenue trend
- Low stock items

**Inventory Status**
```
GET /api/supplier/dashboard/inventory?threshold={quantity}
```
Returns:
- Inventory items with status
- Low stock and adequate stock breakdown
- Total inventory value

**Warehouses**
```
GET /api/supplier/dashboard/warehouses
```
Returns:
- List of all warehouses
- Capacity and utilization percentage

**Sales Analytics**
```
GET /api/supplier/dashboard/sales-analytics?period={days}
```
Returns:
- Total sales and count
- Average order value
- Top products
- Top buyers

**License Status**
```
GET /api/supplier/dashboard/license-status
```
Returns:
- License existence flag
- License details and status
- Expiration date

**Deliveries**
```
GET /api/supplier/dashboard/deliveries?page={page}
```
Returns all deliveries with tracking info

---

## 5. Transport Provider Dashboard

**Main Dashboard**
```
GET /api/transport/dashboard
```
Returns:
- Delivery statistics (total, pending, active, completed)
- Vehicle statistics (total, active)
- Total revenue from deliveries
- Average delivery time
- Recent deliveries
- Active deliveries with tracking
- Deliveries by status
- Monthly revenue trend

**Delivery Requests**
```
GET /api/transport/dashboard/delivery-requests?status={status}&page={page}
```
Returns paginated delivery requests

**Vehicle Fleet**
```
GET /api/transport/dashboard/vehicles
```
Returns:
- All vehicles with details
- Active deliveries per vehicle
- Vehicle status and capacity

**Active Deliveries**
```
GET /api/transport/dashboard/active-deliveries
```
Returns real-time tracking for active deliveries

**Analytics**
```
GET /api/transport/dashboard/analytics?period={days}
```
Returns:
- Delivery count and revenue
- Average delivery fee
- Top routes
- Vehicle performance

**Delivery History**
```
GET /api/transport/dashboard/history?page={page}
```
Returns paginated completed deliveries

---

## 6. Cooperative Dashboard

**Main Dashboard**
```
GET /api/cooperative/dashboard
```
Returns:
- Member statistics
- Farm statistics and total area
- Bulk sales and revenue
- Bulk orders and expenses
- Recent members and sales
- Sales by month
- Average farm size

**Member Management**
```
GET /api/cooperative/dashboard/members?status={status}&page={page}
```
Returns paginated member list with farm details

**Bulk Purchasing**
```
GET /api/cooperative/dashboard/bulk-purchasing?page={page}
```
Returns bulk purchase orders

**Bulk Sales**
```
GET /api/cooperative/dashboard/bulk-sales?status={status}&page={page}
```
Returns bulk sales transactions

**Collection Centers**
```
GET /api/cooperative/dashboard/collection-centers
```
Returns:
- List of collection centers
- Capacity and utilization per center
- Current stock levels

**Financial Reports**
```
GET /api/cooperative/dashboard/financial-reports?period={days}
```
Returns:
- Sales revenue
- Purchase expenses
- Net profit and margin
- Top performing members

**Member Statistics**
```
GET /api/cooperative/dashboard/member-statistics
```
Returns:
- Members by status
- Members joined by month

---

## 7. Agricultural Expert Dashboard

**Main Dashboard**
```
GET /api/expert/dashboard
```
Returns:
- Consultation statistics (total, pending, completed)
- Training and article statistics
- Engagement metrics (views, interactions)
- Average expert rating
- Recent consultations
- Recent training and articles
- Consultations by status
- Monthly consultation trend

**Consultations**
```
GET /api/expert/dashboard/consultations?status={status}&page={page}
```
Returns paginated consultations

**Training Materials**
```
GET /api/expert/dashboard/training-materials?status={all|published|draft}&page={page}
```
Returns paginated training materials

**Articles**
```
GET /api/expert/dashboard/articles?status={all|published|draft}&page={page}
```
Returns paginated articles

**Engagement Analytics**
```
GET /api/expert/dashboard/engagement-analytics?period={days}
```
Returns:
- Top training materials and articles
- Consultations by crop type
- Average consultation duration

**Farmer Reach**
```
GET /api/expert/dashboard/farmer-reach
```
Returns:
- Unique farmers reached
- Unique farms reached
- Satisfaction rating
- Geographic reach breakdown

**Consultation Details**
```
GET /api/expert/dashboard/consultation/{id}
```
Returns complete consultation details

---

## 8. Financial Institution Dashboard

**Main Dashboard**
```
GET /api/financial/dashboard
```
Returns:
- Loan statistics (total, pending, approved, disbursed, defaulted)
- Loan amounts (total, approved, disbursed)
- Outstanding amount and repayment rate
- Insurance statistics and coverage amount
- Repayment statistics
- Recent loan applications
- Loans by status
- Disbursement trend
- Portfolio risk assessment

**Pending Loan Applications**
```
GET /api/financial/dashboard/pending-applications?page={page}
```
Returns paginated pending loan applications

**Loan Management**
```
GET /api/financial/dashboard/loans?status={status}&page={page}
```
Returns loans by status with repayment history

**Insurance Management**
```
GET /api/financial/dashboard/insurances?status={status}&page={page}
```
Returns insurance policies

**Repayments Tracking**
```
GET /api/financial/dashboard/repayments?page={page}
```
Returns paginated loan repayments

**Transactions**
```
GET /api/financial/dashboard/transactions?page={page}
```
Returns financial transactions

**Portfolio Analytics**
```
GET /api/financial/dashboard/portfolio-analytics?period={days}
```
Returns:
- Portfolio composition by purpose and term
- Default rate
- Average loan size
- Interest revenue

**Loan Details**
```
GET /api/financial/dashboard/loan/{id}
```
Returns:
- Loan details
- Repayment history
- Outstanding balance
- Total repaid

**Risk Assessment**
```
GET /api/financial/dashboard/risk-assessment
```
Returns:
- Risk breakdown (low, medium, high)
- High-risk loans count
- Portfolio health score

---

## Common Query Parameters

- `page`: Pagination page number (default: 1)
- `period`: Date range in days (default: 30)
- `status`: Filter by status
- `search`: Search query
- `threshold`: Threshold value for comparisons

## Response Format

All successful responses return:
```json
{
  "data": {...},
  "message": "Success message",
  "status": 200
}
```

For paginated responses:
```json
{
  "data": [...],
  "links": {...},
  "meta": {
    "current_page": 1,
    "total": 100,
    "per_page": 20
  }
}
```

## Error Responses

- `401 Unauthorized`: Missing or invalid token
- `403 Forbidden`: Insufficient role/permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server error

---

## Authentication

All endpoints require a valid Bearer token:
```
Authorization: Bearer {your_api_token}
```

Tokens are obtained during login:
```
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}
```

Response includes:
```json
{
  "message": "Login successful",
  "user": {...},
  "token": "your_api_token_here"
}
```

---

## Rate Limiting

- Admin endpoints: 100 requests/hour
- Other endpoints: 60 requests/hour
- Burst limit: 10 requests/minute

---

## Status Codes

- `200 OK`: Successful GET request
- `201 Created`: Successful POST request
- `204 No Content`: Successful DELETE request
- `400 Bad Request`: Invalid parameters
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Permission denied
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation failed
- `500 Internal Server Error`: Server error
