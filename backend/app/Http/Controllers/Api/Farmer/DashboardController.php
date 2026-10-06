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
                return response()->json([
                    'message' => 'Unauthorized - user not authenticated',
                    'debug' => [
                        'has_auth_header' => $request->hasHeader('Authorization'),
                        'bearer_token' => $request->bearerToken() ? 'present' : 'missing'
                    ]
                ], 401);
            }

            return response()->json([
                'message' => 'Dashboard loaded successfully',
                'farmer' => [
                    'id' => 1,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'farmer_registration_number' => 'FRM-' . $user->id . '-' . time(),
                    'farm_name' => $user->name . ' Farm',
                    'region' => $user->region ?? 'Not Specified',
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
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Farmer Dashboard Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id ?? 'unknown',
            ]);
            
            return response()->json([
                'message' => 'Server error. Please try again later.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
