<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'total_amount' => (float) $this->total, // Frontend expects total_amount
            'paid_amount' => 0, // Default for now since table doesn't have this field
            'status' => strtoupper($this->payment_status), // Frontend expects uppercase status
            'payment_status' => $this->payment_status,
            'due_date' => $this->created_at->addDays(30), // Default 30 days from creation
            'is_paid' => $this->isPaid(),
            'is_overdue' => $this->isOverdue(),
            'order' => $this->whenLoaded('order', fn() => [
                'id' => $this->order->id,
                'customer_name' => $this->order->customer_name,
                'customer_email' => $this->order->customer_email,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}