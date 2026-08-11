<?php

namespace App\Http\Resources\Cooperative;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CooperativeSalesResource extends JsonResource
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
            'cooperative_id' => $this->cooperative_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product?->product_name,
            'quantity_sold' => $this->quantity_sold,
            'quantity_unit' => $this->quantity_unit,
            'unit_price' => $this->unit_price,
            'total_amount' => $this->total_amount,
            'sale_date' => $this->sale_date,
            'buyer_name' => $this->buyer_name,
            'buyer_phone' => $this->buyer_phone,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'delivery_address' => $this->delivery_address,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
