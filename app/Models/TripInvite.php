<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TripInvite extends Model
{
    protected $fillable = [
        'trip_id', 'token', 'role', 'email', 'created_by',
        'max_uses', 'uses', 'expires_at', 'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (TripInvite $invite) {
            $invite->token ??= Str::random(40);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isUsable(): bool
    {
        return $this->revoked_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture())
            && ($this->max_uses === null || $this->uses < $this->max_uses);
    }

    public function url(): string
    {
        return route('trips.join', $this->token);
    }
}
