<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:1000',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.image_url' => 'nullable|url|max:500',
            'items.*.type' => 'required|in:solar_system,product,custom_package,custom_system',
            'items.*.product_id' => 'nullable|exists:products,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:500',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'is_gift' => 'nullable|boolean',
            'recipient_name' => 'required_if:is_gift,true|nullable|string|max:255',
            'recipient_email' => 'required_if:is_gift,true|nullable|email|max:255',
            'recipient_phone' => 'nullable|string|max:20',
            'recipient_address' => 'required_if:is_gift,true|nullable|string|max:500',
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
            'items.required' => 'At least one item is required for the order.',
            'items.*.name.required' => 'Each item must have a name.',
            'items.*.price.required' => 'Each item must have a price.',
            'items.*.quantity.required' => 'Each item must have a quantity.',
            'customer_name.required' => 'Customer name is required.',
            'customer_email.required' => 'Customer email is required.',
            'customer_email.email' => 'Please provide a valid email address.',
            'recipient_name.required_if' => 'Recipient name is required for gift orders.',
            'recipient_email.required_if' => 'Recipient email is required for gift orders.',
            'recipient_address.required_if' => 'Recipient address is required for gift orders.',
        ];
    }
}
