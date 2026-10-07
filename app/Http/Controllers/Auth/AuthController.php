<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RegistrationAttempted;
use App\Support\PendingTrip;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Anti-enumeration: whether or not the email already exists, the user lands
     * on the same "check your email" screen. An existing account is quietly
     * notified instead of the form reporting "already taken".
     */
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $existing = User::where('email', $data['email'])->first();

        if ($existing) {
            $existing->notify(new RegistrationAttempted);
        } else {
            $user = User::create($data);
            event(new Registered($user));   // sends the verification email
            \App\Support\VisitTracker::record($request, 'signup');
            Auth::login($user);
            $request->session()->regenerate();

            if ($trip = PendingTrip::claim($request, $user)) {
                return redirect()->route('trips.show', $trip)->with('status', 'Your trip is saved. Invite your group, or draft a day with AI. (We also sent you a link to confirm your email.)');
            }
        }

        return redirect()->route('register.pending')->with('pending_email', $data['email']);
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $request->session()->regenerate();

        if ($trip = PendingTrip::claim($request, $request->user())) {
            return redirect()->route('trips.show', $trip)->with('status', 'Your trip is saved. Invite your group, or draft a day with AI.');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
