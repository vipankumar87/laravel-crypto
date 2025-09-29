<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckBannedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // First let the request pass through to establish the session
        $response = $next($request);
        
        // Then check if the user is banned
        if (Auth::check() && Auth::user()->isBanned()) {
            // Skip check during impersonation
            if (!session('impersonating')) {
                $banReason = Auth::user()->ban_reason ?? 'No reason provided.';
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Your account has been banned. Reason: ' . $banReason);
            }
        }

        return $response;
    }
}
