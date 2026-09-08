<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function privacy(): View
    {
        return view('legal.privacy', [
            'contact' => config('app.privacy_contact'),
        ]);
    }
}
