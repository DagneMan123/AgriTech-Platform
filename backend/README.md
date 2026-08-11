# AgriTech Platform - Backend API

A comprehensive Laravel-based REST API for the AgriTech agricultural marketplace platform supporting 8 user roles with role-based access control, marketplace functionality, consulting services, loan management, and delivery logistics.

## Features

### Core Functionality
- **Multi-role user system**: Admin, Farmer, Buyer, Supplier, Transport Provider, Cooperative, Expert, Financial Institution
- **Role-based access control (RBAC)** with granular permissions
- **RESTful API** with comprehensive endpoints
- **JWT/Sanctum authentication** with token management
- **Real-time notifications** via email and SMS

### Marketplace
- Product listing and management with images
- Shopping cart and order management
- Order tracking and status management
- Product reviews and ratings
- Wishlist functionality

### Farm Management
- Farm profiles and documentation
- Crop tracking and monitoring
- Harvest recording
- Farm equipment and worker management
- Activity logging

### Expert Consultation
- Farmer-Expert consultation system
- Training materials and resources
- Message-based communication
- Consultation status tracking

### Financial Services
- Loan application and management
- Insurance policy management
- Loan repayment tracking
- Financial reporting and analytics

### Logistics & Delivery
- Delivery request and tracking
- Route management
- Vehicle management
- Real-time delivery tracking

### Additional Features
- Weather forecasting integration
- Market price tracking
- Comprehensive reporting system
- Activity logging and audit trails
- Multi-language support

## Tech Stack

- **Framework**: Laravel 11.x
- **Database**: PostgreSQL
- **Authentication**: Laravel Sanctum
- **API Documentation**: OpenAPI/Swagger ready
- **Queue System**: Laravel Queue (Sync/Redis)
- **Caching**: Redis/File cache

## System Requirements

- PHP 8.2+
- Composer
- PostgreSQL 12+
- Redis (optional)
- Node.js 16+ (for frontend)

## Installation

### 1. Clone the repository
```bash
git clone <repository-url>
cd backend
```

### 2. Install dependencies
```bash
composer install
```

### 3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure database
Edit `.env` file with your PostgreSQL credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agritech_db
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 5. Run migrations
```bash
php artisan migrate
```

### 6. Seed the database
```bash
php artisan db:seed
```

This will create:
- 8 roles (Admin, Farmer, Buyer, Supplier, Transport, Cooperative, Expert, Financial)
- All permissions with role assignments
- Sample users for testing
- Product categories
- 50 demo products
- 20 demo orders

### 7. Start the application
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

### Public Endpoints
- `POST /register` - User registration
- `POST /login` - User login
- `GET /products` - List products
- `GET /products/{id}` - Get product details
- `GET /categories` - Get product categories
- `GET /weather` - Get weather data
- `GET /market-prices` - Get market prices

### Protected Endpoints

#### Authentication
- `POST /logout` - Logout user
- `GET /me` - Get current user profile
- `POST /profile/update` - Update profile
- `POST /password/change` - Change password

#### Farmer Routes
- `GET/POST /farms` - Manage farms
- `GET/POST /crops` - Manage crops
- `POST /crops/{crop}/harvest` - Record harvest
- `GET/POST /products` - Manage products
- `GET /my-orders` - View farmer's orders
- `POST /orders/{order}/accept` - Accept order
- `POST /orders/{order}/reject` - Reject order

#### Buyer Routes
- `POST /orders` - Create order
- `GET /my-orders` - View buyer's orders
- `POST /cart/add` - Add to cart
- `GET /cart` - View cart
- `POST /products/{product}/review` - Add product review

#### Supplier Routes
- `GET/POST /supplier/products` - Manage supplier products
- `GET /supplier/orders` - View orders
- `GET /supplier/inventory` - View inventory
- `GET /supplier/warehouse` - View warehouse

#### Transport Routes
- `GET /deliveries` - View available deliveries
- `POST /deliveries/{delivery}/accept` - Accept delivery
- `POST /deliveries/{delivery}/track` - Update tracking
- `GET/POST /vehicles` - Manage vehicles

#### Expert Routes
- `GET /consultations` - View consultations
- `POST /consultations/{consultation}/respond` - Respond to consultation
- `GET/POST /training-materials` - Manage training materials

#### Financial Institution Routes
- `GET /loan-applications` - View loan applications
- `POST /loan-applications/{application}/approve` - Approve loan
- `POST /loan-applications/{application}/reject` - Reject loan

#### Admin Routes
- `GET/POST /admin/users` - Manage users
- `POST /admin/users/{user}/suspend` - Suspend user
- `POST /admin/users/{user}/activate` - Activate user
- `GET /admin/orders` - View all orders
- `GET /admin/payments` - View all payments
- `GET /admin/statistics` - Get system statistics

## Database Schema

### Core Tables
- `users` - User accounts with role assignment
- `roles` - User roles
- `permissions` - System permissions
- `permission_role` - Role-permission mapping

### Marketplace
- `categories` - Product categories
- `products` - Products listing
- `product_images` - Product images
- `product_reviews` - Product reviews
- `shopping_carts` - Shopping carts
- `cart_items` - Cart items
- `wishlists` - Wishlist items

### Orders & Payments
- `orders` - Customer orders
- `order_items` - Order line items
- `order_status_logs` - Order status history
- `payments` - Payment records
- `payment_methods` - Payment methods
- `invoices` - Invoices
- `transactions` - Financial transactions
- `refunds` - Refund records

### Farm Management
- `farmers` - Farmer profiles
- `farms` - Farm information
- `farm_images` - Farm photos
- `farm_documents` - Farm documents
- `farm_equipment` - Farm equipment
- `farm_workers` - Farm workers
- `farm_activities` - Farm activities log
- `crops` - Crop records
- `crop_categories` - Crop types
- `crop_images` - Crop photos
- `crop_growth_records` - Growth tracking
- `harvests` - Harvest records

### Services & Consultations
- `consultations` - Consultation records
- `consultation_messages` - Consultation messages
- `training_materials` - Expert training materials
- `loans` - Loan records
- `loan_applications` - Loan applications
- `loan_repayments` - Loan repayments
- `insurance_policies` - Insurance policies

### Logistics
- `deliveries` - Delivery orders
- `delivery_tracking` - Delivery tracking updates
- `delivery_routes` - Delivery routes
- `vehicles` - Transport vehicles

### Other
- `cooperatives` - Cooperative information
- `conversations` - User conversations
- `messages` - Direct messages
- `weather_forecasts` - Weather data
- `market_prices` - Market prices
- `reports` - Generated reports
- `activity_logs` - System activity logs
- `notifications` - Notification records

## Console Commands

### Fetch Weather Data
```bash
php artisan agritech:fetch-weather
```

### Update Order Status
```bash
php artisan agritech:update-orders
```

## Testing

Run tests with:
```bash
php artisan test
```

Run specific test file:
```bash
php artisan test tests/Feature/AuthTest.php
```

Run with coverage:
```bash
php artisan test --coverage
```

### Test Files
- `tests/Feature/AuthTest.php` - Authentication tests
- `tests/Feature/ProductTest.php` - Product management tests
- `tests/Feature/OrderTest.php` - Order management tests
- `tests/Feature/ApiTest.php` - General API tests

## Queue Jobs

Jobs are processed by Laravel's queue system:

- `ProcessPayment` - Process payment transactions
- `SendSmsNotification` - Send SMS notifications

### Running the queue worker
```bash
php artisan queue:work
```

## File Structure

```
backend/
├── app/
│   ├── Console/Commands/       # Artisan commands
│   ├── Events/                 # Event classes
│   ├── Http/
│   │   ├── Controllers/Api/    # API controllers
│   │   ├── Middleware/         # Custom middleware
│   │   ├── Requests/           # Form requests
│   │   └── Resources/          # API resources
│   ├── Jobs/                   # Queued jobs
│   ├── Listeners/              # Event listeners
│   ├── Mail/                   # Mailable classes
│   ├── Models/                 # Eloquent models
│   ├── Notifications/          # Notification classes
│   ├── Policies/               # Authorization policies
│   ├── Services/               # Business logic services
│   └── Traits/                 # Reusable traits
├── config/                     # Configuration files
├── database/
│   ├── factories/              # Model factories
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── routes/
│   └── api.php                 # API routes
├── storage/                    # File storage
├── tests/                      # Test files
├── .env.example                # Environment template
└── composer.json               # Project dependencies
```

## Configuration

### Environment Variables

Key environment variables in `.env`:

```env
# App
APP_NAME=AgriTech
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=agritech_db

# Mail
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io

# SMS Service
SMS_PROVIDER=nexmo
NEXMO_KEY=...
NEXMO_SECRET=...

# Payment
PAYMENT_PROVIDER=stripe
STRIPE_PUBLIC_KEY=...
STRIPE_SECRET_KEY=...

# Weather
WEATHER_PROVIDER=openweathermap
OPENWEATHERMAP_API_KEY=...

# Maps
MAP_PROVIDER=google
GOOGLE_MAPS_API_KEY=...
```

## Security Considerations

- All passwords are hashed using bcrypt
- API uses Sanctum for token-based authentication
- CORS is configured for frontend communication
- Rate limiting is enabled on API endpoints
- SQL injection protection via parameterized queries
- CSRF protection on form submissions
- Role-based access control (RBAC) on all protected routes

## Common Issues

### Database connection error
- Ensure PostgreSQL is running
- Verify credentials in `.env`
- Run: `php artisan migrate`

### Key generation error
- Run: `php artisan key:generate`

### Permission denied errors
- Ensure `storage/` and `bootstrap/cache/` are writable
- Run: `chmod -R 775 storage bootstrap/cache`

### Missing dependencies
- Run: `composer install`
- Run: `composer update`

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License.

## Support

For issues and questions, please open an issue on the repository or contact the development team.

## Roadmap

- [ ] GraphQL API alternative
- [ ] Advanced analytics dashboard
- [ ] Mobile app integration
- [ ] AI-powered crop recommendations
- [ ] Blockchain-based supply chain tracking
- [ ] IoT sensor integration for weather monitoring
