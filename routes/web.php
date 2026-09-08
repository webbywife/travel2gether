<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlacesController;
use App\Http\Controllers\TripBuilderController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripDayController;
use App\Http\Controllers\TripJoinController;
use App\Http\Controllers\TripMemberController;
use App\Http\Controllers\TrippieController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations');

// Trippie — the planning-buddy chatbot (open to guests, rate-limited).
Route::post('/trippie', [TrippieController::class, 'chat'])->middleware('throttle:15,1')->name('trippie.chat');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

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

// POI search / indexing for building itineraries (Google Places API New).
Route::middleware(['auth', 'throttle:40,1'])->group(function () {
    Route::get('/places/search', [PlacesController::class, 'search'])->name('places.search');
    Route::get('/places/{placeId}', [PlacesController::class, 'show'])->name('places.show');
});

// Public / shared trip URL. Seoul 2026 is the seeded sample.
Route::get('/t/{trip:slug}', [TripController::class, 'show'])->name('trips.show');

// Collaboration (Phase 2) — all require a signed-in user.
Route::middleware('auth')->group(function () {
    Route::get('/trips/create', [TripBuilderController::class, 'create'])->name('trips.create');
    Route::post('/trips', [TripBuilderController::class, 'store'])->name('trips.store');

    Route::post('/t/{trip:slug}/duplicate', [TripController::class, 'duplicate'])->name('trips.duplicate');

    Route::post('/t/{trip:slug}/days/{day}/generate', [TripDayController::class, 'generate'])
        ->middleware('throttle:12,1')->name('trips.days.generate');

    Route::post('/t/{trip:slug}/invites', [TripMemberController::class, 'storeInvite'])->name('trips.invites.store');
    Route::delete('/t/{trip:slug}/invites/{invite:token}', [TripMemberController::class, 'revokeInvite'])->name('trips.invites.revoke');

    Route::patch('/t/{trip:slug}/members/{user}', [TripMemberController::class, 'updateRole'])->name('trips.members.update');
    Route::delete('/t/{trip:slug}/members/{user}', [TripMemberController::class, 'destroy'])->name('trips.members.destroy');
    Route::post('/t/{trip:slug}/leave', [TripMemberController::class, 'leave'])->name('trips.leave');

    Route::get('/join/{invite:token}', [TripJoinController::class, 'show'])->name('trips.join');
    Route::post('/join/{invite:token}', [TripJoinController::class, 'store'])->name('trips.join.accept');
});
