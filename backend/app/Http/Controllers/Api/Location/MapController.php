<?php

namespace App\Http\Controllers\Api\Location;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Warehouse;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MapController extends Controller
{
    /**
     * Get farmer's farms on map
     */
    public function farmerFarms()
    {
        $farms = Farm::where('farmer_id', Auth::id())
            ->select('id', 'name', 'latitude', 'longitude', 'area_size', 'image')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $farms,
        ]);
    }

    /**
     * Get nearby farms for buyer
     */
    public function nearbyFarms(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'sometimes|numeric|min:1|max:100',
        ]);

        $radius = $validated['radius'] ?? 10; // Default 10km

        // Using simple distance calculation (Haversine formula would be more accurate)
        $farms = Farm::selectRaw(
            'id, name, latitude, longitude, area_size, 
            (3959 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
            [$validated['latitude'], $validated['longitude'], $validated['latitude']]
        )
        ->having('distance', '<', $radius)
        ->orderBy('distance')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $farms,
        ]);
    }

    /**
     * Get warehouses on map
     */
    public function warehouses()
    {
        $warehouses = Warehouse::select('id', 'name', 'location', 'latitude', 'longitude', 'capacity', 'current_stock')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $warehouses,
        ]);
    }

    /**
     * Get active deliveries on map
     */
    public function activeDeliveries()
    {
        $deliveries = Delivery::where('status', 'in_transit')
            ->select('id', 'pickup_latitude', 'pickup_longitude', 'dropoff_latitude', 'dropoff_longitude', 'status', 'driver_id')
            ->with('vehicle')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $deliveries,
        ]);
    }

    /**
     * Get delivery route
     */
    public function deliveryRoute(Request $request, int $deliveryId)
    {
        $delivery = Delivery::findOrFail($deliveryId);

        $route = [
            'delivery_id' => $delivery->id,
            'pickup' => [
                'latitude' => $delivery->pickup_latitude,
                'longitude' => $delivery->pickup_longitude,
                'address' => $delivery->pickup_location,
            ],
            'dropoff' => [
                'latitude' => $delivery->dropoff_latitude,
                'longitude' => $delivery->dropoff_longitude,
                'address' => $delivery->dropoff_location,
            ],
            'current_location' => $delivery->current_location ? [
                'latitude' => $delivery->current_location['latitude'],
                'longitude' => $delivery->current_location['longitude'],
            ] : null,
            'status' => $delivery->status,
        ];

        return response()->json([
            'success' => true,
            'data' => $route,
        ]);
    }

    /**
     * Get cooperative collection centers
     */
    public function collectionCenters(Request $request)
    {
        $cooperativeId = $request->get('cooperative_id');

        $centers = \App\Models\CollectionCenter::where('cooperative_id', $cooperativeId)
            ->select('id', 'name', 'location', 'latitude', 'longitude', 'capacity', 'current_stock')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $centers,
        ]);
    }

    /**
     * Get market locations
     */
    public function markets(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'sometimes|numeric|min:1|max:100',
        ]);

        $radius = $validated['radius'] ?? 20;

        // This would fetch from a markets table
        $markets = \App\Models\Market::selectRaw(
            'id, name, latitude, longitude,
            (3959 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
            [$validated['latitude'], $validated['longitude'], $validated['latitude']]
        )
        ->having('distance', '<', $radius)
        ->orderBy('distance')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $markets,
        ]);
    }

    /**
     * Update delivery location (driver only)
     */
    public function updateDeliveryLocation(Request $request, int $deliveryId)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $delivery = Delivery::findOrFail($deliveryId);

        $delivery->update([
            'current_location' => [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'updated_at' => now(),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'data' => $delivery,
        ]);
    }

    /**
     * Get location suggestions (autocomplete)
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        // This would typically query a geocoding service
        $suggestions = \App\Models\Location::where('name', 'like', "%$query%")
            ->orWhere('address', 'like', "%$query%")
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }
}
