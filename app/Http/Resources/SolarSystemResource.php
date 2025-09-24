<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolarSystemResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'capacity' => $this->capacity,
            'price' => (float) $this->price,
            'original_price' => $this->when($this->original_price, (float) $this->original_price),
            'installment_price' => $this->when($this->installment_price, (float) $this->installment_price),
            'installment_months' => $this->installment_months,
            'image_url' => $this->image_url,
            'gallery_images' => $this->gallery_images ?? [],
            'use_case' => $this->use_case,
            'gradient_colors' => $this->gradient_colors,
            'is_popular' => $this->is_popular,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            
            // Calculated attributes
            'savings' => $this->when($this->savings, (float) $this->savings),
            'savings_percentage' => $this->when($this->savings_percentage, $this->savings_percentage),
            
            // Related data
            'features' => SolarSystemFeatureResource::collection($this->whenLoaded('features')),
            'products' => SolarSystemProductResource::collection($this->whenLoaded('products')),
            'specifications' => SolarSystemSpecificationResource::collection($this->whenLoaded('specifications')),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}