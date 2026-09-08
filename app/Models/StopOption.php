<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StopOption extends Model
{
    protected $fillable = [
        'stop_id', 'place_id', 'sort', 'name', 'tier', 'note', 'cost_min', 'cost_max',
        'currency', 'weather_tag', 'map_provider', 'map_url',
        'is_default_pick', 'is_sponsored',
    ];

    protected $casts = [
        'is_default_pick' => 'boolean',
        'is_sponsored' => 'boolean',
    ];

    public function stop(): BelongsTo
    {
        return $this->belongsTo(Stop::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** Midpoint of the cost range, used to seed the budget worksheet. */
    public function costMidpoint(): ?int
    {
        if ($this->cost_min === null && $this->cost_max === null) {
            return null;
        }

        return (int) round((($this->cost_min ?? $this->cost_max) + ($this->cost_max ?? $this->cost_min)) / 2);
    }

    public function costRangeLabel(): ?string
    {
        if ($this->cost_min === null && $this->cost_max === null) {
            return null;
        }
        $cur = $this->currency ?? $this->stop?->day?->trip?->currency ?? '';
        if ($this->cost_min === 0 && ($this->cost_max === 0 || $this->cost_max === null)) {
            return 'Free';
        }
        if ($this->cost_max === null || $this->cost_min === $this->cost_max) {
            return trim("{$cur} " . number_format($this->cost_min ?? $this->cost_max));
        }

        return trim("{$cur} " . number_format($this->cost_min) . '–' . number_format($this->cost_max));
    }
}
