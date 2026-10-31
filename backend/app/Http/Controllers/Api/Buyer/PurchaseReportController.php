<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TransportRequest;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseReportController extends Controller
{
    /**
     * Get comprehensive purchase reports and analytical metrics for the buyer.
     */
    public function index(Request $request)
    {
        try {
            $buyerId = Auth::id();
            
            // Base query for this authenticated buyer
            $baseQuery = Order::where('buyer_id', $buyerId)
                ->with(['items.product.category', 'farmer.user', 'payment', 'delivery']);

            // 1. Period / Date Filtering
            $period = $request->get('period', 'all');
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');

            if ($fromDate && $toDate) {
                $baseQuery->whereBetween('created_at', [
                    Carbon::parse($fromDate)->startOfDay(),
                    Carbon::parse($toDate)->endOfDay()
                ]);
            } elseif ($period === '7d') {
                $baseQuery->where('created_at', '>=', Carbon::now()->subDays(7));
            } elseif ($period === '30d') {
                $baseQuery->where('created_at', '>=', Carbon::now()->subDays(30));
            } elseif ($period === '90d') {
                $baseQuery->where('created_at', '>=', Carbon::now()->subDays(90));
            } elseif ($period === 'year') {
                $baseQuery->where('created_at', '>=', Carbon::now()->startOfYear());
            }

            // 2. Status Filter
            if ($request->filled('status') && $request->status !== 'all') {
                $baseQuery->where('status', $request->status);
            }

            // 3. Payment Status Filter
            if ($request->filled('payment_status') && $request->payment_status !== 'all') {
                $baseQuery->where('payment_status', $request->payment_status);
            }

            // 4. Keyword Search
            if ($request->filled('search')) {
                $search = trim($request->search);
                $baseQuery->where(function ($q) use ($search) {
                    $q->where('order_number', 'ilike', "%{$search}%")
                      ->orWhere('payment_method', 'ilike', "%{$search}%")
                      ->orWhere('delivery_address', 'ilike', "%{$search}%")
                      ->orWhere('notes', 'ilike', "%{$search}%")
                      ->orWhereHas('farmer.user', function ($fq) use ($search) {
                          $fq->where('name', 'ilike', "%{$search}%");
                      })
                      ->orWhereHas('items.product', function ($pq) use ($search) {
                          $pq->where('name', 'ilike', "%{$search}%");
                      });
                });
            }

            $orders = $baseQuery->latest()->get();

            // Transform orders list for report table
            $transformedOrders = $orders->map(function ($order) {
                $itemNames = $order->items->map(fn($i) => $i->product->name ?? 'Produce Item')->join(', ');
                $totalQty = $order->items->sum('quantity');
                $qtyUnits = $order->items->map(fn($i) => $i->product->quantity_unit ?? 'kg')->unique()->join(', ');

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'created_at' => $order->created_at->toISOString(),
                    'date_formatted' => $order->created_at->format('M d, Y - H:i'),
                    'seller_name' => $order->farmer->user->name ?? 'Regional Cooperative / Farmer',
                    'seller_phone' => $order->farmer->user->phone ?? null,
                    'items_summary' => $itemNames ?: 'Agricultural Produce',
                    'total_quantity' => (float)$totalQty,
                    'quantity_unit' => $qtyUnits ?: 'kg',
                    'subtotal' => (float)$order->subtotal,
                    'delivery_fee' => (float)$order->delivery_fee,
                    'tax' => (float)$order->tax,
                    'discount' => (float)$order->discount,
                    'grand_total' => (float)$order->grand_total,
                    'status' => $order->status,
                    'status_label' => $order->status_label ?? ucfirst($order->status),
                    'payment_status' => $order->payment_status ?: 'pending',
                    'payment_method' => $order->payment_method ?: 'Chapa Escrow',
                    'payment_reference' => $order->payment_reference ?: ($order->payment->reference ?? null),
                    'delivery_address' => $order->delivery_address ?: 'Addis Ababa Central Produce Depot',
                    'items' => $order->items->map(fn($i) => [
                        'id' => $i->id,
                        'product_name' => $i->product->name ?? 'Produce Item',
                        'category' => $i->product->category->name ?? 'Crop',
                        'quantity' => (float)$i->quantity,
                        'price' => (float)$i->price,
                        'subtotal' => (float)$i->subtotal,
                        'unit' => $i->product->quantity_unit ?? 'kg'
                    ])
                ];
            });

            // Calculate Aggregate Summary KPIs
            $totalSpend = $orders->where('status', '!=', 'cancelled')->sum('grand_total');
            $totalOrdersCount = $orders->count();
            $completedCount = $orders->where('status', 'delivered')->count();
            $pendingCount = $orders->whereIn('status', ['pending', 'processing', 'shipped'])->count();
            $cancelledCount = $orders->where('status', 'cancelled')->count();
            $avgOrderValue = $totalOrdersCount > 0 ? ($totalSpend / $totalOrdersCount) : 0;
            $totalProduceUnits = $orders->flatMap(fn($o) => $o->items)->sum('quantity');

            // Category Distribution Breakdown
            $categorySpend = [];
            foreach ($orders as $order) {
                if ($order->status === 'cancelled') continue;
                foreach ($order->items as $item) {
                    $catName = $item->product->category->name ?? 'Other Produce';
                    $categorySpend[$catName] = ($categorySpend[$catName] ?? 0) + (float)$item->subtotal;
                }
            }

            $categoriesBreakdown = [];
            foreach ($categorySpend as $category => $amount) {
                $categoriesBreakdown[] = [
                    'category' => $category,
                    'amount' => round($amount, 2),
                    'percentage' => $totalSpend > 0 ? round(($amount / $totalSpend) * 100, 1) : 0
                ];
            }
            usort($categoriesBreakdown, fn($a, $b) => $b['amount'] <=> $a['amount']);

            // Payment Channel Breakdown
            $paymentBreakdown = [];
            foreach ($orders as $order) {
                if ($order->status === 'cancelled') continue;
                $method = $order->payment_method ?: 'Chapa Escrow';
                $paymentBreakdown[$method] = ($paymentBreakdown[$method] ?? 0) + (float)$order->grand_total;
            }

            $paymentMethodStats = [];
            foreach ($paymentBreakdown as $method => $amount) {
                $paymentMethodStats[] = [
                    'method' => $method,
                    'amount' => round($amount, 2),
                    'percentage' => $totalSpend > 0 ? round(($amount / $totalSpend) * 100, 1) : 0
                ];
            }

            // Monthly Spend Trend (Last 6 Months)
            $monthlyTrend = [];
            for ($i = 5; $i >= 0; $i--) {
                $mStart = Carbon::now()->subMonths($i)->startOfMonth();
                $mEnd = Carbon::now()->subMonths($i)->endOfMonth();
                $mKey = $mStart->format('M Y');

                $mOrders = $orders->filter(function ($o) use ($mStart, $mEnd) {
                    return $o->created_at >= $mStart && $o->created_at <= $mEnd && $o->status !== 'cancelled';
                });

                $monthlyTrend[] = [
                    'month' => $mKey,
                    'spend' => round($mOrders->sum('grand_total'), 2),
                    'orders_count' => $mOrders->count()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_spend' => round($totalSpend, 2),
                        'total_orders' => $totalOrdersCount,
                        'completed_orders' => $completedCount,
                        'pending_orders' => $pendingCount,
                        'cancelled_orders' => $cancelledCount,
                        'average_order_value' => round($avgOrderValue, 2),
                        'total_produce_units' => round($totalProduceUnits, 2),
                        'currency' => 'ETB'
                    ],
                    'orders' => $transformedOrders,
                    'categories' => $categoriesBreakdown,
                    'payment_methods' => $paymentMethodStats,
                    'monthly_trend' => $monthlyTrend
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate purchase report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dedicated Expense Analysis breakdown for buyer.
     * Evaluates cost structures, freight logistics expenses, tax expenditures, and savings.
     */
    public function expenses(Request $request)
    {
        try {
            $buyerId = Auth::id();

            // 1. Query Orders
            $orderQuery = Order::where('buyer_id', $buyerId)
                ->where('status', '!=', 'cancelled')
                ->with(['items.product.category', 'farmer.user', 'delivery']);

            // 2. Query Freight Transport Requests
            $freightQuery = TransportRequest::where('buyer_id', $buyerId)
                ->where('status', '!=', 'cancelled');

            // Period Filter
            $period = $request->get('period', 'all');
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');

            if ($fromDate && $toDate) {
                $start = Carbon::parse($fromDate)->startOfDay();
                $end = Carbon::parse($toDate)->endOfDay();
                $orderQuery->whereBetween('created_at', [$start, $end]);
                $freightQuery->whereBetween('created_at', [$start, $end]);
            } elseif ($period === '7d') {
                $sub = Carbon::now()->subDays(7);
                $orderQuery->where('created_at', '>=', $sub);
                $freightQuery->where('created_at', '>=', $sub);
            } elseif ($period === '30d') {
                $sub = Carbon::now()->subDays(30);
                $orderQuery->where('created_at', '>=', $sub);
                $freightQuery->where('created_at', '>=', $sub);
            } elseif ($period === '90d') {
                $sub = Carbon::now()->subDays(90);
                $orderQuery->where('created_at', '>=', $sub);
                $freightQuery->where('created_at', '>=', $sub);
            } elseif ($period === 'year') {
                $sub = Carbon::now()->startOfYear();
                $orderQuery->where('created_at', '>=', $sub);
                $freightQuery->where('created_at', '>=', $sub);
            }

            $orders = $orderQuery->get();
            $freights = $freightQuery->get();

            // Cost components calculation
            $produceSubtotal = (float)$orders->sum('subtotal');
            $orderDeliveryFees = (float)$orders->sum('delivery_fee');
            $directFreightCost = (float)$freights->sum('estimated_cost');
            $totalLogistics = $orderDeliveryFees + $directFreightCost;
            $taxes = (float)$orders->sum('tax');
            $discounts = (float)$orders->sum('discount');
            $totalExpense = $produceSubtotal + $totalLogistics + $taxes;

            $savingsRate = ($totalExpense + $discounts) > 0
                ? round(($discounts / ($totalExpense + $discounts)) * 100, 1)
                : 0.0;

            // Cost structure breakdown
            $costStructure = [
                [
                    'name' => 'Raw Produce & Commodities',
                    'amount' => round($produceSubtotal, 2),
                    'percentage' => $totalExpense > 0 ? round(($produceSubtotal / $totalExpense) * 100, 1) : 0,
                    'color' => '#2563eb' // blue-600
                ],
                [
                    'name' => 'Logistics, Freight & Transport',
                    'amount' => round($totalLogistics, 2),
                    'percentage' => $totalExpense > 0 ? round(($totalLogistics / $totalExpense) * 100, 1) : 0,
                    'color' => '#10b981' // emerald-500
                ],
                [
                    'name' => 'Taxes & Regulatory Escrow',
                    'amount' => round($taxes, 2),
                    'percentage' => $totalExpense > 0 ? round(($taxes / $totalExpense) * 100, 1) : 0,
                    'color' => '#8b5cf6' // purple-500
                ]
            ];

            // Category Expense Breakdown
            $categoryMap = [];
            foreach ($orders as $o) {
                foreach ($o->items as $item) {
                    $cat = $item->product->category->name ?? 'General Agriculture';
                    $categoryMap[$cat] = ($categoryMap[$cat] ?? 0) + (float)$item->subtotal;
                }
            }

            $categoriesList = [];
            foreach ($categoryMap as $cat => $amt) {
                $categoriesList[] = [
                    'category' => $cat,
                    'amount' => round($amt, 2),
                    'percentage' => $produceSubtotal > 0 ? round(($amt / $produceSubtotal) * 100, 1) : 0
                ];
            }
            usort($categoriesList, fn($a, $b) => $b['amount'] <=> $a['amount']);

            // Top Supplier Spend Distribution
            $supplierMap = [];
            foreach ($orders as $o) {
                $sName = $o->farmer->user->name ?? 'Regional Farming Cooperative';
                if (!isset($supplierMap[$sName])) {
                    $supplierMap[$sName] = ['name' => $sName, 'orders_count' => 0, 'amount' => 0];
                }
                $supplierMap[$sName]['orders_count']++;
                $supplierMap[$sName]['amount'] += (float)$o->grand_total;
            }
            $supplierSpend = array_values($supplierMap);
            usort($supplierSpend, fn($a, $b) => $b['amount'] <=> $a['amount']);

            // Monthly Trend Breakdown
            $monthlyExpenses = [];
            for ($i = 5; $i >= 0; $i--) {
                $mStart = Carbon::now()->subMonths($i)->startOfMonth();
                $mEnd = Carbon::now()->subMonths($i)->endOfMonth();
                $mLabel = $mStart->format('M Y');

                $mOrders = $orders->filter(fn($o) => $o->created_at >= $mStart && $o->created_at <= $mEnd);
                $mFreights = $freights->filter(fn($f) => $f->created_at >= $mStart && $f->created_at <= $mEnd);

                $mProduce = (float)$mOrders->sum('subtotal');
                $mTrans = (float)$mOrders->sum('delivery_fee') + (float)$mFreights->sum('estimated_cost');
                $mTax = (float)$mOrders->sum('tax');
                $mTotal = $mProduce + $mTrans + $mTax;

                $monthlyExpenses[] = [
                    'month' => $mLabel,
                    'produce_amount' => round($mProduce, 2),
                    'logistics_amount' => round($mTrans, 2),
                    'tax_amount' => round($mTax, 2),
                    'total_expense' => round($mTotal, 2),
                    'orders_count' => $mOrders->count() + $mFreights->count()
                ];
            }

            // Expense Ledger List (combined orders & transport freight)
            $expenseTransactions = collect();

            foreach ($orders as $o) {
                $expenseTransactions->push([
                    'id' => 'ORD-' . $o->id,
                    'type' => 'Produce Purchase',
                    'reference' => $o->order_number,
                    'description' => $o->items->map(fn($i) => $i->product->name ?? 'Crop')->join(', ') ?: 'Commodity Order',
                    'date' => $o->created_at->format('M d, Y'),
                    'category' => $o->items->first()->product->category->name ?? 'Commodities',
                    'payee' => $o->farmer->user->name ?? 'Producer',
                    'produce_cost' => (float)$o->subtotal,
                    'logistics_cost' => (float)$o->delivery_fee,
                    'tax' => (float)$o->tax,
                    'total' => (float)$o->grand_total,
                    'payment_method' => $o->payment_method ?: 'Chapa Escrow',
                    'status' => $o->status
                ]);
            }

            foreach ($freights as $f) {
                $expenseTransactions->push([
                    'id' => 'FRQ-' . $f->id,
                    'type' => 'Logistics Freight',
                    'reference' => 'TRQ-' . str_pad($f->id, 5, '0', STR_PAD_LEFT),
                    'description' => 'Produce Freight to ' . ($f->delivery_location ?: 'Warehouse'),
                    'date' => $f->created_at->format('M d, Y'),
                    'category' => 'Transport',
                    'payee' => 'Freight Carrier',
                    'produce_cost' => 0.0,
                    'logistics_cost' => (float)$f->estimated_cost,
                    'tax' => 0.0,
                    'total' => (float)$f->estimated_cost,
                    'payment_method' => 'Transport Escrow',
                    'status' => $f->status
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_expense' => round($totalExpense, 2),
                        'produce_subtotal' => round($produceSubtotal, 2),
                        'logistics_total' => round($totalLogistics, 2),
                        'taxes_and_fees' => round($taxes, 2),
                        'discounts_saved' => round($discounts, 2),
                        'savings_rate' => $savingsRate,
                        'total_transactions' => $expenseTransactions->count(),
                        'currency' => 'ETB'
                    ],
                    'cost_structure' => $costStructure,
                    'categories' => $categoriesList,
                    'suppliers' => array_slice($supplierSpend, 0, 5),
                    'monthly_expenses' => $monthlyExpenses,
                    'transactions' => $expenseTransactions->values()
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate expense analysis: ' . $e->getMessage()
            ], 500);
        }
    }
}
