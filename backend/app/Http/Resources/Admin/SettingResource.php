<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
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
            'platform_name' => $this->platform_name,
            'platform_email' => $this->platform_email,
            'platform_phone' => $this->platform_phone,
            'platform_address' => $this->platform_address,
            'platform_logo' => $this->platform_logo,
            'commission_rate' => $this->commission_rate,
            'maintenance_mode' => $this->maintenance_mode,
            'currency' => $this->currency,
            'default_language' => $this->default_language,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_username' => $this->smtp_username,
            'timezone' => $this->timezone,
            'updated_at' => $this->updated_at,
        ];
    }
}
