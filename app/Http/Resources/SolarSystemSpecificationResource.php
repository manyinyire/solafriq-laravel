<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolarSystemSpecificationResource extends JsonResource
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
            'spec_name' => $this->spec_name,
            'spec_value' => $this->spec_value,
            'spec_category' => $this->spec_category,
            'sort_order' => $this->sort_order,
        ];
    }
}