<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => $user,
            'total_orders' => \App\Models\Order::where('buyer_id', $user->id)->count(),
        ]);
    }
}
