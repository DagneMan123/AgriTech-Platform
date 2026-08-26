<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Get main admin dashboard overview
     */
    public function index(Request $request)
    {
        try {
            // Platform activity monitoring
            $totalUsers = User::count();
            $totalOrders = Order::count();
            $totalRevenue = Payment::where('status', 'completed')->sum('amount');
            $activeDeliveries = Delivery::where('status', 'in_transit')->count();
            $totalProducts = Product::count();
            $totalPayments = Payment::count();

            // User and role statistics
            $usersByRole = User::groupBy('role')
                ->selectRaw('role, count(*) as count')
                ->get();

            // Marketplace monitoring
            $ordersByStatus = Order::groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->get();

            $deliveriesByStatus = Delivery::groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->get();

            // Payment monitoring
            $paymentsByStatus = Payment::groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->get();

            // Recent activity
            $recentOrders = Order::with(['buyer', 'items'])
                ->latest()
                ->limit(10)
                ->get();

            $recentDeliveries = Delivery::with(['order', 'transporter'])
                ->latest()
                ->limit(5)
                ->get();

            $recentPayments = Payment::with('order')
                ->latest()
                ->limit(5)
                ->get();

            // Unread notifications count - handle missing table gracefully
            $unreadNotifications = 0;
            try {
                $unreadNotifications = \App\Models\Notification::whereNull('read_at')->count();
            } catch (\Exception $e) {
                // Table might not exist yet, log and continue
                \Illuminate\Support\Facades\Log::warning('Notifications table check failed: ' . $e->getMessage());
            }

            // Pending actions
            $pendingLoans = \App\Models\Loan::where('status', 'pending')->count();
            $suspendedUsers = User::where('is_active', false)->count();

            // Monthly trends (database-agnostic)
            $revenueByMonth = Payment::where('status', 'completed')
                ->whereDate('created_at', '>=', now()->subMonths(6))
                ->get()
                ->groupBy(function($date) {
                    return $date->created_at->format('Y-m');
                })
                ->map(function($group) {
                    return [
                        'month' => $group->first()->created_at->format('Y-m'),
                        'revenue' => $group->sum('amount'),
                        'transactions' => $group->count(),
                    ];
                })
                ->values();

            return response()->json([
                'summary' => [
                    'total_users' => $totalUsers,
                    'total_orders' => $totalOrders,
                    'total_products' => $totalProducts,
                    'total_revenue' => $totalRevenue,
                    'active_deliveries' => $activeDeliveries,
                    'total_payments' => $totalPayments,
                    'suspended_users' => $suspendedUsers,
                    'pending_loans' => $pendingLoans,
                    'unread_notifications' => $unreadNotifications,
                ],
                'users_by_role' => $usersByRole,
                'orders_by_status' => $ordersByStatus,
                'deliveries_by_status' => $deliveriesByStatus,
                'payments_by_status' => $paymentsByStatus,
                'recent_orders' => $recentOrders,
                'recent_deliveries' => $recentDeliveries,
                'recent_payments' => $recentPayments,
                'revenue_by_month' => $revenueByMonth,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Admin dashboard error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            
            return response()->json([
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * User management with approval/suspension
     */
    public function userManagement(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('status')) {
            $status = $request->status;
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        $users = $query->paginate(20);
        return response()->json($users);
    }

    /**
     * Approve or suspend user account
     */
    public function updateUserStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($id);
        $previousStatus = $user->is_active;

        $user->update(['is_active' => $request->is_active]);

        // Log action
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $request->is_active ? 'approved_user' : 'suspended_user',
            'description' => "User {$user->name} ({$user->email}) " . ($request->is_active ? 'approved' : 'suspended') . ". Reason: {$request->reason}",
            'model' => User::class,
            'model_id' => $id,
        ]);

        return response()->json([
            'message' => 'User status updated',
            'user' => $user,
        ]);
    }

    /**
     * Role and permission management
     */
    public function rolePermissionManagement(Request $request)
    {
        $roles = \App\Models\Role::with('permissions')->get();
        $permissions = \App\Models\Permission::all();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Marketplace monitoring
     */
    public function marketplaceMonitoring(Request $request)
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->count();
        $inactiveProducts = Product::where('status', 'inactive')->count();

        $topProducts = Product::withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->limit(10)
            ->get();

        $productsByCategory = Product::groupBy('category_id')
            ->selectRaw('category_id, count(*) as count')
            ->get();

        return response()->json([
            'summary' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'inactive_products' => $inactiveProducts,
            ],
            'top_products' => $topProducts,
            'products_by_category' => $productsByCategory,
        ]);
    }

    /**
     * Order monitoring
     */
    public function orderMonitoring(Request $request)
    {
        $period = $request->query('period', 30);

        $orders = Order::whereDate('created_at', '>=', now()->subDays($period))
            ->with(['buyer', 'items', 'payment', 'delivery'])
            ->latest()
            ->paginate(20);

        $orderStats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'statistics' => $orderStats,
            'orders' => $orders,
        ]);
    }

    /**
     * Delivery monitoring
     */
    public function deliveryMonitoring(Request $request)
    {
        $period = $request->query('period', 30);

        $deliveries = Delivery::whereDate('created_at', '>=', now()->subDays($period))
            ->with(['order', 'transporter', 'vehicle', 'tracking'])
            ->latest()
            ->paginate(20);

        $deliveryStats = [
            'total' => Delivery::count(),
            'pending' => Delivery::where('status', 'pending')->count(),
            'in_transit' => Delivery::where('status', 'in_transit')->count(),
            'completed' => Delivery::where('status', 'completed')->count(),
            'cancelled' => Delivery::where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'statistics' => $deliveryStats,
            'deliveries' => $deliveries,
        ]);
    }

    /**
     * Payment monitoring
     */
    public function paymentMonitoring(Request $request)
    {
        $period = $request->query('period', 30);

        $payments = Payment::whereDate('created_at', '>=', now()->subDays($period))
            ->with('order')
            ->latest()
            ->paginate(20);

        $paymentStats = [
            'total_transactions' => Payment::count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
            'total_amount' => Payment::where('status', 'completed')->sum('amount'),
        ];

        return response()->json([
            'statistics' => $paymentStats,
            'payments' => $payments,
        ]);
    }

    /**
     * Reports and analytics
     */
    public function reportsAnalytics(Request $request)
    {
        $period = $request->query('period', 30);

        $totalRevenue = Payment::where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays($period))
            ->sum('amount');

        $totalOrders = Order::whereDate('created_at', '>=', now()->subDays($period))->count();

        $totalUsers = User::whereDate('created_at', '>=', now()->subDays($period))->count();

        $totalDeliveries = Delivery::whereDate('created_at', '>=', now()->subDays($period))->count();

        $userGrowth = User::whereDate('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(function($date) {
                return $date->created_at->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'month' => $group->first()->created_at->format('Y-m'),
                    'count' => $group->count(),
                ];
            })
            ->values();

        return response()->json([
            'period_days' => $period,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'new_users' => $totalUsers,
            'total_deliveries' => $totalDeliveries,
            'user_growth_trend' => $userGrowth,
        ]);
    }

    /**
     * System settings
     */
    public function systemSettings(Request $request)
    {
        $settings = Setting::pluck('value', 'key');

        return response()->json([
            'settings' => $settings,
        ]);
    }

    /**
     * Update system settings
     */
    public function updateSystemSettings(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Log action
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'updated_settings',
            'description' => 'System settings updated',
        ]);

        return response()->json([
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Send platform announcements
     */
    public function sendAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_roles' => 'nullable|array',
        ]);

        $targetRoles = $request->target_roles ?? ['farmer', 'buyer', 'supplier', 'transport', 'expert', 'cooperative', 'financial'];

        $users = User::whereIn('role', $targetRoles)->get();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'message' => $request->message,
                'type' => 'announcement',
            ]);
        }

        return response()->json([
            'message' => 'Announcement sent to ' . count($users) . ' users',
        ]);
    }

    /**
     * Get system statistics
     */
    public function getSystemStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_farms' => \App\Models\Farm::count(),
            'total_products' => Product::count(),
            'total_consultations' => \App\Models\Consultation::count(),
            'total_loans' => \App\Models\Loan::count(),
            'pending_loans' => \App\Models\Loan::where('status', 'pending')->count(),
            'active_cooperatives' => \App\Models\Cooperative::count(),
            'total_deliveries' => Delivery::count(),
            'total_orders' => Order::count(),
        ];

        return response()->json($stats);
    }

    /**
     * Activity logs
     */
    public function activityLogs(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(50);

        return response()->json($logs);
    }

    /**
     * Notifications management
     */
    public function notificationManagement(Request $request)
    {
        $notifications = Notification::latest()
            ->paginate(50);

        return response()->json($notifications);
    }
}
