<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdvertiser();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'ad_type' => 'required|in:banner,popup,popunder',
            'target_url' => 'required|url|max:500',
            'ad_content' => 'required|string',
            'pricing_model' => 'required|in:cpm,cpc',
            'bid_amount' => 'required|numeric|min:0.01',
            'budget' => 'required|numeric|min:1',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'countries' => 'nullable|array',
            'countries.*' => 'string|size:2',
            'devices' => 'nullable|array',
            'devices.*' => 'in:desktop,mobile,tablet',
            'operating_systems' => 'nullable|array',
            'operating_systems.*' => 'in:windows,mac,linux,android,ios',
            'browsers' => 'nullable|array',
            'browsers.*' => 'in:chrome,firefox,safari,edge',
            'is_vpn_allowed' => 'nullable|boolean',
            'is_proxy_allowed' => 'nullable|boolean',
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
            'name.required' => 'Campaign name is required.',
            'ad_type.required' => 'Ad type is required.',
            'target_url.required' => 'Target URL is required.',
            'target_url.url' => 'Target URL must be a valid URL.',
            'ad_content.required' => 'Ad content is required.',
            'pricing_model.required' => 'Pricing model is required.',
            'bid_amount.required' => 'Bid amount is required.',
            'bid_amount.min' => 'Bid amount must be at least 0.01.',
            'budget.required' => 'Budget is required.',
            'budget.min' => 'Budget must be at least 1.',
            'end_date.after' => 'End date must be after start date.',
        ];
    }
}








