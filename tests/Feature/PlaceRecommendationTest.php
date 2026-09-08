<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceRecommendationTest extends TestCase
{
    use RefreshDatabase;

    private function place(): Place
    {
        return Place::create(['provider' => 'gemini', 'provider_id' => 'four-stones-coffee', 'name' => 'Four Stones Coffee Roasters']);
    }

    public function test_a_guest_cannot_vote(): void
    {
        $place = $this->place();

        $this->post(route('places.recommend', $place), ['vote' => 'up'])
            ->assertRedirect(route('login'));

        $this->assertSame(0, $place->recommendations()->count());
    }

    public function test_a_signed_in_user_can_thumbs_up_a_place(): void
    {
        $place = $this->place();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('places.recommend', $place), ['vote' => 'up'])
            ->assertOk()
            ->assertJson(['up' => 1, 'down' => 0, 'my_vote' => 1]);

        $this->assertSame(1, $place->score());
    }

    public function test_voting_the_same_thumb_again_clears_the_vote(): void
    {
        $place = $this->place();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('places.recommend', $place), ['vote' => 'up']);

        $this->actingAs($user)
            ->postJson(route('places.recommend', $place), ['vote' => 'up'])
            ->assertOk()
            ->assertJson(['up' => 0, 'down' => 0, 'my_vote' => null]);

        $this->assertSame(0, $place->fresh()->score());
    }

    public function test_voting_the_other_thumb_flips_it(): void
    {
        $place = $this->place();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('places.recommend', $place), ['vote' => 'up']);

        $this->actingAs($user)
            ->postJson(route('places.recommend', $place), ['vote' => 'down'])
            ->assertOk()
            ->assertJson(['up' => 0, 'down' => 1, 'my_vote' => -1]);

        $this->assertSame(-1, $place->fresh()->score());
    }

    public function test_a_second_user_recommending_the_same_place_adds_to_the_shared_score(): void
    {
        $place = $this->place();
        [$a, $b] = User::factory()->count(2)->create();

        $this->actingAs($a)->postJson(route('places.recommend', $place), ['vote' => 'up']);
        $this->actingAs($b)->postJson(route('places.recommend', $place), ['vote' => 'up']);

        $this->assertSame(2, $place->fresh()->score());
    }

    public function test_the_dashboard_lists_only_net_positive_places(): void
    {
        $liked = $this->place();
        $ignored = Place::create(['provider' => 'gemini', 'provider_id' => 'unranked-place', 'name' => 'Unranked Place']);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('places.recommend', $liked), ['vote' => 'up']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Top recommendations')
            ->assertSee('Four Stones Coffee Roasters')
            ->assertDontSee('Unranked Place');
    }
}
