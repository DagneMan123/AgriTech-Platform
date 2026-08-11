<?php

namespace App\Http\Resources\Financial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanApplicationResource extends JsonResource
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
            'loan_type' => $this->loan_type,
            'loan_amount' => $this->loan_amount,
            'currency' => $this->currency,
            'loan_purpose' => $this->loan_purpose,
            'requested_term_months' => $this->requested_term_months,
            'collateral_type' => $this->collateral_type,
            'collateral_value' => $this->collateral_value,
            'farm_id' => $this->farm_id,
            'annual_income' => $this->annual_income,
            'credit_score' => $this->credit_score,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
