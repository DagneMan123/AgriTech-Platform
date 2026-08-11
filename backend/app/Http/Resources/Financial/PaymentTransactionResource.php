<?php

namespace App\Http\Resources\Financial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
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
            'transaction_type' => $this->transaction_type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'account_from' => $this->account_from,
            'account_to' => $this->account_to,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number,
            'description' => $this->description,
            'transaction_date' => $this->transaction_date,
            'status' => $this->status,
            'receipt_image' => $this->receipt_image,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
