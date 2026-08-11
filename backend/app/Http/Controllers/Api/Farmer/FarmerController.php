<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FarmerController extends Controller
{
    public function dashboard(Request $request)
    {
        $farmer = $request->user()->farmer;

        return response()->json([
            'total_farms' => $farmer->farms()->count(),
            'total_products' => $farmer->products()->count(),
            'total_orders' => $farmer->orders()->count(),
            'total_revenue' => $farmer->orders()
                ->where('status', 'delivered')
                ->sum('total_amount'),
            'pending_consultations' => $farmer->consultations()
                ->where('status', 'open')
                ->count(),
        ]);
    }
}
