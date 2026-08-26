<?php

namespace App\Http\Controllers\Api\Financial;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Insurance;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\LoanRepayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get financial dashboard overview
     */
    public function index(Request $request)
    {
        $financial = $request->user();

        // Loan statistics
        $totalLoans = Loan::where('financial_id', $financial->id)->count();
        $pendingLoans = Loan::where('financial_id', $financial->id)
            ->where('status', 'pending')
            ->count();
        $approvedLoans = Loan::where('financial_id', $financial->id)
            ->where('status', 'approved')
            ->count();
        $disbursedLoans = Loan::where('financial_id', $financial->id)
            ->where('status', 'disbursed')
            ->count();
        $defaultedLoans = Loan::where('financial_id', $financial->id)
            ->where('status', 'defaulted')
            ->count();

        // Loan amount statistics
        $totalLoanAmount = Loan::where('financial_id', $financial->id)
            ->sum('amount');
        $approvedAmount = Loan::where('financial_id', $financial->id)
            ->where('status', 'approved')
            ->sum('amount');
        $disbursedAmount = Loan::where('financial_id', $financial->id)
            ->where('status', 'disbursed')
            ->sum('amount');

        // Insurance statistics
        $totalInsurances = Insurance::where('financial_id', $financial->id)->count();
        $activeInsurances = Insurance::where('financial_id', $financial->id)
            ->where('status', 'active')
            ->count();
        $totalInsuranceAmount = Insurance::where('financial_id', $financial->id)->sum('coverage_amount');

        // Payment and repayment statistics
        $totalRepayments = LoanRepayment::whereIn('loan_id', function($query) use ($financial) {
            $query->select('id')->from('loans')->where('financial_id', $financial->id);
        })->count();

        $totalRepaid = LoanRepayment::whereIn('loan_id', function($query) use ($financial) {
            $query->select('id')->from('loans')->where('financial_id', $financial->id);
        })
            ->where('status', 'completed')
            ->sum('amount');

        $outstandingAmount = Loan::where('financial_id', $financial->id)
            ->where('status', 'disbursed')
            ->sum(DB::raw('amount - COALESCE((SELECT SUM(amount) FROM loan_repayments WHERE loan_id = loans.id AND status = "completed"), 0))'));

        // Recent loan applications
        $recentApplications = Loan::where('financial_id', $financial->id)
            ->with('farmer')
            ->latest()
            ->limit(5)
            ->get();

        // Loans by status
        $loansByStatus = Loan::where('financial_id', $financial->id)
            ->groupBy('status')
            ->selectRaw('status, count(*) as count, SUM(amount) as total_amount')
            ->get();

        // Monthly loan disbursement trend (database-agnostic)
        $disbursementTrend = Loan::where('financial_id', $financial->id)
            ->where('status', 'disbursed')
            ->whereDate('disbursed_date', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(function($loan) {
                return $loan->disbursed_date->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'month' => $group->first()->disbursed_date->format('Y-m'),
                    'loans' => $group->count(),
                    'amount' => $group->sum('amount'),
                ];
            })
            ->values();

        // Portfolio risk assessment
        $riskAssessment = [
            'low_risk' => Loan::where('financial_id', $financial->id)
                ->where('risk_level', 'low')->count(),
            'medium_risk' => Loan::where('financial_id', $financial->id)
                ->where('risk_level', 'medium')->count(),
            'high_risk' => Loan::where('financial_id', $financial->id)
                ->where('risk_level', 'high')->count(),
        ];

        return response()->json([
            'summary' => [
                'total_loans' => $totalLoans,
                'pending_loans' => $pendingLoans,
                'approved_loans' => $approvedLoans,
                'disbursed_loans' => $disbursedLoans,
                'defaulted_loans' => $defaultedLoans,
                'total_loan_amount' => $totalLoanAmount,
                'approved_amount' => $approvedAmount,
                'disbursed_amount' => $disbursedAmount,
                'outstanding_amount' => $outstandingAmount,
                'total_insured_amount' => $totalInsuranceAmount,
                'active_insurances' => $activeInsurances,
                'total_repayments' => $totalRepayments,
                'total_repaid' => $totalRepaid,
                'repayment_rate_percent' => $disbursedAmount > 0 ? ($totalRepaid / $disbursedAmount) * 100 : 0,
            ],
            'risk_assessment' => $riskAssessment,
            'recent_applications' => $recentApplications,
            'loans_by_status' => $loansByStatus,
            'disbursement_trend' => $disbursementTrend,
        ]);
    }

    /**
     * Get pending loan applications
     */
    public function pendingApplications(Request $request)
    {
        $financial = $request->user();

        $applications = Loan::where('financial_id', $financial->id)
            ->where('status', 'pending')
            ->with('farmer')
            ->latest()
            ->paginate(20);

        return response()->json($applications);
    }

    /**
     * Get loan management
     */
    public function loans(Request $request)
    {
        $financial = $request->user();
        $status = $request->query('status');

        $query = Loan::where('financial_id', $financial->id)
            ->with(['farmer', 'repayments']);

        if ($status) {
            $query->where('status', $status);
        }

        $loans = $query->latest()->paginate(20);

        return response()->json($loans);
    }

    /**
     * Get insurance management
     */
    public function insurances(Request $request)
    {
        $financial = $request->user();
        $status = $request->query('status');

        $query = Insurance::where('financial_id', $financial->id)
            ->with('farmer');

        if ($status) {
            $query->where('status', $status);
        }

        $insurances = $query->latest()->paginate(20);

        return response()->json($insurances);
    }

    /**
     * Get repayments tracking
     */
    public function repayments(Request $request)
    {
        $financial = $request->user();

        $repayments = LoanRepayment::whereIn('loan_id', function($query) use ($financial) {
            $query->select('id')->from('loans')->where('financial_id', $financial->id);
        })
            ->with('loan')
            ->latest()
            ->paginate(20);

        return response()->json($repayments);
    }

    /**
     * Get financial transactions
     */
    public function transactions(Request $request)
    {
        $financial = $request->user();

        $transactions = Transaction::where('financial_id', $financial->id)
            ->with('loan')
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }

    /**
     * Get portfolio analytics
     */
    public function portfolioAnalytics(Request $request)
    {
        $financial = $request->user();
        $period = $request->query('period', 30);

        // Portfolio composition
        $byPurpose = Loan::where('financial_id', $financial->id)
            ->groupBy('purpose')
            ->selectRaw('purpose, count(*) as count, SUM(amount) as total_amount')
            ->get();

        // Portfolio by term
        $byTerm = Loan::where('financial_id', $financial->id)
            ->groupBy('term_months')
            ->selectRaw('term_months, count(*) as count, SUM(amount) as total_amount')
            ->get();

        // Default rate
        $totalDisbursed = Loan::where('financial_id', $financial->id)
            ->where('status', 'disbursed')
            ->count();
        $defaulted = Loan::where('financial_id', $financial->id)
            ->where('status', 'defaulted')
            ->count();
        $defaultRate = $totalDisbursed > 0 ? ($defaulted / $totalDisbursed) * 100 : 0;

        // Average loan size
        $avgLoanSize = Loan::where('financial_id', $financial->id)
            ->where('status', 'disbursed')
            ->avg('amount') ?? 0;

        // Revenue from interest
        $interestRevenue = LoanRepayment::whereIn('loan_id', function($query) use ($financial) {
            $query->select('id')->from('loans')->where('financial_id', $financial->id);
        })
            ->where('status', 'completed')
            ->whereDate('payment_date', '>=', now()->subDays($period))
            ->sum('interest_amount');

        return response()->json([
            'period_days' => $period,
            'portfolio_by_purpose' => $byPurpose,
            'portfolio_by_term' => $byTerm,
            'default_rate_percent' => round($defaultRate, 2),
            'average_loan_size' => round($avgLoanSize, 2),
            'interest_revenue' => $interestRevenue,
        ]);
    }

    /**
     * Get loan details
     */
    public function loanDetails(Request $request, $id)
    {
        $financial = $request->user();

        $loan = Loan::where('financial_id', $financial->id)
            ->where('id', $id)
            ->with(['farmer', 'repayments'])
            ->first();

        if (!$loan) {
            return response()->json(['message' => 'Loan not found'], 404);
        }

        // Calculate outstanding balance
        $totalRepaid = $loan->repayments->where('status', 'completed')->sum('amount');
        $outstandingBalance = $loan->amount - $totalRepaid;

        return response()->json([
            'loan' => $loan,
            'total_repaid' => $totalRepaid,
            'outstanding_balance' => $outstandingBalance,
            'repayment_history' => $loan->repayments,
        ]);
    }

    /**
     * Get risk assessment report
     */
    public function riskAssessment(Request $request)
    {
        $financial = $request->user();

        // Portfolio risk breakdown
        $riskBreakdown = Loan::where('financial_id', $financial->id)
            ->groupBy('risk_level')
            ->selectRaw('risk_level, count(*) as count, SUM(amount) as total_amount')
            ->get();

        // High-risk loans
        $highRiskLoans = Loan::where('financial_id', $financial->id)
            ->where('risk_level', 'high')
            ->orWhere('status', 'defaulted')
            ->with('farmer')
            ->get();

        // Portfolio health score
        $healthScore = 100 - (Loan::where('financial_id', $financial->id)
            ->where('status', 'defaulted')
            ->count() / max(Loan::where('financial_id', $financial->id)->count(), 1)) * 100;

        return response()->json([
            'risk_breakdown' => $riskBreakdown,
            'high_risk_loans_count' => $highRiskLoans->count(),
            'high_risk_loans' => $highRiskLoans->take(10),
            'portfolio_health_score' => round($healthScore, 2),
        ]);
    }
}
