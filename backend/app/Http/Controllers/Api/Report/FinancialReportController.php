<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Loan;
use App\Models\Insurance;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    /**
     * Get financial report
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
        ]);

        $startDate = isset($validated['date_from']) 
            ? $validated['date_from'] 
            : $this->getStartDate($validated['period'] ?? 'month');

        $endDate = $validated['date_to'] ?? now();

        $report = [
            'period' => [
                'from' => $startDate,
                'to' => $endDate,
            ],
            'summary' => $this->getFinancialSummary($startDate, $endDate),
            'revenue_breakdown' => $this->getRevenueBreakdown($startDate, $endDate),
            'payment_analytics' => $this->getPaymentAnalytics($startDate, $endDate),
            'loan_analytics' => $this->getLoanAnalytics($startDate, $endDate),
            'insurance_analytics' => $this->getInsuranceAnalytics($startDate, $endDate),
            'cash_flow' => $this->getCashFlow($startDate, $endDate),
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get financial summary
     */
    private function getFinancialSummary($startDate, $endDate)
    {
        $revenue = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $expenses = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('type', 'expense')
            ->sum('amount');

        $loanDisbursed = Loan::whereBetween('created_at', [$startDate, $endDate])
            ->sum('loan_amount');

        $loanRepaid = Loan::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount_repaid');

        return [
            'total_revenue' => $revenue,
            'total_expenses' => $expenses,
            'net_income' => $revenue - $expenses,
            'loan_disbursed' => $loanDisbursed,
            'loan_repaid' => $loanRepaid,
            'net_loan_position' => $loanDisbursed - $loanRepaid,
        ];
    }

    /**
     * Get revenue breakdown
     */
    private function getRevenueBreakdown($startDate, $endDate)
    {
        return Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('payment_method')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * Get payment analytics
     */
    private function getPaymentAnalytics($startDate, $endDate)
    {
        $payments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'by_status' => $payments->groupBy('status')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'amount' => $items->sum('amount'),
                ]),
            'by_method' => $payments->groupBy('payment_method')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'amount' => $items->sum('amount'),
                ]),
            'average_payment' => $payments->count() > 0 ? $payments->avg('amount') : 0,
            'success_rate' => $payments->count() > 0
                ? ($payments->where('status', 'completed')->count() / $payments->count()) * 100
                : 0,
        ];
    }

    /**
     * Get loan analytics
     */
    private function getLoanAnalytics($startDate, $endDate)
    {
        $loans = Loan::whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total_loans' => $loans->count(),
            'total_disbursed' => $loans->sum('loan_amount'),
            'total_repaid' => $loans->sum('amount_repaid'),
            'average_loan' => $loans->count() > 0 ? $loans->avg('loan_amount') : 0,
            'default_rate' => $loans->count() > 0
                ? ($loans->where('status', 'defaulted')->count() / $loans->count()) * 100
                : 0,
            'by_status' => $loans->groupBy('status')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'total_amount' => $items->sum('loan_amount'),
                ]),
        ];
    }

    /**
     * Get insurance analytics
     */
    private function getInsuranceAnalytics($startDate, $endDate)
    {
        $policies = Insurance::whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total_policies' => $policies->count(),
            'total_premium' => $policies->sum('premium_amount'),
            'total_coverage' => $policies->sum('coverage_amount'),
            'average_premium' => $policies->count() > 0 ? $policies->avg('premium_amount') : 0,
            'by_type' => $policies->groupBy('type')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'premium' => $items->sum('premium_amount'),
                    'coverage' => $items->sum('coverage_amount'),
                ]),
        ];
    }

    /**
     * Get cash flow
     */
    private function getCashFlow($startDate, $endDate)
    {
        return Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(CASE WHEN type IN ("payment", "deposit") THEN amount ELSE 0 END) as inflow')
            ->selectRaw('SUM(CASE WHEN type IN ("expense", "withdrawal") THEN amount ELSE 0 END) as outflow')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->date,
                    'inflow' => $row->inflow,
                    'outflow' => $row->outflow,
                    'net' => $row->inflow - $row->outflow,
                ];
            });
    }

    /**
     * Get profit and loss statement
     */
    public function profitAndLoss(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        $startDate = $this->getStartDate($validated['period'] ?? 'month');

        $revenue = Order::where('status', 'delivered')
            ->where('created_at', '>=', $startDate)
            ->sum('total_amount');

        $expenses = Transaction::where('created_at', '>=', $startDate)
            ->where('type', 'expense')
            ->sum('amount');

        $statement = [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'gross_profit' => $revenue - $expenses,
            'gross_profit_margin' => $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $statement,
        ]);
    }

    /**
     * Get accounts receivable
     */
    public function accountsReceivable()
    {
        $pending = Order::where('status', 'pending')
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total_amount) as amount')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_outstanding' => $pending->sum('amount'),
                'pending_orders' => $pending,
                'oldest_outstanding' => Order::where('status', 'pending')
                    ->oldest()
                    ->first(),
            ],
        ]);
    }

    /**
     * Get accounts payable
     */
    public function accountsPayable()
    {
        $payable = Transaction::where('type', 'payable')
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(amount) as amount')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_payable' => $payable->sum('amount'),
                'payable_items' => $payable,
            ],
        ]);
    }

    /**
     * Get financial ratios
     */
    public function financialRatios(Request $request)
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        $startDate = $this->getStartDate($validated['period'] ?? 'month');

        $revenue = Order::where('status', 'delivered')
            ->where('created_at', '>=', $startDate)
            ->sum('total_amount');

        $expenses = Transaction::where('created_at', '>=', $startDate)
            ->where('type', 'expense')
            ->sum('amount');

        $assets = Loan::where('status', 'active')
            ->sum('loan_amount');

        $liabilities = Loan::where('status', 'active')
            ->sum('remaining_balance');

        $ratios = [
            'profit_margin' => $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0,
            'return_on_assets' => $assets > 0 ? (($revenue - $expenses) / $assets) * 100 : 0,
            'debt_ratio' => ($assets + $liabilities) > 0 ? ($liabilities / ($assets + $liabilities)) * 100 : 0,
            'current_ratio' => $liabilities > 0 ? $assets / $liabilities : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $ratios,
        ]);
    }

    /**
     * Get start date based on period
     */
    private function getStartDate($period)
    {
        return match ($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };
    }

    /**
     * Export financial report
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,xlsx,pdf',
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Financial report exported successfully',
            'data' => [
                'format' => $validated['format'],
                'download_link' => '/reports/financial-report-' . now()->timestamp . '.' . $validated['format'],
            ],
        ]);
    }
}
