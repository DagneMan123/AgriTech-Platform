<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Get sales report
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date')) 
            : Carbon::now()->subMonth();
        
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date')) 
            : Carbon::now();

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])->get();

        $report = [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'total_orders' => $orders->count(),
            'total_sales' => $orders->sum('total_amount'),
            'average_order_value' => $orders->count() > 0 ? $orders->sum('total_amount') / $orders->count() : 0,
            'orders_by_status' => $orders->groupBy('status')->map->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get revenue report
     */
    public function revenue(Request $request)
    {
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date')) 
            : Carbon::now()->subMonth();
        
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date')) 
            : Carbon::now();

        $payments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();

        $report = [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'total_revenue' => $payments->sum('amount'),
            'total_transactions' => $payments->count(),
            'average_transaction' => $payments->count() > 0 ? $payments->sum('amount') / $payments->count() : 0,
            'by_payment_method' => $payments->groupBy('payment_method')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('amount'),
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get user growth report
     */
    public function userGrowth(Request $request)
    {
        $months = $request->get('months', 12);
        $data = [];

        for ($i = $months; $i > 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = User::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            
            $data[] = [
                'month' => $month->format('Y-m'),
                'users' => $count,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get marketplace overview
     */
    public function marketplaceOverview()
    {
        $overview = [
            'total_products' => Product::count(),
            'total_categories' => Product::distinct('category')->count(),
            'active_sellers' => User::whereHas('roles', function ($q) {
                $q->where('name', 'farmer');
            })->count(),
            'total_transactions' => Order::count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $overview,
        ]);
    }

    /**
     * Get custom report
     */
    public function custom(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:sales,revenue,users,products,orders',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'group_by' => 'sometimes|in:day,week,month',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $report = match ($validated['type']) {
            'sales' => $this->generateSalesReport($startDate, $endDate),
            'revenue' => $this->generateRevenueReport($startDate, $endDate),
            'users' => $this->generateUserReport($startDate, $endDate),
            'products' => $this->generateProductReport($startDate, $endDate),
            'orders' => $this->generateOrderReport($startDate, $endDate),
        };

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    private function generateSalesReport($startDate, $endDate)
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])->get();
        return [
            'total_orders' => $orders->count(),
            'total_sales' => $orders->sum('total_amount'),
            'average_order_value' => $orders->count() > 0 ? $orders->sum('total_amount') / $orders->count() : 0,
        ];
    }

    private function generateRevenueReport($startDate, $endDate)
    {
        $payments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')->get();
        return [
            'total_revenue' => $payments->sum('amount'),
            'total_transactions' => $payments->count(),
        ];
    }

    private function generateUserReport($startDate, $endDate)
    {
        $users = User::whereBetween('created_at', [$startDate, $endDate])->get();
        return [
            'new_users' => $users->count(),
            'by_role' => $users->with('roles')->get()->groupBy(fn ($u) => $u->roles->first()?->name)->map->count(),
        ];
    }

    private function generateProductReport($startDate, $endDate)
    {
        $products = Product::whereBetween('created_at', [$startDate, $endDate])->get();
        return [
            'new_products' => $products->count(),
            'by_category' => $products->groupBy('category')->map->count(),
        ];
    }

    private function generateOrderReport($startDate, $endDate)
    {
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])->get();
        return [
            'total_orders' => $orders->count(),
            'by_status' => $orders->groupBy('status')->map->count(),
            'total_value' => $orders->sum('total_amount'),
        ];
    }
}
