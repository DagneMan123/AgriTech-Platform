<?php

namespace App\Http\Controllers\Api\Cooperative;

use App\Http\Controllers\Controller;
use App\Models\Cooperative;
use App\Models\CooperativeMember;
use App\Models\Farm;
use App\Models\CooperativeSale;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get cooperative dashboard overview
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $cooperative = Cooperative::where('user_id', $user->id)->first();

        if (!$cooperative) {
            return response()->json(['message' => 'Cooperative not found'], 404);
        }

        // Member statistics
        $totalMembers = CooperativeMember::where('cooperative_id', $cooperative->id)->count();
        $activeMembers = CooperativeMember::where('cooperative_id', $cooperative->id)
            ->where('status', 'active')
            ->count();

        // Farm statistics
        $totalMemberFarms = Farm::whereIn('user_id', function($query) use ($cooperative) {
            $query->select('user_id')
                ->from('cooperative_members')
                ->where('cooperative_id', $cooperative->id);
        })->count();

        $totalMemberArea = Farm::whereIn('user_id', function($query) use ($cooperative) {
            $query->select('user_id')
                ->from('cooperative_members')
                ->where('cooperative_id', $cooperative->id);
        })->sum('size_hectares');

        // Sales statistics
        $totalSales = CooperativeSale::where('cooperative_id', $cooperative->id)->sum('quantity');
        $totalSalesRevenue = CooperativeSale::where('cooperative_id', $cooperative->id)->sum('total_amount');

        // Bulk purchasing
        $totalBulkOrders = Order::where('cooperative_id', $cooperative->id)->count();
        $totalBulkSpent = Order::where('cooperative_id', $cooperative->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Recent members
        $recentMembers = CooperativeMember::where('cooperative_id', $cooperative->id)
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        // Recent sales
        $recentSales = CooperativeSale::where('cooperative_id', $cooperative->id)
            ->latest()
            ->limit(5)
            ->get();

        // Sales by month (database-agnostic)
        $salesByMonth = CooperativeSale::where('cooperative_id', $cooperative->id)
            ->whereDate('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(function($sale) {
                return $sale->created_at->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'month' => $group->first()->created_at->format('Y-m'),
                    'quantity' => $group->sum('quantity'),
                    'revenue' => $group->sum('total_amount'),
                ];
            })
            ->values();

        return response()->json([
            'cooperative' => $cooperative,
            'summary' => [
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'total_member_farms' => $totalMemberFarms,
                'total_member_area_hectares' => $totalMemberArea,
                'total_bulk_sales' => $totalSales,
                'total_sales_revenue' => $totalSalesRevenue,
                'total_bulk_orders' => $totalBulkOrders,
                'total_bulk_spent' => $totalBulkSpent,
                'average_farm_size' => $totalMemberFarms > 0 ? $totalMemberArea / $totalMemberFarms : 0,
            ],
            'recent_members' => $recentMembers,
            'recent_sales' => $recentSales,
            'sales_by_month' => $salesByMonth,
        ]);
    }

    /**
     * Get member management
     */
    public function members(Request $request)
    {
        $user = $request->user();
        $cooperative = Cooperative::where('user_id', $user->id)->first();

        if (!$cooperative) {
            return response()->json(['message' => 'Cooperative not found'], 404);
        }

        $status = $request->query('status');

        $query = CooperativeMember::where('cooperative_id', $cooperative->id)
            ->with(['user', 'farm']);

        if ($status) {
            $query->where('status', $status);
        }

        $members = $query->paginate(20);

        return response()->json($members);
    }

    /**
     * Get bulk purchasing
     */
    public function bulkPurchasing(Request $request)
    {
        $user = $request->user();
        $cooperative = Cooperative::where('user_id', $user->id)->first();

        if (!$cooperative) {
            return response()->json(['message' => 'Cooperative not found'], 404);
        }

        $orders = Order::where('cooperative_id', $cooperative->id)
            ->with(['items', 'supplier', 'delivery'])
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    /**
     * Get bulk sales
     */
    public function bulkSales(Request $request)
    {
        $user = $request->user();
        $cooperative = Cooperative::where('user_id', $user->id)->first();

        if (!$cooperative) {
            return response()->json(['message' => 'Cooperative not found'], 404);
        }

        $status = $request->query('status');

        $query = CooperativeSale::where('cooperative_id', $cooperative->id)
            ->with('buyer');

        if ($status) {
            $query->where('status', $status);
        }

        $sales = $query->latest()->paginate(20);

        return response()->json($sales);
    }

    /**
     * Get collection centers
     */
    public function collectionCenters(Request $request)
    {
        $user = $request->user();
        $cooperative = Cooperative::where('user_id', $user->id)->first();

        if (!$cooperative) {
            return response()->json(['message' => 'Cooperative not found'], 404);
        }

        $centers = $cooperative->collectionCenters()
            ->with(['stocks' => function($query) {
                $query->select('center_id', DB::raw('SUM(quantity) as total_quantity'));
            }])
            ->get()
            ->map(fn($center) => [
                'id' => $center->id,
                'name' => $center->name,
                'location' => $center->location,
                'capacity' => $center->capacity,
                'current_stock' => $center->stocks->sum('total_quantity') ?? 0,
                'utilization_percent' => ($center->stocks->sum('total_quantity') ?? 0) / $center->capacity * 100,
            ]);

        return response()->json([
            'total_centers' => $centers->count(),
            'centers' => $centers,
        ]);
    }

    /**
     * Get financial reports
     */
    public function financialReports(Request $request)
    {
        $user = $request->user();
        $cooperative = Cooperative::where('user_id', $user->id)->first();

        if (!$cooperative) {
            return response()->json(['message' => 'Cooperative not found'], 404);
        }

        $period = $request->query('period', 30);

        // Sales revenue
        $salesRevenue = CooperativeSale::where('cooperative_id', $cooperative->id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays($period))
            ->sum('total_amount');

        // Purchase expenses
        $purchaseExpenses = Order::where('cooperative_id', $cooperative->id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays($period))
            ->sum('total_amount');

        // Net profit
        $netProfit = $salesRevenue - $purchaseExpenses;

        // Top performing members (by contribution)
        $topMembers = CooperativeMember::where('cooperative_id', $cooperative->id)
            ->with(['user', 'sales' => function($query) use ($period) {
                $query->where('status', 'completed')
                    ->whereDate('created_at', '>=', now()->subDays($period));
            }])
            ->get()
            ->sortByDesc(fn($member) => $member->sales->sum('total_amount'))
            ->take(10);

        return response()->json([
            'period_days' => $period,
            'sales_revenue' => $salesRevenue,
            'purchase_expenses' => $purchaseExpenses,
            'net_profit' => $netProfit,
            'profit_margin_percent' => $salesRevenue > 0 ? ($netProfit / $salesRevenue) * 100 : 0,
            'top_members' => $topMembers,
        ]);
    }

    /**
     * Get member statistics
     */
    public function memberStatistics(Request $request)
    {
        $user = $request->user();
        $cooperative = Cooperative::where('user_id', $user->id)->first();

        if (!$cooperative) {
            return response()->json(['message' => 'Cooperative not found'], 404);
        }

        $members = CooperativeMember::where('cooperative_id', $cooperative->id)
            ->with('user')
            ->get();

        $membersByStatus = $members->groupBy('status')->map->count();

        // Members by joining date (last 6 months) - database-agnostic
        $membersByMonth = CooperativeMember::where('cooperative_id', $cooperative->id)
            ->whereDate('joined_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(function($member) {
                return $member->joined_at->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'month' => $group->first()->joined_at->format('Y-m'),
                    'members' => $group->count(),
                ];
            })
            ->values();

        return response()->json([
            'total_members' => $members->count(),
            'members_by_status' => $membersByStatus,
            'members_by_month' => $membersByMonth,
        ]);
    }
}
