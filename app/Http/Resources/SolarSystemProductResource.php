<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolarSystemProductResource extends JsonResource
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
            'product_description' => $this->product_description,
            'quantity' => $this->quantity,
            'unit_price' => $this->when($this->unit_price, (float) $this->unit_price),
            'total_price' => $this->when($this->total_price, (float) $this->total_price),
            'sort_order' => $this->sort_order,
        ];
    }
}