<?php

namespace App\Http\Controllers\Api\Transport;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryTracking;
use App\Models\Order;
use App\Services\EscrowSettlementService;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $transporter = $request->user();
        $deliveries = Delivery::where(function ($q) use ($transporter) {
            $q->where('transporter_id', $transporter->id)
              ->orWhere('transport_provider_id', $transporter->id);
        })
        ->with(['order.items.product', 'vehicle', 'tracking'])
        ->latest()
        ->paginate(15);

        return response()->json($deliveries);
    }

    public function show($id)
    {
        $delivery = Delivery::with(['order.items.product', 'vehicle', 'tracking'])->findOrFail($id);
        return response()->json($delivery);
    }

    /**
     * Stage 2: Confirmed - Transporter accepts delivery & vehicle assigned
     */
    public function accept(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);
        $user = $request->user();

        $delivery->update([
            'transporter_id' => $user->id,
            'transport_provider_id' => $user->id,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        if ($delivery->order && $delivery->order->status === 'pending') {
            $delivery->order->update(['status' => 'confirmed']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery request accepted and confirmed.',
            'data' => $delivery->fresh(['order', 'vehicle'])
        ]);
    }

    /**
     * Stage 3: Shipped - Cargo picked up and transit officially begins (Dispatch)
     */
    public function dispatchDelivery(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);

        $delivery->update([
            'status' => 'in_transit',
            'picked_up_at' => now(),
            'pickup_time' => now(),
        ]);

        if ($delivery->order) {
            $delivery->order->update(['status' => 'shipped']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produce dispatched and officially in transit (Stage 3: Shipped).',
            'data' => $delivery->fresh(['order', 'vehicle'])
        ]);
    }

    /**
     * Stage 4: Delivered & Paid - Receipt officially confirmed and Chapa Escrow funds released
     */
    public function complete(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);

        $delivery->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'delivery_time' => now(),
        ]);

        // Release Chapa Escrow funds to farmer if linked to an order
        if ($delivery->order) {
            $order = $delivery->order;
            $order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'payment_status' => 'paid',
                'escrow_status' => 'disbursed',
                'escrow_disbursed_at' => now(),
            ]);

            try {
                app(EscrowSettlementService::class)->disburseFarmerPayout($order);
            } catch (\Exception $e) {
                \Log::warning('Escrow disbursement on delivery complete: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery verified. Chapa Escrow funds released directly to farmer account (Stage 4: Delivered & Paid).',
            'data' => $delivery->fresh(['order', 'vehicle'])
        ]);
    }

    public function track(Request $request, $id)
    {
        $delivery = Delivery::findOrFail($id);
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'status' => 'sometimes|in:in_transit,delivered',
        ]);

        DeliveryTracking::create([
            'delivery_id' => $delivery->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        if ($request->has('status')) {
            $delivery->update(['status' => $validated['status']]);
        }

        return response()->json(['message' => 'Tracking updated']);
    }
}
