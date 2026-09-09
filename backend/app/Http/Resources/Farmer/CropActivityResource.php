<?php

namespace App\Http\Resources\Farmer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CropActivityResource extends JsonResource
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
            'farm_id' => $this->farm_id,
            'farmer_id' => $this->farmer_id,
            'activity_type' => $this->activity_type,
            'activity_date' => $this->activity_date?->format('Y-m-d'),
            'activity_time' => $this->activity_time,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'cost' => $this->cost,
            'weather' => $this->weather,
            'notes' => $this->notes,
            'crop_display_name' => $this->crop_display_name,
            'crop' => [
                'id' => $this->crop?->id,
                'name' => $this->crop?->crop_type,
                'crop_type' => $this->crop?->crop_type,
                'variety' => $this->crop?->variety,
                'display_name' => $this->crop?->display_name
            ],
            'farm' => [
                'id' => $this->farm?->id,
                'name' => $this->farm?->name
            ],
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s')
        ];
    }
}
