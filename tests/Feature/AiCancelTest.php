<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\TripDay;
use App\Models\User;
use Tests\TestCase;

class AiCancelTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_owner_can_free_a_stuck_day_and_a_late_job_does_nothing(): void
    {
        $user = User::factory()->create();
        $trip = Trip::create(['slug' => 'stuck-trip', 'title' => 'Stuck', 'destination' => 'Kyoto, Japan',
            'start_date' => now()->addDays(5), 'end_date' => now()->addDays(8), 'owner_id' => $user->id]);
        $trip->members()->attach($user->id, ['role' => 'owner']);
        $day = $trip->days()->create(['day_number' => 1, 'date' => now()->addDays(5), 'title' => 'Arrival',
            'sort' => 1, 'hiccups' => [], 'ai_status' => 'queued']);

        $this->actingAs($user)->post(route('trips.days.ai-cancel', [$trip, $day->id]))->assertRedirect();

        $day->refresh();
        $this->assertSame('failed', $day->ai_status);

        (new \App\Jobs\DraftDayWithAi($day->id))->handle(
            app(\App\Services\GenerateDayItinerary::class),
            app(\App\Actions\IndexGeneratedPlaces::class),
            app(\App\Services\StopPhotoFinder::class),
        );
        $this->assertSame('failed', $day->fresh()->ai_status); // job skipped, not turned into "running"
    }
}
