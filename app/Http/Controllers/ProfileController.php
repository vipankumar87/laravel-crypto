<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // Use different views based on user role
        if ($user->hasRole('user')) {
            return view('profile.user-edit', [
                'user' => $user,
            ]);
        }
        
        // Default view for admin or other roles
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's BEP-20 wallet address.
     */
    public function updateWallet(Request $request): RedirectResponse
    {
        $request->validate([
            'bep_wallet_address' => ['nullable', 'string', 'size:42', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ], [
            'bep_wallet_address.regex' => 'The wallet address must be a valid BSC address starting with 0x.',
            'bep_wallet_address.size' => 'The wallet address must be exactly 42 characters.',
        ]);

        $request->user()->update([
            'bep_wallet_address' => $request->bep_wallet_address,
        ]);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
