@extends('layouts.user')

@section('title', 'Wallet Transactions')

@section('content_header')
    <h1>Wallet Transactions</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Transaction History</h3>
                    <div class="card-tools">
                        <a href="{{ route('wallet.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Wallet
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_id }}</td>
                                    <td>
                                        <span class="badge badge-{{
                                            $transaction->type === 'deposit' ? 'success' :
                                            ($transaction->type === 'withdrawal' ? 'warning' :
                                            ($transaction->type === 'investment' ? 'info' : 'primary'))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="{{ $transaction->type === 'withdrawal' || $transaction->type === 'investment' ? 'text-danger' : 'text-success' }}">
                                            {{ $transaction->type === 'withdrawal' || $transaction->type === 'investment' ? '-' : '+' }}${{ number_format($transaction->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{
                                            $transaction->status === 'completed' ? 'success' :
                                            ($transaction->status === 'pending' ? 'warning' : 'danger')
                                        }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->description }}</td>
                                    <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No transactions yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($transactions, 'hasPages') && $transactions->hasPages())
                    <div class="card-footer">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop