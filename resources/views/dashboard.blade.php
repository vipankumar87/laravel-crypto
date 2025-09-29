@extends('layouts.user')

@section('title', 'User Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Welcome back, {{ $user->name }}!</h3>
                </div>
                <div class="card-body">
                    <!-- Stats Cards -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ number_format($wallet->balance ?? 0, 2) }} USDT</h3>
                                    <p>Wallet Balance</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <a href="{{ route('wallet.index') }}" class="small-box-footer">
                                    Manage Wallet <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $activeInvestments ?? 0 }}</h3>
                                    <p>Active Investments</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <a href="{{ route('investments.index') }}" class="small-box-footer">
                                    View Investments <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ number_format($totalEarnings ?? 0, 2) }} USDT</h3>
                                    <p>Total Earnings</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <a href="{{ route('wallet.transactions') }}" class="small-box-footer">
                                    View Earnings <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Referral Link -->
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Your Referral Link</h3>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" readonly value="{{ $user->getReferralUrl() ?? 'Invest to activate your referral link' }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" onclick="copyToClipboard()">Copy</button>
                                </div>
                            </div>
                            <p class="text-muted">Referrals: {{ $referralCount ?? 0 }}</p>
                        </div>
                    </div>
                    
                    <!-- Recent Transactions -->
                    @if(count($recentTransactions ?? []))
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Transactions</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ ucfirst($transaction->type) }}</td>
                                        <td>{{ number_format($transaction->amount, 2) }} USDT</td>
                                        <td>
                                            <span class="badge badge-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
function copyToClipboard() {
    const referralLink = document.querySelector('input[readonly]');
    referralLink.select();
    document.execCommand('copy');
    alert('Referral link copied to clipboard!');
}
</script>
@stop
