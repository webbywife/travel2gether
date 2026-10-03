<?php

namespace App\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Brute-force / automation protection for the auth endpoints.
        RateLimiter::for('login', function (Request $request) {
            $key = Str::lower((string) $request->input('email')) . '|' . $request->ip();

            return [
                Limit::perMinute(5)->by($key),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });

        RateLimiter::for('register', fn (Request $request) => [
            Limit::perMinute(3)->by($request->ip()),
            Limit::perDay(20)->by($request->ip()),
        ]);

        // Paid AI: per-visitor and per-account daily caps, plus a site-wide daily ceiling,
        // so nobody (guest scripts included) can run up the Anthropic bill.
        RateLimiter::for('ai-trippie', fn (Request $request) => [
            Limit::perMinute(8)->by('trippie-m:' . $request->ip()),
            Limit::perDay(60)->by('trippie-d:' . ($request->user()?->id ?? $request->ip())),
            Limit::perDay((int) config('services.anthropic.daily_cap', 2500))->by('ai-global'),
        ]);
        RateLimiter::for('ai-suggest', fn (Request $request) => [
            Limit::perMinute(6)->by('suggest-m:' . $request->user()?->id),
            Limit::perDay(80)->by('suggest-d:' . $request->user()?->id),
            Limit::perDay((int) config('services.anthropic.daily_cap', 2500))->by('ai-global'),
        ]);
        RateLimiter::for('ai-draft', fn (Request $request) => [
            Limit::perMinute(6)->by('draft-m:' . $request->user()?->id),
            Limit::perDay(40)->by('draft-d:' . $request->user()?->id),
            Limit::perDay((int) config('services.anthropic.daily_cap', 2500))->by('ai-global'),
        ]);

        RateLimiter::for('password-reset', fn (Request $request) => [
            Limit::perMinute(3)->by(Str::lower((string) $request->input('email')) . '|' . $request->ip()),
            Limit::perHour(10)->by($request->ip()),
        ]);

        RateLimiter::for('oauth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        Gate::define('admin', fn (User $user) => $user->isAdmin());

        $this->logSecurityEvents();
    }

    /**
     * Record authentication / authorization events to the `security` log channel.
     */
    private function logSecurityEvents(): void
    {
        $log = fn (string $event, array $context = []) => Log::channel('security')->info($event, $context + [
            'ip' => request()?->ip(),
            'ua' => request()?->userAgent(),
        ]);

        Event::listen(fn (Login $e) => $log('auth.login', ['user_id' => $e->user->getAuthIdentifier(), 'guard' => $e->guard]));
        Event::listen(fn (Logout $e) => $log('auth.logout', ['user_id' => $e->user?->getAuthIdentifier(), 'guard' => $e->guard]));
        $hash = fn (?string $email) => $email ? hash('sha256', \Illuminate\Support\Str::lower($email)) : null;
        Event::listen(fn (Failed $e) => $log('auth.failed', ['email_hash' => $hash($e->credentials['email'] ?? null), 'guard' => $e->guard]));
        Event::listen(fn (Lockout $e) => $log('auth.lockout', ['email_hash' => $hash($e->request->input('email'))]));
        Event::listen(fn (Registered $e) => $log('auth.registered', ['user_id' => $e->user->getAuthIdentifier()]));
        Event::listen(fn (Verified $e) => $log('auth.email_verified', ['user_id' => $e->user->getAuthIdentifier()]));
        Event::listen(fn (PasswordReset $e) => $log('auth.password_reset', ['user_id' => $e->user->getAuthIdentifier()]));
    }
}
