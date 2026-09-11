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
    /** Free AI re-drafts per trip before a paywall kicks in — see canRegenerate(). */
    public const FREE_REGENERATIONS = 1;

    protected $fillable = [
        'slug', 'title', 'tagline', 'subhead', 'destination', 'origin_label',
        'start_date', 'end_date', 'party_size', 'currency', 'map_provider',
        'lat', 'lon', 'hotel_name', 'hotel_address', 'interests',
        'forecast_note', 'segments', 'stats', 'is_public', 'created_by',
        'regenerations_used',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'segments' => 'array',
        'stats' => 'array',
        'interests' => 'array',
        'is_public' => 'boolean',
        'regenerations_used' => 'integer',
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

    /** Normalized flight legs (from CreateTrip) — feeds the airline/route analytics. */
    public function tripSegments(): HasMany
    {
        return $this->hasMany(TripSegment::class)->orderBy('sort');
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

    /** The seeded public samples (Seoul, Tokyo) — no owner, visible to everyone. */
    public function isSample(): bool
    {
        return $this->is_public && $this->created_by === null;
    }

    public function canEdit(?User $user): bool
    {
        // Nobody is a member of a sample trip, so the normal owner/editor
        // check always fails for them — admins get in anyway, so the samples
        // can be kept up to date.
        if ($this->isSample()) {
            return (bool) $user?->isAdmin();
        }

        return in_array($this->roleFor($user), ['owner', 'editor'], true);
    }

    public function canView(?User $user): bool
    {
        return $this->is_public || $this->isMember($user);
    }

    /**
     * Whether $user may (re)draft a day with AI right now. The *first* draft
     * of any day is always allowed for whoever can edit the trip — this only
     * gates *re*-drafting a day that's already been AI-drafted once.
     */
    public function canRegenerate(?User $user): bool
    {
        return (bool) $user?->isAdmin() || $this->regenerations_used < self::FREE_REGENERATIONS;
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
