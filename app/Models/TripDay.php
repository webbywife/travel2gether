<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripDay extends Model
{
    protected $fillable = [
        'trip_id', 'day_number', 'date', 'title', 'title_secondary', 'summary',
        'weather_tag', 'forecast_date', 'temp_high', 'temp_low', 'weather_note',
        'outfit_chips', 'outfit_photos', 'area_label', 'map_embed_url',
        'lat', 'lon', 'hiccups', 'sort', 'source',
        'hotel_name', 'hotel_address', 'hotel_lat', 'hotel_lon',
    ];

    protected $casts = [
        'date' => 'date',
        'forecast_date' => 'date',
        'outfit_chips' => 'array',
        'outfit_photos' => 'array',
        'hiccups' => 'array',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(Stop::class)->orderBy('sort');
    }
}
