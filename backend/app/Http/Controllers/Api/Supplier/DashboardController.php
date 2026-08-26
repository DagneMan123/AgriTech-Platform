<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Models\SupplierProduct;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\SupplierLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get supplier dashboard overview
     */
    public function index(Request $request)
    {
        $supplier = $request->user();

        // Product statistics
        $totalProducts = SupplierProduct::where('supplier_id', $supplier->id)->count();
        $activeProducts = SupplierProduct::where('supplier_id', $supplier->id)
            ->where('status', 'active')
            ->count();

        // Inventory statistics
        $inventoryItems = Inventory::where('supplier_id', $supplier->id)
            ->with('product')
            ->get();

        $totalInventoryValue = $inventoryItems->sum(fn($item) => $item->quantity * $item->unit_price);
        $lowStockItems = $inventoryItems->filter(fn($item) => $item->quantity < 50);

        // Orders statistics
        $totalOrders = Order::where('supplier_id', $supplier->id)->count();
        $pendingOrders = Order::where('supplier_id', $supplier->id)
            ->where('status', 'pending')
            ->count();
        $processedOrders = Order::where('supplier_id', $supplier->id)
            ->where('status', 'completed')
            ->count();

        // Revenue
        $totalRevenue = Order::where('supplier_id', $supplier->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Warehouse statistics
        $totalWarehouses = Warehouse::where('supplier_id', $supplier->id)->count();
        $warehouseCapacity = Warehouse::where('supplier_id', $supplier->id)
            ->sum('capacity');
        $warehouseUtilization = $warehouseCapacity > 0 
            ? ($inventoryItems->sum('quantity') / $warehouseCapacity) * 100 
            : 0;

        // License status
        $license = SupplierLicense::where('supplier_id', $supplier->id)
            ->latest()
            ->first();

        // Recent orders
        $recentOrders = Order::where('supplier_id', $supplier->id)
            ->with(['items', 'buyer', 'delivery'])
            ->latest()
            ->limit(5)
            ->get();

        // Recent products
        $recentProducts = SupplierProduct::where('supplier_id', $supplier->id)
            ->latest()
            ->limit(5)
            ->get();

        // Orders by status
        $ordersByStatus = Order::where('supplier_id', $supplier->id)
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->get();

        // Monthly revenue trend
        $revenueByMonth = Order::where('supplier_id', $supplier->id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(function($date) {
                return $date->created_at->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'month' => $group->first()->created_at->format('Y-m'),
                    'revenue' => $group->sum('total_amount'),
                    'orders' => $group->count(),
                ];
            })
            ->values();

        return response()->json([
            'summary' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'processed_orders' => $processedOrders,
                'total_revenue' => $totalRevenue,
                'total_inventory_value' => $totalInventoryValue,
                'low_stock_items_count' => $lowStockItems->count(),
                'total_warehouses' => $totalWarehouses,
                'warehouse_utilization_percent' => round($warehouseUtilization, 2),
                'license_status' => $license?->status ?? 'not_applied',
            ],
            'recent_orders' => $recentOrders,
            'recent_products' => $recentProducts,
            'orders_by_status' => $ordersByStatus,
            'revenue_by_month' => $revenueByMonth,
            'low_stock_items' => $lowStockItems->take(10),
        ]);
    }

    /**
     * Get inventory status
     */
    public function inventory(Request $request)
    {
        $supplier = $request->user();
        $threshold = $request->query('threshold', 50);

        $inventory = Inventory::where('supplier_id', $supplier->id)
            ->with('product')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_value' => $item->quantity * $item->unit_price,
                'status' => $item->quantity < $threshold ? 'low_stock' : 'adequate',
                'warehouse_id' => $item->warehouse_id,
            ]);

        $lowStock = $inventory->where('status', 'low_stock');
        $adequateStock = $inventory->where('status', 'adequate');

        return response()->json([
            'total_items' => $inventory->count(),
            'low_stock_count' => $lowStock->count(),
            'adequate_stock_count' => $adequateStock->count(),
            'inventory' => $inventory,
        ]);
    }

    /**
     * Get warehouse details
     */
    public function warehouses(Request $request)
    {
        $supplier = $request->user();

        $warehouses = Warehouse::where('supplier_id', $supplier->id)
            ->with(['stocks' => function($query) {
                $query->select('warehouse_id', DB::raw('SUM(quantity) as total_quantity'));
            }])
            ->get()
            ->map(fn($warehouse) => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'location' => $warehouse->location,
                'capacity' => $warehouse->capacity,
                'current_stock' => $warehouse->stocks->sum('total_quantity') ?? 0,
                'utilization_percent' => ($warehouse->stocks->sum('total_quantity') ?? 0) / $warehouse->capacity * 100,
            ]);

        return response()->json([
            'total_warehouses' => $warehouses->count(),
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Get sales analytics
     */
    public function salesAnalytics(Request $request)
    {
        $supplier = $request->user();
        $period = $request->query('period', 30);

        $sales = Order::where('supplier_id', $supplier->id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays($period))
            ->get();

        $topProducts = SupplierProduct::where('supplier_id', $supplier->id)
            ->with('orders')
            ->get()
            ->sortByDesc(fn($product) => $product->orders->count())
            ->take(10);

        $buyerStats = Order::where('supplier_id', $supplier->id)
            ->where('status', 'completed')
            ->groupBy('buyer_id')
            ->selectRaw('buyer_id, count(*) as purchase_count, SUM(total_amount) as total_spent')
            ->with('buyer')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        return response()->json([
            'period_days' => $period,
            'total_sales' => $sales->sum('total_amount'),
            'sales_count' => $sales->count(),
            'average_order_value' => $sales->avg('total_amount') ?? 0,
            'top_products' => $topProducts,
            'top_buyers' => $buyerStats,
        ]);
    }

    /**
     * Get license status
     */
    public function licenseStatus(Request $request)
    {
        $supplier = $request->user();

        $license = SupplierLicense::where('supplier_id', $supplier->id)
            ->latest()
            ->first();

        return response()->json([
            'has_license' => $license !== null,
            'license' => $license,
            'status' => $license?->status ?? 'not_applied',
            'expires_at' => $license?->expires_at,
        ]);
    }

    /**
     * Get delivery management
     */
    public function deliveries(Request $request)
    {
        $supplier = $request->user();

        $deliveries = Delivery::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))
            ->with(['order', 'transporter', 'tracking'])
            ->latest()
            ->paginate(20);

        return response()->json($deliveries);
    }
}
