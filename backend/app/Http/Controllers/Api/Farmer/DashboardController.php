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

            // Get or create farmer profile
            $farmer = $this->getOrCreateFarmer();
            
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

            // Build live summary & metrics
            $summary = $this->buildSummary($farmer);
            $chartData = $this->buildChartData($farmer, $timeRange);
            $cropSalesData = $this->buildCropSalesDistribution($farmer);
            $recentTransactions = $this->buildRecentTransactions($farmer, 10);
            $recentHarvests = $this->buildRecentHarvests($farmer, 5);
            $recentOrders = $this->buildRecentOrders($farmer, 5);

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
            \Log::error('Dashboard Error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            
            return response()->json([
                'message' => 'Server error',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }

    private function getFarmerOrderQuery(Farmer $farmer)
    {
        return Order::where(function ($q) use ($farmer) {
            $q->where('farmer_id', $farmer->id)
              ->orWhere('farmer_id', $farmer->user_id)
              ->orWhereHas('items.product', function ($pq) use ($farmer) {
                  $pq->where('farmer_id', $farmer->id)
                     ->orWhere('farmer_id', $farmer->user_id);
              });
        });
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
                'label' => 'Revenue (ETB)',
                'data' => $data,
                'borderColor' => '#10b981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                'borderWidth' => 2.5,
                'tension' => 0.4,
                'fill' => true,
            ]]
        ];
    }

    private function getEmptyCropSalesData(): array
    {
        return [
            'labels' => ['Agricultural Produce'],
            'datasets' => [[
                'data' => [100],
                'backgroundColor' => ['#10b981'],
            ]]
        ];
    }

    private function buildSummary(Farmer $farmer): array
    {
        try {
            $summary = $this->getEmptySummary();

            // Count farms & area
            try {
                $farms = Farm::where('farmer_id', $farmer->id)->orWhere('farmer_id', $farmer->user_id)->get();
                $summary['total_farms'] = $farms->count();
                $summary['total_farm_area_hectares'] = (float) ($farms->sum('size_hectares') ?: $farms->sum('area') ?: 0);
            } catch (\Exception $e) {
                \Log::error('Error getting farms: ' . $e->getMessage());
            }

            // Count crops
            try {
                $farmIds = Farm::where('farmer_id', $farmer->id)->orWhere('farmer_id', $farmer->user_id)->pluck('id')->toArray();
                if (!empty($farmIds)) {
                    $summary['total_crops'] = Crop::whereIn('farm_id', $farmIds)->count();
                    $summary['active_crops'] = Crop::whereIn('farm_id', $farmIds)->whereIn('status', ['growing', 'planted', 'active'])->count();
                } else {
                    $summary['total_crops'] = Crop::count();
                    $summary['active_crops'] = Crop::whereIn('status', ['growing', 'planted', 'active'])->count();
                }
            } catch (\Exception $e) {
                \Log::error('Error getting crops: ' . $e->getMessage());
            }

            // Count products
            try {
                $products = Product::where('farmer_id', $farmer->id)->orWhere('farmer_id', $farmer->user_id)->get();
                $summary['total_products'] = $products->count();
                $summary['active_products'] = $products->whereIn('status', ['available', 'active'])->count();
            } catch (\Exception $e) {
                \Log::error('Error getting products: ' . $e->getMessage());
            }

            // Count orders & sales
            try {
                $orderQuery = $this->getFarmerOrderQuery($farmer);
                $orders = (clone $orderQuery)->with('refund')->get();
                
                $summary['total_orders'] = $orders->count();
                $summary['pending_orders'] = $orders->filter(function ($o) {
                    if (in_array(strtolower($o->status ?? ''), ['refunded', 'cancelled']) || strtolower($o->payment_status ?? '') === 'refunded') return false;
                    if ($o->refund && in_array(strtolower($o->refund->status ?? ''), ['requested', 'pending', 'under_review', 'in_review', 'dispute', 'disputed', 'approved', 'completed', 'refunded'])) return false;
                    return in_array($o->status, [
                        'pending', 'placed', 'awaiting_transport', 'awaiting_delivery', 'confirmed', 'processing', 'shipped', 'in_transit'
                    ]);
                })->count();
                $summary['completed_orders'] = $orders->filter(function ($o) {
                    if (in_array(strtolower($o->status ?? ''), ['refunded', 'cancelled']) || strtolower($o->payment_status ?? '') === 'refunded') return false;
                    if ($o->refund && in_array(strtolower($o->refund->status ?? ''), ['approved', 'completed', 'refunded'])) return false;
                    return in_array($o->status, ['delivered', 'completed']);
                })->count();
                
                $completedRevenue = $orders->filter(function ($o) {
                    if (in_array(strtolower($o->status ?? ''), ['refunded', 'cancelled']) || strtolower($o->payment_status ?? '') === 'refunded') return false;
                    if ($o->refund && in_array(strtolower($o->refund->status ?? ''), ['approved', 'completed', 'refunded'])) return false;
                    return in_array($o->status, ['delivered', 'completed']);
                })->sum('grand_total');
                $summary['total_sales'] = (float) $completedRevenue;
                $summary['average_order_value'] = (float) ($summary['completed_orders'] > 0 ? ($completedRevenue / $summary['completed_orders']) : 0);
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
            $revenueByDate = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('M d');
                $revenueByDate[$date->format('Y-m-d')] = 0;
            }

            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            $orders = $this->getFarmerOrderQuery($farmer)
                ->whereIn('status', ['delivered', 'completed'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            foreach ($orders as $order) {
                $dateKey = $order->created_at ? $order->created_at->format('Y-m-d') : null;
                if ($dateKey && isset($revenueByDate[$dateKey])) {
                    $revenueByDate[$dateKey] += (float) ($order->grand_total ?: 0);
                }
            }

            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $dateKey = Carbon::now()->subDays($i)->format('Y-m-d');
                $data[] = round($revenueByDate[$dateKey] ?? 0, 2);
            }

            return [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Revenue (ETB)',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'borderWidth' => 2.5,
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
            $colors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'];
            $labels = [];
            $values = [];

            // 1. Try order items from delivered/completed orders only
            $orderItems = OrderItem::whereHas('order', function ($query) use ($farmer) {
                $query->whereIn('status', ['delivered', 'completed'])
                      ->where(function ($q) use ($farmer) {
                          $q->where('farmer_id', $farmer->id)
                            ->orWhere('farmer_id', $farmer->user_id)
                            ->orWhereHas('items.product', function ($pq) use ($farmer) {
                                $pq->where('farmer_id', $farmer->id)
                                   ->orWhere('farmer_id', $farmer->user_id);
                            });
                      });
            })
            ->with('product')
            ->get();

            $byProduct = [];
            foreach ($orderItems as $item) {
                $pName = $item->product?->name ?? 'Produce';
                $amt = (float) ($item->subtotal ?: ($item->quantity * $item->price) ?: 0);
                $byProduct[$pName] = ($byProduct[$pName] ?? 0) + $amt;
            }

            // 2. If no order items yet, fallback to active products inventory
            if (empty($byProduct)) {
                $products = Product::where('farmer_id', $farmer->id)
                    ->orWhere('farmer_id', $farmer->user_id)
                    ->get();

                foreach ($products as $p) {
                    $byProduct[$p->name] = (float) ($p->price * ($p->quantity_available ?: $p->quantity ?: 1));
                }
            }

            // 3. If still empty, check crops
            if (empty($byProduct)) {
                $farmIds = Farm::where('farmer_id', $farmer->id)->orWhere('farmer_id', $farmer->user_id)->pluck('id');
                $crops = Crop::whereIn('farm_id', $farmIds)->get();
                foreach ($crops as $c) {
                    $name = $c->variety ?: $c->crop_type ?: 'Crops';
                    $byProduct[$name] = (float) ($c->area_hectares ?: 1);
                }
            }

            if (empty($byProduct)) {
                return $this->getEmptyCropSalesData();
            }

            arsort($byProduct);
            $topItems = array_slice($byProduct, 0, 5, true);
            $total = array_sum($topItems);

            foreach ($topItems as $name => $amount) {
                $labels[] = $name;
                $values[] = $total > 0 ? (int) round(($amount / $total) * 100) : 0;
            }

            return [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $values,
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
            $orderItems = OrderItem::whereHas('order', function ($query) use ($farmer) {
                $query->whereIn('status', ['delivered', 'completed'])
                      ->where(function ($q) use ($farmer) {
                          $q->where('farmer_id', $farmer->id)
                            ->orWhere('farmer_id', $farmer->user_id)
                            ->orWhereHas('items.product', function ($pq) use ($farmer) {
                                $pq->where('farmer_id', $farmer->id)
                                   ->orWhere('farmer_id', $farmer->user_id);
                            });
                      });
            })
            ->with(['order.buyer', 'product'])
            ->latest()
            ->limit($limit)
            ->get();

            if ($orderItems->isEmpty()) {
                // Fallback to recent delivered orders directly
                $orders = $this->getFarmerOrderQuery($farmer)
                    ->whereIn('status', ['delivered', 'completed'])
                    ->with('buyer')
                    ->latest()
                    ->limit($limit)
                    ->get();

                return $orders->map(function ($o) {
                    return [
                        'id' => $o->order_number ?: ('ORD-' . str_pad($o->id, 5, '0', STR_PAD_LEFT)),
                        'crop' => 'Agricultural Produce',
                        'quantity' => 1,
                        'price' => (float) $o->grand_total,
                        'total' => (float) $o->grand_total,
                        'date' => $o->created_at ? $o->created_at->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                    ];
                })->toArray();
            }

            return $orderItems->map(function ($item) {
                $qty = (float) ($item->quantity ?: 1);
                $price = (float) ($item->price ?: 0);
                $total = (float) ($item->subtotal ?: ($qty * $price));

                return [
                    'id' => $item->order?->order_number ?: ('ORD-' . str_pad((string) $item->order_id, 5, '0', STR_PAD_LEFT)),
                    'crop' => $item->product?->name ?? 'Produce',
                    'quantity' => $qty,
                    'price' => $price,
                    'total' => $total,
                    'date' => $item->order?->created_at ? $item->order->created_at->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Error in buildRecentTransactions: ' . $e->getMessage());
            return [];
        }
    }

    private function buildRecentHarvests(Farmer $farmer, int $limit = 5): array
    {
        try {
            $farmIds = Farm::where('farmer_id', $farmer->id)->orWhere('farmer_id', $farmer->user_id)->pluck('id');

            $harvests = Harvest::whereHas('crop', function ($q) use ($farmIds) {
                if ($farmIds->isNotEmpty()) {
                    $q->whereIn('farm_id', $farmIds);
                }
            })
            ->with('crop')
            ->latest('harvest_date')
            ->limit($limit)
            ->get();

            if ($harvests->isEmpty()) {
                $harvests = Harvest::with('crop')->latest('harvest_date')->limit($limit)->get();
            }

            return $harvests->map(function ($h) {
                return [
                    'id' => $h->id,
                    'crop' => [
                        'name' => $h->crop?->variety ?: $h->crop?->crop_type ?: 'Harvested Crop',
                    ],
                    'quantity' => (float) ($h->quantity ?: 0),
                    'unit' => $h->unit ?: 'kg',
                    'harvest_date' => $h->harvest_date ? $h->harvest_date->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Error in buildRecentHarvests: ' . $e->getMessage());
            return [];
        }
    }

    private function buildRecentOrders(Farmer $farmer, int $limit = 5): array
    {
        try {
            $orders = $this->getFarmerOrderQuery($farmer)
                ->with(['buyer', 'payment', 'refund'])
                ->latest()
                ->limit($limit)
                ->get();

            return $orders->map(function ($o) {
                $isCancelled = $o->getRawOriginal('status') === 'cancelled'
                    || !empty($o->cancelled_at)
                    || str_contains(strtolower($o->notes ?? ''), 'cancellation')
                    || ($o->refund && str_contains(strtolower($o->refund->reason ?? ''), 'cancellation'));

                $isPaid = in_array(strtolower($o->payment_status ?? ''), ['paid', 'completed'])
                    || ($o->payment && in_array(strtolower($o->payment->status ?? ''), ['completed', 'paid']))
                    || $o->escrow_held_at !== null;

                $hasActiveClaim = !$isCancelled && $o->refund && in_array(strtolower($o->refund->status ?? ''), ['requested', 'pending', 'under_review', 'in_review', 'dispute', 'disputed']);
                $isSettledRefund = !$isCancelled && (strtolower($o->status ?? '') === 'refunded'
                    || strtolower($o->payment_status ?? '') === 'refunded'
                    || ($o->refund && in_array(strtolower($o->refund->status ?? ''), ['approved', 'completed', 'refunded'])));

                if ($isCancelled) {
                    $statusDisplay = 'cancelled';
                    $paymentStatusDisplay = 'refunded';
                } else {
                    $statusDisplay = $isSettledRefund ? 'refunded' : ($hasActiveClaim ? ($o->refund->status === 'dispute' ? 'disputed' : 'refund_requested') : ($o->status ?: 'pending'));
                    $paymentStatusDisplay = $isSettledRefund ? 'refunded' : ($hasActiveClaim ? 'disputed' : ($isPaid ? 'paid' : 'pending'));
                }

                return [
                    'id' => $o->id,
                    'order_number' => $o->order_number ?: ('ORD-' . str_pad($o->id, 5, '0', STR_PAD_LEFT)),
                    'customer_name' => $o->buyer?->name ?? 'Customer',
                    'grand_total' => (float) $o->grand_total,
                    'status' => $statusDisplay,
                    'payment_status' => $paymentStatusDisplay,
                    'escrow_status' => $isCancelled ? 'refunded' : ($hasActiveClaim ? 'held_under_review' : ($o->escrow_status ?: ($isPaid ? 'held' : 'pending'))),
                    'has_active_refund_claim' => $hasActiveClaim,
                    'is_refund_settled' => $isSettledRefund,
                    'created_at' => $o->created_at ? $o->created_at->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Error in buildRecentOrders: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get farmer customer orders via dashboard orders endpoint.
     */
    public function customerOrders(Request $request)
    {
        return app(\App\Http\Controllers\Api\Farmer\OrderController::class)->index($request);
    }
}
