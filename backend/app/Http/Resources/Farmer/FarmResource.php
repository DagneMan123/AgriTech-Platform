<?php

namespace App\Http\Resources\Farmer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmResource extends JsonResource
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
            'farm_name' => $this->farm_name,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'total_area' => $this->total_area,
            'area_unit' => $this->area_unit,
            'farm_type' => $this->farm_type,
            'soil_type' => $this->soil_type,
            'water_source' => $this->water_source,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'crops_count' => $this->crops_count ?? $this->crops()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
