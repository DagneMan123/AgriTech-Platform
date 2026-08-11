<?php

namespace App\Http\Resources\Farmer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'harvest_id' => $this->harvest_id,
            'farmer_id' => $this->farmer_id,
            'product_name' => $this->product_name,
            'product_description' => $this->product_description,
            'quantity_available' => $this->quantity_available,
            'quantity_unit' => $this->quantity_unit,
            'unit_price' => $this->unit_price,
            'currency' => $this->currency,
            'quality_grade' => $this->quality_grade,
            'product_image' => $this->product_image,
            'category' => $this->category,
            'certification' => $this->certification,
            'expiry_date' => $this->expiry_date,
            'status' => $this->status,
            'reviews_count' => $this->reviews_count ?? $this->reviews()->count(),
            'average_rating' => $this->average_rating ?? $this->reviews()->avg('rating'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
