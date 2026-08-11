<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Http\Requests\Farmer\StoreLoanRequest;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /**
     * Get all loans for the authenticated farmer
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $loans = Loan::where('farmer_id', $farmer->id)
                ->with(['repayments'])
                ->latest()
                ->paginate(15);

            return response()->json([
                'data' => $loans->items(),
                'pagination' => [
                    'total' => $loans->total(),
                    'per_page' => $loans->perPage(),
                    'current_page' => $loans->currentPage(),
                    'last_page' => $loans->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching loans', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new loan application
     */
    public function store(StoreLoanRequest $request)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $validated = $request->validated();

            // Calculate interest rate based on duration (can be adjusted)
            $interestRate = $validated['interest_rate'] ?? 12;

            $loan = Loan::create([
                'farmer_id' => $farmer->id,
                'amount' => $validated['amount'],
                'interest_rate' => $interestRate,
                'duration_months' => $validated['duration_months'],
                'purpose' => $validated['purpose'],
                'description' => $validated['description'],
                'status' => 'pending',
                'amount_paid' => 0,
                'remaining_balance' => $validated['amount'],
            ]);

            return response()->json([
                'message' => 'Loan application submitted successfully',
                'data' => $loan->load('repayments'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating loan', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific loan
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $loan = Loan::where('farmer_id', $farmer->id)
                ->with(['repayments'])
                ->findOrFail($id);

            return response()->json(['data' => $loan]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Loan not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching loan', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a loan (only pending loans)
     */
    public function update(StoreLoanRequest $request, $id)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $loan = Loan::where('farmer_id', $farmer->id)->findOrFail($id);

            // Only allow updating pending loans
            if ($loan->status !== 'pending') {
                return response()->json(['message' => 'Can only update pending loan applications'], 403);
            }

            $validated = $request->validated();
            $interestRate = $validated['interest_rate'] ?? $loan->interest_rate;

            $loan->update([
                'amount' => $validated['amount'],
                'interest_rate' => $interestRate,
                'duration_months' => $validated['duration_months'],
                'purpose' => $validated['purpose'],
                'description' => $validated['description'],
            ]);

            return response()->json([
                'message' => 'Loan application updated successfully',
                'data' => $loan->load('repayments'),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Loan not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating loan', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a loan (only pending loans)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $loan = Loan::where('farmer_id', $farmer->id)->findOrFail($id);

            // Only allow deleting pending loans
            if ($loan->status !== 'pending') {
                return response()->json(['message' => 'Can only delete pending loan applications'], 403);
            }

            $loan->delete();

            return response()->json(['message' => 'Loan application deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Loan not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting loan', 'error' => $e->getMessage()], 500);
        }
    }
}
