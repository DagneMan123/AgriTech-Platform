<?php

namespace App\Http\Resources\Farmer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CropResource extends JsonResource
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
            'farm_id' => $this->farm_id,
            'crop_name' => $this->crop_name,
            'crop_type' => $this->crop_type,
            'planting_date' => $this->planting_date,
            'expected_harvest_date' => $this->expected_harvest_date,
            'area_planted' => $this->area_planted,
            'area_unit' => $this->area_unit,
            'variety' => $this->variety,
            'seed_source' => $this->seed_source,
            'fertilizer_type' => $this->fertilizer_type,
            'pest_control_method' => $this->pest_control_method,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
