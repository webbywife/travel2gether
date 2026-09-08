<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    protected $fillable = [
        'provider', 'provider_id', 'name', 'formatted_address', 'lat', 'lon',
        'types', 'rating', 'rating_count', 'price_level', 'phone', 'website',
        'raw', 'details_fetched_at',
    ];

    protected $casts = [
        'types' => 'array',
        'raw' => 'array',
        'lat' => 'float',
        'lon' => 'float',
        'rating' => 'float',
        'details_fetched_at' => 'datetime',
    ];

    protected $hidden = ['raw'];

    public function mapUrl(): string
    {
        return match ($this->provider) {
            'kakao' => "https://map.kakao.com/link/map/{$this->provider_id}",
            'gemini' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($this->name),
            default => 'https://www.google.com/maps/place/?q=place_id:' . $this->provider_id,
        };
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(PlaceRecommendation::class);
    }

    /** Net score: thumbs up minus thumbs down. */
    public function score(): int
    {
        if ($this->recommendations_sum_vote !== null) {
            return (int) $this->recommendations_sum_vote;
        }

        return $this->relationLoaded('recommendations')
            ? (int) $this->recommendations->sum('vote')
            : (int) $this->recommendations()->sum('vote');
    }

    public function upCount(): int
    {
        if ($this->recommendations_up_count !== null) {
            return (int) $this->recommendations_up_count;
        }

        return $this->relationLoaded('recommendations')
            ? $this->recommendations->where('vote', 1)->count()
            : $this->recommendations()->where('vote', 1)->count();
    }

    public function downCount(): int
    {
        if ($this->recommendations_down_count !== null) {
            return (int) $this->recommendations_down_count;
        }

        return $this->relationLoaded('recommendations')
            ? $this->recommendations->where('vote', -1)->count()
            : $this->recommendations()->where('vote', -1)->count();
    }

    /** +1 | -1 | null for the given user's current vote. */
    public function voteFor(?User $user): ?int
    {
        if (! $user) {
            return null;
        }

        return $this->recommendations->firstWhere('user_id', $user->id)?->vote;
    }

    /** Places the community actually recommends — net-positive, with at least one vote. */
    public function scopeTopRecommended(Builder $query, int $limit = 6): Builder
    {
        // A plain HAVING on a withSum alias needs a GROUP BY on some drivers
        // (SQLite) but not others — sidestep the portability gap with a
        // correlated subquery in WHERE instead.
        return $query
            ->withSum('recommendations as recommendations_sum_vote', 'vote')
            ->withCount(['recommendations as recommendations_up_count' => fn (Builder $q) => $q->where('vote', 1)])
            ->whereRaw(
                '(select coalesce(sum(vote), 0) from place_recommendations where place_recommendations.place_id = places.id) > 0'
            )
            ->orderByDesc('recommendations_sum_vote')
            ->limit($limit);
    }
}
