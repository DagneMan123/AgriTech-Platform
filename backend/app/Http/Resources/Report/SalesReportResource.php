<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesReportResource extends JsonResource
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
            'report_type' => $this->report_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'category' => $this->category,
            'product_id' => $this->product_id,
            'farmer_id' => $this->farmer_id,
            'cooperative_id' => $this->cooperative_id,
            'group_by' => $this->group_by,
            'total_sales' => $this->total_sales ?? 0,
            'total_quantity' => $this->total_quantity ?? 0,
            'total_revenue' => $this->total_revenue ?? 0,
            'average_price' => $this->average_price ?? 0,
            'number_of_transactions' => $this->number_of_transactions ?? 0,
            'top_products' => $this->top_products,
            'sales_by_category' => $this->sales_by_category,
            'sales_trend' => $this->sales_trend,
            'export_format' => $this->export_format,
            'file_url' => $this->file_url,
            'created_at' => $this->created_at,
        ];
    }
}
