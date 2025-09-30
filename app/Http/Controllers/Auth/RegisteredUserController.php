<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class, 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'bep_wallet_address' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Handle referral code
        $referredBy = null;
        if ($request->has('referral_code') && $request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
            if ($referrer) {
                $referredBy = $referrer->id;
                \Log::info('Registration with referral', [
                    'referral_code' => $request->referral_code,
                    'referrer_id' => $referrer->id,
                    'referrer_name' => $referrer->name
                ]);
            } else {
                \Log::warning('Invalid referral code used', ['referral_code' => $request->referral_code]);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'country' => $request->country,
            'bep_wallet_address' => $request->bep_wallet_address,
            'password' => Hash::make($request->password),
            'encrypted_password' => encrypt($request->password),
            'referral_code' => strtoupper(substr(uniqid(), -8)), // Generate unique referral code
            'referred_by' => $referredBy,
        ]);

        // Assign user role
        $user->assignRole('user');

        // Create wallet for the user
        $user->wallet()->create([
            'balance' => 0,
            'total_invested' => 0,
            'total_earned' => 0,
            'withdrawn_amount' => 0,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
