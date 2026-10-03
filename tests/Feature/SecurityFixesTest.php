<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Database\Seeders\Seoul2026Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicating_a_trip_respects_the_free_trip_limit(): void
    {
        $this->seed(Seoul2026Seeder::class);
        $sample = Trip::where('slug', 'seoul-2026')->first();
        $user = User::factory()->create();
        for ($i = 0; $i < User::FREE_TRIP_LIMIT; $i++) {
            $t = Trip::create(['slug' => "mine-$i", 'title' => "T$i", 'destination' => 'X', 'start_date' => '2027-01-01',
                'end_date' => '2027-01-02', 'is_public' => false, 'created_by' => $user->id]);
            $t->members()->attach($user->id, ['role' => 'owner']);
        }

        $this->actingAs($user)->post(route('trips.duplicate', $sample))->assertRedirect(route('upgrade'));
        $this->assertSame(User::FREE_TRIP_LIMIT, Trip::where('created_by', $user->id)->count());
    }

    public function test_changing_password_signs_out_other_devices_and_rotates_remember_me(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-123'), 'remember_token' => 'old-token']);
        \DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'old-password-123',
            'password' => 'new-password-4567', 'password_confirmation' => 'new-password-4567',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseMissing('sessions', ['id' => 'other-device']);
        $this->assertNotSame('old-token', $user->fresh()->remember_token);
    }

    public function test_changing_email_requires_the_current_password(): void
    {
        $user = User::factory()->create(['email' => 'lea@example.com', 'password' => Hash::make('right-password-1')]);

        $this->actingAs($user)->patch(route('profile.update'), ['name' => 'Lea', 'email' => 'evil@example.com'])
            ->assertSessionHasErrors('current_password');
        $this->assertSame('lea@example.com', $user->fresh()->email);

        $this->actingAs($user)->patch(route('profile.update'), ['name' => 'Lea', 'email' => 'new@example.com', 'current_password' => 'right-password-1'])
            ->assertSessionHasNoErrors();
        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_admin_requires_a_verified_email(): void
    {
        config(['app.admin_emails' => ['boss@example.com']]);
        $this->assertFalse(User::factory()->unverified()->make(['email' => 'boss@example.com'])->isAdmin());
        $this->assertTrue(User::factory()->make(['email' => 'boss@example.com', 'email_verified_at' => now()])->isAdmin());
    }

    public function test_guests_are_capped_on_trippie(): void
    {
        $this->mock(\App\Services\ClaudeClient::class, function ($m) {
            $m->shouldReceive('enabled')->andReturn(true);
            $m->shouldReceive('chat')->andReturn('[happy] hi');
        });
        for ($i = 0; $i < 8; $i++) {
            $this->postJson(route('trippie.chat'), ['message' => 'hi'])->assertOk();
        }
        $this->postJson(route('trippie.chat'), ['message' => 'hi'])->assertStatus(429);
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_haiku_requests_omit_unsupported_options(): void
    {
        config(['services.anthropic.model' => 'claude-haiku-4-5 ']); // note the stray space, as on prod
        $c = app(\App\Services\ClaudeClient::class);
        $r = new \ReflectionClass($c);
        $this->assertSame('claude-haiku-4-5', $c->model());
        $this->assertFalse($r->getMethod('supportsEffort')->invoke($c));
        $this->assertFalse($r->getMethod('supportsFallbacks')->invoke($c));

        config(['services.anthropic.model' => 'claude-opus-5-5']);
        $this->assertTrue($r->getMethod('supportsEffort')->invoke($c));
        $this->assertTrue($r->getMethod('supportsFallbacks')->invoke($c));
    }
}
