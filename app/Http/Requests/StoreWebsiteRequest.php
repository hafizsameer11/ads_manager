<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebsiteRequest extends FormRequest
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
            'domain' => 'required|string|max:255|regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'verification_method' => 'required|in:manual,automatic',
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
            'domain.required' => 'Domain is required.',
            'domain.regex' => 'Please enter a valid domain name (e.g., example.com).',
            'name.required' => 'Website name is required.',
            'verification_method.required' => 'Verification method is required.',
        ];
    }
}












