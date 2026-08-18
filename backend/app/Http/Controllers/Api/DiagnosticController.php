<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Farm;

class DiagnosticController extends Controller
{
    /**
     * Test authentication
     */
    public function auth(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'authenticated' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $farmer = Farmer::where('user_id', $user->id)->first();
        $farms = Farm::where('farmer_id', $user->id)->count();

        return response()->json([
            'authenticated' => true,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'farmer_exists' => !!$farmer,
            'farmer_id' => $farmer?->id,
            'farms_count' => $farms,
            'token_guard' => 'token',
            'message' => 'Authentication successful'
        ]);
    }

    /**
     * Check farms count for authenticated user
     */
    public function farmsCount(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Not authenticated'
            ], 401);
        }

        $farmsCount = Farm::where('farmer_id', $user->id)->count();
        $farmer = Farmer::where('user_id', $user->id)->first();

        return response()->json([
            'user_id' => $user->id,
            'user_role' => $user->role,
            'farms_count' => $farmsCount,
            'farmer_profile_exists' => !!$farmer,
            'message' => 'Farms count retrieved successfully'
        ]);
    }

    /**
     * Get dashboard data summary
     */
    public function dashboardSummary(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        if ($user->role !== 'farmer') {
            return response()->json(['error' => 'User is not a farmer'], 403);
        }

        $farmer = Farmer::where('user_id', $user->id)->first();
        
        if (!$farmer) {
            return response()->json([
                'error' => 'Farmer profile not found',
                'user_id' => $user->id,
                'message' => 'Please create a farmer profile first'
            ], 404);
        }

        return response()->json([
            'authenticated' => true,
            'farmer_exists' => true,
            'user_id' => $user->id,
            'farmer_id' => $farmer->id,
            'message' => 'All prerequisites met for dashboard access'
        ]);
    }
}
