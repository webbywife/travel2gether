<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Trip extends Model
{
    protected $fillable = [
        'slug', 'title', 'tagline', 'subhead', 'destination', 'origin_label',
        'start_date', 'end_date', 'party_size', 'currency', 'map_provider',
        'lat', 'lon', 'forecast_note', 'segments', 'stats', 'is_public', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'segments' => 'array',
        'stats' => 'array',
        'is_public' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function days(): HasMany
    {
        return $this->hasMany(TripDay::class)->orderBy('sort')->orderBy('day_number');
    }

    public function budgetLines(): HasMany
    {
        return $this->hasMany(BudgetLine::class)->orderBy('sort');
    }

    public function stops(): HasManyThrough
    {
        return $this->hasManyThrough(Stop::class, TripDay::class);
    }

    public function nights(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date);
    }
}
