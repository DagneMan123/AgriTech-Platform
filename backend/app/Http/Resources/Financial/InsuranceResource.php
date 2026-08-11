<?php

namespace App\Http\Resources\Financial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsuranceResource extends JsonResource
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
            'farm_id' => $this->farm_id,
            'insurance_type' => $this->insurance_type,
            'coverage_amount' => $this->coverage_amount,
            'currency' => $this->currency,
            'coverage_period_months' => $this->coverage_period_months,
            'premium_amount' => $this->premium_amount,
            'coverage_start_date' => $this->coverage_start_date,
            'coverage_end_date' => $this->coverage_end_date,
            'insured_items' => $this->insured_items,
            'status' => $this->status,
            'claims_count' => $this->claims_count ?? $this->claims()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
