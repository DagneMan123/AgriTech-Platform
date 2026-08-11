<?php

namespace App\Http\Controllers\Api\Cooperative;

use App\Http\Controllers\Controller;
use App\Models\Cooperative;
use App\Models\CooperativeMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /**
     * Get cooperative members
     */
    public function index(Request $request)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:active,inactive,pending,rejected',
            'sort' => 'sometimes|in:newest,oldest,contribution_high,contribution_low',
            'search' => 'sometimes|string',
        ]);

        $query = $cooperative->members();

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['search'])) {
            $query->where('name', 'ilike', '%' . $validated['search'] . '%')
                ->orWhere('phone', 'ilike', '%' . $validated['search'] . '%');
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('joined_date', 'desc'),
                'oldest' => $query->orderBy('joined_date', 'asc'),
                'contribution_high' => $query->orderBy('contribution_amount', 'desc'),
                'contribution_low' => $query->orderBy('contribution_amount', 'asc'),
            };
        } else {
            $query->orderBy('joined_date', 'desc');
        }

        $members = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    /**
     * Add new member
     */
    public function store(Request $request)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $validated = $request->validate([
            'farmer_id' => 'required|exists:users,id',
            'farm_name' => 'required|string',
            'farm_location' => 'required|string',
            'farm_size' => 'required|numeric|min:0.1',
            'crops_grown' => 'required|array',
            'contribution_amount' => 'required|numeric|min:100',
            'contribution_date' => 'required|date',
            'identification_number' => 'sometimes|string',
        ]);

        // Check if farmer already a member
        $existingMember = $cooperative->members()
            ->where('farmer_id', $validated['farmer_id'])
            ->first();

        if ($existingMember) {
            return response()->json([
                'success' => false,
                'message' => 'Farmer is already a member of this cooperative',
            ], 422);
        }

        $member = CooperativeMember::create([
            'cooperative_id' => $cooperative->id,
            'farmer_id' => $validated['farmer_id'],
            'farm_name' => $validated['farm_name'],
            'farm_location' => $validated['farm_location'],
            'farm_size' => $validated['farm_size'],
            'crops_grown' => json_encode($validated['crops_grown']),
            'contribution_amount' => $validated['contribution_amount'],
            'contribution_date' => $validated['contribution_date'],
            'identification_number' => $validated['identification_number'] ?? null,
            'status' => 'pending',
            'joined_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member added successfully. Awaiting approval.',
            'data' => $member,
        ], 201);
    }

    /**
     * Get member details
     */
    public function show(CooperativeMember $member)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $member->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found',
            ], 404);
        }

        $member->load('farmer');

        return response()->json([
            'success' => true,
            'data' => $member,
        ]);
    }

    /**
     * Update member
     */
    public function update(Request $request, CooperativeMember $member)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $member->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found',
            ], 404);
        }

        $validated = $request->validate([
            'farm_name' => 'sometimes|string',
            'farm_location' => 'sometimes|string',
            'farm_size' => 'sometimes|numeric|min:0.1',
            'crops_grown' => 'sometimes|array',
        ]);

        if (isset($validated['crops_grown'])) {
            $validated['crops_grown'] = json_encode($validated['crops_grown']);
        }

        $member->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Member updated successfully',
            'data' => $member,
        ]);
    }

    /**
     * Approve member
     */
    public function approve(Request $request, CooperativeMember $member)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $member->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found',
            ], 404);
        }

        if ($member->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending members can be approved',
            ], 422);
        }

        $member->update([
            'status' => 'active',
            'approved_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member approved successfully',
            'data' => $member,
        ]);
    }

    /**
     * Reject member
     */
    public function reject(Request $request, CooperativeMember $member)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $member->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found',
            ], 404);
        }

        if ($member->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending members can be rejected',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $member->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
            'rejected_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member rejected',
            'data' => $member,
        ]);
    }

    /**
     * Suspend member
     */
    public function suspend(Request $request, CooperativeMember $member)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $member->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found',
            ], 404);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
            'duration_days' => 'sometimes|integer|min:1',
        ]);

        $member->update([
            'status' => 'inactive',
            'suspension_reason' => $validated['reason'],
            'suspended_date' => now(),
            'suspension_end_date' => isset($validated['duration_days']) 
                ? now()->addDays($validated['duration_days']) 
                : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member suspended',
            'data' => $member,
        ]);
    }

    /**
     * Record member contribution
     */
    public function recordContribution(Request $request, CooperativeMember $member)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $member->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found',
            ], 404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'contribution_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check',
        ]);

        $contribution = $member->contributions()->create([
            'amount' => $validated['amount'],
            'contribution_date' => $validated['contribution_date'],
            'payment_method' => $validated['payment_method'],
        ]);

        $member->update([
            'contribution_amount' => $member->contribution_amount + $validated['amount'],
            'last_contribution_date' => $validated['contribution_date'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contribution recorded successfully',
            'data' => $contribution,
        ]);
    }

    /**
     * Get member contribution history
     */
    public function contributionHistory(CooperativeMember $member)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative || $member->cooperative_id !== $cooperative->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found',
            ], 404);
        }

        $contributions = $member->contributions()
            ->orderBy('contribution_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contributions,
        ]);
    }

    /**
     * Get member statistics
     */
    public function memberStatistics()
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $stats = [
            'total_members' => $cooperative->members()->count(),
            'active_members' => $cooperative->members()
                ->where('status', 'active')
                ->count(),
            'pending_members' => $cooperative->members()
                ->where('status', 'pending')
                ->count(),
            'inactive_members' => $cooperative->members()
                ->where('status', 'inactive')
                ->count(),
            'total_contribution' => $cooperative->members()->sum('contribution_amount'),
            'average_farm_size' => $cooperative->members()->avg('farm_size'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
