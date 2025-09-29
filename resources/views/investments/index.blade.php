@extends('adminlte::page')

@section('title', 'My Investments')

@section('content_header')
    <h1>My Investments</h1>
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Active Investments</h3>
                    <div class="card-tools">
                        <a href="{{ route('investments.plans') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Investment
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Daily Rate</th>
                                <th>Expected Return</th>
                                <th>Duration</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($investments as $investment)
                                <tr>
                                    <td>{{ $investment->investment_plan }}</td>
                                    <td>${{ number_format($investment->amount, 2) }}</td>
                                    <td>{{ number_format($investment->daily_return_rate, 2) }}%</td>
                                    <td>${{ number_format($investment->expected_return, 2) }}</td>
                                    <td>{{ $investment->duration_days }} days</td>
                                    <td>{{ $investment->start_date->format('M d, Y') }}</td>
                                    <td>{{ $investment->end_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{
                                            $investment->status === 'active' ? 'success' :
                                            ($investment->status === 'completed' ? 'primary' : 'warning')
                                        }}">
                                            {{ ucfirst($investment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No investments yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($investments->hasPages())
                    <div class="card-footer">
                        {{ $investments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop