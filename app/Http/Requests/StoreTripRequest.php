<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'destination' => ['required', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:120'],
            'party_size' => ['nullable', 'integer', 'between:1,20'],
            'currency' => ['nullable', 'in:USD,PHP,EUR,JPY,KRW,GBP,SGD,AUD'],

            'arrival_date' => ['required', 'date'],
            'departure_date' => ['required', 'date', 'after:arrival_date'],

            'segments' => ['required', 'array', 'size:2'],
            'segments.*.from' => ['nullable', 'string', 'max:60'],
            'segments.*.to' => ['nullable', 'string', 'max:60'],
            'segments.*.date' => ['nullable', 'string', 'max:40'],
            'segments.*.depart' => ['nullable', 'string', 'max:20'],
            'segments.*.arrive' => ['nullable', 'string', 'max:20'],
            'segments.*.terminal' => ['nullable', 'string', 'max:40'],
            'segments.*.airline' => ['nullable', 'string', 'max:60'],
            'segments.*.flight_no' => ['nullable', 'string', 'max:20'],

            'hotel_name' => ['nullable', 'string', 'max:160'],
            'hotel_address' => ['nullable', 'string', 'max:255'],
            'hotel_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'hotel_lon' => ['nullable', 'numeric', 'between:-180,180'],

            'areas' => ['nullable', 'array', 'max:14'],
            'areas.*.name' => ['nullable', 'string', 'max:120'],
            'areas.*.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'areas.*.lon' => ['nullable', 'numeric', 'between:-180,180'],
            // Multi-city: an area may carry its own hotel instead of using the trip's main one.
            'areas.*.hotel_name' => ['nullable', 'string', 'max:160'],
            'areas.*.hotel_address' => ['nullable', 'string', 'max:255'],
            'areas.*.hotel_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'areas.*.hotel_lon' => ['nullable', 'numeric', 'between:-180,180'],

            'interests' => ['nullable', 'array', 'max:12'],
            'interests.*' => ['string', 'max:40'],

            'budget_per_person' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'shopping' => ['nullable', 'array'],
            'shopping.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $areas = collect($this->input('areas', []))->filter(fn ($a) => filled($a['name'] ?? null));
                if ($areas->isEmpty()) {
                    $validator->errors()->add('areas', 'Add at least one area you want to visit.');
                }

                try {
                    $span = Carbon::parse($this->input('arrival_date'))->diffInDays(Carbon::parse($this->input('departure_date')));
                    if ($span > 30) {
                        $validator->errors()->add('departure_date', 'Keep the trip to 30 days or fewer for now.');
                    }
                } catch (\Throwable) {
                    // date rules already report a malformed value
                }
            },
        ];
    }
}
