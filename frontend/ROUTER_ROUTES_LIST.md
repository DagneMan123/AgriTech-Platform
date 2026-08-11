# Complete Router Routes List

## Public Routes (No Authentication Required)

### Home
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/` | HomeView | home | ❌ | - |
| `/404` | NotFoundView | not-found | ❌ | - |

### Authentication Routes
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/auth/login` | LoginView | login | ❌ | - |
| `/auth/register` | RegisterView | register | ❌ | - |
| `/auth/forgot-password` | ForgotPasswordView | forgot-password | ❌ | - |
| `/auth/reset-password/:token` | ResetPasswordView | reset-password | ❌ | - |

### Marketplace Routes (Public Browsing)
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/marketplace/categories` | MarketplaceCategoriesView | marketplace-categories | ❌ | - |
| `/marketplace/products` | MarketplaceProductsView | marketplace-products | ❌ | - |
| `/marketplace/search` | MarketplaceSearchView | marketplace-search | ❌ | - |
| `/marketplace/product/:id` | MarketplaceProductDetailsView | marketplace-product-detail | ❌ | - |

## Protected Routes (Authentication Required)

### Admin Routes (`/admin/*`)
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/admin/dashboard` | AdminDashboardView | admin-dashboard | ✅ | `admin` |
| `/admin/users` | AdminUsersView | admin-users | ✅ | `admin` |
| `/admin/roles` | AdminRolesView | admin-roles | ✅ | `admin` |
| `/admin/activity-logs` | AdminActivityLogsView | admin-activity-logs | ✅ | `admin` |
| `/admin/settings` | AdminSettingsView | admin-settings | ✅ | `admin` |
| `/admin/reports` | AdminReportsView | admin-reports | ✅ | `admin` |

### Farmer Routes (`/farmer/*`)
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/farmer/dashboard` | FarmerDashboardView | farmer-dashboard | ✅ | `farmer` |
| `/farmer/farms` | FarmerFarmsView | farmer-farms | ✅ | `farmer` |
| `/farmer/farms/create` | FarmerCreateFarmView | farmer-create-farm | ✅ | `farmer` |
| `/farmer/farms/:id` | FarmerFarmDetailView | farmer-farm-detail | ✅ | `farmer` |
| `/farmer/crops` | FarmerCropsView | farmer-crops | ✅ | `farmer` |
| `/farmer/products` | FarmerProductsView | farmer-products | ✅ | `farmer` |
| `/farmer/products/create` | FarmerCreateProductView | farmer-create-product | ✅ | `farmer` |
| `/farmer/orders` | FarmerOrdersView | farmer-orders | ✅ | `farmer` |
| `/farmer/consultations` | FarmerConsultationsView | farmer-consultations | ✅ | `farmer` |
| `/farmer/loans` | FarmerLoansView | farmer-loans | ✅ | `farmer` |

### Buyer Routes (`/buyer/*`)
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/buyer/dashboard` | BuyerDashboardView | buyer-dashboard | ✅ | `buyer` |
| `/buyer/marketplace` | BuyerMarketplaceView | buyer-marketplace | ✅ | `buyer` |
| `/buyer/products/:id` | BuyerProductDetailView | buyer-product-detail | ✅ | `buyer` |
| `/buyer/cart` | BuyerCartView | buyer-cart | ✅ | `buyer` |
| `/buyer/orders` | BuyerOrdersView | buyer-orders | ✅ | `buyer` |
| `/buyer/wishlist` | BuyerWishlistView | buyer-wishlist | ✅ | `buyer` |

### Supplier Routes (`/supplier/*`)
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/supplier/dashboard` | SupplierDashboardView | supplier-dashboard | ✅ | `supplier` |
| `/supplier/products` | SupplierProductsView | supplier-products | ✅ | `supplier` |
| `/supplier/inventory` | SupplierInventoryView | supplier-inventory | ✅ | `supplier` |
| `/supplier/orders` | SupplierOrdersView | supplier-orders | ✅ | `supplier` |
| `/supplier/license` | SupplierLicenseApplicationView | supplier-license | ✅ | `supplier` |

### Transport Routes (`/transport/*`)
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/transport/dashboard` | TransportDashboardView | transport-dashboard | ✅ | `transport` |
| `/transport/deliveries` | TransportDeliveriesView | transport-deliveries | ✅ | `transport` |
| `/transport/vehicles` | TransportVehiclesView | transport-vehicles | ✅ | `transport` |

### Expert Routes (`/expert/*`)
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/expert/dashboard` | ExpertDashboardView | expert-dashboard | ✅ | `expert` |
| `/expert/consultations` | ExpertConsultationsView | expert-consultations | ✅ | `expert` |
| `/expert/articles` | ExpertArticlesView | expert-articles | ✅ | `expert` |
| `/expert/training` | ExpertTrainingView | expert-training | ✅ | `expert` |

### Financial Routes (`/financial/*`)
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/financial/dashboard` | FinancialDashboardView | financial-dashboard | ✅ | `financial` |
| `/financial/loans` | FinancialLoansView | financial-loans | ✅ | `financial` |
| `/financial/insurance` | FinancialInsuranceView | financial-insurance | ✅ | `financial` |

### Cooperative Routes (`/cooperative/*`)
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/cooperative/dashboard` | CooperativeDashboardView | cooperative-dashboard | ✅ | `cooperative` |
| `/cooperative/members` | CooperativeMembersView | cooperative-members | ✅ | `cooperative` |
| `/cooperative/sales` | CooperativeSalesView | cooperative-sales | ✅ | `cooperative` |
| `/cooperative/reports` | CooperativeReportsView | cooperative-reports | ✅ | `cooperative` |

### Common Routes (`/app/*`)
| Path | Component | Name | Auth Required | Role Required |
|------|-----------|------|---------------|---------------|
| `/app/profile` | ProfileView | profile | ✅ | - |

## Route Redirects

| From | To | Condition |
|------|----|-----------| 
| `/login` | `/auth/login` | Always |
| `/register` | `/auth/register` | Always |
| `/dashboard` | `/{role}/dashboard` | Based on user role |
| `/:pathMatch(.*)*` | `/404` | Invalid routes |

## Summary Statistics

- **Total Routes**: 62
- **Public Routes**: 11 (17.7%)
- **Protected Routes**: 51 (82.3%)
- **User Roles**: 8
- **Layouts**: 4 (Guest, Auth, Main, Dashboard)

### Routes by Role
- Admin: 6 routes
- Farmer: 10 routes
- Buyer: 6 routes
- Supplier: 5 routes
- Transport: 3 routes
- Expert: 4 routes
- Financial: 3 routes
- Cooperative: 4 routes
- Common: 1 route
- Public: 11 routes (no role required)

## Navigation Guards

### Before Each Route (`beforeEach`)
1. ✅ Check if route requires authentication
2. ✅ Verify user is authenticated
3. ✅ Check role-based access
4. ✅ Update document title
5. ✅ Allow or redirect navigation

### After Each Route (`afterEach`)
1. 📝 Log navigation events
2. 📊 Can extend for analytics

## Usage Examples

### Navigating Programmatically
```typescript
import router from '@/router'

// By path
router.push('/farmer/dashboard')

// By name
router.push({ name: 'farmer-dashboard' })

// With parameters
router.push({ 
  name: 'farmer-farm-detail', 
  params: { id: 123 } 
})

// With query params
router.push({
  name: 'marketplace-search',
  query: { q: 'tomatoes' }
})
```

### In Templates
```vue
<!-- By path -->
<RouterLink to="/farmer/dashboard">Dashboard</RouterLink>

<!-- By name -->
<RouterLink :to="{ name: 'farmer-dashboard' }">Dashboard</RouterLink>

<!-- Dynamic -->
<RouterLink :to="`/farmer/farms/${farmId}`">View Farm</RouterLink>
```

### Getting Current Route Info
```typescript
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// Current path
console.log(route.path) // e.g. '/farmer/dashboard'

// Current name
console.log(route.name) // e.g. 'farmer-dashboard'

// Current params
console.log(route.params) // e.g. { id: '123' }

// Current query
console.log(route.query) // e.g. { redirect: '/auth/login' }
```

---

**Last Updated**: August 5, 2026  
**Router Version**: 2.0 - Complete Role-Based System
