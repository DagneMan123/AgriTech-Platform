<?php

namespace App\Http\Resources\Marketplace;

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
            'product_name' => $this->product_name,
            'product_description' => $this->product_description,
            'category' => $this->category,
            'quantity_available' => $this->quantity_available,
            'quantity_unit' => $this->quantity_unit,
            'unit_price' => $this->unit_price,
            'currency' => $this->currency,
            'total_price' => $this->total_price,
            'product_image' => $this->product_image,
            'quality_grade' => $this->quality_grade,
            'certification' => $this->certification,
            'expiry_date' => $this->expiry_date,
            'farmer_id' => $this->farmer_id,
            'farmer_name' => $this->farmer?->name,
            'status' => $this->status,
            'average_rating' => $this->average_rating ?? 0,
            'reviews_count' => $this->reviews_count ?? 0,
            'views_count' => $this->views_count ?? 0,
            'wishlist_count' => $this->wishlist_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
