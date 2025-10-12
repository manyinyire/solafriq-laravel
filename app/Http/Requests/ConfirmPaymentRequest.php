<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPaymentRequest extends FormRequest
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
            'payment_method' => 'required|string|in:CASH,BANK_TRANSFER,CREDIT_CARD,DEBIT_CARD,MOBILE_MONEY',
            'transaction_reference' => 'required_if:payment_method,BANK_TRANSFER,CREDIT_CARD,DEBIT_CARD,MOBILE_MONEY|nullable|string|max:255',
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
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Invalid payment method selected.',
            'transaction_reference.required_if' => 'Transaction reference is required for this payment method.',
        ];
    }
}
