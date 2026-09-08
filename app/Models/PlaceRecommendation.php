<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceRecommendation extends Model
{
    protected $fillable = ['place_id', 'user_id', 'vote'];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
