<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
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
            'product_image' => $this->product_image,
            'category' => $this->category,
            'quality_grade' => $this->quality_grade,
            'certification' => $this->certification,
            'farmer_name' => $this->farmer?->name,
            'farmer_location' => $this->farmer?->location,
            'distance' => $this->distance,
            'average_rating' => $this->average_rating ?? 0,
            'reviews_count' => $this->reviews_count ?? 0,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
