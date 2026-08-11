<?php

namespace App\Http\Resources\Location;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_type' => $this->location_type,
            'location_name' => $this->location_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zip_code' => $this->zip_code,
            'radius' => $this->radius,
            'radius_unit' => $this->radius_unit,
            'nearby_farms' => $this->nearby_farms,
            'nearby_warehouses' => $this->nearby_warehouses,
            'nearby_markets' => $this->nearby_markets,
            'distance_from_user' => $this->distance_from_user,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
