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
        $userId = Auth::id() ?? auth()->id();
        $query = Order::where('buyer_id', $userId)
            ->with([
                'items.product.farmer.user',
                'delivery.transporter.user',
                'delivery.vehicle',
                'payment',
                'farmer.user',
                'refund'
            ])
            ->latest();

        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $limit = (int) $request->get('limit', 100);
        $orders = $query->paginate($limit);

        // Ensure every order has assigned farmer profile loaded and auto-sync payment_status
        $orders->getCollection()->transform(function ($order) {
            if (!$order->farmer && $order->items->isNotEmpty()) {
                $itemFarmer = $order->items->first()?->product?->farmer;
                if ($itemFarmer) {
                    $order->setRelation('farmer', $itemFarmer);
                }
            }
            $isRefunded = in_array(strtolower($order->payment_status ?? ''), ['refunded'])
                || in_array(strtolower($order->escrow_status ?? ''), ['refunded'])
                || in_array(strtolower($order->status ?? ''), ['refunded'])
                || in_array(strtolower($order->payment?->status ?? ''), ['refunded'])
                || ($order->refund && in_array(strtolower($order->refund->status ?? ''), ['approved', 'completed', 'refunded']));

            if ($isRefunded) {
                if ($order->payment_status !== 'refunded') {
                    $order->payment_status = 'refunded';
                    $order->saveQuietly();
                }
            } elseif ($order->payment_status !== 'paid' && (($order->payment && in_array(strtolower($order->payment->status), ['completed', 'paid'])) || $order->escrow_held_at !== null)) {
                $order->payment_status = 'paid';
                $order->saveQuietly();
            }
            return $order;
        });

        return response()->json([
            'success' => true,
            'data' => $orders,
            'orders' => $orders->items(),
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

                $firstProd = $items->first()?->product;
                $groupOrigin = $request->input('origin_location')
                    ?: ($firstProd?->origin_location ?: 'Adama');

                $order = Order::create([
                    'buyer_id' => $buyerId,
                    'farmer_id' => $sellerId,
                    'subtotal' => $orderAmount,
                    'tax' => $tax,
                    'grand_total' => $totalWithTax,
                    'status' => 'pending',
                    'origin_location' => $groupOrigin,
                    'pickup_location' => $groupOrigin,
                    'delivery_address' => $validated['delivery_address'],
                    'delivery_region' => $request->input('delivery_region', 'Addis Ababa'),
                    'delivery_zone' => $request->input('delivery_zone', ''),
                    'delivery_woreda' => $request->input('delivery_woreda', ''),
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($items as $cartItem) {
                    $order->items()->create([
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                        'subtotal' => $cartItem->price * $cartItem->quantity,
                        'origin_location' => $cartItem->product?->origin_location ?: $groupOrigin,
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
    public function show($id)
    {
        $userId = Auth::id() ?? auth()->id();
        $order = Order::where('buyer_id', $userId)
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('order_number', $id);
                } else {
                    $q->where('order_number', $id);
                }
            })
            ->with(['items.product.farmer.user', 'delivery.transporter', 'delivery.vehicle', 'payment', 'farmer.user', 'buyer', 'refund'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if (!$order->farmer && $order->items->isNotEmpty()) {
            $itemFarmer = $order->items->first()?->product?->farmer;
            if ($itemFarmer) {
                $order->setRelation('farmer', $itemFarmer);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, $id)
    {
        $userId = Auth::id() ?? auth()->id();
        $order = Order::where('buyer_id', $userId)
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('order_number', $id);
                } else {
                    $q->where('order_number', $id);
                }
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled in current status (' . $order->status . ')',
            ], 422);
        }

        $reason = $request->input('reason', 'Cancelled by buyer');

        $wasPaid = in_array(strtolower($order->payment_status ?? ''), ['paid', 'completed']) 
            || ($order->payment && in_array(strtolower($order->payment->status ?? ''), ['paid', 'completed']))
            || $order->escrow_held_at !== null;

        $updateData = [
            'status' => 'cancelled',
            'notes' => ($order->notes ? $order->notes . ' | ' : '') . 'Cancellation: ' . $reason,
            'cancelled_at' => now(),
        ];

        if ($wasPaid) {
            $updateData['payment_status'] = 'refunded';
            $updateData['escrow_status'] = 'refunded';
            if ($order->payment) {
                $order->payment->update([
                    'status' => 'refunded',
                    'escrow_status' => 'refunded',
                ]);
            }
            \App\Models\Refund::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_id' => $order->payment?->id,
                    'requested_by_user_id' => $userId,
                    'amount' => $order->grand_total ?: ($order->subtotal ?: 0),
                    'status' => 'completed',
                    'reason' => 'Cancellation refund: ' . $reason,
                    'completed_at' => now(),
                ]
            );
        }

        $order->update($updateData);

        return response()->json([
            'success' => true,
            'message' => $wasPaid ? 'Order cancelled and escrow payment refunded successfully' : 'Order cancelled successfully',
            'data' => $order->fresh(['items.product', 'delivery', 'payment', 'farmer.user', 'refund']),
        ]);
    }

    /**
     * Buyer confirms delivery receipt & quality inspection at warehouse.
     * Triggers Chapa Escrow disbursement directly to farmer's registered payout account.
     */
    public function confirmInspection(Request $request, $id, \App\Services\EscrowSettlementService $escrowService)
    {
        $userId = Auth::id() ?? auth()->id();
        $order = Order::where('buyer_id', $userId)->findOrFail($id);

        $order->update([
            'status' => 'delivered',
            'delivered_at' => $order->delivered_at ?: now(),
        ]);

        $settlement = $escrowService->disburseFarmerPayout($order);

        return response()->json([
            'success' => true,
            'message' => 'Delivery and warehouse inspection confirmed. Chapa Escrow funds released to farmer payout account.',
            'data' => $order->fresh(['items.product', 'delivery', 'payment', 'farmer.user']),
            'settlement' => $settlement
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
            'total_spent' => $orders->sum('grand_total'),
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
