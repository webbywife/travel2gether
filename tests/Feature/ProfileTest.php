<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_a_user_can_view_and_update_their_profile(): void
    {
        $user = User::factory()->create(['name' => 'Marco', 'email' => 'marco@example.com']);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Marco', false);

        $this->actingAs($user)
            ->patch(route('profile.update'), ['name' => 'Marco Reyes', 'email' => 'marco@example.com'])
            ->assertRedirect(route('profile.edit'));

        $this->assertSame('Marco Reyes', $user->fresh()->name);
    }

    public function test_changing_the_email_resets_verification_and_sends_a_new_one(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);
        $this->assertNotNull($user->email_verified_at);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
        ]);

        $user->refresh();
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_a_user_with_a_password_must_confirm_it_to_change_it(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_a_google_only_user_can_set_a_password_without_one(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => '12345']);

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'password' => 'a-brand-new-password',
                'password_confirmation' => 'a-brand-new-password',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->fresh()->password);
    }
}
