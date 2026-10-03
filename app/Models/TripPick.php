<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** The group's shared choice for one stop on a trip (Phase 2b). */
class TripPick extends Model
{
    protected $fillable = ['trip_id', 'stop_id', 'stop_option_id', 'picked_by'];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function stop(): BelongsTo
    {
        return $this->belongsTo(Stop::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(StopOption::class, 'stop_option_id');
    }

    public function picker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picked_by');
    }

    /** Shape sent to the itinerary page (initial render, polling, broadcasts). */
    public function toClient(): array
    {
        return [
            'optionId' => $this->stop_option_id,
            'byId' => $this->picked_by,
            'by' => $this->picker?->name,
            'at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
