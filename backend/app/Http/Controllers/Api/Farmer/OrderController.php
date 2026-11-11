<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Get farmer's received orders
     */
    public function index(Request $request)
    {
        try {
            $farmer = $this->getOrCreateFarmer();

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $query = Order::where(function ($q) use ($farmer) {
                $q->where('farmer_id', $farmer->id)
                  ->orWhere('farmer_id', $farmer->user_id)
                  ->orWhereHas('items.product', function ($pq) use ($farmer) {
                      $pq->where('farmer_id', $farmer->id)
                         ->orWhere('farmer_id', $farmer->user_id);
                  });
            })
            ->with(['items.product', 'buyer', 'payment', 'delivery', 'refund'])
            ->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', strtolower($request->payment_status));
            }

            $limit = (int) $request->get('limit', 50);
            $orders = $query->paginate($limit);

            // Auto-heal / synchronize status & payment_status from Refund and Payment records
            $orderItems = collect($orders->items())->map(function ($order) {
                $isCancelled = $order->getRawOriginal('status') === 'cancelled'
                    || !empty($order->cancelled_at)
                    || str_contains(strtolower($order->notes ?? ''), 'cancellation')
                    || ($order->refund && str_contains(strtolower($order->refund->reason ?? ''), 'cancellation'));

                if ($isCancelled) {
                    $dirty = false;
                    if ($order->getRawOriginal('status') !== 'cancelled') {
                        $order->status = 'cancelled';
                        $dirty = true;
                    }
                    if ($order->payment_status !== 'refunded') {
                        $order->payment_status = 'refunded';
                        $dirty = true;
                    }
                    if ($dirty) {
                        $order->saveQuietly();
                    }
                    return $order;
                }

                if ($order->refund) {
                    $rStatus = strtolower($order->refund->status ?? '');
                    if (in_array($rStatus, ['approved', 'completed', 'refunded'])) {
                        if ($order->getRawOriginal('status') !== 'refunded') {
                            $order->status = 'refunded';
                            $order->payment_status = 'refunded';
                            $order->saveQuietly();
                        }
                    } elseif (in_array($rStatus, ['dispute', 'disputed'])) {
                        if ($order->getRawOriginal('status') !== 'disputed') {
                            $order->status = 'disputed';
                            $order->saveQuietly();
                        }
                    } elseif (in_array($rStatus, ['under_review', 'in_review'])) {
                        if ($order->getRawOriginal('status') !== 'under_review') {
                            $order->status = 'under_review';
                            $order->saveQuietly();
                        }
                    } elseif (in_array($rStatus, ['requested', 'pending'])) {
                        if ($order->getRawOriginal('status') !== 'refund_requested') {
                            $order->status = 'refund_requested';
                            $order->saveQuietly();
                        }
                    }
                }

                $isRefundSettled = strtolower($order->status ?? '') === 'refunded'
                    || strtolower($order->payment_status ?? '') === 'refunded'
                    || ($order->refund && in_array(strtolower($order->refund->status ?? ''), ['approved', 'completed', 'refunded']));

                if ($isRefundSettled) {
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
                'data' => $orderItems,
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Farmer Orders fetch error: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching orders', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get order details
     */
    public function show(Request $request, $id)
    {
        try {
            $farmer = $this->getOrCreateFarmer();

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $order = Order::where(function ($q) use ($farmer) {
                $q->where('farmer_id', $farmer->id)
                  ->orWhere('farmer_id', $farmer->user_id)
                  ->orWhereHas('items.product', function ($pq) use ($farmer) {
                      $pq->where('farmer_id', $farmer->id)
                         ->orWhere('farmer_id', $farmer->user_id);
                  });
            })
            ->with(['items.product', 'buyer', 'payment', 'delivery', 'refund'])
            ->findOrFail($id);

            // Auto-heal / synchronize status & payment_status
            $isCancelled = $order->getRawOriginal('status') === 'cancelled'
                || !empty($order->cancelled_at)
                || str_contains(strtolower($order->notes ?? ''), 'cancellation')
                || ($order->refund && str_contains(strtolower($order->refund->reason ?? ''), 'cancellation'));

            if ($isCancelled) {
                $dirty = false;
                if ($order->getRawOriginal('status') !== 'cancelled') {
                    $order->status = 'cancelled';
                    $dirty = true;
                }
                if ($order->payment_status !== 'refunded') {
                    $order->payment_status = 'refunded';
                    $dirty = true;
                }
                if ($dirty) {
                    $order->saveQuietly();
                }
            } else {
                if ($order->refund) {
                    $rStatus = strtolower($order->refund->status ?? '');
                    if (in_array($rStatus, ['approved', 'completed', 'refunded'])) {
                        if ($order->getRawOriginal('status') !== 'refunded') {
                            $order->status = 'refunded';
                            $order->payment_status = 'refunded';
                            $order->saveQuietly();
                        }
                    } elseif (in_array($rStatus, ['dispute', 'disputed'])) {
                        if ($order->getRawOriginal('status') !== 'disputed') {
                            $order->status = 'disputed';
                            $order->saveQuietly();
                        }
                    } elseif (in_array($rStatus, ['under_review', 'in_review'])) {
                        if ($order->getRawOriginal('status') !== 'under_review') {
                            $order->status = 'under_review';
                            $order->saveQuietly();
                        }
                    } elseif (in_array($rStatus, ['requested', 'pending'])) {
                        if ($order->getRawOriginal('status') !== 'refund_requested') {
                            $order->status = 'refund_requested';
                            $order->saveQuietly();
                        }
                    }
                }

                $isRefundSettled = strtolower($order->status ?? '') === 'refunded'
                    || strtolower($order->payment_status ?? '') === 'refunded'
                    || ($order->refund && in_array(strtolower($order->refund->status ?? ''), ['approved', 'completed', 'refunded']));

                if ($isRefundSettled) {
                    if ($order->payment_status !== 'refunded') {
                        $order->payment_status = 'refunded';
                        $order->saveQuietly();
                    }
                } elseif ($order->payment_status !== 'paid' && (($order->payment && in_array(strtolower($order->payment->status), ['completed', 'paid'])) || $order->escrow_held_at !== null)) {
                    $order->payment_status = 'paid';
                    $order->saveQuietly();
                }
            }

            return response()->json(['data' => $order]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Accept order (Blocked by RBAC)
     */
    public function accept(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'RBAC Restriction: Farmers are strictly restricted from manually marking orders as Confirmed. Stage 2 (Confirmed) is strictly activated when buyer payment is secured via Chapa Escrow and logistics is assigned.'
        ], 403);
    }

    /**
     * Reject order
     */
    public function reject(Request $request, $id)
    {
        try {
            $farmer = $this->getOrCreateFarmer();

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $order = Order::where(function ($q) use ($farmer) {
                $q->where('farmer_id', $farmer->id)
                  ->orWhere('farmer_id', $farmer->user_id);
            })->with('refund')->findOrFail($id);

            if ($order->refund && in_array(strtolower($order->refund->status ?? ''), ['requested', 'pending', 'under_review', 'in_review', 'dispute', 'disputed'])) {
                return response()->json(['message' => 'Fulfillment locked: Order has an active refund claim or dispute under review.'], 403);
            }

            if ($order->status !== 'pending') {
                return response()->json(['message' => 'Order cannot be rejected'], 403);
            }

            $order->update([
                'status' => 'rejected',
            ]);

            return response()->json([
                'message' => 'Order rejected successfully',
                'data' => $order->load(['items.product', 'buyer', 'payment', 'delivery', 'refund']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error rejecting order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update order status (Blocked by RBAC)
     */
    public function updateStatus(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'RBAC Restriction: Farmers are strictly restricted from manually overriding, updating, or marking orders as Confirmed, Shipped, Delivered, or Paid. Status transitions are exclusively managed by Chapa Escrow verification, Logistics Managers, and Buyer delivery confirmation.'
        ], 403);
    }

    /**
     * Request delivery
     */
    public function requestDelivery(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $order->loadMissing('refund');
        if ($order->refund && in_array(strtolower($order->refund->status ?? ''), ['requested', 'pending', 'under_review', 'in_review', 'dispute', 'disputed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Fulfillment locked: Cannot request transport dispatch for an order with an active refund claim or dispute under review.',
            ], 422);
        }

        if (in_array(strtolower($order->status ?? ''), ['refunded', 'cancelled']) || strtolower($order->payment_status ?? '') === 'refunded') {
            return response()->json([
                'success' => false,
                'message' => 'Fulfillment locked: Order is already refunded or cancelled.',
            ], 422);
        }

        $validated = $request->validate([
            'pickup_location' => 'required|string',
            'dropoff_location' => 'required|string',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'dropoff_latitude' => 'required|numeric',
            'dropoff_longitude' => 'required|numeric',
            'scheduled_date' => 'sometimes|date',
        ]);

        if ($order->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Delivery can only be requested for confirmed orders',
            ], 422);
        }

        $delivery = \App\Models\Delivery::create([
            'order_id' => $order->id,
            ...$validated,
            'status' => 'pending',
            'requester_id' => Auth::id(),
            'requester_type' => 'farmer',
        ]);

        $order->update(['status' => 'awaiting_delivery']);

        return response()->json([
            'success' => true,
            'message' => 'Delivery request created successfully',
            'data' => $delivery,
        ], 201);
    }

    /**
     * Get order by status
     */
    public function byStatus(Request $request, string $status)
    {
        $farmer = $this->getOrCreateFarmer();
        $farmerId = $farmer ? $farmer->id : Auth::id();

        $orders = Order::where(function ($q) use ($farmerId) {
            $q->where('farmer_id', $farmerId)
              ->orWhereHas('items.product', function ($pq) use ($farmerId) {
                  $pq->where('farmer_id', $farmerId);
              });
        })
        ->where('status', $status)
        ->with(['items.product', 'buyer', 'payment', 'delivery', 'refund'])
        ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Get order statistics
     */
    public function statistics(Request $request)
    {
        $farmer = $this->getOrCreateFarmer();
        $farmerId = $farmer ? $farmer->id : Auth::id();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Order::where(function ($q) use ($farmerId) {
            $q->where('farmer_id', $farmerId)
              ->orWhereHas('items.product', function ($pq) use ($farmerId) {
                  $pq->where('farmer_id', $farmerId);
              });
        })->with('refund');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orders = $query->get();

        $stats = [
            'total_orders' => $orders->count(),
            'total_revenue' => (float) $orders->filter(function ($o) {
                if (in_array(strtolower($o->status ?? ''), ['refunded', 'cancelled']) || strtolower($o->payment_status ?? '') === 'refunded') return false;
                if ($o->refund && in_array(strtolower($o->refund->status ?? ''), ['approved', 'completed', 'refunded'])) return false;
                return in_array($o->status, ['delivered', 'completed']);
            })->sum('grand_total'),
            'pending_orders' => $orders->filter(function ($o) {
                if (in_array(strtolower($o->status ?? ''), ['refunded', 'cancelled']) || strtolower($o->payment_status ?? '') === 'refunded') return false;
                if ($o->refund && in_array(strtolower($o->refund->status ?? ''), ['requested', 'pending', 'under_review', 'in_review', 'dispute', 'disputed', 'approved', 'completed', 'refunded'])) return false;
                return in_array($o->status, ['pending', 'placed', 'awaiting_transport', 'awaiting_delivery', 'confirmed', 'processing', 'shipped', 'in_transit']);
            })->count(),
            'completed_orders' => $orders->whereIn('status', ['delivered', 'completed'])->count(),
            'paid_orders' => $orders->where('payment_status', 'paid')->count(),
            'by_status' => $orders->groupBy('status')->map->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
