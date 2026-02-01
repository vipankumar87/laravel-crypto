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
                                <th>Currency</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Tx Hash</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                @php
                                    $isDoge = ($transaction->currency ?? 'USDT') === 'DOGE';
                                    $isDebit = in_array($transaction->type, ['withdrawal', 'investment']);
                                @endphp
                                <tr>
                                    <td><code>{{ $transaction->transaction_id }}</code></td>
                                    <td>
                                        <span class="badge badge-{{
                                            $transaction->type === 'deposit' ? 'success' :
                                            ($transaction->type === 'withdrawal' ? 'warning' :
                                            ($transaction->type === 'investment' ? 'info' :
                                            ($transaction->type === 'doge_bonus' ? 'dark' : 'primary')))
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $isDoge ? 'warning' : 'light' }}">
                                            {{ $transaction->currency ?? 'USDT' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="{{ $isDebit ? 'text-danger' : 'text-success' }}">
                                            {{ $isDebit ? '-' : '+' }}{{ $isDoge ? '' : '$' }}{{ number_format($transaction->amount, $isDoge ? 8 : 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{
                                            $transaction->status === 'completed' ? 'success' :
                                            ($transaction->status === 'pending' ? 'warning' :
                                            ($transaction->status === 'cancelled' ? 'secondary' : 'danger'))
                                        }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($transaction->tx_hash)
                                            <a href="https://bscscan.com/tx/{{ $transaction->tx_hash }}" target="_blank" title="{{ $transaction->tx_hash }}">
                                                <code>{{ substr($transaction->tx_hash, 0, 10) }}...</code>
                                                <i class="fas fa-external-link-alt fa-xs"></i>
                                            </a>
                                        @elseif($transaction->type === 'withdrawal' && $transaction->status === 'completed')
                                            <span class="text-info"><i class="fas fa-spinner fa-spin"></i> Processing</span>
                                        @elseif($transaction->type === 'withdrawal' && $transaction->status === 'pending')
                                            <span class="text-muted">Awaiting approval</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No transactions yet</td>
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