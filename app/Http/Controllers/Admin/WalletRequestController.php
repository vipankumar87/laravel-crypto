<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletRequestController extends Controller
{
    public function index()
    {
        $requests = WalletUpdateRequest::with(['user', 'reviewer'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $pendingCount = WalletUpdateRequest::where('status', 'pending')->count();

        return view('admin.wallet-requests.index', compact('requests', 'pendingCount'));
    }

    public function approve(WalletUpdateRequest $walletRequest)
    {
        if (!$walletRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        try {
            $walletRequest->user->update([
                'bep_wallet_address' => $walletRequest->new_wallet_address,
            ]);

            $walletRequest->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($walletRequest->user)
                ->withProperties([
                    'new_wallet_address' => $walletRequest->new_wallet_address,
                    'admin_action'       => 'approve_wallet_request',
                ])
                ->log('Admin approved wallet update request');

            DB::commit();

            return back()->with('success', "Wallet address updated for {$walletRequest->user->name}.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, WalletUpdateRequest $walletRequest)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        if (!$walletRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $walletRequest->update([
            'status'      => 'rejected',
            'admin_note'  => $request->admin_note,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($walletRequest->user)
            ->withProperties([
                'new_wallet_address' => $walletRequest->new_wallet_address,
                'reason'             => $request->admin_note,
                'admin_action'       => 'reject_wallet_request',
            ])
            ->log('Admin rejected wallet update request');

        return back()->with('success', "Wallet update request for {$walletRequest->user->name} has been rejected.");
    }
}
