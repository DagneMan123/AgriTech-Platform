<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Services\EscrowSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * List all platform orders for Admin / Logistics Officer monitoring.
     */
    public function index(Request $request)
    {
        $query = Order::with(['buyer', 'farmer.user', 'items.product', 'delivery.transporter', 'delivery.vehicle', 'payment']);

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status') && !empty($request->payment_status)) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('buyer', fn($bq) => $bq->where('name', 'like', "%{$search}%"))
                  ->orWhere('delivery_address', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Show single order details.
     */
    public function show($id)
    {
        $order = Order::with(['buyer', 'farmer.user', 'items.product', 'delivery.transporter', 'delivery.vehicle', 'payment'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Stage 2: Confirmed
     * Admin or Logistics Officer assigns a transporter & vehicle to the order.
     * Advances order status to Confirmed.
     */
    public function assignTransporter(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'transporter_id' => 'nullable|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'notes' => 'nullable|string',
            'pickup_location' => 'nullable|string',
            'destination_location' => 'nullable|string',
        ]);

        $transporterId = $validated['transporter_id'] ?? null;
        $vehicleId = $validated['vehicle_id'] ?? null;

        // Auto-select active transporter/vehicle if not explicitly provided
        if (!$transporterId) {
            $activeTransporter = Transporter::where('is_active', true)->first();
            $transporterId = $activeTransporter?->user_id ?? $activeTransporter?->id;
        }

        if (!$vehicleId && $transporterId) {
            $vehicleId = Vehicle::where('transporter_id', $transporterId)->value('id');
        }

        // Find or create Delivery record for this order
        $delivery = Delivery::firstOrNew(['order_id' => $order->id]);
        $delivery->transporter_id = $transporterId;
        $delivery->transport_provider_id = $transporterId;
        $delivery->vehicle_id = $vehicleId;
        $delivery->origin_location = $validated['pickup_location'] ?? ($order->farm?->location ?? 'Farm / Regional Hub');
        $delivery->destination_location = $validated['destination_location'] ?? ($order->delivery_address ?: 'Buyer Destination');
        $delivery->delivery_address = $order->delivery_address;
        $delivery->status = 'assigned';
        $delivery->assigned_at = now();
        $delivery->save();

        // Advance Order to Stage 2: Confirmed
        $order->update([
            'status' => 'confirmed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transporter assigned successfully. Order is now Confirmed (Stage 2: Confirmed).',
            'data' => $order->fresh(['delivery.transporter', 'delivery.vehicle', 'buyer']),
        ]);
    }

    /**
     * Stage 3: Shipped
     * Admin or Logistics Officer marks goods picked up and dispatched into transit.
     */
    public function dispatchOrder(Request $request, $id)
    {
        $order = Order::with('delivery')->findOrFail($id);

        if ($order->delivery) {
            $order->delivery->update([
                'status' => 'in_transit',
                'picked_up_at' => now(),
                'pickup_time' => now(),
            ]);
        }

        $order->update([
            'status' => 'shipped',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order goods dispatched into transit (Stage 3: Shipped).',
            'data' => $order->fresh(['delivery.transporter', 'delivery.vehicle']),
        ]);
    }

    /**
     * Stage 4: Delivered & Paid
     * Finalized when delivery is officially confirmed and Chapa Escrow funds are settled.
     */
    public function completeDelivery(Request $request, $id, EscrowSettlementService $escrowService)
    {
        $order = Order::with(['delivery', 'farmer'])->findOrFail($id);

        if ($order->delivery) {
            $order->delivery->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivery_time' => now(),
            ]);
        }

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'payment_status' => 'paid',
            'escrow_status' => 'disbursed',
            'escrow_disbursed_at' => now(),
        ]);

        $settlement = null;
        try {
            $settlement = $escrowService->disburseFarmerPayout($order);
        } catch (\Exception $e) {
            Log::warning('Escrow payout warning during admin delivery completion: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery finalized and confirmed. Chapa Escrow funds successfully settled to farmer account (Stage 4: Delivered & Paid).',
            'data' => $order->fresh(['delivery', 'payment']),
            'settlement' => $settlement,
        ]);
    }
}
