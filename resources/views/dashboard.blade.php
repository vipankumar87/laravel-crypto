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

                    <!-- Quick Invest Card -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-bolt"></i> Quick Invest
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Start investing now with multiple payment options</p>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <a href="{{ route('quick-invest.crypto') }}" class="btn btn-warning btn-lg btn-block">
                                                <i class="fab fa-bitcoin"></i>
                                                Invest with Crypto Wallet
                                            </a>
                                            <small class="text-muted">Add funds through cryptocurrency</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <button type="button" class="btn btn-success btn-lg btn-block" onclick="investFromWallet()">
                                                <i class="fas fa-wallet"></i>
                                                Invest from Wallet
                                            </button>
                                            <small class="text-muted">
                                                Available: {{ number_format($wallet->balance ?? 0, 2) }} USDT
                                            </small>
                                        </div>
                                    </div>
                                </div>
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

function investFromWallet() {
    const walletBalance = {{ $wallet->balance ?? 0 }};

    if (walletBalance <= 0) {
        alert('Insufficient wallet balance. Please add funds to your wallet first.');
        return;
    }

    const amount = prompt(`Enter investment amount (Available: ${walletBalance.toFixed(2)} USDT):`);

    if (amount === null) return; // User cancelled

    const investmentAmount = parseFloat(amount);

    if (isNaN(investmentAmount) || investmentAmount <= 0) {
        alert('Please enter a valid amount.');
        return;
    }

    if (investmentAmount > walletBalance) {
        alert('Insufficient wallet balance.');
        return;
    }

    // Show confirmation popup
    const confirmed = confirm(`Confirm Investment:\n\nAmount: ${investmentAmount.toFixed(2)} USDT\nSource: Wallet Balance\n\nProceed with this investment?`);

    if (confirmed) {
        // Redirect to investment plans with amount
        window.location.href = `{{ route('investments.plans') }}?amount=${investmentAmount}&source=wallet`;
    }
}
</script>
@stop
