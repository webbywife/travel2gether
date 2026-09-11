<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'google_id', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Trips a free member may own at once — see isPaid(), TripBuilderController. */
    public const FREE_TRIP_LIMIT = 3;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function ownedTrips(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Trip::class, 'created_by');
    }

    public function trips(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_user')
            ->withPivot(['role', 'invited_by'])
            ->withTimestamps();
    }

    /** Site admin — gated by email (ADMIN_EMAILS), not a roles table. See config/app.php. */
    public function isAdmin(): bool
    {
        return in_array($this->email, config('app.admin_emails', []), true);
    }

    /**
     * No payment processing yet — 'subscription' is flipped by hand (tinker,
     * or an admin control later). Admins are always treated as paid.
     */
    public function isPaid(): bool
    {
        return $this->isAdmin() || $this->subscription === 'paid';
    }

    /** Whether this member has hit the free-tier cap on trips they own. */
    public function hasReachedTripLimit(): bool
    {
        return ! $this->isPaid() && $this->ownedTrips()->count() >= self::FREE_TRIP_LIMIT;
    }
}
