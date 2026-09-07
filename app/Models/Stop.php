<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stop extends Model
{
    protected $fillable = [
        'trip_day_id', 'sort', 'time', 'title', 'description', 'cost_label',
        'weather_tag', 'map_provider', 'map_url', 'thumb_url', 'hiccup',
        'has_options', 'option_label',
    ];

    protected $casts = [
        'has_options' => 'boolean',
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(TripDay::class, 'trip_day_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(StopOption::class)->orderBy('sort');
    }
}
