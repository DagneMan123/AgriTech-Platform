<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_users' => $this->total_users ?? 0,
            'total_farmers' => $this->total_farmers ?? 0,
            'total_buyers' => $this->total_buyers ?? 0,
            'total_suppliers' => $this->total_suppliers ?? 0,
            'total_orders' => $this->total_orders ?? 0,
            'total_revenue' => $this->total_revenue ?? 0,
            'pending_orders' => $this->pending_orders ?? 0,
            'completed_orders' => $this->completed_orders ?? 0,
            'total_products' => $this->total_products ?? 0,
            'total_transactions' => $this->total_transactions ?? 0,
            'total_loans' => $this->total_loans ?? 0,
            'total_insurance' => $this->total_insurance ?? 0,
            'total_trainings' => $this->total_trainings ?? 0,
            'total_consultations' => $this->total_consultations ?? 0,
            'recent_users' => $this->recent_users,
            'recent_orders' => $this->recent_orders,
            'revenue_by_month' => $this->revenue_by_month,
            'user_growth' => $this->user_growth,
        ];
    }
}
