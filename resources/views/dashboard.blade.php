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

                    <!-- Earnings Breakdown Section -->
                    @if($analytics && isset($analytics['earnings_breakdown']))
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-pie"></i> Earnings Breakdown
                                    </h3>
                                    <div class="card-tools">
                                        <span class="badge badge-success">Direct vs Referral</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($analytics['earnings_breakdown']['all_time']['total_earnings'] > 0)
                                    <!-- Summary Cards -->
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="info-box bg-gradient-info" data-earnings-type="direct">
                                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Direct Earnings</span>
                                                    <span class="info-box-number">{{ number_format($analytics['earnings_breakdown']['all_time']['direct_earnings'], 2) }} USDT</span>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-info" style="width: {{ $analytics['earnings_breakdown']['all_time']['direct_percentage'] }}%"></div>
                                                    </div>
                                                    <span class="progress-description">
                                                        {{ number_format($analytics['earnings_breakdown']['all_time']['direct_percentage'], 1) }}% of total
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-gradient-warning" data-earnings-type="referral">
                                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Referral Earnings</span>
                                                    <span class="info-box-number">{{ number_format($analytics['earnings_breakdown']['all_time']['referral_earnings'], 2) }} USDT</span>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-warning" style="width: {{ $analytics['earnings_breakdown']['all_time']['referral_percentage'] }}%"></div>
                                                    </div>
                                                    <span class="progress-description">
                                                        {{ number_format($analytics['earnings_breakdown']['all_time']['referral_percentage'], 1) }}% of total
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-gradient-success" data-earnings-type="total">
                                                <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Earnings</span>
                                                    <span class="info-box-number">{{ number_format($analytics['earnings_breakdown']['all_time']['total_earnings'], 2) }} USDT</span>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                                    </div>
                                                    <span class="progress-description">
                                                        All time earnings
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Period Breakdown Table -->
                                    <div class="row">
                                        <div class="col-12">
                                            <h5 class="mb-3"><i class="fas fa-table"></i> Earnings by Period</h5>
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Period</th>
                                                        <th>Direct Earnings</th>
                                                        <th>Referral Earnings</th>
                                                        <th>Total</th>
                                                        <th>Direct %</th>
                                                        <th>Referral %</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Today</strong></td>
                                                        <td>{{ number_format($analytics['earnings_breakdown']['today']['direct_earnings'], 2) }} USDT</td>
                                                        <td>{{ number_format($analytics['earnings_breakdown']['today']['referral_earnings'], 2) }} USDT</td>
                                                        <td><strong>{{ number_format($analytics['earnings_breakdown']['today']['total_earnings'], 2) }} USDT</strong></td>
                                                        <td>
                                                            @if($analytics['earnings_breakdown']['today']['total_earnings'] > 0)
                                                                <span class="badge badge-info">{{ number_format(($analytics['earnings_breakdown']['today']['direct_earnings'] / $analytics['earnings_breakdown']['today']['total_earnings']) * 100, 1) }}%</span>
                                                            @else
                                                                <span class="badge badge-secondary">0%</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($analytics['earnings_breakdown']['today']['total_earnings'] > 0)
                                                                <span class="badge badge-warning">{{ number_format(($analytics['earnings_breakdown']['today']['referral_earnings'] / $analytics['earnings_breakdown']['today']['total_earnings']) * 100, 1) }}%</span>
                                                            @else
                                                                <span class="badge badge-secondary">0%</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>This Week</strong></td>
                                                        <td>{{ number_format($analytics['earnings_breakdown']['this_week']['direct_earnings'], 2) }} USDT</td>
                                                        <td>{{ number_format($analytics['earnings_breakdown']['this_week']['referral_earnings'], 2) }} USDT</td>
                                                        <td><strong>{{ number_format($analytics['earnings_breakdown']['this_week']['total_earnings'], 2) }} USDT</strong></td>
                                                        <td>
                                                            @if($analytics['earnings_breakdown']['this_week']['total_earnings'] > 0)
                                                                <span class="badge badge-info">{{ number_format(($analytics['earnings_breakdown']['this_week']['direct_earnings'] / $analytics['earnings_breakdown']['this_week']['total_earnings']) * 100, 1) }}%</span>
                                                            @else
                                                                <span class="badge badge-secondary">0%</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($analytics['earnings_breakdown']['this_week']['total_earnings'] > 0)
                                                                <span class="badge badge-warning">{{ number_format(($analytics['earnings_breakdown']['this_week']['referral_earnings'] / $analytics['earnings_breakdown']['this_week']['total_earnings']) * 100, 1) }}%</span>
                                                            @else
                                                                <span class="badge badge-secondary">0%</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>This Month</strong></td>
                                                        <td>{{ number_format($analytics['earnings_breakdown']['this_month']['direct_earnings'], 2) }} USDT</td>
                                                        <td>{{ number_format($analytics['earnings_breakdown']['this_month']['referral_earnings'], 2) }} USDT</td>
                                                        <td><strong>{{ number_format($analytics['earnings_breakdown']['this_month']['total_earnings'], 2) }} USDT</strong></td>
                                                        <td>
                                                            @if($analytics['earnings_breakdown']['this_month']['total_earnings'] > 0)
                                                                <span class="badge badge-info">{{ number_format(($analytics['earnings_breakdown']['this_month']['direct_earnings'] / $analytics['earnings_breakdown']['this_month']['total_earnings']) * 100, 1) }}%</span>
                                                            @else
                                                                <span class="badge badge-secondary">0%</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($analytics['earnings_breakdown']['this_month']['total_earnings'] > 0)
                                                                <span class="badge badge-warning">{{ number_format(($analytics['earnings_breakdown']['this_month']['referral_earnings'] / $analytics['earnings_breakdown']['this_month']['total_earnings']) * 100, 1) }}%</span>
                                                            @else
                                                                <span class="badge badge-secondary">0%</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr class="table-primary">
                                                        <td><strong>All Time</strong></td>
                                                        <td><strong>{{ number_format($analytics['earnings_breakdown']['all_time']['direct_earnings'], 2) }} USDT</strong></td>
                                                        <td><strong>{{ number_format($analytics['earnings_breakdown']['all_time']['referral_earnings'], 2) }} USDT</strong></td>
                                                        <td><strong>{{ number_format($analytics['earnings_breakdown']['all_time']['total_earnings'], 2) }} USDT</strong></td>
                                                        <td><strong><span class="badge badge-info">{{ number_format($analytics['earnings_breakdown']['all_time']['direct_percentage'], 1) }}%</span></strong></td>
                                                        <td><strong><span class="badge badge-warning">{{ number_format($analytics['earnings_breakdown']['all_time']['referral_percentage'], 1) }}%</span></strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Referral Level Breakdown -->
                                    @if(!empty($analytics['earnings_breakdown']['referral_by_level']))
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <h5 class="mb-3"><i class="fas fa-sitemap"></i> Referral Earnings by Level</h5>
                                            <div class="row">
                                                @foreach($analytics['earnings_breakdown']['referral_by_level'] as $level => $data)
                                                <div class="col-md-3 mb-3">
                                                    <div class="small-box bg-light">
                                                        <div class="inner">
                                                            <h4>Level {{ $data['level'] }}</h4>
                                                            <p>{{ number_format($data['total_earnings'], 2) }} USDT</p>
                                                            <small class="text-muted">{{ $data['referral_count'] }} referrals</small>
                                                        </div>
                                                        <div class="icon">
                                                            <i class="fas fa-layer-group"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @else
                                    <!-- No Earnings State -->
                                    <div class="text-center py-5">
                                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">No Earnings Yet</h4>
                                        <p class="text-muted">Start investing to see your earnings breakdown here.</p>
                                        <a href="{{ route('investments.plans') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Make Investment
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Investment Analytics Section -->
                    @if($analytics)
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-bar"></i> Investment Analytics
                                    </h3>
                                    <div class="card-tools">
                                        <span class="badge badge-info" id="last-updated">Live</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Wallet Overview -->
                                    <div class="row mb-4">
                                        <div class="col-md-3 col-sm-6">
                                            <div class="info-box bg-gradient-primary">
                                                <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Wallet Balance</span>
                                                    <span class="info-box-number" id="wallet-balance">{{ number_format($analytics['wallet']['balance'], 2) }} USDT</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="info-box bg-gradient-success">
                                                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Invested</span>
                                                    <span class="info-box-number" id="total-invested">{{ number_format($analytics['wallet']['invested_amount'], 8) }} DOGE</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="info-box bg-gradient-warning">
                                                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Earned</span>
                                                    <span class="info-box-number" id="total-earned">{{ number_format($analytics['wallet']['earned_amount'], 8) }} DOGE</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="info-box bg-gradient-info">
                                                <span class="info-box-icon"><i class="fas fa-piggy-bank"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Value</span>
                                                    <span class="info-box-number" id="total-value">{{ number_format($analytics['wallet']['total_value'], 8) }} DOGE</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Interest Generation Stats -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h5 class="mb-3"><i class="fas fa-percentage"></i> Interest Generation</h5>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="small-box bg-light">
                                                <div class="inner">
                                                    <h4 id="daily-interest">{{ number_format($analytics['investments']['daily_interest'], 8) }} DOGE</h4>
                                                    <p>Daily Interest</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-calendar-day"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="small-box bg-light">
                                                <div class="inner">
                                                    <h4 id="weekly-interest">{{ number_format($analytics['investments']['daily_interest'] * 7, 8) }} DOGE</h4>
                                                    <p>Weekly Interest</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-calendar-week"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="small-box bg-light">
                                                <div class="inner">
                                                    <h4 id="monthly-interest">{{ number_format($analytics['investments']['monthly_interest'], 8) }} DOGE</h4>
                                                    <p>Monthly Interest</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="small-box bg-light">
                                                <div class="inner">
                                                    <h4 id="yearly-interest">{{ number_format($analytics['investments']['yearly_interest'], 8) }} DOGE</h4>
                                                    <p>Yearly Interest</p>
                                                </div>
                                                <div class="icon">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Earnings Breakdown -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Weekly Earnings</h3>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="weeklyChart" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Monthly Earnings</h3>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="monthlyChart" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Interest Breakdown Table -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Interest Breakdown</h3>
                                                </div>
                                                <div class="card-body p-0">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Period</th>
                                                                <th>Earnings</th>
                                                                <th>Growth</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><strong>Today</strong></td>
                                                                <td id="interest-today">{{ number_format($analytics['interest']['today'], 2) }} USDT</td>
                                                                <td><span class="badge badge-success">Active</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>This Week</strong></td>
                                                                <td id="interest-week">{{ number_format($analytics['interest']['this_week'], 2) }} USDT</td>
                                                                <td><span class="badge badge-info">{{ number_format($analytics['weekly']['total'], 2) }} USDT</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>This Month</strong></td>
                                                                <td id="interest-month">{{ number_format($analytics['interest']['this_month'], 2) }} USDT</td>
                                                                <td><span class="badge badge-primary">{{ number_format($analytics['monthly']['total'], 2) }} USDT</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>All Time</strong></td>
                                                                <td id="interest-alltime">{{ number_format($analytics['interest']['all_time'], 2) }} USDT</td>
                                                                <td><span class="badge badge-warning">Total</span></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Real-time Stats -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Growth Rate</span>
                                                    <span class="info-box-number" id="growth-rate">{{ number_format($analytics['realtime']['growth_percentage'], 2) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Days Active</span>
                                                    <span class="info-box-number" id="days-active">{{ $analytics['realtime']['days_active'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Days Remaining</span>
                                                    <span class="info-box-number" id="days-remaining">{{ $analytics['realtime']['days_remaining'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

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
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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

    if (amount === null) return;

    const investmentAmount = parseFloat(amount);

    if (isNaN(investmentAmount) || investmentAmount <= 0) {
        alert('Please enter a valid amount.');
        return;
    }

    if (investmentAmount > walletBalance) {
        alert('Insufficient wallet balance.');
        return;
    }

    const confirmed = confirm(`Confirm Investment:\n\nAmount: ${investmentAmount.toFixed(2)} USDT\nSource: Wallet Balance\n\nProceed with this investment?`);

    if (confirmed) {
        window.location.href = `{{ route('investments.plans') }}?amount=${investmentAmount}&source=wallet`;
    }
}

@if($analytics)
// Analytics Charts and Real-time Updates
let weeklyChart, monthlyChart;
let updateInterval;

// Initialize charts
function initCharts() {
    const weeklyData = @json($analytics['weekly']['data']);
    const monthlyData = @json($analytics['monthly']['data']);

    // Weekly Chart
    const weeklyCtx = document.getElementById('weeklyChart');
    if (weeklyCtx) {
        weeklyChart = new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: weeklyData.map(d => d.day),
                datasets: [{
                    label: 'Daily Earnings (USDT)',
                    data: weeklyData.map(d => d.earnings),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Earnings: ' + context.parsed.y.toFixed(2) + ' USDT';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(2) + ' USDT';
                            }
                        }
                    }
                }
            }
        });
    }

    // Monthly Chart
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.week_label),
                datasets: [{
                    label: 'Weekly Earnings (USDT)',
                    data: monthlyData.map(d => d.earnings),
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Earnings: ' + context.parsed.y.toFixed(2) + ' USDT';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(2) + ' USDT';
                            }
                        }
                    }
                }
            }
        });
    }
}

// Update analytics data in real-time
function updateAnalytics() {
    fetch('{{ route("analytics.get") }}', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const analytics = data.data;
            
            // Update wallet stats
            if (analytics.wallet) {
                updateElement('wallet-balance', analytics.wallet.balance.toFixed(2) + ' USDT');
                updateElement('total-invested', analytics.wallet.invested_amount.toFixed(2) + ' USDT');
                updateElement('total-earned', analytics.wallet.earned_amount.toFixed(2) + ' USDT');
                updateElement('total-value', analytics.wallet.total_value.toFixed(2) + ' USDT');
            }
            
            // Update interest stats
            if (analytics.investments) {
                updateElement('daily-interest', analytics.investments.daily_interest.toFixed(2) + ' USDT');
                updateElement('weekly-interest', (analytics.investments.daily_interest * 7).toFixed(2) + ' USDT');
                updateElement('monthly-interest', analytics.investments.monthly_interest.toFixed(2) + ' USDT');
                updateElement('yearly-interest', analytics.investments.yearly_interest.toFixed(2) + ' USDT');
            }
            
            // Update interest breakdown
            if (analytics.interest) {
                updateElement('interest-today', analytics.interest.today.toFixed(2) + ' USDT');
                updateElement('interest-week', analytics.interest.this_week.toFixed(2) + ' USDT');
                updateElement('interest-month', analytics.interest.this_month.toFixed(2) + ' USDT');
                updateElement('interest-alltime', analytics.interest.all_time.toFixed(2) + ' USDT');
            }
            
            // Update real-time stats
            if (analytics.realtime) {
                updateElement('growth-rate', analytics.realtime.growth_percentage.toFixed(2) + '%');
                updateElement('days-active', analytics.realtime.days_active);
                updateElement('days-remaining', analytics.realtime.days_remaining);
            }
            
            // Update earnings breakdown if available
            if (analytics.earnings_breakdown) {
                // Update summary cards
                const breakdown = analytics.earnings_breakdown;
                
                // Update direct earnings card
                const directCard = document.querySelector('[data-earnings-type="direct"]');
                if (directCard) {
                    directCard.querySelector('.info-box-number').textContent = breakdown.all_time.direct_earnings.toFixed(2) + ' USDT';
                    const progressBar = directCard.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.style.width = breakdown.all_time.direct_percentage + '%';
                    }
                    const progressDesc = directCard.querySelector('.progress-description');
                    if (progressDesc) {
                        progressDesc.textContent = breakdown.all_time.direct_percentage.toFixed(1) + '% of total';
                    }
                }
                
                // Update referral earnings card
                const referralCard = document.querySelector('[data-earnings-type="referral"]');
                if (referralCard) {
                    referralCard.querySelector('.info-box-number').textContent = breakdown.all_time.referral_earnings.toFixed(2) + ' USDT';
                    const progressBar = referralCard.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.style.width = breakdown.all_time.referral_percentage + '%';
                    }
                    const progressDesc = referralCard.querySelector('.progress-description');
                    if (progressDesc) {
                        progressDesc.textContent = breakdown.all_time.referral_percentage.toFixed(1) + '% of total';
                    }
                }
                
                // Update total earnings card
                const totalCard = document.querySelector('[data-earnings-type="total"]');
                if (totalCard) {
                    totalCard.querySelector('.info-box-number').textContent = breakdown.all_time.total_earnings.toFixed(2) + ' USDT';
                }
            }
            
            // Update charts
            if (analytics.weekly && weeklyChart) {
                weeklyChart.data.datasets[0].data = analytics.weekly.data.map(d => d.earnings);
                weeklyChart.update();
            }
            
            if (analytics.monthly && monthlyChart) {
                monthlyChart.data.datasets[0].data = analytics.monthly.data.map(d => d.earnings);
                monthlyChart.update();
            }
            
            // Update last updated badge
            const now = new Date();
            document.getElementById('last-updated').textContent = 'Updated ' + now.toLocaleTimeString();
        }
    })
    .catch(error => {
        console.error('Error updating analytics:', error);
    });
}

// Helper function to update element with animation
function updateElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        const oldValue = element.textContent;
        if (oldValue !== value) {
            element.style.transition = 'all 0.3s ease';
            element.style.color = '#28a745';
            element.textContent = value;
            setTimeout(() => {
                element.style.color = '';
            }, 1000);
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    
    // Update analytics every 30 seconds
    updateInterval = setInterval(updateAnalytics, 30000);
    
    // Update immediately after 5 seconds
    setTimeout(updateAnalytics, 5000);
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
@endif
</script>
@stop
