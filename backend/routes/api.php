<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

// Admin Controllers
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\Admin\SettingController;

// Farmer Controllers
use App\Http\Controllers\Api\Farmer\FarmController;
use App\Http\Controllers\Api\Farmer\CropController;
use App\Http\Controllers\Api\Farmer\CropDebugController;
use App\Http\Controllers\Api\Farmer\HarvestController;
use App\Http\Controllers\Api\Farmer\ProductController as FarmerProductController;
use App\Http\Controllers\Api\Farmer\OrderController as FarmerOrderController;
use App\Http\Controllers\Api\Farmer\DashboardController as FarmerDashboardController;
use App\Http\Controllers\Api\Farmer\WeatherController as FarmerWeatherController;
use App\Http\Controllers\Api\Farmer\LoanController as FarmerLoanController;
use App\Http\Controllers\Api\Farmer\ConsultationController as FarmerConsultationController;
use App\Http\Controllers\Api\Farmer\TransportController as FarmerTransportController;

// Buyer Controllers
use App\Http\Controllers\Api\Buyer\BuyerController;
use App\Http\Controllers\Api\Buyer\CartController;
use App\Http\Controllers\Api\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Api\Buyer\PaymentController as BuyerPaymentController;
use App\Http\Controllers\Api\Buyer\ReviewController;
use App\Http\Controllers\Api\Buyer\WishlistController;
use App\Http\Controllers\Api\Buyer\MarketplaceController;
use App\Http\Controllers\Api\Buyer\DashboardController as BuyerDashboardController;

// Supplier Controllers
use App\Http\Controllers\Api\Supplier\ProductController as SupplierProductController;
use App\Http\Controllers\Api\Supplier\InventoryController;
use App\Http\Controllers\Api\Supplier\WarehouseController;
use App\Http\Controllers\Api\Supplier\LicenseController;
use App\Http\Controllers\Api\Supplier\OrderController as SupplierOrderController;
use App\Http\Controllers\Api\Supplier\DashboardController as SupplierDashboardController;

// Transport Controllers
use App\Http\Controllers\Api\Transport\VehicleController;
use App\Http\Controllers\Api\Transport\DeliveryController;
use App\Http\Controllers\Api\Transport\TrackingController;
use App\Http\Controllers\Api\Transport\DashboardController as TransportDashboardController;

// Cooperative Controllers
use App\Http\Controllers\Api\Cooperative\CooperativeController;
use App\Http\Controllers\Api\Cooperative\MemberController;
use App\Http\Controllers\Api\Cooperative\SalesController;
use App\Http\Controllers\Api\Cooperative\DashboardController as CooperativeDashboardController;

// Expert Controllers
use App\Http\Controllers\Api\Expert\ExpertController;
use App\Http\Controllers\Api\Expert\ConsultationController;
use App\Http\Controllers\Api\Expert\TrainingController;
use App\Http\Controllers\Api\Expert\ArticleController;
use App\Http\Controllers\Api\Expert\DashboardController as ExpertDashboardController;

// Financial Controllers
use App\Http\Controllers\Api\Financial\LoanController;
use App\Http\Controllers\Api\Financial\InsuranceController;
use App\Http\Controllers\Api\Financial\PaymentController;
use App\Http\Controllers\Api\Financial\TransactionController;
use App\Http\Controllers\Api\Financial\DashboardController as FinancialDashboardController;

// Marketplace Controllers
use App\Http\Controllers\Api\Market\MarketPriceController;
use App\Http\Controllers\Api\Location\MapController;
use App\Http\Controllers\Api\Marketplace\CategoryController;

// Report & Notification Controllers
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Report\SalesReportController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\RepairController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Health check endpoints
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'time' => now()]);
});

Route::get('/test', function () {
    return response()->json(['message' => 'Test endpoint working', 'time' => now()]);
});

// Diagnostic endpoints (for debugging)
Route::get('/diagnostic/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'environment' => config('app.env'),
        'debug' => config('app.debug'),
        'time' => now(),
    ]);
});

Route::get('/diagnostic/routes', function () {
    $routes = collect(Route::getRoutes())->filter(function ($route) {
        return strpos($route->uri(), 'farmer/dashboard') !== false;
    })->map(function ($route) {
        return [
            'method' => implode('|', $route->methods),
            'path' => $route->uri(),
            'action' => $route->action['controller'] ?? 'Closure',
        ];
    })->values();

    return response()->json([
        'farmer_dashboard_routes' => $routes,
        'message' => 'Farmer dashboard routes registered'
    ]);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/diagnostic/auth', [\App\Http\Controllers\Api\DiagnosticController::class, 'auth']);
    Route::get('/diagnostic/farms-count', [\App\Http\Controllers\Api\DiagnosticController::class, 'farmsCount']);
    Route::get('/diagnostic/dashboard-summary', [\App\Http\Controllers\Api\DiagnosticController::class, 'dashboardSummary']);
});

// Authentication Routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Marketplace Public Routes
Route::get('/marketplace/products', [MarketplaceController::class, 'index']);
Route::get('/marketplace/products/{id}', [MarketplaceController::class, 'show']);
Route::get('/marketplace/categories', [CategoryController::class, 'index']);

// Weather & Market Info - Public
Route::get('/weather', [FarmerWeatherController::class, 'index']);
Route::get('/market-prices', [MarketPriceController::class, 'index']);

// Experts - Public
Route::get('/experts', [ExpertController::class, 'index']);

// Location - Public
Route::get('/locations', [MapController::class, 'index']);

// Database Repair Routes (Development)
Route::get('/repair/crops-table', [RepairController::class, 'fixCropsTable']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Authentication Required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api'])->group(function () {

    /*
    |----------------------------------------------------------------------
    | AUTHENTICATION ROUTES
    |----------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/profile/update', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    /*
    |----------------------------------------------------------------------
    | ADMIN ROUTES (Admin Role Required)
    |----------------------------------------------------------------------
    */
    Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
        // Dashboard Overview
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        
        // User Management
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::get('/users/{id}', [AdminUserController::class, 'show']);
        Route::put('/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
        Route::post('/users/{id}/status', [AdminDashboardController::class, 'updateUserStatus']);
        Route::get('/users-management', [AdminDashboardController::class, 'userManagement']);
        
        // Role & Permission Management
        Route::get('/role-permission-management', [AdminDashboardController::class, 'rolePermissionManagement']);
        Route::apiResource('/roles', RoleController::class);
        Route::apiResource('/permissions', PermissionController::class);
        
        // Marketplace Management
        Route::get('/marketplace-monitoring', [AdminDashboardController::class, 'marketplaceMonitoring']);
        
        // Order Management
        Route::get('/order-monitoring', [AdminDashboardController::class, 'orderMonitoring']);
        
        // Delivery Monitoring
        Route::get('/delivery-monitoring', [AdminDashboardController::class, 'deliveryMonitoring']);
        
        // Payment Monitoring
        Route::get('/payment-monitoring', [AdminDashboardController::class, 'paymentMonitoring']);
        
        // Reports & Analytics
        Route::get('/reports-analytics', [AdminDashboardController::class, 'reportsAnalytics']);
        Route::get('/reports', [ReportController::class, 'index']);
        
        // System Settings
        Route::get('/settings', [AdminDashboardController::class, 'systemSettings']);
        Route::post('/settings', [AdminDashboardController::class, 'updateSystemSettings']);
        
        // Announcements
        Route::post('/announcements', [AdminDashboardController::class, 'sendAnnouncement']);
        
        // Notifications
        Route::get('/notifications-management', [AdminDashboardController::class, 'notificationManagement']);
        
        // Activity Logs
        Route::get('/activity-logs', [AdminDashboardController::class, 'activityLogs']);
        
        // System Statistics
        Route::get('/stats', [AdminDashboardController::class, 'getSystemStats']);
    });

    /*
    |----------------------------------------------------------------------
    | FARMER ROUTES (Farmer Role Required)
    |----------------------------------------------------------------------
    */
    Route::middleware(['auth:api', 'role:farmer'])->prefix('farmer')->group(function () {
        // Dashboard Overview
        Route::get('/dashboard', [FarmerDashboardController::class, 'index']);
        
        // Debug routes (remove in production)
        Route::post('/crops-debug', [CropDebugController::class, 'debugCreate']);
        Route::get('/farms-debug', [CropDebugController::class, 'debugFarms']);
        
        // Farm Management
        Route::get('/dashboard/farm-management', [FarmerDashboardController::class, 'farmManagement']);
        Route::apiResource('/farms', FarmController::class);
        Route::get('/farms/{farm}/images', [FarmController::class, 'getImages']);
        Route::post('/farms/{farm}/upload-images', [FarmController::class, 'uploadImages']);
        
        // Crop Management
        Route::get('/dashboard/crop-management', [FarmerDashboardController::class, 'cropManagement']);
        Route::apiResource('/crops', CropController::class);
        
        // Harvest Management
        Route::get('/dashboard/harvest-management', [FarmerDashboardController::class, 'harvestManagement']);
        Route::apiResource('/harvests', HarvestController::class);
        
        // Product Management
        Route::get('/dashboard/product-management', [FarmerDashboardController::class, 'productManagement']);
        Route::apiResource('/products', FarmerProductController::class);
        Route::post('/products/{product}/upload-images', [FarmerProductController::class, 'uploadImages']);
        
        // Customer Orders
        Route::get('/dashboard/orders', [FarmerDashboardController::class, 'customerOrders']);
        Route::get('/orders', [FarmerOrderController::class, 'index']);
        Route::get('/orders/{id}', [FarmerOrderController::class, 'show']);
        Route::post('/orders/{id}/accept', [FarmerOrderController::class, 'accept']);
        Route::post('/orders/{id}/reject', [FarmerOrderController::class, 'reject']);
        
        // Agricultural Inputs - Buy/Request
        Route::get('/dashboard/agricultural-inputs', [FarmerDashboardController::class, 'requestInputs']);
        
        // Transportation Requests
        Route::get('/dashboard/transport-requests', [FarmerDashboardController::class, 'transportRequests']);
        Route::apiResource('/transport-requests', FarmerTransportController::class);
        
        // Weather Forecast
        Route::get('/dashboard/weather-forecast', [FarmerDashboardController::class, 'weatherForecast']);
        
        // Market Prices
        Route::get('/dashboard/market-prices', [FarmerDashboardController::class, 'marketPrices']);
        
        // Consultations
        Route::get('/dashboard/consultations', [FarmerDashboardController::class, 'consultationRequests']);
        Route::get('/consultations', [FarmerConsultationController::class, 'index']);
        Route::post('/consultations', [FarmerConsultationController::class, 'store']);
        Route::get('/consultations/{id}', [FarmerConsultationController::class, 'show']);
        Route::put('/consultations/{id}', [FarmerConsultationController::class, 'update']);
        Route::delete('/consultations/{id}', [FarmerConsultationController::class, 'destroy']);
        
        // Loan Applications
        Route::get('/dashboard/loan-applications', [FarmerDashboardController::class, 'loanApplications']);
        Route::apiResource('/loans', FarmerLoanController::class);
        
        // Sales Reports
        Route::get('/dashboard/sales-reports', [FarmerDashboardController::class, 'salesReports']);
        Route::get('/dashboard/sales-reports', [SalesReportController::class, 'index']);
        Route::get('/dashboard/performance-metrics', [SalesReportController::class, 'performanceMetrics']);
        Route::get('/dashboard/export-report', [SalesReportController::class, 'export']);
        Route::get('/reports/top-products', [SalesReportController::class, 'topProducts']);
        Route::get('/reports/revenue-by-category', [SalesReportController::class, 'revenueByCategory']);
    });

    /*
    |----------------------------------------------------------------------
    | BUYER ROUTES (Buyer Role Required)
    |----------------------------------------------------------------------
    */
    Route::middleware(['auth:api', 'role:buyer'])->prefix('buyer')->group(function () {
        // Dashboard
        Route::get('/dashboard', [BuyerDashboardController::class, 'index']);
        Route::get('/dashboard/order-history', [BuyerDashboardController::class, 'orderHistory']);
        Route::get('/dashboard/cart-summary', [BuyerDashboardController::class, 'cartSummary']);
        Route::get('/dashboard/delivery-tracking', [BuyerDashboardController::class, 'deliveryTracking']);
        Route::get('/dashboard/payment-history', [BuyerDashboardController::class, 'paymentHistory']);
        Route::get('/dashboard/wishlist', [BuyerDashboardController::class, 'wishlist']);
        Route::get('/dashboard/purchase-analytics', [BuyerDashboardController::class, 'purchaseAnalytics']);
        
        // Profile
        Route::get('/profile', [BuyerController::class, 'profile']);
        Route::post('/profile', [BuyerController::class, 'updateProfile']);
        
        // Shopping Cart
        Route::apiResource('/cart', CartController::class);
        Route::post('/cart/checkout', [CartController::class, 'checkout']);
        
        // Orders
        Route::apiResource('/orders', BuyerOrderController::class);
        Route::post('/orders/{id}/cancel', [BuyerOrderController::class, 'cancel']);
        
        // Payments
        Route::apiResource('/payments', BuyerPaymentController::class);
        Route::post('/payments/{id}/verify', [BuyerPaymentController::class, 'verify']);
        
        // Reviews
        Route::apiResource('/reviews', ReviewController::class);
        
        // Wishlist
        Route::apiResource('/wishlist', WishlistController::class);
        
        // Marketplace
        Route::get('/marketplace', [MarketplaceController::class, 'index']);
        Route::get('/marketplace/search', [MarketplaceController::class, 'search']);
    });

    /*
    |----------------------------------------------------------------------
    | SUPPLIER ROUTES (Supplier Role Required)
    |----------------------------------------------------------------------
    */
    Route::middleware(['auth:api', 'role:supplier'])->prefix('supplier')->group(function () {
        // Dashboard
        Route::get('/dashboard', [SupplierDashboardController::class, 'index']);
        Route::get('/dashboard/inventory', [SupplierDashboardController::class, 'inventory']);
        Route::get('/dashboard/warehouses', [SupplierDashboardController::class, 'warehouses']);
        Route::get('/dashboard/sales-analytics', [SupplierDashboardController::class, 'salesAnalytics']);
        Route::get('/dashboard/license-status', [SupplierDashboardController::class, 'licenseStatus']);
        Route::get('/dashboard/deliveries', [SupplierDashboardController::class, 'deliveries']);
        
        // Products
        Route::apiResource('/products', SupplierProductController::class);
        Route::post('/products/{id}/upload-images', [SupplierProductController::class, 'uploadImages']);
        
        // Inventory
        Route::apiResource('/inventory', InventoryController::class);
        
        // Warehouses
        Route::apiResource('/warehouses', WarehouseController::class);
        
        // License
        Route::post('/license/apply', [LicenseController::class, 'apply']);
        Route::get('/license', [LicenseController::class, 'show']);
        
        // Orders
        Route::apiResource('/orders', SupplierOrderController::class);
        Route::post('/orders/{id}/process', [SupplierOrderController::class, 'process']);
    });

    /*
    |----------------------------------------------------------------------
    | TRANSPORT ROUTES (Transport Role Required)
    |----------------------------------------------------------------------
    */
    Route::middleware(['auth:api', 'role:transport'])->prefix('transport')->group(function () {
        // Dashboard
        Route::get('/dashboard', [TransportDashboardController::class, 'index']);
        Route::get('/dashboard/delivery-requests', [TransportDashboardController::class, 'deliveryRequests']);
        Route::get('/dashboard/vehicles', [TransportDashboardController::class, 'vehicles']);
        Route::get('/dashboard/active-deliveries', [TransportDashboardController::class, 'activeDeliveries']);
        Route::get('/dashboard/analytics', [TransportDashboardController::class, 'analytics']);
        Route::get('/dashboard/history', [TransportDashboardController::class, 'history']);
        
        // Vehicles
        Route::apiResource('/vehicles', VehicleController::class);
        
        // Deliveries
        Route::apiResource('/deliveries', DeliveryController::class);
        Route::post('/deliveries/{id}/accept', [DeliveryController::class, 'accept']);
        Route::post('/deliveries/{id}/complete', [DeliveryController::class, 'complete']);
        
        // Tracking
        Route::post('/tracking', [TrackingController::class, 'store']);
        Route::get('/tracking/{delivery_id}', [TrackingController::class, 'show']);
    });

    /*
    |----------------------------------------------------------------------
    | EXPERT ROUTES (Expert Role Required)
    |----------------------------------------------------------------------
    */
    Route::middleware(['auth:api', 'role:expert'])->prefix('expert')->group(function () {
        // Dashboard
        Route::get('/dashboard', [ExpertDashboardController::class, 'index']);
        Route::get('/dashboard/consultations', [ExpertDashboardController::class, 'consultations']);
        Route::get('/dashboard/training-materials', [ExpertDashboardController::class, 'trainingMaterials']);
        Route::get('/dashboard/articles', [ExpertDashboardController::class, 'articles']);
        Route::get('/dashboard/engagement-analytics', [ExpertDashboardController::class, 'engagementAnalytics']);
        Route::get('/dashboard/farmer-reach', [ExpertDashboardController::class, 'farmerReach']);
        Route::get('/dashboard/consultation/{id}', [ExpertDashboardController::class, 'consultationDetails']);
        
        // Profile
        Route::get('/profile', [ExpertController::class, 'profile']);
        Route::post('/profile', [ExpertController::class, 'updateProfile']);
        
        // Consultations
        Route::apiResource('/consultations', ConsultationController::class);
        Route::post('/consultations/{id}/reply', [ConsultationController::class, 'reply']);
        
        // Training
        Route::apiResource('/training', TrainingController::class);
        Route::post('/training/{id}/publish', [TrainingController::class, 'publish']);
        
        // Articles
        Route::apiResource('/articles', ArticleController::class);
        Route::post('/articles/{id}/publish', [ArticleController::class, 'publish']);
    });

    /*
    |----------------------------------------------------------------------
    | FINANCIAL ROUTES (Financial Role Required)
    |----------------------------------------------------------------------
    */
    Route::middleware(['auth:api', 'role:financial'])->prefix('financial')->group(function () {
        // Dashboard
        Route::get('/dashboard', [FinancialDashboardController::class, 'index']);
        Route::get('/dashboard/pending-applications', [FinancialDashboardController::class, 'pendingApplications']);
        Route::get('/dashboard/loans', [FinancialDashboardController::class, 'loans']);
        Route::get('/dashboard/insurances', [FinancialDashboardController::class, 'insurances']);
        Route::get('/dashboard/repayments', [FinancialDashboardController::class, 'repayments']);
        Route::get('/dashboard/transactions', [FinancialDashboardController::class, 'transactions']);
        Route::get('/dashboard/portfolio-analytics', [FinancialDashboardController::class, 'portfolioAnalytics']);
        Route::get('/dashboard/loan/{id}', [FinancialDashboardController::class, 'loanDetails']);
        Route::get('/dashboard/risk-assessment', [FinancialDashboardController::class, 'riskAssessment']);
        
        // Loans
        Route::apiResource('/loans', LoanController::class);
        Route::post('/loans/{id}/approve', [LoanController::class, 'approve']);
        Route::post('/loans/{id}/reject', [LoanController::class, 'reject']);
        
        // Insurance
        Route::apiResource('/insurance', InsuranceController::class);
        Route::post('/insurance/{id}/approve', [InsuranceController::class, 'approve']);
        
        // Payments
        Route::apiResource('/payments', PaymentController::class);
        Route::post('/payments/{id}/verify', [PaymentController::class, 'verify']);
        
        // Transactions
        Route::apiResource('/transactions', TransactionController::class);
    });

    /*
    |----------------------------------------------------------------------
    | COOPERATIVE ROUTES (Cooperative Role Required)
    |----------------------------------------------------------------------
    */
    Route::middleware(['auth:api', 'role:cooperative'])->prefix('cooperative')->group(function () {
        // Dashboard
        Route::get('/dashboard', [CooperativeDashboardController::class, 'index']);
        Route::get('/dashboard/members', [CooperativeDashboardController::class, 'members']);
        Route::get('/dashboard/bulk-purchasing', [CooperativeDashboardController::class, 'bulkPurchasing']);
        Route::get('/dashboard/bulk-sales', [CooperativeDashboardController::class, 'bulkSales']);
        Route::get('/dashboard/collection-centers', [CooperativeDashboardController::class, 'collectionCenters']);
        Route::get('/dashboard/financial-reports', [CooperativeDashboardController::class, 'financialReports']);
        Route::get('/dashboard/member-statistics', [CooperativeDashboardController::class, 'memberStatistics']);
        
        // Cooperative Info
        Route::apiResource('/', CooperativeController::class)->only(['show', 'update']);
        
        // Members
        Route::apiResource('/members', MemberController::class);
        
        // Sales
        Route::apiResource('/sales', SalesController::class);
    });

    /*
    |----------------------------------------------------------------------
    | NOTIFICATION ROUTES (All Authenticated Users)
    |----------------------------------------------------------------------
    */
    Route::apiResource('/notifications', NotificationController::class)->only(['index', 'show']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    /*
    |----------------------------------------------------------------------
    | COMMON ROUTES (All Authenticated Users)
    |----------------------------------------------------------------------
    */
    Route::get('/weather', [FarmerWeatherController::class, 'index']);
    Route::get('/market-prices', [MarketPriceController::class, 'index']);
    Route::get('/locations', [MapController::class, 'index']);
    Route::post('/locations/search', [MapController::class, 'search']);

});