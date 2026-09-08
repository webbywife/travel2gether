<?php

namespace App\Events;

use App\Models\Trip;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A single change on a trip, streamed live to everyone viewing it.
 *
 * Phase 2b dispatches this from the pick / budget / stop controllers, e.g.
 *   TripActivity::dispatch($trip, 'pick', ['stopId' => 12, 'optionId' => 44], $user);
 * The itinerary page listens on the presence channel and reconciles.
 */
class TripActivity implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public Trip $trip,
        public string $type,
        public array $payload = [],
        public ?int $actorId = null,
        public ?string $actorName = null,
    ) {}

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('trip.' . $this->trip->slug);
    }

    public function broadcastAs(): string
    {
        return 'trip.activity';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'payload' => $this->payload,
            'actorId' => $this->actorId,
            'actorName' => $this->actorName,
            'at' => now()->toIso8601String(),
        ];
    }
}
