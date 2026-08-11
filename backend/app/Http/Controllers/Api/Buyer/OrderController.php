<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Get buyer's orders
     */
    public function index(Request $request)
    {
        $orders = Order::where('buyer_id', Auth::id())
            ->with('items.product', 'delivery', 'payment')
            ->latest()
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Create order from cart
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivery_address' => 'required|string',
            'delivery_latitude' => 'sometimes|numeric',
            'delivery_longitude' => 'sometimes|numeric',
            'payment_method' => 'required|string',
            'notes' => 'sometimes|string',
        ]);

        $buyerId = Auth::id();
        $cart = Cart::where('user_id', $buyerId)->with('items')->firstOrFail();

        if ($cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Group items by seller
            $itemsBySeller = $cart->items->groupBy(fn ($item) => $item->product->seller_id);

            $orders = [];
            $totalAmount = 0;

            foreach ($itemsBySeller as $sellerId => $items) {
                $orderAmount = $items->sum(fn ($item) => $item->price * $item->quantity);
                $tax = $orderAmount * 0.1;
                $totalWithTax = $orderAmount + $tax;

                $order = Order::create([
                    'buyer_id' => $buyerId,
                    'seller_id' => $sellerId,
                    'total_amount' => $totalWithTax,
                    'status' => 'pending',
                    'delivery_address' => $validated['delivery_address'],
                    'delivery_latitude' => $validated['delivery_latitude'] ?? null,
                    'delivery_longitude' => $validated['delivery_longitude'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($items as $cartItem) {
                    $order->items()->create([
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                    ]);
                }

                $orders[] = $order;
                $totalAmount += $totalWithTax;
            }

            // Clear cart
            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'orders' => $orders,
                    'total_amount' => $totalAmount,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order details
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return response()->json([
            'success' => true,
            'data' => $order->load('items.product', 'delivery', 'payment', 'seller', 'buyer'),
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $order->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['reason'],
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully',
            'data' => $order,
        ]);
    }

    /**
     * Get orders by status
     */
    public function byStatus(Request $request, string $status)
    {
        $orders = Order::where('buyer_id', Auth::id())
            ->where('status', $status)
            ->with('items.product', 'delivery')
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Track order
     */
    public function track(Order $order)
    {
        $this->authorize('view', $order);

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'delivery' => $order->delivery,
                'items' => $order->items,
            ],
        ]);
    }

    /**
     * Get order statistics
     */
    public function statistics(Request $request)
    {
        $buyerId = Auth::id();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Order::where('buyer_id', $buyerId);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orders = $query->get();

        $stats = [
            'total_orders' => $orders->count(),
            'total_spent' => $orders->sum('total_amount'),
            'completed_orders' => $orders->where('status', 'delivered')->count(),
            'pending_orders' => $orders->whereIn('status', ['pending', 'confirmed'])->count(),
            'by_status' => $orders->groupBy('status')->map->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
