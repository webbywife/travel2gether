<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('trip')) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120'],
            'destination' => ['required', 'string', 'max:120'],
            'party_size' => ['nullable', 'integer', 'between:1,20'],
            'currency' => ['nullable', \Illuminate\Validation\Rule::in(\App\Services\ExchangeRates::CURRENCIES)],
            'hotel_name' => ['nullable', 'string', 'max:160'],
            'hotel_address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
