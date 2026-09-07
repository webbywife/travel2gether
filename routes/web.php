<?php

use App\Http\Controllers\TripController;
use App\Models\Trip;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Phase 1: land on the reference trip. Phase 7 turns this into a gallery.
    $featured = Trip::where('is_public', true)->orderByDesc('start_date')->first();

    abort_unless($featured, 404);

    return redirect()->route('trips.show', $featured);
})->name('home');

Route::get('/t/{trip:slug}', [TripController::class, 'show'])->name('trips.show');
