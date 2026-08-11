<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Farmer;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardReportController extends Controller
{
    /**
     * Get overall system dashboard report
     */
    public function index()
    {
        $report = [
            'summary' => $this->getSummary(),
            'growth_metrics' => $this->getGrowthMetrics(),
            'top_performers' => $this->getTopPerformers(),
            'recent_activities' => $this->getRecentActivities(),
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get summary statistics
     */
    private function getSummary()
    {
        return [
            'total_users' => User::count(),
            'total_farmers' => User::where('role', 'farmer')->count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'total_suppliers' => User::where('role', 'supplier')->count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', 'delivered')->sum('total_amount'),
            'active_products' => Product::where('status', 'active')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];
    }

    /**
     * Get growth metrics
     */
    private function getGrowthMetrics()
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $thisMonthUsers = User::where('created_at', '>=', $thisMonth)->count();
        $lastMonthUsers = User::whereBetween('created_at', [$lastMonth, $thisMonth])->count();

        $thisMonthOrders = Order::where('created_at', '>=', $thisMonth)->count();
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonth, $thisMonth])->count();

        $thisMonthRevenue = Order::where('created_at', '>=', $thisMonth)
            ->where('status', 'delivered')
            ->sum('total_amount');
        $lastMonthRevenue = Order::whereBetween('created_at', [$lastMonth, $thisMonth])
            ->where('status', 'delivered')
            ->sum('total_amount');

        return [
            'user_growth' => $lastMonthUsers > 0 
                ? (($thisMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100 
                : 0,
            'order_growth' => $lastMonthOrders > 0 
                ? (($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100 
                : 0,
            'revenue_growth' => $lastMonthRevenue > 0 
                ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 
                : 0,
        ];
    }

    /**
     * Get top performers
     */
    private function getTopPerformers()
    {
        $topFarmers = Farmer::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();

        $topBuyers = Buyer::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();

        $topProducts = Product::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();

        return [
            'top_farmers' => $topFarmers,
            'top_buyers' => $topBuyers,
            'top_products' => $topProducts,
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $orders = Order::with('farmer', 'buyer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $users = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'recent_orders' => $orders,
            'recent_users' => $users,
        ];
    }

    /**
     * Get period report
     */
    public function byPeriod(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|in:week,month,quarter,year',
            'metric' => 'sometimes|in:users,orders,revenue,products',
        ]);

        $startDate = match ($validated['period']) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
        };

        $metrics = [];

        if (!isset($validated['metric']) || $validated['metric'] === 'users') {
            $metrics['users'] = User::where('created_at', '>=', $startDate)->count();
        }

        if (!isset($validated['metric']) || $validated['metric'] === 'orders') {
            $metrics['orders'] = Order::where('created_at', '>=', $startDate)->count();
        }

        if (!isset($validated['metric']) || $validated['metric'] === 'revenue') {
            $metrics['revenue'] = Order::where('created_at', '>=', $startDate)
                ->where('status', 'delivered')
                ->sum('total_amount');
        }

        if (!isset($validated['metric']) || $validated['metric'] === 'products') {
            $metrics['products'] = Product::where('created_at', '>=', $startDate)->count();
        }

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Get geographic distribution
     */
    public function geographicDistribution()
    {
        $usersByRegion = User::selectRaw('region')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('region')
            ->get();

        $ordersByRegion = Order::selectRaw('destination_region')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->groupBy('destination_region')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'users_by_region' => $usersByRegion,
                'orders_by_region' => $ordersByRegion,
            ],
        ]);
    }

    /**
     * Get user demographics
     */
    public function userDemographics()
    {
        $demographics = [
            'by_role' => User::selectRaw('role')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('role')
                ->get(),
            'by_region' => User::selectRaw('region')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('region')
                ->get(),
            'by_gender' => User::selectRaw('gender')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('gender')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $demographics,
        ]);
    }

    /**
     * Export dashboard report
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,xlsx,pdf',
            'include' => 'sometimes|array',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report exported successfully',
            'data' => [
                'format' => $validated['format'],
                'download_link' => '/reports/dashboard-report-' . now()->timestamp . '.' . $validated['format'],
            ],
        ]);
    }
}
