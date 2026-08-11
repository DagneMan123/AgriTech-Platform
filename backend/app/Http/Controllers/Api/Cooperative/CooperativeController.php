<?php

namespace App\Http\Controllers\Api\Cooperative;

use App\Http\Controllers\Controller;
use App\Models\Cooperative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CooperativeController extends Controller
{
    /**
     * Get cooperative details
     */
    public function show()
    {
        $cooperative = Cooperative::where('head_id', Auth::id())
            ->with('members', 'collectionCenters')
            ->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cooperative,
        ]);
    }

    /**
     * Update cooperative details
     */
    public function update(Request $request)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'registration_number' => 'sometimes|string|unique:cooperatives,registration_number,' . $cooperative->id,
            'description' => 'sometimes|string',
            'location' => 'sometimes|string',
            'contact_phone' => 'sometimes|string',
            'contact_email' => 'sometimes|email',
            'website' => 'sometimes|url',
            'bank_account' => 'sometimes|string',
            'tax_id' => 'sometimes|string',
            'by_laws' => 'sometimes|string',
        ]);

        $cooperative->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cooperative updated successfully',
            'data' => $cooperative,
        ]);
    }

    /**
     * Get cooperative statistics
     */
    public function statistics()
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $stats = [
            'registration_date' => $cooperative->registration_date,
            'members_count' => $cooperative->members()->count(),
            'active_members' => $cooperative->members()
                ->where('status', 'active')
                ->count(),
            'collection_centers' => $cooperative->collectionCenters()->count(),
            'total_sales' => $cooperative->sales()->sum('total_amount'),
            'total_transactions' => $cooperative->sales()->count(),
            'member_contributions' => $cooperative->members()->sum('contribution_amount'),
            'status' => $cooperative->status,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Upload cooperative documents
     */
    public function uploadDocuments(Request $request)
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $validated = $request->validate([
            'document_type' => 'required|in:registration,bylaws,tax_certificate,audit_report',
            'file' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $path = $request->file('file')->store('cooperative/documents', 'public');

        $documents = json_decode($cooperative->documents ?? '{}', true);
        $documents[$validated['document_type']] = $path;

        $cooperative->update(['documents' => json_encode($documents)]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'data' => [
                'document_type' => $validated['document_type'],
                'path' => $path,
            ],
        ]);
    }

    /**
     * Get cooperative performance metrics
     */
    public function performanceMetrics()
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $metrics = [
            'member_growth_rate' => $this->calculateMemberGrowth($cooperative),
            'sales_growth_rate' => $this->calculateSalesGrowth($cooperative),
            'member_retention_rate' => $this->calculateRetentionRate($cooperative),
            'average_member_contribution' => $cooperative->members()->count() > 0
                ? $cooperative->members()->avg('contribution_amount')
                : 0,
            'revenue_per_member' => $cooperative->members()->count() > 0
                ? $cooperative->sales()->sum('total_amount') / $cooperative->members()->count()
                : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Calculate member growth rate
     */
    private function calculateMemberGrowth(Cooperative $cooperative)
    {
        $lastMonth = $cooperative->members()
            ->where('joined_date', '>=', now()->subMonth())
            ->count();

        $twoMonthsAgo = $cooperative->members()
            ->where('joined_date', '>=', now()->subMonths(2))
            ->where('joined_date', '<', now()->subMonth())
            ->count();

        if ($twoMonthsAgo == 0) {
            return $lastMonth > 0 ? 100 : 0;
        }

        return (($lastMonth - $twoMonthsAgo) / $twoMonthsAgo) * 100;
    }

    /**
     * Calculate sales growth rate
     */
    private function calculateSalesGrowth(Cooperative $cooperative)
    {
        $lastMonth = $cooperative->sales()
            ->where('created_at', '>=', now()->subMonth())
            ->sum('total_amount');

        $twoMonthsAgo = $cooperative->sales()
            ->where('created_at', '>=', now()->subMonths(2))
            ->where('created_at', '<', now()->subMonth())
            ->sum('total_amount');

        if ($twoMonthsAgo == 0) {
            return $lastMonth > 0 ? 100 : 0;
        }

        return (($lastMonth - $twoMonthsAgo) / $twoMonthsAgo) * 100;
    }

    /**
     * Calculate member retention rate
     */
    private function calculateRetentionRate(Cooperative $cooperative)
    {
        $totalMembers = $cooperative->members()->count();
        $activeMembers = $cooperative->members()
            ->where('status', 'active')
            ->count();

        return $totalMembers > 0 ? ($activeMembers / $totalMembers) * 100 : 0;
    }

    /**
     * Get cooperative compliance status
     */
    public function complianceStatus()
    {
        $cooperative = Cooperative::where('head_id', Auth::id())->first();

        if (!$cooperative) {
            return response()->json([
                'success' => false,
                'message' => 'Cooperative not found',
            ], 404);
        }

        $compliance = [
            'registration_valid' => $cooperative->status === 'active',
            'has_bylaws' => $cooperative->bylaws !== null,
            'has_tax_certificate' => !empty($cooperative->tax_id),
            'has_bank_account' => !empty($cooperative->bank_account),
            'financial_reports_current' => $cooperative->last_audit_date 
                ? $cooperative->last_audit_date->greaterThan(now()->subYear())
                : false,
            'compliance_score' => $this->calculateComplianceScore($cooperative),
            'issues' => $this->identifyComplianceIssues($cooperative),
        ];

        return response()->json([
            'success' => true,
            'data' => $compliance,
        ]);
    }

    /**
     * Calculate compliance score
     */
    private function calculateComplianceScore(Cooperative $cooperative)
    {
        $score = 0;
        $total = 0;

        // Registration
        $total += 20;
        if ($cooperative->status === 'active') $score += 20;

        // Bylaws
        $total += 20;
        if ($cooperative->bylaws !== null) $score += 20;

        // Tax
        $total += 20;
        if (!empty($cooperative->tax_id)) $score += 20;

        // Bank Account
        $total += 20;
        if (!empty($cooperative->bank_account)) $score += 20;

        // Financial Reports
        $total += 20;
        if ($cooperative->last_audit_date && $cooperative->last_audit_date->greaterThan(now()->subYear())) {
            $score += 20;
        }

        return ($score / $total) * 100;
    }

    /**
     * Identify compliance issues
     */
    private function identifyComplianceIssues(Cooperative $cooperative)
    {
        $issues = [];

        if ($cooperative->status !== 'active') {
            $issues[] = 'Cooperative is not active';
        }

        if (empty($cooperative->bylaws)) {
            $issues[] = 'Bylaws not uploaded';
        }

        if (empty($cooperative->tax_id)) {
            $issues[] = 'Tax ID not registered';
        }

        if (empty($cooperative->bank_account)) {
            $issues[] = 'Bank account not registered';
        }

        if (!$cooperative->last_audit_date || $cooperative->last_audit_date->lessThan(now()->subYear())) {
            $issues[] = 'Financial audit report is outdated';
        }

        return $issues;
    }
}
