<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuickInvestController extends Controller
{
    public function crypto()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        return view('quick-invest.crypto', compact('user', 'wallet'));
    }

    public function processCryptoInvestment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $amount = $request->amount;
        $fee = 1; // 1 USDT fee for crypto investments
        $totalAmount = $amount + $fee;

        return response()->json([
            'success' => true,
            'data' => [
                'amount' => $amount,
                'fee' => $fee,
                'total' => $totalAmount,
                'message' => 'Crypto investment confirmation'
            ]
        ]);
    }
}