<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
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
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->warehouse?->warehouse_name,
            'quantity' => $this->quantity,
            'batch_number' => $this->batch_number,
            'expiry_date' => $this->expiry_date,
            'purchase_date' => $this->purchase_date,
            'purchase_price' => $this->purchase_price,
            'supplier_reference' => $this->supplier_reference,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
