<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlaceRecommendationController;
use App\Http\Controllers\PlacesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripBuilderController;
use App\Http\Controllers\TripCalendarController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripDayController;
use App\Http\Controllers\TripJoinController;
use App\Http\Controllers\TripMemberController;
use App\Http\Controllers\TripPickController;
use App\Http\Controllers\TrippieController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::get('/gallery/{slug}', [GalleryController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('gallery.show');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');

// Trippie — the planning-buddy chatbot (open to guests, rate-limited).
Route::post('/trippie', [TrippieController::class, 'chat'])->middleware('throttle:ai-trippie')->name('trippie.chat');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    // Forgot password (email + password accounts)
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])
        ->middleware('throttle:password-reset')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])
        ->middleware('throttle:password-reset-submit')->name('password.update');

    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
        ->middleware('throttle:oauth')->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->middleware('throttle:oauth');
});

// Shown after registering — identical for a new and an already-existing email.
Route::view('/register/pending', 'auth.register-pending')->name('register.pending');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Email verification
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')->name('verification.send');
});

Route::get('/dashboard', [PageController::class, 'dashboard'])
    ->middleware(array_filter(['auth', config('auth.require_verification') ? 'verified' : null]))
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::patch('/analytics/users/{user}/subscription', [AnalyticsController::class, 'toggleSubscription'])->name('analytics.toggle-subscription');
});

Route::view('/upgrade', 'upgrade')->name('upgrade');

// POI search / indexing for building itineraries (Google Places API New).
Route::middleware(['auth', 'throttle:40,1'])->group(function () {
    Route::get('/places/search', [PlacesController::class, 'search'])->name('places.search');
    Route::get('/places/explore', [PlacesController::class, 'explore'])->middleware('throttle:12,1')->name('places.explore');
    Route::get('/places/{placeId}', [PlacesController::class, 'show'])->name('places.show');
});

// Short, typeable link for TikTok videos (captions and comments there aren't clickable).
// Only used on TikTok, so visits through it count as TikTok in the launch funnel.
Route::redirect('/tokyo', '/t/tokyo-2026?ref=tiktok', 302);
foreach ([
    'seoul' => 'seoul', 'nami' => 'gapyeong-nami-island', 'kyoto' => 'kyoto', 'osaka' => 'osaka', 'fukuoka' => 'fukuoka',
    'kamakura' => 'kamakura-enoshima', 'fuji' => 'mt-fuji-five-lakes', 'hakone' => 'hakone', 'taipei' => 'taipei-keelung',
    'hongkong' => 'hong-kong', 'singapore' => 'singapore', 'paris' => 'paris', 'rome' => 'rome', 'florence' => 'florence',
    'barcelona' => 'barcelona', 'nyc' => 'new-york-city', 'grandcanyon' => 'grand-canyon-national-park',
    'antelope' => 'antelope-canyon-page-arizona', 'baguio' => 'cordillera-ph', 'palawan' => 'mimaropa-ph', 'bohol' => 'central-visayas-ph',
] as $short => $gallerySlug) {
    Route::redirect('/' . $short, '/gallery/' . $gallerySlug . '?ref=tiktok', 302);
}

// Public / shared trip URL. Seoul 2026 is the seeded sample.
Route::get('/t/{trip:slug}', [TripController::class, 'show'])->name('trips.show');
Route::get('/t/{trip:slug}/print', [TripController::class, 'print'])->name('trips.print');
Route::get('/t/{trip:slug}/calendar.ics', [TripCalendarController::class, 'show'])->middleware('throttle:30,1')->name('trips.calendar');
Route::get('/t/{trip:slug}/picks', [TripPickController::class, 'index'])->middleware('throttle:60,1')->name('trips.picks.index');

// Collaboration (Phase 2) — all require a signed-in user.
Route::middleware('auth')->group(function () {
    Route::get('/trips/create', [TripBuilderController::class, 'create'])->name('trips.create');
    Route::post('/trips', [TripBuilderController::class, 'store'])->name('trips.store');

    Route::get('/t/{trip:slug}/edit', [TripController::class, 'edit'])->name('trips.edit');
    Route::patch('/t/{trip:slug}', [TripController::class, 'update'])->name('trips.update');
    Route::delete('/t/{trip:slug}', [TripController::class, 'destroy'])->name('trips.destroy');

    Route::post('/t/{trip:slug}/duplicate', [TripController::class, 'duplicate'])->name('trips.duplicate');

    Route::post('/t/{trip:slug}/days/{day}/generate', [TripDayController::class, 'generate'])
        ->middleware('throttle:ai-draft')->name('trips.days.generate');
    Route::get('/t/{trip:slug}/days/{day}/ai-status', [TripDayController::class, 'aiStatus'])
        ->middleware('throttle:120,1')->name('trips.days.ai-status');
    Route::post('/t/{trip:slug}/days/{day}/suggest', [TripDayController::class, 'suggest'])
        ->middleware('throttle:ai-suggest')->name('trips.days.suggest');
    Route::get('/t/{trip:slug}/days/{day}/edit', [TripDayController::class, 'edit'])->name('trips.days.edit');
    Route::patch('/t/{trip:slug}/days/{day}', [TripDayController::class, 'update'])->name('trips.days.update');

    // Phase 2b — shared picks (owners/editors).
    Route::put('/t/{trip:slug}/stops/{stop}/pick', [TripPickController::class, 'update'])
        ->middleware('throttle:60,1')->name('trips.picks.update');
    Route::delete('/t/{trip:slug}/stops/{stop}/pick', [TripPickController::class, 'destroy'])
        ->middleware('throttle:60,1')->name('trips.picks.destroy');

    Route::post('/t/{trip:slug}/invites', [TripMemberController::class, 'storeInvite'])->name('trips.invites.store');
    Route::delete('/t/{trip:slug}/invites/{invite:token}', [TripMemberController::class, 'revokeInvite'])->name('trips.invites.revoke');

    Route::patch('/t/{trip:slug}/members/{user}', [TripMemberController::class, 'updateRole'])->name('trips.members.update');
    Route::delete('/t/{trip:slug}/members/{user}', [TripMemberController::class, 'destroy'])->name('trips.members.destroy');
    Route::post('/t/{trip:slug}/leave', [TripMemberController::class, 'leave'])->name('trips.leave');

    Route::get('/join/{invite:token}', [TripJoinController::class, 'show'])->name('trips.join');
    Route::post('/join/{invite:token}', [TripJoinController::class, 'store'])->name('trips.join.accept');

    // Thumbs up/down on a place — signed-in users only.
    Route::post('/places/{place}/recommend', [PlaceRecommendationController::class, 'store'])
        ->middleware('throttle:30,1')->name('places.recommend');
});
