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
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $orders = Order::where('farmer_id', $farmer->id)
                ->with(['items.product', 'buyer'])
                ->latest()
                ->paginate(15);

            return response()->json([
                'data' => $orders->items(),
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching orders', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get order details
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $order = Order::where('farmer_id', $farmer->id)
                ->with(['items.product', 'buyer'])
                ->findOrFail($id);

            return response()->json(['data' => $order]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Accept order
     */
    public function accept(Request $request, $id)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $order = Order::where('farmer_id', $farmer->id)->findOrFail($id);

            if ($order->status !== 'pending') {
                return response()->json(['message' => 'Order cannot be accepted'], 403);
            }

            $order->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Order accepted successfully',
                'data' => $order->load(['items.product', 'buyer']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error accepting order', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject order
     */
    public function reject(Request $request, $id)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $order = Order::where('farmer_id', $farmer->id)->findOrFail($id);

            if ($order->status !== 'pending') {
                return response()->json(['message' => 'Order cannot be rejected'], 403);
            }

            $order->update([
                'status' => 'rejected',
            ]);

            return response()->json([
                'message' => 'Order rejected successfully',
                'data' => $order->load(['items.product', 'buyer']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error rejecting order', 'error' => $e->getMessage()], 500);
        }
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
        $farmerId = Auth::id();

        $orders = Order::whereHas('items.product', function ($q) use ($farmerId) {
            $q->where('seller_id', $farmerId);
        })
        ->where('status', $status)
        ->with('items', 'buyer')
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
        $farmerId = Auth::id();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Order::whereHas('items.product', function ($q) use ($farmerId) {
            $q->where('seller_id', $farmerId);
        });

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $orders = $query->get();

        $stats = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->where('status', 'delivered')->sum('total_amount'),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'completed_orders' => $orders->where('status', 'delivered')->count(),
            'by_status' => $orders->groupBy('status')->map->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
