<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Crop;
use App\Models\Product;
use App\Models\Order;
use App\Models\Harvest;
use App\Models\Consultation;
use App\Models\Loan;
use App\Models\TransportRequest;
use App\Models\WeatherData;
use App\Models\MarketPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get farmer dashboard overview
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $farmer = $user->farmer;

        if (!$farmer) {
            return response()->json(['message' => 'Farmer profile not found'], 404);
        }

        // Farm Management
        $totalFarms = Farm::where('farmer_id', $farmer->id)->count();
        $totalFarmArea = Farm::where('farmer_id', $farmer->id)->sum('size_hectares');
        $farms = Farm::where('farmer_id', $farmer->id)->limit(10)->get();

        // Crop Management
        $totalCrops = Crop::where('farmer_id', $farmer->id)->count();
        $activeCrops = Crop::where('farmer_id', $farmer->id)->where('status', 'active')->count();
        $crops = Crop::where('farmer_id', $farmer->id)->with('farm')->latest()->limit(10)->get();

        // Harvest Records
        $totalHarvests = Harvest::where('farmer_id', $farmer->id)->count();
        $recentHarvests = Harvest::where('farmer_id', $farmer->id)->with('crop')->latest()->limit(5)->get();

        // Product Management
        $totalProducts = Product::where('farmer_id', $farmer->id)->count();
        $activeProducts = Product::where('farmer_id', $farmer->id)->where('status', 'active')->count();
        $recentProducts = Product::where('farmer_id', $farmer->id)->latest()->limit(5)->get();

        // Customer Orders Received
        $totalOrders = Order::where('farmer_id', $farmer->id)->count();
        $pendingOrders = Order::where('farmer_id', $farmer->id)->where('status', 'pending')->count();
        $completedOrders = Order::where('farmer_id', $farmer->id)->where('status', 'completed')->count();
        $recentOrders = Order::where('farmer_id', $farmer->id)->with('buyer')->latest()->limit(5)->get();

        // Revenue
        $totalSales = Order::where('farmer_id', $farmer->id)->where('status', 'completed')->sum('total_amount') ?? 0;
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Consultations
        $totalConsultations = Consultation::where('farmer_id', $farmer->id)->count();
        $pendingConsultations = Consultation::where('farmer_id', $farmer->id)->where('status', 'pending')->count();

        // Loan Applications
        $totalLoans = Loan::where('farmer_id', $farmer->id)->count();
        $pendingLoans = Loan::where('farmer_id', $farmer->id)->where('status', 'pending')->count();

        // Transport Requests
        $totalTransportRequests = TransportRequest::where('farmer_id', $farmer->id)->count();
        $pendingTransportRequests = TransportRequest::where('farmer_id', $farmer->id)->where('status', 'pending')->count();

        // Monthly sales trend
        $salesByMonth = Order::where('farmer_id', $farmer->id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(function($date) {
                return $date->created_at->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'month' => $group->first()->created_at->format('M Y'),
                    'revenue' => $group->sum('total_amount'),
                    'orders' => $group->count()
                ];
            })
            ->values();

        return response()->json([
            'summary' => [
                'total_farms' => $totalFarms,
                'total_farm_area_hectares' => $totalFarmArea,
                'total_crops' => $totalCrops,
                'active_crops' => $activeCrops,
                'total_harvests' => $totalHarvests,
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'total_sales' => $totalSales,
                'average_order_value' => $averageOrderValue,
                'total_consultations' => $totalConsultations,
                'pending_consultations' => $pendingConsultations,
                'total_loans' => $totalLoans,
                'pending_loans' => $pendingLoans,
                'total_transport_requests' => $totalTransportRequests,
                'pending_transport_requests' => $pendingTransportRequests,
            ],
            'farms' => $farms,
            'crops' => $crops,
            'recent_products' => $recentProducts,
            'recent_orders' => $recentOrders,
            'recent_harvests' => $recentHarvests,
            'sales_by_month' => $salesByMonth,
        ]);
    }

    /**
     * Farm Management
     */
    public function farmManagement(Request $request)
    {
        $farmer = $request->user();

        $farms = Farm::where('user_id', $farmer->id)
            ->with(['crops', 'products', 'workers', 'documents', 'equipment'])
            ->paginate(20);

        return response()->json($farms);
    }

    /**
     * Crop Management
     */
    public function cropManagement(Request $request)
    {
        $farmer = $request->user();

        $crops = Crop::whereHas('farm', fn($q) => $q->where('user_id', $farmer->id))
            ->with('farm')
            ->paginate(20);

        return response()->json($crops);
    }

    /**
     * Harvest Management
     */
    public function harvestManagement(Request $request)
    {
        $farmer = $request->user();

        $harvests = Harvest::whereHas('crop', fn($q) => 
            $q->whereHas('farm', fn($q2) => $q2->where('user_id', $farmer->id))
        )
            ->with(['crop', 'crop.farm'])
            ->latest()
            ->paginate(20);

        return response()->json($harvests);
    }

    /**
     * Product Management
     */
    public function productManagement(Request $request)
    {
        $farmer = $request->user();

        $products = Product::where('farmer_id', $farmer->id)
            ->with('images')
            ->paginate(20);

        return response()->json($products);
    }

    /**
     * Customer Orders
     */
    public function customerOrders(Request $request)
    {
        $farmer = $request->user();
        $status = $request->query('status');

        $query = Order::where('farmer_id', $farmer->id)->with(['buyer', 'items', 'delivery']);

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(20);

        return response()->json($orders);
    }

    /**
     * Agricultural Inputs - Request purchase
     */
    public function requestInputs(Request $request)
    {
        $farmer = $request->user();

        $inputOrders = Order::where('farmer_id', $farmer->id)
            ->where('order_type', 'input_purchase')
            ->with(['supplier', 'items'])
            ->latest()
            ->paginate(20);

        return response()->json($inputOrders);
    }

    /**
     * Transportation Requests
     */
    public function transportRequests(Request $request)
    {
        $farmer = $request->user();

        $deliveries = \App\Models\Delivery::whereHas('order', fn($q) => $q->where('farmer_id', $farmer->id))
            ->with(['transporter', 'vehicle', 'tracking'])
            ->latest()
            ->paginate(20);

        return response()->json($deliveries);
    }

    /**
     * Weather Forecasts
     */
    public function weatherForecast(Request $request)
    {
        $farmer = $request->user();

        $weather = WeatherData::where('location', $farmer->location)
            ->latest()
            ->first();

        $forecast = WeatherData::where('location', $farmer->location)
            ->where('forecast_date', '>=', now())
            ->orderBy('forecast_date')
            ->limit(7)
            ->get();

        return response()->json([
            'current_weather' => $weather,
            'forecast' => $forecast,
        ]);
    }

    /**
     * Market Prices
     */
    public function marketPrices(Request $request)
    {
        $farmer = $request->user();

        $prices = MarketPrice::where('location', $farmer->location)
            ->orWhere('location', null) // Global prices
            ->latest()
            ->limit(20)
            ->get();

        return response()->json($prices);
    }

    /**
     * Consultation Requests
     */
    public function consultationRequests(Request $request)
    {
        $farmer = $request->user();

        $consultations = Consultation::where('farmer_id', $farmer->id)
            ->with('expert')
            ->latest()
            ->paginate(20);

        return response()->json($consultations);
    }

    /**
     * Loan Applications
     */
    public function loanApplications(Request $request)
    {
        $farmer = $request->user();

        $loans = Loan::where('farmer_id', $farmer->id)
            ->with('financialInstitution')
            ->latest()
            ->paginate(20);

        return response()->json($loans);
    }

    /**
     * Sales Reports
     */
    public function salesReports(Request $request)
    {
        $farmer = $request->user();
        $period = $request->query('period', 30);

        $totalSales = Order::where('farmer_id', $farmer->id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays($period))
            ->sum('total_amount');

        $salesCount = Order::where('farmer_id', $farmer->id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays($period))
            ->count();

        $topProducts = Product::where('farmer_id', $farmer->id)
            ->withCount(['orderItems' => function($q) {
                $q->whereHas('order', fn($q2) => $q2->whereDate('created_at', '>=', now()->subDays(30)));
            }])
            ->orderByDesc('order_items_count')
            ->limit(10)
            ->get();

        $buyerStats = Order::where('farmer_id', $farmer->id)
            ->where('status', 'completed')
            ->groupBy('buyer_id')
            ->selectRaw('buyer_id, count(*) as purchase_count, SUM(total_amount) as total_spent')
            ->with('buyer')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        return response()->json([
            'period_days' => $period,
            'total_sales' => $totalSales,
            'sales_count' => $salesCount,
            'average_order_value' => $salesCount > 0 ? $totalSales / $salesCount : 0,
            'top_products' => $topProducts,
            'top_buyers' => $buyerStats,
        ]);
    }
}
