<?php

namespace App\Http\Resources\Financial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
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
            'farmer_id' => $this->farmer_id,
            'farmer_name' => $this->farmer?->name,
            'loan_amount' => $this->loan_amount,
            'approved_amount' => $this->approved_amount,
            'currency' => $this->currency,
            'interest_rate' => $this->interest_rate,
            'loan_term_months' => $this->loan_term_months,
            'monthly_payment' => $this->monthly_payment,
            'total_repayment' => $this->total_repayment,
            'status' => $this->status,
            'disbursement_date' => $this->disbursement_date,
            'maturity_date' => $this->maturity_date,
            'repaid_amount' => $this->repaid_amount,
            'remaining_balance' => $this->remaining_balance,
            'next_payment_date' => $this->next_payment_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
