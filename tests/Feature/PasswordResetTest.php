<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_links_to_forgot_password(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Forgot password?')->assertSee(route('password.request'), false);
        $this->get(route('password.request'))->assertOk()->assertSee('Forgot your password?');
    }

    public function test_a_reset_link_is_emailed_to_a_real_account(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'lea@example.com']);

        $this->post(route('password.email'), ['email' => 'LEA@example.com'])
            ->assertSessionHas('status', fn ($s) => str_contains($s, "If an account exists"));

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_unknown_emails_get_the_same_answer_and_nothing_is_sent(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHas('status', fn ($s) => str_contains($s, "If an account exists"))
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_a_valid_link_sets_a_new_password_and_signs_out_other_sessions(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-123')]);
        $token = Password::createToken($user);
        \DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk()->assertSee('Choose a new password');

        $this->post(route('password.update'), [
            'token' => $token, 'email' => $user->email,
            'password' => 'brand-new-password-42', 'password_confirmation' => 'brand-new-password-42',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('brand-new-password-42', $user->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'other-device']);

        // single use
        $this->post(route('password.update'), [
            'token' => $token, 'email' => $user->email,
            'password' => 'another-password-99', 'password_confirmation' => 'another-password-99',
        ])->assertSessionHasErrors('email');
    }

    public function test_a_bad_or_expired_token_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password-123')]);

        $this->post(route('password.update'), [
            'token' => 'not-a-real-token', 'email' => $user->email,
            'password' => 'brand-new-password-42', 'password_confirmation' => 'brand-new-password-42',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
    }

    public function test_short_passwords_are_refused(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token, 'email' => $user->email, 'password' => 'short', 'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
    }

    public function test_requests_are_rate_limited(): void
    {
        Notification::fake();
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('password.email'), ['email' => 'spam@example.com']);
        }
        $this->post(route('password.email'), ['email' => 'spam@example.com'])->assertStatus(429);
    }

    public function test_signed_in_users_are_sent_away_from_the_reset_pages(): void
    {
        $this->actingAs(User::factory()->create())->get(route('password.request'))->assertRedirect();
    }
}
