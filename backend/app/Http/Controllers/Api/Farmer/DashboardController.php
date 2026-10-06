<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'debug' => [
                        'guard' => 'api',
                        'user_null' => true,
                        'token' => $request->bearerToken() ? 'present' : 'missing'
                    ]
                ], 401);
            }

            return response()->json([
                'message' => 'Success',
                'farmer' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ],
                'summary' => [
                    'total_farms' => 0,
                    'total_crops' => 0,
                    'total_products' => 0,
                    'total_orders' => 0,
                ],
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
}
