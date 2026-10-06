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
            \Log::info('Dashboard endpoint called');
            
            // Get authenticated user
            $user = auth('api')->user();
            
            \Log::info('Authenticated user: ' . ($user ? $user->id : 'NULL'));
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'error' => 'No authenticated user found'
                ], 401);
            }

            // Get time range parameter (default 30 days)
            $timeRange = (int) $request->query('time_range', 30);
            if ($timeRange < 1 || $timeRange > 365) {
                $timeRange = 30; // Validate range
            }

            \Log::info('Time range: ' . $timeRange);

            // Get farmer profile
            $farmer = Farmer::where('user_id', $user->id)->first();
            
            \Log::info('Farmer found: ' . ($farmer ? $farmer->id : 'NULL'));
            
            if (!$farmer) {
                // Return empty dashboard if no farmer profile
                return response()->json([
                    'message' => 'Success',
                    'farmer' => ['id' => $user->id, 'name' => $user->name, 'role' => $user->role],
                    'summary' => $this->getEmptySummary(),
                    'recent_harvests' => [],
                    'recent_orders' => [],
                    'chart_data' => $this->getEmptyChartData($timeRange),
                    'crop_sales' => $this->getEmptyCropSalesData(),
                    'recent_transactions' => [],
                ], 200);
            }

            // Get summary data
            $summary = $this->getSummary($farmer);

            // Get chart data - dynamically calculates last N days from today
            $chartData = $this->getChartData($farmer, $timeRange);
            
            // Get crop sales distribution
            $cropSalesData = $this->getCropSalesDistribution($farmer);
            
            // Get recent transactions
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

        } catch (\Exception $e) {
            \Log::error('Dashboard Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Server error',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Internal Server Error',
                'debug' => env('APP_DEBUG') ? ['file' => $e->getFile(), 'line' => $e->getLine()] : null,
            ], 500);
        }
    }

    private function getEmptySummary(): array
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

    private function getEmptyChartData(int $days): array
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

    private function getEmptyCropSalesData(): array
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

    private function getSummary(Farmer $farmer): array
    {
        try {
            return [
                'total_farms' => Farm::where('farmer_id', $farmer->id)->count(),
                'total_farm_area_hectares' => (float) (Farm::where('farmer_id', $farmer->id)->sum('farm_size') ?? 0),
                'total_crops' => Crop::where('farmer_id', $farmer->id)->count(),
                'active_crops' => Crop::where('farmer_id', $farmer->id)->where('status', 'active')->count(),
                'total_products' => Product::where('farmer_id', $farmer->id)->count(),
                'active_products' => Product::where('farmer_id', $farmer->id)->where('status', 'available')->count(),
                'pending_orders' => Order::where('farmer_id', $farmer->id)->whereIn('status', ['pending', 'approved'])->count(),
                'total_orders' => Order::where('farmer_id', $farmer->id)->count(),
                'completed_orders' => Order::where('farmer_id', $farmer->id)->where('status', 'delivered')->count(),
                'total_sales' => (float) (Order::where('farmer_id', $farmer->id)->where('status', 'delivered')->sum('grand_total') ?? 0),
                'average_order_value' => (float) (Order::where('farmer_id', $farmer->id)->where('status', 'delivered')->avg('grand_total') ?? 0),
                'pending_consultations' => 0,
                'total_consultations' => 0,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('getSummary Error: ' . $e->getMessage());
            return $this->getEmptySummary();
        }
    }

    private function getChartData(Farmer $farmer, int $days = 30): array
    {
        try {
            $labels = [];
            $data = [];
            
            // Generate date labels for last N days (today backwards)
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('M d'); // Format: "Oct 06"
            }

            // Define date range
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            // Initialize array with all dates to ensure complete data
            $revenueByDate = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $dateKey = Carbon::now()->subDays($i)->format('Y-m-d');
                $revenueByDate[$dateKey] = 0;
            }

            // Query real order data grouped by delivery date
            $orders = Order::where('farmer_id', $farmer->id)
                ->where('status', 'delivered')
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->get();

            // Sum revenue by date
            foreach ($orders as $order) {
                $dateKey = $order->delivered_at->format('Y-m-d');
                if (isset($revenueByDate[$dateKey])) {
                    $revenueByDate[$dateKey] += (float) $order->grand_total;
                }
            }

            // Build data array matching the date labels
            for ($i = $days - 1; $i >= 0; $i--) {
                $dateKey = Carbon::now()->subDays($i)->format('Y-m-d');
                $data[] = $revenueByDate[$dateKey] ?? 0;
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('getChartData Error: ' . $e->getMessage(), [
                'exception' => $e,
                'farmer_id' => $farmer->id ?? null
            ]);
            return $this->getEmptyChartData($days);
        }
    }

    private function getCropSalesDistribution(Farmer $farmer): array
    {
        try {
            // Get top 5 crops by sales
            $cropSales = OrderItem::whereHas('order', function ($query) use ($farmer) {
                $query->where('farmer_id', $farmer->id)->where('status', 'delivered');
            })
            ->select('product_id', DB::raw('COALESCE(SUM(CAST(quantity AS NUMERIC) * CAST(price AS NUMERIC)), 0) as total_amount'))
            ->groupBy('product_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->with('product:id,name')
            ->get();

            if ($cropSales->isEmpty()) {
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('getCropSalesDistribution Error: ' . $e->getMessage());
            return $this->getEmptyCropSalesData();
        }
    }

    private function getRecentTransactions(Farmer $farmer, int $limit = 10): array
    {
        try {
            $transactions = OrderItem::whereHas('order', function ($query) use ($farmer) {
                $query->where('farmer_id', $farmer->id)
                      ->where('status', 'delivered')
                      ->orderBy('delivered_at', 'desc');
            })
            ->with([
                'order' => function ($query) {
                    $query->select('id', 'farmer_id', 'delivered_at');
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
                    'id' => 'ORD-' . str_pad((string) $item->order_id, 5, '0', STR_PAD_LEFT),
                    'crop' => $item->product?->name ?? 'Unknown',
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                    'date' => $item->order?->delivered_at?->format('Y-m-d') ?? Carbon::now()->format('Y-m-d'),
                    'status' => 'completed'
                ];
            })
            ->toArray();

            return $transactions;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('getRecentTransactions Error: ' . $e->getMessage());
            return [];
        }
    }
}
