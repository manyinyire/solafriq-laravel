<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:PENDING,PROCESSING,ACCEPTED,SCHEDULED,INSTALLED,RETURNED,CANCELLED',
            'payment_status' => 'sometimes|in:PENDING,PAID,FAILED,OVERDUE,REFUNDED',
            'payment_method' => 'nullable|string|max:50',
            'tracking_number' => 'nullable|string|max:100',
            'installation_date' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Invalid order status provided.',
            'payment_status.in' => 'Invalid payment status provided.',
            'installation_date.after' => 'Installation date must be in the future.',
        ];
    }
}
