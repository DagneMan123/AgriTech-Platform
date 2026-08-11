<?php

namespace App\Http\Controllers\Api\Financial;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    /**
     * Get all loans
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,approved,active,repaid,defaulted',
            'sort' => 'sometimes|in:newest,oldest,amount_high,amount_low',
            'search' => 'sometimes|string',
        ]);

        $query = Loan::where('lender_id', Auth::id())->with('borrower');

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['search'])) {
            $query->whereHas('borrower', function ($q) use ($validated) {
                $q->where('name', 'ilike', '%' . $validated['search'] . '%')
                    ->orWhere('email', 'ilike', '%' . $validated['search'] . '%');
            });
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('created_at', 'desc'),
                'oldest' => $query->orderBy('created_at', 'asc'),
                'amount_high' => $query->orderBy('loan_amount', 'desc'),
                'amount_low' => $query->orderBy('loan_amount', 'asc'),
            };
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $loans = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $loans,
        ]);
    }

    /**
     * Create new loan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_id' => 'required|exists:users,id',
            'loan_amount' => 'required|numeric|min:1000',
            'duration_months' => 'required|integer|min:1|max:120',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'purpose' => 'required|string|max:500',
            'collateral_description' => 'sometimes|string',
            'disbursement_date' => 'required|date|after:today',
        ]);

        // Verify borrower exists and is a farmer
        $borrower = User::findOrFail($validated['borrower_id']);
        if ($borrower->role !== 'farmer') {
            return response()->json([
                'success' => false,
                'message' => 'Loan can only be issued to farmers',
            ], 422);
        }

        $loan = Loan::create([
            'lender_id' => Auth::id(),
            'borrower_id' => $validated['borrower_id'],
            'loan_amount' => $validated['loan_amount'],
            'duration_months' => $validated['duration_months'],
            'interest_rate' => $validated['interest_rate'],
            'purpose' => $validated['purpose'],
            'collateral_description' => $validated['collateral_description'] ?? null,
            'disbursement_date' => $validated['disbursement_date'],
            'due_date' => now()->addMonths($validated['duration_months']),
            'status' => 'pending',
            'remaining_balance' => $validated['loan_amount'],
            'amount_repaid' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loan created successfully',
            'data' => $loan,
        ], 201);
    }

    /**
     * Get loan details
     */
    public function show(Loan $loan)
    {
        if ($loan->lender_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $loan->load(['borrower', 'repayments', 'documents']);

        return response()->json([
            'success' => true,
            'data' => $loan,
        ]);
    }

    /**
     * Update loan
     */
    public function update(Request $request, Loan $loan)
    {
        if ($loan->lender_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($loan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update pending loans',
            ], 422);
        }

        $validated = $request->validate([
            'loan_amount' => 'sometimes|numeric|min:1000',
            'duration_months' => 'sometimes|integer|min:1|max:120',
            'interest_rate' => 'sometimes|numeric|min:0|max:100',
            'purpose' => 'sometimes|string|max:500',
            'collateral_description' => 'sometimes|string',
        ]);

        $loan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Loan updated successfully',
            'data' => $loan,
        ]);
    }

    /**
     * Approve loan
     */
    public function approve(Request $request, Loan $loan)
    {
        if ($loan->lender_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($loan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Loan is not pending',
            ], 422);
        }

        $validated = $request->validate([
            'approved_amount' => 'sometimes|numeric|min:1000',
            'notes' => 'sometimes|string',
        ]);

        $loan->update([
            'status' => 'approved',
            'approved_amount' => $validated['approved_amount'] ?? $loan->loan_amount,
            'approved_date' => now(),
            'approval_notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loan approved successfully',
            'data' => $loan,
        ]);
    }

    /**
     * Disburse loan
     */
    public function disburse(Request $request, Loan $loan)
    {
        if ($loan->lender_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($loan->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Loan must be approved before disbursement',
            ], 422);
        }

        $validated = $request->validate([
            'disbursement_method' => 'required|in:bank_transfer,cash,check',
            'account_number' => 'sometimes|string',
            'notes' => 'sometimes|string',
        ]);

        $loan->update([
            'status' => 'active',
            'disbursement_date' => now(),
            'disbursement_method' => $validated['disbursement_method'],
            'disbursement_notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loan disbursed successfully',
            'data' => $loan,
        ]);
    }

    /**
     * Record repayment
     */
    public function recordRepayment(Request $request, Loan $loan)
    {
        if ($loan->lender_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,check',
            'reference_number' => 'sometimes|string',
            'notes' => 'sometimes|string',
        ]);

        if ($validated['amount'] > $loan->remaining_balance) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds remaining balance',
            ], 422);
        }

        $repayment = $loan->repayments()->create([
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $loan->update([
            'amount_repaid' => $loan->amount_repaid + $validated['amount'],
            'remaining_balance' => $loan->remaining_balance - $validated['amount'],
            'last_payment_date' => $validated['payment_date'],
            'status' => $loan->remaining_balance - $validated['amount'] <= 0 ? 'repaid' : 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Repayment recorded successfully',
            'data' => [
                'repayment' => $repayment,
                'loan' => $loan,
            ],
        ]);
    }

    /**
     * Mark loan as defaulted
     */
    public function markDefaulted(Request $request, Loan $loan)
    {
        if ($loan->lender_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
            'notes' => 'sometimes|string',
        ]);

        $loan->update([
            'status' => 'defaulted',
            'default_date' => now(),
            'default_reason' => $validated['reason'],
            'default_notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loan marked as defaulted',
            'data' => $loan,
        ]);
    }

    /**
     * Get loan schedule
     */
    public function schedule(Loan $loan)
    {
        if ($loan->lender_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $schedule = [];
        $monthlyPayment = $this->calculateMonthlyPayment(
            $loan->loan_amount,
            $loan->interest_rate,
            $loan->duration_months
        );

        $currentDate = $loan->disbursement_date;
        $balance = $loan->loan_amount;

        for ($i = 1; $i <= $loan->duration_months; $i++) {
            $interestPayment = $balance * ($loan->interest_rate / 100 / 12);
            $principalPayment = $monthlyPayment - $interestPayment;
            $balance -= $principalPayment;

            $schedule[] = [
                'month' => $i,
                'due_date' => $currentDate->addMonth(),
                'principal' => $principalPayment,
                'interest' => $interestPayment,
                'total_payment' => $monthlyPayment,
                'remaining_balance' => max(0, $balance),
                'status' => 'pending',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $schedule,
        ]);
    }

    /**
     * Calculate monthly payment
     */
    private function calculateMonthlyPayment($principal, $annualRate, $months)
    {
        $monthlyRate = $annualRate / 100 / 12;
        if ($monthlyRate == 0) {
            return $principal / $months;
        }
        return ($principal * $monthlyRate * pow(1 + $monthlyRate, $months)) / 
               (pow(1 + $monthlyRate, $months) - 1);
    }
}
