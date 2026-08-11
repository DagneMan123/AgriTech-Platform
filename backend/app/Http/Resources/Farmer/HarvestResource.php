<?php

namespace App\Http\Resources\Farmer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HarvestResource extends JsonResource
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
            'crop_id' => $this->crop_id,
            'crop_name' => $this->crop?->crop_name,
            'harvest_date' => $this->harvest_date,
            'quantity_harvested' => $this->quantity_harvested,
            'quantity_unit' => $this->quantity_unit,
            'quality_grade' => $this->quality_grade,
            'cost_of_harvest' => $this->cost_of_harvest,
            'storage_location' => $this->storage_location,
            'preservation_method' => $this->preservation_method,
            'notes' => $this->notes,
            'products_count' => $this->products_count ?? $this->products()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
