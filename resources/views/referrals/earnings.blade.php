@extends('adminlte::page')

@section('title', 'Referral Earnings')

@section('content_header')
    <h1>Referral Earnings</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Stats Row -->
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Referral Earnings</span>
                    <span class="info-box-number">${{ number_format($totalEarnings, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Transactions</span>
                    <span class="info-box-number">{{ $earnings->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Referrals</span>
                    <span class="info-box-number">{{ auth()->user()->referrals()->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Referral Earnings History</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($earnings as $earning)
                                <tr>
                                    <td>{{ $earning->transaction_id }}</td>
                                    <td>
                                        <span class="text-success">
                                            +${{ number_format($earning->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>{{ $earning->description }}</td>
                                    <td>{{ $earning->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge badge-{{
                                            $earning->status === 'completed' ? 'success' :
                                            ($earning->status === 'pending' ? 'warning' : 'danger')
                                        }}">
                                            {{ ucfirst($earning->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <div class="py-4">
                                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                                            <h4>No earnings yet</h4>
                                            <p>Start referring people to earn commissions!</p>
                                            <a href="{{ route('referrals.index') }}" class="btn btn-primary">
                                                <i class="fas fa-share-alt"></i> Get Referral Link
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($earnings->hasPages())
                    <div class="card-footer">
                        {{ $earnings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop