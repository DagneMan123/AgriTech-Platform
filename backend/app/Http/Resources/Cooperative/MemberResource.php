<?php

namespace App\Http\Resources\Cooperative;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'cooperative_id' => $this->cooperative_id,
            'member_name' => $this->member_name,
            'member_email' => $this->member_email,
            'member_phone' => $this->member_phone,
            'member_role' => $this->member_role,
            'date_joined' => $this->date_joined,
            'membership_status' => $this->membership_status,
            'contribution_amount' => $this->contribution_amount,
            'address' => $this->address,
            'national_id' => $this->national_id,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
