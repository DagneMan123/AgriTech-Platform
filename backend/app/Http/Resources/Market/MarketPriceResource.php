<?php

namespace App\Http\Resources\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketPriceResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product_name' => $this->product?->product_name,
            'category' => $this->product?->category,
            'location' => $this->location,
            'market_type' => $this->market_type,
            'current_price' => $this->current_price,
            'previous_price' => $this->previous_price,
            'min_price' => $this->min_price,
            'max_price' => $this->max_price,
            'average_price' => $this->average_price,
            'currency' => $this->currency,
            'supply_level' => $this->supply_level,
            'demand_level' => $this->demand_level,
            'price_trend' => $this->price_trend,
            'price_change_percentage' => $this->price_change_percentage,
            'quantity_unit' => $this->quantity_unit,
            'reported_by' => $this->reported_by,
            'updated_at' => $this->updated_at,
        ];
    }
}
