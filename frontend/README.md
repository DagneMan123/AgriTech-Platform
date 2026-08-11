# AgriConnect Frontend

A comprehensive Vue 3 + TypeScript frontend for the AgriConnect agricultural technology platform.

## Overview

AgriConnect is a smart agriculture platform that connects farmers, buyers, suppliers, transport providers, agricultural experts, financial institutions, and administrators through a single integrated digital ecosystem.

## Features

### Authentication & Authorization
- User registration with role selection
- Secure login with email and password
- Password reset functionality
- Email verification
- Role-based access control (RBAC)

### Role-Based Dashboards
- **Farmer**: Farm management, crop tracking, product sales, consultations, loans
- **Buyer**: Marketplace browsing, shopping cart, order management, wishlist
- **Supplier**: Product management, inventory tracking, order processing, licenses
- **Transport Provider**: Delivery management, vehicle tracking, GPS routing
- **Agricultural Expert**: Consultation management, article publishing, training materials
- **Financial Institution**: Loan management, insurance policies, transaction tracking
- **Admin**: User management, system monitoring, reports, settings

### Core Modules
- Online Marketplace with product search and filtering
- Farm Management system
- Order Management
- Payment processing
- Notification system
- Real-time weather and market price information
- Consultation booking and tracking
- Loan and insurance applications

## Tech Stack

- **Framework**: Vue 3
- **Language**: TypeScript
- **State Management**: Pinia
- **Routing**: Vue Router 4
- **HTTP Client**: Axios
- **Styling**: Tailwind CSS
- **Icons**: Lucide Vue Next
- **Date Handling**: date-fns
- **Maps**: Leaflet
- **Charts**: Chart.js & vue-chartjs

## Project Structure

```
src/
├── api/              # API service modules
│   ├── auth.ts
│   ├── farmer.ts
│   ├── buyer.ts
│   ├── supplier.ts
│   ├── transport.ts
│   ├── expert.ts
│   ├── financial.ts
│   ├── admin.ts
│   ├── marketplace.ts
│   ├── notifications.ts
│   └── config.ts
├── stores/           # Pinia state management
│   ├── authStore.ts
│   ├── farmerStore.ts
│   ├── buyerStore.ts
│   └── marketplaceStore.ts
├── views/            # Page components by role
│   ├── auth/
│   ├── farmer/
│   ├── buyer/
│   ├── supplier/
│   ├── transport/
│   ├── expert/
│   ├── financial/
│   └── admin/
├── components/       # Reusable components
│   ├── NavMenu.vue
│   ├── NavItem.vue
│   ├── NotificationBell.vue
│   ├── StatCard.vue
│   └── ...
├── layouts/          # Layout components
│   ├── AuthLayout.vue
│   └── MainLayout.vue
├── router/           # Vue Router configuration
│   └── index.ts
├── styles/           # Global styles
│   └── main.css
└── main.ts          # Application entry point
```

## Installation

### Prerequisites
- Node.js 16+ and npm/yarn
- Backend API running on `http://localhost:8000`

### Setup

1. **Clone the repository**
```bash
cd frontend
```

2. **Install dependencies**
```bash
npm install
```

3. **Create environment file**
```bash
cp .env.example .env.local
```

4. **Configure API URL** (if needed)
Edit `.env.local`:
```
VITE_API_URL=http://localhost:8000/api
```

5. **Start development server**
```bash
npm run dev
```

The application will be available at `http://localhost:5173`

## Available Scripts

### Development
```bash
npm run dev        # Start development server
```

### Build
```bash
npm run build      # Build for production
npm run preview    # Preview production build locally
```

### Code Quality
```bash
npm run type-check # Run TypeScript type checking
npm run lint       # Lint and fix code
```

## Authentication Flow

1. Visitor lands on homepage
2. User registers or logs in
3. System validates credentials and returns JWT token
4. Token stored in localStorage
5. Token included in all subsequent API requests
6. User redirected to role-appropriate dashboard
7. Token validated on app initialization

## API Integration

### Authentication
```typescript
import { authAPI } from '@/api/auth'

// Login
const response = await authAPI.login({ email, password })

// Register
const response = await authAPI.register({ ...userData })

// Profile
const profile = await authAPI.profile()
```

### State Management with Pinia
```typescript
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
await authStore.login(credentials)
const user = authStore.user
const isAuthenticated = authStore.isAuthenticated
```

## Role-Based Routing

Routes are protected by role middleware in the router. Each role has:
- Specific dashboard at `/app/{role}/dashboard`
- Role-specific modules and views
- Redirects for unauthorized access

### Role Routes
- **Farmer**: `/app/farmer/*`
- **Buyer**: `/app/buyer/*`
- **Supplier**: `/app/supplier/*`
- **Transport**: `/app/transport/*`
- **Expert**: `/app/expert/*`
- **Financial**: `/app/financial/*`
- **Admin**: `/app/admin/*`

## Components

### Reusable Components
- `NavMenu.vue` - Navigation menu by role
- `NavItem.vue` - Navigation item link
- `NotificationBell.vue` - Real-time notifications
- `StatCard.vue` - Dashboard statistics card
- `FeatureCard.vue` - Feature showcase card
- `BenefitItem.vue` - Benefits list item

### Layout Components
- `AuthLayout.vue` - Authentication page layout
- `MainLayout.vue` - Main application layout with sidebar

## Styling

The project uses Tailwind CSS with custom configurations:

### Custom Classes
- `.btn-primary` - Primary action button
- `.btn-secondary` - Secondary action button
- `.btn-danger` - Destructive action button
- `.input-field` - Standard input styling
- `.card` - Card container
- `.card-hover` - Card with hover effect

### Color Scheme
- Primary: Green (agricultural theme)
- Secondary: Purple
- Accent: Blue

## State Management

### Auth Store
```typescript
const authStore = useAuthStore()

// State
authStore.user              // Current user
authStore.token             // JWT token
authStore.isAuthenticated   // Boolean
authStore.userRole          // User's role

// Actions
authStore.login(credentials)
authStore.register(userData)
authStore.logout()
authStore.updateProfile(data)
```

### Farmer Store
```typescript
const farmerStore = useFarmerStore()

// State
farmerStore.farms
farmerStore.crops
farmerStore.products
farmerStore.orders

// Actions
farmerStore.fetchFarms()
farmerStore.createFarm(data)
farmerStore.fetchProducts()
```

### Buyer Store
```typescript
const buyerStore = useBuyerStore()

// State
buyerStore.cart
buyerStore.orders
buyerStore.wishlist

// Actions
buyerStore.addToCart(data)
buyerStore.checkout(data)
buyerStore.fetchOrders()
```

### Marketplace Store
```typescript
const marketplaceStore = useMarketplaceStore()

// State
marketplaceStore.products
marketplaceStore.categories
marketplaceStore.marketPrices

// Actions
marketplaceStore.fetchProducts()
marketplaceStore.searchProducts(query)
```

## API Error Handling

The Axios instance automatically handles:
- 401 errors → Logout and redirect to login
- Response status codes
- Token refresh (when implemented)

Example:
```typescript
try {
  const result = await farmerAPI.getFarms()
} catch (error) {
  const message = error.response?.data?.message || 'Error occurred'
  console.error(message)
}
```

## Deployment

### Build for Production
```bash
npm run build
```

### Deploy
The `dist` folder contains the production build. Deploy using:
- Vercel
- Netlify
- Traditional web server (nginx, Apache)

### Environment Configuration
Update `.env.local` for production:
```
VITE_API_URL=https://api.agriconnect.com/api
```

## Browser Support

- Chrome/Edge: Latest 2 versions
- Firefox: Latest 2 versions
- Safari: Latest 2 versions
- Mobile browsers: Latest versions

## Performance

### Optimization Features
- Code splitting via Vue Router
- Lazy-loaded components
- Image optimization
- CSS purging with Tailwind
- Tree-shaking of unused code

### Best Practices
- Reactive data with Vue 3 Composition API
- Efficient state management with Pinia
- Debounced search operations
- Pagination for large lists

## Security

### Implemented
- JWT token-based authentication
- Secure headers via API config
- XSS protection (Vue's built-in)
- CSRF protection (Laravel Sanctum)
- Password hashing on backend
- Role-based access control

### Recommendations
- Always use HTTPS in production
- Implement rate limiting on backend
- Sanitize user input
- Regular security audits

## Troubleshooting

### Common Issues

**API Connection Error**
- Ensure backend is running on port 8000
- Check `VITE_API_URL` in `.env.local`
- Verify CORS configuration on backend

**Login Issues**
- Clear localStorage and reload
- Check network tab for API errors
- Verify credentials in backend database

**Build Errors**
- Delete `node_modules` and run `npm install`
- Check Node.js version compatibility
- Review TypeScript errors

## Contributing

1. Create feature branch: `git checkout -b feature/feature-name`
2. Make changes and test
3. Commit: `git commit -m "Add feature"`
4. Push: `git push origin feature/feature-name`
5. Submit pull request

## License

Copyright © 2026 AgriConnect. All rights reserved.

## Support

For support and inquiries:
- Email: support@agriconnect.com
- Documentation: https://docs.agriconnect.com
- Issues: GitHub Issues

## Roadmap

### Planned Features
- Native mobile apps (iOS & Android)
- IoT sensor integration
- AI-powered crop disease detection
- Advanced GIS mapping
- SMS notifications
- Offline mode

### Future Enhancements
- Multi-language support
- Video consultations
- Blockchain for traceability
- Supply chain analytics
- Predictive yield modeling

---

Made with ❤️ for agricultural transformation
