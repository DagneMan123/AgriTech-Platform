<?php

namespace App\Http\Controllers\Api\Financial;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use App\Models\InsuranceClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InsuranceController extends Controller
{
    /**
     * Get all insurance policies
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:crop,livestock,property,liability',
            'status' => 'sometimes|in:active,inactive,expired,cancelled',
            'sort' => 'sometimes|in:newest,oldest,premium_high,premium_low',
        ]);

        $query = Insurance::where('provider_id', Auth::id())->with('holder');

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('created_at', 'desc'),
                'oldest' => $query->orderBy('created_at', 'asc'),
                'premium_high' => $query->orderBy('premium_amount', 'desc'),
                'premium_low' => $query->orderBy('premium_amount', 'asc'),
            };
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $policies = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $policies,
        ]);
    }

    /**
     * Create insurance policy
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'holder_id' => 'required|exists:users,id',
            'type' => 'required|in:crop,livestock,property,liability',
            'coverage_amount' => 'required|numeric|min:1000',
            'premium_amount' => 'required|numeric|min:1',
            'duration_months' => 'required|integer|min:1|max:60',
            'description' => 'required|string',
            'terms_conditions' => 'sometimes|string',
        ]);

        $policy = Insurance::create([
            'provider_id' => Auth::id(),
            'holder_id' => $validated['holder_id'],
            'type' => $validated['type'],
            'coverage_amount' => $validated['coverage_amount'],
            'premium_amount' => $validated['premium_amount'],
            'duration_months' => $validated['duration_months'],
            'description' => $validated['description'],
            'terms_conditions' => $validated['terms_conditions'] ?? null,
            'start_date' => now(),
            'expiry_date' => now()->addMonths($validated['duration_months']),
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Insurance policy created successfully',
            'data' => $policy,
        ], 201);
    }

    /**
     * Get policy details
     */
    public function show(Insurance $policy)
    {
        if ($policy->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $policy->load(['holder', 'claims']);

        return response()->json([
            'success' => true,
            'data' => $policy,
        ]);
    }

    /**
     * Update policy
     */
    public function update(Request $request, Insurance $policy)
    {
        if ($policy->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($policy->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update active policies',
            ], 422);
        }

        $validated = $request->validate([
            'coverage_amount' => 'sometimes|numeric|min:1000',
            'premium_amount' => 'sometimes|numeric|min:1',
            'description' => 'sometimes|string',
            'terms_conditions' => 'sometimes|string',
        ]);

        $policy->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Policy updated successfully',
            'data' => $policy,
        ]);
    }

    /**
     * Renew policy
     */
    public function renew(Request $request, Insurance $policy)
    {
        if ($policy->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'duration_months' => 'required|integer|min:1|max:60',
            'premium_amount' => 'sometimes|numeric|min:1',
        ]);

        $renewedPolicy = $policy->replicate();
        $renewedPolicy->start_date = now();
        $renewedPolicy->expiry_date = now()->addMonths($validated['duration_months']);
        $renewedPolicy->premium_amount = $validated['premium_amount'] ?? $policy->premium_amount;
        $renewedPolicy->status = 'active';
        $renewedPolicy->save();

        $policy->update(['status' => 'expired']);

        return response()->json([
            'success' => true,
            'message' => 'Policy renewed successfully',
            'data' => $renewedPolicy,
        ]);
    }

    /**
     * Cancel policy
     */
    public function cancel(Request $request, Insurance $policy)
    {
        if ($policy->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
            'refund_amount' => 'sometimes|numeric|min:0',
        ]);

        $policy->update([
            'status' => 'cancelled',
            'cancellation_date' => now(),
            'cancellation_reason' => $validated['reason'],
            'refund_amount' => $validated['refund_amount'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Policy cancelled successfully',
            'data' => $policy,
        ]);
    }

    /**
     * File insurance claim
     */
    public function fileClaim(Request $request, Insurance $policy)
    {
        if ($policy->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'claim_amount' => 'required|numeric|min:1',
            'incident_date' => 'required|date|before:today',
            'description' => 'required|string',
            'evidence_documents' => 'sometimes|array',
        ]);

        if ($validated['claim_amount'] > $policy->coverage_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Claim amount exceeds coverage amount',
            ], 422);
        }

        $claim = InsuranceClaim::create([
            'insurance_id' => $policy->id,
            'claim_amount' => $validated['claim_amount'],
            'incident_date' => $validated['incident_date'],
            'description' => $validated['description'],
            'status' => 'pending',
            'filed_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Claim filed successfully',
            'data' => $claim,
        ], 201);
    }

    /**
     * Get claims for policy
     */
    public function getClaims(Insurance $policy)
    {
        if ($policy->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $claims = $policy->claims()
            ->orderBy('filed_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $claims,
        ]);
    }

    /**
     * Approve claim
     */
    public function approveClaim(Request $request, InsuranceClaim $claim)
    {
        if ($claim->insurance->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'approved_amount' => 'sometimes|numeric|min:1',
            'notes' => 'sometimes|string',
        ]);

        $claim->update([
            'status' => 'approved',
            'approved_amount' => $validated['approved_amount'] ?? $claim->claim_amount,
            'approved_date' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Claim approved',
            'data' => $claim,
        ]);
    }

    /**
     * Reject claim
     */
    public function rejectClaim(Request $request, InsuranceClaim $claim)
    {
        if ($claim->insurance->provider_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $claim->update([
            'status' => 'rejected',
            'rejected_date' => now(),
            'rejection_reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Claim rejected',
            'data' => $claim,
        ]);
    }

    /**
     * Get insurance analytics
     */
    public function analytics(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        $period = $validated['period'] ?? 'month';
        $startDate = match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
        };

        $stats = [
            'total_policies' => Insurance::where('provider_id', Auth::id())->count(),
            'active_policies' => Insurance::where('provider_id', Auth::id())
                ->where('status', 'active')
                ->count(),
            'total_claims' => InsuranceClaim::whereIn('insurance_id', 
                Insurance::where('provider_id', Auth::id())->pluck('id'))
                ->where('created_at', '>=', $startDate)
                ->count(),
            'approved_claims' => InsuranceClaim::whereIn('insurance_id',
                Insurance::where('provider_id', Auth::id())->pluck('id'))
                ->where('status', 'approved')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'total_premium_collected' => Insurance::where('provider_id', Auth::id())
                ->sum('premium_amount'),
            'total_claims_paid' => InsuranceClaim::whereIn('insurance_id',
                Insurance::where('provider_id', Auth::id())->pluck('id'))
                ->where('status', 'approved')
                ->sum('approved_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
