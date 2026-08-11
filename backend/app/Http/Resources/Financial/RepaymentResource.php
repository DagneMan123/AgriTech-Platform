<?php

namespace App\Http\Resources\Financial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepaymentResource extends JsonResource
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
            'loan_id' => $this->loan_id,
            'farmer_id' => $this->farmer_id,
            'farmer_name' => $this->farmer?->name,
            'repayment_amount' => $this->repayment_amount,
            'repayment_date' => $this->repayment_date,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number,
            'receipt_image' => $this->receipt_image,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
