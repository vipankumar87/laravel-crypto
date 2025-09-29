@extends('adminlte::page')

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
                        <form method="POST" action="{{ route('wallet.withdraw') }}">
                            @csrf
                            <div class="input-group">
                                <input type="number" name="amount" class="form-control" placeholder="Withdraw Amount" step="0.01" min="1" max="{{ $wallet->balance }}" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-warning">Request Withdrawal</button>
                                </div>
                            </div>
                            <small class="text-muted">Withdrawal requests require admin approval</small>
                        </form>
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