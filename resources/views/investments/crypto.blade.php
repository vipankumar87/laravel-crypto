@extends('layouts.user')

@section('title', 'Crypto Investment')

@section('content_header')
    <h1>Crypto Investment</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Main Investment Content Area -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-coins"></i>
                        Continue with Crypto Investment
                    </h3>
                </div>
                <div class="card-body">
                    <!-- User Wallet Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-wallet"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Wallet Balance</span>
                                    <span class="info-box-number">${{ number_format($wallet->balance ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Invested</span>
                                    <span class="info-box-number">${{ number_format($wallet->invested_amount ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Investment Form -->
                    <form action="{{ route('investments.invest') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Investment Amount ($)</label>
                                    <input type="number" class="form-control" id="amount" name="amount"
                                           min="10" step="0.01" placeholder="Minimum $10" required>
                                    <small class="form-text text-muted">Minimum investment: $10 | Processing fee: $1</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="payment_currency">Payment Currency</label>
                                    <select class="form-control" id="payment_currency" name="payment_currency" required>
                                        <option value="USDT">USDT (BEP20)</option>
                                        <option value="BTC">Bitcoin</option>
                                        <option value="ETH">Ethereum</option>
                                        <option value="SOL">Solana</option>
                                        <option value="DOGE">Dogecoin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="investment_currency">Investment Currency</label>
                                    <select class="form-control" id="investment_currency" name="investment_currency" required>
                                        <option value="USDT">USDT (BEP20)</option>
                                        <option value="BTC">Bitcoin</option>
                                        <option value="ETH">Ethereum</option>
                                        <option value="SOL">Solana</option>
                                        <option value="DOGE">Dogecoin</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Investment Details -->
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle"></i> Investment Details</h5>
                                    <ul class="mb-0">
                                        <li><strong>Daily Return:</strong> 2%</li>
                                        <li><strong>Duration:</strong> 30 days</li>
                                        <li><strong>Total Return:</strong> 60%</li>
                                        <li><strong>Processing Fee:</strong> $1 USDT</li>
                                        <li><strong>Referral Bonus:</strong> 5% (if referred)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-coins"></i> Create Investment
                                </button>
                                <a href="{{ route('investments.index') }}" class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-arrow-left"></i> Back to Investments
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop