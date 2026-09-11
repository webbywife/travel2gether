<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripSegment extends Model
{
    protected $fillable = [
        'trip_id', 'sort', 'from_text', 'to_text', 'airline_text',
        'from_code', 'to_code', 'airline_code', 'airline_name',
        'date', 'flight_no',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
