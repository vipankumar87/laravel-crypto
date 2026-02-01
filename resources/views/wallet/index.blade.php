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

    <!-- Live Conversion Rate Banner -->
    @if($dogeRate > 0)
    <div class="callout callout-warning">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-0"><i class="fas fa-exchange-alt mr-2"></i>DOGE / USDT Live Rate</h5>
                <p class="mb-0 mt-1">
                    <strong>1 DOGE = ${{ number_format($dogeRate, 6) }} USDT</strong>
                    &nbsp;|&nbsp;
                    <strong>1 USDT = {{ number_format(1 / $dogeRate, 4) }} DOGE</strong>
                </p>
            </div>
            <div class="text-right">
                <span class="text-muted"><i class="fas fa-clock"></i> Updates every 5 min</span>
            </div>
        </div>
    </div>
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
                                    <span class="info-box-number">DOGE {{ number_format($wallet->balance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Invested</span>
                                    <span class="info-box-number">DOGE {{ number_format($wallet->invested_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Earnings</span>
                                    <span class="info-box-number">DOGE {{ number_format($wallet->earned_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-handshake"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Referral Bonus</span>
                                    <span class="info-box-number">DOGE {{ number_format($wallet->referral_earnings, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- DOGE Holding & USDT Value -->
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-dog"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">DOGE Holding</span>
                                    <span class="info-box-number">{{ number_format($wallet->doge_balance, 8) }} DOGE</span>
                                    <span class="info-box-text">Withdrawn: {{ number_format($wallet->doge_withdrawn, 8) }} DOGE</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">DOGE Value in USDT</span>
                                    <span class="info-box-number">${{ number_format($dogeBalanceInUsdt, 2) }}</span>
                                    <span class="info-box-text">Withdrawable as USDT</span>
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
                    <h3 class="card-title">Withdraw</h3>
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
                        <div class="alert alert-info mb-2 py-2">
                            <small><i class="fas fa-info-circle"></i> All withdrawals are paid out in <strong>USDT</strong> to your BEP-20 wallet address.</small>
                        </div>
                    @endcan

                    @cannot('manage wallets')
                        @if($pendingWithdrawals > 0)
                            <div class="alert alert-warning py-2">
                                <i class="fas fa-clock"></i> You have <strong>{{ $pendingWithdrawals }}</strong> pending withdrawal{{ $pendingWithdrawals > 1 ? 's' : '' }}.
                            </div>
                        @endif

                        @if(!$canWithdraw)
                            <div class="alert alert-danger py-2">
                                <i class="fas fa-lock"></i> Withdrawals locked. Need <strong>${{ number_format($withdrawalSettings['min_usdt_threshold'], 2) }}</strong> in earnings.
                                <br>Current: <strong>${{ number_format($wallet->earned_amount + $wallet->referral_earnings, 2) }}</strong>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('wallet.withdraw') }}">
                            @csrf
                            <div class="form-group">
                                <label>Withdraw From</label>
                                <select name="currency" id="withdrawCurrency" class="form-control">
                                    <option value="USDT" {{ !$canWithdraw ? 'disabled' : '' }}>USDT Balance ({{ number_format($wallet->balance, 2) }} DOGE)</option>
                                    <option value="DOGE" {{ $dogeBalanceInUsdt < 1 ? 'disabled' : '' }}>DOGE Holding (~${{ number_format($dogeBalanceInUsdt, 2) }} USDT)</option>
                                </select>
                            </div>

                            <div id="dogeConversionInfo" style="display: none;">
                                <div class="alert alert-info py-2 mb-2">
                                    <small>
                                        <i class="fas fa-calculator mr-1"></i>
                                        Enter the USDT amount you want. Equivalent DOGE will be deducted.<br>
                                        <strong>1 DOGE = ${{ number_format($dogeRate, 6) }}</strong><br>
                                        <span id="dogeCalc"></span>
                                    </small>
                                </div>
                            </div>

                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="currencySymbol">$</span>
                                </div>
                                <input type="number"
                                       name="amount"
                                       id="withdrawAmount"
                                       class="form-control"
                                       placeholder="Amount in USDT"
                                       step="0.01"
                                       min="1"
                                       max="{{ $wallet->balance }}"
                                       required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-warning">Withdraw</button>
                                </div>
                            </div>
                            <small class="text-muted">
                                Max: ${{ number_format($withdrawalSettings['max_withdrawal_amount'], 2) }} per withdrawal
                            </small>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-tools"></i> Use <a href="{{ route('admin.users.index') }}">User Management</a> to handle withdrawals.
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
                    <p><strong>Total USDT Withdrawn:</strong> ${{ number_format($wallet->withdrawn_amount, 2) }}</p>
                    <p><strong>Total DOGE Withdrawn:</strong> {{ number_format($wallet->doge_withdrawn, 8) }} DOGE</p>
                    <p><strong>Last Updated:</strong> {{ $wallet->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const currencySelect = document.getElementById('withdrawCurrency');
    const amountInput = document.getElementById('withdrawAmount');
    const dogeInfo = document.getElementById('dogeConversionInfo');
    const dogeCalc = document.getElementById('dogeCalc');
    const dogeRate = {{ $dogeRate }};
    const dogeBalance = {{ $wallet->doge_balance }};
    const dogeBalanceUsdt = {{ $dogeBalanceInUsdt }};
    const usdtBalance = {{ $wallet->balance }};

    function updateForm() {
        if (!currencySelect || !amountInput) return;

        if (currencySelect.value === 'DOGE') {
            amountInput.max = dogeBalanceUsdt;
            if (dogeInfo) dogeInfo.style.display = 'block';
            updateDogeCalc();
        } else {
            amountInput.max = usdtBalance;
            if (dogeInfo) dogeInfo.style.display = 'none';
        }
        amountInput.value = '';
    }

    function updateDogeCalc() {
        if (!dogeCalc) return;
        const usdtAmount = parseFloat(amountInput.value) || 0;
        if (usdtAmount > 0 && dogeRate > 0) {
            const dogeNeeded = (usdtAmount / dogeRate).toFixed(8);
            dogeCalc.innerHTML = '<strong>$' + usdtAmount.toFixed(2) + ' USDT = ' + dogeNeeded + ' DOGE</strong>';
        } else {
            dogeCalc.innerHTML = '';
        }
    }

    if (currencySelect) {
        currencySelect.addEventListener('change', updateForm);
    }
    if (amountInput) {
        amountInput.addEventListener('input', updateDogeCalc);
    }
});
</script>
@stop
