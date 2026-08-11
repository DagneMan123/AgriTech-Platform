<?php

namespace App\Http\Resources\Buyer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product_name' => $this->product?->product_name,
            'product_image' => $this->product?->product_image,
            'unit_price' => $this->product?->unit_price,
            'currency' => $this->product?->currency,
            'category' => $this->product?->category,
            'quality_grade' => $this->product?->quality_grade,
            'product_status' => $this->product?->status,
            'created_at' => $this->created_at,
        ];
    }
}
