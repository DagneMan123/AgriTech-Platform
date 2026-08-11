<?php

namespace App\Http\Resources\Farmer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmerOrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'buyer_name' => $this->buyer?->name,
            'buyer_email' => $this->buyer?->email,
            'delivery_address' => $this->delivery_address,
            'delivery_date' => $this->delivery_date,
            'items_count' => $this->items_count ?? $this->orderItems()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
