<?php

namespace App\Http\Resources\Buyer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'buyer_id' => $this->buyer_id,
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'delivery_address' => $this->delivery_address,
            'delivery_city' => $this->delivery_city,
            'delivery_state' => $this->delivery_state,
            'delivery_phone' => $this->delivery_phone,
            'delivery_date' => $this->delivery_date,
            'special_instructions' => $this->special_instructions,
            'items_count' => $this->items_count ?? $this->orderItems()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
