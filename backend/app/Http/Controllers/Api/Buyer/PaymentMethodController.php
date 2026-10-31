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

        // Live database metrics: sum direct Chapa payments & escrow orders
        $paymentsQuery = Payment::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('order', fn($oq) => $oq->where('buyer_id', $user->id));
        })->whereIn('status', ['completed', 'paid']);
        $totalSpent = (float) $paymentsQuery->sum('amount');
        $completedCount = $paymentsQuery->count();

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

        // 1. Get all Chapa and digital payments for this buyer
        $payments = Payment::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('order', fn($oq) => $oq->where('buyer_id', $user->id));
        })
        ->with('order.items.product')
        ->latest()
        ->get()
        ->map(function ($p) {
            $item = $p->order?->items?->first()?->product?->name ?? 'Produce Escrow Payment';
            return [
                'id' => $p->order_id ?: $p->id,
                'payment_number' => $p->payment_number,
                'transaction_id' => $p->transaction_id,
                'reference' => $p->reference,
                'produce_name' => $item,
                'payment_gateway' => ucfirst($p->payment_gateway ?? 'Chapa'),
                'amount' => (float)$p->amount,
                'status' => $p->status,
                'created_at' => $p->created_at,
                'items' => [
                    ['product' => ['name' => $item]]
                ]
            ];
        });

        // 2. Get orders that don't have a direct payment record yet
        $paymentOrderIds = $payments->pluck('id')->filter()->toArray();
        $orders = Order::where('buyer_id', $user->id)
            ->whereNotIn('id', $paymentOrderIds)
            ->with(['items.product', 'farm'])
            ->latest()
            ->get()
            ->map(function ($o) {
                $item = $o->items?->first()?->product?->name ?? 'Produce Consignment';
                return [
                    'id' => $o->id,
                    'payment_number' => 'ORD-' . $o->id,
                    'transaction_id' => null,
                    'reference' => null,
                    'produce_name' => $item,
                    'payment_gateway' => 'Chapa Gateway',
                    'amount' => (float)($o->grand_total ?: $o->total_amount),
                    'status' => $o->status,
                    'created_at' => $o->created_at,
                    'items' => [
                        ['product' => ['name' => $item]]
                    ]
                ];
            });

        $allTransactions = $payments->concat($orders)->sortByDesc('created_at')->values();

        return response()->json([
            'success' => true,
            'data' => $allTransactions
        ]);
    }
}
