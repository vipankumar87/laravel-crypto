<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\WalletService;
use Symfony\Component\HttpFoundation\Response;

class EnsureCryptoWallet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only process if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is missing any crypto wallet fields
            if (empty($user->crypto_address) || empty($user->private_key) || empty($user->public_key)) {
                try {
                    $walletService = new WalletService();
                    $wallet = $walletService->generateUserWallet($user->id);

                    $user->update([
                        'crypto_address' => $wallet['address'],
                        'private_key' => $walletService->encryptPrivateKey($wallet['private_key']),
                        'public_key' => $wallet['public_key'],
                    ]);

                    // Refresh the user instance
                    $user->refresh();

                    // Update the Auth user instance
                    Auth::setUser($user);

                    \Log::info('Auto-generated crypto wallet for user ' . $user->id . ': ' . $wallet['address']);

                } catch (\Exception $e) {
                    \Log::error('Failed to auto-generate crypto wallet for user ' . $user->id . ': ' . $e->getMessage());
                    // Don't block the request, just log the error
                }
            }
        }

        return $next($request);
    }
}
