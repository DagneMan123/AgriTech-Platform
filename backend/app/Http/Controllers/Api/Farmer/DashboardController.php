<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Return minimal dashboard data
            return response()->json([
                'farmer' => [
                    'id' => 1,
                    'user_id' => $user->id,
                    'farmer_registration_number' => 'FRM-' . $user->id . '-' . time(),
                    'farm_name' => $user->name . ' Farm',
                    'region' => 'Not Specified',
                ],
                'summary' => [
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
                ],
                'farms' => [],
                'crops' => [],
                'recent_products' => [],
                'recent_orders' => [],
                'recent_harvests' => [],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Dashboard error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'message' => 'Server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
