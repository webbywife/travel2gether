<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * "Forgot password?" for people who signed up with email + password.
 * Uses Laravel's password broker: single-use tokens, stored hashed, valid
 * for 60 minutes. Never reveals whether an email has an account.
 */
class PasswordResetController extends Controller
{
    private const SENT = "If an account exists for that email, we've sent a link to reset your password. It's valid for 60 minutes — check your spam folder too.";

    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);

        $status = Password::sendResetLink(['email' => Str::lower($data['email'])]);

        Log::channel('security')->info('auth.password_reset_requested', [
            'email_hash' => hash('sha256', Str::lower($data['email'])),
            'ip' => $request->ip(),
            'sent' => $status === Password::RESET_LINK_SENT,
        ]);

        // Same answer whether or not the account exists (no account enumeration).
        // Only the broker's own per-account throttle is surfaced, as a neutral wait.
        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput()->with('status', 'Please wait a minute before asking for another link.');
        }

        return back()->with('status', self::SENT);
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => (string) $request->query('email', '')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)],
        ]);

        $status = Password::reset(
            ['email' => Str::lower($data['email'])] + $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Sign the account out everywhere else.
                $table = config('session.table', 'sessions');
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('user_id', $user->id)->delete();
                }

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'This reset link is invalid or has expired. Please request a new one.',
            ]);
        }

        return redirect()->route('login')->with('status', 'Your password has been reset — log in with your new password.');
    }
}
