<?php

namespace App\Http\Resources\Transport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'transport_id' => $this->transport_id,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_make' => $this->vehicle_make,
            'vehicle_model' => $this->vehicle_model,
            'license_plate' => $this->license_plate,
            'registration_number' => $this->registration_number,
            'capacity' => $this->capacity,
            'capacity_unit' => $this->capacity_unit,
            'year_manufactured' => $this->year_manufactured,
            'driver_name' => $this->driver_name,
            'driver_phone' => $this->driver_phone,
            'driver_license_number' => $this->driver_license_number,
            'insurance_provider' => $this->insurance_provider,
            'insurance_policy_number' => $this->insurance_policy_number,
            'insurance_expiry_date' => $this->insurance_expiry_date,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
