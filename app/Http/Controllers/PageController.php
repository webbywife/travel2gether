<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', [
            'sampleTrip' => Trip::where('is_public', true)->orderByDesc('start_date')->first(),
        ]);
    }

    public function dashboard(): View
    {
        return view('dashboard', [
            'sampleTrip' => Trip::where('is_public', true)->orderByDesc('start_date')->first(),
        ]);
    }
}
