<?php

namespace App\Http\Controllers\Api\Financial;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Process payment
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:ETB,USD,EUR',
            'payment_method' => 'required|in:bank_transfer,mobile_money,credit_card,cheque',
            'reference_id' => 'required|string',
            'reference_type' => 'required|in:loan_repayment,insurance_premium,transaction,other',
            'description' => 'sometimes|string',
            'recipient_id' => 'sometimes|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'payer_id' => Auth::id(),
                'recipient_id' => $validated['recipient_id'] ?? null,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'payment_method' => $validated['payment_method'],
                'reference_id' => $validated['reference_id'],
                'reference_type' => $validated['reference_type'],
                'description' => $validated['description'] ?? null,
                'status' => 'pending',
                'transaction_id' => uniqid('TXN_'),
            ]);

            $transaction = Transaction::create([
                'financial_institution_id' => Auth::id(),
                'payment_id' => $payment->id,
                'amount' => $validated['amount'],
                'type' => 'payment',
                'status' => 'pending',
                'description' => $validated['description'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment' => $payment,
                    'transaction' => $transaction,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get payment history
     */
    public function history(Request $request)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,completed,failed,cancelled',
            'type' => 'sometimes|in:payment,refund,transfer',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
            'sort' => 'sometimes|in:newest,oldest,amount_high,amount_low',
        ]);

        $query = Payment::where('payer_id', Auth::id())
            ->orWhere('recipient_id', Auth::id())
            ->with(['payer', 'recipient']);

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (isset($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (isset($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('created_at', 'desc'),
                'oldest' => $query->orderBy('created_at', 'asc'),
                'amount_high' => $query->orderBy('amount', 'desc'),
                'amount_low' => $query->orderBy('amount', 'asc'),
            };
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $payments = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Get payment details
     */
    public function show(Payment $payment)
    {
        if ($payment->payer_id !== Auth::id() && $payment->recipient_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $payment->load(['payer', 'recipient', 'transaction']);

        return response()->json([
            'success' => true,
            'data' => $payment,
        ]);
    }

    /**
     * Verify payment
     */
    public function verify(Request $request, Payment $payment)
    {
        if ($payment->recipient_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:completed,failed,cancelled',
            'notes' => 'sometimes|string',
        ]);

        try {
            DB::beginTransaction();

            $payment->update([
                'status' => $validated['status'],
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'verification_notes' => $validated['notes'] ?? null,
            ]);

            if ($payment->transaction) {
                $payment->transaction->update(['status' => $validated['status']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment verified',
                'data' => $payment,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Refund payment
     */
    public function refund(Request $request, Payment $payment)
    {
        if ($payment->recipient_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($payment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Can only refund completed payments',
            ], 422);
        }

        $validated = $request->validate([
            'refund_amount' => 'sometimes|numeric|min:0.01',
            'reason' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $refundAmount = $validated['refund_amount'] ?? $payment->amount;

            $refund = Payment::create([
                'payer_id' => $payment->recipient_id,
                'recipient_id' => $payment->payer_id,
                'amount' => $refundAmount,
                'currency' => $payment->currency,
                'payment_method' => $payment->payment_method,
                'reference_id' => $payment->reference_id,
                'reference_type' => 'refund',
                'description' => 'Refund: ' . $validated['reason'],
                'status' => 'completed',
                'related_payment_id' => $payment->id,
                'transaction_id' => uniqid('REFUND_'),
            ]);

            Transaction::create([
                'financial_institution_id' => Auth::id(),
                'payment_id' => $refund->id,
                'amount' => $refundAmount,
                'type' => 'refund',
                'status' => 'completed',
                'description' => 'Refund for payment: ' . $payment->transaction_id,
            ]);

            $payment->update(['status' => 'refunded']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => $refund,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Refund processing failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get payment reconciliation
     */
    public function reconciliation(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);

        $payments = Payment::where('financial_institution_id', Auth::id())
            ->whereBetween('created_at', [
                $validated['date_from'],
                $validated['date_to'],
            ])
            ->with('transaction')
            ->get();

        $reconciliation = [
            'period' => [
                'from' => $validated['date_from'],
                'to' => $validated['date_to'],
            ],
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'by_status' => $payments->groupBy('status')
                ->map(function ($items) {
                    return [
                        'count' => $items->count(),
                        'amount' => $items->sum('amount'),
                    ];
                }),
            'by_method' => $payments->groupBy('payment_method')
                ->map(function ($items) {
                    return [
                        'count' => $items->count(),
                        'amount' => $items->sum('amount'),
                    ];
                }),
            'discrepancies' => $this->findDiscrepancies($payments),
        ];

        return response()->json([
            'success' => true,
            'data' => $reconciliation,
        ]);
    }

    /**
     * Find payment discrepancies
     */
    private function findDiscrepancies($payments)
    {
        $discrepancies = [];

        foreach ($payments as $payment) {
            if ($payment->transaction) {
                if ($payment->amount != $payment->transaction->amount) {
                    $discrepancies[] = [
                        'payment_id' => $payment->id,
                        'type' => 'amount_mismatch',
                        'expected' => $payment->amount,
                        'actual' => $payment->transaction->amount,
                    ];
                }

                if ($payment->status != $payment->transaction->status) {
                    $discrepancies[] = [
                        'payment_id' => $payment->id,
                        'type' => 'status_mismatch',
                        'expected' => $payment->status,
                        'actual' => $payment->transaction->status,
                    ];
                }
            }
        }

        return $discrepancies;
    }

    /**
     * Get payment statistics
     */
    public function statistics(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:day,week,month,quarter,year',
        ]);

        $period = $validated['period'] ?? 'month';
        $startDate = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
        };

        $payments = Payment::where('financial_institution_id', Auth::id())
            ->where('created_at', '>=', $startDate)
            ->get();

        $stats = [
            'total_transactions' => $payments->count(),
            'total_volume' => $payments->sum('amount'),
            'average_transaction' => $payments->count() > 0 ? $payments->avg('amount') : 0,
            'by_method' => $payments->groupBy('payment_method')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ]),
            'success_rate' => $payments->count() > 0 
                ? ($payments->where('status', 'completed')->count() / $payments->count()) * 100 
                : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
