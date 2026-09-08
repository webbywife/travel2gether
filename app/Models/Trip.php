<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trip_user')
            ->withPivot(['role', 'invited_by'])
            ->withTimestamps();
    }

    public function invites(): HasMany
    {
        return $this->hasMany(TripInvite::class)->latest();
    }

    /** owner | editor | viewer | null */
    public function roleFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return $this->members->firstWhere('id', $user->id)?->pivot->role
            ?? $this->members()->where('users.id', $user->id)->value('role');
    }

    public function isMember(?User $user): bool
    {
        return $user !== null && $this->roleFor($user) !== null;
    }

    public function canEdit(?User $user): bool
    {
        return in_array($this->roleFor($user), ['owner', 'editor'], true);
    }

    public function canView(?User $user): bool
    {
        return $this->is_public || $this->isMember($user);
    }

    /** Trips a user owns or was invited to. */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->where('is_public', true);
        }

        return $query->where(fn (Builder $q) => $q
            ->where('created_by', $user->id)
            ->orWhereHas('members', fn (Builder $m) => $m->where('users.id', $user->id)));
    }

    public function nights(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date);
    }
}
