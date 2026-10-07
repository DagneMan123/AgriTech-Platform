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

            $farmer = Farmer::where('user_id', $user->id)->first();
            
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

            $summary = $this->getSummary($farmer);
            $chartData = $this->getChartData($farmer, $timeRange);
            $cropSalesData = $this->getCropSalesDistribution($farmer);
            $recentTransactions = $this->getRecentTransactions($farmer, 10);

            $recentHarvests = [];
            $recentOrders = [];

            return response()->json([
                'message' => 'Success',
                'farmer' => ['id' => $user->id, 'name' => $user->name, 'role' => $user->role],
                'summary' => $summary,
                'recent_harvests' => $recentHarvests,
                'recent_orders' => $recentOrders,
                'chart_data' => $chartData,
                'crop_sales' => $cropSalesData,
                'recent_transactions' => $recentTransactions,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Internal Server Error',
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
            return $this->getEmptySummary();
        }
    }

    private function getChartData(Farmer $farmer, int $days = 30): array
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
            return $this->getEmptyChartData($days);
        }
    }

    private function getCropSalesDistribution(Farmer $farmer): array
    {
        try {
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
                ];
            })
            ->toArray();

            return $transactions;
        } catch (\Exception $e) {
            return [];
        }
    }
}
