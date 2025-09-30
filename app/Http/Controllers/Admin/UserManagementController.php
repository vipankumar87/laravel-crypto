<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    public function addFunds(Request $request, User $user)
    {
        // Debug CSRF token
        \Log::info('Add Funds Debug', [
            'session_token' => csrf_token(),
            'request_token' => $request->input('_token'),
            'all_data' => $request->all(),
            'session_id' => session()->getId(),
            'tokens_match' => hash_equals(csrf_token(), $request->input('_token', ''))
        ]);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        DB::beginTransaction();

        try {
            // Ensure user has a wallet
            if (!$user->wallet) {
                Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                ]);
                $user->refresh();
            }

            $amount = $request->amount;
            $description = $request->description ?? 'Funds added by admin';

            // Add funds to wallet
            $user->wallet->addBalance($amount, $description);

            // Log admin activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties([
                    'amount' => $amount,
                    'description' => $description,
                    'admin_action' => 'add_funds'
                ])
                ->log('Admin added funds to user wallet');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully added $" . number_format($amount, 2) . " to {$user->name}'s wallet",
                'new_balance' => $user->wallet->fresh()->balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add funds: ' . $e->getMessage()
            ]);
        }
    }

    public function deductFunds(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        DB::beginTransaction();

        try {
            if (!$user->wallet || $user->wallet->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient wallet balance'
                ]);
            }

            $amount = $request->amount;
            $description = $request->description ?? 'Funds deducted by admin';

            // Deduct funds from wallet
            $user->wallet->deductBalance($amount, $description);

            // Log admin activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties([
                    'amount' => $amount,
                    'description' => $description,
                    'admin_action' => 'deduct_funds'
                ])
                ->log('Admin deducted funds from user wallet');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deducted $" . number_format($amount, 2) . " from {$user->name}'s wallet",
                'new_balance' => $user->wallet->fresh()->balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to deduct funds: ' . $e->getMessage()
            ]);
        }
    }

    public function approveTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Transaction is not pending approval'
            ]);
        }

        DB::beginTransaction();

        try {
            $transaction->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // If it's a withdrawal, deduct from wallet
            if ($transaction->type === 'withdrawal') {
                $user = $transaction->user;
                if ($user->wallet && $user->wallet->balance >= $transaction->amount) {
                    $user->wallet->update([
                        'balance' => $user->wallet->balance - $transaction->amount,
                        'withdrawn_amount' => $user->wallet->withdrawn_amount + $transaction->amount,
                    ]);
                }
            }

            // Log admin activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($transaction)
                ->withProperties([
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'admin_action' => 'approve_transaction'
                ])
                ->log('Admin approved transaction');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve transaction: ' . $e->getMessage()
            ]);
        }
    }

    public function rejectTransaction(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Transaction is not pending approval'
            ]);
        }

        try {
            $transaction->update([
                'status' => 'cancelled',
                'processed_at' => now(),
                'description' => $transaction->description . ' (Rejected by admin: ' . ($request->reason ?? 'No reason provided') . ')',
            ]);

            // Log admin activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($transaction)
                ->withProperties([
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'reason' => $request->reason ?? 'No reason provided',
                    'admin_action' => 'reject_transaction'
                ])
                ->log('Admin rejected transaction');

            return response()->json([
                'success' => true,
                'message' => 'Transaction rejected successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject transaction: ' . $e->getMessage()
            ]);
        }
    }

    public function banUser(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        if ($user->hasRole(['admin', 'system'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot ban admin users'
            ]);
        }

        $user->ban($request->reason, auth()->id());

        // Log admin activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'reason' => $request->reason,
                'admin_action' => 'ban_user'
            ])
            ->log('Admin banned user');

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} has been banned successfully"
        ]);
    }

    public function unbanUser(User $user)
    {
        $user->unban();

        // Log admin activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'admin_action' => 'unban_user'
            ])
            ->log('Admin unbanned user');

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} has been unbanned successfully"
        ]);
    }

    public function impersonateUser(User $user)
    {
        if ($user->hasRole(['admin', 'system'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot impersonate admin users'
            ]);
        }

        if ($user->isBanned()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot impersonate banned users'
            ]);
        }

        // Store original admin info before logging in as new user
        $originalAdmin = auth()->user();

        // Log admin activity before impersonation
        activity()
            ->causedBy($originalAdmin)
            ->performedOn($user)
            ->withProperties([
                'admin_action' => 'impersonate_user'
            ])
            ->log('Admin started impersonating user');

        // Store impersonation info in session
        session(['impersonating' => [
            'admin_id' => $originalAdmin->id,
            'admin_name' => $originalAdmin->name,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'started_at' => now(),
        ]]);

        // Login as the target user without regenerating session
        auth()->loginUsingId($user->id, false);

        return response()->json([
            'success' => true,
            'message' => "Now impersonating {$user->name}",
            'redirect' => route('dashboard')
        ]);
    }

    public function stopImpersonation()
    {
        try {
            $impersonation = session('impersonating');

            // Only allow stopping impersonation if there's an active impersonation session
            if (!$impersonation) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active impersonation session'
                ], 403);
            }

            // Additional security: verify the admin still exists and has proper permissions
            $admin = \App\Models\User::find($impersonation['admin_id']);
            if (!$admin || !$admin->hasRole(['admin', 'system'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid impersonation session - admin user not found or no longer has admin permissions'
                ], 403);
            }

            // Log admin activity
            activity()
                ->causedBy(\App\Models\User::find($impersonation['admin_id']))
                ->withProperties([
                    'impersonated_user' => $impersonation['user_name'],
                    'duration' => now()->diffInMinutes($impersonation['started_at']) . ' minutes',
                    'admin_action' => 'stop_impersonation'
                ])
                ->log('Admin stopped impersonating user');

            // Login back as admin
            auth()->loginUsingId($impersonation['admin_id'], false);

            // Clear impersonation session
            session()->forget('impersonating');

            return response()->json([
                'success' => true,
                'message' => 'Stopped impersonating user',
                'redirect' => route('admin.users.index')
            ]);

        } catch (\Exception $e) {
            \Log::error('Stop impersonation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error stopping impersonation: ' . $e->getMessage()
            ], 500);
        }
    }
}
