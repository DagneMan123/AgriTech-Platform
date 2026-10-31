<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BuyerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => $user,
            'total_orders' => Order::where('buyer_id', $user->id)->count(),
        ]);
    }

    /**
     * Get buyer's full personal and business profile details from database.
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        // Ensure Buyer profile record exists for this user
        $buyer = Buyer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'buyer_type' => 'wholesaler',
                'business_name' => $user->name ? $user->name . ' Agricultural Trading' : 'Commercial AgriBuyer',
                'business_phone' => $user->phone,
                'business_address' => $user->address ?: 'Addis Ababa, Ethiopia',
                'verification_status' => 'verified',
                'bio' => 'Commercial buyer sourcing wholesale grain, pulses, oilseeds, and regional agricultural produce across Ethiopia.'
            ]
        );

        // Calculate live metrics from database
        $totalOrders = Order::where('buyer_id', $user->id)->count();
        $completedOrders = Order::where('buyer_id', $user->id)->where('status', 'delivered')->count();
        $totalSpent = Order::where('buyer_id', $user->id)->where('status', '!=', 'cancelled')->sum('grand_total');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'region' => $user->region ?? 'Addis Ababa',
                    'zone' => $user->zone,
                    'woreda' => $user->woreda,
                    'address' => $user->address,
                    'is_active' => (bool)$user->is_active,
                    'profile_image' => $user->profile_image,
                    'created_at' => $user->created_at ? $user->created_at->toISOString() : null
                ],
                'buyer' => [
                    'id' => $buyer->id,
                    'buyer_type' => $buyer->buyer_type ?: 'wholesaler',
                    'business_name' => $buyer->business_name ?: $user->name,
                    'business_registration' => $buyer->business_registration ?: 'AGR-REG-' . (10000 + $user->id),
                    'tax_id' => $buyer->tax_id ?: 'TIN-00' . $user->id . '8910',
                    'business_address' => $buyer->business_address ?: $user->address,
                    'business_phone' => $buyer->business_phone ?: $user->phone,
                    'bio' => $buyer->bio,
                    'verification_status' => $buyer->verification_status ?: 'verified',
                    'is_premium' => (bool)$buyer->is_premium
                ],
                'stats' => [
                    'total_orders' => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'total_spent' => (float)$totalSpent,
                    'currency' => 'ETB'
                ]
            ]
        ]);
    }

    /**
     * Update buyer's personal and business profile details.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'region' => 'nullable|string|max:100',
            'zone' => 'nullable|string|max:100',
            'woreda' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            // Business profile fields
            'business_name' => 'nullable|string|max:255',
            'buyer_type' => 'nullable|string|max:100',
            'business_registration' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'business_address' => 'nullable|string|max:500',
            'business_phone' => 'nullable|string|max:30',
            'bio' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update User
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'region' => $request->region,
            'zone' => $request->zone,
            'woreda' => $request->woreda,
            'address' => $request->address,
        ]);

        // Update or create Buyer business record
        $buyer = Buyer::firstOrCreate(['user_id' => $user->id]);
        $buyer->update([
            'business_name' => $request->business_name ?: $request->name,
            'buyer_type' => $request->buyer_type ?: 'wholesaler',
            'business_registration' => $request->business_registration,
            'tax_id' => $request->tax_id,
            'business_address' => $request->business_address ?: $request->address,
            'business_phone' => $request->business_phone ?: $request->phone,
            'bio' => $request->bio,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Business profile successfully updated in database.',
            'data' => [
                'user' => $user->fresh(),
                'buyer' => $buyer->fresh()
            ]
        ]);
    }

    /**
     * Update buyer's password.
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided current password does not match our records.'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your account password has been successfully updated.'
        ]);
    }
}
