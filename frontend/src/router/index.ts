import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

// Layout components - loaded eagerly (small)
import AuthLayout from '@/layouts/AuthLayout.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'

// Home & Common - loaded eagerly
import HomeView from '@/views/HomeView.vue'
import NotFoundView from '@/views/NotFoundView.vue'
import ProfileView from '@/views/ProfileView.vue'

// Auth views - loaded eagerly (on auth path)
import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import ForgotPasswordView from '@/views/auth/ForgotPasswordView.vue'
import ResetPasswordView from '@/views/auth/ResetPasswordView.vue'
import VerificationPendingView from '@/views/auth/VerificationPendingView.vue'

// Lazy load all dashboard and secondary views for faster initial load
// Using dynamic imports directly (recommended by Vue Router)
const AdminDashboardView = () => import('@/views/admin/AdminDashboard.vue')
const AdminUsersView = () => import('@/views/admin/UsersView.vue')
const AdminRolesView = () => import('@/views/admin/RolesView.vue')
const AdminActivityLogsView = () => import('@/views/admin/ActivityLogsView.vue')
const AdminSettingsView = () => import('@/views/admin/SettingsView.vue')
const AdminReportsView = () => import('@/views/admin/ReportsView.vue')

const FarmerDashboardView = () => import('@/views/farmer/FarmerDashboard.vue')
const FarmerFarmsView = () => import('@/views/farmer/FarmsView.vue')
const FarmerCropsView = () => import('@/views/farmer/CropsView.vue')
const FarmerHarvestsView = () => import('@/views/farmer/HarvestsView.vue')
const FarmerProductsView = () => import('@/views/farmer/ProductsView.vue')
const FarmerOrdersView = () => import('@/views/farmer/OrdersView.vue')
const FarmerBuyInputsView = () => import('@/views/farmer/BuyInputsView.vue')
const FarmerTransportView = () => import('@/views/farmer/TransportView.vue')
const FarmerWeatherView = () => import('@/views/farmer/WeatherView.vue')
const FarmerMarketPricesView = () => import('@/views/farmer/MarketPricesView.vue')
const FarmerConsultationsView = () => import('@/views/farmer/ConsultationsView.vue')
const FarmerLoansView = () => import('@/views/farmer/LoansView.vue')
const FarmerReportsView = () => import('@/views/farmer/ReportsView.vue')
const FarmerProfileView = () => import('@/views/farmer/ProfileView.vue')

const BuyerDashboardView = () => import('@/views/buyer/BuyerDashboard.vue')
const BuyerMarketplaceView = () => import('@/views/buyer/MarketplaceView.vue')
const BuyerProductDetailView = () => import('@/views/buyer/ProductDetailView.vue')
const BuyerCartView = () => import('@/views/buyer/CartView.vue')
const BuyerOrdersView = () => import('@/views/buyer/OrdersView.vue')
const BuyerWishlistView = () => import('@/views/buyer/WishlistView.vue')
const BuyerCategoriesView = () => import('@/views/buyer/CategoriesView.vue')
const BuyerPaymentsView = () => import('@/views/buyer/PaymentsView.vue')
const BuyerReviewsView = () => import('@/views/buyer/ReviewsView.vue')
const BuyerProfileView = () => import('@/views/buyer/ProfileView.vue')

const SupplierDashboardView = () => import('@/views/supplier/SupplierDashboard.vue')
const SupplierProductsView = () => import('@/views/supplier/ProductsView.vue')
const SupplierInventoryView = () => import('@/views/supplier/InventoryView.vue')
const SupplierOrdersView = () => import('@/views/supplier/OrdersView.vue')
const SupplierLicenseApplicationView = () => import('@/views/supplier/LicenseApplicationView.vue')

const TransportDashboardView = () => import('@/views/transport/TransportDashboard.vue')
const TransportDeliveriesView = () => import('@/views/transport/DeliveriesView.vue')
const TransportVehiclesView = () => import('@/views/transport/VehiclesView.vue')

const ExpertDashboardView = () => import('@/views/expert/ExpertDashboard.vue')
const ExpertConsultationsView = () => import('@/views/expert/ConsultationsView.vue')
const ExpertArticlesView = () => import('@/views/expert/ArticlesView.vue')
const ExpertTrainingView = () => import('@/views/expert/TrainingView.vue')

const FinancialDashboardView = () => import('@/views/financial/FinancialDashboard.vue')
const FinancialLoansView = () => import('@/views/financial/LoansView.vue')
const FinancialInsuranceView = () => import('@/views/financial/InsuranceView.vue')

const CooperativeDashboardView = () => import('@/views/cooperative/CooperativeDashboard.vue')
const CooperativeMembersView = () => import('@/views/cooperative/MembersView.vue')
const CooperativeSalesView = () => import('@/views/cooperative/SalesView.vue')
const CooperativeReportsView = () => import('@/views/cooperative/ReportsView.vue')

const routes: RouteRecordRaw[] = [
  // Home - Public
  {
    path: '/',
    name: 'home',
    component: HomeView,
    meta: { requiresAuth: false, title: 'Home' }
  },

  // Reset Password - Public direct link (must be BEFORE auth routes)
  {
    path: '/reset-password',
    name: 'reset-password',
    component: ResetPasswordView,
    meta: { requiresAuth: false, title: 'Reset Password' }
  },

  // Verification Pending - After registration
  {
    path: '/verification-pending',
    name: 'verification-pending',
    component: VerificationPendingView,
    meta: { requiresAuth: true, title: 'Verification Pending' }
  },

  // Authentication
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      {
        path: 'login',
        name: 'login',
        component: LoginView,
        meta: { requiresAuth: false, title: 'Login' }
      },
      {
        path: 'register',
        name: 'register',
        component: RegisterView,
        meta: { requiresAuth: false, title: 'Register' }
      },
      {
        path: 'forgot-password',
        name: 'forgot-password',
        component: ForgotPasswordView,
        meta: { requiresAuth: false, title: 'Forgot Password' }
      }
    ]
  },


  // Admin Routes
  {
    path: '/admin',
    meta: { requiresAuth: true, requiredRole: 'admin' },
    children: [
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: AdminDashboardView,
        meta: { requiresAuth: true, requiredRole: 'admin', title: 'Admin Dashboard' }
      },
      {
        path: 'users',
        name: 'admin-users',
        component: AdminUsersView,
        meta: { requiresAuth: true, requiredRole: 'admin', title: 'Manage Users' }
      },
      {
        path: 'roles',
        name: 'admin-roles',
        component: AdminRolesView,
        meta: { requiresAuth: true, requiredRole: 'admin', title: 'Roles' }
      },
      {
        path: 'activity-logs',
        name: 'admin-activity-logs',
        component: AdminActivityLogsView,
        meta: { requiresAuth: true, requiredRole: 'admin', title: 'Activity Logs' }
      },
      {
        path: 'settings',
        name: 'admin-settings',
        component: AdminSettingsView,
        meta: { requiresAuth: true, requiredRole: 'admin', title: 'Settings' }
      },
      {
        path: 'reports',
        name: 'admin-reports',
        component: AdminReportsView,
        meta: { requiresAuth: true, requiredRole: 'admin', title: 'Reports' }
      }
    ]
  },

  // Farmer Routes
  {
    path: '/farmer',
    meta: { requiresAuth: true, requiredRole: 'farmer' },
    children: [
      {
        path: 'dashboard',
        name: 'farmer-dashboard',
        component: FarmerDashboardView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Farm Dashboard' }
      },
      {
        path: 'farms',
        name: 'farmer-farms',
        component: FarmerFarmsView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'My Farms' }
      },
      {
        path: 'crops',
        name: 'farmer-crops',
        component: FarmerCropsView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'My Crops' }
      },
      {
        path: 'harvests',
        name: 'farmer-harvests',
        component: FarmerHarvestsView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Harvests' }
      },
      {
        path: 'products',
        name: 'farmer-products',
        component: FarmerProductsView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'My Products' }
      },
      {
        path: 'orders',
        name: 'farmer-orders',
        component: FarmerOrdersView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Orders' }
      },
      {
        path: 'buy-inputs',
        name: 'farmer-buy-inputs',
        component: FarmerBuyInputsView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Buy Farm Inputs' }
      },
      {
        path: 'transport',
        name: 'farmer-transport',
        component: FarmerTransportView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Transport Requests' }
      },
      {
        path: 'weather',
        name: 'farmer-weather',
        component: FarmerWeatherView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Weather Forecast' }
      },
      {
        path: 'market-prices',
        name: 'farmer-market-prices',
        component: FarmerMarketPricesView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Market Prices' }
      },
      {
        path: 'consultations',
        name: 'farmer-consultations',
        component: FarmerConsultationsView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Consultations' }
      },
      {
        path: 'loans',
        name: 'farmer-loans',
        component: FarmerLoansView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Loans' }
      },
      {
        path: 'reports',
        name: 'farmer-reports',
        component: FarmerReportsView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'Reports' }
      },
      {
        path: 'profile',
        name: 'farmer-profile',
        component: FarmerProfileView,
        meta: { requiresAuth: true, requiredRole: 'farmer', title: 'My Profile' }
      }
    ]
  },

  // Buyer Routes
  {
    path: '/buyer',
    meta: { requiresAuth: true, requiredRole: 'buyer' },
    children: [
      {
        path: 'dashboard',
        name: 'buyer-dashboard',
        component: BuyerDashboardView,
        meta: { requiresAuth: true, requiredRole: 'buyer', title: 'Buyer Dashboard' }
      },
      {
        path: 'marketplace',
        name: 'buyer-marketplace',
        component: BuyerMarketplaceView,
        meta: { requiresAuth: true, requiredRole: 'buyer', title: 'Marketplace' }
      },
      {
        path: 'products/:id',
        name: 'buyer-product-detail',
        component: BuyerProductDetailView,
        meta: { requiresAuth: true, requiredRole: 'buyer', title: 'Product Details' }
      },
      {
        path: 'cart',
        name: 'buyer-cart',
        component: BuyerCartView,
        meta: { requiresAuth: true, requiredRole: 'buyer', title: 'Shopping Cart' }
      },
      {
        path: 'orders',
        name: 'buyer-orders',
        component: BuyerOrdersView,
        meta: { requiresAuth: true, requiredRole: 'buyer', title: 'My Orders' }
      },
      {
        path: 'wishlist',
        name: 'buyer-wishlist',
        component: BuyerWishlistView,
        meta: { requiresAuth: true, requiredRole: 'buyer', title: 'Wishlist' }
      },
      {
        path: 'categories',
        name: 'buyer-categories',
        component: BuyerCategoriesView,
        meta: { requiresAuth: true, requiredRole: 'buyer', title: 'Categories' }
      },
      {
        path: 'payments',
        name: 'buyer-payments',
        component: BuyerPaymentsView,
        meta: { requiresAuth: true, requiredRole: 'buyer', title: 'Payment Methods' }
      },
      {
        path: 'reviews',
        name: 'buyer-reviews',
        component: BuyerReviewsView,
        meta: { requiresAuth: true, requiredRole: 'buyer', title: 'My Reviews' }
      },
      {
        path: 'profile',
        name: 'buyer-profile',
        component: BuyerProfileView,
        meta: { requiresAuth: true, requiredRole: 'buyer', title: 'My Profile' }
      }
    ]
  },

  // Supplier Routes
  {
    path: '/supplier',
    meta: { requiresAuth: true, requiredRole: 'supplier' },
    children: [
      {
        path: 'dashboard',
        name: 'supplier-dashboard',
        component: SupplierDashboardView,
        meta: { requiresAuth: true, requiredRole: 'supplier', title: 'Supplier Dashboard' }
      },
      {
        path: 'products',
        name: 'supplier-products',
        component: SupplierProductsView,
        meta: { requiresAuth: true, requiredRole: 'supplier', title: 'My Products' }
      },
      {
        path: 'inventory',
        name: 'supplier-inventory',
        component: SupplierInventoryView,
        meta: { requiresAuth: true, requiredRole: 'supplier', title: 'Inventory' }
      },
      {
        path: 'orders',
        name: 'supplier-orders',
        component: SupplierOrdersView,
        meta: { requiresAuth: true, requiredRole: 'supplier', title: 'Orders' }
      },
      {
        path: 'license',
        name: 'supplier-license',
        component: SupplierLicenseApplicationView,
        meta: { requiresAuth: true, requiredRole: 'supplier', title: 'License' }
      }
    ]
  },

  // Transport Routes
  {
    path: '/transport',
    meta: { requiresAuth: true, requiredRole: 'transport' },
    children: [
      {
        path: 'dashboard',
        name: 'transport-dashboard',
        component: TransportDashboardView,
        meta: { requiresAuth: true, requiredRole: 'transport', title: 'Transport Dashboard' }
      },
      {
        path: 'deliveries',
        name: 'transport-deliveries',
        component: TransportDeliveriesView,
        meta: { requiresAuth: true, requiredRole: 'transport', title: 'Deliveries' }
      },
      {
        path: 'vehicles',
        name: 'transport-vehicles',
        component: TransportVehiclesView,
        meta: { requiresAuth: true, requiredRole: 'transport', title: 'Vehicles' }
      }
    ]
  },

  // Expert Routes
  {
    path: '/expert',
    meta: { requiresAuth: true, requiredRole: 'expert' },
    children: [
      {
        path: 'dashboard',
        name: 'expert-dashboard',
        component: ExpertDashboardView,
        meta: { requiresAuth: true, requiredRole: 'expert', title: 'Expert Dashboard' }
      },
      {
        path: 'consultations',
        name: 'expert-consultations',
        component: ExpertConsultationsView,
        meta: { requiresAuth: true, requiredRole: 'expert', title: 'Consultations' }
      },
      {
        path: 'articles',
        name: 'expert-articles',
        component: ExpertArticlesView,
        meta: { requiresAuth: true, requiredRole: 'expert', title: 'Articles' }
      },
      {
        path: 'training',
        name: 'expert-training',
        component: ExpertTrainingView,
        meta: { requiresAuth: true, requiredRole: 'expert', title: 'Training Programs' }
      }
    ]
  },

  // Financial Routes
  {
    path: '/financial',
    meta: { requiresAuth: true, requiredRole: 'financial' },
    children: [
      {
        path: 'dashboard',
        name: 'financial-dashboard',
        component: FinancialDashboardView,
        meta: { requiresAuth: true, requiredRole: 'financial', title: 'Financial Dashboard' }
      },
      {
        path: 'loans',
        name: 'financial-loans',
        component: FinancialLoansView,
        meta: { requiresAuth: true, requiredRole: 'financial', title: 'Loans' }
      },
      {
        path: 'insurance',
        name: 'financial-insurance',
        component: FinancialInsuranceView,
        meta: { requiresAuth: true, requiredRole: 'financial', title: 'Insurance' }
      }
    ]
  },

  // Cooperative Routes
  {
    path: '/cooperative',
    meta: { requiresAuth: true, requiredRole: 'cooperative' },
    children: [
      {
        path: 'dashboard',
        name: 'cooperative-dashboard',
        component: CooperativeDashboardView,
        meta: { requiresAuth: true, requiredRole: 'cooperative', title: 'Cooperative Dashboard' }
      },
      {
        path: 'members',
        name: 'cooperative-members',
        component: CooperativeMembersView,
        meta: { requiresAuth: true, requiredRole: 'cooperative', title: 'Members' }
      },
      {
        path: 'sales',
        name: 'cooperative-sales',
        component: CooperativeSalesView,
        meta: { requiresAuth: true, requiredRole: 'cooperative', title: 'Sales' }
      },
      {
        path: 'reports',
        name: 'cooperative-reports',
        component: CooperativeReportsView,
        meta: { requiresAuth: true, requiredRole: 'cooperative', title: 'Reports' }
      }
    ]
  },

  // Common App Routes
  {
    path: '/app',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'profile',
        name: 'profile',
        component: ProfileView,
        meta: { requiresAuth: true, title: 'My Profile' }
      }
    ]
  },

  // Redirects
  {
    path: '/login',
    redirect: '/auth/login'
  },
  {
    path: '/register',
    redirect: '/auth/register'
  },
  {
    path: '/dashboard',
    redirect: () => {
      const authStore = useAuthStore()
      return `/${authStore.userRole || 'farmer'}/dashboard`
    }
  },

  // 404 - Must be last
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView,
    meta: { title: '404 - Page Not Found' }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior: (to, from, savedPosition) => {
    if (savedPosition) {
      return savedPosition
    }
    return { top: 0 }
  }
})

// Navigation Guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Check if route requires authentication
  const requiresAuth = to.matched.some((record) => record.meta?.requiresAuth)

  if (requiresAuth && !authStore.isAuthenticated) {
    // Redirect to login with return URL
    next({
      name: 'login',
      query: { redirect: to.fullPath }
    })
    return
  }

  // Check role-based access
  const requiredRole = to.matched.find((record) => record.meta?.requiredRole)?.meta?.requiredRole
  if (requiredRole && authStore.userRole !== requiredRole) {
    // Redirect to user's dashboard
    next(`/${authStore.userRole || 'farmer'}/dashboard`)
    return
  }

  // Update page title
  const title = to.meta?.title as string
  document.title = title ? `${title} | AgriConnect` : 'AgriConnect'

  next()
})

// After navigation
router.afterEach((to) => {
  // Analytics or logging here
  console.debug(`Navigated to: ${to.path}`)
})

export default router
