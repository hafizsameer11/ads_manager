<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isPublisher();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:paypal,coinpayment,faucetpay,bank_swift,manual',
            'payment_details' => 'required|array',
            'payment_details.account' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Withdrawal amount is required.',
            'amount.min' => 'Withdrawal amount must be at least 1.',
            'payment_method.required' => 'Payment method is required.',
            'payment_details.required' => 'Payment details are required.',
            'payment_details.account.required' => 'Payment account is required.',
        ];
    }
}
