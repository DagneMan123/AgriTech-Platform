<?php

namespace App\Http\Resources\Buyer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceResource extends JsonResource
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
            'product_name' => $this->product_name,
            'product_description' => $this->product_description,
            'quantity_available' => $this->quantity_available,
            'quantity_unit' => $this->quantity_unit,
            'unit_price' => $this->unit_price,
            'currency' => $this->currency,
            'total_price' => $this->total_price,
            'product_image' => $this->product_image,
            'category' => $this->category,
            'quality_grade' => $this->quality_grade,
            'certification' => $this->certification,
            'farmer_id' => $this->farmer_id,
            'farmer_name' => $this->farmer?->name,
            'farmer_rating' => $this->farmer?->average_rating,
            'status' => $this->status,
            'reviews_count' => $this->reviews_count ?? 0,
            'average_rating' => $this->average_rating ?? 0,
            'created_at' => $this->created_at,
        ];
    }
}
