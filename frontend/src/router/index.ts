import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

// Layout components
import AuthLayout from '@/layouts/AuthLayout.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'

// Home & Common
import HomeView from '@/views/HomeView.vue'
import NotFoundView from '@/views/NotFoundView.vue'
import ProfileView from '@/views/ProfileView.vue'

// Auth views
import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import ForgotPasswordView from '@/views/auth/ForgotPasswordView.vue'
import ResetPasswordView from '@/views/auth/ResetPasswordView.vue'

// Admin views
import AdminDashboardView from '@/views/admin/AdminDashboard.vue'
import AdminUsersView from '@/views/admin/UsersView.vue'
import AdminRolesView from '@/views/admin/RolesView.vue'
import AdminActivityLogsView from '@/views/admin/ActivityLogsView.vue'
import AdminSettingsView from '@/views/admin/SettingsView.vue'
import AdminReportsView from '@/views/admin/ReportsView.vue'

// Farmer views
import FarmerDashboardView from '@/views/farmer/FarmerDashboard.vue'
import FarmerFarmsView from '@/views/farmer/FarmsView.vue'
import FarmerCropsView from '@/views/farmer/CropsView.vue'
import FarmerHarvestsView from '@/views/farmer/HarvestsView.vue'
import FarmerProductsView from '@/views/farmer/ProductsView.vue'
import FarmerOrdersView from '@/views/farmer/OrdersView.vue'
import FarmerBuyInputsView from '@/views/farmer/BuyInputsView.vue'
import FarmerTransportView from '@/views/farmer/TransportView.vue'
import FarmerWeatherView from '@/views/farmer/WeatherView.vue'
import FarmerMarketPricesView from '@/views/farmer/MarketPricesView.vue'
import FarmerConsultationsView from '@/views/farmer/ConsultationsView.vue'
import FarmerLoansView from '@/views/farmer/LoansView.vue'
import FarmerReportsView from '@/views/farmer/ReportsView.vue'
import FarmerProfileView from '@/views/farmer/ProfileView.vue'

// Buyer views
import BuyerDashboardView from '@/views/buyer/BuyerDashboard.vue'
import BuyerMarketplaceView from '@/views/buyer/MarketplaceView.vue'
import BuyerProductDetailView from '@/views/buyer/ProductDetailView.vue'
import BuyerCartView from '@/views/buyer/CartView.vue'
import BuyerOrdersView from '@/views/buyer/OrdersView.vue'
import BuyerWishlistView from '@/views/buyer/WishlistView.vue'
import BuyerCategoriesView from '@/views/buyer/CategoriesView.vue'
import BuyerPaymentsView from '@/views/buyer/PaymentsView.vue'
import BuyerReviewsView from '@/views/buyer/ReviewsView.vue'
import BuyerProfileView from '@/views/buyer/ProfileView.vue'

// Supplier views
import SupplierDashboardView from '@/views/supplier/SupplierDashboard.vue'
import SupplierProductsView from '@/views/supplier/ProductsView.vue'
import SupplierInventoryView from '@/views/supplier/InventoryView.vue'
import SupplierOrdersView from '@/views/supplier/OrdersView.vue'
import SupplierLicenseApplicationView from '@/views/supplier/LicenseApplicationView.vue'

// Transport views
import TransportDashboardView from '@/views/transport/TransportDashboard.vue'
import TransportDeliveriesView from '@/views/transport/DeliveriesView.vue'
import TransportVehiclesView from '@/views/transport/VehiclesView.vue'

// Expert views
import ExpertDashboardView from '@/views/expert/ExpertDashboard.vue'
import ExpertConsultationsView from '@/views/expert/ConsultationsView.vue'
import ExpertArticlesView from '@/views/expert/ArticlesView.vue'
import ExpertTrainingView from '@/views/expert/TrainingView.vue'

// Financial views
import FinancialDashboardView from '@/views/financial/FinancialDashboard.vue'
import FinancialLoansView from '@/views/financial/LoansView.vue'
import FinancialInsuranceView from '@/views/financial/InsuranceView.vue'

// Cooperative views
import CooperativeDashboardView from '@/views/cooperative/CooperativeDashboard.vue'
import CooperativeMembersView from '@/views/cooperative/MembersView.vue'
import CooperativeSalesView from '@/views/cooperative/SalesView.vue'
import CooperativeReportsView from '@/views/cooperative/ReportsView.vue'

const routes: RouteRecordRaw[] = [
  // Home - Public
  {
    path: '/',
    name: 'home',
    component: HomeView,
    meta: { requiresAuth: false, title: 'Home' }
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
      },
      {
        path: 'reset-password/:token',
        name: 'reset-password',
        component: ResetPasswordView,
        meta: { requiresAuth: false, title: 'Reset Password' }
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
