<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Get supplier's orders
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,shipped,delivered,cancelled',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
            'sort' => 'sometimes|in:newest,oldest,amount_high,amount_low',
        ]);

        $query = Order::whereHas('items', function ($q) {
            $q->where('supplier_id', Auth::id());
        })->with(['customer', 'items' => function ($q) {
            $q->where('supplier_id', Auth::id());
        }]);

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (isset($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('created_at', 'desc'),
                'oldest' => $query->orderBy('created_at', 'asc'),
                'amount_high' => $query->orderBy('total_amount', 'desc'),
                'amount_low' => $query->orderBy('total_amount', 'asc'),
            };
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $orders = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Get order details
     */
    public function show(Order $order)
    {
        // Check if supplier has items in this order
        if (!$order->items()->where('supplier_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $order->load(['customer', 'items' => function ($q) {
            $q->where('supplier_id', Auth::id());
        }, 'delivery']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,shipped,delivered,cancelled',
            'notes' => 'sometimes|string',
            'tracking_number' => 'sometimes|string',
        ]);

        // Verify supplier has items in this order
        if (!$order->items()->where('supplier_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check current status
        if ($order->status === 'delivered' || $order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update delivered or cancelled orders',
            ], 422);
        }

        $order->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $order->notes,
        ]);

        if (isset($validated['tracking_number'])) {
            $order->items()
                ->where('supplier_id', Auth::id())
                ->update(['tracking_number' => $validated['tracking_number']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated',
            'data' => $order,
        ]);
    }

    /**
     * Get order statistics
     */
    public function statistics(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:day,week,month,year',
        ]);

        $period = $validated['period'] ?? 'month';
        $startDate = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
        };

        $orders = Order::whereHas('items', function ($q) {
            $q->where('supplier_id', Auth::id());
        })->where('created_at', '>=', $startDate);

        $stats = [
            'total_orders' => $orders->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'confirmed_orders' => $orders->where('status', 'confirmed')->count(),
            'shipped_orders' => $orders->where('status', 'shipped')->count(),
            'delivered_orders' => $orders->where('status', 'delivered')->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'average_order_value' => $orders->count() > 0 ? $orders->avg('total_amount') : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get pending orders
     */
    public function pending()
    {
        $orders = Order::where('status', 'pending')
            ->whereHas('items', function ($q) {
                $q->where('supplier_id', Auth::id());
            })
            ->with('customer', 'items')
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Export orders
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,xlsx',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
        ]);

        $query = Order::whereHas('items', function ($q) {
            $q->where('supplier_id', Auth::id());
        })->with('customer', 'items');

        if (isset($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (isset($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $orders = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Export prepared. Download will start shortly.',
            'data' => [
                'total_records' => $orders->count(),
                'format' => $validated['format'],
            ],
        ]);
    }
}
