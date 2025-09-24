<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarrantyResource extends JsonResource
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
            'product_name' => $this->product_name,
            'serial_number' => $this->serial_number,
            'warranty_period_months' => $this->warranty_period_months,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'remaining_days' => $this->remaining_days,
            'claims' => WarrantyClaimResource::collection($this->whenLoaded('claims')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}