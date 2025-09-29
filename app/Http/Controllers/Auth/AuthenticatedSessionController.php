<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            // Log authentication attempt
            \Log::info('Authentication attempt in controller', [
                'username' => $request->username,
                'session_id' => session()->getId(),
            ]);
            
            $request->authenticate();

            // Log successful authentication
            \Log::info('Authentication successful in controller', [
                'user_id' => Auth::id(),
                'username' => Auth::user()->username,
                'session_id' => session()->getId(),
            ]);

            $request->session()->regenerate();

            // Log session regeneration
            \Log::info('Session regenerated in controller', [
                'new_session_id' => session()->getId(),
            ]);

            // Store a test value in session
            session(['login_test' => 'Login was successful at ' . now()]);

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Exception $e) {
            \Log::error('Authentication error in controller', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors([
                'username' => 'An error occurred during login. Please try again.',
            ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
