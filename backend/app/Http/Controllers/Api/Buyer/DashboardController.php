<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\Delivery;
use App\Models\Review;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get buyer dashboard overview
     */
    public function index(Request $request)
    {
        $buyer = $request->user();

        // Order statistics
        $totalOrders = Order::where('buyer_id', $buyer->id)->count();
        $pendingOrders = Order::where('buyer_id', $buyer->id)
            ->where('status', 'pending')
            ->count();
        $completedOrders = Order::where('buyer_id', $buyer->id)
            ->where('status', 'completed')
            ->count();
        $cancelledOrders = Order::where('buyer_id', $buyer->id)
            ->where('status', 'cancelled')
            ->count();

        // Shopping statistics
        $totalSpent = Order::where('buyer_id', $buyer->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        $cartCount = Cart::where('buyer_id', $buyer->id)->count();
        $wishlistCount = Wishlist::where('buyer_id', $buyer->id)->count();

        // Delivery tracking
        $activeDeliveries = Delivery::whereHas('order', fn($q) => $q->where('buyer_id', $buyer->id))
            ->where('status', 'in_transit')
            ->count();

        // Payment statistics
        $totalPayments = Payment::where('buyer_id', $buyer->id)
            ->where('status', 'completed')
            ->count();

        // Recent orders
        $recentOrders = Order::where('buyer_id', $buyer->id)
            ->with(['items', 'farm', 'delivery', 'payment'])
            ->latest()
            ->limit(5)
            ->get();

        // Recent reviews written
        $recentReviews = Review::where('reviewer_id', $buyer->id)
            ->with('reviewable')
            ->latest()
            ->limit(5)
            ->get();

        // Active deliveries
        $activeDeliveryDetails = Delivery::whereHas('order', fn($q) => $q->where('buyer_id', $buyer->id))
            ->where('status', 'in_transit')
            ->with(['order', 'tracking'])
            ->get();

        // Orders by status chart data
        $ordersByStatus = Order::where('buyer_id', $buyer->id)
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->get();

        // Monthly spending trend (database-agnostic query)
        $spendingByMonth = Order::where('buyer_id', $buyer->id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(function($date) {
                return $date->created_at->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'month' => $group->first()->created_at->format('Y-m'),
                    'spent' => $group->sum('total_amount'),
                    'orders' => $group->count(),
                ];
            })
            ->values();

        return response()->json([
            'summary' => [
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'cancelled_orders' => $cancelledOrders,
                'total_spent' => $totalSpent,
                'average_order_value' => $totalOrders > 0 ? $totalSpent / $totalOrders : 0,
                'cart_items' => $cartCount,
                'wishlist_items' => $wishlistCount,
                'active_deliveries' => $activeDeliveries,
                'total_payments' => $totalPayments,
            ],
            'recent_orders' => $recentOrders,
            'recent_reviews' => $recentReviews,
            'active_deliveries' => $activeDeliveryDetails,
            'orders_by_status' => $ordersByStatus,
            'spending_by_month' => $spendingByMonth,
        ]);
    }

    /**
     * Get order history with filtering
     */
    public function orderHistory(Request $request)
    {
        $buyer = $request->user();
        $status = $request->query('status');
        $period = $request->query('period', 90); // days

        $query = Order::where('buyer_id', $buyer->id)
            ->whereDate('created_at', '>=', now()->subDays($period));

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->with(['items', 'farm', 'delivery', 'payment'])
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    /**
     * Get cart summary
     */
    public function cartSummary(Request $request)
    {
        $buyer = $request->user();

        $cartItems = Cart::where('buyer_id', $buyer->id)
            ->with('product')
            ->get();

        $total = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);

        return response()->json([
            'items' => $cartItems,
            'total_items' => $cartItems->count(),
            'subtotal' => $total,
            'estimated_tax' => $total * 0.15, // 15% tax
            'estimated_total' => $total * 1.15,
        ]);
    }

    /**
     * Get delivery tracking
     */
    public function deliveryTracking(Request $request)
    {
        $buyer = $request->user();

        $deliveries = Delivery::whereHas('order', fn($q) => $q->where('buyer_id', $buyer->id))
            ->with(['order', 'tracking', 'transporter', 'vehicle'])
            ->latest()
            ->paginate(20);

        return response()->json($deliveries);
    }

    /**
     * Get payment history
     */
    public function paymentHistory(Request $request)
    {
        $buyer = $request->user();

        $payments = Payment::where('buyer_id', $buyer->id)
            ->with('order')
            ->latest()
            ->paginate(20);

        return response()->json($payments);
    }

    /**
     * Get wishlist
     */
    public function wishlist(Request $request)
    {
        $buyer = $request->user();

        $wishlistItems = Wishlist::where('buyer_id', $buyer->id)
            ->with('product')
            ->latest()
            ->paginate(20);

        return response()->json($wishlistItems);
    }

    /**
     * Get purchase analytics
     */
    public function purchaseAnalytics(Request $request)
    {
        $buyer = $request->user();

        $topProducts = Product::whereIn('id', function($query) use ($buyer) {
            $query->select('product_id')
                ->from('order_items')
                ->whereIn('order_id', function($q) use ($buyer) {
                    $q->select('id')->from('orders')->where('buyer_id', $buyer->id);
                });
        })
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->limit(10)
            ->get();

        $favoriteSuppliers = Order::where('buyer_id', $buyer->id)
            ->groupBy('farmer_id')
            ->selectRaw('farmer_id, count(*) as purchase_count, SUM(total_amount) as total_spent')
            ->with('farm')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        return response()->json([
            'top_products' => $topProducts,
            'favorite_suppliers' => $favoriteSuppliers,
        ]);
    }
}
