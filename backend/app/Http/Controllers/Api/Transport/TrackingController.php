<?php

namespace App\Http\Controllers\Api\Transport;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryTracking;
use App\Models\TransportProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    /**
     * Get delivery tracking history
     */
    public function history(Delivery $delivery)
    {
        // Verify provider access
        $provider = TransportProvider::where('user_id', Auth::id())->first();
        if (!$provider || $delivery->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $tracking = $delivery->tracking()
            ->orderBy('timestamp', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tracking,
        ]);
    }

    /**
     * Get current location
     */
    public function current(Delivery $delivery)
    {
        // Verify provider access
        $provider = TransportProvider::where('user_id', Auth::id())->first();
        if (!$provider || $delivery->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $currentLocation = $delivery->tracking()
            ->orderBy('timestamp', 'desc')
            ->first();

        if (!$currentLocation) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No tracking data available',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $currentLocation,
        ]);
    }

    /**
     * Update delivery location
     */
    public function updateLocation(Request $request, Delivery $delivery)
    {
        // Verify provider access
        $provider = TransportProvider::where('user_id', Auth::id())->first();
        if (!$provider || $delivery->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status' => 'required|in:picked_up,in_transit,arrived,delivered,failed',
            'notes' => 'sometimes|string',
            'proof_of_delivery' => 'sometimes|file|mimes:jpeg,png,jpg|max:2048',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_of_delivery')) {
            $proofPath = $request->file('proof_of_delivery')->store('deliveries/proofs', 'public');
        }

        $tracking = DeliveryTracking::create([
            'delivery_id' => $delivery->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'proof_of_delivery' => $proofPath,
            'timestamp' => now(),
        ]);

        // Update delivery status
        $delivery->update([
            'status' => $validated['status'] === 'delivered' ? 'delivered' : 'in_transit',
            'actual_delivery_time' => $validated['status'] === 'delivered' ? now() : $delivery->actual_delivery_time,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'data' => $tracking,
        ]);
    }

    /**
     * Get tracking map data
     */
    public function mapData(Request $request)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();
        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Transport provider not found',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:in_transit,picked_up,arrived,delivered,failed',
        ]);

        $deliveries = $provider->deliveries()
            ->where('status', 'in_transit')
            ->with('tracking', 'vehicle', 'driver')
            ->get();

        $mapData = $deliveries->map(function ($delivery) {
            $lastTracking = $delivery->tracking()
                ->orderBy('timestamp', 'desc')
                ->first();

            return [
                'id' => $delivery->id,
                'order_id' => $delivery->order_id,
                'vehicle' => $delivery->vehicle?->plate_number,
                'driver' => $delivery->driver?->name,
                'status' => $delivery->status,
                'latitude' => $lastTracking?->latitude,
                'longitude' => $lastTracking?->longitude,
                'last_updated' => $lastTracking?->timestamp,
                'destination_latitude' => $delivery->destination_latitude,
                'destination_longitude' => $delivery->destination_longitude,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mapData,
        ]);
    }

    /**
     * Get route optimization
     */
    public function routeOptimization(Request $request)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();
        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Transport provider not found',
            ], 404);
        }

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $deliveries = $provider->deliveries()
            ->where('vehicle_id', $validated['vehicle_id'])
            ->where('status', 'in_transit')
            ->orderBy('sequence', 'asc')
            ->with('order')
            ->get();

        // Calculate suggested sequence based on coordinates
        $optimized = $this->calculateOptimalRoute($deliveries);

        return response()->json([
            'success' => true,
            'data' => $optimized,
        ]);
    }

    /**
     * Calculate optimal route
     */
    private function calculateOptimalRoute($deliveries)
    {
        $sequence = [];
        foreach ($deliveries as $delivery) {
            $sequence[] = [
                'delivery_id' => $delivery->id,
                'order_id' => $delivery->order_id,
                'latitude' => $delivery->destination_latitude,
                'longitude' => $delivery->destination_longitude,
                'current_sequence' => $delivery->sequence,
                'distance_to_next' => $this->calculateDistance(
                    $delivery->destination_latitude,
                    $delivery->destination_longitude,
                    $deliveries[$delivery->sequence]?->destination_latitude ?? 0,
                    $deliveries[$delivery->sequence]?->destination_longitude ?? 0
                ),
            ];
        }

        return $sequence;
    }

    /**
     * Calculate distance between coordinates
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371; // in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * asin(sqrt($a));

        return $earth_radius * $c;
    }

    /**
     * Get delivery ETAs
     */
    public function estimatedTimes(Request $request)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();
        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Transport provider not found',
            ], 404);
        }

        $validated = $request->validate([
            'vehicle_id' => 'sometimes|exists:vehicles,id',
        ]);

        $query = $provider->deliveries()
            ->where('status', 'in_transit');

        if (isset($validated['vehicle_id'])) {
            $query->where('vehicle_id', $validated['vehicle_id']);
        }

        $deliveries = $query->with('tracking')->get();

        $etas = $deliveries->map(function ($delivery) {
            $lastTracking = $delivery->tracking()
                ->orderBy('timestamp', 'desc')
                ->first();

            if (!$lastTracking) {
                return null;
            }

            $distance = $this->calculateDistance(
                $lastTracking->latitude,
                $lastTracking->longitude,
                $delivery->destination_latitude,
                $delivery->destination_longitude
            );

            // Assume average speed of 40 km/h
            $estimatedMinutes = ($distance / 40) * 60;

            return [
                'delivery_id' => $delivery->id,
                'order_id' => $delivery->order_id,
                'current_location' => [
                    'latitude' => $lastTracking->latitude,
                    'longitude' => $lastTracking->longitude,
                ],
                'destination' => [
                    'latitude' => $delivery->destination_latitude,
                    'longitude' => $delivery->destination_longitude,
                ],
                'distance_remaining_km' => round($distance, 2),
                'estimated_minutes' => round($estimatedMinutes),
                'estimated_arrival' => now()->addMinutes($estimatedMinutes),
            ];
        })->filter();

        return response()->json([
            'success' => true,
            'data' => $etas,
        ]);
    }

    /**
     * Generate delivery report
     */
    public function deliveryReport(Request $request)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();
        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Transport provider not found',
            ], 404);
        }

        $validated = $request->validate([
            'delivery_id' => 'required|exists:deliveries,id',
        ]);

        $delivery = $provider->deliveries()
            ->where('id', $validated['delivery_id'])
            ->with('tracking', 'vehicle', 'driver')
            ->first();

        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery not found',
            ], 404);
        }

        $report = [
            'delivery_id' => $delivery->id,
            'order_id' => $delivery->order_id,
            'status' => $delivery->status,
            'vehicle' => $delivery->vehicle?->plate_number,
            'driver' => $delivery->driver?->name,
            'pickup_time' => $delivery->pickup_time,
            'actual_delivery_time' => $delivery->actual_delivery_time,
            'total_distance' => $delivery->distance_km,
            'tracking_points' => $delivery->tracking()->count(),
            'stop_count' => $delivery->tracking()
                ->where('status', 'arrived')
                ->count(),
            'rating' => $delivery->rating,
            'notes' => $delivery->notes,
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }
}
