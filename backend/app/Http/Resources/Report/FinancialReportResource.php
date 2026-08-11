<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_type' => $this->report_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'account_type' => $this->account_type,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'group_by' => $this->group_by,
            'total_income' => $this->total_income ?? 0,
            'total_expense' => $this->total_expense ?? 0,
            'total_profit' => $this->total_profit ?? 0,
            'total_transactions' => $this->total_transactions ?? 0,
            'total_loans_amount' => $this->total_loans_amount ?? 0,
            'total_insurance_premium' => $this->total_insurance_premium ?? 0,
            'total_payments' => $this->total_payments ?? 0,
            'cash_flow' => $this->cash_flow,
            'balance_sheet' => $this->balance_sheet,
            'transaction_breakdown' => $this->transaction_breakdown,
            'include_summary' => $this->include_summary,
            'export_format' => $this->export_format,
            'file_url' => $this->file_url,
            'created_at' => $this->created_at,
        ];
    }
}
