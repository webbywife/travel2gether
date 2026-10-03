<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Editing a day's details. The area and hotel drive the AI drafts, live
 * weather, maps and "where you wake up" — so a changed area or hotel must be
 * a real place picked from search (with coordinates), not free text.
 */
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
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lon' => ['nullable', 'numeric', 'between:-180,180'],
            'summary' => ['nullable', 'string', 'max:500'],
            'weather_note' => ['nullable', 'string', 'max:500'],
            'hotel_name' => ['nullable', 'string', 'max:160'],
            'hotel_address' => ['nullable', 'string', 'max:255'],
            'hotel_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'hotel_lon' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            $day = $this->route('day');
            $located = fn (string $a, string $b) => filled($this->input($a)) && filled($this->input($b));

            $area = trim((string) $this->input('area_label'));
            if ($area !== '' && $area !== trim((string) $day->area_label) && ! $located('lat', 'lon')) {
                $v->errors()->add('area_label', 'Pick the area from the search suggestions so we know where it is on the map.');
            }

            $hotel = trim((string) $this->input('hotel_name'));
            if ($hotel !== '' && $hotel !== trim((string) $day->hotel_name) && ! $located('hotel_lat', 'hotel_lon')) {
                $v->errors()->add('hotel_name', 'Pick the hotel from the search suggestions so we know where it is.');
            }
        }];
    }
}
