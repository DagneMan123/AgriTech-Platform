<?php

namespace App\Http\Controllers\Api\Transport;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\TransportProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    /**
     * Get all vehicles
     */
    public function index(Request $request)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Transport provider not found',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:active,inactive,maintenance',
            'type' => 'sometimes|in:motorcycle,auto,van,truck',
            'sort' => 'sometimes|in:newest,oldest,capacity_high,capacity_low',
        ]);

        $query = $provider->vehicles();

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['type'])) {
            $query->where('vehicle_type', $validated['type']);
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('registration_date', 'desc'),
                'oldest' => $query->orderBy('registration_date', 'asc'),
                'capacity_high' => $query->orderBy('capacity_kg', 'desc'),
                'capacity_low' => $query->orderBy('capacity_kg', 'asc'),
            };
        } else {
            $query->orderBy('registration_date', 'desc');
        }

        $vehicles = $query->with('driver', 'currentDeliveries')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $vehicles,
        ]);
    }

    /**
     * Create new vehicle
     */
    public function store(Request $request)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Transport provider not found',
            ], 404);
        }

        $validated = $request->validate([
            'plate_number' => 'required|string|unique:vehicles',
            'vehicle_type' => 'required|in:motorcycle,auto,van,truck',
            'make' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer|min:2000',
            'capacity_kg' => 'required|numeric|min:1',
            'fuel_type' => 'required|in:petrol,diesel,electric,hybrid',
            'chassis_number' => 'sometimes|string',
            'registration_number' => 'sometimes|string',
            'insurance_provider' => 'sometimes|string',
            'insurance_expiry' => 'sometimes|date',
            'inspection_expiry' => 'sometimes|date',
            'notes' => 'sometimes|string',
        ]);

        $vehicle = Vehicle::create([
            'transport_provider_id' => $provider->id,
            'plate_number' => $validated['plate_number'],
            'vehicle_type' => $validated['vehicle_type'],
            'make' => $validated['make'],
            'model' => $validated['model'],
            'year' => $validated['year'],
            'capacity_kg' => $validated['capacity_kg'],
            'fuel_type' => $validated['fuel_type'],
            'chassis_number' => $validated['chassis_number'] ?? null,
            'registration_number' => $validated['registration_number'] ?? null,
            'insurance_provider' => $validated['insurance_provider'] ?? null,
            'insurance_expiry' => $validated['insurance_expiry'] ?? null,
            'inspection_expiry' => $validated['inspection_expiry'] ?? null,
            'status' => 'active',
            'registration_date' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle registered successfully',
            'data' => $vehicle,
        ], 201);
    }

    /**
     * Get vehicle details
     */
    public function show(Vehicle $vehicle)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();

        if (!$provider || $vehicle->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
            ], 404);
        }

        $vehicle->load('driver', 'currentDeliveries', 'maintenanceHistory');

        return response()->json([
            'success' => true,
            'data' => $vehicle,
        ]);
    }

    /**
     * Update vehicle
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();

        if (!$provider || $vehicle->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
            ], 404);
        }

        $validated = $request->validate([
            'make' => 'sometimes|string',
            'model' => 'sometimes|string',
            'capacity_kg' => 'sometimes|numeric|min:1',
            'insurance_provider' => 'sometimes|string',
            'insurance_expiry' => 'sometimes|date',
            'inspection_expiry' => 'sometimes|date',
            'status' => 'sometimes|in:active,inactive,maintenance',
            'notes' => 'sometimes|string',
        ]);

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle,
        ]);
    }

    /**
     * Assign driver to vehicle
     */
    public function assignDriver(Request $request, Vehicle $vehicle)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();

        if (!$provider || $vehicle->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
            ], 404);
        }

        $validated = $request->validate([
            'driver_id' => 'required|exists:users,id',
        ]);

        $vehicle->update(['driver_id' => $validated['driver_id']]);

        return response()->json([
            'success' => true,
            'message' => 'Driver assigned successfully',
            'data' => $vehicle->load('driver'),
        ]);
    }

    /**
     * Record vehicle maintenance
     */
    public function recordMaintenance(Request $request, Vehicle $vehicle)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();

        if (!$provider || $vehicle->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
            ], 404);
        }

        $validated = $request->validate([
            'maintenance_type' => 'required|string',
            'description' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'maintenance_date' => 'required|date',
            'next_maintenance_date' => 'sometimes|date',
            'requires_maintenance' => 'sometimes|boolean',
        ]);

        $maintenance = $vehicle->maintenanceHistory()->create([
            'maintenance_type' => $validated['maintenance_type'],
            'description' => $validated['description'],
            'cost' => $validated['cost'],
            'maintenance_date' => $validated['maintenance_date'],
            'next_maintenance_date' => $validated['next_maintenance_date'] ?? null,
        ]);

        if (isset($validated['requires_maintenance'])) {
            $vehicle->update(['requires_maintenance' => $validated['requires_maintenance']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Maintenance recorded successfully',
            'data' => $maintenance,
        ]);
    }

    /**
     * Get vehicle fuel history
     */
    public function fuelHistory(Vehicle $vehicle)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();

        if (!$provider || $vehicle->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
            ], 404);
        }

        $fuelHistory = $vehicle->fuelHistory()
            ->orderBy('date', 'desc')
            ->get();

        $stats = [
            'total_fuel_cost' => $fuelHistory->sum('cost'),
            'total_liters' => $fuelHistory->sum('liters'),
            'average_cost_per_liter' => $fuelHistory->count() > 0 
                ? $fuelHistory->sum('cost') / $fuelHistory->sum('liters')
                : 0,
            'last_fueling' => $fuelHistory->first(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'history' => $fuelHistory,
            ],
        ]);
    }

    /**
     * Get vehicle utilization
     */
    public function utilization(Request $request, Vehicle $vehicle)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();

        if (!$provider || $vehicle->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
            ], 404);
        }

        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        $period = $validated['period'] ?? 'month';
        $startDate = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
        };

        $deliveries = $vehicle->deliveries()
            ->where('created_at', '>=', $startDate)
            ->get();

        $utilization = [
            'total_deliveries' => $deliveries->count(),
            'total_distance' => $deliveries->sum('distance_km'),
            'total_revenue' => $deliveries->sum('delivery_charge'),
            'average_load_kg' => $deliveries->count() > 0 ? $deliveries->avg('actual_load_kg') : 0,
            'capacity_utilization' => $deliveries->count() > 0 
                ? ($deliveries->avg('actual_load_kg') / $vehicle->capacity_kg) * 100
                : 0,
            'active_days' => $deliveries->groupBy(function ($delivery) {
                return $delivery->created_at->format('Y-m-d');
            })->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $utilization,
        ]);
    }

    /**
     * Update vehicle status
     */
    public function updateStatus(Request $request, Vehicle $vehicle)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();

        if (!$provider || $vehicle->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:active,inactive,maintenance',
            'reason' => 'sometimes|string',
        ]);

        $vehicle->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle status updated',
            'data' => $vehicle,
        ]);
    }

    /**
     * Get vehicle compliance status
     */
    public function complianceStatus(Vehicle $vehicle)
    {
        $provider = TransportProvider::where('user_id', Auth::id())->first();

        if (!$provider || $vehicle->transport_provider_id !== $provider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
            ], 404);
        }

        $compliance = [
            'registration_valid' => true,
            'insurance_valid' => $vehicle->insurance_expiry 
                ? $vehicle->insurance_expiry->isFuture() 
                : false,
            'inspection_valid' => $vehicle->inspection_expiry 
                ? $vehicle->inspection_expiry->isFuture() 
                : false,
            'maintenance_current' => !$vehicle->requires_maintenance,
            'compliance_score' => $this->calculateComplianceScore($vehicle),
            'issues' => $this->identifyComplianceIssues($vehicle),
        ];

        return response()->json([
            'success' => true,
            'data' => $compliance,
        ]);
    }

    /**
     * Calculate compliance score
     */
    private function calculateComplianceScore($vehicle)
    {
        $score = 0;
        $total = 0;

        // Insurance
        $total += 25;
        if ($vehicle->insurance_expiry && $vehicle->insurance_expiry->isFuture()) {
            $score += 25;
        }

        // Inspection
        $total += 25;
        if ($vehicle->inspection_expiry && $vehicle->inspection_expiry->isFuture()) {
            $score += 25;
        }

        // Maintenance
        $total += 25;
        if (!$vehicle->requires_maintenance) {
            $score += 25;
        }

        // Active status
        $total += 25;
        if ($vehicle->status === 'active') {
            $score += 25;
        }

        return ($score / $total) * 100;
    }

    /**
     * Identify compliance issues
     */
    private function identifyComplianceIssues($vehicle)
    {
        $issues = [];

        if ($vehicle->insurance_expiry && $vehicle->insurance_expiry->isPast()) {
            $issues[] = 'Insurance expired';
        }

        if ($vehicle->inspection_expiry && $vehicle->inspection_expiry->isPast()) {
            $issues[] = 'Inspection expired';
        }

        if ($vehicle->requires_maintenance) {
            $issues[] = 'Maintenance required';
        }

        if ($vehicle->status === 'inactive') {
            $issues[] = 'Vehicle is inactive';
        }

        return $issues;
    }
}
