<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $fillable = [
        'provider', 'provider_id', 'name', 'formatted_address', 'lat', 'lon',
        'types', 'rating', 'rating_count', 'price_level', 'phone', 'website',
        'raw', 'details_fetched_at',
    ];

    protected $casts = [
        'types' => 'array',
        'raw' => 'array',
        'lat' => 'float',
        'lon' => 'float',
        'rating' => 'float',
        'details_fetched_at' => 'datetime',
    ];

    protected $hidden = ['raw'];

    public function mapUrl(): string
    {
        return match ($this->provider) {
            'kakao' => "https://map.kakao.com/link/map/{$this->provider_id}",
            default => 'https://www.google.com/maps/place/?q=place_id:' . $this->provider_id,
        };
    }
}
