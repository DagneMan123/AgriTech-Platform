# Dashboards & Sidebars Quick Reference

## 🚀 Quick Start

### View a Dashboard
Navigate to any role's dashboard:
- Admin: `http://localhost:5173/admin/dashboard`
- Farmer: `http://localhost:5173/farmer/dashboard`
- Buyer: `http://localhost:5173/buyer/dashboard`
- Supplier: `http://localhost:5173/supplier/dashboard`
- Transport: `http://localhost:5173/transport/dashboard`
- Cooperative: `http://localhost:5173/cooperative/dashboard`
- Expert: `http://localhost:5173/expert/dashboard`
- Financial: `http://localhost:5173/financial/dashboard`

---

## 📋 Dashboard Overview

| Role | Sidebar Color | Summary Cards | Key Features |
|------|--------------|---------------|--------------|
| Admin | Blue | 6 | User mgmt, Orders, Payments, Analytics |
| Farmer | Green | 7 | Farms, Crops, Products, Orders, Weather |
| Buyer | Purple | 4 | Orders, Spending, Deliveries, Wishlist |
| Supplier | Orange | 4 | Products, Orders, Inventory, Revenue |
| Transport | Red | 4 | Deliveries, Earnings, Vehicles, Performance |
| Cooperative | Teal | 4 | Members, Centers, Revenue, Farms |
| Expert | Indigo | 4 | Consultations, Articles, Materials, Rating |
| Financial | Yellow | 4 | Portfolio, Loans, Applications, Risk |

---

## 📁 File Locations

### Sidebars
```
frontend/src/components/Sidebar/
├── BaseSidebar.vue ...................... Base component
├── AdminSidebar.vue ..................... Admin navigation
├── FarmerSidebar.vue .................... Farmer navigation
├── BuyerSidebar.vue ..................... Buyer navigation
├── SupplierSidebar.vue .................. Supplier navigation
├── TransportSidebar.vue ................. Transport navigation
├── CooperativeSidebar.vue ............... Cooperative navigation
├── ExpertSidebar.vue .................... Expert navigation
└── FinancialSidebar.vue ................. Financial navigation
```

### Dashboards
```
frontend/src/views/
├── Admin/AdminDashboard.vue ............. Admin dashboard
├── Farmer/FarmerDashboard.vue ........... Farmer dashboard
├── Buyer/BuyerDashboard.vue ............. Buyer dashboard
├── Supplier/SupplierDashboard.vue ....... Supplier dashboard
├── Transport/TransportDashboard.vue ..... Transport dashboard
├── Cooperative/CooperativeDashboard.vue  Cooperative dashboard
├── Expert/ExpertDashboard.vue ........... Expert dashboard
└── Financial/FinancialDashboard.vue ..... Financial dashboard
```

---

## 🎨 Styling Guide

### Summary Cards Structure
```vue
<div class="summary-card">
  <div class="card-icon">
    <i class="fas fa-icon"></i>
  </div>
  <div class="card-content">
    <h3>Label</h3>
    <p class="card-value">{{ value }}</p>
  </div>
</div>
```

### Status Badges
```vue
<span class="status-badge" :class="`status-${status}`">
  {{ status }}
</span>
```

Available classes:
- `status-pending` - Yellow
- `status-completed` - Green
- `status-cancelled` - Red
- `status-active` - Blue

---

## 🔌 API Integration

### Dashboard Endpoints
```javascript
// Get dashboard data
GET /api/{role}/dashboard
Headers: { Authorization: 'Bearer token' }

Response:
{
  summary: { ... },
  users: [ ... ],
  orders: [ ... ],
  // role-specific data
}
```

### Sidebar Badge Endpoints
```javascript
// Get unread notification count
GET /api/{role}/notifications/unread-count
Headers: { Authorization: 'Bearer token' }

// Get unread message count
GET /api/{role}/messages/unread-count
Headers: { Authorization: 'Bearer token' }
```

---

## 🔄 Data Fetching Pattern

### Basic Setup
```vue
<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '@/stores/auth'

const auth = useAuth()
const dashboard = ref(null)

onMounted(async () => {
  await fetchDashboardData()
})

const fetchDashboardData = async () => {
  try {
    const res = await fetch(`/api/${auth.user.role}/dashboard`, {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    if (res.ok) {
      dashboard.value = await res.json()
    }
  } catch (error) {
    console.error('Error fetching dashboard:', error)
  }
}
</script>
```

---

## 🎯 Sidebar Menu Structure

### Creating a Menu Section
```vue
const menuSections = [
  {
    section: 'Dashboard',           // Section title
    collapsible: false,             // Can collapse?
    items: [
      {
        label: 'Overview',          // Item label
        icon: 'fas fa-chart-line',  // Font Awesome icon
        route: '/admin/dashboard',  // Router path
        badge: null                 // Badge count (optional)
      }
    ]
  },
  // More sections...
]
```

### Collapsible Sections
```vue
{
  section: 'User Management',
  collapsible: true,              // Enables collapse/expand
  items: [
    { label: 'All Users', icon: 'fas fa-users', route: '...' },
    { label: 'Add User', icon: 'fas fa-plus', route: '...' }
  ]
}
```

---

## 🎭 Color Coding

### CSS Variables by Role
```css
/* Admin - Blue */
.admin-dashboard .card-icon { background: #3b82f6; }

/* Farmer - Green */
.farmer-dashboard .card-icon { background: #10b981; }

/* Buyer - Purple */
.buyer-dashboard .card-icon { background: #8b5cf6; }

/* Supplier - Orange */
.supplier-dashboard .card-icon { background: #f59e0b; }

/* Transport - Red */
.transport-dashboard .card-icon { background: #ef4444; }

/* Cooperative - Teal */
.cooperative-dashboard .card-icon { background: #14b8a6; }

/* Expert - Indigo */
.expert-dashboard .card-icon { background: #6366f1; }

/* Financial - Yellow */
.financial-dashboard .card-icon { background: #eab308; }
```

---

## 📱 Responsive Design

### Breakpoints
```css
/* Desktop */
.sidebar { width: 260px; }
.dashboard { margin-left: 260px; }

/* Tablet (768px and below) */
@media (max-width: 768px) {
  .summary-grid { grid-template-columns: repeat(2, 1fr); }
}

/* Mobile (480px and below) */
@media (max-width: 480px) {
  .sidebar { max-width: 250px; }
  .summary-grid { grid-template-columns: 1fr; }
}
```

---

## 🔐 Authentication

### Protected Routes
All dashboard routes require authentication and role-based access:

```typescript
// In router/index.ts
{
  path: '/admin/dashboard',
  meta: { 
    requiresAuth: true, 
    requiredRole: 'admin' 
  }
}
```

### Auth Headers
Always include Bearer token:
```javascript
{
  headers: { 
    'Authorization': `Bearer ${auth.token}`,
    'Content-Type': 'application/json'
  }
}
```

---

## 🚨 Error Handling

### Dashboard Errors
```vue
const fetchDashboardData = async () => {
  try {
    const res = await fetch('/api/admin/dashboard', {
      headers: { 'Authorization': `Bearer ${auth.token}` }
    })
    
    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`)
    }
    
    dashboard.value = await res.json()
  } catch (error) {
    console.error('Dashboard fetch failed:', error)
    // Show error toast/notification
  }
}
```

---

## 📊 Data Formatting

### Number Formatting
```javascript
const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0)
}
// Result: 1234567 → "1,234,567"
```

### Currency Formatting
```javascript
const formatCurrency = (amount, currency = 'USD') => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency
  }).format(amount || 0)
}
// Result: 1234.56 → "$1,234.56"
```

### Date Formatting
```javascript
const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}
// Result: "2026-08-07"
```

---

## 🔄 Sidebar Interaction

### Toggle Sidebar
```javascript
const isOpen = ref(true)

const toggleSidebar = () => {
  isOpen.value = !isOpen.value
}
```

### Active Route Highlighting
```javascript
const isActive = (route) => {
  return router.currentRoute.value.path === route
}
```

### Toggle Section
```javascript
const expandedSections = ref({})

const toggleSection = (section) => {
  expandedSections.value[section] = 
    !expandedSections.value[section]
}
```

---

## 🧪 Testing Common Issues

### 1. Sidebar Not Showing
- Check if component is imported
- Verify sidebar is inside template
- Check z-index conflicts

### 2. Dashboard Data Not Loading
- Verify API endpoint exists
- Check auth token is valid
- Check CORS headers
- Check network in browser DevTools

### 3. Styling Not Applied
- Check class names match
- Verify scoped styles
- Check CSS specificity
- Clear browser cache

### 4. Icons Not Showing
- Verify Font Awesome is loaded
- Check icon class names
- Inspect in browser DevTools

---

## 📝 Common Customizations

### Add New Menu Item
```vue
const menuSections = [
  {
    section: 'Dashboard',
    collapsible: false,
    items: [
      // ...existing items
      {
        label: 'New Feature',
        icon: 'fas fa-star',
        route: '/admin/new-feature',
        badge: null
      }
    ]
  }
]
```

### Change Sidebar Color
Edit the sidebar component and change the `card-icon` background:
```css
.card-icon {
  background: linear-gradient(135deg, #your-color-1, #your-color-2);
}
```

### Add New Summary Card
```vue
<div class="summary-card">
  <div class="card-icon">
    <i class="fas fa-icon"></i>
  </div>
  <div class="card-content">
    <h3>New Metric</h3>
    <p class="card-value">{{ newValue }}</p>
  </div>
</div>
```

---

## 💡 Best Practices

1. **Always include error handling** in API calls
2. **Use v-loading** or skeleton loaders during data fetch
3. **Format numbers and dates** for better UX
4. **Keep sidebar sections collapsible** to reduce clutter
5. **Use role-specific colors** for visual consistency
6. **Test on mobile** before deploying
7. **Cache frequently accessed data** to reduce API calls
8. **Implement proper authentication** headers on all requests

---

## 🔗 Related Documentation

- [DASHBOARDS_AND_SIDEBARS_COMPLETE.md](./DASHBOARDS_AND_SIDEBARS_COMPLETE.md) - Full implementation details
- [SIDEBAR_IMPLEMENTATION_GUIDE.md](./SIDEBAR_IMPLEMENTATION_GUIDE.md) - Sidebar structure guide
- [DASHBOARD_ENDPOINTS.md](./DASHBOARD_ENDPOINTS.md) - API endpoint reference
- [DASHBOARDS_IMPLEMENTATION_STATUS.md](./DASHBOARDS_IMPLEMENTATION_STATUS.md) - Current status

---

## 📞 Support

For questions or issues:
1. Check the error console in browser DevTools
2. Verify API endpoints return correct data
3. Check router configuration
4. Ensure auth token is valid
5. Review network requests in DevTools

