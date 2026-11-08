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
            ->with(['items.product', 'buyer', 'payment', 'delivery'])
            ->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('payment_status')) {
                $query->where('payment_status', strtolower($request->payment_status));
            }

            $limit = (int) $request->get('limit', 50);
            $orders = $query->paginate($limit);

            // Auto-heal / synchronize payment_status from Payment record if escrow is held or completed
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
            ->with(['items.product', 'buyer', 'payment', 'delivery'])
            ->findOrFail($id);

            // Auto-heal payment_status
            if ($order->payment_status !== 'paid' && (($order->payment && in_array(strtolower($order->payment->status), ['completed', 'paid'])) || $order->escrow_held_at !== null)) {
                $order->payment_status = 'paid';
                $order->saveQuietly();
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
            })->findOrFail($id);

            if ($order->status !== 'pending') {
                return response()->json(['message' => 'Order cannot be rejected'], 403);
            }

            $order->update([
                'status' => 'rejected',
            ]);

            return response()->json([
                'message' => 'Order rejected successfully',
                'data' => $order->load(['items.product', 'buyer', 'payment', 'delivery']),
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
        ->with(['items.product', 'buyer', 'payment', 'delivery'])
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
        });

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orders = $query->get();

        $stats = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->where('status', 'delivered')->sum('grand_total'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'completed_orders' => $orders->where('status', 'delivered')->count(),
            'paid_orders' => $orders->where('payment_status', 'paid')->count(),
            'by_status' => $orders->groupBy('status')->map->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
