@extends('layouts.user')

@section('title', 'My Wallet')

@section('content_header')
    <h1>My Wallet</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <!-- Wallet Overview -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Wallet Overview</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-wallet"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Balance</span>
                                    <span class="info-box-number">${{ number_format($wallet->balance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Invested</span>
                                    <span class="info-box-number">${{ number_format($wallet->invested_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Earnings</span>
                                    <span class="info-box-number">${{ number_format($wallet->earned_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-handshake"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Referral Bonus</span>
                                    <span class="info-box-number">${{ number_format($wallet->referral_earnings, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    @can('manage wallets')
                        <form method="POST" action="{{ route('wallet.deposit') }}" class="mb-3">
                            @csrf
                            <div class="input-group">
                                <input type="number" name="amount" class="form-control" placeholder="Deposit Amount" step="0.01" min="1" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-success">Add Funds</button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Only administrators can add funds to wallets. Contact admin for deposits.
                        </div>
                    @endcan

                    @cannot('manage wallets')
                        <form method="POST" action="{{ route('wallet.withdraw') }}" class="mb-3">
                            @csrf
                            <div class="input-group">
                                <input type="number" name="amount" class="form-control" placeholder="Withdraw Amount" step="0.01" min="1" max="{{ $wallet->balance }}" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-warning">Request Withdrawal</button>
                                </div>
                            </div>
                            <small class="text-muted">Withdrawal requests require admin approval</small>
                        </form>

                        <!-- Invest from Wallet Button -->
                        <button type="button" class="btn btn-primary btn-block" onclick="investFromWallet()">
                            <i class="fas fa-chart-line"></i> Invest from Wallet
                        </button>
                        <small class="text-muted">Quick investment with popup confirmation</small>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-tools"></i> As an administrator, use the <a href="{{ route('admin.users.index') }}">User Management</a> panel to handle withdrawals and deposits for users.
                        </div>
                    @endcannot
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Wallet Status</h3>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong>
                        <span class="badge badge-{{ $wallet->status === 'active' ? 'success' : 'warning' }}">
                            {{ ucfirst($wallet->status) }}
                        </span>
                    </p>
                    <p><strong>Total Withdrawn:</strong> ${{ number_format($wallet->withdrawn_amount, 2) }}</p>
                    <p><strong>Last Updated:</strong> {{ $wallet->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function investFromWallet() {
    const walletBalance = {{ $wallet->balance }};

    if (walletBalance < 11) { // Minimum $10 + $1 fee
        alert('Insufficient balance! You need at least $11 (including $1 processing fee) to make an investment.');
        return;
    }

    const amount = prompt('Enter investment amount (minimum $10):\n\nInvestment Details:\n• Daily Return: 2%\n• Duration: 30 days\n• Total Return: 60%\n• Processing Fee: $1', '10');

    if (amount === null) {
        return; // User cancelled
    }

    const investAmount = parseFloat(amount);

    if (isNaN(investAmount) || investAmount < 10) {
        alert('Please enter a valid amount (minimum $10)');
        return;
    }

    if (investAmount + 1 > walletBalance) {
        alert('Insufficient balance! You need $' + (investAmount + 1) + ' (including $1 processing fee) but have $' + walletBalance);
        return;
    }

    const confirmMessage = `Confirm Investment:\n\nAmount: $${investAmount}\nProcessing Fee: $1\nTotal Deduction: $${investAmount + 1}\n\nDaily Return: 2%\nDuration: 30 days\nExpected Return: $${(investAmount * 0.6).toFixed(2)}\n\nProceed with investment?`;

    if (confirm(confirmMessage)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("wallet.invest") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const amountInput = document.createElement('input');
        amountInput.type = 'hidden';
        amountInput.name = 'amount';
        amountInput.value = investAmount;

        form.appendChild(csrfToken);
        form.appendChild(amountInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@stop