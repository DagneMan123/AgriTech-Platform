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
        // Get quantity - check both column names
        $quantity = 0;
        if (isset($this->quantity) && $this->quantity !== null) {
            $quantity = (float) $this->quantity;
        } elseif (isset($this->quantity_harvested) && $this->quantity_harvested !== null) {
            $quantity = (float) $this->quantity_harvested;
        }

        return [
            'id' => $this->id ?? null,
            'crop_id' => $this->crop_id ?? null,
            'harvest_date' => $this->harvest_date ?? null,
            'quantity' => $quantity,
            'unit' => $this->unit ?? null,
            'quality_grade' => $this->quality_grade ?? null,
            'notes' => $this->notes ?? $this->harvest_notes ?? null,
            'crop' => $this->crop ? [
                'id' => $this->crop->id ?? null,
                'crop_type' => $this->crop->crop_type ?? null,
                'variety' => $this->crop->variety ?? null,
                'farm' => $this->crop->farm ? [
                    'id' => $this->crop->farm->id ?? null,
                    'name' => $this->crop->farm->name ?? null,
                ] : null,
            ] : null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
        ];
    }
}
