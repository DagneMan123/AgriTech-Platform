# AgriConnect Frontend - Complete Folder Structure

## Project Structure Overview

```
frontend/
├── public/
│   ├── favicon.ico                 # Favicon for the application
│   ├── robots.txt                  # SEO robots configuration
│   └── images/                     # Public images (SVG, PNG, JPG)
│       └── .gitkeep
│
├── src/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── global.css          # Global styles and utility classes
│   │   │   └── main.css            # Imported in main.ts
│   │   ├── fonts/                  # Custom fonts (TTF, OTF, WOFF)
│   │   │   └── .gitkeep
│   │   ├── icons/                  # SVG icons and icon files
│   │   │   └── .gitkeep
│   │   └── images/                 # Static images (used in component imports)
│   │       └── .gitkeep
│   │
│   ├── components/
│   │   ├── common/                 # Reusable UI components
│   │   │   ├── AppButton.vue       # Customizable button component
│   │   │   ├── AppCard.vue         # Card container component
│   │   │   ├── AppInput.vue        # Input field component
│   │   │   ├── AppModal.vue        # Modal dialog component
│   │   │   ├── AppPagination.vue   # Pagination component
│   │   │   ├── AppTable.vue        # Data table component
│   │   │   └── LoadingSpinner.vue  # Loading indicator component
│   │   │
│   │   ├── layout/                 # Layout-related components
│   │   │   ├── Navbar.vue          # Top navigation bar
│   │   │   ├── Sidebar.vue         # Side navigation menu
│   │   │   ├── Header.vue          # Page header component
│   │   │   ├── Footer.vue          # Page footer component
│   │   │   └── Breadcrumb.vue      # Breadcrumb navigation
│   │   │
│   │   ├── charts/                 # Chart components
│   │   │   ├── LineChart.vue       # Line chart visualization
│   │   │   └── BarChart.vue        # Bar chart visualization
│   │   │
│   │   ├── maps/                   # Map components
│   │   │   └── LocationMap.vue     # Location/tracking map
│   │   │
│   │   ├── forms/                  # Form-related components
│   │   │   ├── FormField.vue       # Text input field wrapper
│   │   │   └── FormSelect.vue      # Select dropdown wrapper
│   │   │
│   │   ├── BenefitItem.vue         # Home page benefit item
│   │   ├── FeatureCard.vue         # Home page feature card
│   │   ├── NavItem.vue             # Navigation item component
│   │   ├── NavMenu.vue             # Navigation menu wrapper
│   │   ├── NotificationBell.vue    # Notification icon component
│   │   └── StatCard.vue            # Statistics card component
│   │
│   ├── layouts/
│   │   ├── MainLayout.vue          # Main application layout with sidebar
│   │   ├── AuthLayout.vue          # Authentication pages layout
│   │   ├── GuestLayout.vue         # Public pages layout (home, etc)
│   │   └── DashboardLayout.vue     # Dashboard layout with header
│   │
│   ├── views/
│   │   ├── HomeView.vue            # Landing page
│   │   ├── NotFoundView.vue        # 404 error page
│   │   ├── ProfileView.vue         # User profile page
│   │   │
│   │   ├── auth/                   # Authentication pages
│   │   │   ├── LoginView.vue       # Login page
│   │   │   ├── RegisterView.vue    # User registration
│   │   │   ├── ForgotPasswordView.vue
│   │   │   └── ResetPasswordView.vue
│   │   │
│   │   ├── admin/                  # Admin dashboard views
│   │   │   ├── DashboardView.vue   # Admin overview
│   │   │   ├── UsersView.vue       # User management
│   │   │   ├── RolesView.vue       # Role management
│   │   │   ├── PermissionsView.vue # Permission management
│   │   │   ├── SettingsView.vue    # System settings
│   │   │   ├── ActivityLogsView.vue# Activity logging
│   │   │   └── ReportsView.vue     # System reports
│   │   │
│   │   ├── farmer/                 # Farmer role views
│   │   │   ├── DashboardView.vue   # Farm overview
│   │   │   ├── FarmsView.vue       # List of farms
│   │   │   ├── FarmDetailView.vue  # Farm details page
│   │   │   ├── CreateFarmView.vue  # Create new farm
│   │   │   ├── CropsView.vue       # Crop management
│   │   │   ├── ProductsView.vue    # Product listings
│   │   │   ├── CreateProductView.vue
│   │   │   ├── OrdersView.vue      # Order management
│   │   │   ├── ConsultationsView.vue
│   │   │   ├── LoansView.vue       # Loan management
│   │   │   └── WeatherView.vue     # Weather forecast
│   │   │
│   │   ├── buyer/                  # Buyer role views
│   │   │   ├── DashboardView.vue   # Buyer dashboard
│   │   │   ├── MarketplaceView.vue # Marketplace browsing
│   │   │   ├── ProductDetailView.vue
│   │   │   ├── CartView.vue        # Shopping cart
│   │   │   ├── CheckoutView.vue    # Checkout process
│   │   │   ├── OrdersView.vue      # Purchase history
│   │   │   ├── PaymentsView.vue    # Payment info
│   │   │   ├── WishlistView.vue    # Saved items
│   │   │   └── ReviewsView.vue     # Product reviews
│   │   │
│   │   ├── supplier/               # Supplier role views
│   │   │   ├── DashboardView.vue   # Supplier overview
│   │   │   ├── ProductsView.vue    # Product management
│   │   │   ├── CategoriesView.vue  # Category management
│   │   │   ├── InventoryView.vue   # Stock management
│   │   │   ├── WarehousesView.vue  # Warehouse management
│   │   │   ├── LicenseApplicationView.vue
│   │   │   ├── OrdersView.vue      # Supplier orders
│   │   │   └── ReportsView.vue     # Sales reports
│   │   │
│   │   ├── transport/              # Transport provider views
│   │   │   ├── DashboardView.vue   # Transport overview
│   │   │   ├── DeliveriesView.vue  # Delivery management
│   │   │   ├── TrackingView.vue    # Track shipments
│   │   │   ├── VehiclesView.vue    # Fleet management
│   │   │   └── RoutesView.vue      # Route planning
│   │   │
│   │   ├── cooperative/            # Cooperative role views
│   │   │   ├── DashboardView.vue   # Cooperative overview
│   │   │   ├── MembersView.vue     # Member management
│   │   │   ├── SalesView.vue       # Sales tracking
│   │   │   └── ReportsView.vue     # Cooperative reports
│   │   │
│   │   ├── expert/                 # Agricultural expert views
│   │   │   ├── DashboardView.vue   # Expert overview
│   │   │   ├── ConsultationsView.vue
│   │   │   ├── TrainingView.vue    # Training programs
│   │   │   ├── ArticlesView.vue    # Knowledge base articles
│   │   │   └── FarmVisitsView.vue  # Visit scheduling
│   │   │
│   │   ├── financial/              # Financial institution views
│   │   │   ├── DashboardView.vue   # Financial overview
│   │   │   ├── LoansView.vue       # Loan management
│   │   │   ├── InsuranceView.vue   # Insurance products
│   │   │   ├── TransactionsView.vue
│   │   │   └── ReportsView.vue     # Financial reports
│   │   │
│   │   └── marketplace/            # Public marketplace views
│   │       ├── CategoriesView.vue  # Product categories
│   │       ├── ProductsView.vue    # All products
│   │       ├── SearchView.vue      # Product search
│   │       └── ProductDetailsView.vue
│   │
│   ├── router/
│   │   ├── index.ts                # Main router configuration
│   │   ├── auth.ts                 # Authentication routes
│   │   ├── admin.ts                # Admin routes
│   │   ├── farmer.ts               # Farmer routes
│   │   ├── buyer.ts                # Buyer routes
│   │   ├── supplier.ts             # Supplier routes
│   │   ├── transport.ts            # Transport routes
│   │   ├── expert.ts               # Expert routes
│   │   ├── financial.ts            # Financial routes
│   │   ├── cooperative.ts          # Cooperative routes
│   │   └── marketplace.ts          # Marketplace routes
│   │
│   ├── stores/
│   │   ├── authStore.ts            # Authentication state
│   │   ├── farmerStore.ts          # Farmer-specific state
│   │   ├── buyerStore.ts           # Buyer-specific state
│   │   └── marketplaceStore.ts     # Marketplace state
│   │
│   ├── services/
│   │   ├── auth.service.ts         # Authentication API calls
│   │   ├── farmer.service.ts       # Farmer API calls
│   │   ├── buyer.service.ts        # Buyer API calls
│   │   ├── supplier.service.ts     # Supplier API calls
│   │   ├── transport.service.ts    # Transport API calls
│   │   ├── expert.service.ts       # Expert API calls
│   │   ├── financial.service.ts    # Financial API calls
│   │   ├── admin.service.ts        # Admin API calls
│   │   └── marketplace.service.ts  # Marketplace API calls
│   │
│   ├── composables/
│   │   ├── useAuth.ts              # Authentication composable
│   │   ├── usePagination.ts        # Pagination logic
│   │   └── useNotification.ts      # Notification management
│   │
│   ├── types/
│   │   ├── user.ts                 # User type definitions
│   │   ├── product.ts              # Product type definitions
│   │   └── api.ts                  # API response types
│   │
│   ├── utils/
│   │   ├── constants.ts            # Application constants
│   │   ├── permissions.ts          # Permission checking utilities
│   │   ├── formatters.ts           # Data formatting utilities
│   │   └── helpers.ts              # Helper functions
│   │
│   ├── styles/
│   │   └── main.css                # Main stylesheet import
│   │
│   ├── App.vue                     # Root Vue component
│   └── main.ts                     # Application entry point
│
├── .env                            # Environment variables
├── .gitignore                      # Git ignore configuration
├── .npmrc                          # NPM configuration
├── index.html                      # HTML entry point
├── package.json                    # Dependencies and scripts
├── package-lock.json               # Locked dependencies
├── postcss.config.js               # PostCSS configuration
├── tailwind.config.js              # Tailwind CSS configuration
├── tsconfig.json                   # TypeScript configuration
├── tsconfig.app.json               # App-specific TypeScript config
├── vite.config.ts                  # Vite bundler configuration
│
├── README.md                       # Project documentation
├── QUICK_START.md                  # Quick reference guide
├── PROJECT_SUMMARY.md              # Project overview
├── FOLDER_STRUCTURE.md             # This file
└── RESTRUCTURE_COMPLETE.md         # Restructuring documentation
```

## Directory Purpose Guide

### public/
Static files that are served directly without being processed by the build system.
- `favicon.ico` - Browser tab icon
- `robots.txt` - SEO configuration for search engines
- `images/` - Public images accessible via URL

### src/assets/
Build-time assets that are imported by components.
- `css/` - Global CSS and utility styles
- `fonts/` - Custom font files
- `icons/` - SVG icons used in components
- `images/` - Images imported in components

### src/components/
Reusable Vue components organized by functionality.
- `common/` - Generic UI components (buttons, cards, inputs)
- `layout/` - Layout structure components
- `charts/` - Data visualization components
- `maps/` - Location and map components
- `forms/` - Form input wrapper components

### src/layouts/
Template layouts that wrap page views.
- Used to provide consistent structure (header, sidebar, footer)
- Can be nested or combined

### src/views/
Page-level components organized by user role.
- Each role has a directory with its specific pages
- Views use layouts and components to build pages

### src/router/
Vue Router configuration split by concern.
- `index.ts` - Main router with global navigation guards
- Role-specific files - Routes for each user role
- Enables better code organization and lazy loading

### src/stores/
Pinia state management stores.
- `authStore.ts` - Global authentication state
- Role-specific stores for role-based data

### src/services/
API service layer for backend communication.
- One service per role/feature area
- Handles HTTP requests and response transformation
- Centralizes API endpoints

### src/composables/
Reusable Vue 3 Composition API logic.
- `useAuth()` - Authentication logic and helpers
- `usePagination()` - Pagination state management
- `useNotification()` - Toast/notification system

### src/types/
TypeScript type definitions and interfaces.
- `user.ts` - User and role types
- `product.ts` - Product and marketplace types
- `api.ts` - API request/response types

### src/utils/
Utility functions and constants.
- `constants.ts` - App-wide constants and enums
- `permissions.ts` - Role-based permission checks
- `formatters.ts` - Data formatting functions
- `helpers.ts` - General helper functions

## File Naming Conventions

- **Components**: PascalCase with `.vue` extension (e.g., `AppButton.vue`)
- **Services**: camelCase with `.service.ts` extension (e.g., `auth.service.ts`)
- **Stores**: camelCase with `Store.ts` suffix (e.g., `authStore.ts`)
- **Composables**: camelCase with `use` prefix (e.g., `useAuth.ts`)
- **Views**: PascalCase ending with `View` (e.g., `DashboardView.vue`)
- **Types**: camelCase with `.ts` extension (e.g., `user.ts`)
- **Utils**: camelCase with `.ts` extension (e.g., `constants.ts`)

## Import Path Aliases

- `@/` - Points to `src/` directory
- Used throughout for cleaner imports
- Configured in `vite.config.ts` and `tsconfig.json`

## Build Output

- Development: Dev server at `http://localhost:5173`
- Production: Built to `dist/` folder with optimized bundle
- All files are compiled to JavaScript and CSS

---

**Last Updated**: August 5, 2026  
**Status**: Production Ready - Complete Enterprise Architecture
