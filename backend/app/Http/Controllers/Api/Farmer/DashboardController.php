<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Farm;
use App\Models\Crop;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Harvest;
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
                return response()->json([
                    'message' => 'Unauthorized',
                    'error' => 'No authenticated user found'
                ], 401);
            }

            $timeRange = (int) $request->query('time_range', 30);
            if ($timeRange < 1 || $timeRange > 365) {
                $timeRange = 30;
            }

            // Try to find farmer
            $farmer = null;
            try {
                $farmer = Farmer::where('user_id', $user->id)->first();
            } catch (\Exception $e) {
                \Log::error('Error finding farmer: ' . $e->getMessage());
            }
            
            if (!$farmer) {
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

            // Build summary safely
            $summary = $this->buildSummary($farmer);
            $chartData = $this->buildChartData($farmer, $timeRange);
            $cropSalesData = $this->buildCropSalesDistribution($farmer);
            $recentTransactions = $this->buildRecentTransactions($farmer, 10);

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
            \Log::error('Dashboard Error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Server error',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Internal Server Error',
                'file' => env('APP_DEBUG') ? $e->getFile() : null,
                'line' => env('APP_DEBUG') ? $e->getLine() : null,
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
            ]]
        ];
    }

    private function buildSummary(Farmer $farmer): array
    {
        try {
            $summary = $this->getEmptySummary();

            // Count farms
            try {
                $summary['total_farms'] = Farm::where('farmer_id', $farmer->id)->count();
                $summary['total_farm_area_hectares'] = (float) (Farm::where('farmer_id', $farmer->id)->sum('size_hectares') ?? 0);
            } catch (\Exception $e) {
                \Log::error('Error getting farms: ' . $e->getMessage());
            }

            // Count crops - query through farms
            try {
                $farmIds = Farm::where('farmer_id', $farmer->id)->pluck('id')->toArray();
                if (!empty($farmIds)) {
                    $summary['total_crops'] = Crop::whereIn('farm_id', $farmIds)->count();
                    $summary['active_crops'] = Crop::whereIn('farm_id', $farmIds)->where('status', 'growing')->count();
                }
            } catch (\Exception $e) {
                \Log::error('Error getting crops: ' . $e->getMessage());
            }

            // Count products
            try {
                $summary['total_products'] = Product::where('farmer_id', $farmer->id)->count();
                $summary['active_products'] = Product::where('farmer_id', $farmer->id)->where('status', 'available')->count();
            } catch (\Exception $e) {
                \Log::error('Error getting products: ' . $e->getMessage());
            }

            // Count orders
            try {
                $summary['pending_orders'] = Order::where('farmer_id', $farmer->id)->whereIn('status', ['pending', 'approved'])->count();
                $summary['total_orders'] = Order::where('farmer_id', $farmer->id)->count();
                $summary['completed_orders'] = Order::where('farmer_id', $farmer->id)->where('status', 'delivered')->count();
                $summary['total_sales'] = (float) (Order::where('farmer_id', $farmer->id)->where('status', 'delivered')->sum('grand_total') ?? 0);
                $summary['average_order_value'] = (float) (Order::where('farmer_id', $farmer->id)->where('status', 'delivered')->avg('grand_total') ?? 0);
            } catch (\Exception $e) {
                \Log::error('Error getting orders: ' . $e->getMessage());
            }

            return $summary;
        } catch (\Exception $e) {
            \Log::error('Error in buildSummary: ' . $e->getMessage());
            return $this->getEmptySummary();
        }
    }

    private function buildChartData(Farmer $farmer, int $days = 30): array
    {
        try {
            $labels = [];
            $data = [];
            
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('M d');
            }

            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            $revenueByDate = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $dateKey = Carbon::now()->subDays($i)->format('Y-m-d');
                $revenueByDate[$dateKey] = 0;
            }

            $orders = Order::where('farmer_id', $farmer->id)
                ->where('status', 'delivered')
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->get();

            foreach ($orders as $order) {
                $dateKey = $order->delivered_at->format('Y-m-d');
                if (isset($revenueByDate[$dateKey])) {
                    $revenueByDate[$dateKey] += (float) $order->grand_total;
                }
            }

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
                ]]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in buildChartData: ' . $e->getMessage());
            return $this->getEmptyChartData($days);
        }
    }

    private function buildCropSalesDistribution(Farmer $farmer): array
    {
        try {
            $cropSales = OrderItem::whereHas('order', function ($query) use ($farmer) {
                $query->where('farmer_id', $farmer->id)->where('status', 'delivered');
            })
            ->select('product_id', DB::raw('COALESCE(SUM(CAST(quantity AS NUMERIC) * CAST(price AS NUMERIC)), 0) as total_amount'))
            ->groupBy('product_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

            if ($cropSales->isEmpty()) {
                return $this->getEmptyCropSalesData();
            }

            $colors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];
            $labels = [];
            $values = [];
            $total = 0;

            foreach ($cropSales as $item) {
                try {
                    $product = Product::find($item->product_id);
                    $labels[] = $product?->name ?? 'Unknown';
                } catch (\Exception $e) {
                    $labels[] = 'Unknown';
                }
                
                $amount = (float) ($item->total_amount ?? 0);
                $values[] = $amount;
                $total += $amount;
            }
            
            $percentages = array_map(function ($value) use ($total) {
                return $total > 0 ? (int) round(($value / $total) * 100) : 0;
            }, $values);

            return [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $percentages,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                ]]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in buildCropSalesDistribution: ' . $e->getMessage());
            return $this->getEmptyCropSalesData();
        }
    }

    private function buildRecentTransactions(Farmer $farmer, int $limit = 10): array
    {
        try {
            $transactions = OrderItem::whereHas('order', function ($query) use ($farmer) {
                $query->where('farmer_id', $farmer->id)
                      ->where('status', 'delivered')
                      ->orderBy('delivered_at', 'desc');
            })
            ->select('id', 'order_id', 'product_id', 'quantity', 'price')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                try {
                    $order = Order::find($item->order_id);
                    $product = Product::find($item->product_id);
                    
                    return [
                        'id' => 'ORD-' . str_pad((string) $item->order_id, 5, '0', STR_PAD_LEFT),
                        'crop' => $product?->name ?? 'Unknown',
                        'quantity' => (int) $item->quantity,
                        'price' => (float) $item->price,
                        'date' => $order?->delivered_at?->format('Y-m-d') ?? Carbon::now()->format('Y-m-d'),
                    ];
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter()
            ->toArray();

            return $transactions;
        } catch (\Exception $e) {
            \Log::error('Error in buildRecentTransactions: ' . $e->getMessage());
            return [];
        }
    }
}

