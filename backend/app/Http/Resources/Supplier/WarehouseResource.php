<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
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
            'supplier_id' => $this->supplier_id,
            'warehouse_name' => $this->warehouse_name,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'capacity' => $this->capacity,
            'capacity_unit' => $this->capacity_unit,
            'warehouse_type' => $this->warehouse_type,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'storage_features' => $this->storage_features,
            'status' => $this->status,
            'inventory_count' => $this->inventory_count ?? $this->inventory()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
