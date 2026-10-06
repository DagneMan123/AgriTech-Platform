<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $summary = [
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

            $chartData = $this->getChartData();

            return response()->json([
                'message' => 'Success',
                'farmer' => ['id' => $user->id, 'name' => $user->name, 'role' => $user->role],
                'summary' => $summary,
                'recent_harvests' => [],
                'recent_orders' => [],
                'chart_data' => $chartData,
            ], 200);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Dashboard Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'message' => 'Server error',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function getChartData()
    {
        $labels = [];
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $labels[] = Carbon::now()->subDays($i)->format('M d');
            $data[] = 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Daily Revenue ($)',
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
}
