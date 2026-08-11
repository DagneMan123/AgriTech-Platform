<?php

namespace App\Http\Controllers\Api\Financial;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Get all transactions
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:payment,transfer,refund,deposit,withdrawal',
            'status' => 'sometimes|in:pending,completed,failed,cancelled',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
            'sort' => 'sometimes|in:newest,oldest,amount_high,amount_low',
        ]);

        $query = Transaction::where('financial_institution_id', Auth::id())
            ->with('user', 'payment');

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
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

        $transactions = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Create transaction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:payment,transfer,refund,deposit,withdrawal',
            'description' => 'required|string',
            'reference_number' => 'sometimes|string',
            'metadata' => 'sometimes|array',
        ]);

        $transaction = Transaction::create([
            'financial_institution_id' => Auth::id(),
            'user_id' => $validated['user_id'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'status' => 'pending',
            'description' => $validated['description'],
            'reference_number' => $validated['reference_number'] ?? uniqid('TXN_'),
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => $transaction,
        ], 201);
    }

    /**
     * Get transaction details
     */
    public function show(Transaction $transaction)
    {
        if ($transaction->financial_institution_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $transaction->load('user', 'payment');

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, Transaction $transaction)
    {
        if ($transaction->financial_institution_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,completed,failed,cancelled',
            'notes' => 'sometimes|string',
        ]);

        $transaction->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $transaction->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction status updated',
            'data' => $transaction,
        ]);
    }

    /**
     * Get transaction statistics
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

        $transactions = Transaction::where('financial_institution_id', Auth::id())
            ->where('created_at', '>=', $startDate)
            ->get();

        $stats = [
            'total_transactions' => $transactions->count(),
            'total_volume' => $transactions->sum('amount'),
            'average_transaction' => $transactions->count() > 0 ? $transactions->avg('amount') : 0,
            'by_type' => $transactions->groupBy('type')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ]),
            'by_status' => $transactions->groupBy('status')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ]),
            'success_rate' => $transactions->count() > 0 
                ? ($transactions->where('status', 'completed')->count() / $transactions->count()) * 100 
                : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get daily transaction report
     */
    public function dailyReport(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $report = Transaction::where('financial_institution_id', Auth::id())
            ->whereDate('created_at', $validated['date'])
            ->get();

        $summary = [
            'date' => $validated['date'],
            'total_count' => $report->count(),
            'total_amount' => $report->sum('amount'),
            'by_type' => $report->groupBy('type')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ]),
            'by_status' => $report->groupBy('status')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'total' => $items->sum('amount'),
                ]),
            'success_count' => $report->where('status', 'completed')->count(),
            'failed_count' => $report->where('status', 'failed')->count(),
            'pending_count' => $report->where('status', 'pending')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Export transactions
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,xlsx,pdf',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'type' => 'sometimes|in:payment,transfer,refund,deposit,withdrawal',
        ]);

        $query = Transaction::where('financial_institution_id', Auth::id())
            ->whereBetween('created_at', [
                $validated['date_from'],
                $validated['date_to'],
            ]);

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        $transactions = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Export prepared',
            'data' => [
                'total_records' => $transactions->count(),
                'format' => $validated['format'],
                'period' => [
                    'from' => $validated['date_from'],
                    'to' => $validated['date_to'],
                ],
            ],
        ]);
    }

    /**
     * Get transaction by reference number
     */
    public function findByReference(Request $request)
    {
        $validated = $request->validate([
            'reference_number' => 'required|string',
        ]);

        $transaction = Transaction::where('financial_institution_id', Auth::id())
            ->where('reference_number', $validated['reference_number'])
            ->with('user', 'payment')
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }
}
