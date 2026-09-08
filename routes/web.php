<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/dashboard', [PageController::class, 'dashboard'])->middleware('auth')->name('dashboard');

// Public, shareable trip URL (Phase 7). Seoul 2026 is the seeded sample.
Route::get('/t/{trip:slug}', [TripController::class, 'show'])->name('trips.show');
