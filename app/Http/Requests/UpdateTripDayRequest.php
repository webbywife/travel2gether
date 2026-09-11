<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('trip')) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'title_secondary' => ['nullable', 'string', 'max:120'],
            'area_label' => ['nullable', 'string', 'max:120'],
            'summary' => ['nullable', 'string', 'max:500'],
            'weather_note' => ['nullable', 'string', 'max:500'],
            'hotel_name' => ['nullable', 'string', 'max:160'],
            'hotel_address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
