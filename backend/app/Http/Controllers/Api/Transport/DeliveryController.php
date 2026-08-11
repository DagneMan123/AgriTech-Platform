<?php

namespace App\Http\Controllers\Api\Transport;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryTracking;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $transporter = $request->user();
        $deliveries = Delivery::where('transport_provider_id', $transporter->id)
            ->latest()
            ->paginate(15);

        return response()->json($deliveries);
    }

    public function show(Delivery $delivery)
    {
        $delivery->load('tracking');
        return response()->json($delivery);
    }

    public function accept(Request $request, Delivery $delivery)
    {
        $delivery->update([
            'transport_provider_id' => $request->user()->id,
            'status' => 'assigned',
        ]);

        return response()->json($delivery);
    }

    public function track(Request $request, Delivery $delivery)
    {
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
