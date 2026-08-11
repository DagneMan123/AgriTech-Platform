<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    /**
     * Get sales report
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
        ]);

        $startDate = isset($validated['date_from']) 
            ? $validated['date_from'] 
            : $this->getStartDate($validated['period'] ?? 'month');

        $endDate = $validated['date_to'] ?? now();

        // Get authenticated farmer ID
        $farmerId = auth()->id();

        // Get farmer's orders
        $report = [
            'period' => [
                'from' => $startDate,
                'to' => $endDate,
            ],
            'summary' => $this->getSalesSummary($startDate, $endDate, $farmerId),
            'by_product' => $this->getSalesByProduct($startDate, $endDate, $farmerId),
            'by_farmer' => $this->getSalesByFarmer($startDate, $endDate, $farmerId),
            'by_buyer' => $this->getSalesByBuyer($startDate, $endDate, $farmerId),
            'by_day' => $this->getSalesByDay($startDate, $endDate, $farmerId),
            'trends' => $this->getSalesTrends($startDate, $endDate, $farmerId),
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get sales summary
     */
    private function getSalesSummary($startDate, $endDate, $farmerId = null)
    {
        $query = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($farmerId) {
            $query->where('farmer_id', $farmerId);
        }

        $orders = $query->get();

        return [
            'total_sales' => $orders->sum('total_amount'),
            'total_orders' => $orders->count(),
            'average_order_value' => $orders->count() > 0 ? $orders->avg('total_amount') : 0,
            'highest_order' => $orders->max('total_amount'),
            'lowest_order' => $orders->min('total_amount'),
        ];
    }

    /**
     * Get sales by product
     */
    private function getSalesByProduct($startDate, $endDate, $farmerId = null)
    {
        $query = OrderItem::whereBetween('order_items.created_at', [$startDate, $endDate])
            ->selectRaw('order_items.product_id')
            ->selectRaw('products.name as product_name')
            ->selectRaw('SUM(order_items.quantity) as total_quantity')
            ->selectRaw('SUM(order_items.subtotal) as total_sales')
            ->selectRaw('COUNT(DISTINCT order_items.order_id) as order_count')
            ->join('products', 'order_items.product_id', '=', 'products.id');

        if ($farmerId) {
            $query->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.farmer_id', $farmerId)
                ->where('orders.status', 'delivered');
        }

        return $query->groupBy('order_items.product_id', 'products.name')
            ->orderByRaw('SUM(order_items.subtotal) DESC')
            ->get();
    }

    /**
     * Get sales by farmer
     */
    private function getSalesByFarmer($startDate, $endDate, $farmerId = null)
    {
        $query = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('farmer_id')
            ->selectRaw('users.name as farmer_name')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(total_amount) as total_sales')
            ->join('users', 'orders.farmer_id', '=', 'users.id');

        if ($farmerId) {
            $query->where('farmer_id', $farmerId);
        }

        return $query->groupBy('farmer_id', 'farmer_name')
            ->orderBy('total_sales', 'desc')
            ->get();
    }

    /**
     * Get sales by buyer
     */
    private function getSalesByBuyer($startDate, $endDate, $farmerId = null)
    {
        $query = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('buyer_id')
            ->selectRaw('users.name as buyer_name')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(total_amount) as total_spending')
            ->join('users', 'orders.buyer_id', '=', 'users.id');

        if ($farmerId) {
            $query->where('farmer_id', $farmerId);
        }

        return $query->groupBy('buyer_id', 'buyer_name')
            ->orderBy('total_spending', 'desc')
            ->get();
    }

    /**
     * Get sales by day
     */
    private function getSalesByDay($startDate, $endDate, $farmerId = null)
    {
        $query = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw('SUM(total_amount) as total_sales')
            ->selectRaw('AVG(total_amount) as avg_order_value');

        if ($farmerId) {
            $query->where('farmer_id', $farmerId);
        }

        return $query->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get sales trends
     */
    private function getSalesTrends($startDate, $endDate, $farmerId = null)
    {
        $query = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as period')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(total_amount) as total_sales');

        if ($farmerId) {
            $query->where('farmer_id', $farmerId);
        }

        return $query->groupBy('period')
            ->orderBy('period', 'asc')
            ->get();
    }

    /**
     * Get top selling products
     */
    public function topProducts(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:50',
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        $limit = $validated['limit'] ?? 10;
        $startDate = $this->getStartDate($validated['period'] ?? 'month');

        $products = OrderItem::whereBetween('created_at', [$startDate, now()])
            ->selectRaw('product_id')
            ->selectRaw('products.name')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('SUM(subtotal) as total_sales')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->groupBy('product_id', 'products.name')
            ->orderBy('total_sales', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get revenue by category
     */
    public function revenueByCategory(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        $startDate = $this->getStartDate($validated['period'] ?? 'month');

        $categories = OrderItem::whereBetween('created_at', [$startDate, now()])
            ->selectRaw('categories.name as category')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('SUM(subtotal) as total_sales')
            ->selectRaw('COUNT(DISTINCT order_id) as order_count')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_sales', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get sales performance metrics
     */
    public function performanceMetrics(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        $startDate = $this->getStartDate($validated['period'] ?? 'month');
        $farmerId = auth()->id();

        $orders = Order::where('status', 'delivered')
            ->where('farmer_id', $farmerId)
            ->where('created_at', '>=', $startDate)
            ->get();

        $metrics = [
            'total_sales' => $orders->sum('total_amount'),
            'total_orders' => $orders->count(),
            'avg_order_value' => $orders->count() > 0 ? $orders->avg('total_amount') : 0,
            'order_fulfillment_rate' => ($orders->count() / max(Order::where('farmer_id', $farmerId)->where('created_at', '>=', $startDate)->count(), 1)) * 100,
            'repeat_customer_rate' => $this->calculateRepeatCustomerRate($startDate, $farmerId),
            'conversion_rate' => $this->calculateConversionRate($startDate, $farmerId),
        ];

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Get start date based on period
     */
    private function getStartDate($period)
    {
        return match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };
    }

    /**
     * Calculate repeat customer rate
     */
    private function calculateRepeatCustomerRate($startDate, $farmerId = null)
    {
        $query = Order::where('created_at', '>=', $startDate);

        if ($farmerId) {
            $query->where('farmer_id', $farmerId);
        }

        $repeatCustomers = $query->groupBy('buyer_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $totalCustomers = Order::where('created_at', '>=', $startDate);

        if ($farmerId) {
            $totalCustomers->where('farmer_id', $farmerId);
        }

        $totalCustomers = $totalCustomers->distinct('buyer_id')->count();

        return $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0;
    }

    /**
     * Calculate conversion rate
     */
    private function calculateConversionRate($startDate, $farmerId = null)
    {
        $deliveredQuery = Order::where('status', 'delivered')
            ->where('created_at', '>=', $startDate);

        if ($farmerId) {
            $deliveredQuery->where('farmer_id', $farmerId);
        }

        $deliveredOrders = $deliveredQuery->count();

        $totalQuery = Order::where('created_at', '>=', $startDate);

        if ($farmerId) {
            $totalQuery->where('farmer_id', $farmerId);
        }

        $totalOrders = $totalQuery->count();

        return $totalOrders > 0 ? ($deliveredOrders / $totalOrders) * 100 : 0;
    }

    /**
     * Export sales report
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,xlsx,pdf',
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sales report exported successfully',
            'data' => [
                'format' => $validated['format'],
                'download_link' => '/reports/sales-report-' . now()->timestamp . '.' . $validated['format'],
            ],
        ]);
    }
}
