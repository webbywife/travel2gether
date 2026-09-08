<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\RegistrationAttempted;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(string $id, string $email, string $name = 'Marco', bool $verified = true): void
    {
        config([
            'services.google.client_id' => 'test-client',
            'services.google.client_secret' => 'test-secret',
        ]);

        $socialiteUser = (new SocialiteUser)->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'avatar' => 'https://example.com/a.jpg',
        ])->setRaw(['email_verified' => $verified]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_registering_lands_on_the_check_your_email_screen_and_sends_verification(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Newbie',
            'email' => 'newbie@example.com',
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ])->assertRedirect(route('register.pending'));

        $this->assertAuthenticated();
        $user = User::firstWhere('email', 'newbie@example.com');
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registering_with_an_existing_email_does_not_reveal_it(): void
    {
        Notification::fake();
        $existing = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'name' => 'Impostor',
            'email' => 'taken@example.com',
            'password' => 'correct-horse-battery-staple',
            'password_confirmation' => 'correct-horse-battery-staple',
        ]);

        $response->assertRedirect(route('register.pending'));
        $response->assertSessionHasNoErrors();          // no "email already taken"
        $this->assertGuest();                            // impostor is not logged in
        $this->assertSame(1, User::where('email', 'taken@example.com')->count());
        Notification::assertSentTo($existing, RegistrationAttempted::class);
    }

    public function test_short_passwords_are_rejected(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Shorty',
            'email' => 'shorty@example.com',
            'password' => 'short-pass',
            'password_confirmation' => 'short-pass',
        ])->assertRedirect('/register')->assertSessionHasErrors('password');
    }

    public function test_unverified_users_cannot_reach_the_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('verification.notice'));
    }

    public function test_a_registered_user_can_log_in_and_out(): void
    {
        $user = User::factory()->create([
            'email' => 'lea@example.com',
            'password' => 'secret-passphrase-1234',
        ]);

        $this->assertTrue(Hash::check('secret-passphrase-1234', $user->fresh()->password));

        $this->post('/login', ['email' => 'lea@example.com', 'password' => 'secret-passphrase-1234'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_bad_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'lea@example.com', 'password' => 'secret-passphrase-1234']);

        $this->from('/login')
            ->post('/login', ['email' => 'lea@example.com', 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_dashboard_requires_auth(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_google_callback_creates_a_verified_user_and_signs_them_in(): void
    {
        $this->fakeGoogleUser('google-123', 'newbie@example.com', 'Newbie');

        $this->get('/auth/google/callback')->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $user = User::firstWhere('email', 'newbie@example.com');
        $this->assertSame('google-123', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_google_links_to_an_existing_account_only_when_google_verified_the_email(): void
    {
        $user = User::factory()->create(['email' => 'lea@example.com', 'google_id' => null]);
        $this->fakeGoogleUser('google-999', 'lea@example.com', verified: true);

        $this->get('/auth/google/callback')->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-999', $user->fresh()->google_id);
        $this->assertSame(1, User::count());
    }

    public function test_google_refuses_to_link_an_unverified_email_to_an_existing_account(): void
    {
        User::factory()->create(['email' => 'lea@example.com', 'google_id' => null]);
        $this->fakeGoogleUser('google-evil', 'lea@example.com', verified: false);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_google_routes_404_when_not_configured(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get('/auth/google/redirect')->assertNotFound();
    }
}
