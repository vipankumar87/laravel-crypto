<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ensure user has a wallet
        if (!$user->wallet) {
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);
            $user->refresh();
        }

        $wallet = $user->wallet;

        return view('wallet.index', compact('wallet'));
    }

    public function deposit(Request $request)
    {
        // Only admins can deposit funds directly
        if (!auth()->user()->hasRole(['admin', 'system'])) {
            return back()->with('error', 'Only administrators can add funds. Please contact admin for deposits.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        // This is a placeholder - in real app, integrate payment gateway
        return back()->with('success', 'Deposit request submitted. Please complete payment via your chosen method.');
    }

    public function withdraw(Request $request)
    {
        // Admins and system users cannot withdraw - they manage other users' wallets
        if (auth()->user()->hasRole(['admin', 'system'])) {
            return back()->with('error', 'Administrators cannot make withdrawal requests. Use user management to handle withdrawals.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance');
        }

        // Create pending withdrawal transaction
        Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => 'WTH_' . uniqid(),
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'net_amount' => $request->amount,
            'status' => 'pending',
            'description' => 'Withdrawal request',
        ]);

        return back()->with('success', 'Withdrawal request submitted for admin approval.');
    }

    public function transactions()
    {
        $transactions = Auth::user()->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('wallet.transactions', compact('transactions'));
    }
}
