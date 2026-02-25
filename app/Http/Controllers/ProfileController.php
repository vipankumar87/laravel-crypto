<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\WalletUpdateRequest;
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
            $pendingWalletRequest = WalletUpdateRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            return view('profile.user-edit', [
                'user' => $user,
                'pendingWalletRequest' => $pendingWalletRequest,
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
     * - First time: saves directly.
     * - Subsequent changes: creates a pending update request for admin approval.
     */
    public function updateWallet(Request $request): RedirectResponse
    {
        $request->validate([
            'bep_wallet_address' => ['required', 'string', 'size:42', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ], [
            'bep_wallet_address.regex' => 'The wallet address must be a valid BSC address starting with 0x.',
            'bep_wallet_address.size' => 'The wallet address must be exactly 42 characters.',
        ]);

        $user = $request->user();
        $newAddress = $request->bep_wallet_address;

        // First time adding — save directly
        if (!$user->bep_wallet_address) {
            $user->update(['bep_wallet_address' => $newAddress]);

            return Redirect::route('profile.edit')->with('status', 'wallet-saved');
        }

        // Same address — nothing to do
        if ($user->bep_wallet_address === $newAddress) {
            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        }

        // Already has a pending request
        $hasPending = WalletUpdateRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return Redirect::route('profile.edit')
                ->with('error', 'You already have a pending wallet update request. Please wait for admin approval.');
        }

        // Create update request for admin approval
        WalletUpdateRequest::create([
            'user_id'           => $user->id,
            'new_wallet_address' => $newAddress,
            'status'            => 'pending',
        ]);

        return Redirect::route('profile.edit')->with('status', 'wallet-request-submitted');
    }

    /**
     * Cancel the user's pending wallet update request.
     */
    public function cancelWalletRequest(Request $request): RedirectResponse
    {
        WalletUpdateRequest::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->delete();

        return Redirect::route('profile.edit')->with('status', 'wallet-request-cancelled');
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
