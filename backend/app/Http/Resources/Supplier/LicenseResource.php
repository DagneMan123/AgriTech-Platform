<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LicenseResource extends JsonResource
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
            'company_name' => $this->company_name,
            'company_registration_number' => $this->company_registration_number,
            'business_type' => $this->business_type,
            'company_address' => $this->company_address,
            'company_phone' => $this->company_phone,
            'company_email' => $this->company_email,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'tax_identification_number' => $this->tax_identification_number,
            'business_license_document' => $this->business_license_document,
            'tax_certificate_document' => $this->tax_certificate_document,
            'company_logo' => $this->company_logo,
            'years_in_business' => $this->years_in_business,
            'number_of_employees' => $this->number_of_employees,
            'status' => $this->status,
            'verified_at' => $this->verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
