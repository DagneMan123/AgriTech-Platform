<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierOrderResource extends JsonResource
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
            'supplier_id' => $this->supplier_id,
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'payment_terms' => $this->payment_terms,
            'payment_status' => $this->payment_status,
            'buyer_name' => $this->buyer_name,
            'buyer_email' => $this->buyer_email,
            'buyer_phone' => $this->buyer_phone,
            'delivery_address' => $this->delivery_address,
            'delivery_city' => $this->delivery_city,
            'delivery_state' => $this->delivery_state,
            'delivery_date' => $this->delivery_date,
            'special_instructions' => $this->special_instructions,
            'items_count' => $this->items_count ?? $this->orderItems()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
