<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Farm;
use App\Models\Crop;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Get farmer profile - use first() instead of firstOrFail to handle no farmer gracefully
            $farmer = Farmer::where('user_id', $user->id)->first();
            
            if (!$farmer) {
                // Return empty dashboard if no farmer profile
                return response()->json([
                    'message' => 'Success',
                    'farmer' => ['id' => $user->id, 'name' => $user->name, 'role' => $user->role],
                    'summary' => $this->getEmptySummary(),
                    'recent_harvests' => [],
                    'recent_orders' => [],
                    'chart_data' => $this->getEmptyChartData(30),
                    'crop_sales' => $this->getEmptyCropSalesData(),
                    'recent_transactions' => [],
                ], 200);
            }
            
            $timeRange = $request->query('time_range', '30'); 
            $days = intval($timeRange);

            // Build summary with real data
            $summary = $this->getSummary($farmer);

            // Get chart data with real order data
            $chartData = $this->getChartData($farmer, $days);
            
            // Get crop sales distribution from order items
            $cropSalesData = $this->getCropSalesDistribution($farmer);
            
            // Get recent transactions from orders
            $recentTransactions = $this->getRecentTransactions($farmer, 10);

            return response()->json([
                'message' => 'Success',
                'farmer' => ['id' => $user->id, 'name' => $user->name, 'role' => $user->role],
                'summary' => $summary,
                'recent_harvests' => [],
                'recent_orders' => [],
                'chart_data' => $chartData,
                'crop_sales' => $cropSalesData,
                'recent_transactions' => $recentTransactions,
            ], 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Dashboard Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Server error',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function getEmptySummary()
    {
        return [
            'total_farms' => 0,
            'total_farm_area_hectares' => 0,
            'total_crops' => 0,
            'active_crops' => 0,
            'total_products' => 0,
            'active_products' => 0,
            'pending_orders' => 0,
            'total_orders' => 0,
            'completed_orders' => 0,
            'total_sales' => 0,
            'average_order_value' => 0,
            'pending_consultations' => 0,
            'total_consultations' => 0,
        ];
    }

    private function getEmptyChartData($days)
    {
        $labels = [];
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $labels[] = Carbon::now()->subDays($i)->format('M d');
            $data[] = 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Revenue',
                'data' => $data,
                'borderColor' => '#10b981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'borderWidth' => 2,
                'tension' => 0.4,
                'fill' => true,
                'pointBackgroundColor' => '#10b981',
                'pointBorderColor' => '#ffffff',
                'pointBorderWidth' => 2,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
            ]]
        ];
    }

    private function getEmptyCropSalesData()
    {
        return [
            'labels' => [],
            'datasets' => [[
                'data' => [],
                'backgroundColor' => [],
                'borderColor' => ['#ffffff'],
                'borderWidth' => 2,
                'hoverOffset' => 10,
            ]]
        ];
    }

    private function getSummary(Farmer $farmer)
    {
        try {
            return [
                'total_farms' => Farm::where('farmer_id', $farmer->id)->count(),
                'total_farm_area_hectares' => (float) (Farm::where('farmer_id', $farmer->id)->sum('farm_size') ?? 0),
                'total_crops' => Crop::where('farmer_id', $farmer->id)->count(),
                'active_crops' => Crop::where('farmer_id', $farmer->id)
                    ->where('status', 'active')
                    ->count(),
                'total_products' => Product::where('farmer_id', $farmer->id)->count(),
                'active_products' => Product::where('farmer_id', $farmer->id)
                    ->where('status', 'available')
                    ->count(),
                'pending_orders' => Order::where('farmer_id', $farmer->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->count(),
                'total_orders' => Order::where('farmer_id', $farmer->id)->count(),
                'completed_orders' => Order::where('farmer_id', $farmer->id)
                    ->where('status', 'delivered')
                    ->count(),
                'total_sales' => (float) (Order::where('farmer_id', $farmer->id)
                    ->where('status', 'delivered')
                    ->sum('grand_total') ?? 0),
                'average_order_value' => (float) (Order::where('farmer_id', $farmer->id)
                    ->where('status', 'delivered')
                    ->avg('grand_total') ?? 0),
                'pending_consultations' => 0,
                'total_consultations' => 0,
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('getSummary Error', [
                'message' => $e->getMessage(),
            ]);
            return $this->getEmptySummary();
        }
    }

    private function getChartData(Farmer $farmer, $days = 30)
    {
        try {
            $labels = [];
            $data = [];
            
            // Generate date labels
            for ($i = $days - 1; $i >= 0; $i--) {
                $labels[] = Carbon::now()->subDays($i)->format('M d');
            }

            // Query real order data grouped by date
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            $ordersByDate = Order::where('farmer_id', $farmer->id)
                ->where('status', 'delivered')
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->select(DB::raw('DATE(delivered_at) as date'), DB::raw('COALESCE(SUM(grand_total), 0) as total'))
                ->groupBy('date')
                ->pluck('total', 'date');

            // Map data to dates
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $data[] = (float) ($ordersByDate[$date] ?? 0);
            }

            return [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Revenue',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ]]
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('getChartData Error', [
                'message' => $e->getMessage(),
            ]);
            return $this->getEmptyChartData($days);
        }
    }

    private function getCropSalesDistribution(Farmer $farmer)
    {
        try {
            // Get sales by product from order items
            $cropSales = OrderItem::whereHas('order', function ($query) use ($farmer) {
                $query->where('farmer_id', $farmer->id)
                      ->where('status', 'delivered');
            })
            ->select('product_id', DB::raw('COALESCE(SUM(quantity * price), 0) as total_amount'))
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

            if ($cropSales->isEmpty()) {
                // Return empty template if no sales
                return $this->getEmptyCropSalesData();
            }

            $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
            $labels = [];
            $values = [];
            $total = 0;

            foreach ($cropSales as $item) {
                $labels[] = $item->product?->name ?? 'Unknown';
                $amount = (float) ($item->total_amount ?? 0);
                $values[] = $amount;
                $total += $amount;
            }
            
            // Calculate percentages
            $percentages = array_map(function ($value) use ($total) {
                return $total > 0 ? (int) round(($value / $total) * 100) : 0;
            }, $values);

            return [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $percentages,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                    'borderColor' => ['#ffffff'],
                    'borderWidth' => 2,
                    'hoverOffset' => 10,
                ]]
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('getCropSalesDistribution Error', [
                'message' => $e->getMessage(),
            ]);
            return $this->getEmptyCropSalesData();
        }
    }

    private function getRecentTransactions(Farmer $farmer, $limit = 10)
    {
        try {
            $transactions = OrderItem::whereHas('order', function ($query) use ($farmer) {
                $query->where('farmer_id', $farmer->id)
                      ->where('status', 'delivered')
                      ->orderBy('delivered_at', 'desc');
            })
            ->with([
                'order' => function ($query) {
                    $query->select('id', 'farmer_id', 'delivered_at', 'order_number');
                },
                'product' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->select('id', 'order_id', 'product_id', 'quantity', 'price')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'ORD-' . str_pad($item->order_id, 5, '0', STR_PAD_LEFT),
                    'crop' => $item->product?->name ?? 'Unknown',
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                    'date' => $item->order?->delivered_at?->format('Y-m-d') ?? Carbon::now()->format('Y-m-d'),
                    'status' => 'completed'
                ];
            })
            ->toArray();

            return $transactions;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('getRecentTransactions Error', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
