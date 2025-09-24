<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'tracking_number' => $this->tracking_number,
            'notes' => $this->notes,
            
            // Customer information
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,

            // Gift information
            'is_gift' => (bool) $this->is_gift,
            'recipient_name' => $this->recipient_name,
            'recipient_email' => $this->recipient_email,
            'recipient_phone' => $this->recipient_phone,
            'recipient_address' => $this->recipient_address,
            
            // Status flags
            'is_paid' => $this->isPaid(),
            'is_pending' => $this->isPending(),
            'is_completed' => $this->isCompleted(),
            
            // Calculated values
            'subtotal' => $this->when($this->relationLoaded('items'), (float) $this->subtotal),
            
            // Related data
            'user' => $this->whenLoaded('user', function () {
                if ($this->user) {
                    return [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                    ];
                }
                return null;
            }),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'invoice_id' => $this->whenLoaded('invoice', fn() => $this->invoice->id),
            'warranties' => WarrantyResource::collection($this->whenLoaded('warranties')),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}