<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    /**
     * Get all registered payment methods for the authenticated buyer from PostgreSQL.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $methods = PaymentMethod::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // If the buyer has no payment method registered in the database yet,
        // create their primary Telebirr / CBE Birr account based on their verified phone
        if ($methods->isEmpty() && $user->phone) {
            $defaultMethod = PaymentMethod::create([
                'user_id' => $user->id,
                'type' => 'mobile_money',
                'account_number' => $user->phone,
                'account_holder' => $user->name,
                'bank_name' => 'Telebirr (Ethio Telecom)',
                'is_primary' => true,
                'is_active' => true,
            ]);

            $methods = collect([$defaultMethod]);
        }

        // Live database metrics
        $paymentsQuery = Payment::whereHas('order', fn($q) => $q->where('buyer_id', $user->id));
        $totalSpent = (float) $paymentsQuery->sum('amount');
        $completedCount = $paymentsQuery->where('status', 'completed')->count();

        return response()->json([
            'success' => true,
            'data' => $methods,
            'stats' => [
                'total_methods' => $methods->count(),
                'primary_method' => $methods->firstWhere('is_primary', true)?->bank_name ?? 'None',
                'total_spent' => $totalSpent,
                'completed_transactions' => $completedCount
            ]
        ]);
    }

    /**
     * Store a newly created payment method in the database.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:mobile_money,bank_transfer,card,cash',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'account_holder' => 'required|string|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $isPrimary = $request->boolean('is_primary');

        // If user has no existing methods, automatically make this one primary
        $existingCount = PaymentMethod::where('user_id', $user->id)->count();
        if ($existingCount === 0) {
            $isPrimary = true;
        }

        if ($isPrimary) {
            PaymentMethod::where('user_id', $user->id)->update(['is_primary' => false]);
        }

        $paymentMethod = PaymentMethod::create([
            'user_id' => $user->id,
            'type' => $request->input('type'),
            'bank_name' => $request->input('bank_name'),
            'account_number' => $request->input('account_number'),
            'account_holder' => $request->input('account_holder'),
            'is_primary' => $isPrimary,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method saved to database successfully',
            'data' => $paymentMethod
        ], 201);
    }

    /**
     * Set a payment method as primary in database.
     */
    public function setDefault(Request $request, $id)
    {
        $user = $request->user();

        $method = PaymentMethod::where('user_id', $user->id)->findOrFail($id);

        PaymentMethod::where('user_id', $user->id)->update(['is_primary' => false]);
        $method->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method set as primary successfully',
            'data' => $method
        ]);
    }

    /**
     * Remove a payment method from database.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $method = PaymentMethod::where('user_id', $user->id)->findOrFail($id);
        $wasPrimary = $method->is_primary;

        $method->delete();

        if ($wasPrimary) {
            $next = PaymentMethod::where('user_id', $user->id)->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully from database'
        ]);
    }

    /**
     * Get transaction history for orders and escrow payments belonging to this buyer.
     */
    public function transactions(Request $request)
    {
        $user = $request->user();

        $orders = Order::where('buyer_id', $user->id)
            ->with(['items.product', 'farm', 'payment'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
}
