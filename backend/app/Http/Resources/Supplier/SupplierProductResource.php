<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierProductResource extends JsonResource
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
            'product_name' => $this->product_name,
            'product_description' => $this->product_description,
            'product_category' => $this->product_category,
            'unit_price' => $this->unit_price,
            'currency' => $this->currency,
            'stock_quantity' => $this->stock_quantity,
            'stock_unit' => $this->stock_unit,
            'supplier_code' => $this->supplier_code,
            'manufacturer' => $this->manufacturer,
            'product_image' => $this->product_image,
            'certification' => $this->certification,
            'minimum_order_quantity' => $this->minimum_order_quantity,
            'lead_time_days' => $this->lead_time_days,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
