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
        $supplierId = Auth::id();

        $query = Order::where(function ($q) use ($supplierId) {
            $q->where('farmer_id', $supplierId)
              ->orWhereHas('items.product', function ($pq) use ($supplierId) {
                  $pq->where('farmer_id', $supplierId);
              });
        })->with(['buyer', 'items.product', 'payment', 'delivery']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', strtolower($request->payment_status));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'amount_high' => $query->orderBy('grand_total', 'desc'),
            'amount_low' => $query->orderBy('grand_total', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $orders = $query->paginate((int) $request->get('limit', 20));

        // Auto-heal payment_status
        $orderItems = collect($orders->items())->map(function ($order) {
            if ($order->payment_status !== 'paid' && (($order->payment && in_array(strtolower($order->payment->status), ['completed', 'paid'])) || $order->escrow_held_at !== null)) {
                $order->payment_status = 'paid';
                $order->saveQuietly();
            }
            return $order;
        });

        return response()->json([
            'success' => true,
            'data' => $orderItems,
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Get order details
     */
    public function show(Order $order)
    {
        $supplierId = Auth::id();
        $isAuthorized = ($order->farmer_id == $supplierId)
            || $order->items()->whereHas('product', fn($q) => $q->where('farmer_id', $supplierId))->exists();

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $order->load(['buyer', 'items.product', 'payment', 'delivery']);

        if ($order->payment_status !== 'paid' && (($order->payment && in_array(strtolower($order->payment->status), ['completed', 'paid'])) || $order->escrow_held_at !== null)) {
            $order->payment_status = 'paid';
            $order->saveQuietly();
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update order status (Blocked by RBAC)
     */
    public function updateStatus(Request $request, Order $order)
    {
        return response()->json([
            'success' => false,
            'message' => 'RBAC Restriction: Suppliers are strictly restricted from manually altering order statuses (Confirmed, Shipped, Delivered). Status progression is strictly managed via Chapa Escrow verification, Admin / Logistics Officers, and Buyer delivery confirmation.',
        ], 403);
    }

    /**
     * Prevent direct order updates from suppliers (RBAC)
     */
    public function update(Request $request, Order $order)
    {
        return response()->json([
            'success' => false,
            'message' => 'RBAC Restriction: Suppliers cannot modify order details directly.',
        ], 403);
    }

    /**
     * Process order
     */
    public function process(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be processed until payment is secured in Chapa Escrow.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order processed successfully for logistics packaging',
            'data' => $order->load(['buyer', 'items.product', 'payment', 'delivery']),
        ]);
    }
}
