<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\DocumentVerificationController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Farmer\FarmController;
use App\Http\Controllers\Api\Farmer\CropController;
use App\Http\Controllers\Api\Farmer\CropActivityController;
use App\Http\Controllers\Api\Farmer\CropDebugController;
use App\Http\Controllers\Api\Farmer\HarvestController;
use App\Http\Controllers\Api\Farmer\ProductController as FarmerProductController;
use App\Http\Controllers\Api\Farmer\OrderController as FarmerOrderController;
use App\Http\Controllers\Api\Farmer\DashboardController as FarmerDashboardController;
use App\Http\Controllers\Api\Farmer\WeatherController as FarmerWeatherController;
use App\Http\Controllers\Api\Farmer\LoanController as FarmerLoanController;
use App\Http\Controllers\Api\Farmer\ConsultationController as FarmerConsultationController;
use App\Http\Controllers\Api\Farmer\TransportController as FarmerTransportController;
use App\Http\Controllers\Api\Buyer\BuyerController;
use App\Http\Controllers\Api\Buyer\CartController;
use App\Http\Controllers\Api\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Api\Buyer\PaymentController as BuyerPaymentController;
use App\Http\Controllers\Api\Buyer\ReviewController;
use App\Http\Controllers\Api\Buyer\WishlistController;
use App\Http\Controllers\Api\Buyer\MarketplaceController;
use App\Http\Controllers\Api\Buyer\DashboardController as BuyerDashboardController;
use App\Http\Controllers\Api\Supplier\ProductController as SupplierProductController;
use App\Http\Controllers\Api\Supplier\InventoryController;
use App\Http\Controllers\Api\Supplier\WarehouseController;
use App\Http\Controllers\Api\Supplier\LicenseController;
use App\Http\Controllers\Api\Supplier\OrderController as SupplierOrderController;
use App\Http\Controllers\Api\Supplier\DashboardController as SupplierDashboardController;
use App\Http\Controllers\Api\Transport\VehicleController;
use App\Http\Controllers\Api\Transport\DeliveryController;
use App\Http\Controllers\Api\Transport\TrackingController;
use App\Http\Controllers\Api\Transport\DashboardController as TransportDashboardController;
use App\Http\Controllers\Api\Cooperative\CooperativeController;
use App\Http\Controllers\Api\Cooperative\MemberController;
use App\Http\Controllers\Api\Cooperative\SalesController;
use App\Http\Controllers\Api\Cooperative\DashboardController as CooperativeDashboardController;
use App\Http\Controllers\Api\Expert\ExpertController;
use App\Http\Controllers\Api\Expert\ConsultationController;
use App\Http\Controllers\Api\Expert\TrainingController;
use App\Http\Controllers\Api\Expert\ArticleController;
use App\Http\Controllers\Api\Expert\DashboardController as ExpertDashboardController;
use App\Http\Controllers\Api\Financial\LoanController;
use App\Http\Controllers\Api\Financial\InsuranceController;
use App\Http\Controllers\Api\Financial\PaymentController;
use App\Http\Controllers\Api\Financial\TransactionController;
use App\Http\Controllers\Api\Financial\DashboardController as FinancialDashboardController;
use App\Http\Controllers\Api\Market\MarketPriceController;
use App\Http\Controllers\Api\Location\MapController;
use App\Http\Controllers\Api\Marketplace\CategoryController;
use App\Http\Controllers\Api\Report\SalesReportController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\RepairController;

Route::get('/health', fn() => response()->json(['status' => 'ok', 'time' => now()]));

Route::get('/diagnostic/health', fn() => response()->json([
    'status' => 'ok',
    'app' => config('app.name'),
    'environment' => config('app.env'),
    'debug' => config('app.debug'),
    'time' => now(),
]));

Route::get('/test-auth', function(\Illuminate\Http\Request $request) {
    $token = $request->bearerToken();
    $user = auth('api')->user();
    
    return response()->json([
        'authenticated' => $user !== null,
        'user' => $user,
        'token_present' => $token ? true : false,
        'guard' => 'api',
        'timestamp' => now(),
    ]);
});

Route::get('/test-db', function() {
    try {
        $tables = \Illuminate\Support\Facades\DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = ?', [env('DB_DATABASE')]);
        $table_names = array_map(fn($t) => $t->table_name, $tables);
        
        $pat_exists = in_array('personal_access_tokens', $table_names);
        $pat_count = $pat_exists ? \App\Models\PersonalAccessToken::count() : 0;
        
        return response()->json([
            'database' => env('DB_DATABASE'),
            'personal_access_tokens_table_exists' => $pat_exists,
            'personal_access_tokens_count' => $pat_count,
            'all_tables' => $table_names,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::prefix('email')->group(function () {
    Route::get('/health', [App\Http\Controllers\Api\EmailDiagnosticController::class, 'health']);
    Route::get('/test-config', [App\Http\Controllers\Api\EmailDiagnosticController::class, 'testConfiguration']);
    Route::post('/test-send', [App\Http\Controllers\Api\EmailDiagnosticController::class, 'sendTestEmail']);
    Route::get('/test-providers', [App\Http\Controllers\Api\EmailDiagnosticController::class, 'testMultipleProviders']);
});

Route::prefix('marketplace')->group(function () {
    Route::get('/products', [MarketplaceController::class, 'index']);
    Route::get('/products/{id}', [MarketplaceController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
});

Route::get('/weather', [FarmerWeatherController::class, 'index']);
Route::get('/market-prices', [MarketPriceController::class, 'index']);
Route::get('/experts', [ExpertController::class, 'index']);
Route::get('/locations', [MapController::class, 'index']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware(['auth:api'])->group(function () {
    
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/profile/update', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::apiResource('/users', AdminUserController::class);
        Route::post('/users/{id}/status', [AdminDashboardController::class, 'updateUserStatus']);
        Route::get('/users-management', [AdminDashboardController::class, 'userManagement']);

        Route::prefix('documents')->group(function () {
            Route::get('/pending', [DocumentVerificationController::class, 'pending']);
            Route::get('/{id}', [DocumentVerificationController::class, 'show']);
            Route::post('/{id}/verify', [DocumentVerificationController::class, 'verify']);
            Route::post('/{id}/reject', [DocumentVerificationController::class, 'reject']);
            Route::get('/users/{id}', [DocumentVerificationController::class, 'userDocuments']);
        });

        Route::get('/role-permission-management', [AdminDashboardController::class, 'rolePermissionManagement']);
        Route::apiResource('/roles', RoleController::class);
        Route::apiResource('/permissions', PermissionController::class);

        Route::get('/marketplace-monitoring', [AdminDashboardController::class, 'marketplaceMonitoring']);
        Route::get('/order-monitoring', [AdminDashboardController::class, 'orderMonitoring']);
        Route::get('/delivery-monitoring', [AdminDashboardController::class, 'deliveryMonitoring']);
        Route::get('/payment-monitoring', [AdminDashboardController::class, 'paymentMonitoring']);
        Route::get('/reports-analytics', [AdminDashboardController::class, 'reportsAnalytics']);
        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/settings', [AdminDashboardController::class, 'systemSettings']);
        Route::post('/settings', [AdminDashboardController::class, 'updateSystemSettings']);
        Route::post('/announcements', [AdminDashboardController::class, 'sendAnnouncement']);
        Route::get('/notifications-management', [AdminDashboardController::class, 'notificationManagement']);
        Route::get('/activity-logs', [AdminDashboardController::class, 'activityLogs']);
        Route::get('/stats', [AdminDashboardController::class, 'getSystemStats']);
    });

    Route::middleware(['role:farmer'])->prefix('farmer')->group(function () {
        Route::get('/dashboard', [FarmerDashboardController::class, 'index']);
        Route::get('/dashboard/farm-management', [FarmerDashboardController::class, 'farmManagement']);
        Route::apiResource('/farms', FarmController::class);
        Route::get('/farms/{farm}/images', [FarmController::class, 'getImages']);
        Route::post('/farms/{farm}/upload-images', [FarmController::class, 'uploadImages']);

        Route::get('/dashboard/crop-management', [FarmerDashboardController::class, 'cropManagement']);
        Route::apiResource('/crops', CropController::class);
        Route::post('/crops-debug', [CropDebugController::class, 'debugCreate']);
        Route::get('/farms-debug', [CropDebugController::class, 'debugFarms']);

        Route::apiResource('/crop-activities', CropActivityController::class);
        Route::get('/dashboard/harvest-management', [FarmerDashboardController::class, 'harvestManagement']);
        Route::apiResource('/harvests', HarvestController::class);

        Route::get('/dashboard/product-management', [FarmerDashboardController::class, 'productManagement']);
        Route::apiResource('/products', FarmerProductController::class);
        Route::post('/products/{product}/upload-images', [FarmerProductController::class, 'uploadImages']);

        Route::get('/dashboard/orders', [FarmerDashboardController::class, 'customerOrders']);
        Route::get('/orders', [FarmerOrderController::class, 'index']);
        Route::get('/orders/{id}', [FarmerOrderController::class, 'show']);
        Route::post('/orders/{id}/accept', [FarmerOrderController::class, 'accept']);
        Route::post('/orders/{id}/reject', [FarmerOrderController::class, 'reject']);

        Route::get('/dashboard/agricultural-inputs', [FarmerDashboardController::class, 'requestInputs']);
        Route::get('/dashboard/transport-requests', [FarmerDashboardController::class, 'transportRequests']);
        Route::apiResource('/transport-requests', FarmerTransportController::class);

        Route::get('/dashboard/weather-forecast', [FarmerDashboardController::class, 'weatherForecast']);
        Route::get('/dashboard/market-prices', [FarmerDashboardController::class, 'marketPrices']);

        Route::get('/dashboard/consultations', [FarmerDashboardController::class, 'consultationRequests']);
        Route::apiResource('/consultations', FarmerConsultationController::class);

        Route::get('/dashboard/loan-applications', [FarmerDashboardController::class, 'loanApplications']);
        Route::apiResource('/loans', FarmerLoanController::class);

        Route::get('/dashboard/sales-reports', [FarmerDashboardController::class, 'salesReports']);
        Route::get('/dashboard/performance-metrics', [SalesReportController::class, 'performanceMetrics']);
        Route::get('/dashboard/export-report', [SalesReportController::class, 'export']);
        Route::get('/reports/top-products', [SalesReportController::class, 'topProducts']);
        Route::get('/reports/revenue-by-category', [SalesReportController::class, 'revenueByCategory']);
    });

    Route::middleware(['role:buyer'])->prefix('buyer')->group(function () {
        Route::get('/dashboard', [BuyerDashboardController::class, 'index']);
        Route::get('/dashboard/order-history', [BuyerDashboardController::class, 'orderHistory']);
        Route::get('/dashboard/cart-summary', [BuyerDashboardController::class, 'cartSummary']);
        Route::get('/dashboard/delivery-tracking', [BuyerDashboardController::class, 'deliveryTracking']);
        Route::get('/dashboard/payment-history', [BuyerDashboardController::class, 'paymentHistory']);
        Route::get('/dashboard/wishlist', [BuyerDashboardController::class, 'wishlist']);
        Route::get('/dashboard/purchase-analytics', [BuyerDashboardController::class, 'purchaseAnalytics']);

        Route::get('/profile', [BuyerController::class, 'profile']);
        Route::post('/profile', [BuyerController::class, 'updateProfile']);

        Route::apiResource('/cart', CartController::class);
        Route::post('/cart/checkout', [CartController::class, 'checkout']);

        Route::apiResource('/orders', BuyerOrderController::class);
        Route::post('/orders/{id}/cancel', [BuyerOrderController::class, 'cancel']);

        Route::apiResource('/payments', BuyerPaymentController::class);
        Route::post('/payments/{id}/verify', [BuyerPaymentController::class, 'verify']);

        Route::apiResource('/reviews', ReviewController::class);
        Route::apiResource('/wishlist', WishlistController::class);

        Route::get('/marketplace', [MarketplaceController::class, 'index']);
        Route::get('/marketplace/search', [MarketplaceController::class, 'search']);
    });

    Route::middleware(['role:supplier'])->prefix('supplier')->group(function () {
        Route::get('/dashboard', [SupplierDashboardController::class, 'index']);
        Route::get('/dashboard/inventory', [SupplierDashboardController::class, 'inventory']);
        Route::get('/dashboard/warehouses', [SupplierDashboardController::class, 'warehouses']);
        Route::get('/dashboard/sales-analytics', [SupplierDashboardController::class, 'salesAnalytics']);
        Route::get('/dashboard/license-status', [SupplierDashboardController::class, 'licenseStatus']);
        Route::get('/dashboard/deliveries', [SupplierDashboardController::class, 'deliveries']);

        Route::apiResource('/products', SupplierProductController::class);
        Route::post('/products/{id}/upload-images', [SupplierProductController::class, 'uploadImages']);

        Route::apiResource('/inventory', InventoryController::class);
        Route::apiResource('/warehouses', WarehouseController::class);

        Route::post('/license/apply', [LicenseController::class, 'apply']);
        Route::get('/license', [LicenseController::class, 'show']);

        Route::apiResource('/orders', SupplierOrderController::class);
        Route::post('/orders/{id}/process', [SupplierOrderController::class, 'process']);
    });

    Route::middleware(['role:transport'])->prefix('transport')->group(function () {
        Route::get('/dashboard', [TransportDashboardController::class, 'index']);
        Route::get('/dashboard/delivery-requests', [TransportDashboardController::class, 'deliveryRequests']);
        Route::get('/dashboard/vehicles', [TransportDashboardController::class, 'vehicles']);
        Route::get('/dashboard/active-deliveries', [TransportDashboardController::class, 'activeDeliveries']);
        Route::get('/dashboard/analytics', [TransportDashboardController::class, 'analytics']);
        Route::get('/dashboard/history', [TransportDashboardController::class, 'history']);

        Route::apiResource('/vehicles', VehicleController::class);
        Route::apiResource('/deliveries', DeliveryController::class);
        Route::post('/deliveries/{id}/accept', [DeliveryController::class, 'accept']);
        Route::post('/deliveries/{id}/complete', [DeliveryController::class, 'complete']);

        Route::post('/tracking', [TrackingController::class, 'store']);
        Route::get('/tracking/{delivery_id}', [TrackingController::class, 'show']);
    });

    Route::middleware(['role:expert'])->prefix('expert')->group(function () {
        Route::get('/dashboard', [ExpertDashboardController::class, 'index']);
        Route::get('/dashboard/consultations', [ExpertDashboardController::class, 'consultations']);
        Route::get('/dashboard/training-materials', [ExpertDashboardController::class, 'trainingMaterials']);
        Route::get('/dashboard/articles', [ExpertDashboardController::class, 'articles']);
        Route::get('/dashboard/engagement-analytics', [ExpertDashboardController::class, 'engagementAnalytics']);
        Route::get('/dashboard/farmer-reach', [ExpertDashboardController::class, 'farmerReach']);
        Route::get('/dashboard/consultation/{id}', [ExpertDashboardController::class, 'consultationDetails']);

        Route::get('/profile', [ExpertController::class, 'profile']);
        Route::post('/profile', [ExpertController::class, 'updateProfile']);

        Route::apiResource('/consultations', ConsultationController::class);
        Route::post('/consultations/{id}/reply', [ConsultationController::class, 'reply']);

        Route::apiResource('/training', TrainingController::class);
        Route::post('/training/{id}/publish', [TrainingController::class, 'publish']);

        Route::apiResource('/articles', ArticleController::class);
        Route::post('/articles/{id}/publish', [ArticleController::class, 'publish']);
    });

    Route::middleware(['role:financial'])->prefix('financial')->group(function () {
        Route::get('/dashboard', [FinancialDashboardController::class, 'index']);
        Route::get('/dashboard/pending-applications', [FinancialDashboardController::class, 'pendingApplications']);
        Route::get('/dashboard/loans', [FinancialDashboardController::class, 'loans']);
        Route::get('/dashboard/insurances', [FinancialDashboardController::class, 'insurances']);
        Route::get('/dashboard/repayments', [FinancialDashboardController::class, 'repayments']);
        Route::get('/dashboard/transactions', [FinancialDashboardController::class, 'transactions']);
        Route::get('/dashboard/portfolio-analytics', [FinancialDashboardController::class, 'portfolioAnalytics']);
        Route::get('/dashboard/loan/{id}', [FinancialDashboardController::class, 'loanDetails']);
        Route::get('/dashboard/risk-assessment', [FinancialDashboardController::class, 'riskAssessment']);

        Route::apiResource('/loans', LoanController::class);
        Route::post('/loans/{id}/approve', [LoanController::class, 'approve']);
        Route::post('/loans/{id}/reject', [LoanController::class, 'reject']);

        Route::apiResource('/insurance', InsuranceController::class);
        Route::post('/insurance/{id}/approve', [InsuranceController::class, 'approve']);

        Route::apiResource('/payments', PaymentController::class);
        Route::post('/payments/{id}/verify', [PaymentController::class, 'verify']);

        Route::apiResource('/transactions', TransactionController::class);
    });

    Route::middleware(['role:cooperative'])->prefix('cooperative')->group(function () {
        Route::get('/dashboard', [CooperativeDashboardController::class, 'index']);
        Route::get('/dashboard/members', [CooperativeDashboardController::class, 'members']);
        Route::get('/dashboard/bulk-purchasing', [CooperativeDashboardController::class, 'bulkPurchasing']);
        Route::get('/dashboard/bulk-sales', [CooperativeDashboardController::class, 'bulkSales']);
        Route::get('/dashboard/collection-centers', [CooperativeDashboardController::class, 'collectionCenters']);
        Route::get('/dashboard/financial-reports', [CooperativeDashboardController::class, 'financialReports']);
        Route::get('/dashboard/member-statistics', [CooperativeDashboardController::class, 'memberStatistics']);

        Route::apiResource('/', CooperativeController::class)->only(['show', 'update']);
        Route::apiResource('/members', MemberController::class);
        Route::apiResource('/sales', SalesController::class);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    Route::get('/weather', [FarmerWeatherController::class, 'index']);
    Route::get('/market-prices', [MarketPriceController::class, 'index']);
    Route::get('/locations', [MapController::class, 'index']);
    Route::post('/locations/search', [MapController::class, 'search']);
});
