<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardReportResource extends JsonResource
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
            'dashboard_type' => $this->dashboard_type,
            'date_range' => $this->date_range,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'metric_type' => $this->metric_type,
            'total_sales' => $this->total_sales ?? 0,
            'total_orders' => $this->total_orders ?? 0,
            'total_revenue' => $this->total_revenue ?? 0,
            'total_users' => $this->total_users ?? 0,
            'total_products' => $this->total_products ?? 0,
            'total_transactions' => $this->total_transactions ?? 0,
            'total_loans' => $this->total_loans ?? 0,
            'total_trainings' => $this->total_trainings ?? 0,
            'total_consultations' => $this->total_consultations ?? 0,
            'total_deliveries' => $this->total_deliveries ?? 0,
            'pending_items' => $this->pending_items ?? 0,
            'chart_data' => $this->chart_data,
            'top_products' => $this->top_products,
            'recent_activity' => $this->recent_activity,
            'created_at' => $this->created_at,
        ];
    }
}
