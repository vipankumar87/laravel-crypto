<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->hasRole('admin')) {
            if (Auth::check()) {
                // If user is logged in but doesn't have the admin role
                return redirect()->route('dashboard')->with('error', 'You do not have permission to access admin pages.');
            } else {
                // If user is not logged in
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
