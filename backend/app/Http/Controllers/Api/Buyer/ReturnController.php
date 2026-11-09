<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    /**
     * Get buyer's returns & refunds
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $returns = Refund::where('requested_by_user_id', $userId)
            ->with(['order.items.product', 'order.farmer', 'payment'])
            ->latest()
            ->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $returns,
        ]);
    }

    /**
     * Submit a new return/refund claim
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string',
            'amount' => 'sometimes|numeric|min:0',
            'notes' => 'sometimes|nullable|string',
        ]);

        $userId = Auth::id();
        $order = Order::where('id', $validated['order_id'])
            ->where('buyer_id', $userId)
            ->firstOrFail();

        $payment = Payment::where('order_id', $order->id)->first();
        if (!$payment) {
            $payment = Payment::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'amount' => $order->grand_total ?? $order->subtotal ?? 0,
                'currency' => 'ETB',
                'payment_method' => $order->payment_method ?? 'telebirr',
                'status' => 'completed',
                'escrow_status' => $order->escrow_status ?? 'held',
                'escrow_held_at' => now(),
                'transaction_id' => 'PAY-REF-' . time() . '-' . rand(100, 999),
            ]);
        }

        $claimAmount = isset($validated['amount']) && (float)$validated['amount'] > 0
            ? (float)$validated['amount']
            : (float)($order->grand_total ?? $order->subtotal ?? 0);

        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'requested_by_user_id' => $userId,
            'amount' => $claimAmount,
            'reason' => $validated['reason'],
            'admin_notes' => $validated['notes'] ?? null,
            'status' => 'requested',
        ]);

        // Protect escrow funds from premature release during active dispute and synchronize order status
        $order->update([
            'status' => 'refund_requested',
            'escrow_status' => 'held'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Return request submitted successfully',
            'data' => $refund->load(['order.items.product', 'order.farmer', 'payment']),
        ], 201);
    }

    /**
     * Get details of a single return
     */
    public function show($id)
    {
        $userId = Auth::id();
        $refund = Refund::where('id', $id)
            ->where('requested_by_user_id', $userId)
            ->with(['order.items.product', 'order.farmer', 'payment'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $refund,
        ]);
    }

    /**
     * Cancel a pending return request
     */
    public function cancel($id)
    {
        $userId = Auth::id();
        $refund = Refund::where('id', $id)
            ->where('requested_by_user_id', $userId)
            ->firstOrFail();

        if ($refund->status !== 'requested') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending return requests can be cancelled',
            ], 422);
        }

        $refund->update([
            'status' => 'rejected',
            'admin_notes' => 'Cancelled by buyer',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Return request cancelled successfully',
            'data' => $refund->load(['order.items.product', 'order.farmer', 'payment']),
        ]);
    }
}
