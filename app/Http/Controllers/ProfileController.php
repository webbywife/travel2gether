<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'owned' => $user->ownedTrips()->withCount('members')->latest()->get(),
            'shared' => $user->trips()->where('created_by', '!=', $user->id)->withCount('members')->latest('trip_user.created_at')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $emailChanged = $data['email'] !== $user->email;

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
        ])->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('profile.edit')->with('status', $emailChanged
            ? 'Profile updated — check your new email address to re-verify it.'
            : 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();
        $hasPassword = filled($user->password);

        $data = $request->validate([
            'current_password' => [$hasPassword ? 'required' : 'nullable', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $user->forceFill(['password' => Hash::make($data['password'])])->save();

        return redirect()->route('profile.edit')->with('status', $hasPassword ? 'Password changed.' : 'Password set — you can now log in with it too.');
    }
}
