<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    private function enabled(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    public function redirect(): RedirectResponse
    {
        abort_unless($this->enabled(), 404);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless($this->enabled(), 404);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::channel('security')->warning('auth.google.callback_failed', [
                'message' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in did not complete. Please try again.',
            ]);
        }

        // Google only returns an email it has verified, but confirm the claim
        // before we trust it enough to attach to an existing password account.
        $emailVerified = (bool) ($googleUser->user['email_verified'] ?? false);

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $emailOwner = User::where('email', $googleUser->getEmail())->first();

            if ($emailOwner && ! $emailVerified) {
                return redirect()->route('login')->withErrors([
                    'email' => 'An account already uses that email. Log in with your password first, then link Google from your account.',
                ]);
            }

            $user = $emailOwner; // null, or an account whose address Google vouched for
        }

        if ($user) {
            $user->forceFill(array_filter([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => ($emailVerified && ! $user->email_verified_at) ? now() : $user->email_verified_at,
            ]))->save();
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($googleUser->getEmail(), '@'),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            if ($emailVerified) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        }

        Auth::login($user, remember: $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
