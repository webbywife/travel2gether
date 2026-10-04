<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisitTrackingTest extends TestCase
{
    use RefreshDatabase;

    private const IG_APP = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Instagram 350.0';

    public function test_a_tiktok_link_is_followed_through_the_funnel_once_per_step(): void
    {
        $this->seed(Seoul2026Seeder::class);

        $this->get('/?ref=tiktok')->assertOk();
        $this->get(route('trips.show', 'seoul-2026'))->assertOk();
        $this->get(route('register'))->assertOk();

        $rows = DB::table('visit_events')->get();
        $this->assertSame(['landing', 'register', 'sample'], $rows->pluck('event')->sort()->values()->all());
        $this->assertSame(['tiktok'], $rows->pluck('source')->unique()->values()->all());
    }

    public function test_the_same_visitor_counts_once_per_step_per_day(): void
    {
        $request = \Illuminate\Http\Request::create('/', 'GET', ['ref' => 'ig']);
        $request->setLaravelSession($session = app('session')->driver());
        $session->start();

        \App\Support\VisitTracker::record($request, 'landing');
        \App\Support\VisitTracker::record($request, 'landing');

        $this->assertSame(1, DB::table('visit_events')->where('event', 'landing')->count());
    }

    public function test_the_instagram_in_app_browser_is_recognised_without_a_ref(): void
    {
        $this->withHeader('User-Agent', self::IG_APP)->get('/')->assertOk();

        $row = DB::table('visit_events')->first();
        $this->assertSame('ig', $row->source);
        $this->assertTrue((bool) $row->mobile);
    }

    public function test_bots_and_signed_in_members_are_not_counted_and_nothing_personal_is_stored(): void
    {
        $this->withHeader('User-Agent', 'facebookexternalhit/1.1')->get('/')->assertOk();
        $this->actingAs(User::factory()->create())->get('/')->assertOk();

        $this->assertSame(0, DB::table('visit_events')->count());
        $this->assertSame(['id', 'day', 'source', 'event', 'visitor', 'mobile', 'created_at'],
            array_keys((array) DB::selectOne('select * from visit_events union all select null,null,null,null,null,null,null limit 1')));
    }

    public function test_signing_up_is_counted_with_the_source_the_visitor_arrived_from(): void
    {
        $this->get('/?ref=ig');
        $this->post(route('register'), [
            'name' => 'Ana', 'email' => 'ana@example.com',
            'password' => 'a-long-password-123', 'password_confirmation' => 'a-long-password-123',
        ]);

        $this->assertDatabaseHas('visit_events', ['event' => 'signup', 'source' => 'ig']);
    }

    public function test_the_short_tiktok_link_opens_the_tokyo_sample_tagged_as_tiktok(): void
    {
        $this->get('/tokyo')->assertRedirect('/t/tokyo-2026?ref=tiktok');
    }

    public function test_place_short_links_open_the_gallery_tagged_as_tiktok(): void
    {
        $this->get('/kyoto')->assertRedirect('/gallery/kyoto?ref=tiktok');
        $this->get('/nyc')->assertRedirect('/gallery/new-york-city?ref=tiktok');
        $this->get('/baguio')->assertRedirect('/gallery/cordillera-ph?ref=tiktok');
    }
}
