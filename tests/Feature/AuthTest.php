<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

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
