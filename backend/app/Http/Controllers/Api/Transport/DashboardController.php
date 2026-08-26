<?php

namespace App\Http\Controllers\Api\Transport;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Vehicle;
use App\Models\DeliveryTracking;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get transport dashboard overview
     */
    public function index(Request $request)
    {
        $transporter = $request->user();

        // Delivery statistics
        $totalDeliveries = Delivery::where('transporter_id', $transporter->id)->count();
        $pendingDeliveries = Delivery::where('transporter_id', $transporter->id)
            ->where('status', 'pending')
            ->count();
        $activeDeliveries = Delivery::where('transporter_id', $transporter->id)
            ->where('status', 'in_transit')
            ->count();
        $completedDeliveries = Delivery::where('transporter_id', $transporter->id)
            ->where('status', 'completed')
            ->count();

        // Vehicle statistics
        $totalVehicles = Vehicle::where('transporter_id', $transporter->id)->count();
        $activeVehicles = Vehicle::where('transporter_id', $transporter->id)
            ->where('status', 'active')
            ->count();

        // Revenue from completed deliveries
        $totalRevenue = Delivery::where('transporter_id', $transporter->id)
            ->where('status', 'completed')
            ->sum('delivery_fee');

        // Recent deliveries
        $recentDeliveries = Delivery::where('transporter_id', $transporter->id)
            ->with(['order', 'tracking', 'vehicle'])
            ->latest()
            ->limit(5)
            ->get();

        // Active deliveries with tracking
        $activeDeliveryDetails = Delivery::where('transporter_id', $transporter->id)
            ->where('status', 'in_transit')
            ->with(['order', 'tracking', 'vehicle'])
            ->get();

        // Deliveries by status
        $deliveriesByStatus = Delivery::where('transporter_id', $transporter->id)
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->get();

        // Monthly revenue trend (database-agnostic)
        $revenueByMonth = Delivery::where('transporter_id', $transporter->id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(function($delivery) {
                return $delivery->created_at->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'month' => $group->first()->created_at->format('Y-m'),
                    'revenue' => $group->sum('delivery_fee'),
                    'deliveries' => $group->count(),
                ];
            })
            ->values();

        // Average delivery time
        $avgDeliveryTime = Delivery::where('transporter_id', $transporter->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get()
            ->avg(fn($delivery) => $delivery->completed_at->diffInHours($delivery->created_at));

        return response()->json([
            'summary' => [
                'total_deliveries' => $totalDeliveries,
                'pending_deliveries' => $pendingDeliveries,
                'active_deliveries' => $activeDeliveries,
                'completed_deliveries' => $completedDeliveries,
                'total_vehicles' => $totalVehicles,
                'active_vehicles' => $activeVehicles,
                'total_revenue' => $totalRevenue,
                'average_delivery_hours' => round($avgDeliveryTime, 2),
            ],
            'recent_deliveries' => $recentDeliveries,
            'active_deliveries' => $activeDeliveryDetails,
            'deliveries_by_status' => $deliveriesByStatus,
            'revenue_by_month' => $revenueByMonth,
        ]);
    }

    /**
     * Get delivery requests
     */
    public function deliveryRequests(Request $request)
    {
        $transporter = $request->user();
        $status = $request->query('status', 'pending');

        $deliveries = Delivery::where('transporter_id', $transporter->id)
            ->where('status', $status)
            ->with(['order', 'tracking'])
            ->latest()
            ->paginate(20);

        return response()->json($deliveries);
    }

    /**
     * Get vehicle fleet
     */
    public function vehicles(Request $request)
    {
        $transporter = $request->user();

        $vehicles = Vehicle::where('transporter_id', $transporter->id)
            ->with(['activeDeliveries' => function($query) {
                $query->where('status', 'in_transit');
            }])
            ->get()
            ->map(fn($vehicle) => [
                'id' => $vehicle->id,
                'registration_number' => $vehicle->registration_number,
                'vehicle_type' => $vehicle->vehicle_type,
                'capacity' => $vehicle->capacity,
                'status' => $vehicle->status,
                'current_location' => $vehicle->current_location,
                'active_deliveries' => $vehicle->activeDeliveries->count(),
                'last_service_date' => $vehicle->last_service_date,
            ]);

        return response()->json([
            'total_vehicles' => $vehicles->count(),
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Get active deliveries with real-time tracking
     */
    public function activeDeliveries(Request $request)
    {
        $transporter = $request->user();

        $deliveries = Delivery::where('transporter_id', $transporter->id)
            ->where('status', 'in_transit')
            ->with(['order', 'tracking' => function($query) {
                $query->latest()->limit(1);
            }, 'vehicle'])
            ->get();

        return response()->json([
            'count' => $deliveries->count(),
            'deliveries' => $deliveries,
        ]);
    }

    /**
     * Get delivery analytics
     */
    public function analytics(Request $request)
    {
        $transporter = $request->user();
        $period = $request->query('period', 30);

        $deliveries = Delivery::where('transporter_id', $transporter->id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays($period))
            ->get();

        // Top routes
        $topRoutes = Delivery::where('transporter_id', $transporter->id)
            ->where('status', 'completed')
            ->groupBy('pickup_location', 'delivery_location')
            ->selectRaw('pickup_location, delivery_location, count(*) as deliveries, AVG(delivery_fee) as avg_fee')
            ->orderByDesc('deliveries')
            ->limit(10)
            ->get();

        // Best performing vehicles
        $vehiclePerformance = Vehicle::where('transporter_id', $transporter->id)
            ->withCount(['deliveries' => function($q) {
                $q->where('status', 'completed')->whereDate('created_at', '>=', now()->subDays($period));
            }])
            ->orderByDesc('deliveries_count')
            ->limit(10)
            ->get();

        return response()->json([
            'period_days' => $period,
            'total_deliveries' => $deliveries->count(),
            'total_revenue' => $deliveries->sum('delivery_fee'),
            'average_fee' => $deliveries->avg('delivery_fee') ?? 0,
            'on_time_deliveries' => $deliveries->where('status', 'completed')->count(),
            'top_routes' => $topRoutes,
            'vehicle_performance' => $vehiclePerformance,
        ]);
    }

    /**
     * Get delivery history
     */
    public function history(Request $request)
    {
        $transporter = $request->user();

        $deliveries = Delivery::where('transporter_id', $transporter->id)
            ->with(['order', 'tracking', 'vehicle'])
            ->latest()
            ->paginate(20);

        return response()->json($deliveries);
    }
}
