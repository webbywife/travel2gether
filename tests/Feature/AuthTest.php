<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(string $id, string $email, string $name = 'Marco'): void
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
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_google_callback_creates_a_new_user_and_signs_them_in(): void
    {
        $this->fakeGoogleUser('google-123', 'newbie@example.com', 'Newbie');

        $this->get('/auth/google/callback')->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'newbie@example.com',
            'google_id' => 'google-123',
        ]);
    }

    public function test_google_callback_links_to_an_existing_email_account(): void
    {
        $user = User::create(['name' => 'Lea', 'email' => 'lea@example.com', 'password' => 'secret-passphrase']);
        $this->fakeGoogleUser('google-999', 'lea@example.com');

        $this->get('/auth/google/callback')->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-999', $user->fresh()->google_id);
        $this->assertSame(1, User::count());
    }

    public function test_google_routes_404_when_not_configured(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get('/auth/google/redirect')->assertNotFound();
    }

    public function test_a_visitor_can_register_and_lands_on_the_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Marco',
            'email' => 'marco@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'marco@example.com']);
    }

    public function test_a_registered_user_can_log_in_and_out(): void
    {
        $user = User::create([
            'name' => 'Lea',
            'email' => 'lea@example.com',
            'password' => 'secret-passphrase',
        ]);

        $this->assertTrue(Hash::check('secret-passphrase', $user->fresh()->password));

        $this->post('/login', ['email' => 'lea@example.com', 'password' => 'secret-passphrase'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_bad_credentials_are_rejected(): void
    {
        User::create(['name' => 'Lea', 'email' => 'lea@example.com', 'password' => 'secret-passphrase']);

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
}
